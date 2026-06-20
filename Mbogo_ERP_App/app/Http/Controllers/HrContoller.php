<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\CompanySite;
use App\Models\WorkPoint;
use App\Models\Company_unit;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Payroll;
use App\Models\PayrollLine;
use App\Models\Overtime;
use App\Models\HeslbLoan;
use App\Models\HeslbLoanPayment;
use App\Models\StaffSalaryAdjustment;
use App\Models\StaffNextOfKin;
use App\Models\Absence;
use App\Models\EmployeeLoan;
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
class HrContoller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // parent::__construct();
    }
     protected function isSuperRole()
    {
        return in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer'], true);
    }
     // Human Resources
    public function hr()
    {
        return view('admin.home.hr');
    }
    /**
     * Display list of staff
     */
    public function index()
    {
        $user = Auth::user();
        $roles = Role::where('Status', 'Active')->orderBy('name')->get();
        $users = collect();
        $companies = collect();
        $workPoints = collect();
        if ($user->role === 'Admin-Developer') {
            // All data
            $users = User::with(['company', 'workpoint'])->where('id', '!=', 1)
                ->where('status', '!=', 'Deleted')->orderBy('created_at', 'desc')->get();
            $companies = CompanySite::where('id', '!=', 1)->where('status', '!=', 'Deleted')
                ->orderBy('company_name')->get();
            $workPoints = WorkPoint::where('id', '!=', 1)->where('status', '!=', 'Deleted')
                ->with('company')->orderBy('work_name')->get();
            $unities = Company_unit::with('company')->where('id','!=','1')->where('status','!=','Deleted')
                ->orderBy('unit_name')->get();
        } elseif (($user->role === 'Admin')||($user->role === 'CEO')) {
            $users = User::with(['company', 'workpoint'])->where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('created_at', 'desc')->get();
            $companies = CompanySite::where('id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('company_name')->get();
            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
            $unities = Company_unit::with('company')->where('company_id',$user->company_id)->where('status','!=','Deleted')
                ->orderBy('unit_name')->get();
        } else {
            $usersQuery = User::with(['company', 'workpoint'])->where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted');
            if (!empty($user->work_point_id)) {
                $usersQuery->where('work_point_id', $user->work_point_id);
            }
            $users = $usersQuery->orderBy('created_at', 'desc')->get();
            $companies = CompanySite::where('id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('company_name')->get();
            $workPointsQuery = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted');
            if (!empty($user->work_point_id)) {
                $workPointsQuery->where('id', $user->work_point_id);
            }
            $workPoints = $workPointsQuery->orderBy('work_name')->get();
            $unities = Company_unit::with('company')->where('company_id',$user->company_id)->where('status','!=','Deleted')
                ->orderBy('unit_name')->get();
        }
        return view('admin.hr.staff.index', compact('users', 'roles', 'companies', 'workPoints','unities'));
    }
    /**
     * Store a new staff user
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => ['required','string','max:100','unique:users,username'],
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:users,email',
                'phone_No' => 'nullable|string|max:50',
                'gender' => 'nullable|in:Male,Female,Other',
                'company_id' => 'nullable|exists:company_sites,id',
                'work_point_id' => 'nullable|exists:work_points,id',
                'comp_unit_id' => 'nullable|exists:company_units,id',
                'role' => 'required|string|exists:roles,slug',
                'status' => 'nullable|in:Active,Inactive',
                'gross_salary' => 'nullable|numeric|min:0',
                'accName' => 'nullable|string|max:255',
                'accNo' => 'nullable|string|max:255',
                'nssfNo' => 'nullable|string|max:255',
                'wcfNo' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $defaultPassword = '123456';
            $user = User::create([
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'phone_No' => $request->phone_No,
                'gender' => $request->gender,
                'company_id' => $request->company_id,
                'work_point_id' => $request->work_point_id,
                'comp_unit_id' => $request->comp_unit_id,
                'role' => $request->role,
                'status' => $request->status ?? 'Active',
                'password' => Hash::make($defaultPassword),
                'gross_salary' => $request->gross_salary ?? 0.00,
                'accName' => $request->accName ?? 'N/A',
                'accNo' => $request->accNo ?? 'N/A',
                'nssfNo' => $request->nssfNo ?? 'N/A',
                'wcfNo' => $request->wcfNo ?? 'N/A',
            ]);

            Alert::success('Success', 'Staff registered successfully.');
            return redirect()->route('staff.index');
        } catch (Exception $e) {
            Alert::error('Error', 'Technical error occurred. ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Update staff user
     * $id expected to be encrypted (like your sample)
     */
    public function update(Request $request, $id)
    {
        try {
            $decryptedId = decrypt($id);
            $user = User::findOrFail($decryptedId);

            $validator = Validator::make($request->all(), [
                'username' => ['required','string','max:100', \Illuminate\Validation\Rule::unique('users','username')->ignore($user->id)],
                'name' => 'required|string|max:255',
                'email' => ['nullable','email','max:255', \Illuminate\Validation\Rule::unique('users','email')->ignore($user->id)],
                'phone_No' => 'nullable|string|max:50',
                'gender' => 'nullable|in:Male,Female,Other',
                'company_id' => 'nullable|exists:company_sites,id',
                'work_point_id' => 'nullable|exists:work_points,id',
                'comp_unit_id' => 'nullable|exists:company_units,id',
                'role' => 'required|string|exists:roles,slug',
                'status' => 'nullable|in:Active,Inactive',
                'gross_salary' => 'nullable|numeric|min:0',
                'accName' => 'nullable|string|max:255',
                'accNo' => 'nullable|string|max:255',
                'nssfNo' => 'nullable|string|max:255',
                'wcfNo' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $user->update([
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'phone_No' => $request->phone_No,
                'gender' => $request->gender,
                'company_id' => $request->company_id,
                'work_point_id' => $request->work_point_id,
                'comp_unit_id' => $request->comp_unit_id,
                'role' => $request->role,
                'status' => $request->status ?? $user->status,
                'gross_salary' => $request->gross_salary ?? $user->gross_salary,
                'accName' => $request->accName ?? $user->accName,
                'accNo' => $request->accNo ?? $user->accNo,
                'nssfNo' => $request->nssfNo ?? $user->nssfNo,
                'wcfNo' => $request->wcfNo ?? $user->wcfNo,
            ]);

            Alert::success('Success', 'Staff updated successfully.');
            return redirect()->route('staff.index');
        } catch (Exception $e) {
            Alert::error('Error', 'Technical error occurred. ' . $e->getMessage());
            return back();
        }
    }
    /**
     * Soft remove staff (mark as Deleted)
     */
    public function remove($id)
    {
        try {
            $decryptedId = decrypt($id);
            $user = User::findOrFail($decryptedId);
            $user->update(['status' => 'Deleted']);
            Alert::success('Success', 'Staff removed successfully.');
            return redirect()->route('staff.index');
        } catch (Exception $e) {
            Alert::error('Error', 'Technical error occurred. ' . $e->getMessage());
            return back();
        }
    }
    /**
     * Activate staff
     */
    public function activate($id)
    {
        try {
            $decryptedId = decrypt($id);
            $user = User::findOrFail($decryptedId);
            $user->update(['status' => 'Active']);
            Alert::success('Success', 'Staff activated successfully.');
            return redirect()->route('staff.index');
        } catch (Exception $e) {
            Alert::error('Error', 'Technical error occurred. ' . $e->getMessage());
            return back();
        }
    }
    /**
     * Deactivate staff
     */
    public function deactivate($id)
    {
        try {
            $decryptedId = decrypt($id);
            $user = User::findOrFail($decryptedId);
            $user->update(['status' => 'Inactive']);
            Alert::success('Success', 'Staff deactivated successfully.');
            return redirect()->route('staff.index');
        } catch (Exception $e) {
            Alert::error('Error', 'Technical error occurred. ' . $e->getMessage());
            return back();
        }
    }
    /**
     * Reset password to default (and optionally email)
     */
    public function resetPassword($id)
    {
        try {
            $decryptedId = decrypt($id);
            $user = User::findOrFail($decryptedId);
            $newPassword = '123456';
            $user->update(['password' => Hash::make($newPassword)]);
            Alert::success('Success', 'Password reset to default for user ' . $user->username);
            return redirect()->route('staff.index');
        } catch (Exception $e) {
            Alert::error('Error', 'Technical error occurred. ' . $e->getMessage());
            return back();
        }
    }
    // Staff Next of Kins view
public function staffNextOfKins()
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    if ($isSuper) {
        $nextOfKins = StaffNextOfKin::with(['user','company','workpoint'])
            ->where('company_id', $user->company_id)->where('status', '!=', 'Deleted')
            ->orderBy('name')->get();
        $workPoints = WorkPoint::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
    } else {
        $nextOfKins = StaffNextOfKin::with(['user','company','workpoint'])
            ->where('company_id', $user->company_id)->where('work_point_id', $user->work_point_id)
            ->where('status', '!=', 'Deleted')->orderBy('name')->get();

        $workPoints = collect();
    }
    $companies = CompanySite::where('id', $user->company_id)->get();
    $staffUsers = User::where('company_id', $user->company_id)->where('status','Active')->orderBy('name')->get();
    return view('admin.hr.next_of_kins', compact('nextOfKins','workPoints','companies','staffUsers'));
}

public function storeNextOfKin(Request $request)
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    $rules = [
        'user_id' => ['required','integer', Rule::exists('users','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })],
        'name' => ['required','string','max:255'],
        'relationship' => ['nullable','string','max:255'],
        'phone' => ['nullable','string','max:50'],
        'status' => ['nullable', Rule::in(['Active','Inactive','Deleted'])],
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

    StaffNextOfKin::create([
        'user_id' => $request->user_id,
        'company_id' => $companyId,
        'work_point_id' => $workPointId,
        'name' => $request->name,
        'relationship' => $request->relationship,
        'phone' => $request->phone,
        'address' => $request->address ?? null,
        'status' => $request->status ?? 'Active',
    ]);

    Alert::success('Success','Next of kin created successfully.');
    return redirect()->route('hr.nextofkins.index');
}

public function updateNextOfKin(Request $request, $id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) {
        Alert::error('Error','Invalid identifier'); return back();
    }
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    $nok = StaffNextOfKin::findOrFail($decrypted);
    if ($nok->company_id !== $user->company_id) {
        Alert::error('Unauthorized','You cannot edit records from other companies.'); return back();
    }

    $rules = [
        'name' => ['required','string','max:255'],
        'status' => ['required', Rule::in(['Active','Inactive','Deleted'])],
    ];
    if ($isSuper) {
        $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })];
    }

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    $nok->update([
        'company_id' => $user->company_id,
        'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
        'name' => $request->name,
        'relationship' => $request->relationship,
        'phone' => $request->phone,
        'address' => $request->address,
        'status' => $request->status,
    ]);

    Alert::success('Success','Next of kin updated successfully.');
    return redirect()->route('hr.nextofkins.index');
}

public function removeNextOfKin($id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) {
        Alert::error('Error','Invalid identifier'); return back();
    }
    $user = auth()->user();
    $nok = StaffNextOfKin::findOrFail($decrypted);
    if ($nok->company_id !== $user->company_id) {
        Alert::error('Unauthorized','You cannot remove records from other companies.'); return back();
    }
    $nok->update(['status' => 'Deleted']);
    Alert::success('Success','Next of kin removed successfully.');
    return redirect()->route('hr.nextofkins.index');
}
// View
public function staffEducations()
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    if ($isSuper) {
        $educations = \App\Models\StaffEducation::with(['user','company','workpoint'])
            ->where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('level')->get();

        $workPoints = WorkPoint::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
    } else {
        $educations = \App\Models\StaffEducation::with(['user','company','workpoint'])
            ->where('company_id', $user->company_id)
            ->where('work_point_id', $user->work_point_id)
            ->where('status', '!=', 'Deleted')->orderBy('level')->get();

        $workPoints = collect();
    }

    $companies = CompanySite::where('id', $user->company_id)->get();
    $staffUsers = User::where('company_id', $user->company_id)->where('status','Active')->orderBy('name')->get();

    return view('admin.hr.educations', compact('educations','workPoints','companies','staffUsers'));
}

