<?php

namespace App\Http\Controllers;

use App\Models\AssignLuggage;
use App\Models\DriverCurrentLocation;
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
        [$loggedUser, $isAdmin, $isManager] = $this->authorizedDashboardContext();
        $userId = session('user_id');

        // Fetch all Drivers for the filter dropdown
        $driverRole = Role::where('role_name', 'Driver')->first();
        $drivers = User::where('role_id', $driverRole ? $driverRole->id : -1)
                       ->where('status', 0)
                       ->orderBy('name', 'asc')
                       ->get();

        // Apply filters
        $driverId = $request->input('driver_id');
        $status = $request->input('status');
        $search = $request->input('search');
        $query = $this->activityQuery($request, $isManager, $userId);
        $kpis = $this->kpis($isManager, $userId);

        // Orders in Pickup status are already collected by driver GPS tracking.
        $liveTrackingQuery = AssignLuggage::with(['company', 'station', 'driver', 'creator'])
            ->where('status', 'Pickup')
            ->whereNotNull('driver_id');

        if ($isManager) {
            $liveTrackingQuery->where('created_by', $userId);
        }

        $liveTrackingAssignments = $liveTrackingQuery
            ->latest()
            ->limit(12)
            ->get();

        $currentLocations = DriverCurrentLocation::whereIn('driver_id', $liveTrackingAssignments->pluck('driver_id')->filter()->unique())
            ->get()
            ->keyBy('driver_id');

        // Fetch paginated assignments list
        $assignments = $query->latest()->paginate(10)->withQueryString();

        return view('admin.driver-activities.index', compact(
            'assignments',
            'drivers',
            'kpis',
            'driverId',
            'status',
            'search',
            'loggedUser',
            'liveTrackingAssignments',
            'currentLocations'
        ));
    }

    /**
     * Return filtered activities for AJAX table/pagination refresh.
     */
    public function list(Request $request)
    {
        [$loggedUser, $isAdmin, $isManager] = $this->authorizedDashboardContext();

        $assignments = $this->activityQuery($request, $isManager, session('user_id'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Driver activities loaded successfully.',
            'data' => $assignments->getCollection()->map(function (AssignLuggage $assignment) {
                return $this->formatActivity($assignment);
            }),
            'meta' => [
                'current_page' => $assignments->currentPage(),
                'last_page' => $assignments->lastPage(),
                'per_page' => $assignments->perPage(),
                'total' => $assignments->total(),
                'from' => $assignments->firstItem(),
                'to' => $assignments->lastItem(),
            ],
            'kpis' => $this->kpis($isManager, session('user_id')),
        ]);
    }

    private function authorizedDashboardContext(): array
    {
        $userId = session('user_id');
        $loggedUser = User::find($userId);
        $isAdmin = $loggedUser && $loggedUser->role_id === 0;
        $isManager = false;

        if ($loggedUser && $loggedUser->role_id > 0 && $loggedUser->role) {
            $isManager = (stripos($loggedUser->role->role_name, 'manager') !== false);
        }

        if (!$isAdmin && !$isManager) {
            abort(403, 'Unauthorized action. This monitoring dashboard is restricted to Administrators and Managers.');
        }

        return [$loggedUser, $isAdmin, $isManager];
    }

    private function activityQuery(Request $request, bool $isManager, ?int $userId)
    {
        $query = AssignLuggage::with(['company', 'station', 'driver', 'creator']);

        if ($isManager) {
            $query->where('created_by', $userId);
        }

        $driverId = $request->input('driver_id');
        $status = $request->input('status');
        $search = $request->input('search');

        if ($driverId) {
            $query->where('driver_id', $driverId);
        }

        if ($status && in_array($status, ['In Progress', 'Pickup', 'Delivered'], true)) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pickup_location', 'like', "%{$search}%")
                    ->orWhere('drop_location', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($c) use ($search) {
                        $c->where('company_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('driver', function ($d) use ($search) {
                        $d->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function kpis(bool $isManager, ?int $userId): array
    {
        $statsQuery = AssignLuggage::query();

        if ($isManager) {
            $statsQuery->where('created_by', $userId);
        }

        return [
            'total' => (clone $statsQuery)->count(),
            'transit' => (clone $statsQuery)->where('status', 'In Progress')->count(),
            'pickup' => (clone $statsQuery)->where('status', 'Pickup')->count(),
            'delivered' => (clone $statsQuery)->where('status', 'Delivered')->count(),
        ];
    }

    private function formatActivity(AssignLuggage $assignment): array
    {
        $assignment->loadMissing(['company', 'station', 'driver', 'creator']);
        $driver = $assignment->driver;
        $company = $assignment->company;
        $driverName = $driver->name ?? 'Unassigned';
        $companyName = $company->company_name ?? 'N/A';
        $proofImages = is_array($assignment->delivery_proof_images) ? $assignment->delivery_proof_images : [];

        return [
            'id' => $assignment->id,
            'order_number' => '#ORD-' . str_pad($assignment->id, 5, '0', STR_PAD_LEFT),
            'driver_name' => $driverName,
            'driver_email' => $driver->email ?? 'N/A',
            'driver_initials' => ($driver && method_exists($driver, 'getInitials') && !empty($driverName)) ? $driver->getInitials() : 'UN',
            'driver_photo_url' => ($driver && !empty($driver->profile_photo)) ? asset($driver->profile_photo) : null,
            'company_name' => $companyName,
            'company_logo_url' => ($company && !empty($company->logo)) ? asset($company->logo) : null,
            'pickup_location' => $assignment->pickup_location ?? 'N/A',
            'drop_location' => $assignment->drop_location ?? 'N/A',
            'status' => $assignment->status ?? 'In Progress',
            'delivered_at' => $assignment->delivered_at ? $assignment->delivered_at->format('d M, H:i') : '',
            'delivery_proof_images' => collect($proofImages)->filter()->map(function ($path, $idx) use ($assignment) {
                return [
                    'url' => asset($path),
                    'label' => '#ORD-' . str_pad($assignment->id, 5, '0', STR_PAD_LEFT) . ' Proof Photo ' . ($idx + 1),
                ];
            })->values(),
            'show_url' => route('assign-luggage.show', $assignment->id),
        ];
    }
}
