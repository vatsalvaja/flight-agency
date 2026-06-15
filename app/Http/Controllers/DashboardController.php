<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $userId = session('user_id');
        $user = \App\Models\User::find($userId);

        if ($user && $user->role_id !== 0 && $user->role) {
            if (stripos($user->role->role_name, 'manager') !== false) {
                return redirect()->route('luggage-assign.index');
            }
            if (stripos($user->role->role_name, 'driver') !== false) {
                return redirect()->route('assignable-orders.index');
            }
        }

        $companiesCount = Company::count();
        // Placeholders as requested
        $stationsCount = 0; 
        $locationsCount = 0;

        return view('admin.dashboard', compact('companiesCount', 'stationsCount', 'locationsCount'));
    }
}