public function storeEducation(Request $request)
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    $rules = [
        'user_id' => ['required','integer', Rule::exists('users','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })],
        'level' => ['required','string','max:255'],
        'status' => ['nullable', Rule::in(['Active','Inactive','Deleted'])],
    ];

    if ($isSuper) {
        $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })];
    }

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    \App\Models\StaffEducation::create([
        'user_id' => $request->user_id,
        'company_id' => $user->company_id,
        'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
        'level' => $request->level,
        'institution' => $request->institution,
        'field_of_study' => $request->field_of_study,
        'year_completed' => $request->year_completed,
        'status' => $request->status ?? 'Active',
    ]);

    Alert::success('Success','Education record created successfully.');
    return redirect()->route('hr.educations.index');
}

public function updateEducation(Request $request, $id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) {
        Alert::error('Error','Invalid identifier'); return back();
    }
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    $edu = \App\Models\StaffEducation::findOrFail($decrypted);
    if ($edu->company_id !== $user->company_id) {
        Alert::error('Unauthorized','You cannot edit records from other companies.'); return back();
    }

    $rules = [
        'level' => ['required','string','max:255'],
        'status' => ['required', Rule::in(['Active','Inactive','Deleted'])],
    ];
    if ($isSuper) {
        $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })];
    }
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    $edu->update([
        'company_id' => $user->company_id,
        'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
        'level' => $request->level,
        'institution' => $request->institution,
        'field_of_study' => $request->field_of_study,
        'year_completed' => $request->year_completed,
        'status' => $request->status,
    ]);

    Alert::success('Success','Education updated successfully.');
    return redirect()->route('hr.educations.index');
}

public function removeEducation($id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) {
        Alert::error('Error','Invalid identifier'); return back();
    }
    $user = auth()->user();
    $edu = \App\Models\StaffEducation::findOrFail($decrypted);
    if ($edu->company_id !== $user->company_id) {
        Alert::error('Unauthorized','You cannot remove records from other companies.'); return back();
    }
    $edu->update(['status' => 'Deleted']);
    Alert::success('Success','Education removed successfully.');
    return redirect()->route('hr.educations.index');
}
// View
public function staffDocuments()
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    if ($isSuper) {
        $docs = \App\Models\StaffDocument::with(['user','company','workpoint'])
            ->where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('created_at','desc')->get();

        $workPoints = WorkPoint::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
    } else {
        $docs = \App\Models\StaffDocument::with(['user','company','workpoint'])
            ->where('company_id', $user->company_id)
            ->where('work_point_id', $user->work_point_id)
            ->where('status', '!=', 'Deleted')->orderBy('created_at','desc')->get();

        $workPoints = collect();
    }

    $companies = CompanySite::where('id', $user->company_id)->get();
    $staffUsers = User::where('company_id', $user->company_id)->where('status','Active')->orderBy('name')->get();

    return view('admin.hr.documents', compact('docs','workPoints','companies','staffUsers'));
}

public function storeDocument(Request $request)
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    $rules = [
        'user_id' => ['required','integer', Rule::exists('users','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })],
        'file' => ['required','file','max:10240'], // max 10MB
        'title' => ['nullable','string','max:255'],
        'status' => ['nullable', Rule::in(['Active','Inactive','Deleted'])],
    ];

    if ($isSuper) {
        $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })];
    }

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    // store file in public folder (as you requested)
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $fileName = time().'_'.$file->getClientOriginalName();
        $dest = public_path('staff_documents');
        if (!file_exists($dest)) mkdir($dest, 0755, true);
        $file->move($dest, $fileName);
        $filePath = 'staff_documents/'.$fileName;
    } else {
        Alert::error('Error','No file uploaded'); return back();
    }

    \App\Models\StaffDocument::create([
        'user_id' => $request->user_id,
        'company_id' => $user->company_id,
        'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
        'title' => $request->title,
        'file_path' => $filePath,
        'file_name' => $file->getClientOriginalName(),
        'mime_type' => $file->getClientMimeType(),
        // 'size' => $file->getSize(),
        'status' => $request->status ?? 'Active',
    ]);

    Alert::success('Success','Document uploaded successfully.');
    return redirect()->route('hr.documents.index');
}

public function updateDocument(Request $request, $id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) {
        Alert::error('Error','Invalid identifier'); return back();
    }
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    $doc = \App\Models\StaffDocument::findOrFail($decrypted);
    if ($doc->company_id !== $user->company_id) {
        Alert::error('Unauthorized','You cannot edit records from other companies.'); return back();
    }

    $rules = [
        'title' => ['nullable','string','max:255'],
        'status' => ['required', Rule::in(['Active','Inactive','Deleted'])],
    ];
    if ($isSuper) {
        $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })];
    }

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    $updateData = [
        'company_id' => $user->company_id,
        'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
        'title' => $request->title,
        'status' => $request->status,
    ];

    // optional file replace
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $fileName = time().'_'.$file->getClientOriginalName();
        $dest = public_path('staff_documents');
        if (!file_exists($dest)) mkdir($dest, 0755, true);
        $file->move($dest, $fileName);
        $filePath = 'staff_documents/'.$fileName;

        // try to unlink old file (best-effort)
        try { if ($doc->file_path && file_exists(public_path($doc->file_path))) unlink(public_path($doc->file_path)); } catch (\Throwable $e) {}

        $updateData = array_merge($updateData, [
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            // 'size' => $file->getSize(),
        ]);
    }

    $doc->update($updateData);

    Alert::success('Success','Document updated successfully.');
    return redirect()->route('hr.documents.index');
}
public function removeDocument($id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) {
        Alert::error('Error','Invalid identifier'); return back();
    }
    $user = auth()->user();
    $doc = \App\Models\StaffDocument::findOrFail($decrypted);
    if ($doc->company_id !== $user->company_id) {
        Alert::error('Unauthorized','You cannot remove records from other companies.'); return back();
    }
    // soft-delete via status
    $doc->update(['status' => 'Deleted']);
    Alert::success('Success','Document removed successfully.');
    return redirect()->route('hr.documents.index');
}
// View
public function leaves()
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    if ($isSuper) {
        $leaves = \App\Models\LeaveRequest::with(['user','approver','company','workpoint'])
            ->where('company_id', $user->company_id)
            ->orderBy('start_date','desc')->get();

        $workPoints = WorkPoint::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
    } else {
        $leaves = \App\Models\LeaveRequest::with(['user','approver','company','workpoint'])
            ->where('company_id', $user->company_id)
            ->where('work_point_id', $user->work_point_id)
            ->orderBy('start_date','desc')->get();

        $workPoints = collect();
    }

    $companies = CompanySite::where('id', $user->company_id)->get();
    $staffUsers = User::where('company_id', $user->company_id)->where('status','Active')->orderBy('name')->get();

    return view('admin.hr.leaves', compact('leaves','workPoints','companies','staffUsers'));
}

public function storeLeave(Request $request)
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    $rules = [
        'user_id' => ['required','integer', Rule::exists('users','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })],
        'leave_type' => ['required','string','max:255'],
        'start_date' => ['required','date'],
        'end_date' => ['required','date','after_or_equal:start_date'],
        'status' => ['nullable', Rule::in(['Pending','Approved','Rejected','Cancelled'])],
    ];

    if ($isSuper) {
        $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })];
    }

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    \App\Models\LeaveRequest::create([
        'user_id' => $request->user_id,
        'company_id' => $user->company_id,
        'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
        'leave_type' => $request->leave_type,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'reason' => $request->reason,
        'status' => $request->status ?? 'Pending',
    ]);

    Alert::success('Success','Leave request created successfully.');
    return redirect()->route('hr.leaves.index');
}

