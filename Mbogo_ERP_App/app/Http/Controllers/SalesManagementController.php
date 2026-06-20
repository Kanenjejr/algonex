<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Carbon\Carbon;

// MODELS
use App\Models\Lead;
use App\Models\SalesPipeline;
use App\Models\User;
use App\Models\Followup;
use App\Models\CompanySite;
use App\Models\Communication;
use App\Models\Pipeline;
use App\Models\CustomerPayment;
use App\Models\CustomerLedger;
use App\Models\GeneralSupplyRequest;
use App\Models\CrmReport;
use App\Models\Customer;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\CampaignResponse;
use App\Models\Opportunity;
use App\Models\Activity;
use App\Models\Proforma;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\CreditNote;
use App\Models\Product;
use App\Models\PosSale;
use App\Models\CashSale;
use App\Models\StockLedger;
use App\Models\Account;
use App\Models\Company_unit;
use App\Models\Warehouse;
use App\Models\GeneralSupplyItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;
use App\Exports\CustomerLedgerExport;
use App\Models\Sale;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Company;
use App\Models\WorkPoint;
use App\Models\ItemStock;
use App\Models\SaleItem;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Ledger;
use App\Models\DeliveryItem;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Crypt;
class SalesManagementController extends Controller
{    
    // ================= SWEET ALERT HELPERS =================
    private function swalSuccess($message, $title = 'Success')
    {
        Alert::success($title, $message);
    }

    private function swalError($message, $title = 'Error')
    {
        Alert::error($title, $message);
    }

    private function swalWarning($message, $title = 'Warning')
    {
        Alert::warning($title, $message);
    }

    private function swalInfo($message, $title = 'Information')
    {
        Alert::info($title, $message);
    }

    private function swalMessage($type, $message)
    {
        if ($type === 'success') {
            $this->swalSuccess($message);
            return;
        }

        if ($type === 'error') {
            $this->swalError($message);
            return;
        }

        if ($type === 'warning') {
            $this->swalWarning($message);
            return;
        }

        $this->swalInfo($message);
    }

