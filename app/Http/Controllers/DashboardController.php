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
        $companiesCount = Company::count();
        // Placeholders as requested
        $stationsCount = 0; 
        $locationsCount = 0;

        return view('admin.dashboard', compact('companiesCount', 'stationsCount', 'locationsCount'));
    }
}
