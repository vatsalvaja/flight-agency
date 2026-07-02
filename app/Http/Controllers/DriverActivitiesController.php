<?php

namespace App\Http\Controllers;

use App\Models\AssignLuggage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class DriverActivitiesController extends Controller
{
    /**
     * Display the driver activities monitoring board.
     */
    public function index(Request $request)
    {
        $userId = session('user_id');
        $loggedUser = User::find($userId);

        // Authorization check: Must be Admin (role_id = 0) or Manager (role contains "manager")
        $isAdmin = $loggedUser && $loggedUser->role_id === 0;
        $isManager = false;
        if ($loggedUser && $loggedUser->role_id > 0 && $loggedUser->role) {
            $isManager = (stripos($loggedUser->role->role_name, 'manager') !== false);
        }

        if (!$isAdmin && !$isManager) {
            abort(403, 'Unauthorized action. This monitoring dashboard is restricted to Administrators and Managers.');
        }

        // Fetch all Drivers for the filter dropdown
        $driverRole = Role::where('role_name', 'Driver')->first();
        $drivers = User::where('role_id', $driverRole ? $driverRole->id : -1)
                       ->where('status', 0)
                       ->orderBy('name', 'asc')
                       ->get();

        // Build base query
        $query = AssignLuggage::with(['company', 'station', 'driver', 'creator']);

        // Scope data: Managers only see activities of orders they created; Admins see everything.
        if ($isManager) {
            $query->where('created_by', $userId);
        }

        // Apply filters
        $driverId = $request->input('driver_id');
        $status = $request->input('status');
        $search = $request->input('search');

        if ($driverId) {
            $query->where('driver_id', $driverId);
        }

        if ($status && in_array($status, ['In Progress', 'Pickup', 'Delivered'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('pickup_location', 'like', "%{$search}%")
                  ->orWhere('drop_location', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('company', function($c) use ($search) {
                      $c->where('company_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('driver', function($d) use ($search) {
                      $d->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Calculate KPI stats before pagination (scoped by manager visibility, but before search/filters)
        $statsQuery = AssignLuggage::query();
        if ($isManager) {
            $statsQuery->where('created_by', $userId);
        }

        $kpis = [
            'total' => (clone $statsQuery)->count(),
            'transit' => (clone $statsQuery)->where('status', 'In Progress')->count(),
            'pickup' => (clone $statsQuery)->where('status', 'Pickup')->count(),
            'delivered' => (clone $statsQuery)->where('status', 'Delivered')->count(),
        ];

        // Fetch paginated assignments list
        $assignments = $query->latest()->paginate(10)->withQueryString();

        return view('admin.driver-activities.index', compact('assignments', 'drivers', 'kpis', 'driverId', 'status', 'search', 'loggedUser'));
    }
}