public function updateLeave(Request $request, $id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) {
        Alert::error('Error','Invalid identifier'); return back();
    }
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    $leave = \App\Models\LeaveRequest::findOrFail($decrypted);
    if ($leave->company_id !== $user->company_id) {
        Alert::error('Unauthorized','You cannot edit records from other companies.'); return back();
    }

    $rules = [
        'leave_type' => ['required','string','max:255'],
        'start_date' => ['required','date'],
        'end_date' => ['required','date','after_or_equal:start_date'],
        'status' => ['required', Rule::in(['Pending','Approved','Rejected','Cancelled'])],
    ];
    if ($isSuper) {
        $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })];
    }
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    $leave->update([
        'company_id' => $user->company_id,
        'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
        'leave_type' => $request->leave_type,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'reason' => $request->reason,
        'status' => $request->status,
        'approved_by' => ($request->status === 'Approved') ? $user->id : $leave->approved_by,
        'admin_note' => $request->admin_note,
    ]);

    Alert::success('Success','Leave updated successfully.');
    return redirect()->route('hr.leaves.index');
}

public function removeLeave($id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) {
        Alert::error('Error','Invalid identifier'); return back();
    }
    $user = auth()->user();
    $leave = \App\Models\LeaveRequest::findOrFail($decrypted);
    if ($leave->company_id !== $user->company_id) {
        Alert::error('Unauthorized','You cannot remove records from other companies.'); return back();
    }
    $leave->update(['status' => 'Cancelled']);
    Alert::success('Success','Leave cancelled successfully.');
    return redirect()->route('hr.leaves.index');
}
/**
 * Approve leave request (called by Approve button)
 */
public function approveLeave(Request $request, $id)
{
    try {
        $decrypted = decrypt($id);
    } catch (\Throwable $th) {
        Alert::error('Error','Invalid identifier');
        return back();
    }

    $user = auth()->user();
    $leave = \App\Models\LeaveRequest::findOrFail($decrypted);
    if ($leave->company_id !== $user->company_id) {
        Alert::error('Unauthorized','You cannot approve leave requests from other companies.');
        return back();
    }

    if ($leave->status === 'Approved') {
        Alert::info('Info','Leave already approved.');
        return back();
    }

    $leave->update([
        'status' => 'Approved',
        'approved_by' => $user->id,
        'approved_at' => now(),
    ]);

    Alert::success('Success','Leave approved successfully.');
    return redirect()->route('hr.leaves.index');
}

/**
 * Printable leave view
 */
public function printLeave($id)
{
    try {
        $decrypted = decrypt($id);
    } catch (\Throwable $th) {
        Alert::error('Error','Invalid identifier');
        return back();
    }

    $user = auth()->user();
    $leave = \App\Models\LeaveRequest::with(['user','approver','company','workpoint'])->findOrFail($decrypted);
    if ($leave->company_id !== $user->company_id) {
        Alert::error('Unauthorized','You cannot print leave requests from other companies.');
        return back();
    }

    if ($leave->status !== 'Approved') {
        Alert::error('Error','Only approved leave requests can be printed.');
        return back();
    }
    return view('admin.hr.leave_print', compact('leave'));
}

private function nclCompanyId()
{
    return optional(CompanySite::where('company_code', 'NCL001')->first())->id;
}

private function payrollMonthStart($period)
{
    if (preg_match('/^\d{4}-\d{2}$/', $period)) {
        return Carbon::createFromFormat('Y-m', $period)->startOfMonth();
    }

    return Carbon::parse($period)->startOfMonth();
}

private function calculatePaye($grossSalary)
{
    $salary = (float) $grossSalary;
    $ti = $salary - ($salary * 0.10); // taxable income after employee NSSF 10%

    if ($ti >= 1000000) {
        return round((($ti - 1000000) * 0.30) + 128000, 2);
    } elseif ($ti >= 760000 && $ti < 1000000) {
        return round((($ti - 760000) * 0.25) + 68000, 2);
    } elseif ($ti >= 520000 && $ti < 760000) {
        return round((($ti - 520000) * 0.20) + 20000, 2);
    } elseif ($ti >= 270000 && $ti < 520000) {
        return round(($ti - 270000) * 0.08, 2);
    }

    return 0;
}

private function allowanceAndBonusForEmployee($employee, $period)
{
    $items = StaffSalaryAdjustment::where('user_id', $employee->id)
        ->where('period', $period)
        ->where('status', 'Active')
        ->get();

    $allowance = 0;
    $bonus = 0;

    foreach ($items as $item) {
        $value = 0;

        if ($item->calc_type === 'Percent') {
            $value = round(((float)$employee->gross_salary) * ((float)$item->rate / 100), 2);
        } else {
            $value = round((float)$item->amount, 2);
        }

        if ($item->type === 'Bonus') {
            $bonus += $value;
        } else {
            $allowance += $value;
        }
    }

    return [round($allowance, 2), round($bonus, 2)];
}

private function heslbDeductionForEmployee($employee, $basicSalary)
{
    $loan = HeslbLoan::where('user_id', $employee->id)
        ->where('status', 'Active')
        ->where('outstanding_balance', '>', 0)
        ->orderBy('id')
        ->first();

    if (!$loan) {
        return [0, 0, 0, null];
    }

    $before = round((float)$loan->outstanding_balance, 2);
    $monthly = round(((float)$basicSalary) * ((float)$loan->monthly_rate / 100), 2);
    $deduction = min($monthly, $before);
    $after = round($before - $deduction, 2);

    return [$deduction, $before, $after, $loan];
}

private function normalLoanDeductionForEmployee($employee)
{
    $remainingToDeduct = 0;

    $loans = EmployeeLoan::where('user_id', $employee->id)
        ->where('status', 'Active')
        ->where(function ($q) {
            $q->whereNull('balance')->orWhere('balance', '>', 0);
        })
        ->get();

    foreach ($loans as $loan) {
        $balance = (float)($loan->balance ?? $loan->amount);
        $monthly = (float)($loan->monthly_deduction ?? 0);

        if ($balance <= 0 || $monthly <= 0) {
            continue;
        }

        $remainingToDeduct += min($monthly, $balance);
    }

    return round($remainingToDeduct, 2);
}

private function refreshPayrollTotals($payrollId)
{
    $payroll = Payroll::with('lines')->findOrFail($payrollId);

    $payroll->update([
        'gross_total' => round($payroll->lines->sum('gross'), 2),
        'allowance_total' => round($payroll->lines->sum('allowances'), 2),
        'bonus_total' => round($payroll->lines->sum('bonus'), 2),
        'absence_total' => round($payroll->lines->sum('absence_deduction'), 2),
        'heslb_total' => round($payroll->lines->sum('heslb_deduction'), 2),
        'loan_total' => round($payroll->lines->sum('loan_deduction'), 2),
        'net_total' => round($payroll->lines->sum('net_pay'), 2),
        'paye_total' => round($payroll->lines->sum('paye'), 2),
        'employer_cost_total' => round($payroll->lines->sum('employer_cost'), 2),
        'payroll_cost_total' => round($payroll->lines->sum('total_payroll_cost'), 2),
    ]);
}

/*
|--------------------------------------------------------------------------
| HESLB CRUD
|--------------------------------------------------------------------------
*/
public function employeeLoans()
{
    // No auth company/work-point filtering here.
    // Show every non-deleted loan, every active work point, and every active staff/user.
    $loans = EmployeeLoan::with(['user.company', 'user.workpoint', 'company', 'workpoint.company'])
        ->where('status', '!=', 'Deleted')
        ->orderBy('created_at', 'desc')
        ->get();

    $companies = CompanySite::where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('company_name')
        ->get();
    $workPoints = WorkPoint::with('company')
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('company_id')
        ->orderBy('work_name')
        ->get();

    $staffUsers = User::with(['company', 'workpoint'])
        ->where('status', 'Active')
        ->orderBy('company_id')
        ->orderBy('work_point_id')
        ->orderBy('name')
        ->get();

    return view('admin.hr.loans', compact('loans', 'workPoints', 'companies', 'staffUsers'));
}

public function storeLoan(Request $request)
{
    $rules = [
        'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(function ($q) {
            $q->where('status', 'Active');
        })],
        'work_point_id' => ['required', 'integer', Rule::exists('work_points', 'id')->where(function ($q) {
            $q->where(function ($x) {
                $x->where('status', '!=', 'Deleted')->orWhereNull('status');
            });
        })],
        'type' => ['required', Rule::in(['Advance', 'Loan'])],
        'amount' => ['required', 'numeric', 'min:0'],
        'installments' => ['nullable', 'integer', 'min:1'],
    ];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $staff = User::findOrFail($request->user_id);
    $workPoint = WorkPoint::with('company')->findOrFail($request->work_point_id);

    $amount = round((float) $request->amount, 2);
    $installments = $request->installments ? (int) $request->installments : null;
    $monthly = $installments ? round($amount / $installments, 2) : null;

    EmployeeLoan::create([
        'user_id' => $staff->id,
        'company_id' => $workPoint->company_id ?: $staff->company_id,
        'work_point_id' => $workPoint->id,
        'type' => $request->type,
        'amount' => $amount,
        'balance' => $amount,
        'installments' => $installments,
        'monthly_deduction' => $monthly,
        'disbursed_at' => now(),
        'status' => 'Active',
    ]);

    Alert::success('Success', 'Advance/Loan recorded successfully.');
    return redirect()->route('hr.loans.index');
}

