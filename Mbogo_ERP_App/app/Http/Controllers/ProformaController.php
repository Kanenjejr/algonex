<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use RealRashid\SweetAlert\Facades\Alert;

use App\Models\Proforma;
use App\Models\ProformaItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\WorkPoint;
use App\Models\CompanySite;
use App\Models\Company_unit;
use App\Models\AccntChart;
use App\Models\Section;
use App\Models\AccntSubchart;
use App\Models\AccntTransaction;

class ProformaController extends Controller
{
    // ================= SWEET ALERT =================
    private function swalMessage($type, $message)
    {
        if ($type == 'success') {
            Alert::success('Success', $message);
        } elseif ($type == 'error') {
            Alert::error('Error', $message);
        } elseif ($type == 'warning') {
            Alert::warning('Warning', $message);
        } else {
            Alert::info('Information', $message);
        }
    }

    // ================= DECRYPT PROFORMA ID =================
    private function decryptProformaId($encryptedId)
    {
        return Crypt::decryptString($encryptedId);
    }

    // ================= VAT / DECIMAL HELPERS =================
    private function proformaVatRate($invoiceType, $customer)
    {
        $invoiceType = $invoiceType ?? 'local';

        $hasTIN = !empty($customer->tin_number) || !empty($customer->tin);

        if ($invoiceType === 'local' && $hasTIN) {
            return 0.18;
        }

        return 0;
    }

    private function roundAmount($amount, $decimals = 6)
    {
        return round((float) $amount, $decimals);
    }

    private function roundTotalAmount($amount)
    {
        return round((float) $amount, 0);
    }


    private function allowedCurrencies()
    {
        return [
            'TZS',
            'USD',
            'EUR',
            'GBP',
            'KES',
            'UGX',
            'RWF',
            'ZAR',
            'AED',
            'CNY',
            'INR',
        ];
    }

    private function proformaCurrency($invoiceType, $currency = null)
    {
        $invoiceType = $invoiceType ?? 'local';

        // Local proforma is always paid in TZS.
        if ($invoiceType === 'local') {
            return 'TZS';
        }

        $currency = strtoupper(trim($currency ?? ''));

        // Foreign/export proforma defaults to USD, but the field supports other currencies.
        if ($currency === '') {
            return 'USD';
        }

        return in_array($currency, $this->allowedCurrencies(), true) ? $currency : 'USD';
    }

    private function roundCurrencyAmount($amount, $currency = 'TZS')
    {
        $currency = strtoupper($currency ?? 'TZS');

        if ($currency === 'TZS') {
            return round((float) $amount, 0);
        }

        return round((float) $amount, 2);
    }

    // ================= INDEX =================
    public function index()
    {
        $data = Proforma::with([
                'customer',
                'items.product',
                'items.service',
                'bank',
                'company',
                'businessUnit',
                'workPoint',
                'creator',
                'approver',
            ])
            ->latest()
            ->get();

        $customers = Customer::orderBy('customer_name')->get();

        $companies = CompanySite::orderBy('company_name')->get();

        $companyUnits = Company_unit::orderBy('unit_name')->get();

        $workPoints = WorkPoint::orderBy('work_name')->get();

        $banks = $this->bankAccounts();

        return view(
            'admin.store.sales_management.proformas',
            [
                'data' => $data,
                'customers' => $customers,
                'companies' => $companies,
                'companyUnits' => $companyUnits,
                'workPoints' => $workPoints,
                'banks' => $banks,
                'user' => auth()->user(),
            ]
        );
    }

