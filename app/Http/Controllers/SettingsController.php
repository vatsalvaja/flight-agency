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
            $path = $this->storePublicUpload($request->file('application_logo'), 'settings');
            $validated['application_logo'] = $path;
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon) {
                Storage::disk('public')->delete($setting->favicon);
            }
            $path = $this->storePublicUpload($request->file('favicon'), 'settings');
            $validated['favicon'] = $path;
        }

        $setting->update($validated);

        return redirect()->route('settings.edit')->with('success', 'Application settings updated successfully.');
    }

    /**
     * Remove the application logo from storage and database.
     */
    public function removeLogo()
    {
        $setting = Setting::firstOrCreate([], [
            'application_name' => 'Wings',
        ]);

        if ($setting->application_logo) {
            Storage::disk('public')->delete($setting->application_logo);
            $setting->update(['application_logo' => null]);
        }

        return redirect()->route('settings.edit')->with('success', 'Application logo removed successfully.');
    }

    /**
     * Remove the favicon from storage and database.
     */
    public function removeFavicon()
    {
        $setting = Setting::firstOrCreate([], [
            'application_name' => 'Wings',
        ]);

        if ($setting->favicon) {
            Storage::disk('public')->delete($setting->favicon);
            $setting->update(['favicon' => null]);
        }

        return redirect()->route('settings.edit')->with('success', 'Favicon removed successfully.');
    }
}