public function updateLoan(Request $request, $id)
{
        $decrypted = decrypt($id);
    $loan = EmployeeLoan::findOrFail($decrypted);

    $rules = [
        'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(function ($q) {
            $q->where('status', 'Active');
        })],
        'work_point_id' => ['required', 'integer', Rule::exists('work_points', 'id')->where(function ($q) {
            $q->where(function ($x) {
                $x->where('status', '!=', 'Deleted')->orWhereNull('status');
            });
        })],
        'type' => ['required', Rule::in(['Advance', 'Loan'])],
        'amount' => ['required', 'numeric', 'min:0'],
        'installments' => ['nullable', 'integer', 'min:1'],
        'status' => ['required', Rule::in(['Active', 'Paid', 'Defaulted', 'Deleted'])],
    ];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $staff = User::findOrFail($request->user_id);
    $workPoint = WorkPoint::with('company')->findOrFail($request->work_point_id);

    $oldAmount = round((float) $loan->amount, 2);
    $oldPaid = max(0, round($oldAmount - (float) ($loan->balance ?? 0), 2));

    $amount = round((float) $request->amount, 2);
    $installments = $request->installments ? (int) $request->installments : null;
    $monthly = $installments ? round($amount / $installments, 2) : null;

    // Preserve already paid amount when amount is edited.
    $newBalance = max(0, round($amount - $oldPaid, 2));

    if ($request->status === 'Paid') {
        $newBalance = 0;
    }

    $loan->update([
        'user_id' => $staff->id,
        'company_id' => $workPoint->company_id ?: $staff->company_id,
        'work_point_id' => $workPoint->id,
        'type' => $request->type,
        'amount' => $amount,
        'balance' => $newBalance,
        'installments' => $installments,
        'monthly_deduction' => $monthly,
        'status' => $request->status,
    ]);

    Alert::success('Success', 'Advance/Loan updated successfully.');
    return redirect()->route('hr.loans.index');
}

public function removeLoan($id)
{
        $decrypted = decrypt($id);
    $loan = EmployeeLoan::findOrFail($decrypted);
    $loan->update(['status' => 'Deleted']);

    Alert::success('Success', 'Record removed successfully.');
    return redirect()->route('hr.loans.index');
}
// ================= PAYROLL LIST =================

public function payrolls()
{
    $payrolls = Payroll::with(['company', 'workpoint', 'lines'])
        ->orderBy('period', 'desc')
        ->orderBy('id', 'desc')
        ->get();

    $companies = CompanySite::where('status', '!=', 'Deleted')
        ->orderBy('company_name')
        ->get();

    $nclCompany = CompanySite::where('company_code', 'NCL001')->first();

    $workPoints = collect();

    return view('admin.hr.payrolls', compact('payrolls', 'workPoints', 'companies', 'nclCompany'));
}

