<?php

namespace App\Http\Controllers;

use App\Models\CompanySite;
use App\Models\Company_unit;
use App\Models\Department;
use App\Models\Section;
use App\Models\AccntTransaction;
use App\Models\User;
use App\Models\WorkPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Check if current user has super access.
     */
    protected function isSuperRole(): bool
    {
        return in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer'], true);
    }

    /**
     * Dashboard
     */
    public function dashboard()
    {
        return view('admin.home.dashboard');
    }
    public function businessAdmin()
    {
        $user = Auth::user();

        $canViewAdministration = $user->can('Administration-Modules');
        $canViewAccounting     = $user->can('Accounting-Modules');

        $companies = collect();
        $businessUnits = collect();
        $workPoints = collect();
        $users = collect();
        $departments = collect();
        $sections = collect();

        $totalCompanies = 0;
        $totalBusinessUnits = 0;
        $totalWorkPoints = 0;
        $totalUsers = 0;
        $totalDepartments = 0;
        $totalSections = 0;

        $companyNames = array();
        $businessUnitNames = array();
        $workPointNames = array();
        $userNames = array();
        $departmentNames = array();
        $sectionNames = array();

        $adminChartLabels = array();
        $adminChartData = array();

        $totalPendingRequests = 0;
        $totalVerifiedRequests = 0;
        $totalApprovedRequests = 0;

        $accountingCompanyLabels = array();
        $approvedByCompany = array();
        $pendingByCompany = array();
        $verifiedByCompany = array();

        if ($canViewAdministration) {
            $companies = CompanySite::where('id', '!=', 1)->where('Status', 'Active')->get();
            $businessUnits = Company_unit::where('id', '!=', 1)->where('Status', 'Active')->get(); // change model if needed
            $workPoints = WorkPoint::where('id', '!=', 1)->where('Status', 'Active')->get();
            $users = User::where('id', '!=', 1)->where('Status', 'Active')->get();
            $departments = Department::where('Status', 'Active')->get();
            $sections = Section::where('Status', 'Active')->get();

            $totalCompanies = $companies->count();
            $totalBusinessUnits = $businessUnits->count();
            $totalWorkPoints = $workPoints->count();
            $totalUsers = $users->count();
            $totalDepartments = $departments->count();
            $totalSections = $sections->count();

            $companyNames = $companies->pluck('company_name')->filter()->values()->toArray();

            // change 'unit_name' if your business unit column is different
            $businessUnitNames = $businessUnits->pluck('unit_name')->filter()->values()->toArray();

            $workPointNames = $workPoints->pluck('work_name')->filter()->values()->toArray();
            $departmentNames = $departments->pluck('depName')->filter()->values()->toArray();
            $sectionNames = $sections->pluck('secName')->filter()->values()->toArray();

            $userNames = $users->pluck('name')->filter()->values()->toArray();
            if (empty($userNames)) {
                $userNames = $users->pluck('username')->filter()->values()->toArray();
            }

            foreach ($companies as $company) {
                $adminChartLabels[] = $company->company_name;
                $adminChartData[] = WorkPoint::where('company_id', $company->id)->where('Status', 'Active')->count();
            }
        }

        if ($canViewAccounting) {
            $baseAccountingQuery = AccntTransaction::whereNull('deleted_at')
                ->where('Status', 'Active');

            $totalPendingRequests = (clone $baseAccountingQuery)
                ->where('verified', 'pending')
                ->where('approved', 'pending')
                ->distinct('transaction_group')
                ->count('transaction_group');

            $totalVerifiedRequests = (clone $baseAccountingQuery)
                ->where('verified', 'verified')
                ->where('approved', 'pending')
                ->distinct('transaction_group')
                ->count('transaction_group');

            $totalApprovedRequests = (clone $baseAccountingQuery)
                ->where('approved', 'approved')
                ->distinct('transaction_group')
                ->count('transaction_group');

            $accountingCompanies = CompanySite::where('id', '!=', 1)
                ->orderBy('company_name')
                ->get();

            foreach ($accountingCompanies as $company) {
                $accountingCompanyLabels[] = $company->company_name;

                $pendingByCompany[] = AccntTransaction::whereNull('deleted_at')
                    ->where('Status', 'Active')
                    ->where('company_id', $company->id)
                    ->where('verified', 'pending')
                    ->where('approved', 'pending')
                    ->distinct('transaction_group')
                    ->count('transaction_group');

                $verifiedByCompany[] = AccntTransaction::whereNull('deleted_at')
                    ->where('Status', 'Active')
                    ->where('company_id', $company->id)
                    ->where('verified', 'verified')
                    ->where('approved', 'pending')
                    ->distinct('transaction_group')
                    ->count('transaction_group');

                $approvedByCompany[] = AccntTransaction::whereNull('deleted_at')
                    ->where('Status', 'Active')
                    ->where('company_id', $company->id)
                    ->where('approved', 'approved')
                    ->distinct('transaction_group')
                    ->count('transaction_group');
            }
        }

        return view('admin.home.business-admin')->with(array(
            'canViewAdministration' => $canViewAdministration,
            'canViewAccounting' => $canViewAccounting,

            'totalCompanies' => $totalCompanies,
            'totalBusinessUnits' => $totalBusinessUnits,
            'totalWorkPoints' => $totalWorkPoints,
            'totalUsers' => $totalUsers,
            'totalDepartments' => $totalDepartments,
            'totalSections' => $totalSections,

            'companyNames' => $companyNames,
            'businessUnitNames' => $businessUnitNames,
            'workPointNames' => $workPointNames,
            'userNames' => $userNames,
            'departmentNames' => $departmentNames,
            'sectionNames' => $sectionNames,

            'adminChartLabels' => $adminChartLabels,
            'adminChartData' => $adminChartData,

            'totalPendingRequests' => $totalPendingRequests,
            'totalVerifiedRequests' => $totalVerifiedRequests,
            'totalApprovedRequests' => $totalApprovedRequests,

            'accountingCompanyLabels' => $accountingCompanyLabels,
            'approvedByCompany' => $approvedByCompany,
            'pendingByCompany' => $pendingByCompany,
            'verifiedByCompany' => $verifiedByCompany,
        ));
    }

    /**
     * Company list
     */
    public function companyIndex()
    {
        $user = auth()->user();
        // if ((Auth()->user()->role == 'Admin-Developer')) {
            $companies = CompanySite::where('id','!=','1')->where('status','!=','Deleted')
                ->orderBy('company_name')->get();
        // }else{
        //     $companies = CompanySite::where('id','!=','1')->where(function ($q) use ($user) {
        //             $q->where('user_id', $user->id)->orWhere('id', $user->company_id);
        //         })->where('status','!=','Deleted')->orderBy('company_name')->get();
        // }
        return view('admin.company.index', compact('companies'));
    }

    /**
     * Store company
     */
    public function companyStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_code' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'Type'         => 'required|string|max:255',
            'district'     => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:500',
            'TIN'          => 'nullable|string|max:500',
            'phone_No'     => 'required|string|max:50',
            'status'       => 'nullable|in:Active,Inactive',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->only([
                'company_code',
                'company_name',
                'Type',
                'district',
                'city',
                'TIN',
                'phone_No',
            ]);

            $data['status'] = $request->status ?? 'Active';
            $data['user_id'] = Auth::id();

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

                $publicRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
                $destFolder = $publicRoot . '/assets';

                if (!File::exists($destFolder)) {
                    File::makeDirectory($destFolder, 0755, true);
                }

                $file->move($destFolder, $filename);
                $data['logo'] = 'assets/' . $filename;
            }

            CompanySite::create($data);

            DB::commit();

            Alert::success('Congrats ' . Auth::user()->name, 'Company site registered successfully.');
            return redirect()->route('company.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('companyStore error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);

            Alert::error('Sorry! ' . (Auth::user()->name ?? 'User'), 'Technical error occurred. Please contact IT.');
            return back()->withInput();
        }
    }

    /**
     * Update company
     */
    public function companyUpdate(Request $request, $id)
    {
        $decryptedId = decrypt($id);
        $company = CompanySite::findOrFail($decryptedId);

        $validator = Validator::make($request->all(), [
            'company_code' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'Type'         => 'required|string|max:255',
            'district'     => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:500',
            'TIN'          => 'nullable|string|max:500',
            'phone_No'     => 'required|string|max:50',
            'status'       => 'required|in:Active,Inactive',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->only([
                'company_code',
                'company_name',
                'Type',
                'district',
                'city',
                'TIN',
                'phone_No',
                'status',
            ]);

            $data['user_id'] = Auth::id();

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

                $publicRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
                $destFolder = $publicRoot . '/assets';

                if (!File::exists($destFolder)) {
                    File::makeDirectory($destFolder, 0755, true);
                }

                if (!empty($company->logo)) {
                    $oldPath = $publicRoot . '/' . ltrim($company->logo, '/');
                    if (File::exists($oldPath) && is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $file->move($destFolder, $filename);
                $data['logo'] = 'assets/' . $filename;
            }

            $company->update($data);

            DB::commit();

            Alert::success('Congrats ' . Auth::user()->name, 'Company site updated successfully.');
            return redirect()->route('company.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('companyUpdate error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);

            Alert::error('Sorry! ' . (Auth::user()->name ?? 'User'), 'Technical error occurred. Please contact IT.');
            return back()->withInput();
        }
    }

    /**
     * Delete company
     */
    public function companyDelete(Request $request, $id)
    {
        try {
            $decryptedId = decrypt($id);
            CompanySite::where('id', $decryptedId)->update(['status' => 'Deleted']);

            Alert::success('Congrats ' . Auth()->user()->name, 'Company site removed successfully.');
            return redirect()->route('company.index');
        } catch (\Throwable $th) {
            Alert::error('Sorry! ' . Auth()->user()->name, 'Technical error occurred. Please contact IT.');
            return back();
        }
    }

    /**
     * Company Unit list
     */
    public function companyUnitIndex()
    {
        $user = auth()->user();
        // if ($user->role === 'Admin-Developer') {
            $units = Company_unit::with('company')->where('id', '!=', 1)
                ->where('status', '!=', 'Deleted')->orderBy('unit_name')->get();
            $companies = CompanySite::where('id', '!=', 1)->where('status', '!=', 'Deleted')
                ->orderBy('company_name')->get();
        // } else {
        //     $units = Company_unit::with('company')->where('id', '!=', 1)->where('status', '!=', 'Deleted')
        //         ->where(function($q) use ($user) {
        //             $q->where('user_id', $user->id)->orWhere('company_id', $user->company_id);
        //         })->orderBy('unit_name')->get();
        //     $companies = CompanySite::where('id', '!=', 1)->where('status', '!=', 'Deleted')
        //         ->where(function($q) use ($user) {
        //             $q->where('user_id', $user->id)->orWhere('id', $user->company_id);
        //         })->orderBy('company_name')->get();
        // }
        return view('admin.company.units', compact('units', 'companies'));
    }

    /**
     * Store company unit
     */
    public function companyUnitStore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'nullable|exists:company_sites,id',
                'unit_code'  => 'required|string|max:255',
                'unit_name'  => 'required|string|max:255',
                'location'   => 'required|string|max:255',
                'district'   => 'nullable|string|max:255',
                'city'       => 'nullable|string|max:255',
                'phone_No'   => 'nullable|string|max:50',
                'status'     => 'nullable|in:Active,Inactive',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            Company_unit::create([
                'company_id' => $request->company_id,
                'unit_code'  => $request->unit_code,
                'unit_name'  => $request->unit_name,
                'location'   => $request->location,
                'district'   => $request->district,
                'city'       => $request->city,
                'phone_No'   => $request->phone_No,
                'status'     => $request->status ?? 'Active',
                'user_id'    => auth()->user()->id,
            ]);

            Alert::success('Congrats ' . auth()->user()->name, 'Company unit registered successfully.');
            return redirect()->route('companyunit.index');
        } catch (\Throwable $th) {
            Log::error('companyUnitStore error: ' . $th->getMessage());
            Alert::error('Sorry! ' . auth()->user()->name, 'Technical error occurred. Please contact IT.');
            return back()->withInput();
        }
    }

    /**
     * Update company unit
     */
    public function companyUnitUpdate(Request $request, $id)
    {
        try {
            $decryptedId = decrypt($id);
            $unit = Company_unit::findOrFail($decryptedId);

            $validator = Validator::make($request->all(), [
                'company_id' => 'nullable|exists:company_sites,id',
                'unit_code'  => 'required|string|max:255',
                'unit_name'  => 'required|string|max:255',
                'location'   => 'required|string|max:255',
                'district'   => 'nullable|string|max:255',
                'city'       => 'nullable|string|max:255',
                'phone_No'   => 'nullable|string|max:50',
                'status'     => 'required|in:Active,Inactive',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $unit->update([
                'company_id' => $request->company_id,
                'unit_code'  => $request->unit_code,
                'unit_name'  => $request->unit_name,
                'location'   => $request->location,
                'district'   => $request->district,
                'city'       => $request->city,
                'phone_No'   => $request->phone_No,
                'status'     => $request->status,
                'user_id'    => auth()->user()->id,
            ]);

            Alert::success('Congrats ' . auth()->user()->name, 'Company unit updated successfully.');
            return redirect()->route('companyunit.index');
        } catch (\Throwable $th) {
            Log::error('companyUnitUpdate error: ' . $th->getMessage());
            Alert::error('Sorry! ' . auth()->user()->name, 'Technical error occurred. Please contact IT.');
            return back()->withInput();
        }
    }

    /**
     * Delete company unit
     */
    public function companyUnitDelete(Request $request, $id)
    {
        try {
            $decryptedId = decrypt($id);
            Company_unit::where('id', $decryptedId)->update(['status' => 'Deleted']);

            Alert::success('Congrats ' . auth()->user()->name, 'Company unit removed successfully.');
            return redirect()->route('companyunit.index');
        } catch (\Throwable $th) {
            Log::error('companyUnitDelete error: ' . $th->getMessage());
            Alert::error('Sorry! ' . auth()->user()->name, 'Technical error occurred. Please contact IT.');
            return back();
        }
    }

    /**
     * WorkPoint list
     */
    public function workPointIndex()
    {
        $user = auth()->user();
        // if ((Auth()->user()->role == 'Admin-Developer')) {
            $workPoints = WorkPoint::with('company')->where('id','!=','1')
                ->where('status', '!=', 'Deleted')->orderBy('created_at','desc')->get();
            $companies = CompanySite::where('id','!=','1')->where('status','!=','Deleted')
                ->orderBy('company_name')->get();
            $unities = Company_unit::with('company')->where('id','!=','1')->where('status','!=','Deleted')
                ->orderBy('unit_name')->get();
        // }else{
        //     $workPoints = WorkPoint::with('company')->where('id','!=','1')->where(function ($q) use ($user) {
        //             $q->where('user_id', $user->id)->orWhere('id', $user->company_id);
        //         })->where('status', '!=', 'Deleted')->orderBy('created_at','desc')->get();
        //     $companies = CompanySite::where('id','!=','1')->where(function ($q) use ($user) {
        //             $q->where('user_id', $user->id)->orWhere('id', $user->company_id);
        //         })->where('status','!=','Deleted')->orderBy('company_name')->get();
        //     $unities = Company_unit::with('company')->where('id','!=','1')->where(function ($q) use ($user) {
        //             $q->where('user_id', $user->id)->orWhere('id', $user->company_id);
        //         })->where('status','!=','Deleted')->orderBy('unit_name')->get();
        // }
        return view('admin.company.wrkindex', compact('workPoints','companies','unities'));
    }

    /**
     * Store WorkPoint
     */
    public function workPointStore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id'   => 'nullable|exists:company_sites,id',
                'comp_unit_id' => 'nullable|exists:company_units,id',
                'work_name'    => 'required|string|max:255',
                'work_code'    => 'required|string|max:255',
                'city'         => 'required|string|max:255',
                'district'     => 'required|string|max:255',
                'location'     => 'required|string|max:255',
                'phone_No'     => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            WorkPoint::create([
                'company_id'   => $request->company_id,
                'comp_unit_id' => $request->comp_unit_id,
                'work_code'    => $request->work_code,
                'work_name'    => $request->work_name,
                'city'         => $request->city,
                'district'     => $request->district,
                'location'     => $request->location,
                'phone_No'     => $request->phone_No,
                'status'       => $request->status ?? 'Active',
                'user_id'      => auth()->user()->id,
            ]);

            Alert::success('Congrats ' . Auth()->user()->name, 'Work point registered successfully.');
            return redirect()->route('workpoint.index');
        } catch (\Throwable $th) {
            Log::error('workPointStore error: ' . $th->getMessage());
            Alert::error('Sorry! ' . Auth()->user()->name, 'Technical error occurred. Please contact IT.');
            return back()->withInput();
        }
    }

    /**
     * Update WorkPoint
     */
    public function workPointUpdate(Request $request, $id)
    {
        try {
            $decryptedId = decrypt($id);
            $wp = WorkPoint::findOrFail($decryptedId);

            $validator = Validator::make($request->all(), [
                'company_id'   => 'nullable|exists:company_sites,id',
                'comp_unit_id' => 'nullable|exists:company_units,id',
                'work_name'    => 'required|string|max:255',
                'work_code'    => 'required|string|max:255',
                'city'         => 'required|string|max:255',
                'district'     => 'required|string|max:255',
                'location'     => 'required|string|max:255',
                'phone_No'     => 'nullable|string|max:50',
                'status'       => 'required|in:Active,Inactive',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $wp->update([
                'company_id'   => $request->company_id,
                'comp_unit_id' => $request->comp_unit_id,
                'work_code'    => $request->work_code,
                'work_name'    => $request->work_name,
                'city'         => $request->city,
                'district'     => $request->district,
                'location'     => $request->location,
                'phone_No'     => $request->phone_No,
                'status'       => $request->status,
                'user_id'      => auth()->user()->id,
            ]);

            Alert::success('Congrats ' . Auth()->user()->name, 'Work point updated successfully.');
            return redirect()->route('workpoint.index');
        } catch (\Throwable $th) {
            Log::error('workPointUpdate error: ' . $th->getMessage());
            Alert::error('Sorry! ' . Auth()->user()->name, 'Technical error occurred. Please contact IT.');
            return back();
        }
    }

    /**
     * Delete WorkPoint
     */
    public function workPointDelete(Request $request, $id)
    {
        try {
            $decryptedId = decrypt($id);
            WorkPoint::where('id', $decryptedId)->update(['status' => 'Deleted']);

            Alert::success('Congrats ' . Auth()->user()->name, 'Work point removed successfully.');
            return redirect()->route('workpoint.index');
        } catch (\Throwable $th) {
            Log::error('workPointDelete error: ' . $th->getMessage());
            Alert::error('Sorry! ' . Auth()->user()->name, 'Technical error occurred. Please contact IT.');
            return back();
        }
    }

    /**
     * Department info page
     */
    public function deptinfo()
    {
        $user = auth()->user();
        // if ($this->isSuperRole()) {
            $workPoints = WorkPoint::where('id', '!=','1')
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
            // departments for selecting in section create/edit
            $departments = Department::where('Status', '!=', 'Deleted')->orderBy('depName')->get();
            $unities = Company_unit::with('company')->where('id','!=','1')->where('status','!=','Deleted')
                ->orderBy('unit_name')->get();
        // } else {
        //     $workPoints = collect();
        //     $unities = Company_unit::with('company')->where('id','!=','1')->where(function ($q) use ($user) {
        //             $q->where('user_id', $user->id)->orWhere('id', $user->company_id);
        //         })->where('status','!=','Deleted')->orderBy('unit_name')->get();
        //     // only departments for the user's work point
        //     $departments = Department::where('company_id', $user->company_id)
        //         ->where('work_point_id', $user->work_point_id)
        //         ->where('Status', '!=', 'Deleted')->orderBy('depName')->get();
        // }
        $companies = CompanySite::where('id', '!=','1')->where('Status', '!=', 'Deleted')->get();
        return view('admin.company.deptinfo', compact('workPoints','departments','companies','unities'));
    }

    /**
     * Store department
     */
    public function regdeptinfo(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'depCode' => [
                'required',
                'string',
                'max:50',
                Rule::unique('departments', 'depCode')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ],
            'Status' => ['nullable', Rule::in(['Active', 'Inactive', 'Deleted'])],
            'comp_unit_id' => [
                'nullable',
                'integer',
                Rule::exists('company_units', 'id')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = [
                'required',
                'integer',
                Rule::exists('work_points', 'id')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $workPointId = $isSuper ? $request->work_point_id : $user->work_point_id;
        $compUnitId = $request->comp_unit_id ?? null;
        $companies = Company_unit::where('id', '=',$compUnitId)->first();
        $companyId = $companies->company_id;
        Department::create([
            'company_id'   => $companyId,
            'comp_unit_id'  => $compUnitId,
            'work_point_id' => $workPointId,
            'depName'      => $request->name,
            'depCode'      => $request->depCode,
            'Status'       => $request->Status ?? 'Active',
        ]);

        Alert::success('Success', 'Department created.');
        return redirect()->route('departments.index');
    }

    /**
     * Update department
     */
    public function updatedeptinfo(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error', 'Invalid identifier.');
            return back();
        }

        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $dept = Department::findOrFail($decrypted);

        // if ($dept->company_id !== $user->company_id) {
        //     Alert::error('Unauthorized', 'You cannot edit departments of other companies.');
        //     return back();
        // }
        
        $companies = Company_unit::where('id', '=',$request->comp_unit_id)->first();
        $companyId = $companies->company_id;
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'depCode' => [
                'required',
                'string',
                'max:50',
                Rule::unique('departments', 'depCode')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->ignore($dept->id),
            ],
            'Status' => ['required', Rule::in(['Active', 'Inactive', 'Deleted'])],
            'comp_unit_id' => [
                'nullable',
                'integer',
                Rule::exists('company_units', 'id')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = [
                'required',
                'integer',
                Rule::exists('work_points', 'id')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $workPointId = $isSuper ? $request->work_point_id : $user->work_point_id;
        $compUnitId = $request->comp_unit_id ?? null;

        $dept->update([
            'company_id'   => $companyId,
            'comp_unit_id'  => $compUnitId,
            'work_point_id' => $workPointId,
            'depName'      => $request->name,
            'depCode'      => $request->depCode,
            'Status'       => $request->Status,
        ]);

        Alert::success('Success', 'Department updated.');
        return redirect()->route('departments.index');
    }

    /**
     * Section info page
     */
    public function sectinfo()
    {
        $user = auth()->user();
        // if ($this->isSuperRole()) {
            // all sections for the user's company
            $sections = Section::with(['company','workpoint','dept'])
            // ->where('company_id', $user->company_id)
                ->where('Status', '!=', 'Deleted')->orderBy('secName')->get();
            $workPoints = WorkPoint::where('id','!=','1')
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
            // departments for selecting in section create/edit
            $departments = Department::where('Status', '!=', 'Deleted')->orderBy('depName')->get();
            $unities = Company_unit::with('company')->where('id','!=','1')->where('status','!=','Deleted')
                ->orderBy('unit_name')->get();
        // } else {
        //     $workPoints = collect();
        //     $unities = Company_unit::with('company')->where('id','!=','1')->where(function ($q) use ($user) {
        //             $q->where('user_id', $user->id)->orWhere('id', $user->company_id);
        //         })->where('status','!=','Deleted')->orderBy('unit_name')->get();
        //     $sections = Section::with(['company','workpoint','dept'])
        //         ->where('company_id', $user->company_id)->where('work_point_id', $user->work_point_id)
        //         ->where('Status', '!=', 'Deleted')->orderBy('secName')->get();
        //     // only departments for the user's work point
        //     $departments = Department::where('company_id', $user->company_id)
        //         ->where('work_point_id', $user->work_point_id)
        //         ->where('Status', '!=', 'Deleted')->orderBy('depName')->get();
        // }
        $companies = CompanySite::where('id', auth()->user()->company_id)->get();

        return view('admin.company.secinfo', compact('sections', 'workPoints', 'departments', 'companies', 'unities'));
    }

    /**
     * Store section
     */
    public function regsectinfo(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        // $companyId = $user->company_id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'secCode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('sections', 'secCode')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ],
            'Status' => ['nullable', Rule::in(['Active', 'Inactive', 'Deleted'])],
            'dept_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ],
            'comp_unit_id' => [
                'nullable',
                'integer',
                Rule::exists('company_units', 'id')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = [
                'required',
                'integer',
                Rule::exists('work_points', 'id')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $workPointId = $isSuper ? $request->work_point_id : $user->work_point_id;
        $compUnitId = $request->comp_unit_id ?? null;
        $companies = Company_unit::where('id', '=',$compUnitId)->first();
        $companyId = $companies->company_id;
        Section::create([
            'company_id'   => $companyId,
            'comp_unit_id'  => $compUnitId,
            'work_point_id' => $workPointId,
            'dept_id'      => $request->dept_id,
            'secName'      => $request->name,
            'secCode'      => $request->secCode,
            'Status'       => $request->Status ?? 'Active',
        ]);

        Alert::success('Success', 'Section created succesfully.');
        return redirect()->route('sections.index');
    }

    /**
     * Update section
     */
    public function updatesectinfo(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error', 'Invalid identifier.');
            return back();
        }

        $user = auth()->user();
        // $isSuper = $this->isSuperRole();

        $sect = Section::findOrFail($decrypted);

        // if ($sect->company_id !== $user->company_id) {
        //     Alert::error('Unauthorized', 'You cannot edit sections of other companies.');
        //     return back();
        // }

        // $companyId = $user->company_id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'secCode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('sections', 'secCode')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->ignore($sect->id),
            ],
            'Status' => ['required', Rule::in(['Active', 'Inactive', 'Deleted'])],
            'dept_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ],
            'comp_unit_id' => [
                'nullable',
                'integer',
                Rule::exists('company_units', 'id')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = [
                'required',
                'integer',
                Rule::exists('work_points', 'id')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $workPointId = $isSuper ? $request->work_point_id : $user->work_point_id;
        $compUnitId = $request->comp_unit_id ?? null;
        $companies = Company_unit::where('id', '=',$compUnitId)->first();
        $companyId = $companies->company_id;

        $sect->update([
            'company_id'   => $companyId,
            'comp_unit_id'  => $compUnitId,
            'work_point_id' => $workPointId,
            'dept_id'      => $request->dept_id,
            'secName'      => $request->name,
            'secCode'      => $request->secCode,
            'Status'       => $request->Status,
        ]);

        Alert::success('Success', 'Section updated succesfully.');
        return redirect()->route('sections.index');
    }
    public function remvsectinfo($id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error', 'Invalid identifier.');
            return back();
        }

        $user = auth()->user();
        $sect = Section::findOrFail($decrypted);

        if ($sect->company_id !== $user->company_id) {
            Alert::error('Unauthorized', 'You cannot remove sections from other companies.');
            return back();
        }

        $sect->update(['Status' => 'Deleted']);

        Alert::success('Success', 'Section removed succesfully.');
        return redirect()->route('sections.index');
    }
    // Reporting
    public function reporting()
    {
        return view('admin.home.reporting');
    }
}