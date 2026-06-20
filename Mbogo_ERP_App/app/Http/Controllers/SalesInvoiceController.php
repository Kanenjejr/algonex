<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\CustomerPayment;
use App\Models\Proforma;
use App\Models\Customer;
use App\Models\Section;
use App\Models\Product;
use App\Models\Service;
use App\Models\CompanySite;
use App\Models\Company_unit;
use App\Models\WorkPoint;
use App\Models\AccntChart;
use App\Models\AccntSubchart;
use App\Models\AccntTransaction;
use App\Models\ProductStock;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\File;

class SalesInvoiceController extends Controller
{
    private function swalMessage($type, $message)
    {
        if ($type === 'success') Alert::success('Success', $message);
        elseif ($type === 'error') Alert::error('Error', $message);
        elseif ($type === 'warning') Alert::warning('Warning', $message);
        else Alert::info('Information', $message);
    }

    private function encryptId($id)
    {
        return Crypt::encryptString((string) $id);
    }

    private function decryptId($value)
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) return (int) $value;
        return (int) Crypt::decryptString($value);
    }

    private function decryptRequestIds(Request $request)
    {
        $fields = [
            'proforma_id',
            'invoice_id',
            'customer_id',
            'company_id',
            'business_unit_id',
            'work_point_id',
            'payment_account_id',
            'bank_id',
            'delivery_payment_account_id',
            'delivery_service_income_account_id',
            'waybill_payment_account_id',
            'waybill_service_income_account_id',
        ];

        $data = [];

        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $data[$field] = $this->decryptId($request->input($field));
            }
        }

        if (isset($data['waybill_payment_account_id'])) {
            $data['delivery_payment_account_id'] = $data['waybill_payment_account_id'];
        }

        if (isset($data['waybill_service_income_account_id'])) {
            $data['delivery_service_income_account_id'] = $data['waybill_service_income_account_id'];
        }

        $items = $request->input('items', []);

        foreach ($items as $index => $item) {
            if (!empty($item['product_id'])) {
                $items[$index]['product_id'] = $this->decryptId($item['product_id']);
            }

            if (!empty($item['service_id'])) {
                $items[$index]['service_id'] = $this->decryptId($item['service_id']);
            }
        }

        if (!empty($items)) {
            $data['items'] = $items;
        }

        if (!empty($data)) {
            $request->merge($data);
        }
    }

    public function index()
    {
        $invoices = Invoice::with(['customer','proforma','items','payments','deliveries'])->latest()->get();
        $customers = Customer::orderBy('customer_name')->get();
        $companies = CompanySite::orderBy('company_name')->get();
        $companyUnits = Company_unit::orderBy('unit_name')->get();
        $workPoints = WorkPoint::orderBy('work_name')->get();

        // Only approved proformas that still have balance.
        $proformas = Proforma::with(['customer','items'])
            ->whereIn('status', ['approved', 'converted'])
            ->where(function ($q) {
                $q->whereNull('payment_status')
                  ->orWhereIn('payment_status', ['unpaid','partial']);
            })
            ->whereRaw('COALESCE(paid_amount,0) < total')
            ->orderByDesc('created_at')
            ->get();

        $paymentAccounts = $this->paymentAccounts();
        $serviceIncomeAccounts = $this->serviceIncomeAccounts();

        return view('admin.store.sales_management.invoices', compact(
            'invoices','customers','companies','companyUnits','workPoints','proformas','paymentAccounts','serviceIncomeAccounts'
        ));
    }

    public function show($encryptedId)
    {
        $invoice = Invoice::with(['customer','company','businessUnit','workPoint','items.product','payments.paymentAccount','proforma','deliveries.items.product'])->findOrFail($this->decryptId($encryptedId));
        $paymentAccounts = $this->paymentAccounts(null, $invoice->company_id, $invoice->work_point_id);
        return view('admin.store.sales_management.invoice_view', compact('invoice','paymentAccounts'));
    }

    public function print($encryptedId)
    {
        $invoice = Invoice::with(['customer','company','businessUnit','workPoint','items.product','payments.paymentAccount','proforma','deliveries'])->findOrFail($this->decryptId($encryptedId));
        return view('admin.store.sales_management.invoice_print', compact('invoice'));
    }

    public function store(Request $request)
    {
        $this->decryptRequestIds($request);

        $request->validate([
            'proforma_id' => 'nullable|exists:proformas,id',
            'customer_id' => 'required_without:proforma_id|exists:customers,id',
            'company_id' => 'required_without:proforma_id|exists:company_sites,id',
            'business_unit_id' => 'nullable|exists:company_units,id',
            'work_point_id' => 'required_without:proforma_id|exists:work_points,id',
            'invoice_type' => 'nullable|string',
            'vat_option' => 'nullable|in:yes,no',
            'currency' => 'nullable|string|max:10',
            'exchange_rate' => 'nullable|numeric|min:0.000001',
            'payment_type' => 'required|in:full,partial,credit',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'payment_account_id' => 'nullable|exists:accnt_subcharts,id',
            'receipt_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'items' => 'required_without:proforma_id|array',
            'items.*.qty' => 'nullable|numeric|min:0',
            'items.*.price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $proforma = null;

            if ($request->filled('proforma_id')) {
                $proforma = Proforma::with([
                    'items.product',
                    'items.service',
                    'customer',
                    'company',
                    'businessUnit',
                    'workPoint',
                    'bank'
                ])->lockForUpdate()->findOrFail($request->proforma_id);

                if (!in_array(strtolower($proforma->status), ['approved', 'converted'])) {
                    throw new \Exception('Only approved proformas can be invoiced or paid.');
                }

                if (
                    strtolower($proforma->payment_status ?? 'unpaid') === 'paid' ||
                    (float) $proforma->paid_amount >= (float) $proforma->total
                ) {
                    throw new \Exception('This proforma is already fully paid.');
                }
            }

            $customerId = $proforma->customer_id ?? $request->customer_id;
            $companyId = $proforma->company_id ?? $request->company_id;
            $businessUnitId = $proforma->business_unit_id ?? $request->business_unit_id;
            $workPointId = $proforma->work_point_id ?? $request->work_point_id;
            $invoiceType = $proforma->invoice_type ?? ($request->invoice_type ?? 'local');
            $currency = $request->currency ?? 'TZS';
            $exchangeRate = $request->exchange_rate ?? 1;

            $customer = Customer::findOrFail($customerId);
            $company = CompanySite::find($companyId);

            $preparedItems = $proforma
                ? $this->itemsFromProforma($proforma)
                : $this->prepareManualItems($request->items ?? []);

            if (count($preparedItems) < 1) {
                throw new \Exception('Please add at least one invoice item.');
            }

            /*
            |--------------------------------------------------------------------------
            | VAT LOGIC
            |--------------------------------------------------------------------------
            | 1. Proforma invoices use the exact VAT already saved in proforma.
            |    If proforma VAT is 0, invoice VAT is also 0.
            |
            | 2. Normal sales can be VAT inclusive or not.
            |    If VAT inclusive = yes, item line totals are treated as gross amount.
            |    Example: 20,000 paid with VAT yes:
            |       Subtotal = 20,000 / 1.18
            |       VAT      = 20,000 - Subtotal
            |       Total    = 20,000
            */
            $vatInclusive = false;

            if ($proforma) {
                $subtotal = (float) $proforma->subtotal;
                $tax = (float) $proforma->vat;
                $total = (float) $proforma->total;
                $vatRate = $subtotal > 0 ? round(($tax / $subtotal) * 100, 2) : 0;
                $vatInclusive = false;
            } else {
                $grossTotal = collect($preparedItems)->sum('total');
                $includeVat = $request->vat_option === 'yes';

                if ($includeVat && $invoiceType !== 'export') {
                    $vatRate = 18;
                    $total = round($grossTotal, 2);
                    $subtotal = round($grossTotal / 1.18, 2);
                    $tax = round($total - $subtotal, 2);
                    $vatInclusive = true;

                    // Store invoice item values as VAT-exclusive sales values.
                    // This makes accounting credit revenue only, while VAT is posted separately.
                    foreach ($preparedItems as $key => $line) {
                        $grossLine = (float) $line['total'];
                        $netLine = round($grossLine / 1.18, 2);
                        $qty = (float) ($line['qty'] ?: 1);
                        $preparedItems[$key]['price'] = round($netLine / $qty, 2);
                        $preparedItems[$key]['total'] = $netLine;
                    }
                } else {
                    $vatRate = 0;
                    $subtotal = round($grossTotal, 2);
                    $tax = 0;
                    $total = round($grossTotal, 2);
                    $vatInclusive = false;
                }
            }

            $alreadyPaid = $proforma ? (float) $proforma->paid_amount : 0;
            $invoiceNo = $this->generateInvoiceNo($company, $customer);

            $paymentAmount = $this->resolvePaymentAmount(
                $request->payment_type,
                (float) $request->payment_amount,
                $total,
                $alreadyPaid
            );

            $balance = max(0, $total - $alreadyPaid);

            $invoice = Invoice::create([
                'proforma_id' => optional($proforma)->id,
                'invoice_no' => $invoiceNo,
                'reference_no' => $request->reference_no,
                'invoice_date' => now()->toDateString(),
                'due_date' => $request->due_date,
                'agreement_date' => $request->agreement_date,
                'invoice_type' => $invoiceType,
                'customer_id' => $customerId,
                'company_id' => $companyId,
                'business_unit_id' => $businessUnitId,
                'work_point_id' => $workPointId,
                'created_by' => auth()->id(),

                'sub_total' => $subtotal,
                'tax' => $tax,
                'vat_rate' => $vatRate,
                'vat_inclusive' => $vatInclusive,
                'discount' => 0,
                'total' => $total,

                'paid_amount' => $alreadyPaid,
                'balance' => $balance,
                'status' => $alreadyPaid > 0 ? 'partial' : 'unpaid',
                'payment_status' => $alreadyPaid > 0 ? 'partial' : 'unpaid',
                'payment_type' => $request->payment_type,

                'bank_id' => $proforma->bank_id ?? $request->bank_id,
                'bank_name' => optional(optional($proforma)->bank)->SubDescription ?? $request->bank_name,
                'account_number' => optional($proforma)->account_number ?? $request->account_number,
                'swift_code' => optional($proforma)->swift_code ?? $request->swift_code,
                'branch' => optional($proforma)->branch ?? $request->branch,

                'currency' => $currency,
                'exchange_rate' => $exchangeRate,
                'total_tzs' => $currency === 'TZS' ? $total : ($total * $exchangeRate),
            ]);

            foreach ($preparedItems as $item) {
                InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
            }

            // Create pending payment only. Accounting and stock posting happen on verifyPayment().
            if ($paymentAmount > 0) {
                if (empty($request->payment_account_id)) {
                    throw new \Exception('Please select Bank / Cash / Mobile Account before saving invoice payment.');
                }

                $this->createPendingPayment($invoice, $proforma, $paymentAmount, $request);
            }

            if ($proforma) {
                $proforma->update([
                    'status' => 'converted',
                    'updated_by' => auth()->id(),
                ]);
            }

            DB::commit();

            $this->swalMessage(
                'success',
                'Invoice created. Payment is pending approval before accounting/stock posting.'
            );

            return redirect()->route('sales.invoice.view', $this->encryptId($invoice->id));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->swalMessage('error', $e->getMessage());

            return back()
                ->withErrors(['invoice_submit' => $e->getMessage()])
                ->withInput();
        }
    }

    public function recordAdditionalPayment(Request $request, $encryptedInvoiceId)
    {
        $request->merge(['invoice_id' => $this->decryptId($encryptedInvoiceId)]);
        $this->decryptRequestIds($request);

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'payment_account_id' => 'required|exists:accnt_subcharts,id',
            'receipt_no' => 'nullable|string|max:100',
            'receipt_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::with('proforma')->lockForUpdate()->findOrFail($request->invoice_id);
            if ($invoice->balance <= 0) throw new \Exception('This invoice is already fully paid.');
            $amount = min((float)$request->payment_amount, (float)$invoice->balance);
            $this->createPendingPayment($invoice, $invoice->proforma, $amount, $request);
            DB::commit();
            $this->swalMessage('success', 'Payment recorded as pending. It must be approved before posting to accounting.');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->swalMessage('error', $e->getMessage());
            return back()->withInput();
        }
    }

    public function destroy($encryptedId)
    {
        DB::beginTransaction();
        try {
            $invoice = Invoice::with('payments')->findOrFail($this->decryptId($encryptedId));
            if ($invoice->isLocked()) throw new \Exception('This invoice has approved payment and cannot be deleted.');
            $invoice->items()->delete();
            $invoice->payments()->where('status','pending')->delete();
            $invoice->delete();
            DB::commit();
            $this->swalMessage('success', 'Invoice deleted successfully.');
            return redirect()->route('sales.invoices.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->swalMessage('error', $e->getMessage());
            return back();
        }
    }

    public function edit($encryptedId)
    {
        $invoice = Invoice::with(['items', 'payments'])->findOrFail($this->decryptId($encryptedId));

        if ($invoice->isLocked()) {
            $this->swalMessage('warning', 'This invoice has approved payment and cannot be edited.');
            return redirect()->route('sales.invoice.view', $this->encryptId($invoice->id));
        }

        return redirect()->route('sales.invoices.index')->with('edit_invoice_id', $this->encryptId($invoice->id));
    }

    public function update(Request $request, $encryptedId)
    {
        $invoice = Invoice::with('payments')->findOrFail($this->decryptId($encryptedId));

        if ($invoice->isLocked()) {
            $this->swalMessage('warning', 'This invoice has approved payment and cannot be updated.');
            return back();
        }

        $this->swalMessage('info', 'Invoice update form is not enabled in this workflow. Delete and recreate pending invoice if needed.');
        return redirect()->route('sales.invoice.view', $this->encryptId($invoice->id));
    }
    public function payments(Request $request)
    {
        $query = CustomerPayment::with([
            'invoice.items',
            'invoice.company',
            'invoice.businessUnit',
            'invoice.workPoint',
            'customer',
            'company',
            'businessUnit',
            'workPoint',
            'paymentAccount',
        ]);

        $selectedCompanyId = null;
        $selectedUnitId = null;
        $selectedWorkPointId = null;

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        if ($request->filled('company_id')) {
            $selectedCompanyId = $this->decryptId($request->company_id);

            $query->where(function ($q) use ($selectedCompanyId) {
                $q->whereHas('invoice', function ($invoiceQuery) use ($selectedCompanyId) {
                    $invoiceQuery->where('company_id', $selectedCompanyId);
                })->orWhere(function ($fallback) use ($selectedCompanyId) {
                    $fallback->whereNull('invoice_id')->where('company_id', $selectedCompanyId);
                });
            });
        }

        if ($request->filled('business_unit_id')) {
            $selectedUnitId = $this->decryptId($request->business_unit_id);

            $query->where(function ($q) use ($selectedUnitId) {
                $q->whereHas('invoice', function ($invoiceQuery) use ($selectedUnitId) {
                    $invoiceQuery->where('business_unit_id', $selectedUnitId);
                })->orWhere(function ($fallback) use ($selectedUnitId) {
                    $fallback->whereNull('invoice_id')->where('business_unit_id', $selectedUnitId);
                });
            });
        }

        if ($request->filled('work_point_id')) {
            $selectedWorkPointId = $this->decryptId($request->work_point_id);

            $query->where(function ($q) use ($selectedWorkPointId) {
                $q->whereHas('invoice', function ($invoiceQuery) use ($selectedWorkPointId) {
                    $invoiceQuery->where('work_point_id', $selectedWorkPointId);
                })->orWhere(function ($fallback) use ($selectedWorkPointId) {
                    $fallback->whereNull('invoice_id')->where('work_point_id', $selectedWorkPointId);
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_id')) {
            $productId = $this->decryptId($request->product_id);

            $query->whereHas('invoice.items', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        $payments = $query->latest()->get();

        $companies = CompanySite::orderBy('company_name')->get();

        $companyUnits = Company_unit::when($selectedCompanyId, function ($q) use ($selectedCompanyId) {
                $q->where('company_id', $selectedCompanyId);
            })
            ->orderBy('unit_name')
            ->get();

        $workPoints = WorkPoint::when($selectedUnitId, function ($q) use ($selectedUnitId) {
                $q->where('comp_unit_id', $selectedUnitId);
            })
            ->orderBy('work_name')
            ->get();

        $products = Product::where('status', 'Active')->orderBy('product_name')->get();

        $selectedCompany = $selectedCompanyId ? CompanySite::find($selectedCompanyId) : null;
        $selectedUnit = $selectedUnitId ? Company_unit::find($selectedUnitId) : null;
        $selectedWorkPoint = $selectedWorkPointId ? WorkPoint::find($selectedWorkPointId) : null;

        return view('admin.store.sales_management.payments', compact(
            'payments',
            'companies',
            'companyUnits',
            'workPoints',
            'products',
            'selectedCompanyId',
            'selectedUnitId',
            'selectedWorkPointId',
            'selectedCompany',
            'selectedUnit',
            'selectedWorkPoint'
        ));
    }

    public function ajaxCompanyUnits($encryptedCompanyId)
    {
        $companyId = $this->decryptId($encryptedCompanyId);

        return response()->json(
            Company_unit::where('company_id', $companyId)
                ->orderBy('unit_name')
                ->get()
                ->map(function ($unit) {
                    return [
                        'id' => $this->encryptId($unit->id),
                        'unit_code' => $unit->unit_code,
                        'unit_name' => $unit->unit_name,
                        'text' => trim(($unit->unit_code ? $unit->unit_code . ' - ' : '') . $unit->unit_name),
                    ];
                })
                ->values()
        );
    }

    public function ajaxUnitWorkPoints($encryptedUnitId)
    {
        $unitId = $this->decryptId($encryptedUnitId);

        return response()->json(
            WorkPoint::where('comp_unit_id', $unitId)
                ->orderBy('work_name')
                ->get()
                ->map(function ($workPoint) {
                    return [
                        'id' => $this->encryptId($workPoint->id),
                        'work_code' => $workPoint->work_code,
                        'work_name' => $workPoint->work_name,
                        'text' => trim(($workPoint->work_code ? $workPoint->work_code . ' - ' : '') . $workPoint->work_name),
                    ];
                })
                ->values()
        );
    }

    public function verifyPayment(Request $request, $encryptedId)
    {
        DB::beginTransaction();
        try {
            $payment = CustomerPayment::with(['invoice.items.product','invoice.proforma','proforma'])->lockForUpdate()->findOrFail($this->decryptId($encryptedId));
            if ($payment->status === 'approved') throw new \Exception('This payment is already approved.');
            if ($payment->status !== 'pending') throw new \Exception('Only pending payments can be approved.');
            if (!$payment->invoice) throw new \Exception('Payment invoice not found.');

            $invoice = Invoice::with(['items.product','proforma'])->lockForUpdate()->findOrFail($payment->invoice_id);
            $group = $this->postPaymentAccounting($invoice, $payment);
            $this->applyApprovedPaymentToInvoiceAndProforma($invoice, $payment);
            $this->decreaseStockOnPaymentApproval($invoice);

            $payment->update([
                'status' => 'approved',
                'transaction_group' => $group,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'approval_comment' => $request->approval_comment,
                'locked' => true,
            ]);

            $invoice->update(['locked' => true]);
            DB::commit();
            $this->swalMessage('success', 'Payment approved, accounting posted, and stock deducted.');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->swalMessage('error', $e->getMessage());
            return back();
        }
    }

    public function deletePayment($encryptedId)
    {
        DB::beginTransaction();
        try {
            $payment = CustomerPayment::findOrFail($this->decryptId($encryptedId));
            if ($payment->status === 'approved' || $payment->locked) throw new \Exception('Approved payment cannot be deleted.');
            $payment->delete();
            DB::commit();
            $this->swalMessage('success', 'Pending payment deleted.');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->swalMessage('error', $e->getMessage());
            return back();
        }
    }

    public function printPayment($encryptedId)
    {
        $payment = CustomerPayment::with('invoice')->findOrFail($this->decryptId($encryptedId));

        if (!$payment->invoice) {
            $this->swalMessage('error', 'Payment invoice was not found.');
            return back();
        }

        return redirect()->route('sales.invoice.print', $this->encryptId($payment->invoice_id));
    }

    public function deliveries()
    {
        $deliveries = Delivery::with([
            'customer',
            'invoice.customer',
            'invoice.company',
            'invoice.businessUnit',
            'invoice.workPoint',
            'proforma.customer',
            'items.product',
            'paymentAccount',
            'serviceIncomeAccount',
            'company',
            'businessUnit',
            'workPoint',
        ])->latest()->get();

        $customers = Customer::orderBy('customer_name')->get();
        $companies = CompanySite::orderBy('company_name')->get();
        $companyUnits = Company_unit::orderBy('unit_name')->get();
        $workPoints = WorkPoint::orderBy('work_name')->get();
        $products = Product::where('status', 'Active')->orderBy('product_name')->get();

        $invoices = Invoice::with(['customer', 'company', 'businessUnit', 'workPoint', 'items'])
            ->whereIn('payment_status', ['unpaid', 'partial', 'paid'])
            ->latest()
            ->get();

        $proformas = Proforma::with(['customer', 'company', 'businessUnit', 'workPoint', 'items'])
            ->whereIn('status', ['approved', 'converted'])
            ->latest()
            ->get();

        $paymentAccounts = $this->paymentAccounts();
        $serviceIncomeAccounts = $this->serviceIncomeAccounts();

        return view('admin.store.sales_management.deliveries', compact(
            'deliveries',
            'customers',
            'companies',
            'companyUnits',
            'workPoints',
            'products',
            'invoices',
            'proformas',
            'paymentAccounts',
            'serviceIncomeAccounts'
        ));
    }

    public function storeDelivery(Request $request)
    {
        $this->decryptRequestIds($request);

        $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'proforma_id' => 'nullable|exists:proformas,id',
            'customer_id' => 'required_without_all:invoice_id,proforma_id|exists:customers,id',
            'company_id' => 'required_without_all:invoice_id,proforma_id|exists:company_sites,id',
            'business_unit_id' => 'nullable|exists:company_units,id',
            'work_point_id' => 'required_without_all:invoice_id,proforma_id|exists:work_points,id',
            'delivery_date' => 'required|date',
            'transport_owner' => 'nullable|in:company,customer',
            'items' => 'required_without_all:invoice_id,proforma_id|array',
            'waybill_amount' => 'nullable|numeric|min:0',
            'delivery_income_amount' => 'nullable|numeric|min:0',
            'waybill_payment_account_id' => 'nullable|exists:accnt_subcharts,id',
            'delivery_payment_account_id' => 'nullable|exists:accnt_subcharts,id',
            'waybill_service_income_account_id' => 'nullable|exists:accnt_subcharts,id',
            'delivery_service_income_account_id' => 'nullable|exists:accnt_subcharts,id',
            'export_reference_no' => 'nullable|string|max:150',
            'transporter_name' => 'nullable|string|max:255',
            'clearing_agent' => 'nullable|string|max:255',
            'bill_of_entry_no' => 'nullable|string|max:150',
            'exit_entry_no' => 'nullable|string|max:150',
            'truck2_registration_no' => 'nullable|string|max:100',
            'trailer_registration_no' => 'nullable|string|max:100',
            'container2_no' => 'nullable|string|max:100',
            'container3_no' => 'nullable|string|max:100',
            'total_gross_weight' => 'nullable|string|max:100',
            'items.*.packages_no_type' => 'nullable|string|max:150',
            'items.*.gross_weight' => 'nullable|string|max:150',
        ]);

        DB::beginTransaction();

        try {
            $source = $this->resolveDeliverySource($request);

            /*
            |--------------------------------------------------------------------------
            | Delivery items rule
            |--------------------------------------------------------------------------
            | If delivery comes from invoice/proforma, items must be exactly the source
            | items. Do not accept additional items from the form. Manual items are only
            | allowed when both invoice_id and proforma_id are empty.
            */
            $items = $source['has_source']
                ? $source['items']
                : $this->prepareDeliveryItems($request->items ?? []);

            // Keep source invoice/proforma quantities protected, but allow packing-list
            // and manifest details such as packages and gross weight to be entered per row.
            if ($source['has_source'] && is_array($request->items ?? null)) {
                $requestItems = array_values($request->items ?? []);

                foreach ($items as $index => $sourceItem) {
                    $extra = $requestItems[$index] ?? [];

                    $items[$index]['packages_no_type'] = $extra['packages_no_type'] ?? null;
                    $items[$index]['gross_weight'] = $extra['gross_weight'] ?? null;
                }
            }

            if (count($items) < 1) {
                throw new \Exception('Please add delivery items.');
            }

            $transportOwner = $request->transport_owner ?: 'company';

            /*
            |--------------------------------------------------------------------------
            | Waybill income rule
            |--------------------------------------------------------------------------
            | If customer uses own transport, no waybill income section is required.
            | If company transport is used and waybill amount is entered, accounting will
            | be posted on delivery approval.
            */
            $waybillAmount = $transportOwner === 'customer'
                ? 0
                : (float) ($request->waybill_amount ?? $request->delivery_income_amount ?? 0);

            if ($waybillAmount > 0) {
                if (empty($request->delivery_payment_account_id)) {
                    throw new \Exception('Please select Bank / Cash / Mobile account for waybill income.');
                }

                if (empty($request->delivery_service_income_account_id)) {
                    throw new \Exception('Please select service income account for waybill income.');
                }
            }

            $documentDate = $request->delivery_date ?: now()->toDateString();
            $deliveryNoteNo = $this->generateDeliveryDocumentNo('DLN', $documentDate);
            $waybillNo = $this->generateDeliveryDocumentNo('WBL', $documentDate);
            $customsManifestNo = $this->generateDeliveryDocumentNo('CRM', $documentDate);

            $delivery = Delivery::create([
                'invoice_id' => $source['invoice_id'],
                'proforma_id' => $source['proforma_id'],
                'customer_id' => $source['customer_id'],
                'company_id' => $source['company_id'],
                'business_unit_id' => $source['business_unit_id'],
                'work_point_id' => $source['work_point_id'],

                // Delivery number and delivery note number use the same DLN document number.
                'delivery_no' => $deliveryNoteNo,
                'delivery_note_no' => $deliveryNoteNo,
                'waybill_no' => $waybillNo,
                'delivery_date' => $documentDate,

                'delivery_type' => $request->delivery_type ?? 'local',
                'tracking_no' => $request->tracking_no,
                'transport_owner' => $transportOwner,
                'transport_mode' => $request->transport_mode,
                'driver_name' => $request->driver_name,
                'driver_phone' => $request->driver_phone,
                'vehicle_no' => $request->vehicle_no,
                'container_no' => $request->container_no,
                'origin' => $request->origin,
                'destination' => $request->destination,
                'dispatch_date' => $request->dispatch_date,
                'expected_delivery_date' => $request->expected_delivery_date,


                'permit_no' => $request->permit_no,
                'storage_type' => $request->storage_type,
                'approved_qty' => $request->approved_qty,
                'safety_officer' => $request->safety_officer,
                'escort_officer' => $request->escort_officer,
                'authority' => $request->authority,

                'delivery_income_amount' => $waybillAmount,
                'delivery_income_currency' => $transportOwner === 'customer' ? 'TZS' : ($request->waybill_currency ?? $request->delivery_income_currency ?? 'TZS'),
                'delivery_income_exchange_rate' => $transportOwner === 'customer' ? 1 : ($request->waybill_exchange_rate ?? $request->delivery_income_exchange_rate ?? 1),
                'delivery_payment_method' => $transportOwner === 'customer' ? null : ($request->waybill_payment_method ?? $request->delivery_payment_method),
                'delivery_payment_account_id' => $transportOwner === 'customer' ? null : $request->delivery_payment_account_id,
                'delivery_service_income_account_id' => $transportOwner === 'customer' ? null : $request->delivery_service_income_account_id,

                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Save newly added packing-list and customs-manifest fields without
            // requiring changes to your existing Delivery model fillable array.
            $delivery->forceFill([
                'customs_manifest_no' => $customsManifestNo,
                'export_reference_no' => $request->export_reference_no,
                'transporter_name' => $request->transporter_name,
                'clearing_agent' => $request->clearing_agent,
                'bill_of_entry_no' => $request->bill_of_entry_no,
                'exit_entry_no' => $request->exit_entry_no,
                'truck2_registration_no' => $request->truck2_registration_no,
                'trailer_registration_no' => $request->trailer_registration_no,
                'container2_no' => $request->container2_no,
                'container3_no' => $request->container3_no,
                'total_gross_weight' => $request->total_gross_weight,
            ])->save();

            foreach ($items as $item) {
                $deliveryItem = DeliveryItem::create([
                    'delivery_id' => $delivery->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'quantity' => $item['quantity'] ?? 0,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'total' => $item['total'] ?? 0,
                    'issued_qty' => $item['issued_qty'] ?? ($item['quantity'] ?? 0),
                ]);

                // Save added item-level packing/manifest fields without changing
                // the existing DeliveryItem model fillable array.
                $deliveryItem->forceFill([
                    'packages_no_type' => $item['packages_no_type'] ?? null,
                    'gross_weight' => $item['gross_weight'] ?? null,
                ])->save();
            }

            DB::commit();

            $this->swalMessage('success', 'Delivery note and waybill created successfully.');
            return redirect()->route('sales.deliveries');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->swalMessage('error', $e->getMessage());
            return back()
                ->withErrors(['delivery_submit' => $e->getMessage()])
                ->withInput();
        }
    }


    public function editDelivery($encryptedId)
    {
        $delivery = Delivery::findOrFail($this->decryptId($encryptedId));

        if ($delivery->isClosed() || strtolower($delivery->approval_status ?? '') === 'approved') {
            $this->swalMessage('warning', 'Approved or closed delivery cannot be edited.');
            return redirect()->route('sales.deliveries');
        }

        return redirect()->route('sales.deliveries')->with('edit_delivery_id', $this->encryptId($delivery->id));
    }

    public function updateDelivery(Request $request, $encryptedId)
    {
        $delivery = Delivery::findOrFail($this->decryptId($encryptedId));

        if ($delivery->isClosed() || strtolower($delivery->approval_status ?? '') === 'approved') {
            $this->swalMessage('warning', 'Approved or closed delivery cannot be updated.');
            return back();
        }

        $this->swalMessage('info', 'Delivery update form is not enabled in this workflow. Delete and recreate pending delivery if needed.');
        return redirect()->route('sales.deliveries');
    }

    public function approveDelivery(Request $request, $encryptedId)
    {
        DB::beginTransaction();
        try {
            $delivery = Delivery::with(['items.product','customer'])->lockForUpdate()->findOrFail($this->decryptId($encryptedId));
            if ($delivery->approval_status === 'approved') throw new \Exception('Delivery already approved.');
            if ($delivery->isClosed()) throw new \Exception('Closed delivery cannot be approved again.');

            $group = null;
            if ((float)$delivery->delivery_income_amount > 0) {
                $group = $this->postDeliveryIncomeAccounting($delivery);
            }

            $delivery->update([
                'status' => 'approved',
                'approval_status' => 'approved',
                'delivery_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'approval_comment' => $request->approval_comment,
                'delivery_income_transaction_group' => $group,
            ]);

            DB::commit();
            $this->swalMessage('success', 'Delivery approved successfully. Delivery income posted if amount was entered.');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->swalMessage('error', $e->getMessage());
            return back();
        }
    }

    public function acceptDelivery(Request $request, $encryptedId)
    {
        DB::beginTransaction();
        try {
            $delivery = Delivery::findOrFail($this->decryptId($encryptedId));
            if ($delivery->approval_status !== 'approved') throw new \Exception('Only approved deliveries can be accepted/closed.');
            $delivery->update([
                'status' => 'closed',
                'delivery_status' => 'closed',
                'receiver_name' => $request->receiver_name,
                'receiver_signature' => $request->receiver_signature,
                'delivered_at' => now(),
                'customer_accepted_at' => now(),
                'customer_accepted_by' => auth()->id(),
                'locked' => true,
            ]);
            DB::commit();
            $this->swalMessage('success', 'Delivery accepted by customer and closed.');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->swalMessage('error', $e->getMessage());
            return back();
        }
    }

    public function deleteDelivery($encryptedId)
    {
        DB::beginTransaction();
        try {
            $delivery = Delivery::findOrFail($this->decryptId($encryptedId));
            if ($delivery->isClosed() || $delivery->approval_status === 'approved') throw new \Exception('Approved/closed delivery cannot be deleted.');
            $delivery->items()->delete();
            $delivery->delete();
            DB::commit();
            $this->swalMessage('success', 'Delivery deleted.');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->swalMessage('error', $e->getMessage());
            return back();
        }
    }

    public function deliveryNote($encryptedId)
    {
        $delivery = Delivery::with([
            'invoice.customer',
            'invoice.company',
            'invoice.businessUnit',
            'invoice.workPoint',
            'proforma.customer',
            'proforma.company',
            'proforma.businessUnit',
            'proforma.workPoint',
            'customer',
            'items.product',
            'company',
            'businessUnit',
            'workPoint',
            'approver',
            'paymentAccount',
            'serviceIncomeAccount',
        ])->findOrFail($this->decryptId($encryptedId));

        return view('admin.store.sales_management.delivery_note_view', compact('delivery'));
    }

    public function waybill($encryptedId)
    {
        $delivery = Delivery::with([
            'invoice.customer',
            'invoice.company',
            'invoice.businessUnit',
            'invoice.workPoint',
            'proforma.customer',
            'proforma.company',
            'proforma.businessUnit',
            'proforma.workPoint',
            'customer',
            'items.product',
            'company',
            'businessUnit',
            'workPoint',
            'approver',
            'paymentAccount',
            'serviceIncomeAccount',
        ])->findOrFail($this->decryptId($encryptedId));

        return view('admin.store.sales_management.waybill_view', compact('delivery'));
    }

    public function packingList($encryptedId)
    {
        $delivery = Delivery::with([
            'invoice.customer',
            'invoice.company',
            'invoice.businessUnit',
            'invoice.workPoint',
            'proforma.customer',
            'proforma.company',
            'proforma.businessUnit',
            'proforma.workPoint',
            'customer',
            'items.product',
            'company',
            'businessUnit',
            'workPoint',
            'creator',
            'approver',
        ])->findOrFail($this->decryptId($encryptedId));

        return view('admin.store.sales_management.delivery_packing_list', compact('delivery'));
    }

    public function customsRoadManifest($encryptedId)
    {
        $delivery = Delivery::with([
            'invoice.customer',
            'invoice.company',
            'invoice.businessUnit',
            'invoice.workPoint',
            'proforma.customer',
            'proforma.company',
            'proforma.businessUnit',
            'proforma.workPoint',
            'customer',
            'items.product',
            'company',
            'businessUnit',
            'workPoint',
            'creator',
            'approver',
        ])->findOrFail($this->decryptId($encryptedId));

        return view('admin.store.sales_management.customs_road_manifest', compact('delivery'));
    }

    public function ajaxDeliveryInvoice($encryptedInvoiceId)
    {
        $invoice = Invoice::with([
            'customer',
            'company',
            'businessUnit',
            'workPoint',
            'items.product',
            'items.service',
        ])->findOrFail($this->decryptId($encryptedInvoiceId));

        return response()->json([
            'invoice' => [
                'id' => $this->encryptId($invoice->id),
                'invoice_no' => $invoice->invoice_no,

                'customer_id' => $this->encryptId($invoice->customer_id),
                'customer_name' => trim((optional($invoice->customer)->customer_code ? optional($invoice->customer)->customer_code . ' - ' : '') . optional($invoice->customer)->customer_name),

                'company_id' => $this->encryptId($invoice->company_id),
                'company_text' => trim((optional($invoice->company)->company_code ? optional($invoice->company)->company_code . ' - ' : '') . optional($invoice->company)->company_name),

                'business_unit_id' => $invoice->business_unit_id ? $this->encryptId($invoice->business_unit_id) : '',
                'business_unit_text' => trim((optional($invoice->businessUnit)->unit_code ? optional($invoice->businessUnit)->unit_code . ' - ' : '') . optional($invoice->businessUnit)->unit_name),

                'work_point_id' => $invoice->work_point_id ? $this->encryptId($invoice->work_point_id) : '',
                'work_point_text' => trim((optional($invoice->workPoint)->work_code ? optional($invoice->workPoint)->work_code . ' - ' : '') . optional($invoice->workPoint)->work_name),

                'origin' => optional($invoice->workPoint)->work_name ?? optional($invoice->workPoint)->location,
                'destination' => optional($invoice->customer)->address,
            ],
            'items' => $invoice->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id ? $this->encryptId($item->product_id) : '',
                    'item_name' => $item->product_name,
                    'unit' => $item->unit ?? 'pcs',
                    'quantity' => (float) $item->qty,
                    'unit_price' => (float) $item->price,
                    'total' => (float) $item->total,
                ];
            })->values(),
        ]);
    }

    public function ajaxDeliveryProforma($encryptedProformaId)
    {
        $proforma = Proforma::with([
            'customer',
            'company',
            'businessUnit',
            'workPoint',
            'items.product',
            'items.service',
        ])->findOrFail($this->decryptId($encryptedProformaId));

        return response()->json([
            'proforma' => [
                'id' => $this->encryptId($proforma->id),
                'proforma_no' => $proforma->proforma_no,

                'customer_id' => $this->encryptId($proforma->customer_id),
                'customer_name' => trim((optional($proforma->customer)->customer_code ? optional($proforma->customer)->customer_code . ' - ' : '') . optional($proforma->customer)->customer_name),

                'company_id' => $this->encryptId($proforma->company_id),
                'company_text' => trim((optional($proforma->company)->company_code ? optional($proforma->company)->company_code . ' - ' : '') . optional($proforma->company)->company_name),

                'business_unit_id' => $proforma->business_unit_id ? $this->encryptId($proforma->business_unit_id) : '',
                'business_unit_text' => trim((optional($proforma->businessUnit)->unit_code ? optional($proforma->businessUnit)->unit_code . ' - ' : '') . optional($proforma->businessUnit)->unit_name),

                'work_point_id' => $proforma->work_point_id ? $this->encryptId($proforma->work_point_id) : '',
                'work_point_text' => trim((optional($proforma->workPoint)->work_code ? optional($proforma->workPoint)->work_code . ' - ' : '') . optional($proforma->workPoint)->work_name),

                'origin' => optional($proforma->workPoint)->work_name ?? optional($proforma->workPoint)->location,
                'destination' => optional($proforma->customer)->address,
            ],
            'items' => $proforma->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id ? $this->encryptId($item->product_id) : '',
                    'item_name' => $item->item_name,
                    'unit' => $item->unit ?? 'pcs',
                    'quantity' => (float) $item->qty,
                    'unit_price' => (float) $item->price,
                    'total' => (float) $item->total,
                ];
            })->values(),
        ]);
    }

    public function getProformaDetails($encryptedId)
    {
        $proforma = Proforma::with([
            'items.product',
            'items.service',
            'customer',
            'bank',
            'company',
            'businessUnit',
            'workPoint'
        ])->findOrFail($this->decryptId($encryptedId));

        if (
            !in_array(strtolower($proforma->status), ['approved', 'converted']) ||
            strtolower($proforma->payment_status ?? 'unpaid') === 'paid' ||
            (float) $proforma->paid_amount >= (float) $proforma->total
        ) {
            return response()->json([
                'error' => 'This proforma is not available for invoicing/payment.'
            ], 422);
        }

        return response()->json([
            'proforma' => [
                'id' => $this->encryptId($proforma->id),
                'proforma_no' => $proforma->proforma_no,

                'customer_id' => $this->encryptId($proforma->customer_id),
                'customer_name' => optional($proforma->customer)->customer_name,
                'customer_code' => optional($proforma->customer)->customer_code,
                'customer_phone' => optional($proforma->customer)->phone,
                'customer_address' => optional($proforma->customer)->address,
                'customer_tin' => optional($proforma->customer)->tin_number ?? optional($proforma->customer)->tin,

                'company_id' => $this->encryptId($proforma->company_id),
                'company_code' => optional($proforma->company)->company_code,
                'company_name' => optional($proforma->company)->company_name,

                'business_unit_id' => $this->encryptId($proforma->business_unit_id),
                'business_unit_code' => optional($proforma->businessUnit)->unit_code,
                'business_unit_name' => optional($proforma->businessUnit)->unit_name,

                'work_point_id' => $this->encryptId($proforma->work_point_id),
                'work_point_code' => optional($proforma->workPoint)->work_code,
                'work_point_name' => optional($proforma->workPoint)->work_name,

                'invoice_type' => $proforma->invoice_type,
                'subtotal' => (float) $proforma->subtotal,
                'vat' => (float) $proforma->vat,
                'total' => (float) $proforma->total,
                'vat_rate' => (float) $proforma->subtotal > 0
                    ? round(((float) $proforma->vat / (float) $proforma->subtotal) * 100, 2)
                    : 0,
                'paid_amount' => (float) $proforma->paid_amount,
                'balance' => max(0, (float) $proforma->total - (float) $proforma->paid_amount),

                'bank_id' => $proforma->bank_id ? $this->encryptId($proforma->bank_id) : null,
                'bank_name' => optional($proforma->bank)->SubDescription,
                'bank_code' => optional($proforma->bank)->SubCode,
                'account_number' => $proforma->account_number,
                'swift_code' => $proforma->swift_code,
                'branch' => $proforma->branch,
            ],
            'items' => $proforma->items->map(function ($item) {
                return [
                    'item_type' => $item->item_type,
                    'product_id' => $item->product_id ? $this->encryptId($item->product_id) : null,
                    'service_id' => $item->service_id ? $this->encryptId($item->service_id) : null,
                    'product_name' => $item->item_name,
                    'description' => $item->description,
                    'qty' => (float) $item->qty,
                    'unit' => $item->unit,
                    'price' => (float) $item->price,
                    'total' => (float) $item->total,
                ];
            })->values(),
        ]);
    }

    public function getProducts($encryptedCompanyId, $encryptedBusinessUnitId, $encryptedWorkPointId)
    {
        $companyId = $this->decryptId($encryptedCompanyId);
        $businessUnitId = ($encryptedBusinessUnitId === '0' || $encryptedBusinessUnitId === '')
            ? null
            : $this->decryptId($encryptedBusinessUnitId);
        $workPointId = $this->decryptId($encryptedWorkPointId);

        $query = Product::query();

        if (Schema::hasColumn('products', 'company_id')) {
            $query->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            });
        }

        if ($businessUnitId) {
            $query->where(function ($q) use ($businessUnitId) {
                if (Schema::hasColumn('products', 'comp_unit_id')) {
                    $q->orWhere('comp_unit_id', $businessUnitId)->orWhereNull('comp_unit_id');
                }
                if (Schema::hasColumn('products', 'business_unit_id')) {
                    $q->orWhere('business_unit_id', $businessUnitId)->orWhereNull('business_unit_id');
                }
            });
        }

        if ($workPointId && Schema::hasColumn('products', 'work_point_id')) {
            $query->where(function ($q) use ($workPointId) {
                $q->where('work_point_id', $workPointId)->orWhereNull('work_point_id');
            });
        }

        if (Schema::hasColumn('products', 'status')) {
            $query->where('status', 'Active');
        }

        return response()->json(
            $query->orderBy('product_name')->get()->map(function ($p) {
                return [
                    'id' => $this->encryptId($p->id),
                    'product_name' => $p->product_name,
                    'unit' => $p->unit ?? $p->product_size ?? 'pcs',
                    'selling_price' => $p->selling_price ?? $p->avg_cost ?? 0,
                    'avg_cost' => $p->avg_cost ?? 0,
                    'revenue_account_code' => $p->revenue_account_code ?? null,
                    'inventory_account_code' => $p->inventory_account_code ?? null,
                    'cogs_account_code' => $p->cogs_account_code ?? null,
                    'total_qty' => $p->total_qty ?? 0,
                ];
            })->values()
        );
    }

    public function getServices($encryptedCompanyId, $encryptedBusinessUnitId, $encryptedWorkPointId)
    {
        $companyId = $this->decryptId($encryptedCompanyId);
        $businessUnitId = ($encryptedBusinessUnitId === '0' || $encryptedBusinessUnitId === '')
            ? null
            : $this->decryptId($encryptedBusinessUnitId);
        $workPointId = $this->decryptId($encryptedWorkPointId);

        $query = Service::query();

        if (Schema::hasColumn('services', 'company_id')) {
            $query->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            });
        }

        if ($businessUnitId && Schema::hasColumn('services', 'business_unit_id')) {
            $query->where(function ($q) use ($businessUnitId) {
                $q->where('business_unit_id', $businessUnitId)->orWhereNull('business_unit_id');
            });
        }

        if ($workPointId && Schema::hasColumn('services', 'work_point_id')) {
            $query->where(function ($q) use ($workPointId) {
                $q->where('work_point_id', $workPointId)->orWhereNull('work_point_id');
            });
        }

        if (Schema::hasColumn('services', 'status')) {
            $query->where('status', 'Active');
        }

        return response()->json(
            $query->orderBy('service_name')->get()->map(function ($s) {
                return [
                    'id' => $this->encryptId($s->id),
                    'service_name' => $s->service_name,
                    'unit' => $s->unit ?? 'service',
                    'price' => $s->price ?? 0,
                ];
            })->values()
        );
    }

    public function getPaymentAccounts(Request $request, $method)
    {
        $companyId = $request->filled('company_id') ? $this->decryptId($request->query('company_id')) : null;
        $workPointId = $request->filled('work_point_id') ? $this->decryptId($request->query('work_point_id')) : null;
        return response()->json($this->paymentAccounts($method, $companyId, $workPointId)->map(function($a){
            return ['id'=>$this->encryptId($a->id),'SubCode'=>$a->SubCode,'SubDescription'=>$a->SubDescription,'label'=>$a->SubCode.' - '.$a->SubDescription];
        }));
    }

    // ================= HELPERS =================
    private function createPendingPayment(Invoice $invoice, $proforma, $amount, Request $request)
    {
        return CustomerPayment::create([
            'payment_no' => $this->generatePaymentNo(),
            'payment_date' => now()->toDateString(),
            'invoice_id' => $invoice->id,
            'proforma_id' => optional($proforma)->id,
            'customer_id' => $invoice->customer_id,
            'company_id' => $invoice->company_id,
            'business_unit_id' => $invoice->business_unit_id,
            'work_point_id' => $invoice->work_point_id,
            'amount' => $amount,
            'currency' => $invoice->currency,
            'exchange_rate' => $invoice->exchange_rate,
            'payment_method' => $request->payment_method,
            'payment_account_id' => $request->payment_account_id,
            'receipt_no' => $request->receipt_no,
            'receipt_attachment' => $this->storePublicFile($request, 'receipt_attachment', 'customer_receipts'),
            'notes' => $request->payment_notes,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);
    }

    private function applyApprovedPaymentToInvoiceAndProforma(Invoice $invoice, CustomerPayment $payment)
    {
        $paid = min((float)$invoice->total, (float)$invoice->paid_amount + (float)$payment->amount);
        $balance = max(0, (float)$invoice->total - $paid);
        $status = $balance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
        $invoice->update(['paid_amount'=>$paid,'balance'=>$balance,'status'=>$status,'payment_status'=>$status]);

        if ($invoice->proforma) {
            $p = $invoice->proforma;
            $newPaid = min((float)$p->total, (float)$p->paid_amount + (float)$payment->amount);
            $p->update(['paid_amount'=>$newPaid,'payment_status'=>$newPaid >= (float)$p->total ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid')]);
        }
    }

    private function postPaymentAccounting(Invoice $invoice, CustomerPayment $payment)
    {
        $group = (string) Str::uuid();
        $paymentSub = AccntSubchart::find($payment->payment_account_id);
        if (!$paymentSub) throw new \Exception('Payment account is required before approval.');
        $paymentChart = $this->chartFromSubAccount($paymentSub);
        if (!$paymentChart) throw new \Exception('Payment chart account is not mapped correctly.');
        $payee = optional($invoice->customer)->customer_name ?? 'Customer';
        $memo = 'Approved customer payment '.$payment->payment_no.' for invoice '.$invoice->invoice_no;

        AccntTransaction::create($this->transactionPayload($group, $invoice->company_id, $invoice->work_point_id, $paymentChart, $paymentSub, 'debit', $payment->amount, $memo, $payee, $payment->payment_no.'-DR-PAYMENT', $payment->currency, $payment->exchange_rate, $payment->payment_method));

        if ($invoice->proforma_id) {
            $customerSub = $this->customerReceivableSubAccount($invoice);
            if (!$customerSub) throw new \Exception('Customer receivable account is not mapped.');
            $customerChart = $this->chartFromSubAccount($customerSub);
            AccntTransaction::create($this->transactionPayload($group, $invoice->company_id, $invoice->work_point_id, $customerChart, $customerSub, 'credit', $payment->amount, $memo.' | Clear receivable', $payee, $payment->payment_no.'-CR-CUSTOMER', $payment->currency, $payment->exchange_rate, $payment->payment_method));
            return $group;
        }

        $amountBeforeVat = $invoice->total > 0 ? round($payment->amount * ($invoice->sub_total / $invoice->total), 2) : $payment->amount;
        foreach ($invoice->items as $idx => $item) {
            $lineRatio = $invoice->sub_total > 0 ? ((float)$item->total / (float)$invoice->sub_total) : 0;
            $lineAmount = round($amountBeforeVat * $lineRatio, 2);
            if ($lineAmount <= 0) continue;
            $revSub = $this->itemRevenueSubAccount($invoice, $item);
            if (!$revSub) throw new \Exception('Revenue account is not mapped for item: '.($item->product_name ?? 'item'));
            $revChart = $this->chartFromSubAccount($revSub);
            AccntTransaction::create($this->transactionPayload($group, $invoice->company_id, $invoice->work_point_id, $revChart, $revSub, 'credit', $lineAmount, $memo.' | '.$item->product_name, $payee, $payment->payment_no.'-CR-ITEM-'.($idx+1), $payment->currency, $payment->exchange_rate, $payment->payment_method));
        }

        if ($invoice->tax > 0) {
            $vatAmount = round($payment->amount - $amountBeforeVat, 2);
            if ($vatAmount > 0 && ($vatSub = $this->vatSubAccount($invoice))) {
                AccntTransaction::create($this->transactionPayload($group, $invoice->company_id, $invoice->work_point_id, $this->chartFromSubAccount($vatSub), $vatSub, 'credit', $vatAmount, $memo.' | VAT', $payee, $payment->payment_no.'-CR-VAT', $payment->currency, $payment->exchange_rate, $payment->payment_method));
            }
        }
        return $group;
    }

    private function postDeliveryIncomeAccounting(Delivery $delivery)
    {
        $paymentSub = AccntSubchart::find($delivery->delivery_payment_account_id);
        $incomeSub = AccntSubchart::find($delivery->delivery_service_income_account_id);

        if (!$paymentSub) {
            throw new \Exception('Select delivery Bank/Cash/Mobile payment account before approval.');
        }

        if (!$incomeSub) {
            throw new \Exception('Select delivery service income account before approval.');
        }

        $paymentChart = $this->chartFromSubAccount($paymentSub);
        $incomeChart = $this->chartFromSubAccount($incomeSub);

        if (!$paymentChart || !$incomeChart) {
            throw new \Exception('Delivery accounting account chart mapping is not correct.');
        }

        $group = (string) Str::uuid();
        $payee = optional($delivery->customer)->customer_name
            ?? optional(optional($delivery->invoice)->customer)->customer_name
            ?? optional(optional($delivery->proforma)->customer)->customer_name
            ?? 'Customer';

        $memo = 'Approved waybill / delivery income ' . $delivery->delivery_no;
        $amount = (float) $delivery->delivery_income_amount;
        $currency = $delivery->delivery_income_currency ?: 'TZS';
        $rate = $delivery->delivery_income_exchange_rate ?: 1;

        AccntTransaction::create($this->transactionPayload(
            $group,
            $delivery->company_id,
            $delivery->work_point_id,
            $paymentChart,
            $paymentSub,
            'debit',
            $amount,
            $memo,
            $payee,
            $delivery->delivery_no . '-DR-WAYBILL-PAYMENT',
            $currency,
            $rate,
            $delivery->delivery_payment_method
        ));

        AccntTransaction::create($this->transactionPayload(
            $group,
            $delivery->company_id,
            $delivery->work_point_id,
            $incomeChart,
            $incomeSub,
            'credit',
            $amount,
            $memo . ' | Service income',
            $payee,
            $delivery->delivery_no . '-CR-WAYBILL-SERVICE',
            $currency,
            $rate,
            $delivery->delivery_payment_method
        ));

        return $group;
    }

    private function transactionPayload($group, $companyId, $workPointId, $chart, $sub, $type, $amount, $memo, $payee, $reference, $currency='TZS', $exchangeRate=1, $paymentMethod=null)
    {
        $category = strtolower((string) $paymentMethod) === 'cash' ? 'Cash' : 'Bank';

        $sec=Section::where('secCode','=','BDD00102')->first();
        return [
            'transaction_group' => $group,
            'pcv_no' => null,
            'trans_date' => now()->toDateString(),
            'reference' => $reference,
            'check_no' => null,
            'request_no' => null,
            'requisition_id' => null,
            'category' => $category,
            'currency' => $currency,
            'exchange_rate' => $exchangeRate,
            'memo' => $memo,
            'payee' => $payee,
            'user_id' => auth()->id(),
            'company_id' => $companyId,
            'work_point_id' => $workPointId,
            'account_id' => $chart->id,
            'sub_account_id' => $sub->id,
            'department_id'=> $sec->dept_id,
            'section_id'=> $sec->id,
            'type' => $type,
            'amount' => $amount,
            'source_amount' => $amount,
            'imported_from_excel' => false,
            'Status' => 'Active',
            'verified' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_comment' => 'Auto verified from sales module',
            'approved' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_comment' => 'Auto approved from sales module',
        ];
    }

    private function decreaseStockOnPaymentApproval(Invoice $invoice)
    {
        if ($invoice->stock_posted) return;
        foreach ($invoice->items as $item) {
            if (($item->item_type ?? 'product') !== 'product' || empty($item->product_id)) continue;
            $qty = (float)$item->qty;
            $stock = ProductStock::firstOrCreate([
                'product_id'=>$item->product_id,
                'company_id'=>$invoice->company_id,
                'business_unit_id'=>$invoice->business_unit_id,
                'work_point_id'=>$invoice->work_point_id,
            ], ['current_stock'=>0,'minimum_stock'=>0]);
            $stock->decrement('current_stock', $qty);
            Product::where('id', $item->product_id)->decrement('total_qty', $qty);
        }
        $invoice->update(['stock_posted'=>true]);
    }

    private function resolveDeliverySource(Request $request)
    {
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::with('items')->findOrFail($request->invoice_id);

            return [
                'has_source' => true,
                'invoice_id' => $invoice->id,
                'proforma_id' => $invoice->proforma_id,
                'customer_id' => $invoice->customer_id,
                'company_id' => $invoice->company_id,
                'business_unit_id' => $invoice->business_unit_id,
                'work_point_id' => $invoice->work_point_id,
                'items' => $invoice->items->map(function ($i) {
                    return [
                        'product_id' => $i->product_id,
                        'item_name' => $i->product_name,
                        'unit' => $i->unit ?: 'pcs',
                        'quantity' => (float) $i->qty,
                        'unit_price' => (float) $i->price,
                        'total' => (float) $i->total,
                        'issued_qty' => (float) $i->qty,
                        'packages_no_type' => null,
                        'gross_weight' => null,
                    ];
                })->toArray(),
            ];
        }

        if ($request->filled('proforma_id')) {
            $proforma = Proforma::with('items')->findOrFail($request->proforma_id);

            return [
                'has_source' => true,
                'invoice_id' => null,
                'proforma_id' => $proforma->id,
                'customer_id' => $proforma->customer_id,
                'company_id' => $proforma->company_id,
                'business_unit_id' => $proforma->business_unit_id,
                'work_point_id' => $proforma->work_point_id,
                'items' => $proforma->items->map(function ($i) {
                    return [
                        'product_id' => $i->product_id,
                        'item_name' => $i->item_name,
                        'unit' => $i->unit ?: 'pcs',
                        'quantity' => (float) $i->qty,
                        'unit_price' => (float) $i->price,
                        'total' => (float) $i->total,
                        'issued_qty' => (float) $i->qty,
                        'packages_no_type' => null,
                        'gross_weight' => null,
                    ];
                })->toArray(),
            ];
        }

        return [
            'has_source' => false,
            'invoice_id' => null,
            'proforma_id' => null,
            'customer_id' => $request->customer_id,
            'company_id' => $request->company_id,
            'business_unit_id' => $request->business_unit_id,
            'work_point_id' => $request->work_point_id,
            'items' => [],
        ];
    }

    private function prepareDeliveryItems(array $requestItems)
    {
        $items=[];
        foreach ($requestItems as $item) {
            $productId = $item['product_id'] ?? null;
            $product = $productId ? Product::find($productId) : null;
            $name = $item['item_name'] ?? optional($product)->product_name;
            $qty = (float)($item['quantity'] ?? $item['qty'] ?? 0);
            $price = (float)($item['unit_price'] ?? $item['price'] ?? optional($product)->selling_price ?? 0);
            if ($qty <= 0 || !$name) continue;
            $items[] = [
                'product_id' => $productId,
                'item_name' => $name,
                'unit' => $item['unit'] ?? optional($product)->unit ?? 'pcs',
                'quantity' => $qty,
                'unit_price' => $price,
                'total' => $qty * $price,
                'issued_qty' => $qty,
                'packages_no_type' => $item['packages_no_type'] ?? null,
                'gross_weight' => $item['gross_weight'] ?? null,
            ];
        }
        return $items;
    }

    private function itemsFromProforma(Proforma $proforma)
    {
        return $proforma->items->map(function($item){
            return ['item_type'=>$item->item_type ?? 'product','product_id'=>$item->product_id,'service_id'=>$item->service_id,'product_name'=>$item->item_name,'description'=>$item->description,'qty'=>(float)$item->qty,'unit'=>$item->unit,'price'=>(float)$item->price,'total'=>(float)$item->total];
        })->values()->toArray();
    }

    private function prepareManualItems(array $requestItems)
    {
        $items=[];
        foreach ($requestItems as $item) {
            $type=$item['item_type'] ?? 'product';
            $productId=$type==='product' ? ($item['product_id'] ?? null) : null;
            $serviceId=$type==='service' ? ($item['service_id'] ?? null) : null;
            $name=trim($item['product_name'] ?? '');
            $description=trim($item['description'] ?? '');
            $unit=$item['unit'] ?? null;
            $qty=(float)($item['qty'] ?? 0);
            $price=(float)($item['price'] ?? 0);
            if ($type==='product' && $productId && ($product=Product::find($productId))) {
                $name=$name ?: $product->product_name; $description=$description ?: $product->product_name; $unit=$unit ?: ($product->unit ?? 'pcs'); $price=$price>0?$price:(float)($product->selling_price ?? $product->avg_cost ?? 0);
            }
            if ($type==='service' && $serviceId && ($service=Service::find($serviceId))) {
                $name=$name ?: $service->service_name; $description=$description ?: $service->service_name; $unit=$unit ?: ($service->unit ?? 'service'); $price=$price>0?$price:(float)($service->price ?? 0);
            }
            if (!$name && $description) $name=$description;
            if (!$description && $name) $description=$name;
            if ($qty<=0 || !$name) continue;
            $items[]=['item_type'=>$type,'product_id'=>$productId,'service_id'=>$serviceId,'product_name'=>$name,'description'=>$description,'qty'=>$qty,'unit'=>$unit ?: ($type==='service'?'service':'pcs'),'price'=>$price,'total'=>$qty*$price];
        }
        return $items;
    }

    private function resolvePaymentAmount($paymentType, $requestAmount, $total, $alreadyPaid=0)
    {
        $remaining=max(0,$total-$alreadyPaid);
        if ($paymentType==='credit') return 0;
        if ($paymentType==='full') return $remaining;
        return min(max(0,$requestAmount),$remaining);
    }

    private function paymentAccounts($method = null, $companyId = null, $workPointId = null)
    {
        /*
        |--------------------------------------------------------------------------
        | Payment account selector
        |--------------------------------------------------------------------------
        | Your chart structure keeps the account class in accnt_charts.AccCode
        | while the selectable account is accnt_subcharts.SubCode. Therefore do
        | not rely only on SubCode beginning with 56/57. We check both:
        |   - parent chart AccCode / AccDescription
        |   - sub account SubCode / SubDescription
        |
        | Bank   => class 56 or descriptions containing bank
        | Cash   => class 57 or descriptions containing cash/fund/petty
        | Mobile => descriptions containing mobile providers
        */
        $method = strtolower((string) $method);
        $base = AccntSubchart::query()
            ->leftJoin('accnt_charts as chart', 'chart.id', '=', 'accnt_subcharts.accnt_chart_id')
            ->select('accnt_subcharts.*')
            ->where(function ($q) {
                $q->where('accnt_subcharts.Status', 'Active')
                  ->orWhere('accnt_subcharts.Status', 'active');
            });
        if (Schema::hasColumn('accnt_subcharts', 'company_id') && $companyId) {
            $base->where(function ($q) use ($companyId) {
                $q->whereNull('accnt_subcharts.company_id')
                  ->orWhere('accnt_subcharts.company_id', $companyId);
            });
        }
        if (Schema::hasColumn('accnt_subcharts', 'work_point_id') && $workPointId) {
            $base->where(function ($q) use ($workPointId) {
                $q->whereNull('accnt_subcharts.work_point_id')
                  ->orWhere('accnt_subcharts.work_point_id', $workPointId);
            });
        }
        $applyMethod = function ($query) use ($method) {
            if ($method === 'bank') {
                $query->where(function ($q) {
                    $q->where('chart.AccCode', 'LIKE', '56%')
                      ->orWhere('accnt_subcharts.SubCode', 'LIKE', '56%')
                      ->orWhere('chart.AccDescription', 'LIKE', '%bank%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%bank%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%CRDB%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%NMB%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%NBC%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%ABSA%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%KCB%');
                });
            } elseif ($method === 'cash') {
                $query->where(function ($q) {
                    $q->where('chart.AccCode', 'LIKE', '57%')
                      ->orWhere('accnt_subcharts.SubCode', 'LIKE', '57%')
                      ->orWhere('chart.AccDescription', 'LIKE', '%cash%')
                      ->orWhere('chart.AccDescription', 'LIKE', '%fund%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%cash%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%fund%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%petty%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%office%');
                });
            } elseif ($method === 'mobile') {
                $query->where(function ($q) {
                    $q->where('accnt_subcharts.SubDescription', 'LIKE', '%mobile%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%mpesa%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%m-pesa%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%tigo%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%airtel%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%halopesa%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%halo pesa%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%t-pesa%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%selcom%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%wallet%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%network%');
                });
            } else {
                $query->where(function ($q) {
                    $q->where('chart.AccCode', 'LIKE', '56%')
                      ->orWhere('chart.AccCode', 'LIKE', '57%')
                      ->orWhere('accnt_subcharts.SubCode', 'LIKE', '56%')
                      ->orWhere('accnt_subcharts.SubCode', 'LIKE', '57%')
                      ->orWhere('chart.AccDescription', 'LIKE', '%bank%')
                      ->orWhere('chart.AccDescription', 'LIKE', '%cash%')
                      ->orWhere('chart.AccDescription', 'LIKE', '%fund%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%bank%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%cash%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%mobile%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%mpesa%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%tigo%')
                      ->orWhere('accnt_subcharts.SubDescription', 'LIKE', '%airtel%');
                });
            }
        };
        $strict = clone $base;
        $applyMethod($strict);
        $accounts = $strict->orderBy('accnt_subcharts.SubCode')->get();
        /*
        |--------------------------------------------------------------------------
        | Safety fallback
        |--------------------------------------------------------------------------
        | Some old seeded accounts may not have the expected code/description.
        | If filtering by method gives nothing, still return active subaccounts
        | from the selected company/workpoint so the user can continue.
        */
        if ($accounts->isEmpty()) {
            $fallback = clone $base;
            $accounts = $fallback->orderBy('accnt_subcharts.SubCode')->get();
        }

        return $accounts;
    }

    private function serviceIncomeAccounts($companyId = null, $workPointId = null)
    {
        $query = AccntSubchart::where('Status','Active')->whereRaw('LENGTH(SubCode)=8')
            ->where(function($q){
                $q->where('SubCode','LIKE','715%')->orWhere('SubCode','LIKE','711%')->orWhere('SubCode','LIKE','701%')
                  ->orWhere('SubDescription','LIKE','%service%')->orWhere('SubDescription','LIKE','%delivery%')->orWhere('SubDescription','LIKE','%transport%')->orWhere('SubDescription','LIKE','%income%');
            });
        if ($companyId) $query->where(fn($q)=>$q->whereNull('company_id')->orWhere('company_id',$companyId));
        if ($workPointId) $query->where(fn($q)=>$q->whereNull('work_point_id')->orWhere('work_point_id',$workPointId));
        return $query->orderBy('SubCode')->get();
    }

    private function chartFromSubAccount($sub)
    {
        if (!$sub) return null;
        if (!empty($sub->accnt_chart_id)) return AccntChart::find($sub->accnt_chart_id);
        return AccntChart::where('AccCode', substr($sub->SubCode,0,2))->first();
    }

    private function customerReceivableSubAccount($model)
    {
        $customer = Customer::find($model->customer_id);
        if ($customer && !empty($customer->account_id)) {
            $byId = AccntSubchart::where('id',$customer->account_id)->where('Status','Active')->first();
            if ($byId) return $byId;
        }
        return AccntSubchart::where('Status','Active')->whereRaw('LENGTH(SubCode)=8')->where('SubCode','LIKE','411%')->orderBy('SubCode')->first();
    }

    private function itemRevenueSubAccount($invoice, $item)
    {
        if (!empty($item->product_id) && ($product = Product::find($item->product_id)) && !empty($product->revenue_account_code)) {
            return AccntSubchart::where('SubCode',$product->revenue_account_code)->where('Status','Active')->first();
        }
        return $this->serviceIncomeAccounts($invoice->company_id, $invoice->work_point_id)->first();
    }

    private function vatSubAccount($invoice)
    {
        return AccntSubchart::where('Status', 'Active')
        ->whereRaw('LENGTH(SubCode) = 8')
        ->where(function ($q) {
            $q->where('SubCode', '14310107')
                ->orWhere('SubDescription', 'LIKE', '%Value Added Tax (VAT) on Domestic%')
                ->orWhere('SubDescription', 'LIKE', '%Output VAT%')
                ->orWhere('SubDescription', 'LIKE', '%VAT on Domestic%')
                ->orWhere('SubCode', 'LIKE', '143%');
        })
        ->orderByRaw("CASE WHEN SubCode = '14310107' THEN 0 ELSE 1 END")
        ->orderBy('SubCode')
        ->first();
    }

    private function generateInvoiceNo($company, $customer)
    {
        $count=Invoice::whereYear('created_at',now()->year)->count()+1;
        return ($company->company_code ?? 'INV').'-'.strtoupper($customer->country ?? 'TZ').'-'.str_pad($count,3,'0',STR_PAD_LEFT).'-'.now()->year;
    }

    private function generatePaymentNo()
    {
        return 'PAY-'.now()->format('Ymd').'-'.str_pad(CustomerPayment::whereDate('created_at',now()->toDateString())->count()+1,4,'0',STR_PAD_LEFT);
    }

    private function generateDeliveryNo($date = null)
    {
        return $this->generateDeliveryDocumentNo('DLN', $date ?: now()->toDateString());
    }

    private function generateDeliveryDocumentNo($prefix, $date = null)
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();
        $datePart = $date->format('Ymd');

        $prefix = strtoupper($prefix);

        $columnMap = [
            'WBL' => 'waybill_no',
            'DLN' => 'delivery_note_no',
            'CRM' => 'customs_manifest_no',
        ];

        $column = $columnMap[$prefix] ?? 'delivery_note_no';

        $last = Delivery::whereYear('delivery_date', $date->year)
            ->whereMonth('delivery_date', $date->month)
            ->where($column, 'LIKE', $prefix . '-%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value($column);

        $next = 1;

        if ($last && preg_match('/-(\d{4})$/', $last, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        do {
            $number = $prefix . '-' . $datePart . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
            $exists = Delivery::where($column, $number)->exists()
                || ($prefix === 'DLN' && Delivery::where('delivery_no', $number)->exists());
            $next++;
        } while ($exists);

        return $number;
    }

    private function storePublicFile(Request $request, $field, $folder)
    {
        if (!$request->hasFile($field)) return null;
        $path = public_path($folder);
        if (!is_dir($path)) mkdir($path, 0775, true);
        $file=$request->file($field);
        $name=time().'_'.Str::random(10).'.'.$file->getClientOriginalExtension();
        $file->move($path,$name);
        return $folder.'/'.$name;
    }

    public function documentPack($id)
    {
        
        $deliveryId = Crypt::decryptString($id);

        $delivery = Delivery::with([
            'items.product',
            'company',
            'customer',
            'invoice.payments',
            'proforma',
            'approver'
        ])->findOrFail($deliveryId);

        return view(
            'admin.store.sales_management.derivery_document_pack',
            compact('delivery')
        );
    }
        public function downloadDocumentPack($id)
    {
        $deliveryId = Crypt::decryptString($id);

        $delivery = Delivery::with([
            'items.product',
            'company',
            'customer',
            'invoice.payments.paymentAccount',
            'proforma',
            'approver',
        ])->findOrFail($deliveryId);

        $receiptPayments = collect(optional($delivery->invoice)->payments ?? [])
            ->filter(function ($p) {
                return !empty($p->receipt_attachment);
            })
            ->values();

        // Hide the inline receipt area when generating the base PDF,
        // so the final merged PDF will not have an empty page.
        $hideReceipt = true;

        $tempDir = storage_path('app/tmp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $basePdfPath = $tempDir . DIRECTORY_SEPARATOR . 'delivery_pack_' . $delivery->id . '_' . time() . '.pdf';
        // Render the current document pack blade into a PDF file.
        // IMPORTANT: the blade should wrap the receipt block with:
        // @if(!($hideReceipt ?? false)) ... @endif
        $basePdf = Pdf::loadView('admin.store.sales_management.derivery_document_pack', [
            'delivery'    => $delivery,
            'hideReceipt' => $hideReceipt,
        ])->setPaper('A4', 'portrait');

        File::put($basePdfPath, $basePdf->output());

        $merger = new Merger();
        $merger->addFile($basePdfPath);

        foreach ($receiptPayments as $payment) {
            $receiptFile = public_path($payment->receipt_attachment);

            if (File::exists($receiptFile)) {
                $merger->addFile($receiptFile);
            }
        }

        $mergedPdf = $merger->merge();

        File::delete($basePdfPath);

        return response($mergedPdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="delivery_pack_' . $delivery->delivery_no . '.pdf"',
        ]);
    }
}