public function preparePayroll(Request $request)
{
    $validator = Validator::make($request->all(), [
        'period' => ['required', 'string'],
        'scope_type' => ['required', Rule::in(['All', 'Exclude-NCL', 'Only-NCL'])],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    try {
        $start = $this->payrollMonthStart($request->period);
    } catch (\Throwable $th) {
        Alert::error('Error', 'Invalid period format. Use YYYY-MM.');
        return back();
    }

    $end = (clone $start)->endOfMonth();
    $period = $start->format('Y-m');
    $daysInMonth = $start->daysInMonth;
    $scopeType = $request->scope_type;
    $nclId = $this->nclCompanyId();

    $existingQuery = Payroll::where('period', $period)
        ->where('scope_type', $scopeType)
        ->whereIn('status', ['Prepared', 'Approved', 'Paid']);

    if ($existingQuery->exists()) {
        Alert::error('Error', 'Payroll for this period and scope already exists.');
        return back();
    }

    DB::beginTransaction();

    try {
        $nssfEmployeeRate = 10.00;
        $nssfEmployerRate = 10.00;
        $sdlRate = 3.50;
        $wcfRate = 0.50;
        $psssfRate = 0.00;

        $payroll = Payroll::create([
            'company_id' => null,
            'work_point_id' => null,
            'scope_type' => $scopeType,
            'include_ncl' => $scopeType !== 'Exclude-NCL',
            'period' => $period,
            'days_in_month' => $daysInMonth,
            'prepared_at' => now(),
            'prepared_by' => auth()->id(),
            'status' => 'Prepared',

            'gross_total' => 0,
            'allowance_total' => 0,
            'bonus_total' => 0,
            'absence_total' => 0,
            'heslb_total' => 0,
            'loan_total' => 0,
            'net_total' => 0,
            'paye_total' => 0,
            'employer_cost_total' => 0,
            'payroll_cost_total' => 0,

            'nssf_employee_rate' => $nssfEmployeeRate,
            'nssf_employer_rate' => $nssfEmployerRate,
            'psssf_rate' => $psssfRate,
            'sdl_rate' => $sdlRate,
            'wcf_rate' => $wcfRate,
            'notes' => 'Payroll generated by one user. Scope: '.$scopeType.'. NCL code: NCL001.',
        ]);

        $employees = User::with(['company', 'comp_unit', 'workpoint'])
            ->where('status', 'Active')
            ->where('id', '!=', 1)
            ->where('gross_salary', '>', 0);

        if ($scopeType === 'Only-NCL') {
            if (!$nclId) {
                throw new \Exception('NCL company with company_code NCL001 was not found.');
            }
            $employees->where('company_id', $nclId);
        }

        if ($scopeType === 'Exclude-NCL' && $nclId) {
            $employees->where('company_id', '!=', $nclId);
        }

        $employees = $employees->orderBy('company_id')
            ->orderBy('comp_unit_id')
            ->orderBy('work_point_id')
            ->orderBy('name')
            ->get();

        if ($employees->count() < 1) {
            DB::rollBack();
            Alert::error('Error', 'No active employees found for this payroll scope.');
            return back();
        }

        $grossTotal = $allowanceTotal = $bonusTotal = $absenceTotal = 0;
        $heslbTotal = $loanTotal = $netTotal = $payeTotal = 0;
        $employerCostTotal = $payrollCostTotal = 0;

        foreach ($employees as $emp) {
            $basicSalary = round((float)($emp->gross_salary ?? 0), 2);

            [$allowances, $bonus] = $this->allowanceAndBonusForEmployee($emp, $period);

            $overtime = round((float) Overtime::where('user_id', $emp->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->where('status', 'Approved')
                ->sum('amount'), 2);

            $absences = Absence::where('user_id', $emp->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->where('status', 'Approved')
                ->get();

            $absentDays = round((float)$absences->sum('days'), 2);
            $dailyRate = round($basicSalary / $daysInMonth, 2);
            $absenceDeduction = round((float)$absences->sum('deduction_amount'), 2);

            // If old absence rows have no amount, auto-calculate.
            if ($absenceDeduction <= 0 && $absentDays > 0) {
                $absenceDeduction = round($dailyRate * $absentDays, 2);
            }

            $paidDays = max(0, round($daysInMonth - $absentDays, 2));

            [$heslbDeduction, $heslbBefore, $heslbAfter, $heslbLoan] = $this->heslbDeductionForEmployee($emp, $basicSalary);
            $loanDeduction = $this->normalLoanDeductionForEmployee($emp);

            // Requested formula: Gross = Basic + allowance + bonus - absentism. Overtime remains added if approved.
            $gross = round($basicSalary + $allowances + $bonus + $overtime - $absenceDeduction, 2);

            $nssfEmployee = round($gross * ($nssfEmployeeRate / 100), 2);
            $nssfEmployer = round($gross * ($nssfEmployerRate / 100), 2);
            $sdl = round($gross * ($sdlRate / 100), 2);
            $wcf = round($gross * ($wcfRate / 100), 2);
            $psssf = 0;

            $paye = $this->calculatePaye($gross);

            $totalDeductions = round(
                $paye + $nssfEmployee + $psssf + $loanDeduction + $heslbDeduction,
                2
            );

            $netPay = round($gross - $totalDeductions, 2);
            $employerCost = round($nssfEmployer + $sdl + $wcf, 2);
            $totalPayrollCost = round($gross + $employerCost, 2);

            $previousLine = PayrollLine::where('user_id', $emp->id)
                ->whereHas('payroll', function ($q) use ($period) {
                    $q->where('period', '<', $period)
                      ->whereIn('status', ['Prepared', 'Approved', 'Paid']);
                })
                ->latest('id')
                ->first();

            $previousNet = round((float)($previousLine->net_pay ?? 0), 2);
            $previousGross = round((float)($previousLine->gross ?? 0), 2);

            PayrollLine::create([
                'payroll_id' => $payroll->id,
                'user_id' => $emp->id,
                'company_id' => $emp->company_id,
                'work_point_id' => $emp->work_point_id,

                'calendar_days' => $daysInMonth,
                'absent_days' => $absentDays,
                'paid_days' => $paidDays,
                'daily_rate' => $dailyRate,

                'basic_salary' => $basicSalary,
                'allowances' => $allowances,
                'bonus' => $bonus,
                'overtime_payment' => $overtime,
                'gross' => $gross,

                'paye' => $paye,
                'nssf_employee' => $nssfEmployee,
                'nssf_employer' => $nssfEmployer,
                'psssf' => $psssf,
                'sdl' => $sdl,
                'wcf' => $wcf,

                'loan_deduction' => $loanDeduction,
                'heslb_deduction' => $heslbDeduction,
                'heslb_balance_before' => $heslbBefore,
                'heslb_balance_after' => $heslbAfter,

                'absence_deduction' => $absenceDeduction,
                'total_deductions' => $totalDeductions,
                'net_pay' => $netPay,

                'employer_cost' => $employerCost,
                'total_payroll_cost' => $totalPayrollCost,

                'previous_net_pay' => $previousNet,
                'net_variation' => round($netPay - $previousNet, 2),
                'gross_variation' => round($gross - $previousGross, 2),

                'note' => $heslbLoan ? 'HESLB loan ID '.$heslbLoan->id.' calculated; balance will reduce after Pay action.' : null,
            ]);

            $grossTotal += $gross;
            $allowanceTotal += $allowances;
            $bonusTotal += $bonus;
            $absenceTotal += $absenceDeduction;
            $heslbTotal += $heslbDeduction;
            $loanTotal += $loanDeduction;
            $netTotal += $netPay;
            $payeTotal += $paye;
            $employerCostTotal += $employerCost;
            $payrollCostTotal += $totalPayrollCost;
        }

        $payroll->update([
            'gross_total' => round($grossTotal, 2),
            'allowance_total' => round($allowanceTotal, 2),
            'bonus_total' => round($bonusTotal, 2),
            'absence_total' => round($absenceTotal, 2),
            'heslb_total' => round($heslbTotal, 2),
            'loan_total' => round($loanTotal, 2),
            'net_total' => round($netTotal, 2),
            'paye_total' => round($payeTotal, 2),
            'employer_cost_total' => round($employerCostTotal, 2),
            'payroll_cost_total' => round($payrollCostTotal, 2),
        ]);

        DB::commit();

        Alert::success('Success', 'Payroll prepared successfully for '.$employees->count().' employees. Period: '.$period.' Scope: '.$scopeType);
        return redirect()->route('hr.payrolls.index');

    } catch (\Throwable $th) {
        DB::rollBack();
        Alert::error('Error', $th->getMessage());
        return back();
    }
}

public function approvePayroll(Request $request, $id)
{
        $payrollId = decrypt($id);
    $payroll = Payroll::findOrFail($payrollId);

    if ($payroll->status !== 'Prepared') {
        Alert::error('Error', 'Only prepared payroll can be approved.');
        return back();
    }

    $payroll->update([
        'approved_at' => now(),
        'approved_by' => auth()->id(),
        'status' => 'Approved',
    ]);

    Alert::success('Success', 'Payroll approved successfully.');
    return redirect()->route('hr.payrolls.index');
}


// ================= PAY PAYROLL =================

public function payPayroll(Request $request, $id)
{
        $payrollId = decrypt($id);
    DB::beginTransaction();

    try {
        $payroll = Payroll::with('lines')->findOrFail($payrollId);

        if ($payroll->status !== 'Approved') {
            DB::rollBack();
            Alert::error('Error', 'Payroll must be Approved before paying.');
            return back();
        }

        foreach ($payroll->lines as $line) {
            if (($line->loan_deduction ?? 0) > 0) {
                $remaining = (float)$line->loan_deduction;

                $loans = EmployeeLoan::where('user_id', $line->user_id)
                    ->where('status', 'Active')
                    ->where(function ($q) {
                        $q->whereNull('balance')->orWhere('balance', '>', 0);
                    })
                    ->orderBy('id')
                    ->get();

                foreach ($loans as $loan) {
                    if ($remaining <= 0) break;

                    $balance = (float)($loan->balance ?? $loan->amount);
                    $deduct = min($remaining, $balance);
                    $newBalance = round($balance - $deduct, 2);

                    $loan->update([
                        'balance' => $newBalance,
                        'status' => $newBalance <= 0 ? 'Paid' : 'Active',
                    ]);

                    $remaining -= $deduct;
                }
            }

            if (($line->heslb_deduction ?? 0) > 0) {
                $loan = HeslbLoan::where('user_id', $line->user_id)
                    ->where('status', 'Active')
                    ->where('outstanding_balance', '>', 0)
                    ->orderBy('id')
                    ->first();

                if ($loan) {
                    $before = round((float)$loan->outstanding_balance, 2);
                    $amount = min((float)$line->heslb_deduction, $before);
                    $after = round($before - $amount, 2);

                    $loan->update([
                        'outstanding_balance' => $after,
                        'status' => $after <= 0 ? 'Paid' : 'Active',
                    ]);

                    HeslbLoanPayment::create([
                        'heslb_loan_id' => $loan->id,
                        'payroll_id' => $payroll->id,
                        'payroll_line_id' => $line->id,
                        'user_id' => $line->user_id,
                        'period' => $payroll->period,
                        'amount' => $amount,
                        'balance_before' => $before,
                        'balance_after' => $after,
                        'status' => 'Posted',
                        'notes' => 'Posted from payroll payment.',
                    ]);
                }
            }
        }

        $payroll->update([
            'paid_at' => now(),
            'paid_by' => auth()->id(),
            'status' => 'Paid',
        ]);

        DB::commit();

        Alert::success('Success', 'Payroll paid successfully and HESLB/loan balances updated.');
        return redirect()->route('hr.payrolls.index');

    } catch (\Throwable $th) {
        DB::rollBack();
        Alert::error('Error', $th->getMessage());
        return back();
    }
}

public function removePayroll($id)
{
        $payrollId = decrypt($id);

    $payroll = Payroll::findOrFail($payrollId);

    if (!in_array($payroll->status, ['Prepared', 'Cancelled', 'Rolled Back'])) {
        Alert::error('Error', 'Only Prepared, Cancelled or Rolled Back payroll can be removed.');
        return back();
    }

    $payroll->update(['status' => 'Cancelled']);

    Alert::success('Success', 'Payroll cancelled successfully.');
    return redirect()->route('hr.payrolls.index');
}

public function showPayroll($id)
{
        $payrollId = decrypt($id);
    

    $payroll = Payroll::with([
        'company',
        'workpoint',
        'preparer',
        'approver',
        'payer',
        'lines.user.company',
        'lines.user.comp_unit',
        'lines.user.workpoint',
    ])->findOrFail($payrollId);

    $userIds = $payroll->lines->pluck('user_id')->filter()->unique()->values();

    $previousLinesByUser = PayrollLine::with('payroll')
        ->whereIn('user_id', $userIds)
        ->whereHas('payroll', function ($q) use ($payroll) {
            $q->where('period', '<', $payroll->period)
                ->whereIn('status', ['Prepared', 'Approved', 'Paid']);
        })
        ->orderByDesc('id')
        ->get()
        ->unique('user_id')
        ->keyBy('user_id');

    return view('admin.hr.payroll_show', compact('payroll', 'previousLinesByUser'));
}

public function updatePayrollLine(Request $request, Payroll $payroll, PayrollLine $line)
{
    if ($line->payroll_id !== $payroll->id) {
        Alert::error('Error', 'Payroll line does not belong to this payroll.');
        return back();
    }

    if (!in_array($payroll->status, ['Prepared'])) {
        Alert::error('Error', 'Only Prepared payroll lines can be edited.');
        return back();
    }

    $validator = Validator::make($request->all(), [
        'basic_salary' => ['required','numeric','min:0'],
        'allowances' => ['nullable','numeric','min:0'],
        'bonus' => ['nullable','numeric','min:0'],
        'absence_deduction' => ['nullable','numeric','min:0'],
        'loan_deduction' => ['nullable','numeric','min:0'],
        'heslb_deduction' => ['nullable','numeric','min:0'],
        'overtime_payment' => ['nullable','numeric','min:0'],
        'note' => ['nullable','string'],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $basic = round((float)$request->basic_salary, 2);
    $allowance = round((float)$request->allowances, 2);
    $bonus = round((float)$request->bonus, 2);
    $absence = round((float)$request->absence_deduction, 2);
    $loan = round((float)$request->loan_deduction, 2);
    $heslb = round((float)$request->heslb_deduction, 2);
    $ot = round((float)$request->overtime_payment, 2);

    $gross = round($basic + $allowance + $bonus + $ot - $absence, 2);
    $nssfEmployee = round($gross * (($payroll->nssf_employee_rate ?? 10) / 100), 2);
    $nssfEmployer = round($gross * (($payroll->nssf_employer_rate ?? 10) / 100), 2);
    $sdl = round($gross * (($payroll->sdl_rate ?? 3.5) / 100), 2);
    $wcf = round($gross * (($payroll->wcf_rate ?? 0.5) / 100), 2);
    $paye = $this->calculatePaye($gross);
    $psssf = 0;
    $totalDeductions = round($paye + $nssfEmployee + $psssf + $loan + $heslb, 2);
    $net = round($gross - $totalDeductions, 2);
    $employerCost = round($nssfEmployer + $sdl + $wcf, 2);
    $payrollCost = round($gross + $employerCost, 2);

    $line->update([
        'basic_salary' => $basic,
        'allowances' => $allowance,
        'bonus' => $bonus,
        'absence_deduction' => $absence,
        'loan_deduction' => $loan,
        'heslb_deduction' => $heslb,
        'overtime_payment' => $ot,
        'gross' => $gross,
        'nssf_employee' => $nssfEmployee,
        'nssf_employer' => $nssfEmployer,
        'paye' => $paye,
        'psssf' => $psssf,
        'sdl' => $sdl,
        'wcf' => $wcf,
        'total_deductions' => $totalDeductions,
        'net_pay' => $net,
        'employer_cost' => $employerCost,
        'total_payroll_cost' => $payrollCost,
        'net_variation' => round($net - (float)$line->previous_net_pay, 2),
        'note' => $request->note,
    ]);

    $this->refreshPayrollTotals($payroll->id);

    Alert::success('Success', 'Payroll line updated successfully.');
    return back();
}

public function payrollReports($id)
{
        $payrollId = decrypt($id);

    $payroll = Payroll::with([
            'company',
            'workpoint',
            'preparer',
            'approver',
            'payer',
            'lines.user.company',
            'lines.user.comp_unit',
            'lines.user.workpoint',
        ])
        ->findOrFail($payrollId);

    return view('admin.hr.payroll_reports', compact('payroll'));
}

// ================= NET SHEET =================

public function payrollSheetNet($id)
{
        $payrollId = decrypt($id);
    $payroll = Payroll::with([
            'company',
            'workpoint',
            'preparer',
            'approver',
            'payer',
        ])
        ->findOrFail($payrollId);

    if (!in_array($payroll->status, ['Approved', 'Paid'])) {
        Alert::error('Error', 'Payroll must be Approved before printing Net sheet.');
        return back();
    }

    $lines = $payroll->lines()
        ->with(['user.company', 'user.comp_unit', 'user.workpoint'])
        ->orderBy('company_id')
        ->orderBy('work_point_id')
        ->orderBy('user_id')
        ->get();

    return view('admin.hr.payroll_sheets.net', compact('payroll', 'lines'));
}


// ================= NSSF SHEET =================

public function payrollSheetNssf($id)
{
        $payrollId = decrypt($id);
    $payroll = Payroll::with([
            'company',
            'workpoint',
            'preparer',
            'approver',
            'payer',
        ])
        ->findOrFail($payrollId);

    if (!in_array($payroll->status, ['Approved', 'Paid'])) {
        Alert::error('Error', 'Payroll must be Approved before printing NSSF sheet.');
        return back();
    }

    $lines = $payroll->lines()
        ->with(['user.company', 'user.comp_unit', 'user.workpoint'])
        ->orderBy('company_id')
        ->orderBy('work_point_id')
        ->orderBy('user_id')
        ->get();

    return view('admin.hr.payroll_sheets.nssf', compact('payroll', 'lines'));
}


// ================= WCF SHEET =================

public function payrollSheetWcf($id)
{
        $payrollId = decrypt($id);
    $payroll = Payroll::with([
            'company',
            'workpoint',
            'preparer',
            'approver',
            'payer',
        ])
        ->findOrFail($payrollId);

    if (!in_array($payroll->status, ['Approved', 'Paid'])) {
        Alert::error('Error', 'Payroll must be Approved before printing WCF sheet.');
        return back();
    }

    $lines = $payroll->lines()
        ->with(['user.company', 'user.comp_unit', 'user.workpoint'])
        ->orderBy('company_id')
        ->orderBy('work_point_id')
        ->orderBy('user_id')
        ->get();

    return view('admin.hr.payroll_sheets.wcf', compact('payroll', 'lines'));
}


// ================= SDL SHEET =================

public function payrollSheetSdl($id)
{
        $payrollId = decrypt($id);
    $payroll = Payroll::with([
            'company',
            'workpoint',
            'preparer',
            'approver',
            'payer',
        ])
        ->findOrFail($payrollId);

    if (!in_array($payroll->status, ['Approved', 'Paid'])) {
        Alert::error('Error', 'Payroll must be Approved before printing SDL sheet.');
        return back();
    }

    $lines = $payroll->lines()
        ->with(['user.company', 'user.comp_unit', 'user.workpoint'])
        ->orderBy('company_id')
        ->orderBy('work_point_id')
        ->orderBy('user_id')
        ->get();

    return view('admin.hr.payroll_sheets.sdl', compact('payroll', 'lines'));
}


// ================= LOANS SHEET =================

public function payrollSheetLoans($id)
{
        $payrollId = decrypt($id);
   $payroll = Payroll::with([
            'company',
            'workpoint',
            'preparer',
            'approver',
            'payer',
        ])
        ->findOrFail($payrollId);

    if (!in_array($payroll->status, ['Approved', 'Paid'])) {
        Alert::error('Error', 'Payroll must be Approved before printing Loans sheet.');
        return back();
    }

    $lines = $payroll->lines()
        ->with(['user.company', 'user.comp_unit', 'user.workpoint'])
        ->where('loan_deduction', '>', 0)
        ->orderBy('company_id')
        ->orderBy('work_point_id')
        ->orderBy('user_id')
        ->get();

    return view('admin.hr.payroll_sheets.loans', compact('payroll', 'lines'));
}

public function payrollSheetHeslb($id)
{
    try {
        $payrollId = decrypt($id);
    } catch (\Throwable $th) {
        Alert::error('Error', 'Invalid identifier');
        return back();
    }

    $payroll = Payroll::with([
            'company',
            'workpoint',
            'preparer',
            'approver',
            'payer',
        ])
        ->findOrFail($payrollId);

    if (!in_array($payroll->status, ['Approved', 'Paid'])) {
        Alert::error('Error', 'Payroll must be Approved before printing HESLB sheet.');
        return back();
    }

    $lines = $payroll->lines()
        ->with(['user.company', 'user.comp_unit', 'user.workpoint'])
        ->where('heslb_deduction', '>', 0)
        ->orderBy('company_id')
        ->orderBy('work_point_id')
        ->orderBy('user_id')
        ->get();

    return view('admin.hr.payroll_sheets.heslb', compact('payroll', 'lines'));
}

// ================= PAYSLIP =================

public function payslip($payrollId, $userId)
{
        $payrollId = decrypt($payrollId);
        $userId = decrypt($userId);
   
    $payroll = Payroll::with([
            'company',
            'workpoint',
            'preparer',
            'approver',
            'payer',
        ])
        ->findOrFail($payrollId);

    $line = PayrollLine::where('payroll_id', $payroll->id)
        ->where('user_id', $userId)
        ->first();
    if (!$line) {
        Alert::error('Not found', 'Payslip not found for that user in the selected payroll.');
        return back();
    }
    $staff = User::with(['company', 'comp_unit', 'workpoint'])
        ->findOrFail($userId);

    return view('admin.hr.payslip', compact('payroll', 'line', 'staff'));
}
// ================= PRINT PAYSLIP =================

public function printPayslip($payrollId, $userId)
{
    return $this->payslip($payrollId, $userId);
}
// List

public function overtimes()
{
    $overtimes = Overtime::with(['user', 'approver', 'company', 'workpoint'])
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('date', 'desc')
        ->orderBy('id', 'desc')
        ->get();

    $workPoints = WorkPoint::with('company')
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('company_id')
        ->orderBy('work_name')
        ->get();

    $companies = CompanySite::where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('company_name')
        ->get();

    $staffUsers = User::with(['company', 'workpoint'])
        ->where('status', 'Active')
        ->orderBy('company_id')
        ->orderBy('work_point_id')
        ->orderBy('name')
        ->get();

    return view('admin.hr.overtimes', compact('overtimes', 'workPoints', 'companies', 'staffUsers'));
}

public function storeOvertime(Request $request)
{
    $rules = [
        'work_point_id' => [
            'required',
            'integer',
            Rule::exists('work_points', 'id')->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->where('status', '!=', 'Deleted')
                       ->orWhereNull('status');
                });
            }),
        ],
        'user_id' => [
            'required',
            'integer',
            Rule::exists('users', 'id')->where(function ($q) {
                $q->where('status', 'Active');
            }),
        ],
        'date' => ['required', 'date'],
        'hours' => ['required', 'numeric', 'min:0.01'],
        'rate_per_hour' => ['nullable', 'numeric', 'min:0'],
        'note' => ['nullable', 'string', 'max:1000'],
    ];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $staff = User::findOrFail($request->user_id);
    $workPoint = WorkPoint::with('company')->findOrFail($request->work_point_id);

    $rate = $request->filled('rate_per_hour') ? (float) $request->rate_per_hour : null;
    $hours = (float) $request->hours;
    $amount = $rate !== null ? round($rate * $hours, 2) : 0;

    Overtime::create([
        'user_id' => $staff->id,
        'company_id' => $workPoint->company_id ?? $staff->company_id,
        'work_point_id' => $workPoint->id,
        'date' => $request->date,
        'hours' => $hours,
        'rate_per_hour' => $rate,
        'amount' => $amount,
        'status' => 'Pending',
        'approved_by' => null,
        'note' => $request->note,
    ]);

    Alert::success('Success', 'Overtime recorded successfully.');
    return redirect()->route('hr.overtimes.index');
}

public function updateOvertime(Request $request, $id)
{
        $decrypted = decrypt($id);

    $ot = Overtime::findOrFail($decrypted);

    if (in_array($ot->status, ['Approved', 'Paid', 'Deleted'])) {
        Alert::error('Locked', 'Approved, Paid or Deleted overtime cannot be edited.');
        return back();
    }

    $rules = [
        'work_point_id' => [
            'required',
            'integer',
            Rule::exists('work_points', 'id')->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->where('status', '!=', 'Deleted')
                       ->orWhereNull('status');
                });
            }),
        ],
        'user_id' => [
            'required',
            'integer',
            Rule::exists('users', 'id')->where(function ($q) {
                $q->where('status', 'Active');
            }),
        ],
        'date' => ['required', 'date'],
        'hours' => ['required', 'numeric', 'min:0.01'],
        'rate_per_hour' => ['nullable', 'numeric', 'min:0'],
        'note' => ['nullable', 'string', 'max:1000'],
        'status' => ['required', Rule::in(['Pending', 'Approved', 'Paid', 'Deleted'])],
    ];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $staff = User::findOrFail($request->user_id);
    $workPoint = WorkPoint::with('company')->findOrFail($request->work_point_id);

    $rate = $request->filled('rate_per_hour') ? (float) $request->rate_per_hour : null;
    $hours = (float) $request->hours;
    $amount = $rate !== null ? round($rate * $hours, 2) : 0;

    $updateData = [
        'company_id' => $workPoint->company_id ?? $staff->company_id,
        'work_point_id' => $workPoint->id,
        'user_id' => $staff->id,
        'date' => $request->date,
        'hours' => $hours,
        'rate_per_hour' => $rate,
        'amount' => $amount,
        'note' => $request->note,
        'status' => $request->status,
    ];

    if ($request->status === 'Approved') {
        $updateData['approved_by'] = auth()->id();
    }

    $ot->update($updateData);

    Alert::success('Success', 'Overtime updated successfully.');
    return redirect()->route('hr.overtimes.index');
}