    // ================= GENERATE CUSTOMER CODE =================
    private function generateCustomerCode(): string
    {
        $lastCustomer = Customer::orderBy('id', 'desc')->first();

        if (!$lastCustomer || empty($lastCustomer->customer_code)) {
            return 'CUS001';
        }

        $digits = preg_replace('/\D+/', '', (string) $lastCustomer->customer_code);
        $lastNumber = (int) ($digits !== '' ? $digits : 0);
        $nextNumber = $lastNumber + 1;

        return 'CUS' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

public function dashboard()
    {
        //STATS
        $stats = [

            'customers' => Customer::count(),
            'contacts' => Contact::count(),
            'campaigns' => Campaign::count(),
            'responses' => CampaignResponse::count(),
            'opportunities' => Opportunity::count(),
            'activities' => Activity::count(),
            'proformas' => Proforma::count(),
            'deliveries' => Delivery::where('work_point_id', session('work_point_id'))->count(),
            'invoices' => Invoice::where('work_point_id', session('work_point_id'))->count(),

            'pos_sales' => PosSale::sum('total'),
            'sales_today' => Invoice::whereDate('invoice_date', today())->sum('total'),
            'sales_week' => Invoice::whereBetween('invoice_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->sum('total'),

            'sales_month' => Invoice::whereMonth('invoice_date', now()->month)->sum('total'),

            'sales_year' => Invoice::whereYear('invoice_date', now()->year)->sum('total'),

            'unpaid_invoices' => Invoice::whereIn('status', ['unpaid','partial'])->count(),
        ];
        $campaigns = Campaign::latest()->limit(10)->get();
        $responses = CampaignResponse::latest()->limit(10)->get();
        $opps = Opportunity::latest()->limit(10)->get();
        $activities = Activity::latest()->limit(10)->get();
        
        // PRODUCTS (IMPORTANT)
        $products = Product::where('work_point_id', session('work_point_id'))->get();
        return view('admin.store.sales_management.dashboard', compact(
            'stats',
            'campaigns',
            'responses',
            'opps',
            'activities',
            'products'
        ));

        //  SALES BY LOCATION
$salesByLocation = \App\Models\Sale::selectRaw('
    work_point_id,
    SUM(total_amount) as total_sales
')
->with(['workPoint','company'])
->groupBy('work_point_id')
->get();

//  SALES BY COMPANY
$salesByCompany = \App\Models\Sale::selectRaw('
    company_id,
    SUM(total_amount) as total_sales
')
->with('company')
->groupBy('company_id')
->get();

//  CAMPAIGN PERFORMANCE
$campaignReport = \App\Models\Campaign::selectRaw('
    name,
    revenue_generated,
    discount_given,
    (revenue_generated - discount_given) as profit,
    budget
')->get();

//  SALES VS PAYMENTS
$salesVsPayments = [
    'sales' => \App\Models\Sale::sum('total_amount'),
    'payments' => \App\Models\Payment::sum('amount'),
];

//  PRODUCT PERFORMANCE
$productPerformance = \App\Models\SaleItem::selectRaw('
    product_id,
    SUM(qty) as total_qty,
    SUM(total) as total_sales
')
->with('product')
->groupBy('product_id')
->orderByDesc('total_qty')
->get();

//  SALES TREND (MONTHLY)
$salesTrend = \App\Models\Sale::selectRaw('
    DATE(created_at) as date,
    SUM(total_amount) as total
')
->groupBy('date')
->orderBy('date','asc')
->get();
$query = Sale::query();

// DATE FILTER
if(request('from')){
    $query->whereDate('created_at','>=',request('from'));
}
if(request('to')){
    $query->whereDate('created_at','<=',request('to'));
}

// COMPANY
if(request('company_id')){
    $query->where('company_id',request('company_id'));
}

// LOCATION
if(request('work_point_id')){
    $query->where('work_point_id',request('work_point_id'));
}

$salesFiltered = $query->get();
$lossAlerts = Campaign::whereRaw('(revenue_generated - discount_given) < 0')->get();
 }
// ================= CUSTOMERS MODULE =================
public function customers(Request $request)
{
    $query = DB::table('customers as c')

        ->leftJoin(
            'company_sites as cs',
            'c.company_id',
            '=',
            'cs.id'
        )

        ->leftJoin(
            'company_units as cu',
            'c.comp_unit_id',
            '=',
            'cu.id'
        )

        ->leftJoin(
            'work_points as wp',
            'c.work_point_id',
            '=',
            'wp.id'
        )

        ->select(
            'c.id',
            'c.customer_name',
            'c.customer_code',
            'c.customer_type',
            'c.phone',
            'c.email',
            'c.address',
            'c.tin_number',
            'c.vrn',

            'c.country',
            'c.destination',

            'c.credit_limit',
            'c.opening_balance',
            'c.description',
            'c.status',
            'c.company_id',
            'c.comp_unit_id',
            'c.work_point_id',
            'c.account_id',

            'cs.company_code',
            'cs.company_name',

            'cu.unit_code as business_code',
            'cu.unit_name as business_name',

            'wp.work_code as location_code',
            'wp.work_name as location_name'
        );

    // ================= SEARCH =================
    if ($request->filled('search')) {

        $search = $request->search;

        $query->where(function ($q) use ($search) {

            $q->where(
                    'c.customer_name',
                    'like',
                    '%' . $search . '%'
                )

              ->orWhere(
                    'c.customer_code',
                    'like',
                    '%' . $search . '%'
                )

              ->orWhere(
                    'c.phone',
                    'like',
                    '%' . $search . '%'
                )

              ->orWhere(
                    'c.email',
                    'like',
                    '%' . $search . '%'
                )

              ->orWhere(
                    'c.tin_number',
                    'like',
                    '%' . $search . '%'
                )

              ->orWhere(
                    'c.country',
                    'like',
                    '%' . $search . '%'
                );

        });
    }

    // ================= PAGINATION =================
    $customers = $query
        ->orderByRaw('CAST(SUBSTRING(c.customer_code,4) AS UNSIGNED) ASC')
        ->paginate(100)
        ->appends($request->query());

    // ================= CUSTOMER ACCOUNTS =================
    $customerAccounts = DB::table('accnt_subcharts')

        ->where('Status', 'Active')

        ->where('SubCode', 'LIKE', '417401%')

        ->whereRaw('LENGTH(SubCode)=8')

        ->select(
            'id',
            'SubCode',
            'SubDescription'
        )

        ->orderBy('SubCode')

        ->get();

    // ================= COMPANIES =================
    $companies = \App\Models\CompanySite::all();

    // ================= BUSINESS UNITS =================
    $businessUnits = \App\Models\Company_unit::all();

    // ================= WORK POINTS =================
    $workPoints = \App\Models\WorkPoint::all();

    // ================= ACCOUNT CATEGORIES =================
    $accounts = DB::table('accnt_subcharts')

        ->where('Status', 'Active')

        ->where(function ($q) {

            $q->where('SubDescription', 'like', '%Receivable%')
              ->orWhere('SubDescription', 'like', '%Debtor%');

        })

        ->whereRaw('LENGTH(SubCode)=8')

        ->select(
            'id',
            'SubCode',
            'SubDescription'
        )

        ->orderBy('SubCode')

        ->get();

    // ================= NEXT ACCOUNT CODE =================
    try {

        $nextAccountCode = $this->generateCustomerAccountCode();

    } catch (\Exception $e) {

        $nextAccountCode = null;
    }

    return view(
        'admin.store.sales_management.customers',
        compact(
            'customers',
            'customerAccounts',
            'companies',
            'businessUnits',
            'workPoints',
            'accounts',
            'nextAccountCode'
        )
    );
}
// ================= STORE CUSTOMER =================
public function storeCustomer(Request $request)
{
    $request->validate([
        'customer_name' => 'required|string|max:255',
        'account_id'    => 'nullable|exists:accnt_subcharts,id',
        'company_id'    => 'required|exists:company_sites,id',
        'comp_unit_id'  => 'required|exists:company_units,id',
        'work_point_id' => 'required|exists:work_points,id',
        'phone'         => 'nullable|string|max:50',
        'email'         => 'nullable|email|max:255',
        'tin_number'    => 'nullable|string|max:100',
        'vrn'           => 'nullable|string|max:100',
        'country'       => 'nullable|string|max:10',
        'destination'   => 'nullable|string|max:10',
        'address'       => 'nullable|string',
        'description'   => 'nullable|string',
        'status'        => 'nullable|in:Active,Inactive',
        'customer_type' => 'nullable|string|max:100',
        'credit_limit'  => 'nullable|numeric',
        'opening_balance' => 'nullable|numeric',
    ]);

    DB::beginTransaction();

    try {

        $company = DB::table('company_sites')
            ->where('id', $request->company_id)
            ->first();

        $business = DB::table('company_units')
            ->where('id', $request->comp_unit_id)
            ->first();

        $work = DB::table('work_points')
            ->where('id', $request->work_point_id)
            ->first();

        if (!$company || !$business || !$work) {
            DB::rollBack();

            $this->swalMessage('error', 'Invalid company, business, or work point selection');

            return back()->withInput();
        }

        // ================= GENERATE CUSTOMER CODE =================
        $customerCode = $this->generateCustomerCode();

        $existingCustomer = Customer::where('customer_code', $customerCode)->first();

        if ($existingCustomer) {
            DB::rollBack();

            $this->swalMessage('error', 'Customer already exists');

            return back()->withInput();
        }

        Customer::create([
            'customer_code'   => $customerCode,
            'customer_name'   => $request->customer_name,
            'customer_type'   => $request->customer_type ?? 'Individual',
            'account_id'      => $request->account_id,
            'phone'           => $request->phone,
            'email'           => $request->email,
            'address'         => $request->address,
            'tin_number'      => $request->tin_number,
            'vrn'             => $request->vrn,
            'country'         => $request->country ? strtoupper($request->country) : null,
            'destination'     => $request->destination ? strtoupper($request->destination) : null,
            'credit_limit'    => $request->credit_limit ?? 0,
            'opening_balance' => $request->opening_balance ?? 0,
            'company_id'      => $request->company_id,
            'comp_unit_id'    => $request->comp_unit_id,
            'work_point_id'   => $request->work_point_id,
            'description'     => $request->description,
            'created_by'      => auth()->id(),
            'status'          => $request->status ?? 'Active',
        ]);

        DB::commit();

        $this->swalMessage('success', 'Customer saved successfully');

        return back();

    } catch (\Exception $e) {
        DB::rollBack();

        $this->swalMessage('error', $e->getMessage());

        return back()->withInput();
    }
}

// ================= EDIT CUSTOMER =================
public function editCustomer($encryptedId)
{
    $id = Crypt::decryptString($encryptedId);
    $customer = Customer::findOrFail($id);
    // ================= COMPANIES =================
    $companies = DB::table('company_sites')
        ->select(
            'id',
            'company_code',
            'company_name'
        )
        ->orderBy('company_name')
        ->get();

    // ================= BUSINESS UNITS OF SELECTED COMPANY =================
    $businessUnits = DB::table('company_units')
        ->where('company_id', $customer->company_id)
        ->select(
            'id',
            'unit_code',
            'unit_name'
        )
        ->orderBy('unit_name')
        ->get();

    // ================= WORK POINTS OF SELECTED BUSINESS UNIT =================
    $workPoints = DB::table('work_points')
        ->where('comp_unit_id', $customer->comp_unit_id)
        ->select(
            'id',
            'work_code',
            'work_name'
        )
        ->orderBy('work_name')
        ->get();

    // ================= ACCOUNT CATEGORIES =================
    $accounts = DB::table('accnt_subcharts')
        ->where('Status', 'Active')
        ->where(function ($q) {
            $q->where('SubCode', 'LIKE', '411%')
              ->orWhere('SubCode', 'LIKE', '417%');
        })
        ->whereRaw('LENGTH(SubCode)=8')
        ->select(
            'id',
            'SubCode',
            'SubDescription'
        )
        ->orderBy('SubCode')
        ->get();

    // ================= CUSTOMER DROPDOWN =================
    $customerAccounts = DB::table('accnt_subcharts')
        ->where('Status', 'Active')
        ->where(function ($q) {
            $q->where('SubCode', 'LIKE', '411%')
              ->orWhere('SubCode', 'LIKE', '417%');
        })
        ->whereRaw('LENGTH(SubCode)=8')
        ->select(
            'id',
            'SubCode',
            'SubDescription'
        )
        ->orderBy('SubCode')
        ->get();

    return view(
        'admin.store.sales_management.edit_customer',
        compact(
            'customer',
            'companies',
            'businessUnits',
            'workPoints',
            'accounts',
            'customerAccounts',
            'encryptedId'
        )
    );
}


// ================= UPDATE CUSTOMER =================
public function updateCustomer(Request $request, $encryptedId)
{
    try {
        $id = Crypt::decryptString($encryptedId);
    } catch (\Exception $e) {
        $this->swalMessage('error', 'Invalid customer link');

        return redirect()
            ->route('customers.index');
    }

    $request->validate([
        'customer_code' => 'required|string|max:100',
        'customer_name' => 'required|string|max:255',
        'company_id'    => 'required|exists:company_sites,id',
        'comp_unit_id'  => 'required|exists:company_units,id',
        'work_point_id' => 'required|exists:work_points,id',
        'account_id'    => 'nullable|exists:accnt_subcharts,id',
        'phone'         => 'nullable|string|max:50',
        'email'         => 'nullable|email|max:255',
        'address'       => 'nullable|string',
        'tin_number'    => 'nullable|string|max:100',
        'vrn'           => 'nullable|string|max:100',
        'country'       => 'nullable|string|max:10',
        'destination'   => 'nullable|string|max:10',
        'description'   => 'nullable|string',
        'status'        => 'required|in:Active,Inactive',
        'customer_type' => 'nullable|string|max:100',
        'credit_limit'  => 'nullable|numeric',
        'opening_balance' => 'nullable|numeric',
    ]);

    DB::beginTransaction();

    try {

        $customer = Customer::findOrFail($id);

        $company = DB::table('company_sites')
            ->where('id', $request->company_id)
            ->first();

        $business = DB::table('company_units')
            ->where('id', $request->comp_unit_id)
            ->where('company_id', $request->company_id)
            ->first();

        $work = DB::table('work_points')
            ->where('id', $request->work_point_id)
            ->where('comp_unit_id', $request->comp_unit_id)
            ->first();

        if (!$company || !$business || !$work) {
            DB::rollBack();

            $this->swalMessage('error', 'Invalid company, business unit, or work point selection');

            return back()->withInput();
        }

        $customer->update([
            'customer_code'   => $request->customer_code,
            'customer_name'   => $request->customer_name,
            'customer_type'   => $request->customer_type ?? 'Individual',
            'account_id'      => $request->account_id,
            'phone'           => $request->phone,
            'email'           => $request->email,
            'address'         => $request->address,
            'tin_number'      => $request->tin_number,
            'vrn'             => $request->vrn,
            'country'         => $request->country ? strtoupper($request->country) : null,
            'destination'     => $request->destination ? strtoupper($request->destination) : null,
            'credit_limit'    => $request->credit_limit ?? 0,
            'opening_balance' => $request->opening_balance ?? 0,
            'company_id'      => $request->company_id,
            'comp_unit_id'    => $request->comp_unit_id,
            'work_point_id'   => $request->work_point_id,
            'description'     => $request->description,
            'updated_by'      => auth()->id(),
            'status'          => $request->status,
        ]);

        DB::commit();

        $this->swalMessage('success', 'Customer updated successfully');

        return redirect()
            ->route('customers.index');

    } catch (\Exception $e) {
        DB::rollBack();

        $this->swalMessage('error', $e->getMessage());

        return back()->withInput();
    }
}


// ================= DELETE CUSTOMER WITH ENCRYPTED ID =================
// ================= DELETE CUSTOMER WITH ENCRYPTED ID =================
public function deleteCustomer($encryptedId)
{
    try {
        $id = Crypt::decryptString($encryptedId);
    } catch (\Exception $e) {
        $this->swalMessage('error', 'Invalid customer link');

    return redirect()
            ->route('customers.index');
    }
    $hasProforma = DB::table('proformas')
        ->where('customer_id', $id)
        ->exists();
    if ($hasProforma) {
        DB::table('customers')
            ->where('id', $id)
            ->update([
                'status' => 'Inactive',
                'updated_by' => auth()->id(),
            ]);
        $this->swalMessage('warning', 'Customer has transactions, so it has been deactivated');

    return back();
    }
    Customer::where('id', $id)->update([
        'status' => 0,
        'is_active' => 0
    ]);
    $this->swalMessage('success', 'Customer deleted successfully');

    return back();
}
// ================= AJAX: COMPANY → BUSINESS UNITS =================
public function getBusinessUnitsByCompany($companyId)
{
    $businessUnits = \App\Models\Company_unit::where('company_id', $companyId)
        ->select(
            'id',
            'unit_code',
            'unit_name'
        )
        ->orderBy('unit_code')
        ->get();

    return response()->json($businessUnits);
}


// ================= AJAX: BUSINESS UNIT → WORK POINTS =================
public function getWorkPointsByBusinessUnit($unitId)
{
    $workPoints = \App\Models\WorkPoint::where('comp_unit_id', $unitId)
        ->select(
            'id',
            'work_code',
            'work_name'
        )
        ->orderBy('work_code')
        ->get();

    return response()->json($workPoints);
}

public function contacts(Request $request)
{
    $query = Contact::query();

    if ($request->search) {
        $query->where('first_name', 'like', '%'.$request->search.'%')
              ->orWhere('last_name', 'like', '%'.$request->search.'%')
              ->orWhere('phone', 'like', '%'.$request->search.'%')
              ->orWhere('email', 'like', '%'.$request->search.'%');
    }

    $contacts = $query->latest()->paginate(10);

    return view('admin.store.sales_management.contacts', compact('contacts'));
}

public function searchContacts(Request $request)
{
    $contacts = \App\Models\Contact::where('name', 'like', '%'.$request->search.'%')
        ->orWhere('phone', 'like', '%'.$request->search.'%')
        ->orWhere('email', 'like', '%'.$request->search.'%')
        ->latest()
        ->paginate(10);

    return view('admin.store.sales_management.partials.contacts_table', compact('contacts'))->render();
}
public function storeContact(Request $request)
{
    $request->validate([
        'first_name' => 'required|string|max:255',
        'email' => 'nullable|email',
    ]);

    Contact::create([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'phone' => $request->phone,
        'email' => $request->email,
        'company_id' => session('company_id'),
        'work_point_id' => session('work_point_id'),
    ]);

    $this->swalMessage('success', 'Contact added');

    return redirect()->back();
}

public function updateContact(Request $request, $id)
{
    $request->validate([
        'first_name' => 'required'
    ]);

    $contact = Contact::findOrFail($id);

    $contact->update([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'phone' => $request->phone,
        'email' => $request->email,
    ]);

    $this->swalMessage('success', 'Contact updated');

    return redirect()->back();
}
public function deleteContact($id)
{
    \App\Models\Contact::findOrFail($id)->delete();

    $this->swalMessage('success', 'Deleted');

    return back();
}

public function getContactDetails($id)
{
    $contact = Contact::with('customer')->findOrFail($id);

    return response()->json([
        'contact_id' => $contact->id,
        'name' => $contact->full_name,
        'phone' => $contact->phone,
        'email' => $contact->email,
        'customer_id' => $contact->cstm_id
    ]);
}
// ================= CAMPAIGNS =================
public function campaigns()
{
    // ================= GET CAMPAIGNS =================
    $campaigns = Campaign::with([
        'company',
        'businessUnit',
        'workPoint'
    ])
    ->latest()
    ->get();

    // ================= DASHBOARD =================
    $totalCampaigns = $campaigns->count();

    $activeCampaigns = $campaigns
        ->where('status', 'active')
        ->filter(function ($c) {
            return now()->between(
                \Carbon\Carbon::parse($c->start_date),
                \Carbon\Carbon::parse($c->end_date)
            );
        })
        ->count();

    $totalDiscount = $campaigns->sum('discount_given');

    $campaignRevenue = $campaigns->sum('revenue_generated');

    // ================= NEW METRICS =================
    $totalBudget = $campaigns->sum('budget');

    $profit = $campaignRevenue - $totalDiscount;

    $roi = $totalBudget > 0
        ? ($profit / $totalBudget) * 100
        : 0;

    // ================= DROPDOWNS =================
    $companies = CompanySite::all();

    $businessUnits = Company_unit::all();

    $workpoints = WorkPoint::all();

    // ================= RETURN =================
    return view('admin.store.sales_management.campaigns', [
        'campaigns' => $campaigns,

        // DASHBOARD
        'totalCampaigns' => $totalCampaigns,
        'activeCampaigns' => $activeCampaigns,
        'totalDiscount' => $totalDiscount,
        'campaignRevenue' => $campaignRevenue,

        // NEW
        'totalBudget' => $totalBudget,
        'profit' => $profit,
        'roi' => $roi,

        // DROPDOWNS
        'companies' => $companies,
        'businessUnits' => $businessUnits,
        'workpoints' => $workpoints,
    ]);
}


// ================= STORE CAMPAIGN =================
public function storeCampaign(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|string|max:100',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'company_id' => 'required|exists:company_sites,id',
        'business_unit_id' => 'required|exists:company_units,id',
        'work_point_id' => 'required|exists:work_points,id',
    ]);

    // ================= GET RELATED DATA =================
    $company = CompanySite::findOrFail($request->company_id);

    $unit = Company_unit::findOrFail($request->business_unit_id);

    $workpoint = WorkPoint::findOrFail($request->work_point_id);

    // ================= SAVE CAMPAIGN =================
    Campaign::create([

        // BASIC
        'name' => $request->name,

        'description' => $request->description,

        'type' => $request->type,

        // FINANCIAL
        'discount' => $request->discount ?? 0,

        'budget' => $request->budget ?? 0,

        'revenue_generated' => $request->revenue_generated ?? 0,

        'discount_given' => $request->discount_given ?? 0,

        // TARGET
        'customer_type' => $request->customer_type ?? 'all',

        // DATES
        'start_date' => $request->start_date,

        'end_date' => $request->end_date,

        // STATUS
        'status' => $request->status ?? 'active',

        // LINKS
        'company_id' => $request->company_id,

        'business_unit_id' => $request->business_unit_id,

        'work_point_id' => $request->work_point_id,

        // COMPANY SNAPSHOT
        'company_code' => $company->company_code,

        'company_name' => $company->company_name,

        // BUSINESS SNAPSHOT
        'business_code' => $unit->unit_code,

        'business_name' => $unit->unit_name,

        // LOCATION SNAPSHOT
        'location_code' => $workpoint->work_code,

        'location_name' => $workpoint->work_name,

    ]);

    $this->swalMessage('success', 'Campaign Created Successfully');

    return back();
}


// ================= EDIT CAMPAIGN =================
public function editCampaign($id)
{
    $campaignId = Crypt::decryptString($id);

    $campaign = Campaign::with([
        'company',
        'businessUnit',
        'workPoint'
    ])
    ->findOrFail($campaignId);

    $companies = CompanySite::all();

    $businessUnits = Company_unit::where('company_id', $campaign->company_id)
        ->orderBy('unit_code')
        ->get();

    $workpoints = WorkPoint::where('comp_unit_id', $campaign->business_unit_id)
        ->orderBy('work_code')
        ->get();

    return view(
        'admin.store.sales_management.edit_campaign',
        compact(
            'campaign',
            'companies',
            'businessUnits',
            'workpoints'
        )
    );
}


// ================= UPDATE CAMPAIGN =================
public function updateCampaign(Request $request, $id)
{
    $campaignId = Crypt::decryptString($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|string|max:100',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'company_id' => 'required|exists:company_sites,id',
        'business_unit_id' => 'required|exists:company_units,id',
        'work_point_id' => 'required|exists:work_points,id',
    ]);

    $campaign = Campaign::findOrFail($campaignId);

    // ================= GET RELATED DATA =================
    $company = CompanySite::findOrFail($request->company_id);

    $unit = Company_unit::findOrFail($request->business_unit_id);

    $workpoint = WorkPoint::findOrFail($request->work_point_id);

    // ================= UPDATE CAMPAIGN =================
    $campaign->update([

        // BASIC
        'name' => $request->name,

        'description' => $request->description,

        'type' => $request->type,

        // FINANCIAL
        'discount' => $request->discount ?? 0,

        'budget' => $request->budget ?? 0,

        'revenue_generated' => $request->revenue_generated ?? $campaign->revenue_generated,

        'discount_given' => $request->discount_given ?? $campaign->discount_given,

        // TARGET
        'customer_type' => $request->customer_type ?? 'all',

        // DATES
        'start_date' => $request->start_date,

        'end_date' => $request->end_date,

        // STATUS
        'status' => $request->status ?? 'active',

        // LINKS
        'company_id' => $request->company_id,

        'business_unit_id' => $request->business_unit_id,

        'work_point_id' => $request->work_point_id,

        // COMPANY SNAPSHOT
        'company_code' => $company->company_code,

        'company_name' => $company->company_name,

        // BUSINESS SNAPSHOT
        'business_code' => $unit->unit_code,

        'business_name' => $unit->unit_name,

        // LOCATION SNAPSHOT
        'location_code' => $workpoint->work_code,

        'location_name' => $workpoint->work_name,

    ]);

    $this->swalMessage('success', 'Campaign Updated Successfully');

    return redirect()
        ->route('sales.campaigns.index');
}


// ================= DELETE CAMPAIGN =================
public function deleteCampaign($id)
{
    $campaignId = Crypt::decryptString($id);

    $campaign = Campaign::findOrFail($campaignId);

    $campaign->delete();

    $this->swalMessage('success', 'Campaign Deleted Successfully');

    return back();
}
public function processSale(Request $request)
{
    $request->validate([
        'customer_id' => 'required',
        'total_amount' => 'required|numeric',
        'company_id' => 'required'
    ]);

    DB::beginTransaction();

    try {

        $customer = Customer::findOrFail($request->customer_id);

        $sale = Sale::create([
            'customer_id' => $customer->id,
            'total_amount' => $request->total_amount,
            'company_id' => $request->company_id,
            'work_point_id' => $request->work_point_id ?? null,
            'payment_method' => $request->payment_method ?? 'cash'
        ]);

        //  GET CAMPAIGN
        $campaign = $this->getCampaign($sale->company_id, $customer);

        $discountAmount = 0;

        //  CHECK KAMA CAMPAIGN IPO
        if($campaign){

            if($campaign->type == 'discount'){
                $discountAmount = ($sale->total_amount * $campaign->discount) / 100;
            }

            $campaign->update([
                'discount_given' => $campaign->discount_given + $discountAmount,
                'revenue_generated' => $campaign->revenue_generated + $sale->total_amount
            ]);
        }

        //  FINAL AMOUNT
        $finalAmount = $sale->total_amount - $discountAmount;

        //  SAVE ITEMS (NEW - POS SUPPORT)
        if($request->has('items')){

            foreach($request->items as $item){

                // SAVE ITEM
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total' => $item['qty'] * $item['price']
                ]);

                //  STOCK REDUCTION
                $product = Product::find($item['id']);
                if($product){
                    $product->closing_stock -= $item['qty'];
                    $product->save();
                }
            }
        }

        //  ACCOUNTING (HAIJAGUSWA)
        $this->accounting($sale, $discountAmount, $finalAmount);

        DB::commit();

        return response()->json([
            'sale_id' => $sale->id,
            'amount' => $sale->total_amount,
            'discount' => $discountAmount,
            'final' => $finalAmount
        ]);

    } catch (\Exception $e){

        DB::rollback();

        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

private function accounting($sale, $discountAmount, $final)
{
    $cash = Account::where('module','finance')->where('type','cash')->first();
    $sales = Account::where('module','sales')->where('type','revenue')->first();
    $discountAcc = Account::where('module','sales')->where('type','expense')->first();

    $journal = JournalEntry::create([
        'reference' => 'Sale-'.$sale->id,
        'date' => now()
    ]);

    // CASH
    JournalEntryLine::create([
        'journal_entry_id' => $journal->id,
        'account_id' => $cash->id,
        'debit' => $final,
        'credit' => 0
    ]);

    // SALES
    JournalEntryLine::create([
        'journal_entry_id' => $journal->id,
        'account_id' => $sales->id,
        'debit' => 0,
        'credit' => $sale->total_amount
    ]);

    // DISCOUNT
    if($discountAmount > 0){
        JournalEntryLine::create([
            'journal_entry_id' => $journal->id,
            'account_id' => $discountAcc->id,
            'debit' => $discountAmount,
            'credit' => 0
        ]);
    }
}

public function responses()
{
    $data = CampaignResponse::query()
        ->with(['campaign', 'customer'])
        ->latest()
        ->paginate(20);

    return view('admin.store.sales_management.responses', compact('data'));
}

// ================= OPPORTUNITIES =================
public function opportunities()
{
    $data = Opportunity::query()
        ->with([
            'customer',
            'company',
            'businessUnit',
            'workPoint',
            'user',
            'assignedUser'
        ])
        ->latest()
        ->paginate(20);

    $customers = Customer::orderBy('customer_name')->get();

    $companies = CompanySite::orderBy('company_name')->get();

    $users = User::orderBy('name')->get();

    $totalOpportunities = Opportunity::count();

    $openOpportunities = Opportunity::where('status', 'Open')->count();

    $wonOpportunities = Opportunity::where('status', 'Won')->count();

    $lostOpportunities = Opportunity::where('status', 'Lost')->count();

    $expectedRevenue = Opportunity::where('status', 'Open')
        ->sum('estimated_value');

    return view(
        'admin.store.sales_management.opportunities',
        compact(
            'data',
            'customers',
            'companies',
            'users',
            'totalOpportunities',
            'openOpportunities',
            'wonOpportunities',
            'lostOpportunities',
            'expectedRevenue'
        )
    );
}


// ================= STORE OPPORTUNITY =================
public function storeOpportunity(Request $request)
{
    $request->validate([
        'opportunity_name' => 'required|string|max:255',
        'cstm_id'          => 'required|exists:customers,id',
        'company_id'       => 'nullable|exists:company_sites,id',
        'business_unit_id' => 'nullable|exists:company_units,id',
        'work_point_id'    => 'nullable|exists:work_points,id',
        'estimated_value'  => 'nullable|numeric',
        'close_expected'   => 'nullable|date',
        'stage'            => 'required',
        'status'           => 'required',
        'assigned_to'      => 'nullable|exists:users,id',
        'description'      => 'nullable|string',
    ]);

    Opportunity::create([
        'opportunity_name' => $request->opportunity_name,

        'user_id' => auth()->id(),

        'assigned_to' => $request->assigned_to,

        'company_id' => $request->company_id,

        'business_unit_id' => $request->business_unit_id,

        'work_point_id' => $request->work_point_id,

        'cstm_id' => $request->cstm_id,

        'estimated_value' => $request->estimated_value ?? 0,

        'close_expected' => $request->close_expected,

        'stage' => $request->stage,

        'status' => $request->status,

        'description' => $request->description,
    ]);

    $this->swalMessage('success', 'Opportunity saved successfully');

    return redirect()
        ->back();
}


// ================= EDIT OPPORTUNITY =================
public function editOpportunity($id)
{
    $opportunityId = Crypt::decryptString($id);

    $opportunity = Opportunity::with([
            'customer',
            'company',
            'businessUnit',
            'workPoint',
            'user',
            'assignedUser'
        ])
        ->findOrFail($opportunityId);

    $customers = Customer::orderBy('customer_name')->get();

    $companies = CompanySite::orderBy('company_name')->get();

    $users = User::orderBy('name')->get();

    return view(
        'admin.store.sales_management.edit_opportunity',
        compact(
            'opportunity',
            'customers',
            'companies',
            'users'
        )
    );
}


// ================= UPDATE OPPORTUNITY =================
public function updateOpportunity(Request $request, $id)
{
    $opportunityId = Crypt::decryptString($id);

    $request->validate([
        'opportunity_name' => 'required|string|max:255',
        'cstm_id'          => 'required|exists:customers,id',
        'company_id'       => 'nullable|exists:company_sites,id',
        'business_unit_id' => 'nullable|exists:company_units,id',
        'work_point_id'    => 'nullable|exists:work_points,id',
        'estimated_value'  => 'nullable|numeric',
        'close_expected'   => 'nullable|date',
        'stage'            => 'required',
        'status'           => 'required',
        'assigned_to'      => 'nullable|exists:users,id',
        'description'      => 'nullable|string',
    ]);

    $opportunity = Opportunity::findOrFail($opportunityId);

    $opportunity->update([
        'opportunity_name' => $request->opportunity_name,

        'assigned_to' => $request->assigned_to,

        'company_id' => $request->company_id,

        'business_unit_id' => $request->business_unit_id,

        'work_point_id' => $request->work_point_id,

        'cstm_id' => $request->cstm_id,

        'estimated_value' => $request->estimated_value ?? 0,

        'close_expected' => $request->close_expected,

        'stage' => $request->stage,

        'status' => $request->status,

        'description' => $request->description,
    ]);

    $this->swalMessage('success', 'Opportunity updated successfully');

    return redirect()
        ->route('sales.opportunities');
}


// ================= DELETE OPPORTUNITY =================
public function deleteOpportunity($id)
{
    $opportunityId = Crypt::decryptString($id);

    $opportunity = Opportunity::findOrFail($opportunityId);

    $opportunity->delete();

    $this->swalMessage('success', 'Opportunity deleted successfully');

    return redirect()
        ->back();
}
// ================= ACTIVITIES =================
public function activities()
{
    $data = Activity::query()
        ->with([
            'user',
            'assignedTo',
            'company',
            'workPoint'
        ])
        ->latest()
        ->paginate(20);

    $users = User::orderBy('name')->get();

    $companies = CompanySite::orderBy('company_name')->get();

    $workpoints = WorkPoint::orderBy('work_name')->get();

    $totalActivities = Activity::count();

    $pendingActivities = Activity::where('status', 'Pending')->count();

    $doneActivities = Activity::where('status', 'Done')->count();

    $approvedActivities = Activity::where('status', 'Approved')->count();

    return view(
        'admin.store.sales_management.activities',
        compact(
            'data',
            'users',
            'companies',
            'workpoints',
            'totalActivities',
            'pendingActivities',
            'doneActivities',
            'approvedActivities'
        )
    );
}


// ================= STORE ACTIVITY =================
public function storeActivity(Request $request)
{
    $request->validate([
        'subject'       => 'required|string|max:255',
        'type'          => 'nullable|string|max:100',
        'module'        => 'nullable|string|max:100',
        'company_id'    => 'nullable|exists:company_sites,id',
        'work_point_id' => 'nullable|exists:work_points,id',
        'assigned_to'   => 'nullable|exists:users,id',
        'activity_date' => 'nullable|date',
        'due_at'        => 'nullable|date',
        'status'        => 'required|in:Pending,Approved,Done,Cancelled,Deleted',
        'body'          => 'nullable|string',
    ]);

    Activity::create([
        'user_id'       => auth()->id(),

        'assigned_to'   => $request->assigned_to,

        'company_id'    => $request->company_id,

        'work_point_id' => $request->work_point_id,

        'type'          => $request->type,

        'module'        => $request->module,

        'subject'       => $request->subject,

        'body'          => $request->body,

        'status'        => $request->status ?? 'Pending',

        'activity_date' => $request->activity_date,

        'due_at'        => $request->due_at,
    ]);

    $this->swalMessage('success', 'Activity saved successfully');

    return redirect()
        ->back();
}


// ================= EDIT ACTIVITY =================
public function editActivity($id)
{
    $activityId = Crypt::decryptString($id);

    $activity = Activity::with([
            'user',
            'assignedTo',
            'company',
            'workPoint'
        ])
        ->findOrFail($activityId);

    $users = User::orderBy('name')->get();

    $companies = CompanySite::orderBy('company_name')->get();

    $workpoints = WorkPoint::orderBy('work_name')->get();

    return view(
        'admin.store.sales_management.edit_activity',
        compact(
            'activity',
            'users',
            'companies',
            'workpoints'
        )
    );
}
// ================= UPDATE ACTIVITY =================
public function updateActivity(Request $request, $id)
{
    $activityId = Crypt::decryptString($id);

    $request->validate([
        'subject'       => 'required|string|max:255',
        'type'          => 'nullable|string|max:100',
        'module'        => 'nullable|string|max:100',
        'company_id'    => 'nullable|exists:company_sites,id',
        'work_point_id' => 'nullable|exists:work_points,id',
        'assigned_to'   => 'nullable|exists:users,id',
        'activity_date' => 'nullable|date',
        'due_at'        => 'nullable|date',
        'status'        => 'required|in:Pending,Approved,Done,Cancelled,Deleted',
        'body'          => 'nullable|string',
    ]);

    $activity = Activity::findOrFail($activityId);

    $activity->update([
        'assigned_to'   => $request->assigned_to,

        'company_id'    => $request->company_id,

        'work_point_id' => $request->work_point_id,

        'type'          => $request->type,

        'module'        => $request->module,

        'subject'       => $request->subject,

        'body'          => $request->body,

        'status'        => $request->status,

        'activity_date' => $request->activity_date,

        'due_at'        => $request->due_at,
    ]);

    $this->swalMessage('success', 'Activity updated successfully');

    return redirect()
        ->route('sales.activities');
}


// ================= DELETE ACTIVITY =================
public function deleteActivity($id)
{
    $activityId = Crypt::decryptString($id);

    $activity = Activity::findOrFail($activityId);

    $activity->delete();

    $this->swalMessage('success', 'Activity deleted successfully');

    return redirect()
        ->back();
}
    public function creditNotes()
    {
        $data = CreditNote::latest()->get();
        return view('admin.store.sales_management.credit_notes', compact('data'));
    }

    public function pos()
{
    $companies = \App\Models\CompanySite::all();
    $workPoints = \App\Models\WorkPoint::all();
    $products = Product::where('work_point_id', session('work_point_id'))->get();

    return view('admin.store.sales_management.pos', compact(
        'companies',
        'workPoints',
        'products'
    ));
}

  //CENTRAL FINANCE POSTING (STANDARD ERP)
private function postToFinance($data)
{
    DB::table('accounting_transactions')->insert([
        'posting_date' => now(),
        'account_code' => $data['account_code'],
        'account_description' => $data['account_name'] ?? null,
        'company_code' => $data['company_id'] ?? null,
        'debit' => $data['debit'] ?? 0,
        'credit' => $data['credit'] ?? 0,
        'details' => $data['details'] ?? '',
        'status' => 'posted',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

public function approveDelivery($id)
{
    DB::beginTransaction();
    DB::statement('LOCK TABLE stock_ledgers WRITE');

    try {

        $delivery = Delivery::with('items.product')->findOrFail($id);

        //  prevent double posting
        if ($delivery->approval_status == 'approved') {
            $this->swalMessage('error', 'Already approved');

    return back();
        }

        foreach ($delivery->items as $item) {

            $product = $item->product;
           

                $qty = $item->quantity;
                $cost = $product->avg_cost;

                //  UPDATE PRODUCT STOCK VALUE
                $product->total_qty -= $qty;
                $product->total_value -= ($qty * $cost);

                //  AVOID NEGATIVE
                if ($product->total_qty < 0) {
                    throw new \Exception("Stock corrupted");
                }

                $product->avg_cost = $product->total_qty > 0
                    ? $product->total_value / $product->total_qty
                    : 0;

                $product->save();

            $batches = StockBatch::where('product_id', $item->product_id)
                ->where('work_point_id', $delivery->work_point_id)
                ->where('qty','>',0)
                ->orderBy('created_at','asc')
                ->get();

            foreach ($batches as $batch) {

                if ($remainingQty <= 0) break;

                if ($batch->qty >= $remainingQty) {

                    $batch->qty -= $remainingQty;
                    $batch->save();

                    $remainingQty = 0;

                } else {

                    $remainingQty -= $batch->qty;
                    $batch->qty = 0;
                    $batch->save();
                }
            }

            if ($remainingQty > 0) {
                throw new \Exception("Batch stock not enough");
            }

            //  CURRENT STOCK CALCULATION
            $received = StockLedger::where('product_id',$product->id)
                ->where('work_point_id',$delivery->work_point_id)
                ->sum('qty_in');

            $issued = StockLedger::where('product_id',$product->id)
                ->where('work_point_id',$delivery->work_point_id)
                ->sum('qty_out');

            $current_stock = $product->opening_stock + $received - $issued;

            //  VALIDATION
            if ($item->quantity > $current_stock) {
                throw new \Exception("Stock not enough for ".$product->product_name);
            }

            //  STOCK OUT ENTRY
            StockLedger::create([
                'product_id'    => $product->id,
                'qty_in'        => 0,
                'qty_out'       => $item->quantity,
                'unit_cost' => $cost,
                'total_cost' => $qty * $cost,
                'reference_type'=> 'delivery',
                'reference_id'  => $delivery->id,
                'account_code'  => $product->account_code,
                'account_name'  => $product->account_name,
                'company_id'    => $delivery->company_id,
                'work_point_id' => $delivery->work_point_id,
            ]);
        }

        //  UPDATE DELIVERY STATUS
        $delivery->approval_status = 'approved';
        $delivery->delivery_status = 'pending';
        $delivery->save();

        DB::commit();

        $this->swalMessage('success', 'Delivery approved & stock updated');

    return back();

    } catch (\Exception $e) {

        DB::rollback();
        $this->swalMessage('error', $e->getMessage());

    return back();
    }
}


public function dispatchDelivery($id)
{
    $delivery = Delivery::findOrFail($id);

    //  VALIDATION (VERY IMPORTANT)
    if (!$delivery->driver_name || !$delivery->vehicle_no) {
        $this->swalMessage('error', 'Driver and Vehicle required before dispatch');

    return back();
    }

    // EXPLOSIVE VALIDATION
    foreach ($delivery->items as $item) {

        if ($item->product && $item->product->category == 'explosive') {

            if (!$delivery->permit_no) {
                $this->swalMessage('error', 'Explosive permit required');

    return back();
            }

            if (!$delivery->safety_officer) {
                $this->swalMessage('error', 'Safety officer required');

    return back();
            }

        }
    }

    $delivery->delivery_status = 'in_transit';
    $delivery->dispatch_date = now();
    $delivery->save();

    $this->swalMessage('success', 'Delivery dispatched successfully');

    return back();
}

public function confirmDeliveryForm($id)
{
    $delivery = Delivery::findOrFail($id);

    return view('admin.store.sales_management.delivery_confirm', compact('delivery'));
}

public function confirmDelivery(Request $request, $id)
{
    DB::beginTransaction();

    try {

        $delivery = Delivery::findOrFail($id);

        if(!$request->receiver_name){
            throw new \Exception("Receiver name required");
        }

        $delivery->receiver_name = $request->receiver_name;
        $delivery->receiver_signature = $request->signature;
        $delivery->delivered_at = now();

        $delivery->delivery_status = 'delivered';
        $delivery->actual_delivery_date = now();

        $delivery->save();

        DB::commit();

        $this->swalMessage('success', 'Delivery confirmed successfully');

    return redirect()->route('sales.deliveries');

    } catch (\Exception $e){

        DB::rollback();
        $this->swalMessage('error', $e->getMessage());

    return back();
    }
}


public function getProductsByWorkpoint($workpointId)
{
    $products = DB::table('accnt_subcharts')
        ->where('work_point_id', $workpointId)
        ->where('SubCode', 'LIKE', '351301%')
        ->where('SubCode', '!=', '351301') 
        ->where('Status', 'Active')
        ->get();

    return response()->json($products);
}

public function getProformaItems($id)
{
    $proforma = Proforma::with('items','bank')->findOrFail($id);

    return response()->json([
        'items' => $proforma->items,

        // BANK DETAILS
        'bank_name' => optional($proforma->bank)->SubDescription,
        'account_number' => $proforma->account_number,
        'swift_code' => $proforma->swift_code,
        'branch' => $proforma->branch,
    ]);
}

public function proformaList()
{
    $data = \App\Models\Proforma::with(['customer','items'])
            ->latest()
            ->get();

    return view('admin.store.sales_management.proforma_list', compact('data'));
}

public function gsItemsIndex()
{
    $items = GeneralSupplyItem::orderByDesc('id')->get();
    return view('admin.store.items', compact('items'));
}

public function gsItemsStore(Request $request)
{
    try {
        $request->validate([
            'item_name' => 'required|string|max:255|unique:general_supply_items,item_name',
            'item_code' => 'nullable|string|max:100',
            'status' => 'required|in:Active,Inactive',
        ]);

        GeneralSupplyItem::create([
            'item_name' => $request->item_name,
            'item_code' => $request->item_code,
            'status' => $request->status,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        Alert::success('Success', 'Item created successfully');
        return redirect()->route('sales.gs.items.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to create item');
        return back()->withInput();
    }
}

public function gsItemsUpdate(Request $request, $id)
{
    try {
        $realId = decrypt($id);
        $item = GeneralSupplyItem::findOrFail($realId);

        $request->validate([
            'item_name' => ['required','string','max:255', Rule::unique('general_supply_items', 'item_name')->ignore($item->id)],
            'item_code' => 'nullable|string|max:100',
            'status' => 'required|in:Active,Inactive',
        ]);

        $item->update([
            'item_name' => $request->item_name,
            'item_code' => $request->item_code,
            'status' => $request->status,
            'updated_by' => Auth::id(),
        ]);

        Alert::success('Success', 'Item updated successfully');
        return redirect()->route('sales.gs.items.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to update item');
        return back()->withInput();
    }
}

public function gsItemsDestroy($id)
{
    try {
        $realId = decrypt($id);
        GeneralSupplyItem::findOrFail($realId)->delete();
        Alert::success('Success', 'Item removed successfully');
        return redirect()->route('sales.gs.items.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to remove item');
        $this->swalMessage('success', 'Order created successfully');
    return redirect()->route('sales.orders.index');
    }
}

public function profitReport()
{
    $invoices = Invoice::where('work_point_id', session('work_point_id'))->get();

    $data = [];
    $daily = [];
    $monthly = [];
    $products = [];

    foreach ($invoices as $invoice) {

        $product = Product::find($invoice->product_id);

        $cost = $product->cost_price ?? 0;

        $revenue = $invoice->total;
        $cogs = $invoice->qty * $cost;
        $profit = $revenue - $cogs;

        // MAIN TABLE
        $data[] = [
            'invoice_no' => $invoice->id,
            'product' => $product->product_name ?? '',
            'qty' => $invoice->qty,
            'revenue' => $revenue,
            'cogs' => $cogs,
            'profit' => $profit,
            'date' => $invoice->created_at,
        ];

        // PROFIT PER DAY
        $day = $invoice->created_at->format('Y-m-d');
        $daily[$day] = ($daily[$day] ?? 0) + $profit;

        // PROFIT PER MONTH
        $month = $invoice->created_at->format('Y-m');
        $monthly[$month] = ($monthly[$month] ?? 0) + $profit;

        // TOP PRODUCTS
        $name = $product->product_name ?? 'Unknown';
        $products[$name] = ($products[$name] ?? 0) + $invoice->qty;
    }

    arsort($products);

    return view('admin.store.sales_management.profit_report', [
        'data' => $data,
        'daily' => $daily,
        'monthly' => $monthly,
        'topProducts' => array_slice($products, 0, 5)
    ]);
}
public function exportProfitReport()
{
    $query = Invoice::where('work_point_id', session('work_point_id'));

if (request('company_id')) {
    $query->where('company_id', request('company_id'));
}

$invoices = $query->get();

    $filename = "profit_report.csv";

    $headers = [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
    ];

    $callback = function() use ($invoices) {

        $file = fopen('php://output', 'w');

        fputcsv($file, ['Invoice', 'Product', 'Qty', 'Revenue', 'COGS', 'Profit', 'Date']);

        foreach ($invoices as $invoice) {

            $product = Product::find($invoice->product_id);
            $cost = $product->cost_price ?? 0;

            $revenue = $invoice->total;
            $cogs = $invoice->qty * $cost;
            $profit = $revenue - $cogs;

            fputcsv($file, [
                $invoice->id,
                $product->product_name ?? '',
                $invoice->qty,
                $revenue,
                $cogs,
                $profit,
                $invoice->created_at
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
private function generateCustomerAccountCode()
{
    $lastCustomer = \App\Models\Customer::whereNotNull('account_code')
        ->orderBy('account_code', 'desc')
        ->first();

    if (!$lastCustomer) {
        return '41140100'; 
    }

    $lastCode = (int) $lastCustomer->account_code;

    $nextCode = $lastCode + 1;

    return (string) $nextCode;
}

public function deleteAttachment($id)
{
    $file = \App\Models\Attachment::findOrFail($id);

    Storage::disk('public')->delete($file->file_path);

    $file->delete();

    $this->swalMessage('success', 'File deleted successfully');

    return back();
}

public function payments()
{
    $data = Payment::with(['customer','invoice'])->latest()->get();

    $customers = \App\Models\Customer::all();
    $invoices = \App\Models\Invoice::all();

    return view('admin.store.sales_management.payments', compact(
        'data',
        'customers',
        'invoices'
    ));
}

public function storeAttachment(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:pdf|max:2048'
    ]);

    $file = $request->file('file');

    $path = $file->store('attachments', 'public');

    \App\Models\Attachment::create([
        'sales_order_id' => $request->sales_order_id,
        'file_name' => $file->getClientOriginalName(),
        'file_path' => $path,
        'status' => 'pending'
    ]);

    $this->swalMessage('success', 'File uploaded');

    return back();
}

public function gsStockIndex()
{

    $itemStocks = \App\Models\GeneralSupplyItem::all(); // au model yako ya item
    $rows = [];
    $audits = [];
    $products = [];
    $stats = [];

    return view('admin.store.general_supply.stock', compact(
        'itemStocks','rows','audits','products','stats'
    ));
}

public function getBusiness($companyId)
{
    return DB::table('company_units')
        ->where('company_id', $companyId)
        ->where('status', 'Active')
        ->select(
            'id',
            'unit_code as code',
            'unit_name as name',
            'location'
        )
        ->orderBy('unit_code')
        ->get();
}

public function getWorkPoints($businessId)
{
    return DB::table('work_points')
        ->where('business_unit_id', $businessId)
        ->where('status', 'Active')
        ->select(
            'id',
            'work_code as code',
            'work_name as name'
        )
        ->get();
}

public function getSubAccounts($chartId)
{
    return DB::table('sub_account_codes')
        ->where('accnt_chart_id', $chartId)
        ->whereRaw('LENGTH(SubCode) = 8') 
        ->where('Status', 'Active')
        ->orderBy('SubCode')
        ->get();
}
public function exportCustomersExcel()
{
    return Excel::download(new CustomersExport, 'customers.xlsx');
}
public function exportCustomersPDF()
{
    $customers = DB::table('customers as c')
        ->leftJoin('accounts as a', 'c.account_id', '=', 'a.id')
        ->select(
            'c.customer_code',
            'c.customer_name',
            'c.phone',
            'c.tin_number',
            'c.status',
            'a.account_code',
            'a.account_name',
            'c.category'
        )
        ->get();

    $pdf = Pdf::loadView('admin.store.sales_management.reports.customers_pdf', compact('customers'));

    return $pdf->download('customers.pdf');
}
public function getCustomers($workPointId)
{
    return DB::table('customers')
        ->where('work_point_id', $workPointId)
        ->where('status', 'Active')
        ->select('id', 'customer_name', 'account_code')
        ->get();
}
// ================= LEADS =================
public function leads()
{
    $leads = Lead::with('user')
        ->latest()
        ->get();

    return view(
        'admin.store.sales_management.leads',
        compact('leads')
    );
}


// ================= STORE LEAD =================
public function storeLead(Request $request)
{
    $request->validate([

        'customer_name' => 'required|string|max:255',

        'phone' => 'nullable|string|max:50',

        'email' => 'nullable|email|max:255',

        'business_type' => 'nullable|string|max:100',

        'source' => 'nullable|string|max:100',

        'status' => 'required|in:pending,converted',

        'description' => 'nullable|string',

    ]);

    Lead::create([

        'customer_name' => $request->customer_name,

        'phone' => $request->phone,

        'email' => $request->email,

        'business_type' => $request->business_type,

        'source' => $request->source,

        'status' => $request->status ?? 'pending',

        'description' => $request->description,

        'user_id' => auth()->id(),

        'company_id' => session('company_id'),

        'work_point_id' => session('work_point_id'),

    ]);

    $this->swalMessage('success', 'Lead saved successfully');

    return back();
}


// ================= EDIT LEAD =================
public function editLead($id)
{
        $leadId = Crypt::decryptString($id);
    $lead = Lead::findOrFail($leadId);

    return view(
        'admin.store.sales_management.edit_lead',
        compact('lead')
    );
}


// ================= UPDATE LEAD =================
public function updateLead(Request $request, $id)
{

        $leadId = Crypt::decryptString($id);
    $request->validate([

        'customer_name' => 'required|string|max:255',

        'phone' => 'nullable|string|max:50',

        'email' => 'nullable|email|max:255',

        'business_type' => 'nullable|string|max:100',

        'source' => 'nullable|string|max:100',

        'status' => 'required|in:pending,converted',

        'description' => 'nullable|string',

    ]);

    $lead = Lead::findOrFail($leadId);

    $lead->update([

        'customer_name' => $request->customer_name,

        'phone' => $request->phone,

        'email' => $request->email,

        'business_type' => $request->business_type,

        'source' => $request->source,

        'status' => $request->status,

        'description' => $request->description,

    ]);

    $this->swalMessage('success', 'Lead updated successfully');

    return redirect()
        ->route('sales.leads');
}


// ================= DELETE LEAD =================
public function deleteLead($id)
{
    try {

        $leadId = Crypt::decryptString($id);

    } catch (\Exception $e) {

        $this->swalMessage('error', 'Invalid lead link');

        return redirect()
            ->route('sales.leads');
    }

    $lead = Lead::findOrFail($leadId);

    $lead->delete();

    $this->swalMessage('success', 'Lead deleted successfully');

    return back();
}
// ================= FOLLOWUPS =================
public function followups()
{
    $customers = Customer::all();

    $followups = Followup::with('customer', 'user')
        ->latest()
        ->get();

    $completedFollowups = Followup::where('status', 'completed')->count();

    $pendingFollowups = Followup::where('status', 'pending')->count();

    $highPriorityFollowups = Followup::where('priority', 'High')->count();

    return view(
        'admin.store.sales_management.followups',
        compact(
            'customers',
            'followups',
            'completedFollowups',
            'pendingFollowups',
            'highPriorityFollowups'
        )
    );
}


// ================= STORE FOLLOWUP =================
public function storeFollowup(Request $request)
{
    $request->validate([
        'customer_id'   => 'required',
        'followup_date' => 'required',
        'priority'      => 'required',
        'notes'         => 'nullable',
    ]);

    Followup::create([
        'customer_id'   => $request->customer_id,
        'followup_date' => $request->followup_date,
        'priority'      => $request->priority,
        'notes'         => $request->notes,
        'status'        => $request->status ?? 'pending',
        'user_id'       => auth()->id(),
    ]);

    $this->swalMessage('success', 'Followup saved successfully');

    return redirect()->back();
}


// ================= EDIT FOLLOWUP =================
public function editFollowup($id)
{

        $followupId = Crypt::decryptString($id);

  
    $followup = Followup::with('customer', 'user')
        ->findOrFail($followupId);

    $customers = Customer::all();

    return view(
        'admin.store.sales_management.edit_followup',
        compact(
            'followup',
            'customers'
        )
    );
}


// ================= UPDATE FOLLOWUP =================
public function updateFollowup(Request $request, $id)
{
        $followupId = Crypt::decryptString($id);

    
    $request->validate([
        'customer_id'   => 'required',
        'followup_date' => 'required',
        'priority'      => 'required',
        'status'        => 'required',
        'notes'         => 'nullable',
    ]);

    $followup = Followup::findOrFail($followupId);

    $followup->update([
        'customer_id'   => $request->customer_id,
        'followup_date' => $request->followup_date,
        'priority'      => $request->priority,
        'status'        => $request->status,
        'notes'         => $request->notes,
    ]);

    $this->swalMessage('success', 'Followup updated successfully');

    return redirect()
        ->route('sales.followups');
}


// ================= DELETE FOLLOWUP =================
public function deleteFollowup($id)
{
    
    $followupId = Crypt::decryptString($id);
    $followup = Followup::findOrFail($followupId);

    $followup->delete();

    $this->swalMessage('success', 'Followup deleted successfully');

    return redirect()->back();
}

// ================= COMMUNICATIONS =================
public function communications()
{
    $customers = Customer::all();

    $communications = Communication::with('customer', 'user')
        ->latest()
        ->get();

    return view(
        'admin.store.sales_management.communications',
        compact(
            'customers',
            'communications'
        )
    );
}


// ================= STORE COMMUNICATION =================
public function storeCommunication(Request $request)
{
    $request->validate([
        'customer_id' => 'required',
        'type'        => 'required',
        'subject'     => 'required',
        'message'     => 'required',
    ]);

    Communication::create([

        'customer_id' => $request->customer_id,

        'type'        => $request->type,

        'subject'     => $request->subject,

        'message'     => $request->message,

        'status'      => 'Sent',

        'user_id'     => auth()->id(),

    ]);

    $this->swalMessage('success', 'Communication sent successfully');

    return redirect()
        ->back();
}


// ================= EDIT COMMUNICATION =================
public function editCommunication($id)
{
        $communicationId = Crypt::decryptString($id);


    $communication = Communication::with('customer', 'user')
        ->findOrFail($communicationId);

    $customers = Customer::all();

    return view(
        'admin.store.sales_management.edit_communication',
        compact(
            'communication',
            'customers'
        )
    );
}


// ================= UPDATE COMMUNICATION =================
public function updateCommunication(Request $request, $id)
{
   
        $communicationId = Crypt::decryptString($id);


    $request->validate([
        'customer_id' => 'required',
        'type'        => 'required',
        'subject'     => 'required',
        'message'     => 'required',
        'status'      => 'required',
    ]);

    $communication = Communication::findOrFail($communicationId);

    $communication->update([

        'customer_id' => $request->customer_id,

        'type'        => $request->type,

        'subject'     => $request->subject,

        'message'     => $request->message,

        'status'      => $request->status,

    ]);

    $this->swalMessage('success', 'Communication updated successfully');

    return redirect()
        ->route('sales.communications');
}


// ================= DELETE COMMUNICATION =================
public function deleteCommunication($id)
{

        $communicationId = Crypt::decryptString($id);


    $communication = Communication::findOrFail($communicationId);

    $communication->delete();

    $this->swalMessage('success', 'Communication deleted successfully');

    return redirect()
        ->back();
}

// ================= SALES PIPELINE =================
public function pipeline()
{
    // SUMMARY COUNTS
    $leads = Lead::count();

    $opportunities = Opportunity::count();

    $invoices = Invoice::count();

    $payments = Payment::count();

    $customers = Customer::count();

    // PIPELINE RECORDS
    $pipelines = SalesPipeline::with([
            'customer',
            'lead',
            'opportunity',
            'company',
            'businessUnit',
            'workPoint',
            'assignedUser',
            'creator'
        ])
        ->latest()
        ->get();

    // DASHBOARD
    $totalPipelines = $pipelines->count();

    $openPipelines = $pipelines->where('status', 'Open')->count();

    $inProgressPipelines = $pipelines->where('status', 'In Progress')->count();

    $completedPipelines = $pipelines->where('status', 'Completed')->count();

    $pipelineValue = $pipelines->sum('expected_value');

    $actualValue = $pipelines->sum('actual_value');

    // DROPDOWNS
    $customersList = Customer::orderBy('customer_name')->get();

    $leadsList = Lead::orderBy('customer_name')->get();

    $opportunitiesList = Opportunity::orderBy('opportunity_name')->get();

    $companies = CompanySite::orderBy('company_name')->get();

    $users = User::orderBy('name')->get();

    return view(
        'admin.store.sales_management.pipeline',
        compact(
            'leads',
            'opportunities',
            'invoices',
            'payments',
            'customers',

            'pipelines',
            'totalPipelines',
            'openPipelines',
            'inProgressPipelines',
            'completedPipelines',
            'pipelineValue',
            'actualValue',

            'customersList',
            'leadsList',
            'opportunitiesList',
            'companies',
            'users'
        )
    );
}


// ================= STORE PIPELINE =================
public function storePipeline(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'customer_id' => 'nullable|exists:customers,id',
        'lead_id' => 'nullable|exists:leads,id',
        'opportunity_id' => 'nullable|exists:opportunities,id',
        'company_id' => 'nullable|exists:company_sites,id',
        'business_unit_id' => 'nullable|exists:company_units,id',
        'work_point_id' => 'nullable|exists:work_points,id',
        'stage' => 'required|string',
        'status' => 'required|string',
        'expected_value' => 'nullable|numeric',
        'actual_value' => 'nullable|numeric',
        'probability' => 'nullable|integer|min:0|max:100',
        'expected_close_date' => 'nullable|date',
        'closed_date' => 'nullable|date',
        'assigned_to' => 'nullable|exists:users,id',
        'description' => 'nullable|string',
    ]);

    $lastId = SalesPipeline::max('id') + 1;

    $pipelineCode = 'PIPE-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

    SalesPipeline::create([
        'pipeline_code' => $pipelineCode,

        'title' => $request->title,

        'description' => $request->description,

        'customer_id' => $request->customer_id,

        'lead_id' => $request->lead_id,

        'opportunity_id' => $request->opportunity_id,

        'company_id' => $request->company_id,

        'business_unit_id' => $request->business_unit_id,

        'work_point_id' => $request->work_point_id,

        'stage' => $request->stage,

        'status' => $request->status,

        'expected_value' => $request->expected_value ?? 0,

        'actual_value' => $request->actual_value ?? 0,

        'probability' => $request->probability ?? 0,

        'expected_close_date' => $request->expected_close_date,

        'closed_date' => $request->closed_date,

        'assigned_to' => $request->assigned_to,

        'created_by' => auth()->id(),
    ]);

    $this->swalMessage('success', 'Pipeline saved successfully');

    return redirect()
        ->back();
}


// ================= EDIT PIPELINE =================
public function editPipeline($id)
{
    $pipelineId = Crypt::decryptString($id);

    $pipeline = SalesPipeline::with([
            'customer',
            'lead',
            'opportunity',
            'company',
            'businessUnit',
            'workPoint',
            'assignedUser',
            'creator'
        ])
        ->findOrFail($pipelineId);

    $customersList = Customer::orderBy('customer_name')->get();

    $leadsList = Lead::orderBy('customer_name')->get();

    $opportunitiesList = Opportunity::orderBy('opportunity_name')->get();

    $companies = CompanySite::orderBy('company_name')->get();

    $users = User::orderBy('name')->get();

    return view(
        'admin.store.sales_management.edit_pipeline',
        compact(
            'pipeline',
            'customersList',
            'leadsList',
            'opportunitiesList',
            'companies',
            'users'
        )
    );
}


// ================= UPDATE PIPELINE =================
public function updatePipeline(Request $request, $id)
{
    $pipelineId = Crypt::decryptString($id);

    $request->validate([
        'title' => 'required|string|max:255',
        'customer_id' => 'nullable|exists:customers,id',
        'lead_id' => 'nullable|exists:leads,id',
        'opportunity_id' => 'nullable|exists:opportunities,id',
        'company_id' => 'nullable|exists:company_sites,id',
        'business_unit_id' => 'nullable|exists:company_units,id',
        'work_point_id' => 'nullable|exists:work_points,id',
        'stage' => 'required|string',
        'status' => 'required|string',
        'expected_value' => 'nullable|numeric',
        'actual_value' => 'nullable|numeric',
        'probability' => 'nullable|integer|min:0|max:100',
        'expected_close_date' => 'nullable|date',
        'closed_date' => 'nullable|date',
        'assigned_to' => 'nullable|exists:users,id',
        'description' => 'nullable|string',
    ]);

    $pipeline = SalesPipeline::findOrFail($pipelineId);

    $pipeline->update([
        'title' => $request->title,

        'description' => $request->description,

        'customer_id' => $request->customer_id,

        'lead_id' => $request->lead_id,

        'opportunity_id' => $request->opportunity_id,

        'company_id' => $request->company_id,

        'business_unit_id' => $request->business_unit_id,

        'work_point_id' => $request->work_point_id,

        'stage' => $request->stage,

        'status' => $request->status,

        'expected_value' => $request->expected_value ?? 0,

        'actual_value' => $request->actual_value ?? 0,

        'probability' => $request->probability ?? 0,

        'expected_close_date' => $request->expected_close_date,

        'closed_date' => $request->closed_date,

        'assigned_to' => $request->assigned_to,

        'updated_by' => auth()->id(),
    ]);

    $this->swalMessage('success', 'Pipeline updated successfully');

    return redirect()
        ->route('sales.pipeline');
}


// ================= DELETE PIPELINE =================
public function deletePipeline($id)
{
    $pipelineId = Crypt::decryptString($id);

    $pipeline = SalesPipeline::findOrFail($pipelineId);

    $pipeline->delete();

    $this->swalMessage('success', 'Pipeline deleted successfully');

    return redirect()
        ->back();
}

public function customerLedger()
{
    $ledgers = CustomerLedger::with([
            'customer'
        ])
        ->latest()
        ->get();

    // ================= SUMMARY =================

    $totalInvoice = CustomerLedger::sum(
        'invoice_amount'
    );

    $totalPaid = CustomerLedger::sum(
        'paid_amount'
    );

    $totalBalance = CustomerLedger::sum(
        'balance'
    );

    $totalCustomers = CustomerLedger::distinct(
            'customer_id'
        )
        ->count('customer_id');

    $unpaidCustomers = CustomerLedger::where(
            'balance',
            '>',
            0
        )
        ->distinct('customer_id')
        ->count('customer_id');

    return view(
        'admin.store.sales_management.customer_ledger',
        compact(
            'ledgers',

            'totalInvoice',
            'totalPaid',
            'totalBalance',

            'totalCustomers',
            'unpaidCustomers'
        )
    );
}


public function crmReports()
{
    $customers = Customer::count();

    $sales = Invoice::sum('total');

    $debts = CustomerLedger::sum('balance');

    $leads = Lead::count();

    $payments = CustomerPayment::sum('amount');

    $campaigns = Campaign::count();

    $activities = Activity::count();

    return view(
        'admin.store.sales_management.reports',
        compact(
            'customers',
            'sales',
            'debts',
            'leads',
            'payments',
            'campaigns',
            'activities'
        )
    );
}

public function exportCustomerLedgerExcel()
{
    return Excel::download(
        new CustomerLedgerExport,
        'customer_ledger.xlsx'
    );
}

public function printCustomerLedger()
{
    $ledgers = CustomerLedger::with('customer')
        ->latest()
        ->get();

    return view(
        'admin.store.sales_management.print_customer_ledger',
        compact('ledgers')
    );
}



public function stockLedger()
{
    $products = Product::all();

    $rows = StockLedger::with('product')
        ->latest()
        ->get();

    $itemStocks = StockLedger::selectRaw('
            product_id,
            SUM(qty_in) as total_received,
            SUM(qty_out) as total_used,
            SUM(balance) as total_available
        ')
        ->groupBy('product_id')
        ->with('product')
        ->get();

    $stats = [

        'total_in' => StockLedger::sum('qty_in'),

        'total_out' => StockLedger::sum('qty_out'),

        'balance' => StockLedger::sum('balance'),

    ];

    return view(
        'admin.store.stock.management.partials.ledger',
        compact(
            'products',
            'rows',
            'itemStocks',
            'stats'
        )
    );
}

public function storeRequests()
{
    $rows = GeneralSupplyRequest::with([

            'item',
            'description',
            'department',
            'section',
            'workpoint'

        ])
        ->latest()
        ->get();

    return view(
        'admin.store.requested',
        compact('rows')
    );
}

public function exportStockExcel()
{
    $this->swalMessage('success', 'Excel export coming soon');

    return back();
}

public function exportStockPdf()
{
    $this->swalMessage('success', 'PDF export coming soon');

    return back();
}

}