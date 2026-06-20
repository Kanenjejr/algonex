<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Imports\PermissionImport;
use Maatwebsite\Excel\Facades\Excel;
use Image;
use Carbon\Carbon;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Console\Input\Input;

class PermissionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        // parent::__construct();
    }
    //role functions
    public function roleinfo()
    {
        $role = Role::where('Status', '=', 'Active')->get();
        return view('admin.role.Roles_Info', compact('role'));
    }
    public function regrole(Request $request)
    {
        try {
            $request->validate([
                'slug' => 'required',
            ]);
            $role = new Role();
            $role->name = $request->slug;
            $role->slug = $request->slug;
            $role->save();
            Alert::success('Congrats  ' . Auth()->user()->name, 'You\'ve Registered Staff Category Successfully');
            return redirect()->route('roleinfo');
        } catch (\Throwable $th) {
            Alert::error('Sorry! ' . Auth()->user()->name, 'Technical Error Exists, Please Contact IT Team For Support');
            return back();
        }
    }
    public function remvrole(Request $request, $id)
    {
        try {
            $role = Role::where('id', decrypt($id))->update(['Status' => 'Deleted']);
            Alert::success('Congrats  ' . Auth()->user()->name, 'You\'ve Removed Staff Category Successfully');
            return redirect()->route('roleinfo');
        } catch (\Throwable $th) {
            Alert::error('Sorry! ' . Auth()->user()->name, 'Technical Error Exists, Please Contact IT Team For Support');
            return back();
        }
    }
    //permisssion function
    public function perminfo()
    {
        try {
            $perm = DB::table('permissions')->orderBy('id')->groupBy('name')->where('Status', '=', 'Active')->get();
            return view('admin.role.Perm_Info', compact('perm'));
        } catch (\Throwable $th) {
            Alert::error('Sorry! ' . Auth()->user()->name, 'Technical Error Exists, Please Contact IT Team For Support');
            return back();
        }
    }
    public function regperm(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'slug' => 'required',
            ]);
            $perm = new Permission();
            $perm->name = $request->name;
            $perm->slug = $request->slug;
            $perm->save();
            Alert::success('Congrats  ' . Auth()->user()->name, 'You\'ve Registered Permission Successfully');
            return redirect()->route('perminfo');
        } catch (\Throwable $th) {
            Alert::error('Sorry! ' . Auth()->user()->name, 'Technical Error Exists, Please Contact IT Team For Support');
            return back();
        }
    }
    public function remvperm(Request $request, $id)
    {
        Permission::where('id', '=', decrypt($id))->update(['Status' => 'Deleted']);
        Alert::success('Congrats  ' . Auth()->user()->name, 'You\'ve Removed Permission Successfully');
        return redirect()->route('perminfo');
    }
    //assign role
    public function assignrole()
    {
        try {
            if ((Auth()->user()->role == 'Admin-Developer')|| (Auth()->user()->role == 'Admin')|| (Auth()->user()->role == 'CEO')){
                if ((Auth()->user()->role == 'Admin-Developer')) {
                    $role =User::where('Status','=','Active')->get();
                }else{
                    $role =User::where('Status','=','Active')->where('company_id',Auth()->user()->company_id)->get();
                }
                return view('admin.role.Assign_Role', compact('role'));
            } else {
                Alert::error('Sorry!  ' . Auth()->user()->name, 'You Are Not Allowed To Perfom This Task Please Contact MD Or Company Administrator.');
                return redirect()->back();
            }
        } catch (\Throwable $th) {
            Alert::error('Sorry! ' . Auth()->user()->name, 'Technical Error Exists, Please Contact IT Team For Support');
            return back();
        }
    }
    public function attachrole($id)
    {
        $user = User::find(decrypt($id));
        $perm = Permission::where('Status', '=', 'Active')->orderBy('id')->get();
        return view('admin.role.Attach_Role', compact('perm', 'user'));
    }
    public function storerole(Request $request)
    {
        try {
            $user = User::where('id', $request['User_id'])->firstOrFail();
            if ($request->has('assign')) {  // Assigning permissions
                if (empty($request->perm)) {
                    Alert::error('Sorry! ' . Auth()->user()->name, 'No Permissions Selected. Please Select Permissions');
                    return redirect()->back();
                } else {
                    if (($user->role == 'Admin-Developer') || ($user->role == 'Admin')) {
                        $perm = Permission::get();
                        $alreadyAssignedPerms = $user->permissions()->pluck('id')->toArray();

                        if (count($alreadyAssignedPerms) == count($perm)) {
                            Alert::error('Sorry! ' . Auth()->user()->name, 'All permissions are already assigned to this admin.');
                            return redirect()->route('assignrole');
                        }
                        $user->permissions()->sync($perm);
                        Alert::success('Congrats ' . Auth()->user()->name, 'You\'ve Assigned All Permissions To Admin Successfully');
                    } else {
                        $perm = Permission::whereIn('id', $request->perm)->get();
                        $alreadyAssignedPerms = $user->permissions()->whereIn('id', $request->perm)->pluck('id')->toArray();

                        if (count($alreadyAssignedPerms) == count($request->perm)) {
                            Alert::error('Sorry! ' . Auth()->user()->name, 'The selected permissions are already assigned.');
                            return redirect()->back();
                        }

                        $user->permissions()->attach($perm);
                        Alert::success('Congrats ' . Auth()->user()->name, 'You\'ve Assigned New Permissions Successfully');
                    }
                    return redirect()->back();
                }
            } else {  // Removing permissions
                if (empty($request->perm)) {
                    Alert::error('Sorry! ' . Auth()->user()->name, 'No Permissions Selected. Please Select Permissions');
                    return redirect()->back();
                } else {
                    $perm = Permission::whereIn('id', $request->perm)->get();
                    $currentlyAssignedPerms = $user->permissions()->whereIn('id', $request->perm)->pluck('id')->toArray();

                    if (empty($currentlyAssignedPerms)) {
                        Alert::error('Sorry! ' . Auth()->user()->name, 'The selected permissions are not currently assigned and cannot be removed.');
                        return redirect()->back();
                    }
                    $user->permissions()->detach($perm);
                    Alert::success('Congrats ' . Auth()->user()->name, 'You\'ve Removed Permissions Successfully');
                    return redirect()->back();
                }
            }
        } catch (\Throwable $th) {
            Alert::error('Sorry! ' . Auth()->user()->name, 'Technical Error Exists, Please Contact IT Team For Support');
            return back();
        }
    }
}