public function approveOvertime(Request $request, $id)
{
        $decrypted = decrypt($id);

    $ot = Overtime::findOrFail($decrypted);

    if ($ot->status !== 'Pending') {
        Alert::error('Error', 'Only Pending overtime can be approved.');
        return back();
    }

    $ot->update([
        'status' => 'Approved',
        'approved_by' => auth()->id(),
    ]);

    Alert::success('Success', 'Overtime approved.');
    return redirect()->route('hr.overtimes.index');
}

public function payOvertime(Request $request, $id)
{
        $decrypted = decrypt($id);
    $ot = Overtime::findOrFail($decrypted);

    if ($ot->status !== 'Approved') {
        Alert::error('Error', 'Overtime must be Approved before paying.');
        return back();
    }

    $ot->update(['status' => 'Paid']);

    Alert::success('Success', 'Overtime marked as Paid.');
    return redirect()->route('hr.overtimes.index');
}

public function removeOvertime($id)
{
        $decrypted = decrypt($id);

    $ot = Overtime::findOrFail($decrypted);

    if (in_array($ot->status, ['Approved', 'Paid'])) {
        Alert::error('Locked', 'Approved or Paid overtime cannot be removed.');
        return back();
    }

    $ot->update(['status' => 'Deleted']);

    Alert::success('Success', 'Overtime removed.');
    return redirect()->route('hr.overtimes.index');
}
// ================= ABSENCES LIST - SHOW ALL COMPANY SITES / ALL WORK POINTS / ALL STAFF =================
public function absences()
{
    /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    | No auth user company filter.
    | No work point filter.
    | This allows the page to show all company sites, all work points, and all
    | active staff so the select boxes are not limited to one work point/user.
    */

    $absences = Absence::with(['user', 'approver', 'company', 'workpoint'])
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('date', 'desc')
        ->orderBy('id', 'desc')
        ->get();

    $companies = CompanySite::where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('company_name')
        ->get();

    $workPoints = WorkPoint::with('company')
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('company_id')
        ->orderBy('work_name')
        ->get();

    $staffUsers = User::with(['company', 'workpoint'])
        ->where(function ($q) {
            $q->where('status', 'Active')
              ->orWhereNull('status');
        })
        ->orderBy('company_id')
        ->orderBy('work_point_id')
        ->orderBy('name')
        ->get();

    return view('admin.hr.absences', compact('absences', 'workPoints', 'companies', 'staffUsers'));
}

