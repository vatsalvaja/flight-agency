<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $userId = session('user_id');
        $user = User::findOrFail($userId);
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Update the profile details.
     */
    public function update(Request $request)
    {
        $userId = session('user_id');
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'designation' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                $this->deletePublicUpload($user->profile_photo);
            }
            $path = $this->storePublicUpload($request->file('profile_photo'), 'profiles');
            $validated['profile_photo'] = $path;
        }

        $user->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Profile details updated successfully.');
    }

    /**
     * Show the account security settings page.
     */
    public function accountSettings(Request $request)
    {
        $userId = session('user_id');
        $user = User::findOrFail($userId);

        return view('admin.profile.account-settings', compact('user'));
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $userId = session('user_id');
        $user = User::findOrFail($userId);

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('account-settings.edit')->with('success', 'Password changed successfully.');
    }
}