    // ================= CREATE REDIRECT =================
    public function createProforma()
    {
        return redirect()
            ->route('sales.proformas');
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'company_id'        => 'required|exists:company_sites,id',
            'business_unit_id'  => 'required|exists:company_units,id',
            'work_point_id'     => 'required|exists:work_points,id',
            'invoice_type'      => 'nullable|string',
            'currency'          => 'nullable|string|in:TZS,USD,EUR,GBP,KES,UGX,RWF,ZAR,AED,CNY,INR',
            'exchange_rate'     => 'nullable|numeric|min:0',
            'bank_id'           => 'nullable',
            'items'             => 'required|array',
        ]);

        DB::beginTransaction();

        try {

            $year = date('Y');

            $count = Proforma::whereYear('created_at', $year)->count() + 1;

            $company = CompanySite::findOrFail($request->company_id);

            $customer = Customer::findOrFail($request->customer_id);

            $currency = $this->proformaCurrency($request->invoice_type ?? 'local', $request->currency);

            $companyCode = $company->company_code ?? 'MMGS';

            $country = strtoupper($customer->country ?? 'TZ');

            $country = str_replace(' ', '', $country);

            $proforma_no =
                $companyCode . '-' .
                $country . '-' .
                str_pad($count, 3, '0', STR_PAD_LEFT) . '-' .
                $year;

            // ================= VAT RATE =================
            // Unit price entered by user is VAT INCLUSIVE when customer has TIN.
            // Example: 95,000 x 1,200 = 114,000,000 total, VAT is extracted inside this total.
            $vatRate = $this->proformaVatRate($request->invoice_type ?? 'local', $customer);

            // ================= PREPARE ITEMS =================
            $preparedItems = $this->prepareProformaItems(
                $request->items,
                $request->item_type ?? 'product',
                $vatRate > 0,
                $vatRate
            );

            if (count($preparedItems) < 1) {

                DB::rollBack();

                $this->swalMessage('error', 'Please select at least one product or service item');

                return back()
                    ->withInput();
            }

            // ================= CALCULATE TOTALS =================
            // Unit price entered in the view is VAT inclusive.
            // Stored item price and item total are VAT exclusive actual values.
            $subtotal = 0;
            $vat = 0;

            foreach ($preparedItems as $item) {
                $subtotal += $item['total'];
                $vat += $item['vat_amount'] ?? 0;
            }

            $subtotal = $this->roundAmount($subtotal, 6);
            $vat = $this->roundAmount($vat, 6);
            $total = $this->roundCurrencyAmount($subtotal + $vat, $currency);

            // Keep the printed/posted figures balanced after currency rounding.
            if ($vatRate > 0) {
                $vat = $this->roundAmount($total - $subtotal, 6);
            } else {
                $vat = 0;
                $total = $this->roundCurrencyAmount($subtotal, $currency);
            }

            $total = $this->roundAmount($subtotal + $vat, 6);

// ================= SAVE PROFORMA =================
            $proforma = Proforma::create([
                'proforma_no'      => $proforma_no,
                'customer_id'      => $request->customer_id,
                'company_id'       => $request->company_id,
                'business_unit_id' => $request->business_unit_id,
                'work_point_id'    => $request->work_point_id,
                'subtotal'         => $subtotal,
                'vat'              => $vat,
                'total'            => $total,
                'invoice_type'     => $request->invoice_type ?? 'local',
                'status'           => 'draft',
                'created_by'       => auth()->id(),
                'bank_id'          => $request->bank_id,
                'account_number'   => $request->account_number,
                'swift_code'       => $request->swift_code,
                'branch'           => $request->branch,
            ]);

            $proforma->forceFill([
                'currency'      => $currency,
                'exchange_rate' => $request->filled('exchange_rate') ? (float) $request->exchange_rate : null,
            ])->save();

            // ================= SAVE ITEMS =================
            foreach ($preparedItems as $item) {

                ProformaItem::create([
                    'proforma_id' => $proforma->id,
                    'item_type'   => $item['item_type'],
                    'product_id'  => $item['product_id'],
                    'service_id'  => $item['service_id'],
                    'item_name'   => $item['item_name'],
                    'description' => $item['description'],
                    'qty'         => $item['qty'],
                    'unit'        => $item['unit'],
                    'price'       => $item['price'],
                    'total'       => $item['total'],
                ]);
            }

            DB::commit();

            $this->swalMessage('success', 'Proforma created successfully');

            return redirect()
                ->route('sales.proformas');

        } catch (\Exception $e) {

            DB::rollBack();

            $this->swalMessage('error', $e->getMessage());

            return back()
                ->withInput();
        }
    }

    // ================= PREPARE ITEMS BEFORE STORE / UPDATE =================
    private function prepareProformaItems($requestItems, $mainItemType = 'product', $vatInclusive = false, $vatRate = 0.18)
    {
        $preparedItems = [];

        foreach ($requestItems as $item) {

            $itemType = $item['item_type'] ?? $mainItemType ?? 'product';

            $productId = $item['product_id'] ?? null;

            $serviceId = $item['service_id'] ?? null;

            // ================= SAFETY FOR JS NAME CHANGE =================
            if ($itemType === 'service' && empty($serviceId) && !empty($productId)) {
                $serviceId = $productId;
                $productId = null;
            }

            if ($itemType === 'product' && empty($productId) && !empty($serviceId)) {
                $productId = $serviceId;
                $serviceId = null;
            }

            $itemName = trim($item['product_name'] ?? '');

            $description = trim($item['description'] ?? '');

            $unit = $item['unit'] ?? null;

            $qty = (float) ($item['qty'] ?? 0);

            $price = (float) ($item['price'] ?? 0);

            // ================= PRODUCT FALLBACK =================
            if ($itemType === 'product' && !empty($productId)) {

                $product = Product::find($productId);

                if ($product) {

                    if (empty($itemName)) {
                        $itemName = $product->product_name;
                    }

                    if (empty($description)) {
                        $description = $product->product_name;
                    }

                    if (empty($unit)) {
                        $unit = $product->unit ?? 'pcs';
                    }

                    if ($price <= 0) {
                        $price = (float) (
                            $product->selling_price ??
                            $product->avg_cost ??
                            0
                        );
                    }
                }
            }

            // ================= SERVICE FALLBACK =================
            if ($itemType === 'service' && !empty($serviceId)) {

                $service = Service::find($serviceId);

                if ($service) {

                    if (empty($itemName)) {
                        $itemName = $service->service_name;
                    }

                    if (empty($description)) {
                        $description = $service->service_name;
                    }

                    if (empty($unit)) {
                        $unit = $service->unit ?? 'service';
                    }

                    if ($price <= 0) {
                        $price = (float) ($service->price ?? 0);
                    }
                }
            }

            // ================= MANUAL DESCRIPTION FALLBACK =================
            if (empty($itemName) && !empty($description)) {
                $itemName = $description;
            }

            if (empty($description) && !empty($itemName)) {
                $description = $itemName;
            }

            if (empty($unit)) {
                $unit = $itemType === 'service' ? 'service' : 'pcs';
            }

            if ($qty <= 0) {
                continue;
            }

            if (empty($itemName)) {
                continue;
            }

            // ================= VAT INCLUSIVE INPUT / ACTUAL VALUES =================
            // Price typed on the form is VAT inclusive. The system stores actual VAT exclusive unit price.
            $grossUnitPrice = $this->roundAmount($price, 6);

            if ($vatInclusive && $vatRate > 0) {
                $actualUnitPrice = $this->roundAmount($grossUnitPrice / (1 + $vatRate), 6);
            } else {
                $actualUnitPrice = $grossUnitPrice;
            }

            $lineSubtotal = $this->roundAmount($qty * $actualUnitPrice, 6);
            $lineVatAmount = $vatInclusive && $vatRate > 0
                ? $this->roundAmount($lineSubtotal * $vatRate, 6)
                : 0;
            $grossLineTotal = $this->roundAmount($lineSubtotal + $lineVatAmount, 6);

            $preparedItems[] = [
                'item_type'        => $itemType,
                'product_id'       => $itemType === 'product' ? $productId : null,
                'service_id'       => $itemType === 'service' ? $serviceId : null,
                'item_name'        => $itemName,
                'description'      => $description,
                'qty'              => $this->roundAmount($qty, 6),
                'unit'             => $unit,
                'price'            => $actualUnitPrice,
                'total'            => $lineSubtotal,
                'vat_amount'       => $lineVatAmount,
                'gross_unit_price' => $grossUnitPrice,
                'gross_total'      => $grossLineTotal,
            ];
        }

        return $preparedItems;
    }

    // ================= ACCOUNTING =================
    private function postAccounting($proforma)
    {
        $proforma->load([
            'customer.account',
            'items.product',
            'items.service',
            'workPoint'
        ]);

        if ($proforma->accounting_transaction_group) {
            throw new \Exception('Accounting transaction already posted for this proforma');
        }
        $customerSubAccount = $this->customerReceivableSubAccount($proforma);

        if (!$customerSubAccount) {
            throw new \Exception('Customer receivable account is not mapped. Please set account_id on customer or create active 8-digit receivable sub account.');
        }

        $customerChart = $this->chartFromSubAccount($customerSubAccount);

        if (!$customerChart) {
            throw new \Exception('Customer account chart is not mapped correctly.');
        }

        $transactionGroup = (string) Str::uuid();

        $currency = $proforma->currency ?? (($proforma->invoice_type ?? 'local') === 'export' ? 'USD' : 'TZS');

        $exchangeRate = 1;

        $payee = optional($proforma->customer)->customer_name ?? 'Customer';

        $memo = 'Approved proforma ' . $proforma->proforma_no;
        $sec=Section::where('secCode','=','BDD00102')->first();
        // ================= DEBIT CUSTOMER =================
        AccntTransaction::create([
            'transaction_group'      => $transactionGroup,
            'pcv_no'                 => null,
            'trans_date'             => now()->toDateString(),
            'reference'              => $proforma->proforma_no . '-DR-CUSTOMER',
            'check_no'               => null,
            'request_no'             => null,
            'requisition_id'         => null,
            'category'               => 'Bank',
            'currency'               => $currency,
            'exchange_rate'          => $exchangeRate,
            'memo'                   => $memo,
            'payee'                  => $payee,
            'user_id'                => auth()->id(),
            'company_id'             => $proforma->company_id,
            'work_point_id'          => $proforma->work_point_id,
            'account_id'             => $customerChart->id,
            'sub_account_id'         => $customerSubAccount->id,
            'department_id'          => $sec->dept_id,
            'section_id'             => $sec->id,
            'type'                   => 'debit',
            'amount'                 => $proforma->total,
            'source_amount'          => $proforma->total,
            'imported_from_excel'    => false,
            'Status'                 => 'Active',
            'verified'               => 'verified',
            'verified_by'            => auth()->id(),
            'verified_at'            => now(),
            'verification_comment'   => 'Auto verified from approved proforma ' . $proforma->proforma_no,
            'approved'               => 'approved',
            'approved_by'            => auth()->id(),
            'approved_at'            => now(),
            'approval_comment'       => 'Auto approved from approved proforma ' . $proforma->proforma_no,
        ]);

        // ================= CREDIT PRODUCTS / SERVICES =================
        foreach ($proforma->items as $index => $item) {

            $creditSubAccount = $this->itemRevenueSubAccount($proforma, $item);

            if (!$creditSubAccount) {

                \Log::error('Revenue Account Mapping Failed', [
                    'item' => $item->item_name,
                    'product_id' => $item->product_id,
                    'revenue_code' => optional($item->product)->revenue_account_code,
                    'company_id' => $proforma->company_id,
                    'work_point_id' => $proforma->work_point_id,
                ]);

                throw new \Exception(
                    'Revenue account is not mapped for item: '
                    . $item->item_name
                );
            }

            $creditChart = $this->chartFromSubAccount($creditSubAccount);

            if (!$creditChart) {
                throw new \Exception('Revenue chart is not mapped correctly for item: ' . $item->item_name);
            }

            AccntTransaction::create([
                'transaction_group'      => $transactionGroup,
                'pcv_no'                 => null,
                'trans_date'             => now()->toDateString(),
                'reference'              => $proforma->proforma_no . '-CR-ITEM-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'check_no'               => null,
                'request_no'             => null,
                'requisition_id'         => null,
                'category'               => 'Bank',
                'currency'               => $currency,
                'exchange_rate'          => $exchangeRate,
                'memo'                   => $memo . ' | ' . $item->item_name,
                'payee'                  => $payee,
                'user_id'                => auth()->id(),
                'company_id'             => $proforma->company_id,
                'work_point_id'          => $proforma->work_point_id,
                'account_id'             => $creditChart->id,
                'sub_account_id'         => $creditSubAccount->id,
                'department_id'          => $sec->dept_id,
                'section_id'             => $sec->id,
                'type'                   => 'credit',
                'amount'                 => $item->total,
                'source_amount'          => $item->total,
                'imported_from_excel'    => false,
                'Status'                 => 'Active',
                'verified'               => 'verified',
                'verified_by'            => auth()->id(),
                'verified_at'            => now(),
                'verification_comment'   => 'Auto verified from approved proforma ' . $proforma->proforma_no,
                'approved'               => 'approved',
                'approved_by'            => auth()->id(),
                'approved_at'            => now(),
                'approval_comment'       => 'Auto approved from approved proforma ' . $proforma->proforma_no,
            ]);
        }

        // ================= CREDIT VAT IF ANY =================
        if ($proforma->vat > 0) {

            $vatSubAccount = $this->vatSubAccount($proforma);

            if (!$vatSubAccount) {
                throw new \Exception('VAT account is not mapped. Please create active 8-digit VAT sub account.');
            }

            $vatChart = $this->chartFromSubAccount($vatSubAccount);

            if (!$vatChart) {
                throw new \Exception('VAT chart is not mapped correctly.');
            }

            AccntTransaction::create([
                'transaction_group'      => $transactionGroup,
                'pcv_no'                 => null,
                'trans_date'             => now()->toDateString(),
                'reference'              => $proforma->proforma_no . '-CR-VAT',
                'check_no'               => null,
                'request_no'             => null,
                'requisition_id'         => null,
                'category'               => 'Bank',
                'currency'               => $currency,
                'exchange_rate'          => $exchangeRate,
                'memo'                   => $memo . ' | VAT',
                'payee'                  => $payee,
                'user_id'                => auth()->id(),
                'company_id'             => $proforma->company_id,
                'work_point_id'          => $proforma->work_point_id,
                'account_id'             => $vatChart->id,
                'sub_account_id'         => $vatSubAccount->id,
                'department_id'          => $sec->dept_id,
                'section_id'             => $sec->id,
                'type'                   => 'credit',
                'amount'                 => $proforma->vat,
                'source_amount'          => $proforma->vat,
                'imported_from_excel'    => false,
                'Status'                 => 'Active',
                'verified'               => 'verified',
                'verified_by'            => auth()->id(),
                'verified_at'            => now(),
                'verification_comment'   => 'Auto verified from approved proforma ' . $proforma->proforma_no,
                'approved'               => 'approved',
                'approved_by'            => auth()->id(),
                'approved_at'            => now(),
                'approval_comment'       => 'Auto approved from approved proforma ' . $proforma->proforma_no,
            ]);
        }

        return $transactionGroup;
    }
    // ================= CUSTOMER RECEIVABLE ACCOUNT - 8 DIGIT ONLY =================
     private function customerReceivableSubAccount($proforma)
    {
        // 1. Use customer mapped account if it is active and 8 digit
        if (!empty($proforma->customer->account_id)) {
            $sub = AccntSubchart::where('id', $proforma->customer->account_id)
                ->where('Status', 'Active')->whereRaw('LENGTH(SubCode) = 8')->first();
            if ($sub) {
                return $sub;
            }
        }
        // 2. Try exact default customer receivable account
        $sub = AccntSubchart::where('Status', 'Active')->whereRaw('LENGTH(SubCode) = 8')
            ->where('SubCode', '41740100')->first();
        if ($sub) {
            return $sub;
        }
        // 3. Try ordinary customer account
        $sub = AccntSubchart::where('Status', 'Active')->whereRaw('LENGTH(SubCode) = 8')
            ->where('SubCode', '41140100')->first();
        if ($sub) {
            return $sub;
        }
        // 4. Last fallback: any active 8-digit customer account under 411 or 417
        return AccntSubchart::where('Status', 'Active')->whereRaw('LENGTH(SubCode) = 8')
            ->where(function ($q) {
                $q->where('SubCode', 'LIKE', '411%')->orWhere('SubCode', 'LIKE', '417%');
            })->orderBy('SubCode')->first();
    }
    // ================= ITEM REVENUE ACCOUNT - 8 DIGIT ONLY =================
    private function itemRevenueSubAccount($proforma, $item)
    {
        $code = trim((string) optional($item->product)->revenue_account_code);

        if ($code !== '') {

            $sub = $this->find8DigitSubAccountByCode(
                $proforma->company_id,
                $proforma->work_point_id,
                $code
            );

            if ($sub) {
                return $sub;
            }
        }

        return $this->getExisting8DigitSubAccount(
            $proforma->company_id,
            $proforma->work_point_id,
            ['701', '711', '715'],
            ['Sales', 'Revenue', 'Income', 'Service']
        );
    }

    // ================= VAT ACCOUNT - 8 DIGIT ONLY =================
        private function vatSubAccount($proforma)
    {
        // 1) Try company + work point VAT account first
        $sub = $this->getExisting8DigitSubAccount(
            $proforma->company_id,
            $proforma->work_point_id,
            ['143'],
            ['Value Added Tax (VAT) on Domestic', 'Output VAT', 'VAT', 'Tax']
        );

        if ($sub) {
            return $sub;
        }

        // 2) Global fallback: any active 8-digit VAT account
        return AccntSubchart::where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode)=8')
            ->where('SubCode', 'LIKE', '143%')
            ->where(function ($q) {
                $q->where('SubDescription', 'LIKE', '%VAT%')
                ->orWhere('SubDescription', 'LIKE', '%Output VAT%')
                ->orWhere('SubDescription', 'LIKE', '%Tax%');
            })
            ->orderBy('SubCode')
            ->first();
    }
    // ================= CHART FROM SUB ACCOUNT =================
    private function chartFromSubAccount($subAccount)
    {
        if (!$subAccount) {
            return null;
        }

        return AccntChart::where('id', $subAccount->accnt_chart_id)
            ->where('Status', 'Active')
            ->first();
    }

    // ================= FIND 8 DIGIT SUB ACCOUNT BY CODE =================
    private function find8DigitSubAccountByCode($companyId, $workPointId, $subCode)
    {
        $subCode = trim((string) $subCode);

        if ($subCode === '') {
            return null;
        }

        return AccntSubchart::query()
            ->whereRaw('TRIM(SubCode) = ?', [$subCode])
            ->where('Status', 'Active')
            ->whereRaw('LENGTH(TRIM(SubCode)) = 8')
            ->first();
    }

    // ================= GET EXISTING 8 DIGIT SUB ACCOUNT =================
    private function getExisting8DigitSubAccount($companyId, $workPointId, $prefixes, $keywords = [])
    {
        if (!is_array($prefixes)) {
            $prefixes = [$prefixes];
        }

        if (!is_array($keywords)) {
            $keywords = [$keywords];
        }

        $query = AccntSubchart::where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode)=8')
            ->where(function ($q) use ($companyId) {
                $q->whereNull('company_id')
                    ->orWhere('company_id', $companyId);
            })
            ->where(function ($q) use ($workPointId) {
                $q->whereNull('work_point_id')
                    ->orWhere('work_point_id', $workPointId);
            })
            ->where(function ($q) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    $q->orWhere('SubCode', 'LIKE', $prefix . '%');
                }
            });

        if (count($keywords) > 0) {

            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    if (!empty($keyword)) {
                        $q->orWhere('SubDescription', 'LIKE', '%' . $keyword . '%');
                    }
                }
            });
        }

        return $query
            ->orderBy('SubCode')
            ->first();
    }

    // ================= GET ACCOUNT BY NAME - 8 DIGIT ONLY =================
    private function getAccountByName($companyId, $workPointId, $keyword)
    {
        return AccntSubchart::where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode)=8')
            ->where(function ($q) use ($companyId) {
                $q->whereNull('company_id')
                    ->orWhere('company_id', $companyId);
            })
            ->where(function ($q) use ($workPointId) {
                $q->whereNull('work_point_id')
                    ->orWhere('work_point_id', $workPointId);
            })
            ->where('SubDescription', 'LIKE', '%' . $keyword . '%')
            ->orderBy('SubCode')
            ->first();
    }

    // ================= APPROVE / REJECT =================
    public function approve(Request $request, $id)
    {
        $id = $this->decryptProformaId($id);

        $proforma = Proforma::with([
                'customer',
                'items.product',
                'items.service'
            ])
            ->findOrFail($id);

        $action = strtolower($request->approval_action ?? 'approve');

        if (!in_array(strtolower($proforma->status), ['draft'])) {

            $this->swalMessage('error', 'Only draft proforma can be approved or rejected');

            return back();
        }

        // ================= REJECT =================
        // No accounting transaction is posted when rejected.
        // Rejected proforma remains editable, and on update it becomes draft again for resubmission.
        if ($action === 'reject') {

            $proforma->update([
                'status' => 'rejected',
                'approved_by' => null,
                'approved_at' => null,
                'accounting_transaction_group' => null,
                'updated_by' => auth()->id(),
            ]);

            $this->swalMessage('warning', 'Proforma rejected. It can be edited and resubmitted again.');

            return back();
        }

        DB::beginTransaction();

        try {

            $transactionGroup = $this->postAccounting($proforma);

            $proforma->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'accounting_transaction_group' => $transactionGroup,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            $this->swalMessage('success', 'Proforma approved successfully and accounting transaction posted');

            return back();

        } catch (\Exception $e) {

            DB::rollBack();

            $this->swalMessage('error', $e->getMessage());

            return back();
        }
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $id = $this->decryptProformaId($id);

        $proforma = Proforma::with([
                'items.product',
                'items.service',
                'customer',
                'bank',
                'company',
                'businessUnit',
                'workPoint',
                'creator',
                'approver',
            ])
            ->findOrFail($id);

        if (strtolower($proforma->status) === 'approved') {

            $this->swalMessage('warning', 'Approved proforma cannot be edited. You can view only.');

            return redirect()
                ->route(
                    'proforma.view',
                    Crypt::encryptString($proforma->id)
                );
        }

        $customers = Customer::orderBy('customer_name')->get();

        $companies = CompanySite::orderBy('company_name')->get();

        $companyUnits = Company_unit::where('company_id', $proforma->company_id)
            ->orderBy('unit_name')
            ->get();

        $workPoints = WorkPoint::where('comp_unit_id', $proforma->business_unit_id)
            ->orderBy('work_name')
            ->get();
        $banks = $this->bankAccounts();

        $bank = DB::table('accnt_subcharts')
            ->where('id', $proforma->bank_id)
            ->first();

        return view(
            'admin.store.sales_management.proformas_edit',
            [
                'proforma' => $proforma,
                'customers' => $customers,
                'companies' => $companies,
                'companyUnits' => $companyUnits,
                'workPoints' => $workPoints,
                'banks' => $banks,
                'bank' => $bank,
                'user' => auth()->user(),
            ]
        );
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $id = $this->decryptProformaId($id);

        $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'company_id'        => 'required|exists:company_sites,id',
            'business_unit_id'  => 'required|exists:company_units,id',
            'work_point_id'     => 'required|exists:work_points,id',
            'invoice_type'      => 'nullable|string',
            'currency'          => 'nullable|string|in:TZS,USD,EUR,GBP,KES,UGX,RWF,ZAR,AED,CNY,INR',
            'items'             => 'required|array',
        ]);

        DB::beginTransaction();

        try {

            $proforma = Proforma::findOrFail($id);

            if (strtolower($proforma->status) === 'approved') {

                DB::rollBack();

                $this->swalMessage('error', 'Approved proforma cannot be updated');

                return redirect()
                    ->route(
                        'proforma.view',
                        Crypt::encryptString($proforma->id)
                    );
            }

            $customer = Customer::findOrFail($request->customer_id);

            $currency = $this->proformaCurrency($request->invoice_type ?? 'local', $request->currency);

            // ================= VAT RATE =================
            // Unit price entered by user is VAT INCLUSIVE when customer has TIN.
            // Example: 95,000 x 1,200 = 114,000,000 total, VAT is extracted inside this total.
            $vatRate = $this->proformaVatRate($request->invoice_type ?? 'local', $customer);

            // ================= PREPARE ITEMS =================
            $preparedItems = $this->prepareProformaItems(
                $request->items,
                $request->item_type ?? 'product',
                $vatRate > 0,
                $vatRate
            );

            if (count($preparedItems) < 1) {

                DB::rollBack();

                $this->swalMessage('error', 'Please select at least one product or service item');

                return back()
                    ->withInput();
            }

            // ================= CALCULATE TOTALS =================
            // Unit price entered in the view is VAT inclusive.
            // Stored item price and item total are VAT exclusive actual values.
            $subtotal = 0;
            $vat = 0;

            foreach ($preparedItems as $item) {
                $subtotal += $item['total'];
                $vat += $item['vat_amount'] ?? 0;
            }

            $subtotal = $this->roundAmount($subtotal, 6);
            $vat = $this->roundAmount($vat, 6);
            $total = $this->roundCurrencyAmount($subtotal + $vat, $currency);

            // Keep the printed/posted figures balanced after currency rounding.
            if ($vatRate > 0) {
                $vat = $this->roundAmount($total - $subtotal, 6);
            } else {
                $vat = 0;
                $total = $this->roundCurrencyAmount($subtotal, $currency);
            }

            $total = $this->roundAmount($subtotal + $vat, 6);

// ================= UPDATE PROFORMA =================
            $proforma->update([
                'customer_id'      => $request->customer_id,
                'company_id'       => $request->company_id,
                'business_unit_id' => $request->business_unit_id,
                'work_point_id'    => $request->work_point_id,
                'subtotal'         => $subtotal,
                'vat'              => $vat,
                'total'            => $total,
                'invoice_type'     => $request->invoice_type ?? 'local',
                'bank_id'          => $request->bank_id,
                'account_number'   => $request->account_number,
                'swift_code'       => $request->swift_code,
                'branch'           => $request->branch,
                'status'           => 'draft',
                'approved_by'      => null,
                'approved_at'      => null,
                'accounting_transaction_group' => null,
                'updated_by'       => auth()->id(),
            ]);

            $proforma->forceFill([
                'currency'      => $currency,
                'exchange_rate' => $request->filled('exchange_rate') ? (float) $request->exchange_rate : null,
            ])->save();

            // ================= DELETE OLD ITEMS =================
            ProformaItem::where('proforma_id', $proforma->id)->delete();

            // ================= SAVE NEW ITEMS =================
            foreach ($preparedItems as $item) {

                ProformaItem::create([
                    'proforma_id' => $proforma->id,
                    'item_type'   => $item['item_type'],
                    'product_id'  => $item['product_id'],
                    'service_id'  => $item['service_id'],
                    'item_name'   => $item['item_name'],
                    'description' => $item['description'],
                    'qty'         => $item['qty'],
                    'unit'        => $item['unit'],
                    'price'       => $item['price'],
                    'total'       => $item['total'],
                ]);
            }

            DB::commit();

            $this->swalMessage('success', 'Proforma updated successfully');

            return redirect()
                ->route('sales.proformas');

        } catch (\Exception $e) {

            DB::rollBack();

            $this->swalMessage('error', $e->getMessage());

            return back()
                ->withInput();
        }
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $id = $this->decryptProformaId($id);

        $proforma = Proforma::findOrFail($id);

        if (!in_array(strtolower($proforma->status), ['draft', 'rejected'])) {

            $this->swalMessage('error', 'Only draft or rejected proforma can be deleted');

            return back();
        }

        DB::beginTransaction();

        try {

            ProformaItem::where('proforma_id', $id)->delete();

            $proforma->delete();

            DB::commit();

            $this->swalMessage('success', 'Proforma deleted successfully');

            return back();

        } catch (\Exception $e) {

            DB::rollBack();

            $this->swalMessage('error', $e->getMessage());

            return back();
        }
    }

    // ================= AJAX BUSINESS UNITS =================
    public function getBusinessUnits($company_id)
    {
        return Company_unit::where('company_id', $company_id)
            ->select(
                'id',
                'unit_code',
                'unit_name'
            )
            ->orderBy('unit_name')
            ->get();
    }

    // ================= AJAX WORK POINTS =================
    public function getWorkPoints($business_id)
    {
        return WorkPoint::where('comp_unit_id', $business_id)
            ->select(
                'id',
                'work_code',
                'work_name'
            )
            ->orderBy('work_name')
            ->get();
    }

    // ================= GET BANK =================
    public function getBank($company_id)
    {
        return DB::table('accnt_subcharts')
            ->where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode)=8')
            ->where('company_id', $company_id)
            ->where(function ($query) {
                $query->where('SubDescription', 'LIKE', '%BANK%')
                    ->orWhere('SubDescription', 'LIKE', '%ACCOUNT%');
            })
            ->first();
    }

    // ================= BANK ACCOUNTS =================
    private function bankAccounts()
    {
        return DB::table('accnt_subcharts')
            ->where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode)=8')
            ->where(function ($q) {
                $q->where('SubDescription', 'LIKE', '%Bank%')
                    ->orWhere('SubDescription', 'LIKE', '%CRDB%')
                    ->orWhere('SubDescription', 'LIKE', '%NMB%')
                    ->orWhere('SubDescription', 'LIKE', '%DTB%')
                    ->orWhere('SubDescription', 'LIKE', '%ACCOUNT%');
            })
            ->select(
                'id',
                'SubCode',
                'SubDescription'
            )
            ->orderBy('SubDescription')
            ->get();
    }

    // ================= VIEW =================
    public function view($id)
    {
        $id = $this->decryptProformaId($id);

        $proforma = Proforma::with([
                'items.product',
                'items.service',
                'customer',
                'company',
                'businessUnit',
                'workPoint',
                'creator',
                'approver',
                'bank',
            ])
            ->findOrFail($id);

        $banks = DB::table('accnt_subcharts')
            ->where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode)=8')
            ->where('company_id', $proforma->company_id)
            ->where(function ($query) {
                $query->where('SubDescription', 'LIKE', '%BANK%')
                    ->orWhere('SubDescription', 'LIKE', '%ACCOUNT%');
            })
            ->get();

        if ($banks->isEmpty()) {

            $banks = DB::table('accnt_subcharts')
                ->where('Status', 'Active')
                ->whereRaw('LENGTH(SubCode)=8')
                ->where(function ($query) {
                    $query->where('SubDescription', 'LIKE', '%BANK%')
                        ->orWhere('SubDescription', 'LIKE', '%ACCOUNT%');
                })
                ->get();
        }

        $selectedBank = DB::table('accnt_subcharts')
            ->where('id', $proforma->bank_id)
            ->first();

        return view(
            'admin.store.sales_management.proforma_view',
            [
                'proforma' => $proforma,
                'banks' => $banks,
                'bank' => $selectedBank,
            ]
        );
    }

    // ================= PDF VIEW =================
    public function pdf($id)
    {
        $id = $this->decryptProformaId($id);

        $proforma = Proforma::with([
                'items.product',
                'items.service',
                'customer',
                'company',
                'businessUnit',
                'workPoint',
                'creator',
                'approver',
                'bank',
            ])
            ->findOrFail($id);

        $banks = DB::table('accnt_subcharts')
            ->where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode)=8')
            ->where('company_id', $proforma->company_id)
            ->where(function ($query) {
                $query->where('SubDescription', 'LIKE', '%BANK%')
                    ->orWhere('SubDescription', 'LIKE', '%ACCOUNT%');
            })
            ->get();

        if ($banks->isEmpty()) {

            $banks = DB::table('accnt_subcharts')
                ->where('Status', 'Active')
                ->whereRaw('LENGTH(SubCode)=8')
                ->where(function ($query) {
                    $query->where('SubDescription', 'LIKE', '%BANK%')
                        ->orWhere('SubDescription', 'LIKE', '%ACCOUNT%');
                })
                ->get();
        }

        $selectedBank = DB::table('accnt_subcharts')
            ->where('id', $proforma->bank_id)
            ->first();

        return view(
            'admin.store.sales_management.proforma_pdf',
            [
                'proforma' => $proforma,
                'banks' => $banks,
                'bank' => $selectedBank,
            ]
        );
    }

    // ================= PRINT =================
    public function print($id)
    {
        $id = $this->decryptProformaId($id);

        $proforma = Proforma::with([
                'items.product',
                'items.service',
                'customer',
                'company',
                'businessUnit',
                'workPoint',
                'creator',
                'approver',
                'bank',
            ])
            ->findOrFail($id);

        return view(
            'admin.store.sales_management.proforma_print',
            compact('proforma')
        );
    }