// ================= STORE ABSENCE - COMPANY/WORK POINT COMES FROM SELECTED STAFF/WORK POINT =================
public function storeAbsence(Request $request)
{
    $rules = [
        'user_id' => [
            'required',
            'integer',
            Rule::exists('users', 'id')->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->where('status', 'Active')
                       ->orWhereNull('status');
                });
            }),
        ],
        'work_point_id' => [
            'nullable',
            'integer',
            Rule::exists('work_points', 'id')->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->where('status', '!=', 'Deleted')
                       ->orWhereNull('status');
                });
            }),
        ],
        'date' => ['required', 'date'],
        'days' => ['required', 'numeric', 'min:0.25'],
        'reason' => ['nullable', 'string'],
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $staff = User::with(['company', 'workpoint'])->findOrFail($request->user_id);

    $workPointId = $request->filled('work_point_id')
        ? (int) $request->work_point_id
        : ($staff->work_point_id ?: null);

    $selectedWorkPoint = $workPointId ? WorkPoint::find($workPointId) : null;

    /*
    |--------------------------------------------------------------------------
    | Company detection
    |--------------------------------------------------------------------------
    | First use staff company_id. If it is missing, use selected work point company_id.
    */
    $companyId = $staff->company_id ?: optional($selectedWorkPoint)->company_id;

    $date = Carbon::parse($request->date);
    $calendarDays = $date->daysInMonth;
    $days = round((float) $request->days, 2);

    if ($days > $calendarDays) {
        Alert::error('Error', 'Absent days cannot be greater than days of selected month: ' . $calendarDays);
        return back()->withInput();
    }

    $basicSalary = (float) ($staff->gross_salary ?? 0);
    $dailyRate = $calendarDays > 0 ? round($basicSalary / $calendarDays, 2) : 0;
    $deduction = round($dailyRate * $days, 2);
    $paidDays = max(0, round($calendarDays - $days, 2));

    Absence::create([
        'user_id' => $staff->id,
        'company_id' => $companyId,
        'work_point_id' => $workPointId,
        'date' => $date->toDateString(),
        'days' => $days,
        'calendar_days' => $calendarDays,
        'paid_days' => $paidDays,
        'daily_rate' => $dailyRate,
        'deduction_amount' => $deduction,
        'deduction_is_auto' => true,
        'reason' => $request->reason,
        'status' => 'Pending',
    ]);

    Alert::success('Success', 'Absence recorded. Deduction calculated from ' . $calendarDays . ' days month.');
    return redirect()->route('hr.absences.index');
}

// ================= UPDATE ABSENCE - NO AUTH COMPANY / WORK POINT FILTER =================
public function updateAbsence(Request $request, $id)
{
    try {
        $absenceId = decrypt($id);
    } catch (\Throwable $th) {
        Alert::error('Error', 'Invalid identifier.');
        return back();
    }

    $absence = Absence::findOrFail($absenceId);

    $rules = [
        'user_id' => [
            'required',
            'integer',
            Rule::exists('users', 'id')->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->where('status', 'Active')
                       ->orWhereNull('status');
                });
            }),
        ],
        'work_point_id' => [
            'nullable',
            'integer',
            Rule::exists('work_points', 'id')->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->where('status', '!=', 'Deleted')
                       ->orWhereNull('status');
                });
            }),
        ],
        'date' => ['required', 'date'],
        'days' => ['required', 'numeric', 'min:0.25'],
        'status' => ['required', Rule::in(['Pending', 'Approved', 'Rejected', 'Deleted'])],
        'reason' => ['nullable', 'string'],
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $staff = User::with(['company', 'workpoint'])->findOrFail($request->user_id);

    $workPointId = $request->filled('work_point_id')
        ? (int) $request->work_point_id
        : ($staff->work_point_id ?: $absence->work_point_id);

    $selectedWorkPoint = $workPointId ? WorkPoint::find($workPointId) : null;
    $companyId = $staff->company_id ?: optional($selectedWorkPoint)->company_id;

    $date = Carbon::parse($request->date);
    $calendarDays = $date->daysInMonth;
    $days = round((float) $request->days, 2);

    if ($days > $calendarDays) {
        Alert::error('Error', 'Absent days cannot be greater than days of selected month: ' . $calendarDays);
        return back()->withInput();
    }

    $basicSalary = (float) ($staff->gross_salary ?? 0);
    $dailyRate = $calendarDays > 0 ? round($basicSalary / $calendarDays, 2) : 0;
    $deduction = round($dailyRate * $days, 2);
    $paidDays = max(0, round($calendarDays - $days, 2));

    $absence->update([
        'user_id' => $staff->id,
        'company_id' => $companyId,
        'work_point_id' => $workPointId,
        'date' => $date->toDateString(),
        'days' => $days,
        'calendar_days' => $calendarDays,
        'paid_days' => $paidDays,
        'daily_rate' => $dailyRate,
        'deduction_amount' => $deduction,
        'deduction_is_auto' => true,
        'reason' => $request->reason,
        'status' => $request->status,
    ]);

    Alert::success('Success', 'Absence updated. Deduction recalculated from ' . $calendarDays . ' days month.');
    return redirect()->route('hr.absences.index');
}

// ================= REMOVE ABSENCE - NO AUTH COMPANY / WORK POINT FILTER =================
public function removeAbsence($id)
{
    try {
        $absenceId = decrypt($id);
    } catch (\Throwable $th) {
        Alert::error('Error', 'Invalid identifier.');
        return back();
    }

    $absence = Absence::findOrFail($absenceId);
    $absence->update(['status' => 'Deleted']);

    Alert::success('Success', 'Absence removed.');
    return redirect()->route('hr.absences.index');
}

// ================= APPROVE ABSENCE - NO AUTH COMPANY / WORK POINT FILTER =================
public function approveAbsence(Request $request, $id)
{
    try {
        $absenceId = decrypt($id);
    } catch (\Throwable $th) {
        Alert::error('Error', 'Invalid identifier.');
        return back();
    }

    $absence = Absence::findOrFail($absenceId);

    $absence->update([
        'status' => 'Approved',
        'approved_by' => auth()->id(),
    ]);

    Alert::success('Success', 'Absence approved.');
    return redirect()->route('hr.absences.index');
}
public function rollbackPayroll(Request $request, $id)
{
        $payrollId = decrypt($id);

    $validator = Validator::make($request->all(), [
        'rollback_reason' => ['required', 'string', 'max:1000'],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    DB::beginTransaction();

    try {
        $payroll = Payroll::with('lines')->findOrFail($payrollId);

        if (!in_array($payroll->status, ['Prepared', 'Approved', 'Paid'])) {
            DB::rollBack();
            Alert::error('Error', 'Only Prepared, Approved or Paid payroll can be rolled back.');
            return back();
        }

        if ($payroll->status === 'Paid') {
            $payments = HeslbLoanPayment::where('payroll_id', $payroll->id)
                ->where('status', 'Posted')
                ->get();

            foreach ($payments as $payment) {
                $loan = HeslbLoan::find($payment->heslb_loan_id);
                if ($loan) {
                    $loan->update([
                        'outstanding_balance' => round((float)$loan->outstanding_balance + (float)$payment->amount, 2),
                        'status' => 'Active',
                    ]);
                }

                $payment->update([
                    'status' => 'Reversed',
                    'notes' => trim(($payment->notes ?? '').' Reversed during payroll rollback.'),
                ]);
            }

            // Roll back normal employee loan balances by payroll line amount.
            foreach ($payroll->lines as $line) {
                $remaining = (float)($line->loan_deduction ?? 0);

                if ($remaining <= 0) continue;

                $loans = EmployeeLoan::where('user_id', $line->user_id)
                    ->whereIn('status', ['Active', 'Paid'])
                    ->orderByDesc('id')
                    ->get();

                foreach ($loans as $loan) {
                    if ($remaining <= 0) break;

                    $currentBalance = (float)($loan->balance ?? 0);
                    $maxBalance = (float)$loan->amount;
                    $canRestore = max(0, $maxBalance - $currentBalance);
                    $restore = min($remaining, $canRestore);

                    if ($restore > 0) {
                        $newBalance = round($currentBalance + $restore, 2);
                        $loan->update([
                            'balance' => $newBalance,
                            'status' => 'Active',
                        ]);
                        $remaining -= $restore;
                    }
                }
            }
        }

        $payroll->update([
            'status' => 'Rolled Back',
            'rolled_back_at' => now(),
            'rolled_back_by' => auth()->id(),
            'rollback_reason' => $request->rollback_reason,
        ]);

        DB::commit();

        Alert::success('Success', 'Payroll rolled back successfully.');
        return redirect()->route('hr.payrolls.index');

    } catch (\Throwable $th) {
        DB::rollBack();
        Alert::error('Error', $th->getMessage());
        return back();
    }
}

public function heslbLoans()
{
    $heslbLoans = HeslbLoan::with(['user', 'company', 'workpoint'])
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('created_at', 'desc')
        ->get();

    $companies = CompanySite::where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('company_name')
        ->get();

    $workPoints = WorkPoint::with('company')
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('work_name')
        ->get();

    $staffUsers = User::with(['company', 'workpoint'])
        ->where('status', 'Active')
        ->orderBy('name')
        ->get();

    return view('admin.hr.heslb_loans', compact('heslbLoans', 'workPoints', 'staffUsers', 'companies'));
}

public function storeHeslbLoan(Request $request)
{
    $rules = [
        'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(function ($q) {
            $q->where('status', 'Active');
        })],
        'work_point_id' => ['required', 'integer', Rule::exists('work_points', 'id')->where(function ($q) {
            $q->where(function ($qq) {
                $qq->where('status', '!=', 'Deleted')
                   ->orWhereNull('status');
            });
        })],
        'original_amount' => ['required', 'numeric', 'min:0'],
        'outstanding_balance' => ['nullable', 'numeric', 'min:0'],
        'monthly_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        'start_date' => ['nullable', 'date'],
        'notes' => ['nullable', 'string'],
    ];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $staff = User::findOrFail($request->user_id);
    $workPoint = WorkPoint::with('company')->findOrFail($request->work_point_id);

    $companyId = $workPoint->company_id ?: $staff->company_id;
    $originalAmount = round((float) $request->original_amount, 2);
    $outstandingBalance = $request->filled('outstanding_balance')
        ? round((float) $request->outstanding_balance, 2)
        : $originalAmount;

    HeslbLoan::create([
        'user_id' => $staff->id,
        'company_id' => $companyId,
        'work_point_id' => $workPoint->id,
        'original_amount' => $originalAmount,
        'outstanding_balance' => $outstandingBalance,
        'monthly_rate' => round((float) $request->monthly_rate, 2),
        'start_date' => $request->start_date,
        'status' => 'Active',
        'notes' => $request->notes,
    ]);

    Alert::success('Success', 'HESLB loan recorded successfully.');
    return redirect()->route('hr.heslb.index');
}

