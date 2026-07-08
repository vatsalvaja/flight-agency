<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Station;
use App\Models\User;
use App\Models\AssignLuggage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin/manager/driver dashboard.
     */
    public function index()
    {
        $context = $this->getDashboardUserContext();

        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        return view('admin.dashboard', [
            'isAdmin' => $context['isAdmin'],
            'isManager' => $context['isManager'],
            'isDriver' => $context['isDriver'],
        ]);
    }

    public function adminData()
    {
        return $this->dashboardJsonResponse('admin');
    }

    public function managerData()
    {
        return $this->dashboardJsonResponse('manager');
    }

    public function driverData()
    {
        return $this->dashboardJsonResponse('driver');
    }

    private function dashboardJsonResponse(string $role)
    {
        $dashboard = $this->getDashboardData($role);

        if ($dashboard instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please login again.',
            ], 401);
        }

        if ($dashboard instanceof \Illuminate\Http\JsonResponse) {
            return $dashboard;
        }

        return response()->json([
            'success' => true,
            'data' => $dashboard,
        ]);
    }

    private function getDashboardData(string $role)
    {
        $context = $this->getDashboardUserContext();

        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        $user = $context['user'];
        $isAdmin = $context['isAdmin'];
        $isManager = $context['isManager'];
        $isDriver = $context['isDriver'];

        if (
            ($role === 'admin' && !$isAdmin) ||
            ($role === 'manager' && !$isManager) ||
            ($role === 'driver' && !$isDriver)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized dashboard data request.',
            ], 403);
        }

        // Setup base query for luggage assignments scoped by role
        if ($role === 'admin') {
            $baseQuery = AssignLuggage::query();
        } elseif ($role === 'manager') {
            $baseQuery = AssignLuggage::where('created_by', $user->id);
        } else {
            $baseQuery = AssignLuggage::where('driver_id', $user->id);
        }

        // Core KPI counts (role-scoped metrics)
        $companiesCount = Company::count();
        $stationsCount = Station::count();
        $usersCount = User::count();
        $driversCount = User::whereHas('role', function($q) {
            $q->where('role_name', 'like', '%driver%');
        })->count();
        $managersCount = User::whereHas('role', function($q) {
            $q->where('role_name', 'like', '%manager%');
        })->count();

        // Scoped stats counters
        $assignmentsCount = (clone $baseQuery)->count();
        $pickupCount = (clone $baseQuery)->where('status', 'Pickup')->count();
        $inProgressCount = (clone $baseQuery)->where('status', 'In Progress')->count();
        $deliveredCount = (clone $baseQuery)->where('status', 'Delivered')->count();
        $totalDistance = round((clone $baseQuery)->sum('distance_km'), 2);

        // Average delivery speed/time logic
        $avgQuery = (clone $baseQuery)->where('status', 'Delivered')
            ->whereNotNull('delivered_at');

        if (DB::getDriverName() === 'sqlite') {
            $avgTimeHours = $avgQuery->selectRaw('ROUND(AVG(strftime("%s", delivered_at) - strftime("%s", created_at)) / 3600, 1) as avg_hours')
                ->value('avg_hours') ?? 0;
        } else {
            $avgTimeHours = $avgQuery->selectRaw('ROUND(AVG(TIMESTAMPDIFF(SECOND, created_at, delivered_at)) / 3600, 1) as avg_hours')
                ->value('avg_hours') ?? 0;
        }

        // Recent luggage assignments list
        $recentAssignments = (clone $baseQuery)->with(['company', 'station', 'driver'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // Outbound shipping trend for last 15 days
        $startDate = Carbon::now()->subDays(14)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $dailyTrendRaw = (clone $baseQuery)->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date_label'), 'status', DB::raw('count(*) as count'))
            ->groupBy('date_label', 'status')
            ->orderBy('date_label', 'asc')
            ->get();

        $trendLookup = [];
        foreach ($dailyTrendRaw as $row) {
            $trendLookup[$row->date_label][$row->status] = $row->count;
        }

        $dailyTrend = [];
        $currentDate = clone $startDate;
        while ($currentDate->lte($endDate)) {
            $formattedDate = $currentDate->format('Y-m-d');
            $dailyTrend[] = [
                'date' => $currentDate->format('M d'),
                'pickup' => $trendLookup[$formattedDate]['Pickup'] ?? 0,
                'in_progress' => $trendLookup[$formattedDate]['In Progress'] ?? 0,
                'delivered' => $trendLookup[$formattedDate]['Delivered'] ?? 0
            ];
            $currentDate->addDay();
        }

        // Shipments by flight company (Top 5)
        $companyWise = (clone $baseQuery)->select('company_id', DB::raw('count(*) as count'))
            ->groupBy('company_id')
            ->orderBy('count', 'desc')
            ->with('company')
            ->take(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->company ? $item->company->company_name : 'Unknown Company',
                    'count' => $item->count
                ];
            });

        return [
            'isAdmin' => $isAdmin,
            'isManager' => $isManager,
            'isDriver' => $isDriver,
            'counts' => [
                'companies' => $companiesCount,
                'stations' => $stationsCount,
                'users' => $usersCount,
                'drivers' => $driversCount,
                'managers' => $managersCount,
                'assignments' => $assignmentsCount,
                'pickup' => $pickupCount,
                'in_progress' => $inProgressCount,
                'delivered' => $deliveredCount,
                'total_distance' => $totalDistance,
                'avg_time_hours' => $avgTimeHours,
            ],
            'recent_assignments' => $recentAssignments->map(function ($assignment) use ($isDriver) {
                return [
                    'id' => $assignment->id,
                    'company' => [
                        'name' => optional($assignment->company)->company_name,
                        'logo' => optional($assignment->company)->logo ? asset($assignment->company->logo) : null,
                        'initial' => optional($assignment->company)->company_name ? substr($assignment->company->company_name, 0, 1) : 'C',
                    ],
                    'station' => [
                        'name' => optional($assignment->station)->station_name,
                    ],
                    'driver' => [
                        'name' => optional($assignment->driver)->name,
                    ],
                    'drop_location' => $assignment->drop_location,
                    'distance_km' => $assignment->distance_km,
                    'status' => $assignment->status,
                    'action_url' => $isDriver
                        ? route('assignable-orders.show', $assignment->id)
                        : route('assign-luggage.show', $assignment->id),
                ];
            })->values(),
            'daily_trend' => $dailyTrend,
            'company_wise' => $companyWise,
        ];
    }

    private function getDashboardUserContext()
    {
        $userId = session('user_id');
        $user = User::with('role')->find($userId);

        if (!$user) {
            return redirect('/');
        }

        $isAdmin = $user->role_id === 0;
        $isManager = false;
        $isDriver = false;

        if ($user->role_id > 0 && $user->role) {
            $isManager = stripos($user->role->role_name, 'manager') !== false;
            $isDriver = stripos($user->role->role_name, 'driver') !== false;
        }

        return [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'isManager' => $isManager,
            'isDriver' => $isDriver,
        ];
    }
}
