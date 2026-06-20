<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CompanySite;
use App\Models\WorkPoint;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function Auth(Request $request)
    {
        // ✅ Validation (important sana)
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        try {
            $credentials = $request->only('username', 'password');

            // ✅ Attempt login
            if (Auth::attempt($credentials)) {

                $user = Auth::user();

                // ✅ Check user exists
                if (!$user) {
                    Auth::logout();
                    Alert::error('Error', 'User not found');
                    return back();
                }

                // ✅ Check status
                if ($user->status !== 'Active') {
                    Auth::logout();
                    Alert::warning('Inactive Account', 'Please contact your HOD to activate your account');
                    return back();
                }

                // ✅ Success login
                Alert::success('Welcome!', 'Login successful');
                return redirect()->route('company.dashboard');

            } else {
                Alert::error('Login Failed', 'Invalid username or password');
                return back();
            }

        } catch (\Throwable $th) {

            // ✅ Log error (professional)
            Log::error('LOGIN ERROR: ' . $th->getMessage());

            // ✅ TEMP (developer mode) → unaweza kuwasha uki-debug
            // dd($th->getMessage());

            // ✅ Safe message kwa user
            Alert::error('System Error', 'Something went wrong. Please contact IT support');

            return back();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::flush();
        return redirect()->route('login');
    }

    public function changePassword()
    {
        return view('auth.changepassword');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors(['new_password' => 'New password cannot be same as current']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        Alert::success('Success', 'Password changed successfully');
        return redirect()->route('login');
    }

    public function profile()
    {
        $user = Auth::user();
        $company = null;
        $workPoint = null;

        if ($user && $user->company_id) {
            $company = CompanySite::find($user->company_id);
        }

        if ($user && $user->work_point_id) {
            $workPoint = WorkPoint::find($user->work_point_id);
        }

        return view('auth.profile', compact('user', 'company', 'workPoint'));
    }

    public function editprofile()
    {
        $user = Auth::user();
        $companies = CompanySite::orderBy('company_name')->get();
        $workPoints = WorkPoint::orderBy('work_name')->get();

        return view('auth.edit-profile', compact('user', 'companies', 'workPoints'));
    }

    public function updateprofile(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'username' => ['required','string','max:255', Rule::unique('users','username')->ignore($user->id)],
            'email' => ['nullable','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'phone_No' => ['nullable','string','max:50'],
            'gender' => ['nullable','in:Male,Female,Other'],
            'company_id' => ['nullable','exists:company_sites,id'],
            'work_point_id' => ['nullable','exists:work_points,id'],
            'Image' => ['nullable','image','mimes:jpg,jpeg,png,gif','max:4096'],
        ]);

        // Assign values
        $user->fill($validated);

        // Handle image
        if ($request->hasFile('Image')) {
            $file = $request->file('Image');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('storage'), $filename);

            if ($user->image && file_exists(public_path('storage/'.$user->image))) {
                unlink(public_path('storage/'.$user->image));
            }

            $user->image = $filename;
        }

        $user->save();

        Alert::success('Success', 'Profile updated successfully');
        return redirect()->route('profile');
    }

    public function main()
    {
        return view('auth.main');
    }
}