public function updateHeslbLoan(Request $request, $id)
{
    try {
        $loanId = decrypt($id);
    } catch (\Throwable $th) {
        Alert::error('Error', 'Invalid identifier.');
        return back();
    }

    $loan = HeslbLoan::findOrFail($loanId);

    $rules = [
        'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(function ($q) {
            $q->where('status', 'Active');
        })],
        'work_point_id' => ['required', 'integer', Rule::exists('work_points', 'id')->where(function ($q) {
            $q->where(function ($qq) {
                $qq->where('status', '!=', 'Deleted')
                   ->orWhereNull('status');
            });
        })],
        'original_amount' => ['required', 'numeric', 'min:0'],
        'outstanding_balance' => ['required', 'numeric', 'min:0'],
        'monthly_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        'status' => ['required', Rule::in(['Active', 'Paid', 'Suspended', 'Deleted'])],
        'start_date' => ['nullable', 'date'],
        'end_date' => ['nullable', 'date'],
        'notes' => ['nullable', 'string'],
    ];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $staff = User::findOrFail($request->user_id);
    $workPoint = WorkPoint::with('company')->findOrFail($request->work_point_id);
    $companyId = $workPoint->company_id ?: $staff->company_id;

    $loan->update([
        'user_id' => $staff->id,
        'company_id' => $companyId,
        'work_point_id' => $workPoint->id,
        'original_amount' => round((float) $request->original_amount, 2),
        'outstanding_balance' => round((float) $request->outstanding_balance, 2),
        'monthly_rate' => round((float) $request->monthly_rate, 2),
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'status' => $request->status,
        'notes' => $request->notes,
    ]);

    Alert::success('Success', 'HESLB loan updated successfully.');
    return redirect()->route('hr.heslb.index');
}

public function removeHeslbLoan($id)
{
    try {
        $loanId = decrypt($id);
    } catch (\Throwable $th) {
        Alert::error('Error', 'Invalid identifier.');
        return back();
    }

    $loan = HeslbLoan::findOrFail($loanId);
    $loan->update(['status' => 'Deleted']);

    Alert::success('Success', 'HESLB loan removed successfully.');
    return redirect()->route('hr.heslb.index');
}

/*
|--------------------------------------------------------------------------
| ALLOWANCE / BONUS CRUD
|--------------------------------------------------------------------------
*/

public function salaryAdjustments()
{
    $adjustments = StaffSalaryAdjustment::with(['user', 'company', 'workpoint'])
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('period', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

    $companies = CompanySite::where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('company_name')
        ->get();

    $workPoints = WorkPoint::with('company')
        ->where(function ($q) {
            $q->where('status', '!=', 'Deleted')
              ->orWhereNull('status');
        })
        ->orderBy('work_name')
        ->get();

    $staffUsers = User::with(['company', 'workpoint'])
        ->where('status', 'Active')
        ->orderBy('name')
        ->get();

    return view('admin.hr.salary_adjustments', compact(
        'adjustments',
        'companies',
        'workPoints',
        'staffUsers'
    ));
}

public function storeSalaryAdjustment(Request $request)
{
    $rules = [
        'work_point_id' => [
            'required',
            'integer',
            Rule::exists('work_points', 'id')->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->where('status', '!=', 'Deleted')
                       ->orWhereNull('status');
                });
            }),
        ],
        'user_id' => [
            'required',
            'integer',
            Rule::exists('users', 'id')->where(function ($q) {
                $q->where('status', 'Active');
            }),
        ],
        'period' => ['required', 'string'],
        'type' => ['required', Rule::in(['Allowance', 'Bonus'])],
        'calc_type' => ['required', Rule::in(['Fixed', 'Percent'])],
        'rate' => ['nullable', 'numeric', 'min:0'],
        'amount' => ['nullable', 'numeric', 'min:0'],
        'note' => ['nullable', 'string'],
    ];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $workPoint = WorkPoint::with('company')->findOrFail($request->work_point_id);
    $staff = User::findOrFail($request->user_id);

    try {
        $period = $this->payrollMonthStart($request->period)->format('Y-m');
    } catch (\Throwable $th) {
        try {
            $period = Carbon::parse($request->period . '-01')->format('Y-m');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Invalid period format. Use YYYY-MM.');
            return back()->withInput();
        }
    }

    if ($request->calc_type === 'Percent' && (float) ($request->rate ?? 0) <= 0) {
        Alert::error('Error', 'Please enter percentage rate for Percent calculation.');
        return back()->withInput();
    }

    if ($request->calc_type === 'Fixed' && (float) ($request->amount ?? 0) <= 0) {
        Alert::error('Error', 'Please enter amount for Fixed calculation.');
        return back()->withInput();
    }

    StaffSalaryAdjustment::create([
        'user_id' => $staff->id,
        'company_id' => $workPoint->company_id ?: $staff->company_id,
        'work_point_id' => $workPoint->id,
        'period' => $period,
        'type' => $request->type,
        'calc_type' => $request->calc_type,
        'rate' => $request->calc_type === 'Percent' ? round((float) $request->rate, 2) : null,
        'amount' => $request->calc_type === 'Fixed' ? round((float) $request->amount, 2) : 0,
        'status' => 'Active',
        'note' => $request->note,
    ]);

    Alert::success('Success', $request->type . ' recorded successfully.');
    return redirect()->route('hr.salary-adjustments.index');
}

public function updateSalaryAdjustment(Request $request, $id)
{
    try {
        $adjustmentId = decrypt($id);
    } catch (\Throwable $th) {
        Alert::error('Error', 'Invalid identifier.');
        return back();
    }

    $adjustment = StaffSalaryAdjustment::findOrFail($adjustmentId);

    $rules = [
        'work_point_id' => [
            'required',
            'integer',
            Rule::exists('work_points', 'id')->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->where('status', '!=', 'Deleted')
                       ->orWhereNull('status');
                });
            }),
        ],
        'user_id' => [
            'required',
            'integer',
            Rule::exists('users', 'id')->where(function ($q) {
                $q->where('status', 'Active');
            }),
        ],
        'period' => ['required', 'string'],
        'type' => ['required', Rule::in(['Allowance', 'Bonus'])],
        'calc_type' => ['required', Rule::in(['Fixed', 'Percent'])],
        'rate' => ['nullable', 'numeric', 'min:0'],
        'amount' => ['nullable', 'numeric', 'min:0'],
        'status' => ['required', Rule::in(['Active', 'Inactive', 'Deleted'])],
        'note' => ['nullable', 'string'],
    ];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $workPoint = WorkPoint::with('company')->findOrFail($request->work_point_id);
    $staff = User::findOrFail($request->user_id);

    try {
        $period = $this->payrollMonthStart($request->period)->format('Y-m');
    } catch (\Throwable $th) {
        try {
            $period = Carbon::parse($request->period . '-01')->format('Y-m');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Invalid period format. Use YYYY-MM.');
            return back()->withInput();
        }
    }

    if ($request->calc_type === 'Percent' && (float) ($request->rate ?? 0) <= 0) {
        Alert::error('Error', 'Please enter percentage rate for Percent calculation.');
        return back()->withInput();
    }

    if ($request->calc_type === 'Fixed' && (float) ($request->amount ?? 0) <= 0) {
        Alert::error('Error', 'Please enter amount for Fixed calculation.');
        return back()->withInput();
    }

    $adjustment->update([
        'user_id' => $staff->id,
        'company_id' => $workPoint->company_id ?: $staff->company_id,
        'work_point_id' => $workPoint->id,
        'period' => $period,
        'type' => $request->type,
        'calc_type' => $request->calc_type,
        'rate' => $request->calc_type === 'Percent' ? round((float) $request->rate, 2) : null,
        'amount' => $request->calc_type === 'Fixed' ? round((float) $request->amount, 2) : 0,
        'status' => $request->status,
        'note' => $request->note,
    ]);

    Alert::success('Success', 'Salary adjustment updated successfully.');
    return redirect()->route('hr.salary-adjustments.index');
}

public function removeSalaryAdjustment($id)
{
    try {
        $adjustmentId = decrypt($id);
    } catch (\Throwable $th) {
        Alert::error('Error', 'Invalid identifier.');
        return back();
    }

    $adjustment = StaffSalaryAdjustment::findOrFail($adjustmentId);
    $adjustment->update(['status' => 'Deleted']);

    Alert::success('Success', 'Salary adjustment removed successfully.');
    return redirect()->route('hr.salary-adjustments.index');
}

}