// ================= PROFORMA ITEMS =================
    public function getProformaItems($id)
    {
        try {

            if (!is_numeric($id)) {
                $id = $this->decryptProformaId($id);
            }

            $proforma = Proforma::with('items')
                ->findOrFail($id);

            $items = [];

            foreach ($proforma->items as $item) {

                $items[] = [
                    'description' => $item->description ?? $item->item_name,
                    'qty' => $item->qty ?? 0,
                    'unit' => $item->unit ?? 'PCS',
                    'price' => $item->price ?? 0,
                    'total' => $item->total ?? 0,
                ];
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'bank_name' => '',
                'account_number' => $proforma->account_number,
                'swift_code' => $proforma->swift_code,
                'branch' => $proforma->branch,
                'currency' => $proforma->currency ?? 'TZS',
                'exchange_rate' => $proforma->exchange_rate ?? null,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    // ================= ALL PRODUCTS =================
    public function getAllProducts()
    {
        $products = DB::table('products as p')
            ->leftJoin('product_stocks as ps', 'ps.product_id', '=', 'p.id')
            ->where('p.status', 'Active')
            ->select(
                DB::raw('MIN(p.id) as id'),
                'p.product_name',
                DB::raw('MAX(p.product_size) as product_size'),
                DB::raw("'pcs' as unit"),
                DB::raw('COALESCE(SUM(ps.current_stock), MAX(p.opening_stock), 0) as current_stock'),
                DB::raw('COALESCE(MAX(p.selling_price), MAX(p.avg_cost), 0) as price')
            )
            ->groupBy('p.product_name')
            ->orderBy('p.product_name')
            ->get();

        return response()->json($products);
    }
    // ================= ALL SERVICES FOR PROFORMA =================
    public function getAllServices()
    {
        $services = DB::table('services')
            ->where('status', 'Active')
            ->select(
                DB::raw('MIN(id) as id'),
                DB::raw('MAX(service_code) as service_code'),
                'service_name',
                DB::raw('MAX(price) as price'),
                DB::raw("COALESCE(MAX(unit), 'service') as unit")
            )
            ->groupBy('service_name')
            ->orderBy('service_name')
            ->get();

        return response()->json($services);
    }
}
