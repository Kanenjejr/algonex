<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_image')) {
            // delete old image if exists
            if ($user->image) {
                Storage::delete('public/'.$user->image);
            }

            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $user->image = $imagePath;
            $user->save();
        }

        return back()->with('success', 'Profile image updated successfully!');
    }
}