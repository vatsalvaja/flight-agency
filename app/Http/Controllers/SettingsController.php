<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Show the form for editing the application settings.
     */
    public function edit()
    {
        $setting = Setting::firstOrCreate([], [
            'application_name' => 'Wings',
        ]);
        return view('admin.settings.edit', compact('setting'));
    }

    /**
     * Update the application settings in storage.
     */
    public function update(Request $request)
    {
        $setting = Setting::firstOrCreate([], [
            'application_name' => 'Wings',
        ]);

        $validated = $request->validate([
            'application_name' => 'required|string|max:255',
            'application_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,ico|max:1024',
        ]);

        if ($request->hasFile('application_logo')) {
            if ($setting->application_logo) {
                Storage::disk('public')->delete($setting->application_logo);
            }
            $path = $request->file('application_logo')->store('settings', 'public');
            $validated['application_logo'] = $path;
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon) {
                Storage::disk('public')->delete($setting->favicon);
            }
            $path = $request->file('favicon')->store('settings', 'public');
            $validated['favicon'] = $path;
        }

        $setting->update($validated);

        return redirect()->route('settings.edit')->with('success', 'Application settings updated successfully.');
    }
}
