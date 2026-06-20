<?php

namespace App\Http\Controllers;
use App\Models\CompanySite;
use App\Models\WorkPoint;
use App\Models\Company_unit;
use App\Models\RawMaterial;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Contract;
use App\Models\RawMaterialPurchase;
use App\Models\RawMaterialStock;
use App\Models\RawMaterialIssue;
use App\Models\RawMaterialRequest;
use App\Models\ManufacturingReceipt;
use App\Models\StockAudit;
use App\Models\StockAuditItem;
use App\Models\User;
use App\Models\Department;
use App\Models\Section;
use App\Models\CstmOrder;
use App\Models\CstmProduct;
use App\Models\Product;
use App\Models\CstmSply;
use App\Models\Contact;
use App\Models\MarketingCampaign;
use App\Models\CampaignResponse;
use App\Models\Opportunity;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Activity;
use App\Models\GeneralSupplyItem;
use App\Models\GeneralSupplyItemDescription;
use App\Models\GeneralSupplyReceiving;
use App\Models\GeneralSupplyStock;
use App\Models\GeneralSupplyRequest;
use App\Models\GeneralSupplyIssue;
use App\Models\AccntChart;
use App\Models\AccntSubchart;
use App\Models\AccntTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Image;
use App;
use Carbon\Carbon;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
class SalesController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
        // parent::__construct();
    }
    protected function isSuperRole()
    {
        return in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer'], true);
    }
     // Sales & Marketing
    public function salesMarketing()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $companyId = $user->company_id;
        $workPoint = $isSuper ? null : $user->work_point_id;

        // Customers count
        $customersQuery = CstmSply::where('company_id', $companyId)->where('category', 'Customer')->where('status', '!=', 'Deleted');
        if (!$isSuper) $customersQuery->where('work_point_id', $workPoint);
        $customersCount = $customersQuery->count();

        // Orders (sales)
        $ordersQuery = CstmOrder::where('company_id', $companyId)->where('type', 'sale')->where('status', '!=', 'Cancelled');
        if (!$isSuper) $ordersQuery->where('work_point_id', $workPoint);
        $totalOrders = $ordersQuery->count();
        $totalSalesAmount = (float) $ordersQuery->sum('total_amount');

        // Sales this month / year
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $startOfYear = Carbon::now()->startOfYear()->toDateString();
        $salesThisMonth = (float) CstmOrder::where('company_id', $companyId)
            ->where('type','sale')->where('status','!=','Cancelled')->where('order_date', '>=', $startOfMonth)
            ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))->sum('total_amount');

        $salesThisYear = (float) CstmOrder::where('company_id', $companyId)
            ->where('type','sale')->where('status','!=','Cancelled')->where('order_date', '>=', $startOfYear)
            ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))->sum('total_amount');

        // Recent orders (5)
        $recentOrders = CstmOrder::with('customer')->where('company_id', $companyId)->where('type','sale')
            ->orderBy('order_date','desc')->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
            ->limit(6)->get();
        // Top customers (by total_amount)
        $topCustomers = CstmOrder::select('cstm_id', DB::raw('SUM(total_amount) as total'))
            ->where('company_id', $companyId) ->where('type','sale')->where('status','!=','Cancelled')
            ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
            ->groupBy('cstm_id')->orderByDesc('total')->with('customer')->limit(6) ->get();

        // Sales by product (top 6) - from cstm_products table
        $salesByProduct = DB::table('cstm_products as cp')->leftJoin('products', 'cp.product_id', '=', 'products.id')
            ->select('cp.product_id','products.product_name',
                DB::raw('SUM(cp.total_price) as total_sales'),DB::raw('SUM(cp.quantity) as sold_qty')
            )->where('cp.company_id', $companyId)->when(!$isSuper, fn($q)=> $q->where('cp.work_point_id', $workPoint))
            ->groupBy('cp.product_id', 'products.product_name')->orderByDesc('total_sales')->limit(8)->get();

        // Chart: sales by month (last 6 months)
        $months = collect();
        $salesData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $label = $dt->format('M Y');
            $months->push($label);
            $from = $dt->copy()->startOfMonth()->toDateString();
            $to = $dt->copy()->endOfMonth()->toDateString();
            $sum = (float) CstmOrder::where('company_id', $companyId)
                ->where('type','sale')->where('status','!=','Cancelled')
                ->whereBetween('order_date', [$from, $to])
                ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
                ->sum('total_amount');
            $salesData->push($sum);
        }

        // Campaign performance: number of responses & interested count
        $campaigns = MarketingCampaign::where('company_id', $companyId)->where('status','!=','Deleted')
            ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))->get();
        $campaignStats = [];
        foreach ($campaigns as $c) {
            $responses = CampaignResponse::where('marketing_campaign_id',$c->id)
                ->where('company_id',$companyId)
                ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
                ->count();
            $interested = CampaignResponse::where('marketing_campaign_id',$c->id)
                ->where('response_type','LIKE','%interest%')
                ->where('company_id',$companyId)
                ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
                ->count();
            $campaignStats[] = [
                'campaign' => $c,
                'responses' => $responses,
                'interested' => $interested,
            ];
        }
        // Quotes summary
        $quotesCount = Quote::where('company_id', $companyId)
            ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
            ->where('status','!=','Deleted')->count();

        // Activities pending
        $activitiesPending = Activity::where('company_id', $companyId)
            ->where('status','Pending')
            ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
            ->count();

        // Return view
        return view('admin.home.sales', [
            'customersCount' => $customersCount,'totalOrders' => $totalOrders,
            'totalSalesAmount' => $totalSalesAmount,'salesThisMonth' => $salesThisMonth,
            'salesThisYear' => $salesThisYear,'recentOrders' => $recentOrders,
            'topCustomers' => $topCustomers, 'salesByProduct' => $salesByProduct,
            'graphLabels' => $months->values()->all(),'graphSalesData' => $salesData->values()->all(),
            'campaignStats' => collect($campaignStats),
            'quotesCount' => $quotesCount,'activitiesPending' => $activitiesPending,
        ]);
    }
    /**
     * Show Contacts page
     */
    public function contacts()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        if ($isSuper) {
            $contacts = Contact::with(['company','workpoint','customer'])
                ->where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('first_name')->get();

            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        } else {
            $contacts = Contact::with(['company','workpoint','customer'])
                ->where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('status', '!=', 'Deleted')->orderBy('first_name')->get();

            $workPoints = collect();
        }

        // customers to choose from when creating contacts
        $customers = CstmSply::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('customer_name')->get();

        $companies = CompanySite::where('id', $user->company_id)->get();

        return view('admin.sales.contacts', compact('contacts','workPoints','companies','customers'));
    }

    /**
     * Store contact
     */
    public function storeContact(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', Rule::in(['Active','Inactive','Deleted'])],
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $companyId = $user->company_id;
        $workPointId = $isSuper ? $request->work_point_id : $user->work_point_id;

        Contact::create([
            'user_id' => $user->id,
            'company_id' => $companyId,
            'work_point_id' => $workPointId,
            'cstm_id' => $request->cstm_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'job_title' => $request->job_title,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
            'status' => $request->status ?? 'Active',
        ]);

        Alert::success('Success','Contact created successfully.');
        return redirect()->route('sales.contacts.index');
    }

    /**
     * Update contact
     */
    public function updateContact(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $contact = Contact::findOrFail($decrypted);

        if ($contact->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot edit contacts from other companies.');
            return back();
        }

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['Active','Inactive','Deleted'])],
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $contact->update([
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'cstm_id' => $request->cstm_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'job_title' => $request->job_title,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        Alert::success('Success','Contact updated successfully.');
        return redirect()->route('sales.contacts.index');
    }

    /**
     * Remove (soft) contact
     */
    public function removeContact($id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $contact = Contact::findOrFail($decrypted);
        if ($contact->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot remove contacts from other companies.');
            return back();
        }
        $contact->update(['status' => 'Deleted']);
        Alert::success('Success','Contact removed successfully.');
        return redirect()->route('sales.contacts.index');
    }
    /**
     * Show Marketing Campaigns page
     */
    public function campaigns()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        if ($isSuper) {
            $campaigns = MarketingCampaign::with(['company','workpoint'])
                ->where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('start_date','desc')->get();

            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        } else {
            $campaigns = MarketingCampaign::with(['company','workpoint'])
                ->where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('status', '!=', 'Deleted')->orderBy('start_date','desc')->get();

            $workPoints = collect();
        }

        $companies = CompanySite::where('id', $user->company_id)->get();

        return view('admin.sales.campaigns', compact('campaigns','workPoints','companies'));
    }

    /**
     * Store Marketing Campaign
     */
    public function storeCampaign(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'name' => ['required','string','max:255'],
            'objective' => ['nullable','string'],
            'start_date' => ['nullable','date'],
            'end_date' => ['nullable','date'],
            'status' => ['nullable', Rule::in(['Planned','Running','Completed','Cancelled','Deleted'])],
            'budget' => ['nullable','numeric'],
            'actual_cost' => ['nullable','numeric'],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        MarketingCampaign::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'name' => $request->name,
            'objective' => $request->objective,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status ?? 'Planned',
            'budget' => $request->budget,
            'actual_cost' => $request->actual_cost,
        ]);

        Alert::success('Success','Marketing campaign created successfully.');
        return redirect()->route('sales.campaigns.index');
    }

    /**
     * Update Marketing Campaign
     */
    public function updateCampaign(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $campaign = MarketingCampaign::findOrFail($decrypted);

        if ($campaign->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot edit campaigns from other companies.');
            return back();
        }

        $rules = [
            'name' => ['required','string','max:255'],
            'objective' => ['nullable','string'],
            'start_date' => ['nullable','date'],
            'end_date' => ['nullable','date'],
            'status' => ['required', Rule::in(['Planned','Running','Completed','Cancelled','Deleted'])],
            'budget' => ['nullable','numeric'],
            'actual_cost' => ['nullable','numeric'],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $campaign->update([
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'name' => $request->name,
            'objective' => $request->objective,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
            'budget' => $request->budget,
            'actual_cost' => $request->actual_cost,
        ]);

        Alert::success('Success','Marketing campaign updated successfully.');
        return redirect()->route('sales.campaigns.index');
    }

    /**
     * Remove (soft) Marketing Campaign
     */
    public function removeCampaign($id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $campaign = MarketingCampaign::findOrFail($decrypted);
        if ($campaign->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot remove campaigns from other companies.');
            return back();
        }

        $campaign->update(['status' => 'Deleted']);
        Alert::success('Success','Marketing campaign removed successfully.');
        return redirect()->route('sales.campaigns.index');
    }

    /**
     * Show Campaign Responses page
     */
    public function campaignResponses()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        if ($isSuper) {
            $responses = CampaignResponse::with(['campaign','customer','contact','user'])
                ->where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('response_date','desc')->get();

            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        } else {
            $responses = CampaignResponse::with(['campaign','customer','contact','user'])
                ->where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('status', '!=', 'Deleted')->orderBy('response_date','desc')->get();

            $workPoints = collect();
        }

        $campaigns = MarketingCampaign::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')->orderBy('name')->get();

        $customers = CstmSply::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')->orderBy('customer_name')->get();

        $contacts = Contact::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')->orderBy('first_name')->get();

        $companies = CompanySite::where('id', $user->company_id)->get();

        return view('admin.sales.campaign_responses', compact('responses','workPoints','companies','campaigns','customers','contacts'));
    }

    /**
     * Store Campaign Response
     */
    public function storeCampaignResponse(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'marketing_campaign_id' => ['nullable','integer', Rule::exists('marketing_campaigns','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
            'contact_id' => ['nullable','integer', Rule::exists('contacts','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
            'response_type' => ['nullable','string','max:255'],
            'notes' => ['nullable','string'],
            'response_date' => ['nullable','date'],
            'status' => ['nullable', Rule::in(['New','Processed','Deleted'])],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        CampaignResponse::create([
            'marketing_campaign_id' => $request->marketing_campaign_id,
            'user_id' => $user->id,
            'cstm_id' => $request->cstm_id,
            'contact_id' => $request->contact_id,
            'response_type' => $request->response_type,
            'notes' => $request->notes,
            'response_date' => $request->response_date,
            'status' => $request->status ?? 'New',
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
        ]);

        Alert::success('Success','Campaign response recorded successfully.');
        return redirect()->route('sales.campaignresponses.index');
    }

    /**
     * Update Campaign Response
     */
    public function updateCampaignResponse(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $response = CampaignResponse::findOrFail($decrypted);

        if ($response->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot edit responses from other companies.');
            return back();
        }

        $rules = [
            'marketing_campaign_id' => ['nullable','integer', Rule::exists('marketing_campaigns','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
            'contact_id' => ['nullable','integer', Rule::exists('contacts','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
            'response_type' => ['nullable','string','max:255'],
            'notes' => ['nullable','string'],
            'response_date' => ['nullable','date'],
            'status' => ['required', Rule::in(['New','Processed','Deleted'])],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $response->update([
            'marketing_campaign_id' => $request->marketing_campaign_id,
            'cstm_id' => $request->cstm_id,
            'contact_id' => $request->contact_id,
            'response_type' => $request->response_type,
            'notes' => $request->notes,
            'response_date' => $request->response_date,
            'status' => $request->status,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
        ]);

        Alert::success('Success','Campaign response updated successfully.');
        return redirect()->route('sales.campaignresponses.index');
    }

    /**
     * Remove (soft) Campaign Response
     */
    public function removeCampaignResponse($id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $response = CampaignResponse::findOrFail($decrypted);
        if ($response->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot remove responses from other companies.');
            return back();
        }

        $response->update(['status' => 'Deleted']);
        Alert::success('Success','Campaign response removed successfully.');
        return redirect()->route('sales.campaignresponses.index');
    }
    // Show Opportunities
    public function opportunities()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        if ($isSuper) {
            $opps = Opportunity::with(['company','workpoint','customer','assignedTo'])
                ->where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('created_at','desc')->get();
            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        } else {
            $opps = Opportunity::with(['company','workpoint','customer','assignedTo'])
                ->where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('status', '!=', 'Deleted')->orderBy('created_at','desc')->get();

            $workPoints = collect();
        }

        $customers = CstmSply::where('company_id', $user->company_id)->where('status','!=','Deleted')->orderBy('customer_name')->get();
        $users = User::where('company_id', $user->company_id)->orderBy('name')->get();
        $companies = CompanySite::where('id', $user->company_id)->get();

        return view('admin.sales.opportunities', compact('opps','workPoints','companies','customers','users'));
    }

    // Store Opportunity
    public function storeOpportunity(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'opportunity_name' => ['required','string','max:255'],
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
            'estimated_value' => ['nullable','numeric'],
            'close_expected' => ['nullable','date'],
            'stage' => ['nullable', Rule::in(['Prospecting','Qualification','Proposal','Negotiation','Closed Won','Closed Lost','On Hold'])],
            'status' => ['nullable', Rule::in(['Open','Won','Lost','Deleted'])],
            'assigned_to' => ['nullable','integer', Rule::exists('users','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Opportunity::create([
            'opportunity_name' => $request->opportunity_name,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'cstm_id' => $request->cstm_id,
            'estimated_value' => $request->estimated_value,
            'close_expected' => $request->close_expected,
            'stage' => $request->stage ?? 'Prospecting',
            'status' => $request->status ?? 'Open',
            'assigned_to' => $request->assigned_to,
            'description' => $request->description,
        ]);

        Alert::success('Success','Opportunity created successfully.');
        return redirect()->route('sales.opportunities.index');
    }

    // Update Opportunity
    public function updateOpportunity(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $opp = Opportunity::findOrFail($decrypted);

        if ($opp->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot edit opportunities from other companies.');
            return back();
        }

        $rules = [
            'opportunity_name' => ['required','string','max:255'],
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
            'estimated_value' => ['nullable','numeric'],
            'close_expected' => ['nullable','date'],
            'stage' => ['required', Rule::in(['Prospecting','Qualification','Proposal','Negotiation','Closed Won','Closed Lost','On Hold'])],
            'status' => ['required', Rule::in(['Open','Won','Lost','Deleted'])],
            'assigned_to' => ['nullable','integer', Rule::exists('users','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user){
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $opp->update([
            'opportunity_name' => $request->opportunity_name,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'cstm_id' => $request->cstm_id,
            'estimated_value' => $request->estimated_value,
            'close_expected' => $request->close_expected,
            'stage' => $request->stage,
            'status' => $request->status,
            'assigned_to' => $request->assigned_to,
            'description' => $request->description,
        ]);

        Alert::success('Success','Opportunity updated successfully.');
        return redirect()->route('sales.opportunities.index');
    }

    // Remove (soft)
    public function removeOpportunity($id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $opp = Opportunity::findOrFail($decrypted);
        if ($opp->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot remove opportunities from other companies.');
            return back();
        }

        $opp->update(['status' => 'Deleted']);
        Alert::success('Success','Opportunity removed successfully.');
        return redirect()->route('sales.opportunities.index');
    }

    // Close / win or lose
    public function closeOpportunity($id, $result)
    {
        // $result expected 'won' or 'lost'
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }
        $user = auth()->user();
        $opp = Opportunity::findOrFail($decrypted);
        if ($opp->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot change opportunities from other companies.');
            return back();
        }
        if (!in_array(strtolower($result), ['won','lost'])) {
            Alert::error('Error','Invalid result.');
            return back();
        }
        $opp->update(['status' => $result === 'won' ? 'Won' : 'Lost']);
        Alert::success('Success','Opportunity marked as '.($result === 'won' ? 'Won' : 'Lost').'.');
        return redirect()->route('sales.opportunities.index');
    }
    // Show Quotes
    public function quotes()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        if ($isSuper) {
            $quotes = Quote::with(['company','workpoint','customer','items'])
                ->where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('created_at','desc')->get();
            $workPoints = WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
        } else {
            $quotes = Quote::with(['company','workpoint','customer','items'])
                ->where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('status', '!=', 'Deleted')->orderBy('created_at','desc')->get();
            $workPoints = collect();
        }

        $customers = CstmSply::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
        $companies = CompanySite::where('id', $user->company_id)->get();

        return view('admin.sales.quotes', compact('quotes','workPoints','companies','customers'));
    }

    // Store Quote
    public function storeQuote(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'quote_number' => ['nullable','string','max:255'],
            'quote_date' => ['nullable','date'],
            'expiry_date' => ['nullable','date'],
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })],
            'sub_total' => ['nullable','numeric'],
            'tax' => ['nullable','numeric'],
            'discount' => ['nullable','numeric'],
            'total' => ['nullable','numeric'],
            'status' => ['nullable', Rule::in(['Draft','Sent','Accepted','Rejected','Expired','Deleted'])],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) { return back()->withErrors($validator)->withInput(); }

        $quote = Quote::create([
            'quote_number' => $request->quote_number,
            'quote_date' => $request->quote_date,
            'expiry_date' => $request->expiry_date,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper? $request->work_point_id : $user->work_point_id,
            'cstm_id' => $request->cstm_id,
            'sub_total' => $request->sub_total ?? 0,
            'tax' => $request->tax ?? 0,
            'discount' => $request->discount ?? 0,
            'total' => $request->total ?? 0,
            'status' => $request->status ?? 'Draft',
        ]);

        // optional: handle items if provided as arrays: product_name[], quantity[], unit_price[], unit[]
        if ($request->has('item_product_name') && is_array($request->item_product_name)) {
            foreach ($request->item_product_name as $i => $name) {
                $qty = $request->item_quantity[$i] ?? 0;
                $unit = $request->item_unit[$i] ?? null;
                $unit_price = $request->item_unit_price[$i] ?? 0;
                $total_price = ($qty * $unit_price);
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'product_name' => $name,
                    'quantity' => $qty,
                    'unit' => $unit,
                    'unit_price' => $unit_price,
                    'total_price' => $total_price
                ]);
            }
        }

        Alert::success('Success','Quote created successfully.');
        return redirect()->route('sales.quotes.index');
    }

    // Update Quote
    public function updateQuote(Request $request, $id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid identifier'); return back(); }

        $user = auth()->user();
        $quote = Quote::findOrFail($decrypted);
        if ($quote->company_id !== $user->company_id) { Alert::error('Unauthorized','You cannot edit quotes from other companies.'); return back(); }

        $rules = [
            'quote_number' => ['nullable','string','max:255'],
            'quote_date' => ['nullable','date'],
            'expiry_date' => ['nullable','date'],
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })],
            'sub_total' => ['nullable','numeric'],
            'tax' => ['nullable','numeric'],
            'discount' => ['nullable','numeric'],
            'total' => ['nullable','numeric'],
            'status' => ['required', Rule::in(['Draft','Sent','Accepted','Rejected','Expired','Deleted'])],
        ];

        if ($this->isSuperRole()) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) { return back()->withErrors($validator)->withInput(); }

        $quote->update([
            'quote_number' => $request->quote_number,
            'quote_date' => $request->quote_date,
            'expiry_date' => $request->expiry_date,
            'company_id' => $user->company_id,
            'work_point_id' => $this->isSuperRole() ? $request->work_point_id : $user->work_point_id,
            'cstm_id' => $request->cstm_id,
            'sub_total' => $request->sub_total ?? 0,
            'tax' => $request->tax ?? 0,
            'discount' => $request->discount ?? 0,
            'total' => $request->total ?? 0,
            'status' => $request->status,
        ]);

        // Optional: update items — for simplicity delete existing and recreate if arrays provided
        if ($request->has('item_product_name') && is_array($request->item_product_name)) {
            $quote->items()->delete();
            foreach ($request->item_product_name as $i => $name) {
                $qty = $request->item_quantity[$i] ?? 0;
                $unit = $request->item_unit[$i] ?? null;
                $unit_price = $request->item_unit_price[$i] ?? 0;
                $total_price = ($qty * $unit_price);
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'product_name' => $name,
                    'quantity' => $qty,
                    'unit' => $unit,
                    'unit_price' => $unit_price,
                    'total_price' => $total_price
                ]);
            }
        }

        Alert::success('Success','Quote updated successfully.');
        return redirect()->route('sales.quotes.index');
    }

    // Remove Quote (soft)
    public function removeQuote($id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid identifier'); return back(); }
        $user = auth()->user();
        $quote = Quote::findOrFail($decrypted);
        if ($quote->company_id !== $user->company_id) { Alert::error('Unauthorized','You cannot remove quotes from other companies.'); return back(); }
        $quote->update(['status' => 'Deleted']);
        Alert::success('Success','Quote removed successfully.');
        return redirect()->route('sales.quotes.index');
    }
    // Show Activities
    public function activities()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        if ($isSuper) {
            $activities = Activity::with(['user','opportunity','customer','assignedTo'])
                ->where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('due_at','desc')->get();

            $workPoints = WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
        } else {
            $activities = Activity::with(['user','opportunity','customer','assignedTo'])
                ->where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('status', '!=', 'Deleted')->orderBy('due_at','desc')->get();
            $workPoints = collect();
        }
        $opps = Opportunity::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
        $customers = CstmSply::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
        $users = User::where('company_id', $user->company_id)->get();
        $companies = CompanySite::where('id', $user->company_id)->get();
        return view('admin.sales.activities', compact('activities','workPoints','companies','opps','customers','users'));
    }
    // Store Activity
    public function storeActivity(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'type' => ['required','string','max:50'],
            'subject' => ['nullable','string','max:255'],
            'body' => ['nullable','string'],
            'due_at' => ['nullable','date'],
            'status' => ['nullable', Rule::in(['Pending','Done','Cancelled','Deleted'])],
            'opportunity_id' => ['nullable','integer', Rule::exists('opportunities','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })],
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })],
            'assigned_to' => ['nullable','integer', Rule::exists('users','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) { return back()->withErrors($validator)->withInput(); }

        Activity::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'opportunity_id' => $request->opportunity_id,
            'cstm_id' => $request->cstm_id,
            'cstm_order_id' => $request->cstm_order_id,
            'type' => $request->type,
            'subject' => $request->subject,
            'body' => $request->body,
            'due_at' => $request->due_at,
            'status' => $request->status ?? 'Pending',
            'assigned_to' => $request->assigned_to,
        ]);

        Alert::success('Success','Activity created successfully.');
        return redirect()->route('sales.activities.index');
    }

    // Update Activity
    public function updateActivity(Request $request, $id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid identifier'); return back(); }

        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $act = Activity::findOrFail($decrypted);
        if ($act->company_id !== $user->company_id) { Alert::error('Unauthorized','You cannot edit activities from other companies.'); return back(); }

        $rules = [
            'type' => ['required','string','max:50'],
            'subject' => ['nullable','string','max:255'],
            'body' => ['nullable','string'],
            'due_at' => ['nullable','date'],
            'status' => ['required', Rule::in(['Pending','Done','Cancelled','Deleted'])],
            'opportunity_id' => ['nullable','integer', Rule::exists('opportunities','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })],
            'cstm_id' => ['nullable','integer', Rule::exists('cstm_splies','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })],
            'assigned_to' => ['nullable','integer', Rule::exists('users','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) { return back()->withErrors($validator)->withInput(); }

        $act->update([
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'opportunity_id' => $request->opportunity_id,
            'cstm_id' => $request->cstm_id,
            'cstm_order_id' => $request->cstm_order_id,
            'type' => $request->type,
            'subject' => $request->subject,
            'body' => $request->body,
            'due_at' => $request->due_at,
            'status' => $request->status,
            'assigned_to' => $request->assigned_to,
        ]);

        Alert::success('Success','Activity updated successfully.');
        return redirect()->route('sales.activities.index');
    }

    // Remove activity (soft)
    public function removeActivity($id)
    {
        try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid identifier'); return back(); }
        $user = auth()->user();
        $act = Activity::findOrFail($decrypted);
        if ($act->company_id !== $user->company_id) { Alert::error('Unauthorized','You cannot remove activities from other companies.'); return back(); }
        $act->update(['status' => 'Deleted']);
        Alert::success('Success','Activity removed successfully.');
        return redirect()->route('sales.activities.index');
    }
    /**
     * Reports index (list of available reports)
     */
    public function reportsIndex()
    {
        $user = auth()->user();
        $companies = CompanySite::where('id',$user->company_id)->get();
        return view('admin.sales.reports_index', compact('companies'));
    }
    /**
     * Sales Summary Report
     */
    public function salesSummary(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $isSuper = $this->isSuperRole();
        $workPoint = $isSuper ? null : $user->work_point_id;

        $from = $request->from ?? Carbon::now()->startOfMonth()->toDateString();
        $to = $request->to ?? Carbon::now()->toDateString();

        $orders = CstmOrder::with('customer')
            ->where('company_id', $companyId)
            ->where('type','sale')
            ->whereBetween('order_date', [$from, $to])
            ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
            ->orderBy('order_date','desc')
            ->get();

        $total = $orders->sum('total_amount');
        return view('admin.sales.reports.sales_summary', compact('orders','total','from','to'));
    }

    /**
     * Top customers report
     */
    public function topCustomers(Request $request)
    {
        $user = auth()->user(); $companyId = $user->company_id; $isSuper = $this->isSuperRole(); $workPoint = $isSuper ? null : $user->work_point_id;

        $top = CstmOrder::select('cstm_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total_amount) as total_amount'))
            ->where('company_id', $companyId)->where('type','sale')->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
            ->groupBy('cstm_id')->orderByDesc('total_amount')->with('customer')->limit(50)->get();

        return view('admin.sales.reports.top_customers', compact('top'));
    }

    /**
     * Sales by product report
     */
    public function salesByProduct(Request $request)
    {
        $user = auth()->user(); $companyId = $user->company_id; $isSuper = $this->isSuperRole(); $workPoint = $isSuper ? null : $user->work_point_id;

        $byProduct = DB::table('cstm_products')
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total_price) as total'))
            ->where('company_id', $companyId)
            ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
            ->groupBy('product_id','product_name')
            ->orderByDesc('total')
            ->get();

        return view('admin.sales.reports.by_product', compact('byProduct'));
    }
    /**
     * Campaign performance report
     */
    public function campaignPerformance(Request $request)
    {
        $user = auth()->user(); $companyId = $user->company_id; $isSuper = $this->isSuperRole(); $workPoint = $isSuper ? null : $user->work_point_id;

        $campaigns = MarketingCampaign::where('company_id',$companyId)
            ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))->get();

        $data = [];
        foreach ($campaigns as $c) {
            $responses = CampaignResponse::where('marketing_campaign_id', $c->id)
                ->where('company_id',$companyId)
                ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
                ->count();
            $interested = CampaignResponse::where('marketing_campaign_id', $c->id)
                ->where('company_id',$companyId)
                ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
                ->where('response_type','LIKE','%interest%')
                ->count();
            $data[] = [
                'campaign' => $c,
                'responses' => $responses,
                'interested' => $interested
            ];
        }

        return view('admin.sales.reports.campaign_performance', compact('data'));
    }

    /**
     * Quotes report
     */
    public function quotesReport(Request $request)
    {
        $user = auth()->user(); $companyId = $user->company_id; $isSuper = $this->isSuperRole(); $workPoint = $isSuper ? null : $user->work_point_id;

        $quotes = Quote::with('customer')
            ->where('company_id', $companyId)
            ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
            ->orderBy('quote_date','desc')->get();

        return view('admin.sales.reports.quotes', compact('quotes'));
    }

    /**
     * Activities report
     */
    public function activitiesReport(Request $request)
    {
        $user = auth()->user(); $companyId = $user->company_id; $isSuper = $this->isSuperRole(); $workPoint = $isSuper ? null : $user->work_point_id;

        $activities = Activity::with(['user','assignedTo'])
            ->where('company_id', $companyId)
            ->when(!$isSuper, fn($q)=>$q->where('work_point_id', $workPoint))
            ->orderBy('due_at','desc')->get();

        return view('admin.sales.reports.activities', compact('activities'));
    }

    // ================= GENERAL SUPPLY HELPERS =================
protected function gsGlobalRoles(): array
{
    return ['Admin', 'CEO', 'Managing Director (MD)', 'Admin-Developer'];
}

protected function gsCanAll($user): bool
{
    return in_array($user->role, $this->gsGlobalRoles(), true) || $user->can('View-General-Supply-All');
}

protected function gsCanCompany($user): bool
{
    return $user->can('View-General-Supply-Company') || $user->role === 'Company Manager';
}

protected function gsCanUnit($user): bool
{
    return $user->can('View-General-Supply-Unit') || $user->role === 'Unit Manager';
}

protected function gsAllowedWorkPoints($user)
{
    if ($this->gsCanAll($user)) {
        return WorkPoint::where('status', '!=', 'Deleted')->orderBy('work_name')->get();
    }

    if ($this->gsCanCompany($user)) {
        return WorkPoint::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('work_name')
            ->get();
    }

    if ($this->gsCanUnit($user)) {
        return WorkPoint::where('company_id', $user->company_id)
            ->where('comp_unit_id', $user->comp_unit_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('work_name')
            ->get();
    }

    return WorkPoint::where('id', $user->work_point_id)->get();
}


// ================= ITEMS =================
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
        return back();
    }
}

// ================= DESCRIPTIONS =================
public function gsDescriptionsIndex()
{
    $items = GeneralSupplyItem::where('status', 'Active')->orderBy('item_name')->get();
    $descriptions = GeneralSupplyItemDescription::with('item')->orderByDesc('id')->get();

    return view('admin.store.descriptions', compact('items', 'descriptions'));
}

public function gsDescriptionsStore(Request $request)
{
    try {
        $request->validate([
            'item_id' => 'required|exists:general_supply_items,id',
            'description_name' => 'required|string|max:255',
            'unit_name' => 'required|string|max:100',
            'status' => 'required|in:Active,Inactive',
        ]);

        $exists = GeneralSupplyItemDescription::where('item_id', $request->item_id)
            ->where('description_name', $request->description_name)
            ->where('unit_name', $request->unit_name)
            ->exists();

        if ($exists) {
            Alert::error('Error', 'Description already exists for selected item');
            return back()->withInput();
        }

        GeneralSupplyItemDescription::create([
            'item_id' => $request->item_id,
            'description_name' => $request->description_name,
            'unit_name' => $request->unit_name,
            'status' => $request->status,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        Alert::success('Success', 'Description created successfully');
        return redirect()->route('sales.gs.descriptions.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to create description');
        return back()->withInput();
    }
}

public function gsDescriptionsUpdate(Request $request, $id)
{
    try {
        $realId = decrypt($id);
        $row = GeneralSupplyItemDescription::findOrFail($realId);

        $request->validate([
            'item_id' => 'required|exists:general_supply_items,id',
            'description_name' => 'required|string|max:255',
            'unit_name' => 'required|string|max:100',
            'status' => 'required|in:Active,Inactive',
        ]);

        $exists = GeneralSupplyItemDescription::where('item_id', $request->item_id)
            ->where('description_name', $request->description_name)
            ->where('unit_name', $request->unit_name)
            ->where('id', '!=', $row->id)
            ->exists();

        if ($exists) {
            Alert::error('Error', 'Description already exists for selected item');
            return back()->withInput();
        }

        $row->update([
            'item_id' => $request->item_id,
            'description_name' => $request->description_name,
            'unit_name' => $request->unit_name,
            'status' => $request->status,
            'updated_by' => Auth::id(),
        ]);

        Alert::success('Success', 'Description updated successfully');
        return redirect()->route('sales.gs.descriptions.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to update description');
        return back()->withInput();
    }
}

public function gsDescriptionsDestroy($id)
{
    try {
        $realId = decrypt($id);
        GeneralSupplyItemDescription::findOrFail($realId)->delete();

        Alert::success('Success', 'Description removed successfully');
        return redirect()->route('sales.gs.descriptions.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to remove description');
        return back();
    }
}

// ================= RECEIVE =================
public function gsReceivedIndex()
{
    $user = auth()->user();

    $receivings = GeneralSupplyReceiving::with([
        'company', 'unit', 'workpoint', 'department', 'section', 'item', 'description'
    ])->orderByDesc('id')->get();

    $items = GeneralSupplyItem::where('status', 'Active')->orderBy('item_name')->get();
    $workPoints = $this->gsAllowedWorkPoints($user);
    $sections = Section::where('Status', 'Active')->orderBy('secName')->get();

    return view('admin.store.received', compact(
        'receivings',
        'items',
        'workPoints',
        'sections'
    ));
}
public function gsReceivedStore(Request $request)
{
    $user = auth()->user();

    try {
        $request->validate([
            'work_point_id' => 'required|exists:work_points,id',
            'section_id' => 'nullable|exists:sections,id',
            'stock_scope' => 'required|in:Shared,Dedicated',
            'item_id' => 'required|exists:general_supply_items,id',
            'item_description_id' => 'required|exists:general_supply_item_descriptions,id',
            'receive_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'received_qty' => 'required|numeric|min:0.01',
            'damaged_qty' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
            'invoice_no' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        if ($request->stock_scope === 'Dedicated' && !$request->section_id) {
            Alert::error('Error', 'Section is required for dedicated stock');
            return back()->withInput();
        }

        $desc = GeneralSupplyItemDescription::findOrFail($request->item_description_id);
        if ((int) $desc->item_id !== (int) $request->item_id) {
            Alert::error('Error', 'Selected description does not belong to selected item');
            return back()->withInput();
        }

        if ((float) $request->damaged_qty > (float) $request->received_qty) {
            Alert::error('Error', 'Damaged quantity cannot exceed received quantity');
            return back()->withInput();
        }

        $workPoint = WorkPoint::findOrFail($request->work_point_id);
        $companyId = $workPoint->company_id;
        $unitId = $workPoint->comp_unit_id;

        $sectionId = $request->section_id ?: null;
        $deptId = null;

        if ($sectionId) {
            $section = Section::findOrFail($sectionId);
            $deptId = $section->dept_id;
        }

        $receivedQty = (float) $request->received_qty;
        $damagedQty = (float) $request->damaged_qty;
        $goodQty = $receivedQty - $damagedQty;
        $purchasePrice = (float) $request->purchase_price;
        $totalAmount = $receivedQty * $purchasePrice;

        DB::beginTransaction();

        GeneralSupplyReceiving::create([
            'company_id' => $companyId,
            'comp_unit_id' => $unitId,
            'work_point_id' => $request->work_point_id,
            'dept_id' => $deptId,
            'section_id' => $sectionId,
            'stock_scope' => $request->stock_scope,
            'item_id' => $request->item_id,
            'item_description_id' => $request->item_description_id,
            'receive_date' => $request->receive_date,
            'expiry_date' => $request->expiry_date,
            'received_qty' => $receivedQty,
            'damaged_qty' => $damagedQty,
            'good_qty' => $goodQty,
            'purchase_price' => $purchasePrice,
            'total_amount' => $totalAmount,
            'supplier_name' => $request->supplier_name,
            'invoice_no' => $request->invoice_no,
            'reference_no' => $request->reference_no,
            'remarks' => $request->remarks,
            'received_by' => $user->id,
            'updated_by' => $user->id,
            'status' => 'Received',
        ]);

        $stock = GeneralSupplyStock::where('company_id', $companyId)
            ->where('comp_unit_id', $unitId)
            ->where('work_point_id', $request->work_point_id)
            ->where('dept_id', $deptId)
            ->where('section_id', $sectionId)
            ->where('stock_scope', $request->stock_scope)
            ->where('item_id', $request->item_id)
            ->where('item_description_id', $request->item_description_id)
            ->where('expiry_date', $request->expiry_date)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            $stock->qty_in += $goodQty;
            $stock->damaged_qty += $damagedQty;
            $stock->balance = $stock->qty_in - $stock->qty_out;
            $stock->purchase_price = $purchasePrice;
            $stock->save();
        } else {
            GeneralSupplyStock::create([
                'company_id' => $companyId,
                'comp_unit_id' => $unitId,
                'work_point_id' => $request->work_point_id,
                'dept_id' => $deptId,
                'section_id' => $sectionId,
                'stock_scope' => $request->stock_scope,
                'item_id' => $request->item_id,
                'item_description_id' => $request->item_description_id,
                'expiry_date' => $request->expiry_date,
                'qty_in' => $goodQty,
                'qty_out' => 0,
                'damaged_qty' => $damagedQty,
                'balance' => $goodQty,
                'purchase_price' => $purchasePrice,
                'status' => 'Active',
            ]);
        }

        DB::commit();

        Alert::success('Success', 'Items received successfully');
        return redirect()->route('sales.gs.received.index');
    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', 'Failed to receive items');
        return back()->withInput();
    }
}
public function gsReceivedUpdate(Request $request, $id)
{
    $user = auth()->user();

    try {
        $realId = decrypt($id);
        $row = GeneralSupplyReceiving::findOrFail($realId);

        $request->validate([
            'work_point_id' => 'required|exists:work_points,id',
            'section_id' => 'nullable|exists:sections,id',
            'stock_scope' => 'required|in:Shared,Dedicated',
            'item_id' => 'required|exists:general_supply_items,id',
            'item_description_id' => 'required|exists:general_supply_item_descriptions,id',
            'receive_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'received_qty' => 'required|numeric|min:0.01',
            'damaged_qty' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
            'invoice_no' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        if ($request->stock_scope === 'Dedicated' && !$request->section_id) {
            Alert::error('Error', 'Section is required for dedicated stock');
            return back()->withInput();
        }

        if ((float) $request->damaged_qty > (float) $request->received_qty) {
            Alert::error('Error', 'Damaged quantity cannot exceed received quantity');
            return back()->withInput();
        }

        $desc = GeneralSupplyItemDescription::findOrFail($request->item_description_id);
        if ((int) $desc->item_id !== (int) $request->item_id) {
            Alert::error('Error', 'Selected description does not belong to selected item');
            return back()->withInput();
        }

        $workPoint = WorkPoint::findOrFail($request->work_point_id);
        $companyId = $workPoint->company_id;
        $unitId = $workPoint->comp_unit_id;

        $sectionId = $request->section_id ?: null;
        $deptId = null;

        if ($sectionId) {
            $section = Section::findOrFail($sectionId);
            $deptId = $section->dept_id;
        }

        $newReceivedQty = (float) $request->received_qty;
        $newDamagedQty = (float) $request->damaged_qty;
        $newGoodQty = $newReceivedQty - $newDamagedQty;
        $newPurchasePrice = (float) $request->purchase_price;
        $newTotalAmount = $newReceivedQty * $newPurchasePrice;

        DB::beginTransaction();

        $oldStock = GeneralSupplyStock::where('company_id', $row->company_id)
            ->where('comp_unit_id', $row->comp_unit_id)
            ->where('work_point_id', $row->work_point_id)
            ->where('dept_id', $row->dept_id)
            ->where('section_id', $row->section_id)
            ->where('stock_scope', $row->stock_scope)
            ->where('item_id', $row->item_id)
            ->where('item_description_id', $row->item_description_id)
            ->where('expiry_date', $row->expiry_date)
            ->lockForUpdate()
            ->first();

        if ($oldStock) {
            if ($oldStock->qty_out > $row->good_qty) {
                DB::rollBack();
                Alert::error('Error', 'Cannot update this receive because some quantity was already issued from this stock line.');
                return back()->withInput();
            }

            $oldStock->qty_in -= $row->good_qty;
            $oldStock->damaged_qty -= $row->damaged_qty;
            if ($oldStock->qty_in < 0) $oldStock->qty_in = 0;
            if ($oldStock->damaged_qty < 0) $oldStock->damaged_qty = 0;
            $oldStock->balance = $oldStock->qty_in - $oldStock->qty_out;
            if ($oldStock->balance < 0) $oldStock->balance = 0;
            $oldStock->save();
        }

        $newStock = GeneralSupplyStock::where('company_id', $companyId)
            ->where('comp_unit_id', $unitId)
            ->where('work_point_id', $request->work_point_id)
            ->where('dept_id', $deptId)
            ->where('section_id', $sectionId)
            ->where('stock_scope', $request->stock_scope)
            ->where('item_id', $request->item_id)
            ->where('item_description_id', $request->item_description_id)
            ->where('expiry_date', $request->expiry_date)
            ->lockForUpdate()
            ->first();

        if ($newStock) {
            $newStock->qty_in += $newGoodQty;
            $newStock->damaged_qty += $newDamagedQty;
            $newStock->balance = $newStock->qty_in - $newStock->qty_out;
            $newStock->purchase_price = $newPurchasePrice;
            $newStock->save();
        } else {
            GeneralSupplyStock::create([
                'company_id' => $companyId,
                'comp_unit_id' => $unitId,
                'work_point_id' => $request->work_point_id,
                'dept_id' => $deptId,
                'section_id' => $sectionId,
                'stock_scope' => $request->stock_scope,
                'item_id' => $request->item_id,
                'item_description_id' => $request->item_description_id,
                'expiry_date' => $request->expiry_date,
                'qty_in' => $newGoodQty,
                'qty_out' => 0,
                'damaged_qty' => $newDamagedQty,
                'balance' => $newGoodQty,
                'purchase_price' => $newPurchasePrice,
                'status' => 'Active',
            ]);
        }

        $row->update([
            'company_id' => $companyId,
            'comp_unit_id' => $unitId,
            'work_point_id' => $request->work_point_id,
            'dept_id' => $deptId,
            'section_id' => $sectionId,
            'stock_scope' => $request->stock_scope,
            'item_id' => $request->item_id,
            'item_description_id' => $request->item_description_id,
            'receive_date' => $request->receive_date,
            'expiry_date' => $request->expiry_date,
            'received_qty' => $newReceivedQty,
            'damaged_qty' => $newDamagedQty,
            'good_qty' => $newGoodQty,
            'purchase_price' => $newPurchasePrice,
            'total_amount' => $newTotalAmount,
            'supplier_name' => $request->supplier_name,
            'invoice_no' => $request->invoice_no,
            'reference_no' => $request->reference_no,
            'remarks' => $request->remarks,
            'updated_by' => $user->id,
        ]);

        DB::commit();

        Alert::success('Success', 'Received item updated successfully');
        return redirect()->route('sales.gs.received.index');
    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', 'Failed to update received item');
        return back()->withInput();
    }
}
// ================= REQUESTS VIEW IN SALES FOR ISSUE =================
public function gsRequestedIndex()
{
    $user = auth()->user();
    $rows = GeneralSupplyRequest::with([
        'company','unit','workpoint','department','section','item','description'
    ])->whereIn('status', ['Pending','Partial'])->orderByDesc('id')->get();

    return view('admin.store.requested', compact('rows'));
}

// ================= ISSUE FROM SALES =================
public function gsIssueStore(Request $request)
{
    $user = auth()->user();

    try {
        $request->validate([
            'request_id' => 'required|exists:general_supply_requests,id',
            'issue_date' => 'required|date',
            'issued_qty' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string',
        ]);

        $req = GeneralSupplyRequest::findOrFail($request->request_id);
        $remainingRequest = (float)$req->requested_qty - (float)$req->issued_qty;

        if ((float)$request->issued_qty > $remainingRequest) {
            Alert::error('Error', 'Issued quantity exceeds remaining requested quantity');
            return back()->withInput();
        }

        DB::beginTransaction();

        // issue dedicated stock first if request has section, then shared
        $qtyToIssue = (float)$request->issued_qty;
        $issuedTotal = 0;

        if ($req->section_id) {
            $dedicatedStocks = GeneralSupplyStock::where('work_point_id', $req->work_point_id)
                ->where('item_id', $req->item_id)
                ->where('item_description_id', $req->item_description_id)
                ->where('stock_scope', 'Dedicated')
                ->where('section_id', $req->section_id)
                ->where('balance', '>', 0)
                ->orderBy('expiry_date')
                ->lockForUpdate()
                ->get();

            foreach ($dedicatedStocks as $stock) {
                if ($qtyToIssue <= 0) break;
                $take = min($qtyToIssue, (float)$stock->balance);
                $stock->qty_out += $take;
                $stock->balance = $stock->qty_in - $stock->qty_out;
                $stock->save();
                $qtyToIssue -= $take;
                $issuedTotal += $take;
            }
        }

        if ($qtyToIssue > 0) {
            $sharedStocks = GeneralSupplyStock::where('work_point_id', $req->work_point_id)
                ->where('item_id', $req->item_id)
                ->where('item_description_id', $req->item_description_id)
                ->where('stock_scope', 'Shared')
                ->where('balance', '>', 0)
                ->orderBy('expiry_date')
                ->lockForUpdate()
                ->get();

            foreach ($sharedStocks as $stock) {
                if ($qtyToIssue <= 0) break;
                $take = min($qtyToIssue, (float)$stock->balance);
                $stock->qty_out += $take;
                $stock->balance = $stock->qty_in - $stock->qty_out;
                $stock->save();
                $qtyToIssue -= $take;
                $issuedTotal += $take;
            }
        }

        if ($issuedTotal <= 0 || $issuedTotal < (float)$request->issued_qty) {
            DB::rollBack();
            Alert::error('Error', 'Insufficient stock to complete this issue');
            return back()->withInput();
        }

        GeneralSupplyIssue::create([
            'request_id' => $req->id,
            'company_id' => $req->company_id,
            'comp_unit_id' => $req->comp_unit_id,
            'work_point_id' => $req->work_point_id,
            'dept_id' => $req->dept_id,
            'section_id' => $req->section_id,
            'stock_scope' => $req->stock_scope,
            'item_id' => $req->item_id,
            'item_description_id' => $req->item_description_id,
            'issue_date' => $request->issue_date,
            'issued_qty' => $issuedTotal,
            'remarks' => $request->remarks,
            'issued_by' => $user->id,
            'status' => 'Issued',
        ]);

        $req->issued_qty += $issuedTotal;
        if ((float)$req->issued_qty >= (float)$req->requested_qty) {
            $req->status = 'Issued';
        } elseif ((float)$req->issued_qty > 0) {
            $req->status = 'Partial';
        } else {
            $req->status = 'Pending';
        }
        $req->save();

        DB::commit();

        Alert::success('Success', 'Requested item issued successfully');
        return redirect()->route('sales.gs.requested.index');
    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', 'Failed to issue requested item');
        return back()->withInput();
    }
}

public function gsIssuedIndex()
{
    $rows = GeneralSupplyIssue::with([
        'request','company','unit','workpoint','department','section','item','description'
    ])->orderByDesc('id')->get();

    return view('admin.store.issued', compact('rows'));
}

// ================= STOCK =================
public function gsStockIndex()
{
    $user = auth()->user();

    // combined/nested style for broad access based on user's sample layout pattern
    // sample showed grouped item stock with description breakdown and totals :contentReference[oaicite:1]{index=1}
    if ($this->gsCanAll($user) || $this->gsCanCompany($user) || $this->gsCanUnit($user)) {
        $itemStocks = GeneralSupplyStock::select(
                'item_id',
                'item_description_id',
                'section_id',
                'stock_scope',
                DB::raw('SUM(qty_in) as total_received'),
                DB::raw('SUM(balance) as total_available'),
                DB::raw('SUM(qty_out) as total_used'),
                DB::raw('SUM(damaged_qty) as total_damaged')
            )
            ->with(['item','description','section'])
            ->groupBy('item_id', 'item_description_id', 'section_id', 'stock_scope')
            ->orderBy('item_id')
            ->get();

        return view('admin.store.stock', compact('itemStocks'));
    }
    $rows = GeneralSupplyStock::with([
        'company','unit','workpoint','department','section','item','description'
    ])->orderByDesc('id')->get();

    return view('admin.store.stock_simple', compact('rows'));
}

// ================= PURCHASE REPORT + EXPIRY REPORT =================
public function gsPurchaseReport()
{
    $rows = GeneralSupplyReceiving::with([
        'company','unit','workpoint','department','section','item','description'
    ])->orderByDesc('id')->get();

    $expiredRows = GeneralSupplyStock::with(['item','description','section'])
        ->whereNotNull('expiry_date')
        ->whereDate('expiry_date', '<', now()->toDateString())
        ->where('balance', '>', 0)
        ->get();

    return view('admin.store.purchase_report', compact('rows', 'expiredRows'));
}


// ================= RAW MATERIAL PERMISSION HELPERS =================
protected function rmGlobalRoles(): array
{
    return ['Admin', 'CEO', 'Managing Director (MD)', 'Admin-Developer'];
}

protected function rmCanAll($user): bool
{
    return in_array($user->role, $this->rmGlobalRoles(), true)
        || $user->can('View-Raw-Materials-All');
}

protected function rmCanCompany($user): bool
{
    return $user->can('View-Raw-Materials-Company')
        || $user->role === 'Company Manager';
}

protected function rmCanUnit($user): bool
{
    return $user->can('View-Raw-Materials-Unit')
        || $user->role === 'Unit Manager';
}

protected function rmAllowedWorkPoints($user)
{
    if ($this->rmCanAll($user)) {
        return WorkPoint::where('status', '!=', 'Deleted')
            ->orderBy('work_name')
            ->get();
    }

    if ($this->rmCanCompany($user)) {
        return WorkPoint::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('work_name')
            ->get();
    }

    if ($this->rmCanUnit($user)) {
        return WorkPoint::where('company_id', $user->company_id)
            ->where('comp_unit_id', $user->comp_unit_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('work_name')
            ->get();
    }

    return WorkPoint::where('id', $user->work_point_id)
        ->where('status', '!=', 'Deleted')
        ->orderBy('work_name')
        ->get();
}

protected function rmScopedQuery($user)
{
    $query = RawMaterial::with(['company', 'workpoint'])
        ->where('status', '!=', 'Deleted');

    if ($this->rmCanAll($user)) {
        return $query;
    }

    if ($this->rmCanCompany($user)) {
        return $query->where('company_id', $user->company_id);
    }

    if ($this->rmCanUnit($user)) {
        return $query->where('company_id', $user->company_id)
            ->where('comp_unit_id', $user->comp_unit_id);
    }

    return $query->where('company_id', $user->company_id)
        ->where('work_point_id', $user->work_point_id);
}

protected function rmCanUseSelectableWorkPoint($user): bool
{
    return $this->rmCanAll($user) || $this->rmCanCompany($user) || $this->rmCanUnit($user);
}

// ================= RAW MATERIALS =================
public function rmIndex()
{
    $user = auth()->user();

    $rows = $this->rmScopedQuery($user)
        ->orderBy('material_name')
        ->get();

    $workPoints = $this->rmAllowedWorkPoints($user);

    return view('admin.store.raw_materials', compact('rows', 'workPoints'));
}

public function rmStore(Request $request)
{
    $user = auth()->user();

    $rules = [
        'material_name' => ['required', 'string', 'max:255'],
        'material_code' => ['nullable', 'string', 'max:255'],
        'unit_name'     => ['nullable', 'string', 'max:255'],
        'status'        => ['required', Rule::in(['Active', 'Inactive'])],
    ];

    if ($this->rmCanUseSelectableWorkPoint($user)) {
        $rules['work_point_id'] = [
            'required',
            'integer',
            Rule::exists('work_points', 'id')->where(function ($q) use ($user) {
                if ($this->rmCanAll($user)) {
                    $q->where('status', '!=', 'Deleted');
                } elseif ($this->rmCanCompany($user)) {
                    $q->where('company_id', $user->company_id)
                        ->where('status', '!=', 'Deleted');
                } elseif ($this->rmCanUnit($user)) {
                    $q->where('company_id', $user->company_id)
                        ->where('comp_unit_id', $user->comp_unit_id)
                        ->where('status', '!=', 'Deleted');
                }
            }),
        ];
    }

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $workPointId = $this->rmCanUseSelectableWorkPoint($user)
        ? $request->work_point_id
        : $user->work_point_id;

    $workPoint = WorkPoint::find($workPointId);

    if (!$workPoint) {
        Alert::error('Error', 'Invalid work point selected');
        return back()->withInput();
    }

    RawMaterial::create([
        'user_id'       => $user->id,
        'company_id'    => $workPoint->company_id,
        'comp_unit_id'  => $workPoint->comp_unit_id,
        'work_point_id' => $workPoint->id,
        'material_name' => $request->material_name,
        'material_code' => $request->material_code,
        'unit_name'     => $request->unit_name,
        'status'        => $request->status,
    ]);

    Alert::success('Success', 'Raw material created successfully');
    return redirect()->route('sales.rm.index');
}

public function rmUpdate(Request $request, $id)
{
    try {
        $realId = decrypt($id);
    } catch (\Throwable $e) {
        Alert::error('Error', 'Invalid identifier');
        return back();
    }

    $user = auth()->user();
    $row = RawMaterial::findOrFail($realId);

    $owned = false;

    if ($this->rmCanAll($user)) {
        $owned = true;
    } elseif ($this->rmCanCompany($user)) {
        $owned = ($row->company_id == $user->company_id);
    } elseif ($this->rmCanUnit($user)) {
        $owned = ($row->company_id == $user->company_id && $row->comp_unit_id == $user->comp_unit_id);
    } else {
        $owned = ($row->company_id == $user->company_id && $row->work_point_id == $user->work_point_id);
    }

    if (!$owned) {
        Alert::error('Unauthorized', 'You cannot edit this raw material');
        return back();
    }

    $rules = [
        'material_name' => ['required', 'string', 'max:255'],
        'material_code' => ['nullable', 'string', 'max:255'],
        'unit_name'     => ['nullable', 'string', 'max:255'],
        'status'        => ['required', Rule::in(['Active', 'Inactive'])],
    ];

    if ($this->rmCanUseSelectableWorkPoint($user)) {
        $rules['work_point_id'] = [
            'required',
            'integer',
            Rule::exists('work_points', 'id')->where(function ($q) use ($user) {
                if ($this->rmCanAll($user)) {
                    $q->where('status', '!=', 'Deleted');
                } elseif ($this->rmCanCompany($user)) {
                    $q->where('company_id', $user->company_id)
                        ->where('status', '!=', 'Deleted');
                } elseif ($this->rmCanUnit($user)) {
                    $q->where('company_id', $user->company_id)
                        ->where('comp_unit_id', $user->comp_unit_id)
                        ->where('status', '!=', 'Deleted');
                }
            }),
        ];
    }

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $workPointId = $this->rmCanUseSelectableWorkPoint($user)
        ? $request->work_point_id
        : $user->work_point_id;

    $workPoint = WorkPoint::find($workPointId);

    if (!$workPoint) {
        Alert::error('Error', 'Invalid work point selected');
        return back()->withInput();
    }

    $row->update([
        'company_id'    => $workPoint->company_id,
        'comp_unit_id'  => $workPoint->comp_unit_id,
        'work_point_id' => $workPoint->id,
        'material_name' => $request->material_name,
        'material_code' => $request->material_code,
        'unit_name'     => $request->unit_name,
        'status'        => $request->status,
    ]);

    Alert::success('Success', 'Raw material updated successfully');
    return redirect()->route('sales.rm.index');
}

public function rmDestroy($id)
{
    try {
        $realId = decrypt($id);
    } catch (\Throwable $e) {
        Alert::error('Error', 'Invalid identifier');
        return back();
    }

    $user = auth()->user();
    $row = RawMaterial::findOrFail($realId);

    $owned = false;

    if ($this->rmCanAll($user)) {
        $owned = true;
    } elseif ($this->rmCanCompany($user)) {
        $owned = ($row->company_id == $user->company_id);
    } elseif ($this->rmCanUnit($user)) {
        $owned = ($row->company_id == $user->company_id && $row->comp_unit_id == $user->comp_unit_id);
    } else {
        $owned = ($row->company_id == $user->company_id && $row->work_point_id == $user->work_point_id);
    }

    if (!$owned) {
        Alert::error('Unauthorized', 'You cannot remove this raw material');
        return back();
    }

    $row->update(['status' => 'Deleted']);

    Alert::success('Success', 'Raw material removed successfully');
    return redirect()->route('sales.rm.index');
}
// ================= VENDORS =================
public function vendorsIndex()
{
    $vendors = Vendor::orderByDesc('id')->where('status', 'Active')->get();
    return view('admin.store.vendors', compact('vendors'));
}
public function vendorsStore(Request $request)
{
    try {
        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'vendor_code' => 'nullable|string|max:255|unique:vendors,vendor_code',
            'phone_no' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'tin_no' => 'nullable|string|max:255',
            'account_code' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);
        $user = auth()->user();
        Vendor::create([
            'company_id' => 2,
            'vendor_name' => $request->vendor_name,
            'vendor_code' => $request->vendor_code,
            'phone_no' => $request->phone_no,
            'email' => $request->email,
            'address' => $request->address,
            'tin_no' => $request->tin_no,
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'status' => $request->status,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        Alert::success('Success', 'Vendor created successfully');
        return redirect()->route('sales.vendors.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to create vendor');
        return back()->withInput();
    }
}
public function vendorsUpdate(Request $request, $id)
{
    try {
        $realId = decrypt($id);
        $row = Vendor::findOrFail($realId);
        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'vendor_code' => 'nullable|string|max:255|unique:vendors,vendor_code,' . $row->id,
            'phone_no' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'tin_no' => 'nullable|string|max:255',
            'account_code' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);
        $row->update([
            'company_id' => 2,
            'vendor_name' => $request->vendor_name,
            'vendor_code' => $request->vendor_code,
            'phone_no' => $request->phone_no,
            'email' => $request->email,
            'address' => $request->address,
            'tin_no' => $request->tin_no,
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'status' => $request->status,
            'updated_by' => auth()->id(),
        ]);
        Alert::success('Success', 'Vendor updated successfully');
        return redirect()->route('sales.vendors.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to update vendor');
        return back()->withInput();
    }
}
public function vendorsDestroy($id)
{
    try {
        $realId = decrypt($id);
        Vendor::findOrFail($realId)->delete();
        Alert::success('Success', 'Vendor removed successfully');
        return redirect()->route('sales.vendors.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to remove vendor');
        return back();
    }
}


// ================= PURCHASE ORDER VAT / DECIMAL HELPERS =================
private function poVendorHasTin($vendor)
{
    return !empty($vendor->tin_no)
        || !empty($vendor->tin_number)
        || !empty($vendor->tin);
}

private function poRoundAmount($amount, $decimals = 6)
{
    return round((float) $amount, $decimals);
}

private function poRoundTotalAmount($amount)
{
    return round((float) $amount, 0);
}

private function poEffectiveVatRate($requestVatRate, $vendor)
{
    // Same idea as Proforma: apply VAT only when the supplier/vendor has TIN.
    // The number stored here is a percentage, for example 18 means 18%.
    $rate = $this->poRoundAmount($requestVatRate, 6);

    if ($this->poVendorHasTin($vendor) && $rate > 0) {
        return $rate;
    }

    return 0;
}

// ================= PURCHASE ORDERS =================
public function poIndex()
{
    $orders = PurchaseOrder::with([
            'company',
            'businessUnit',
            'workPoint',
            'vendor',
            'items',
            'creator',
            'approver',
            'receiver',
        ])
        ->orderByDesc('id')
        ->get();

    $companies = CompanySite::where('status', 'Active')
        ->orderBy('company_name')
        ->get();

    $vendors = Vendor::where('status', 'Active')
        ->orderBy('vendor_name')
        ->get();

    $items = Product::where('status', 'Active')
        ->orderBy('product_name')
        ->get();

    $rawMaterials = RawMaterial::where('status', 'Active')
        ->orderBy('material_name')
        ->get();

    return view('admin.store.purchase_orders', compact(
        'orders',
        'companies',
        'vendors',
        'items',
        'rawMaterials'
    ));
}
public function poStore(Request $request)
{
    $request->validate([
        'company_id' => 'required|exists:company_sites,id',
        'business_unit_id' => 'required|exists:company_units,id',
        'work_point_id' => 'required|exists:work_points,id',
        'vendor_id' => 'required|exists:vendors,id',
        'po_date' => 'required|date',
        'expected_delivery_date' => 'nullable|date',
        'purchase_type' => 'required|in:GeneralSupply,RawMaterial',
        'currency' => 'required|string|max:10',
        'exchange_rate' => 'required|numeric|min:0.0001',
        'vat_rate' => 'required|numeric|min:0|max:100',
        'discount' => 'nullable|numeric|min:0',

        // Only proforma is uploaded during PO creation
        'supplier_proforma_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',

        'items' => 'required|array|min:1',
        'items.*.item_id' => 'required|integer',
        'items.*.qty' => 'required|numeric|min:0.01',
        'items.*.unit_price' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();

    try {
        $company = CompanySite::findOrFail($request->company_id);
        $businessUnit = Company_unit::findOrFail($request->business_unit_id);
        $workPoint = WorkPoint::findOrFail($request->work_point_id);
        $vendor = Vendor::findOrFail($request->vendor_id);

        // Unit price entered by user is treated as VAT-INCLUSIVE only when supplier/vendor has TIN.
        // Example: 95,000 x 1,200 = 114,000,000 final total. VAT is extracted inside that total.
        $effectiveVatRate = $this->poEffectiveVatRate($request->vat_rate, $vendor);

        $preparedItems = $this->preparePurchaseOrderItems(
            $request->items,
            $request->purchase_type,
            $effectiveVatRate,
            $effectiveVatRate > 0
        );

        if (count($preparedItems) < 1) {
            throw new \Exception('Please add at least one valid item.');
        }

        $subTotal = $this->poRoundAmount(collect($preparedItems)->sum('sub_total'), 6);
        $vatAmount = $this->poRoundAmount(collect($preparedItems)->sum('vat_amount'), 6);
        $discount = $this->poRoundAmount($request->discount ?? 0, 6);
        $grossTotal = $this->poRoundAmount($subTotal + $vatAmount, 6);
        $totalAmount = $this->poRoundTotalAmount($grossTotal - $discount);
        $totalTzs = $this->poRoundTotalAmount($totalAmount * (float) $request->exchange_rate);

        $po = PurchaseOrder::create([
            'company_id' => $request->company_id,
            'business_unit_id' => $request->business_unit_id,
            'work_point_id' => $request->work_point_id,
            'vendor_id' => $request->vendor_id,

            'po_no' => $this->generatePurchaseOrderNumber($company),
            'pi_no' => $request->pi_no,
            'po_date' => $request->po_date,
            'expected_delivery_date' => $request->expected_delivery_date,
            'purchase_type' => $request->purchase_type,

            'ship_to' => $request->ship_to ?: $this->buildShipToAddress($company, $businessUnit, $workPoint),
            'vendor_from' => $request->vendor_from ?: $this->buildVendorFromAddress($vendor),
            'shipping_method' => $request->shipping_method,
            'shipping_terms' => $request->shipping_terms,
            'delivery_point' => $request->delivery_point ?: ($workPoint->work_name ?? null),
            'terms_conditions' => $request->terms_conditions,
            'remarks' => $request->remarks,

            'currency' => $request->currency ?: 'TZS',
            'exchange_rate' => $request->exchange_rate,
            'vat_rate' => $effectiveVatRate,

            'sub_total' => $subTotal,
            'vat_amount' => $vatAmount,
            'discount' => $discount,
            'total_amount' => $totalAmount,
            'total_tzs' => $totalTzs,

            'payment_status' => 'unpaid',
            'amount_paid' => 0,
            'balance' => $totalAmount,

            'receive_status' => 'pending',

            'supplier_proforma_attachment' => $this->uploadPoFile($request, 'supplier_proforma_attachment'),
            'supplier_invoice_attachment' => null,
            'delivery_note_attachment' => null,

            'account_code' => $vendor->account_code,
            'account_name' => $vendor->account_name,

            'status' => 'Draft',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach ($preparedItems as $item) {
            PurchaseOrderItem::create(array_merge($item, [
                'purchase_order_id' => $po->id,
            ]));
        }

        DB::commit();

        Alert::success('Success', 'Purchase order created successfully');
        return redirect()->route('sales.po.index');

    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', $e->getMessage());
        return back()->withInput();
    }
}
public function poUpdate(Request $request, $id)
{
    $realId = decrypt($id);

    $request->validate([
        'company_id' => 'required|exists:company_sites,id',
        'business_unit_id' => 'required|exists:company_units,id',
        'work_point_id' => 'required|exists:work_points,id',
        'vendor_id' => 'required|exists:vendors,id',
        'po_date' => 'required|date',
        'expected_delivery_date' => 'nullable|date',
        'purchase_type' => 'required|in:GeneralSupply,RawMaterial',
        'currency' => 'required|string|max:10',
        'exchange_rate' => 'required|numeric|min:0.0001',
        'vat_rate' => 'required|numeric|min:0|max:100',
        'discount' => 'nullable|numeric|min:0',

        // Only proforma is editable before approval
        'supplier_proforma_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',

        'items' => 'required|array|min:1',
        'items.*.item_id' => 'required|integer',
        'items.*.qty' => 'required|numeric|min:0.01',
        'items.*.unit_price' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();

    try {
        $po = PurchaseOrder::with('items')->findOrFail($realId);

        if (!in_array($po->status, ['Draft', 'Cancelled'])) {
            throw new \Exception('Only Draft or Rejected purchase orders can be edited.');
        }

        $company = CompanySite::findOrFail($request->company_id);
        $businessUnit = Company_unit::findOrFail($request->business_unit_id);
        $workPoint = WorkPoint::findOrFail($request->work_point_id);
        $vendor = Vendor::findOrFail($request->vendor_id);

        // Unit price entered by user is treated as VAT-INCLUSIVE only when supplier/vendor has TIN.
        // If supplier/vendor has no TIN, VAT is zero and typed price remains the final total.
        $effectiveVatRate = $this->poEffectiveVatRate($request->vat_rate, $vendor);

        $preparedItems = $this->preparePurchaseOrderItems(
            $request->items,
            $request->purchase_type,
            $effectiveVatRate,
            $effectiveVatRate > 0
        );

        if (count($preparedItems) < 1) {
            throw new \Exception('Please add at least one valid item.');
        }

        $subTotal = $this->poRoundAmount(collect($preparedItems)->sum('sub_total'), 6);
        $vatAmount = $this->poRoundAmount(collect($preparedItems)->sum('vat_amount'), 6);
        $discount = $this->poRoundAmount($request->discount ?? 0, 6);
        $grossTotal = $this->poRoundAmount($subTotal + $vatAmount, 6);
        $totalAmount = $this->poRoundTotalAmount($grossTotal - $discount);
        $totalTzs = $this->poRoundTotalAmount($totalAmount * (float) $request->exchange_rate);

        $po->update([
            'company_id' => $request->company_id,
            'business_unit_id' => $request->business_unit_id,
            'work_point_id' => $request->work_point_id,
            'vendor_id' => $request->vendor_id,

            'pi_no' => $request->pi_no,
            'po_date' => $request->po_date,
            'expected_delivery_date' => $request->expected_delivery_date,
            'purchase_type' => $request->purchase_type,

            'ship_to' => $request->ship_to ?: $this->buildShipToAddress($company, $businessUnit, $workPoint),
            'vendor_from' => $request->vendor_from ?: $this->buildVendorFromAddress($vendor),
            'shipping_method' => $request->shipping_method,
            'shipping_terms' => $request->shipping_terms,
            'delivery_point' => $request->delivery_point ?: ($workPoint->work_name ?? null),
            'terms_conditions' => $request->terms_conditions,
            'remarks' => $request->remarks,

            'currency' => $request->currency ?: 'TZS',
            'exchange_rate' => $request->exchange_rate,
            'vat_rate' => $effectiveVatRate,

            'sub_total' => $subTotal,
            'vat_amount' => $vatAmount,
            'discount' => $discount,
            'total_amount' => $totalAmount,
            'total_tzs' => $totalTzs,
            'balance' => $this->poRoundAmount($totalAmount - $po->amount_paid, 6),

            'supplier_proforma_attachment' => $this->uploadPoFile(
                $request,
                'supplier_proforma_attachment',
                $po->supplier_proforma_attachment
            ),

            'account_code' => $vendor->account_code,
            'account_name' => $vendor->account_name,

            // If it was rejected/cancelled and user edits it, return it to Draft
            'status' => 'Draft',
            'updated_by' => auth()->id(),
        ]);

        $po->items()->delete();

        foreach ($preparedItems as $item) {
            PurchaseOrderItem::create(array_merge($item, [
                'purchase_order_id' => $po->id,
            ]));
        }

        DB::commit();

        Alert::success('Success', 'Purchase order updated successfully');
        return redirect()->route('sales.po.index');

    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', $e->getMessage());
        return back()->withInput();
    }
}
public function poShow($id)
{
    $realId = decrypt($id);

    $order = PurchaseOrder::with([
            'company',
            'businessUnit',
            'workPoint',
            'vendor',
            'items',
            'creator',
            'approver',
            'receiver',
        ])
        ->findOrFail($realId);

    return view('admin.store.purchase_order_show', compact('order'));
}

public function poPrint($id)
{
    $realId = decrypt($id);

    $order = PurchaseOrder::with([
            'company',
            'businessUnit',
            'workPoint',
            'vendor',
            'items',
            'creator',
            'approver',
            'receiver',
        ])
        ->findOrFail($realId);

    return view('admin.store.purchase_order_show', compact('order'));
}

public function poDocuments($id)
{
    $realId = decrypt($id);

    $order = PurchaseOrder::with([
            'company',
            'businessUnit',
            'workPoint',
            'vendor',
            'creator',
        ])
        ->findOrFail($realId);

    return view('admin.store.purchase_order_documents', compact('order'));
}

public function poApprove($id)
{
    $realId = decrypt($id);

    DB::beginTransaction();

    try {
        $po = PurchaseOrder::with(['vendor', 'items', 'company', 'workPoint'])->findOrFail($realId);

        if ($po->status !== 'Draft') {
            throw new \Exception('Only Draft purchase orders can be approved.');
        }

        $transactionGroup = $this->postPurchaseOrderAccounting($po);

        $po->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'accounting_transaction_group' => $transactionGroup,
            'updated_by' => auth()->id(),
        ]);

        DB::commit();

        Alert::success('Success', 'Purchase order approved and posted to accounting');
        return redirect()->route('sales.po.index');

    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', $e->getMessage());
        return back();
    }
}

public function poReceive(Request $request, $id)
{
    $realId = decrypt($id);

    $request->validate([
        'received_date' => 'required|date',
        'delivery_note_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        'supplier_invoice_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        'items' => 'required|array|min:1',
        'items.*.received_qty' => 'nullable|numeric|min:0',
    ]);

    DB::beginTransaction();

    try {
        $po = PurchaseOrder::with('items')->lockForUpdate()->findOrFail($realId);

        if (!in_array($po->status, ['Approved', 'Ordered', 'PartiallyReceived'])) {
            throw new \Exception('Only approved, ordered, or partially received purchase orders can be received.');
        }

        $receivedInputs = $request->input('items', []);

        $hasPositiveReceivedQty = false;

        foreach ($receivedInputs as $purchaseOrderItemId => $row) {
            $receivedNow = $this->poRoundAmount($row['received_qty'] ?? 0, 6);

            if ($receivedNow > 0) {
                $hasPositiveReceivedQty = true;
                break;
            }
        }

        if (!$hasPositiveReceivedQty) {
            throw new \Exception('Please enter at least one received quantity greater than zero.');
        }

        foreach ($receivedInputs as $purchaseOrderItemId => $row) {
            $receivedNow = $this->poRoundAmount($row['received_qty'] ?? 0, 6);

            if ($receivedNow <= 0) {
                continue;
            }

            $item = $po->items->where('id', $purchaseOrderItemId)->first();

            if (!$item) {
                throw new \Exception('Invalid purchase order item selected.');
            }

            $currentReceived = (float) $item->received_qty;
            $orderedQty = (float) $item->qty;
            $currentBalance = max($orderedQty - $currentReceived, 0);

            if ($receivedNow > $currentBalance) {
                throw new \Exception(
                    'Received quantity for item "' . $item->item_name . '" cannot exceed balance quantity ' . $currentBalance . '.'
                );
            }

            $newReceivedQty = $this->poRoundAmount($currentReceived + $receivedNow, 6);
            $newBalanceQty = $this->poRoundAmount(max($orderedQty - $newReceivedQty, 0), 6);

            $item->update([
                'received_qty' => $newReceivedQty,
                'balance_qty' => $newBalanceQty,
            ]);
        }

        $po->load('items');

        $totalBalance = $po->items->sum('balance_qty');
        $totalReceived = $po->items->sum('received_qty');

        if ($totalBalance <= 0) {
            $receiveStatus = 'received';
            $status = 'Received';
        } elseif ($totalReceived > 0) {
            $receiveStatus = 'partial';
            $status = 'PartiallyReceived';
        } else {
            $receiveStatus = 'pending';
            $status = $po->status;
        }

        $po->update([
            'receive_status' => $receiveStatus,
            'status' => $status,
            'received_date' => $request->received_date,
            'received_by' => auth()->id(),
            'delivery_note_attachment' => $this->uploadPoFile(
                $request,
                'delivery_note_attachment',
                $po->delivery_note_attachment
            ),
            'supplier_invoice_attachment' => $this->uploadPoFile(
                $request,
                'supplier_invoice_attachment',
                $po->supplier_invoice_attachment
            ),
            'updated_by' => auth()->id(),
        ]);

        DB::commit();

        Alert::success('Success', 'Received quantities saved successfully.');
        return redirect()->route('sales.po.index');

    } catch (\Throwable $e) {
        DB::rollBack();

        Alert::error('Error', $e->getMessage());
        return back()->withInput();
    }
}
public function poPayment(Request $request, $id)
{
    $realId = decrypt($id);

    $request->validate([
        'amount_paid' => 'required|numeric|min:0.01',
        'payment_method' => 'required|in:cash,pettycash,bank,cheque,mobile',
        'cheque_no' => 'nullable|string|max:255',
        'payment_reference' => 'nullable|string|max:255',
        'payment_date' => 'required|date',
        'payment_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
    ]);

    DB::beginTransaction();

    try {
        $po = PurchaseOrder::findOrFail($realId);

        $newPaid = $this->poRoundAmount($po->amount_paid + (float) $request->amount_paid, 6);

        if ($newPaid > $po->total_amount) {
            $newPaid = $po->total_amount;
        }

        $balance = $this->poRoundAmount($po->total_amount - $newPaid, 6);

        if ($newPaid <= 0) {
            $paymentStatus = 'unpaid';
        } elseif ($balance <= 0) {
            $paymentStatus = 'paid';
            $balance = 0;
        } else {
            $paymentStatus = 'partial';
        }

        $po->update([
            'amount_paid' => $newPaid,
            'balance' => $balance,
            'payment_status' => $paymentStatus,
            'payment_method' => $request->payment_method,
            'cheque_no' => $request->cheque_no,
            'payment_reference' => $request->payment_reference,
            'payment_date' => $request->payment_date,
            'payment_attachment' => $this->uploadPoFile($request, 'payment_attachment', $po->payment_attachment),
            'updated_by' => auth()->id(),
        ]);

        DB::commit();

        Alert::success('Success', 'Purchase order payment updated successfully');
        return redirect()->route('sales.po.index');

    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', $e->getMessage());
        return back();
    }
}

public function poDestroy($id)
{
    try {
        $realId = decrypt($id);

        $po = PurchaseOrder::findOrFail($realId);

        if ($po->status !== 'Draft') {
            Alert::error('Error', 'Only Draft purchase orders can be removed');
            return back();
        }

        $po->delete();

        Alert::success('Success', 'Purchase order removed successfully');
        return redirect()->route('sales.po.index');

    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to remove purchase order');
        return back();
    }
}

private function generatePurchaseOrderNumber($company)
{
    $year = date('Y');

    $count = PurchaseOrder::whereYear('created_at', $year)->count() + 1;

    $companyCode = $company->company_code ?? 'PO';

    return $companyCode . '/PO/' . str_pad($count, 4, '0', STR_PAD_LEFT) . '/' . $year;
}

private function preparePurchaseOrderItems($requestItems, $purchaseType, $vatRate, $vatInclusive = false)
{
    $prepared = [];

    foreach ($requestItems as $row) {
        $itemId = $row['item_id'] ?? null;
        $qty = $this->poRoundAmount($row['qty'] ?? 0, 6);

        // The form unit price is VAT-INCLUSIVE, the same way Proforma works.
        // We convert it to the actual VAT-EXCLUSIVE unit price before saving.
        $grossUnitPrice = $this->poRoundAmount($row['unit_price'] ?? 0, 6);

        if (!$itemId || $qty <= 0) {
            continue;
        }

        $itemName = '';
        $description = $row['description'] ?? null;
        $unit = $row['unit'] ?? null;
        $accountCode = $row['account_code'] ?? null;
        $accountName = $row['account_name'] ?? null;

        if ($purchaseType === 'RawMaterial') {
            $item = RawMaterial::find($itemId);

            if (!$item) {
                continue;
            }

            $itemName = $item->material_name ?? '';
            $unit = $unit ?: ($item->unit_name ?? $item->unit ?? 'PCS');
            $accountCode = $accountCode ?: ($item->account_code ?? $item->inventory_account_code ?? null);
            $accountName = $accountName ?: ($item->account_name ?? null);
        } else {
            $item = Product::find($itemId);

            if (!$item) {
                continue;
            }

            $itemName = $item->product_name ?? '';
            $unit = $unit ?: ($item->unit_name ?? $item->unit ?? 'PCS');
            $accountCode = $accountCode ?: ($item->account_code ?? $item->inventory_account_code ?? null);
            $accountName = $accountName ?: ($item->account_name ?? null);
        }

        if (empty($description)) {
            $description = $itemName;
        }

        if ($grossUnitPrice < 0) {
            $grossUnitPrice = 0;
        }

        $rate = $vatRate > 0 ? ($vatRate / 100) : 0;

        if ($vatInclusive && $rate > 0) {
            $actualUnitPrice = $this->poRoundAmount($grossUnitPrice / (1 + $rate), 6);
        } else {
            $actualUnitPrice = $grossUnitPrice;
        }

        // Stored sub_total and unit_price are VAT-exclusive actual values.
        // Stored total_price is the VAT-inclusive payable line amount.
        $lineSubTotal = $this->poRoundAmount($qty * $actualUnitPrice, 6);
        $lineVatAmount = ($vatInclusive && $rate > 0)
            ? $this->poRoundAmount($lineSubTotal * $rate, 6)
            : 0;
        $lineTotal = $this->poRoundAmount($lineSubTotal + $lineVatAmount, 6);

        $prepared[] = [
            'item_type' => $purchaseType,
            'item_id' => $itemId,
            'item_name' => $itemName,
            'description' => $description,
            'unit' => $unit,
            'qty' => $qty,
            'received_qty' => 0,
            'balance_qty' => $qty,
            'unit_price' => $actualUnitPrice,
            'sub_total' => $lineSubTotal,
            'vat_amount' => $lineVatAmount,
            'total_price' => $lineTotal,
            'account_code' => $accountCode,
            'account_name' => $accountName,
        ];
    }

    return $prepared;
}

private function uploadPoFile(Request $request, $field, $oldPath = null)
{
    if (!$request->hasFile($field)) {
        return $oldPath;
    }

    if ($oldPath && Storage::disk('public')->exists($oldPath)) {
        Storage::disk('public')->delete($oldPath);
    }

    return $request->file($field)->store('purchase_orders', 'public');
}

private function buildShipToAddress($company, $businessUnit, $workPoint)
{
    return trim(
        ($company->company_name ?? '') . "\n" .
        ($businessUnit->unit_name ?? '') . "\n" .
        ($workPoint->work_name ?? '') . "\n" .
        ($workPoint->location ?? '') . "\n" .
        ($workPoint->district ?? '') . ' ' . ($workPoint->city ?? '')
    );
}

private function buildVendorFromAddress($vendor)
{
    return trim(
        ($vendor->vendor_name ?? '') . "\n" .
        ($vendor->address ?? '') . "\n" .
        ($vendor->phone_no ?? '') . "\n" .
        ($vendor->email ?? '')
    );
}

private function documentUrl($path)
{
    if (!$path) {
        return null;
    }

    return Storage::disk('public')->url($path);
}
private function postPurchaseOrderAccounting($po)
{
    $po->load(['vendor', 'items', 'workPoint']);

    if ($po->accounting_transaction_group) {
        throw new \Exception('Accounting transaction already posted for this purchase order.');
    }

    /*
    |--------------------------------------------------------------------------
    | VENDOR PAYABLE ACCOUNT
    |--------------------------------------------------------------------------
    */
    $vendorSubAccount = $this->findPoSubAccountByCode(
        $po->company_id,
        $po->work_point_id,
        $po->vendor->account_code ?? null
    );

    if (!$vendorSubAccount) {
        throw new \Exception('Vendor payable account is not mapped. Please set vendor account_code to an active 8-digit account.');
    }

    $vendorChart = $this->chartFromPoSubAccount($vendorSubAccount);

    if (!$vendorChart) {
        throw new \Exception('Vendor account chart is not mapped correctly.');
    }

    /*
    |--------------------------------------------------------------------------
    | DEFAULT SECTION
    |--------------------------------------------------------------------------
    */
    $sec = Section::where('secCode', 'BDD00102')->first();

    if (!$sec) {
        throw new \Exception('Default accounting section BDD00102 was not found.');
    }

    $transactionGroup = (string) Str::uuid();

    $memo = 'Approved purchase order ' . $po->po_no;
    $payee = optional($po->vendor)->vendor_name ?? 'Vendor';

    /*
    |--------------------------------------------------------------------------
    | DEBIT ITEMS / RAW MATERIALS
    |--------------------------------------------------------------------------
    */
    foreach ($po->items as $index => $item) {
        $debitSubAccount = $this->findPoSubAccountByCode(
            $po->company_id,
            $po->work_point_id,
            $item->account_code
        );

        if (!$debitSubAccount) {
            throw new \Exception('Purchase item account is not mapped for item: ' . $item->item_name);
        }

        $debitChart = $this->chartFromPoSubAccount($debitSubAccount);

        if (!$debitChart) {
            throw new \Exception('Purchase item chart is not mapped correctly for item: ' . $item->item_name);
        }

        $this->createPoAccountingLine([
            'transaction_group' => $transactionGroup,
            'reference' => $po->po_no . '-DR-ITEM-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
            'memo' => $memo . ' | ' . $item->item_name,
            'payee' => $payee,
            'po' => $po,
            'account_id' => $debitChart->id,
            'sub_account_id' => $debitSubAccount->id,
            'department_id' => $sec->dept_id,
            'section_id' => $sec->id,
            'type' => 'debit',
            'amount' => $item->sub_total,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DEBIT PURCHASE VAT INPUT
    |--------------------------------------------------------------------------
    | Account: 25720102 - VAT- input Tax (Tax on purchases/services)
    |--------------------------------------------------------------------------
    */
    if ((float) $po->vat_amount > 0) {
        $vatSubAccount = $this->purchaseVatSubAccount($po);

        if (!$vatSubAccount) {
            throw new \Exception('Purchase VAT account is not mapped. Please create active 8-digit account 25720102 - VAT- input Tax (Tax on purchases/services).');
        }

        $vatChart = $this->chartFromPoSubAccount($vatSubAccount);

        if (!$vatChart) {
            throw new \Exception('VAT account chart is not mapped correctly for SubCode ' . $vatSubAccount->SubCode);
        }

        $this->createPoAccountingLine([
            'transaction_group' => $transactionGroup,
            'reference' => $po->po_no . '-DR-VAT',
            'memo' => $memo . ' | VAT Input',
            'payee' => $payee,
            'po' => $po,
            'account_id' => $vatChart->id,
            'sub_account_id' => $vatSubAccount->id,
            'department_id' => $sec->dept_id,
            'section_id' => $sec->id,
            'type' => 'debit',
            'amount' => $po->vat_amount,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREDIT PURCHASE DISCOUNT / BILLS AT DISCOUNT
    |--------------------------------------------------------------------------
    | Account: 55450100 - Bills at discount
    |--------------------------------------------------------------------------
    */
    if ((float) $po->discount > 0) {
        $discountSubAccount = $this->purchaseDiscountSubAccount($po);

        if (!$discountSubAccount) {
            throw new \Exception('Purchase discount account is not mapped. Please create active 8-digit account 55450100 - Bills at discount.');
        }

        $discountChart = $this->chartFromPoSubAccount($discountSubAccount);

        if (!$discountChart) {
            throw new \Exception('Purchase discount chart is not mapped correctly for SubCode ' . $discountSubAccount->SubCode);
        }

        $this->createPoAccountingLine([
            'transaction_group' => $transactionGroup,
            'reference' => $po->po_no . '-CR-DISCOUNT',
            'memo' => $memo . ' | Bills/Purchase Discount',
            'payee' => $payee,
            'po' => $po,
            'account_id' => $discountChart->id,
            'sub_account_id' => $discountSubAccount->id,
            'department_id' => $sec->dept_id,
            'section_id' => $sec->id,
            'type' => 'credit',
            'amount' => $po->discount,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREDIT VENDOR PAYABLE
    |--------------------------------------------------------------------------
    */
    $this->createPoAccountingLine([
        'transaction_group' => $transactionGroup,
        'reference' => $po->po_no . '-CR-VENDOR',
        'memo' => $memo,
        'payee' => $payee,
        'po' => $po,
        'account_id' => $vendorChart->id,
        'sub_account_id' => $vendorSubAccount->id,
        'department_id' => $sec->dept_id,
        'section_id' => $sec->id,
        'type' => 'credit',
        'amount' => $po->total_amount,
    ]);

    return $transactionGroup;
}

private function createPoAccountingLine(array $data)
{
    $po = $data['po'];

    return AccntTransaction::create([
        'transaction_group' => $data['transaction_group'],
        'pcv_no' => null,
        'trans_date' => now()->toDateString(),
        'reference' => $data['reference'],
        'check_no' => null,
        'request_no' => null,
        'category' => 'Purchase Order',
        'currency' => $po->currency,
        'exchange_rate' => $po->exchange_rate,
        'memo' => $data['memo'],
        'payee' => $data['payee'],
        'user_id' => auth()->id(),
        'company_id' => $po->company_id,
        'work_point_id' => $po->work_point_id,
        'account_id' => $data['account_id'],
        'sub_account_id' => $data['sub_account_id'],
        'department_id' => $data['department_id'],
        'section_id' => $data['section_id'],
        'type' => $data['type'],
        'amount' => $data['amount'],
        'source_amount' => $data['amount'],
        'imported_from_excel' => false,
        'Status' => 'Active',
        'verified' => 'verified',
        'verified_by' => auth()->id(),
        'verified_at' => now(),
        'verification_comment' => 'Auto verified from purchase order ' . $po->po_no,
        'approved' => 'approved',
        'approved_by' => auth()->id(),
        'approved_at' => now(),
        'approval_comment' => 'Auto approved from purchase order ' . $po->po_no,
    ]);
}
private function findPoSubAccountByCode($companyId, $workPointId, $subCode)
{
    if (empty($subCode)) {
        return null;
    }
    $subCode = trim($subCode);
    /*
    |--------------------------------------------------------------------------
    | 1. Exact 8-digit sub-account
    |--------------------------------------------------------------------------
    | Example: 40140101
    */
    if (strlen($subCode) === 8) {
        $account = AccntSubchart::where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode) = 8')
            ->where('SubCode', $subCode)
            ->first();
        if ($account) {
            return $account;
        }
    }
    /*
    |--------------------------------------------------------------------------
    | 2. 6-digit parent account
    |--------------------------------------------------------------------------
    | Example: 401401 should find 40140101 or first active child.
    */
    if (strlen($subCode) === 6) {
        $account = AccntSubchart::where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode) = 8')
            ->where('SubCode', 'LIKE', $subCode . '%')
            ->orderByRaw("CASE WHEN SubCode = ? THEN 0 ELSE 1 END", [$subCode . '01'])
            ->orderBy('SubCode')
            ->first();
        if ($account) {
            return $account;
        }
    }
    /*
    |--------------------------------------------------------------------------
    | 3. Final fallback
    |--------------------------------------------------------------------------
    */
    return AccntSubchart::where('Status', 'Active')
        ->whereRaw('LENGTH(SubCode) = 8')
        ->where(function ($q) use ($subCode) {
            $q->where('SubCode', $subCode)
                ->orWhere('SubCode', 'LIKE', $subCode . '%');
        })
        ->orderByRaw("CASE WHEN SubCode = ? THEN 0 ELSE 1 END", [$subCode . '01'])
        ->orderBy('SubCode')
        ->first();
}
private function chartFromPoSubAccount($subAccount)
{
    if (!$subAccount) {
        return null;
    }
    if (!empty($subAccount->chart_id)) {
        return AccntChart::find($subAccount->chart_id);
    }
    $rootCode = substr($subAccount->SubCode, 0, 2);
    return AccntChart::where('AccCode', $rootCode)->first();
}
private function purchaseVatSubAccount($po)
{
    return AccntSubchart::where('Status', 'Active')
        ->whereRaw('LENGTH(SubCode) = 8')
        ->where(function ($q) {
            $q->where('SubCode', '25720102')
                ->orWhere('SubDescription', 'LIKE', '%VAT- input Tax%')
                ->orWhere('SubDescription', 'LIKE', '%Tax on purchases%')
                ->orWhere('SubDescription', 'LIKE', '%purchases/services%');
        })
        ->orderByRaw("CASE WHEN SubCode = '25720102' THEN 0 ELSE 1 END")
        ->orderBy('SubCode')
        ->first();
}
private function purchaseDiscountSubAccount($po)
{
    return AccntSubchart::where('Status', 'Active')
        ->whereRaw('LENGTH(SubCode) = 8')
        ->where(function ($q) {
            $q->where('SubCode', '55450100')
                ->orWhere('SubDescription', 'LIKE', '%Bills at discount%')
                ->orWhere('SubDescription', 'LIKE', '%Purchase discount%');
        })
        ->orderByRaw("CASE WHEN SubCode = '55450100' THEN 0 ELSE 1 END")
        ->orderBy('SubCode')
        ->first();
}
// ================= PURCHASE ORDER AJAX BUSINESS UNITS =================
public function poBusinessUnits($company_id)
{
    return Company_unit::where('company_id', $company_id)
        ->where('status', 'Active')
        ->select(
            'id',
            'unit_code',
            'unit_name'
        )
        ->orderBy('unit_name')
        ->get();
}
// ================= PURCHASE ORDER AJAX WORK POINTS =================
public function poWorkPoints($business_id)
{
    return WorkPoint::where('comp_unit_id', $business_id)
        ->where('status', 'Active')
        ->select(
            'id',
            'work_code',
            'work_name',
            'location',
            'district',
            'city'
        )
        ->orderBy('work_name')
        ->get();
}
public function poEdit($id)
{
    $realId = decrypt($id);

    $order = PurchaseOrder::with([
            'company',
            'businessUnit',
            'workPoint',
            'vendor',
            'items',
        ])
        ->findOrFail($realId);

    if (!in_array($order->status, ['Draft', 'Cancelled'])) {
        Alert::error('Error', 'Only Draft or Rejected purchase orders can be edited.');
        return redirect()->route('sales.po.index');
    }

    $companies = CompanySite::where('status', 'Active')
        ->orderBy('company_name')
        ->get();

    $vendors = Vendor::where('status', 'Active')
        ->orderBy('vendor_name')
        ->get();

    $items = Product::where('status', 'Active')
        ->orderBy('product_name')
        ->get();

    $rawMaterials = RawMaterial::where('status', 'Active')
        ->orderBy('material_name')
        ->get();

    return view('admin.store.purchase_order_edit', compact(
        'order',
        'companies',
        'vendors',
        'items',
        'rawMaterials'
    ));
}
public function poReject($id)
{
    try {
        $realId = decrypt($id);

        $po = PurchaseOrder::findOrFail($realId);

        if ($po->status !== 'Draft') {
            Alert::error('Error', 'Only Draft purchase orders can be rejected.');
            return back();
        }

        $po->update([
            'status' => 'Cancelled',
            'updated_by' => auth()->id(),
        ]);

        Alert::success('Success', 'Purchase order rejected successfully.');
        return redirect()->route('sales.po.index');

    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to reject purchase order.');
        return back();
    }
}
public function poDocumentFile($id, $type, $action)
{
    $realId = decrypt($id);
    $order = PurchaseOrder::findOrFail($realId);
    $allowedTypes = [
        'proforma' => 'supplier_proforma_attachment',
        'invoice' => 'supplier_invoice_attachment',
        'delivery_note' => 'delivery_note_attachment',
        'payment' => 'payment_attachment',
    ];
    if (!array_key_exists($type, $allowedTypes)) {
        abort(404, 'Invalid document type.');
    }
    if (!in_array($action, ['open', 'download'])) {
        abort(404, 'Invalid document action.');
    }
    $column = $allowedTypes[$type];
    $path = $order->{$column};
    if (!$path) {
        abort(404, 'No document uploaded.');
    }
    if (!Storage::disk('public')->exists($path)) {
        abort(404, 'File not found in storage/app/public.');
    }
    if ($action === 'download') {
        return Storage::disk('public')->download($path);
    }
    return response()->file(Storage::disk('public')->path($path));
}
// ================= CONTRACTS =================
public function contractsIndex()
{
    $contracts = Contract::with('vendor')->orderByDesc('id')->get();
    $vendors = Vendor::where('status', 'Active')->orderBy('vendor_name')->get();

    return view('admin.store.contracts', compact('contracts', 'vendors'));
}

public function contractsStore(Request $request)
{
    try {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'contract_no' => 'nullable|string|max:255',
            'contract_title' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'contract_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'required|in:Active,Inactive,Closed',
        ]);

        $user = auth()->user();

        Contract::create([
            'company_id' => $user->company_id,
            'comp_unit_id' => $user->comp_unit_id,
            'vendor_id' => $request->vendor_id,
            'contract_no' => $request->contract_no,
            'contract_title' => $request->contract_title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'contract_amount' => $request->contract_amount,
            'remarks' => $request->remarks,
            'status' => $request->status,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Alert::success('Success', 'Contract created successfully');
        return redirect()->route('sales.contracts.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to create contract');
        return back()->withInput();
    }
}

public function contractsUpdate(Request $request, $id)
{
    try {
        $realId = decrypt($id);
        $row = Contract::findOrFail($realId);

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'contract_no' => 'nullable|string|max:255',
            'contract_title' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'contract_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'required|in:Active,Inactive,Closed',
        ]);

        $row->update([
            'vendor_id' => $request->vendor_id,
            'contract_no' => $request->contract_no,
            'contract_title' => $request->contract_title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'contract_amount' => $request->contract_amount,
            'remarks' => $request->remarks,
            'status' => $request->status,
            'updated_by' => auth()->id(),
        ]);

        Alert::success('Success', 'Contract updated successfully');
        return redirect()->route('sales.contracts.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to update contract');
        return back()->withInput();
    }
}

public function contractsDestroy($id)
{
    try {
        $realId = decrypt($id);
        Contract::findOrFail($realId)->delete();

        Alert::success('Success', 'Contract removed successfully');
        return redirect()->route('sales.contracts.index');
    } catch (\Throwable $e) {
        Alert::error('Error', 'Failed to remove contract');
        return back();
    }
}
// ================= RAW MATERIAL PURCHASES =================
public function rmPurchaseIndex()
{
    $rows = RawMaterialPurchase::with(['vendor', 'material', 'workpoint'])
        ->orderByDesc('id')
        ->get();

    $vendors = Vendor::where('status', 'Active')->orderBy('vendor_name')->get();
    $materials = RawMaterial::where('status', 'Active')->orderBy('material_name')->get();
    $workPoints = $this->gsAllowedWorkPoints(auth()->user());

    return view('admin.store.raw_material_purchases', compact('rows', 'vendors', 'materials', 'workPoints'));
}

public function rmPurchaseStore(Request $request)
{
    try {
        $request->validate([
            'vendor_id' => 'nullable|exists:vendors,id',
            'raw_material_id' => 'required|exists:raw_materials,id',
            'work_point_id' => 'nullable|exists:work_points,id',
            'purchase_date' => 'required|date',
            'qty' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'invoice_no' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'status' => 'required|in:Purchased,Pending,Cancelled',
        ]);

        $user = auth()->user();
        $workPoint = $request->work_point_id ? WorkPoint::find($request->work_point_id) : null;
        $qty = (float) $request->qty;
        $unitPrice = (float) $request->unit_price;
        $total = $qty * $unitPrice;

        $companyId = $workPoint ? $workPoint->company_id : $user->company_id;
        $unitId = $workPoint ? $workPoint->comp_unit_id : $user->comp_unit_id;

        DB::beginTransaction();

        $purchase = RawMaterialPurchase::create([
            'company_id' => $companyId,
            'comp_unit_id' => $unitId,
            'work_point_id' => $request->work_point_id,
            'vendor_id' => $request->vendor_id,
            'raw_material_id' => $request->raw_material_id,
            'purchase_date' => $request->purchase_date,
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'total_price' => $total,
            'invoice_no' => $request->invoice_no,
            'remarks' => $request->remarks,
            'status' => $request->status,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $stock = RawMaterialStock::where('company_id', $companyId)
            ->where('comp_unit_id', $unitId)
            ->where('work_point_id', $request->work_point_id)
            ->where('raw_material_id', $request->raw_material_id)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            $stock->qty_in += $qty;
            $stock->balance = $stock->qty_in - $stock->qty_out;
            $stock->unit_price = $unitPrice;
            $stock->status = 'Active';
            $stock->save();
        } else {
            RawMaterialStock::create([
                'company_id' => $companyId,
                'comp_unit_id' => $unitId,
                'work_point_id' => $request->work_point_id,
                'raw_material_id' => $request->raw_material_id,
                'qty_in' => $qty,
                'qty_out' => 0,
                'balance' => $qty,
                'unit_price' => $unitPrice,
                'status' => 'Active',
            ]);
        }

        DB::commit();

        Alert::success('Success', 'Raw material purchase created successfully');
        return redirect()->route('sales.rm.purchase.index');
    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', 'Failed to create raw material purchase');
        return back()->withInput();
    }
}

public function rmPurchaseUpdate(Request $request, $id)
{
    try {
        $realId = decrypt($id);
        $row = RawMaterialPurchase::findOrFail($realId);

        $request->validate([
            'vendor_id' => 'nullable|exists:vendors,id',
            'raw_material_id' => 'required|exists:raw_materials,id',
            'work_point_id' => 'nullable|exists:work_points,id',
            'purchase_date' => 'required|date',
            'qty' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'invoice_no' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'status' => 'required|in:Purchased,Pending,Cancelled',
        ]);

        $workPoint = $request->work_point_id ? WorkPoint::find($request->work_point_id) : null;
        $qty = (float) $request->qty;
        $unitPrice = (float) $request->unit_price;
        $total = $qty * $unitPrice;

        $companyId = $workPoint ? $workPoint->company_id : $row->company_id;
        $unitId = $workPoint ? $workPoint->comp_unit_id : $row->comp_unit_id;

        DB::beginTransaction();

        $oldStock = RawMaterialStock::where('company_id', $row->company_id)
            ->where('comp_unit_id', $row->comp_unit_id)
            ->where('work_point_id', $row->work_point_id)
            ->where('raw_material_id', $row->raw_material_id)
            ->lockForUpdate()
            ->first();

        if ($oldStock) {
            if (($oldStock->balance - $row->qty) < 0) {
                DB::rollBack();
                Alert::error('Error', 'Cannot update purchase because some stock was already issued');
                return back()->withInput();
            }

            $oldStock->qty_in -= $row->qty;
            if ($oldStock->qty_in < 0) {
                $oldStock->qty_in = 0;
            }

            $oldStock->balance = $oldStock->qty_in - $oldStock->qty_out;
            if ($oldStock->balance < 0) {
                $oldStock->balance = 0;
            }

            $oldStock->save();
        }

        $newStock = RawMaterialStock::where('company_id', $companyId)
            ->where('comp_unit_id', $unitId)
            ->where('work_point_id', $request->work_point_id)
            ->where('raw_material_id', $request->raw_material_id)
            ->lockForUpdate()
            ->first();

        if ($newStock) {
            $newStock->qty_in += $qty;
            $newStock->balance = $newStock->qty_in - $newStock->qty_out;
            $newStock->unit_price = $unitPrice;
            $newStock->status = 'Active';
            $newStock->save();
        } else {
            RawMaterialStock::create([
                'company_id' => $companyId,
                'comp_unit_id' => $unitId,
                'work_point_id' => $request->work_point_id,
                'raw_material_id' => $request->raw_material_id,
                'qty_in' => $qty,
                'qty_out' => 0,
                'balance' => $qty,
                'unit_price' => $unitPrice,
                'status' => 'Active',
            ]);
        }

        $row->update([
            'company_id' => $companyId,
            'comp_unit_id' => $unitId,
            'work_point_id' => $request->work_point_id,
            'vendor_id' => $request->vendor_id,
            'raw_material_id' => $request->raw_material_id,
            'purchase_date' => $request->purchase_date,
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'total_price' => $total,
            'invoice_no' => $request->invoice_no,
            'remarks' => $request->remarks,
            'status' => $request->status,
            'updated_by' => auth()->id(),
        ]);

        DB::commit();

        Alert::success('Success', 'Raw material purchase updated successfully');
        return redirect()->route('sales.rm.purchase.index');
    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', 'Failed to update raw material purchase');
        return back()->withInput();
    }
}

public function rmPurchaseDestroy($id)
{
    try {
        $realId = decrypt($id);
        $row = RawMaterialPurchase::findOrFail($realId);

        DB::beginTransaction();

        $stock = RawMaterialStock::where('company_id', $row->company_id)
            ->where('comp_unit_id', $row->comp_unit_id)
            ->where('work_point_id', $row->work_point_id)
            ->where('raw_material_id', $row->raw_material_id)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            if (($stock->balance - $row->qty) < 0) {
                DB::rollBack();
                Alert::error('Error', 'Cannot remove purchase because some stock was already issued');
                return back();
            }

            $stock->qty_in -= $row->qty;
            if ($stock->qty_in < 0) {
                $stock->qty_in = 0;
            }

            $stock->balance = $stock->qty_in - $stock->qty_out;
            if ($stock->balance < 0) {
                $stock->balance = 0;
            }

            $stock->save();
        }

        $row->delete();

        DB::commit();

        Alert::success('Success', 'Raw material purchase removed successfully');
        return redirect()->route('sales.rm.purchase.index');
    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', 'Failed to remove raw material purchase');
        return back();
    }
}
// ================= RAW MATERIAL STOCK =================
public function rmStockIndex()
{
    $rows = RawMaterialStock::with(['company', 'unit', 'material', 'workpoint'])
        ->orderByDesc('id')->get();
    return view('admin.store.raw_material_stock', compact('rows'));
}


// ================= RAW MATERIAL ISSUES =================
public function rmIssueIndex()
{
    $user = auth()->user();

    $rows = RawMaterialIssue::with(['company', 'unit', 'workpoint', 'material'])
        ->orderByDesc('id')
        ->get();

    $materials = RawMaterial::where('status', 'Active')->orderBy('material_name')->get();
    $workPoints = $this->gsAllowedWorkPoints($user);

    // Manufacturing requests waiting for issue
    $requests = RawMaterialRequest::with(['rawMaterial', 'workPoint', 'requester'])
        ->where('company_id', $user->company_id)
        ->whereIn('status', ['Pending', 'Partially Issued'])
        ->orderByDesc('id')
        ->get();

    return view('admin.store.raw_material_issues', compact('rows', 'materials', 'workPoints', 'requests'));
}

public function rmIssueStore(Request $request)
{
    dd($request->all());
    try {
        $request->validate([
            'manufacturing_request_id' => 'nullable|exists:raw_material_requests,id',
            'raw_material_id' => 'required|exists:raw_materials,id',
            'work_point_id' => 'nullable|exists:work_points,id',
            'issue_to_work_point_id' => 'nullable|exists:work_points,id',
            'issue_to_type' => 'required|string|max:255',
            'issue_to_name' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'issued_qty' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string',
        ]);

        $user = auth()->user();
        $workPoint = $request->work_point_id ? WorkPoint::find($request->work_point_id) : null;
        $companyId = $workPoint ? $workPoint->company_id : $user->company_id;
        $unitId = $workPoint ? $workPoint->comp_unit_id : $user->comp_unit_id;

        DB::beginTransaction();

        $linkedRequest = null;

        if ($request->filled('manufacturing_request_id')) {
            $linkedRequest = RawMaterialRequest::lockForUpdate()->findOrFail($request->manufacturing_request_id);

            if ((int) $linkedRequest->raw_material_id !== (int) $request->raw_material_id) {
                DB::rollBack();
                Alert::error('Error', 'Selected material does not match the manufacturing request.');
                return back()->withInput();
            }

            if ((float) $linkedRequest->remaining_qty <= 0) {
                DB::rollBack();
                Alert::error('Error', 'This request is already fully issued.');
                return back()->withInput();
            }

            if ((float) $request->issued_qty > (float) $linkedRequest->remaining_qty) {
                DB::rollBack();
                Alert::error('Error', 'Issued qty cannot exceed request remaining qty.');
                return back()->withInput();
            }
        }

        $stock = RawMaterialStock::where('company_id', $companyId)
            ->where('comp_unit_id', $unitId)
            ->where('work_point_id', $request->work_point_id)
            ->where('raw_material_id', $request->raw_material_id)
            ->lockForUpdate()
            ->first();

        if (!$stock || (float) $stock->balance < (float) $request->issued_qty) {
            DB::rollBack();
            Alert::error('Error', 'Insufficient raw material stock');
            return back()->withInput();
        }

        $stock->qty_out += (float) $request->issued_qty;
        $stock->balance = $stock->qty_in - $stock->qty_out;
        $stock->save();

        $issue = RawMaterialIssue::create([
            'company_id' => $companyId,
            'comp_unit_id' => $unitId,
            'work_point_id' => $request->work_point_id,
            'raw_material_id' => $request->raw_material_id,
            'manufacturing_request_id' => $request->manufacturing_request_id,
            'issue_to_work_point_id' => $request->issue_to_work_point_id,
            'issue_to_type' => $request->issue_to_type,
            'issue_to_name' => $request->issue_to_name,
            'issue_date' => $request->issue_date,
            'issued_qty' => $request->issued_qty,
            'remarks' => $request->remarks,
            'issued_by' => $user->id,
            'status' => 'Issued',
        ]);

        if ($linkedRequest) {
            $newIssued = (float) $linkedRequest->issued_qty + (float) $request->issued_qty;
            $newRemaining = (float) $linkedRequest->requested_qty - $newIssued;

            $linkedRequest->issued_qty = $newIssued;
            $linkedRequest->remaining_qty = $newRemaining < 0 ? 0 : $newRemaining;

            if ((float) $linkedRequest->remaining_qty <= 0) {
                $linkedRequest->status = 'Fully Issued';
            } elseif ((float) $linkedRequest->issued_qty > 0) {
                $linkedRequest->status = 'Partially Issued';
            } else {
                $linkedRequest->status = 'Pending';
            }

            $linkedRequest->updated_by = $user->id;
            $linkedRequest->save();
        }

        DB::commit();

        Alert::success('Success', 'Raw material issued successfully');
        return redirect()->route('sales.rm.issue.index');
    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', 'Failed to issue raw material');
        return back()->withInput();
    }
}

public function rmIssueUpdate(Request $request, $id)
{
    try {
        $realId = decrypt($id);
        $row = RawMaterialIssue::findOrFail($realId);

        $request->validate([
            'issue_to_type' => 'required|string|max:255',
            'issue_to_name' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'issued_qty' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();

        // Restrict edit if already received in manufacturing
        $receivedQty = (float) ManufacturingReceipt::where('raw_material_issue_id', $row->id)->sum('received_qty');
        if ($receivedQty > 0) {
            DB::rollBack();
            Alert::error('Error', 'This issue already has manufacturing receipt records and cannot be edited.');
            return back()->withInput();
        }

        $stock = RawMaterialStock::where('company_id', $row->company_id)
            ->where('comp_unit_id', $row->comp_unit_id)
            ->where('work_point_id', $row->work_point_id)
            ->where('raw_material_id', $row->raw_material_id)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            DB::rollBack();
            Alert::error('Error', 'Stock row not found');
            return back()->withInput();
        }

        $linkedRequest = null;
        if ($row->manufacturing_request_id) {
            $linkedRequest = RawMaterialRequest::lockForUpdate()->find($row->manufacturing_request_id);
        }

        // reverse old stock effect
        $stock->qty_out -= (float) $row->issued_qty;
        if ($stock->qty_out < 0) $stock->qty_out = 0;
        $stock->balance = $stock->qty_in - $stock->qty_out;

        if ((float) $stock->balance < (float) $request->issued_qty) {
            DB::rollBack();
            Alert::error('Error', 'Insufficient stock for updated quantity');
            return back()->withInput();
        }

        // reverse old request effect
        if ($linkedRequest) {
            $linkedRequest->issued_qty -= (float) $row->issued_qty;
            if ($linkedRequest->issued_qty < 0) $linkedRequest->issued_qty = 0;

            $linkedRequest->remaining_qty = (float) $linkedRequest->requested_qty - (float) $linkedRequest->issued_qty;

            if ((float) $request->issued_qty > (float) $linkedRequest->remaining_qty) {
                DB::rollBack();
                Alert::error('Error', 'Updated issue qty cannot exceed request remaining qty.');
                return back()->withInput();
            }
        }

        // apply new stock effect
        $stock->qty_out += (float) $request->issued_qty;
        $stock->balance = $stock->qty_in - $stock->qty_out;
        $stock->save();

        // apply new request effect
        if ($linkedRequest) {
            $linkedRequest->issued_qty += (float) $request->issued_qty;
            $linkedRequest->remaining_qty = (float) $linkedRequest->requested_qty - (float) $linkedRequest->issued_qty;

            if ((float) $linkedRequest->remaining_qty <= 0) {
                $linkedRequest->status = 'Fully Issued';
            } elseif ((float) $linkedRequest->issued_qty > 0) {
                $linkedRequest->status = 'Partially Issued';
            } else {
                $linkedRequest->status = 'Pending';
            }

            $linkedRequest->updated_by = auth()->id();
            $linkedRequest->save();
        }

        $row->update([
            'issue_to_type' => $request->issue_to_type,
            'issue_to_name' => $request->issue_to_name,
            'issue_date' => $request->issue_date,
            'issued_qty' => $request->issued_qty,
            'remarks' => $request->remarks,
        ]);

        DB::commit();

        Alert::success('Success', 'Raw material issue updated successfully');
        return redirect()->route('sales.rm.issue.index');
    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', 'Failed to update raw material issue');
        return back()->withInput();
    }
}

public function rmIssueDestroy($id)
{
    try {
        $realId = decrypt($id);
        $row = RawMaterialIssue::findOrFail($realId);

        DB::beginTransaction();

        // Restrict delete if already received in manufacturing
        $receivedQty = (float) ManufacturingReceipt::where('raw_material_issue_id', $row->id)->sum('received_qty');
        if ($receivedQty > 0) {
            DB::rollBack();
            Alert::error('Error', 'This issue already has manufacturing receipt records and cannot be removed.');
            return back();
        }

        $stock = RawMaterialStock::where('company_id', $row->company_id)
            ->where('comp_unit_id', $row->comp_unit_id)
            ->where('work_point_id', $row->work_point_id)
            ->where('raw_material_id', $row->raw_material_id)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            $stock->qty_out -= (float) $row->issued_qty;
            if ($stock->qty_out < 0) $stock->qty_out = 0;
            $stock->balance = $stock->qty_in - $stock->qty_out;
            $stock->save();
        }

        if ($row->manufacturing_request_id) {
            $linkedRequest = RawMaterialRequest::lockForUpdate()->find($row->manufacturing_request_id);
            if ($linkedRequest) {
                $linkedRequest->issued_qty -= (float) $row->issued_qty;
                if ($linkedRequest->issued_qty < 0) $linkedRequest->issued_qty = 0;

                $linkedRequest->remaining_qty = (float) $linkedRequest->requested_qty - (float) $linkedRequest->issued_qty;

                if ((float) $linkedRequest->remaining_qty <= 0) {
                    $linkedRequest->status = 'Fully Issued';
                } elseif ((float) $linkedRequest->issued_qty > 0) {
                    $linkedRequest->status = 'Partially Issued';
                } else {
                    $linkedRequest->status = 'Pending';
                }

                $linkedRequest->updated_by = auth()->id();
                $linkedRequest->save();
            }
        }

        $row->delete();

        DB::commit();

        Alert::success('Success', 'Raw material issue removed successfully');
        return redirect()->route('sales.rm.issue.index');
    } catch (\Throwable $e) {
        DB::rollBack();
        Alert::error('Error', 'Failed to remove raw material issue');
        return back();
    }
}
// ================= STORE REPORTS =================
public function storeReportsIndex(Request $request)
{
    $startDate = $request->get('start_date', now()->toDateString());
    $endDate = $request->get('end_date', now()->toDateString());
    $workPointId = $request->get('work_point_id');

    $workPoints = $this->gsAllowedWorkPoints(auth()->user());

    // =========================
    // GENERAL SUPPLY REPORTS
    // =========================
    $gsReceivedQuery = GeneralSupplyReceiving::with(['item', 'description', 'workpoint'])
        ->whereBetween('receive_date', [$startDate, $endDate]);

    $gsIssuedQuery = GeneralSupplyIssue::with(['item', 'description', 'workpoint'])
        ->whereBetween('issue_date', [$startDate, $endDate]);

    if (!empty($workPointId)) {
        $gsReceivedQuery->where('work_point_id', $workPointId);
        $gsIssuedQuery->where('work_point_id', $workPointId);
    }

    $gsReceived = $gsReceivedQuery->orderByDesc('id')->get();
    $gsIssued = $gsIssuedQuery->orderByDesc('id')->get();

    $gsOpeningReceivedQuery = GeneralSupplyReceiving::with(['item', 'description', 'workpoint'])
        ->whereDate('receive_date', '<', $startDate);

    $gsOpeningIssuedQuery = GeneralSupplyIssue::with(['item', 'description', 'workpoint'])
        ->whereDate('issue_date', '<', $startDate);

    if (!empty($workPointId)) {
        $gsOpeningReceivedQuery->where('work_point_id', $workPointId);
        $gsOpeningIssuedQuery->where('work_point_id', $workPointId);
    }

    $gsOpeningReceived = $gsOpeningReceivedQuery->get();
    $gsOpeningIssued = $gsOpeningIssuedQuery->get();

    $gsMovement = [];

    foreach ($gsOpeningReceived as $row) {
        $key = $row->work_point_id . '_' . $row->item_id . '_' . $row->item_description_id;

        if (!isset($gsMovement[$key])) {
            $gsMovement[$key] = [
                'work_point' => optional($row->workpoint)->work_code
                    ? optional($row->workpoint)->work_code . ' - ' . optional($row->workpoint)->work_name
                    : optional($row->workpoint)->work_name ?? '-',
                'item_name' => optional($row->item)->item_name ?? '-',
                'description_name' => optional($row->description)->description_name ?? '-',
                'opening' => 0,
                'received' => 0,
                'issued' => 0,
                'closing' => 0,
            ];
        }

        $gsMovement[$key]['opening'] += (float) ($row->good_qty ?? $row->received_qty ?? 0);
    }

    foreach ($gsOpeningIssued as $row) {
        $key = $row->work_point_id . '_' . $row->item_id . '_' . $row->item_description_id;

        if (!isset($gsMovement[$key])) {
            $gsMovement[$key] = [
                'work_point' => optional($row->workpoint)->work_code
                    ? optional($row->workpoint)->work_code . ' - ' . optional($row->workpoint)->work_name
                    : optional($row->workpoint)->work_name ?? '-',
                'item_name' => optional($row->item)->item_name ?? '-',
                'description_name' => optional($row->description)->description_name ?? '-',
                'opening' => 0,
                'received' => 0,
                'issued' => 0,
                'closing' => 0,
            ];
        }

        $gsMovement[$key]['opening'] -= (float) ($row->issued_qty ?? 0);
    }

    foreach ($gsReceived as $row) {
        $key = $row->work_point_id . '_' . $row->item_id . '_' . $row->item_description_id;

        if (!isset($gsMovement[$key])) {
            $gsMovement[$key] = [
                'work_point' => optional($row->workpoint)->work_code
                    ? optional($row->workpoint)->work_code . ' - ' . optional($row->workpoint)->work_name
                    : optional($row->workpoint)->work_name ?? '-',
                'item_name' => optional($row->item)->item_name ?? '-',
                'description_name' => optional($row->description)->description_name ?? '-',
                'opening' => 0,
                'received' => 0,
                'issued' => 0,
                'closing' => 0,
            ];
        }

        $gsMovement[$key]['received'] += (float) ($row->good_qty ?? $row->received_qty ?? 0);
    }

    foreach ($gsIssued as $row) {
        $key = $row->work_point_id . '_' . $row->item_id . '_' . $row->item_description_id;

        if (!isset($gsMovement[$key])) {
            $gsMovement[$key] = [
                'work_point' => optional($row->workpoint)->work_code
                    ? optional($row->workpoint)->work_code . ' - ' . optional($row->workpoint)->work_name
                    : optional($row->workpoint)->work_name ?? '-',
                'item_name' => optional($row->item)->item_name ?? '-',
                'description_name' => optional($row->description)->description_name ?? '-',
                'opening' => 0,
                'received' => 0,
                'issued' => 0,
                'closing' => 0,
            ];
        }

        $gsMovement[$key]['issued'] += (float) ($row->issued_qty ?? 0);
    }

    foreach ($gsMovement as $key => $row) {
        $gsMovement[$key]['closing'] = $row['opening'] + $row['received'] - $row['issued'];
    }

    $gsMovement = array_values($gsMovement);

    // =========================
    // RAW MATERIAL REPORTS
    // =========================
    $rmPurchasesQuery = RawMaterialPurchase::with(['material', 'workpoint'])
        ->whereBetween('purchase_date', [$startDate, $endDate]);

    $rmIssuesQuery = RawMaterialIssue::with(['material', 'workpoint'])
        ->whereBetween('issue_date', [$startDate, $endDate]);

    if (!empty($workPointId)) {
        $rmPurchasesQuery->where('work_point_id', $workPointId);
        $rmIssuesQuery->where('work_point_id', $workPointId);
    }

    $rmPurchases = $rmPurchasesQuery->orderByDesc('id')->get();
    $rmIssues = $rmIssuesQuery->orderByDesc('id')->get();

    $rmOpeningPurchasesQuery = RawMaterialPurchase::with(['material', 'workpoint'])
        ->whereDate('purchase_date', '<', $startDate);

    $rmOpeningIssuesQuery = RawMaterialIssue::with(['material', 'workpoint'])
        ->whereDate('issue_date', '<', $startDate);

    if (!empty($workPointId)) {
        $rmOpeningPurchasesQuery->where('work_point_id', $workPointId);
        $rmOpeningIssuesQuery->where('work_point_id', $workPointId);
    }

    $rmOpeningPurchases = $rmOpeningPurchasesQuery->get();
    $rmOpeningIssues = $rmOpeningIssuesQuery->get();

    $rmMovement = [];

    foreach ($rmOpeningPurchases as $row) {
        $key = $row->work_point_id . '_' . $row->raw_material_id;

        if (!isset($rmMovement[$key])) {
            $rmMovement[$key] = [
                'work_point' => optional($row->workpoint)->work_code
                    ? optional($row->workpoint)->work_code . ' - ' . optional($row->workpoint)->work_name
                    : optional($row->workpoint)->work_name ?? '-',
                'material_name' => optional($row->material)->material_name ?? '-',
                'opening' => 0,
                'purchased' => 0,
                'issued' => 0,
                'closing' => 0,
            ];
        }

        $rmMovement[$key]['opening'] += (float) ($row->qty ?? 0);
    }

    foreach ($rmOpeningIssues as $row) {
        $key = $row->work_point_id . '_' . $row->raw_material_id;

        if (!isset($rmMovement[$key])) {
            $rmMovement[$key] = [
                'work_point' => optional($row->workpoint)->work_code
                    ? optional($row->workpoint)->work_code . ' - ' . optional($row->workpoint)->work_name
                    : optional($row->workpoint)->work_name ?? '-',
                'material_name' => optional($row->material)->material_name ?? '-',
                'opening' => 0,
                'purchased' => 0,
                'issued' => 0,
                'closing' => 0,
            ];
        }

        $rmMovement[$key]['opening'] -= (float) ($row->issued_qty ?? 0);
    }

    foreach ($rmPurchases as $row) {
        $key = $row->work_point_id . '_' . $row->raw_material_id;

        if (!isset($rmMovement[$key])) {
            $rmMovement[$key] = [
                'work_point' => optional($row->workpoint)->work_code
                    ? optional($row->workpoint)->work_code . ' - ' . optional($row->workpoint)->work_name
                    : optional($row->workpoint)->work_name ?? '-',
                'material_name' => optional($row->material)->material_name ?? '-',
                'opening' => 0,
                'purchased' => 0,
                'issued' => 0,
                'closing' => 0,
            ];
        }

        $rmMovement[$key]['purchased'] += (float) ($row->qty ?? 0);
    }

    foreach ($rmIssues as $row) {
        $key = $row->work_point_id . '_' . $row->raw_material_id;

        if (!isset($rmMovement[$key])) {
            $rmMovement[$key] = [
                'work_point' => optional($row->workpoint)->work_code
                    ? optional($row->workpoint)->work_code . ' - ' . optional($row->workpoint)->work_name
                    : optional($row->workpoint)->work_name ?? '-',
                'material_name' => optional($row->material)->material_name ?? '-',
                'opening' => 0,
                'purchased' => 0,
                'issued' => 0,
                'closing' => 0,
            ];
        }

        $rmMovement[$key]['issued'] += (float) ($row->issued_qty ?? 0);
    }

    foreach ($rmMovement as $key => $row) {
        $rmMovement[$key]['closing'] = $row['opening'] + $row['purchased'] - $row['issued'];
    }

    $rmMovement = array_values($rmMovement);

    return view('admin.store.stock.dashboard', compact(
        'gsReceived',
        'gsIssued',
        'rmPurchases',
        'rmIssues',
        'gsMovement',
        'rmMovement',
        'workPoints',
        'startDate',
        'endDate',
        'workPointId'
    ));
}
// ================= STOCK AUDITS =================

public function stockAuditIndex()
{
    $rows = StockAudit::with(['items', 'workpoint.company', 'creator', 'approver', 'closer'])
        ->orderByDesc('id')
        ->get();

    $workPoints = WorkPoint::with('company')
        ->where('status', '!=', 'Deleted')
        ->orderBy('work_code')
        ->orderBy('work_name')
        ->get();

    $generalItems = GeneralSupplyItem::where('status', '!=', 'Deleted')
        ->orderBy('item_name')
        ->get();

    $rawMaterials = RawMaterial::where('status', '!=', 'Deleted')
        ->orderBy('material_name')
        ->get();

    $products = Product::where('status', '!=', 'Deleted')
        ->orderBy('product_name')
        ->get();

    return view('admin.store.stock_audits', compact(
        'rows',
        'workPoints',
        'generalItems',
        'rawMaterials',
        'products'
    ));
}

public function stockAuditStore(Request $request)
{
    try {
        $request->validate([
            'work_point_id' => 'required|exists:work_points,id',
            'audit_date' => 'required|date',
            'audit_type' => 'required|in:GeneralSupply,RawMaterial,Product',
            'remarks' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required',
            'items.*.system_qty' => 'required|numeric|min:0',
            'items.*.physical_qty' => 'required|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
        ]);

        $user = auth()->user();
        $workPoint = WorkPoint::findOrFail($request->work_point_id);

        DB::beginTransaction();

        $audit = StockAudit::create([
            'company_id' => $workPoint->company_id,
            'comp_unit_id' => $workPoint->comp_unit_id,
            'work_point_id' => $workPoint->id,
            'audit_date' => $request->audit_date,
            'audit_type' => $request->audit_type,
            'remarks' => $request->remarks,
            'created_by' => $user->id,
            'status' => 'Open',
        ]);

        foreach ($request->items as $item) {
            $itemId = $item['item_id'];

            if ($request->audit_type === 'GeneralSupply') {
                $exists = GeneralSupplyItem::where('id', $itemId)
                    ->where('status', '!=', 'Deleted')
                    ->exists();
            } elseif ($request->audit_type === 'RawMaterial') {
                $exists = RawMaterial::where('id', $itemId)
                    ->where('status', '!=', 'Deleted')
                    ->exists();
            } else {
                $exists = Product::where('id', $itemId)
                    ->where('status', '!=', 'Deleted')
                    ->exists();
            }

            if (!$exists) {
                DB::rollBack();
                Alert::error('Error', 'Invalid item selected');
                return back()->withInput();
            }

            $systemQty = (float) ($item['system_qty'] ?? 0);
            $physicalQty = (float) ($item['physical_qty'] ?? 0);
            $variance = $physicalQty - $systemQty;

            StockAuditItem::create([
                'stock_audit_id' => $audit->id,
                'item_type' => $request->audit_type,
                'item_id' => $itemId,
                'system_qty' => $systemQty,
                'physical_qty' => $physicalQty,
                'counted_qty' => $physicalQty,
                'variance_qty' => $variance,
                'remarks' => $item['remarks'] ?? null,
            ]);
        }

        DB::commit();

        Alert::success('Success', 'Stock audit created successfully');
        return redirect()->route('sales.stock.audit.index');

    } catch (\Throwable $e) {
        DB::rollBack();

        Log::error('Stock audit store failed', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        Alert::error('Error', 'Failed to create stock audit: ' . $e->getMessage());
        return back()->withInput();
    }
}

public function stockAuditUpdate(Request $request, $id)
{
    try {
        $realId = decrypt($id);
        $row = StockAudit::with('items')->findOrFail($realId);

        if ($row->status === 'Closed') {
            Alert::error('Error', 'Closed audit cannot be edited');
            return back();
        }

        $request->validate([
            'work_point_id' => 'required|exists:work_points,id',
            'audit_date' => 'required|date',
            'audit_type' => 'required|in:GeneralSupply,RawMaterial,Product',
            'remarks' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required',
            'items.*.system_qty' => 'required|numeric|min:0',
            'items.*.physical_qty' => 'required|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
        ]);

        $workPoint = WorkPoint::findOrFail($request->work_point_id);

        DB::beginTransaction();

        $row->update([
            'company_id' => $workPoint->company_id,
            'comp_unit_id' => $workPoint->comp_unit_id,
            'work_point_id' => $workPoint->id,
            'audit_date' => $request->audit_date,
            'audit_type' => $request->audit_type,
            'remarks' => $request->remarks,
        ]);

        $row->items()->delete();

        foreach ($request->items as $item) {
            $itemId = $item['item_id'];

            if ($request->audit_type === 'GeneralSupply') {
                $exists = GeneralSupplyItem::where('id', $itemId)
                    ->where('status', '!=', 'Deleted')
                    ->exists();
            } elseif ($request->audit_type === 'RawMaterial') {
                $exists = RawMaterial::where('id', $itemId)
                    ->where('status', '!=', 'Deleted')
                    ->exists();
            } else {
                $exists = Product::where('id', $itemId)
                    ->where('status', '!=', 'Deleted')
                    ->exists();
            }

            if (!$exists) {
                DB::rollBack();
                Alert::error('Error', 'Invalid item selected');
                return back()->withInput();
            }

            $systemQty = (float) ($item['system_qty'] ?? 0);
            $physicalQty = (float) ($item['physical_qty'] ?? 0);
            $variance = $physicalQty - $systemQty;

            StockAuditItem::create([
                'stock_audit_id' => $row->id,
                'item_type' => $request->audit_type,
                'item_id' => $itemId,
                'system_qty' => $systemQty,
                'physical_qty' => $physicalQty,
                'counted_qty' => $physicalQty,
                'variance_qty' => $variance,
                'remarks' => $item['remarks'] ?? null,
            ]);
        }

        DB::commit();

        Alert::success('Success', 'Stock audit updated successfully');
        return redirect()->route('sales.stock.audit.index');

    } catch (\Throwable $e) {
        DB::rollBack();

        Log::error('Stock audit update failed', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        Alert::error('Error', 'Failed to update stock audit: ' . $e->getMessage());
        return back()->withInput();
    }
}

public function stockAuditApprove($id)
{
    try {
        $realId = decrypt($id);
        $row = StockAudit::findOrFail($realId);

        if ($row->status !== 'Open') {
            Alert::error('Error', 'Only open audits can be approved');
            return back();
        }

        $row->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Alert::success('Success', 'Stock audit approved successfully');
        return redirect()->route('sales.stock.audit.index');

    } catch (\Throwable $e) {
        Log::error('Stock audit approve failed', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        Alert::error('Error', 'Failed to approve stock audit');
        return back();
    }
}

public function stockAuditClose($id)
{
    try {
        $realId = decrypt($id);
        $row = StockAudit::findOrFail($realId);

        if ($row->status !== 'Approved') {
            Alert::error('Error', 'Only approved audits can be closed');
            return back();
        }

        $row->update([
            'status' => 'Closed',
            'closed_by' => auth()->id(),
            'closed_at' => now(),
        ]);

        Alert::success('Success', 'Stock audit closed successfully');
        return redirect()->route('sales.stock.audit.index');

    } catch (\Throwable $e) {
        Log::error('Stock audit close failed', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        Alert::error('Error', 'Failed to close stock audit');
        return back();
    }
}

public function stockAuditDestroy($id)
{
    try {
        $realId = decrypt($id);

        DB::beginTransaction();

        $row = StockAudit::findOrFail($realId);

        if ($row->status === 'Closed') {
            DB::rollBack();
            Alert::error('Error', 'Closed audit cannot be deleted');
            return back();
        }

        $row->items()->delete();
        $row->delete();

        DB::commit();

        Alert::success('Success', 'Stock audit removed successfully');
        return redirect()->route('sales.stock.audit.index');

    } catch (\Throwable $e) {
        DB::rollBack();

        Log::error('Stock audit delete failed', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        Alert::error('Error', 'Failed to remove stock audit');
        return back();
    }
}
// ================= LOSS PREVENTION =================

public function lossPreventionIndex()
{
    $expiredItems = collect();
    $damagedItems = collect();

    $auditVariance = StockAuditItem::with('audit.workpoint.company')
        ->orderByDesc('id')
        ->get();

    $generalItems = GeneralSupplyItem::select('id', 'item_name', 'item_code')
        ->get()
        ->keyBy('id');

    $rawMaterials = RawMaterial::select('id', 'material_name', 'material_code', 'unit_name')
        ->get()
        ->keyBy('id');

    $products = Product::select('id', 'product_name', 'product_size')
        ->get()
        ->keyBy('id');

    $auditVariance = $auditVariance->map(function ($row) use ($generalItems, $rawMaterials, $products) {
        $itemName = '-';
        $itemCode = '-';
        $unitName = '-';

        if ($row->item_type === 'GeneralSupply') {
            $item = $generalItems->get($row->item_id);

            $itemName = optional($item)->item_name ?? '-';
            $itemCode = optional($item)->item_code ?? '-';
            $unitName = '-';
        } elseif ($row->item_type === 'RawMaterial') {
            $item = $rawMaterials->get($row->item_id);

            $itemName = optional($item)->material_name ?? '-';
            $itemCode = optional($item)->material_code ?? '-';
            $unitName = optional($item)->unit_name ?? '-';
        } elseif ($row->item_type === 'Product') {
            $item = $products->get($row->item_id);

            $itemName = optional($item)->product_name ?? '-';
            $itemCode = '-';
            $unitName = optional($item)->product_size ?? '-';
        }

        $row->item_name = $itemName;
        $row->item_code = $itemCode;
        $row->unit_name = $unitName;

        return $row;
    });

    return view('admin.store.loss_prevention', compact(
        'expiredItems',
        'damagedItems',
        'auditVariance'
    ));
}
}