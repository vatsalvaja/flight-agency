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
     * Display the admin dashboard.
     */
    public function index()
    {
        $userId = session('user_id');
        $user = User::find($userId);

        if ($user && $user->role_id !== 0 && $user->role) {
            if (stripos($user->role->role_name, 'manager') !== false) {
                return redirect()->route('assign-luggage.index');
            }
            if (stripos($user->role->role_name, 'driver') !== false) {
                return redirect()->route('assignable-orders.index');
            }
        }

        // Core counts
        $companiesCount = Company::count();
        $stationsCount = Station::count();
        $usersCount = User::count();
        $driversCount = User::whereHas('role', function($q) {
            $q->where('role_name', 'like', '%driver%');
        })->count();
        $managersCount = User::whereHas('role', function($q) {
            $q->where('role_name', 'like', '%manager%');
        })->count();

        // Luggage shipment statistics
        $assignmentsCount = AssignLuggage::count();
        $pickupCount = AssignLuggage::where('status', 'Pickup')->count();
        $inProgressCount = AssignLuggage::where('status', 'In Progress')->count();
        $deliveredCount = AssignLuggage::where('status', 'Delivered')->count();
        $totalDistance = round(AssignLuggage::sum('distance_km'), 2);

        // Average delivery speed/time logic
        $deliveredOrders = AssignLuggage::where('status', 'Delivered')
            ->whereNotNull('delivered_at')
            ->get();
        $avgTimeHours = 0;
        if ($deliveredOrders->count() > 0) {
            $totalMinutes = 0;
            foreach ($deliveredOrders as $order) {
                $totalMinutes += $order->created_at->diffInMinutes($order->delivered_at);
            }
            $avgTimeHours = round(($totalMinutes / $deliveredOrders->count()) / 60, 1);
        }

        // Recent luggage assignments list
        $recentAssignments = AssignLuggage::with(['company', 'station', 'driver'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // Outbound shipping trend for last 15 days
        $startDate = Carbon::now()->subDays(14)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $dailyTrendRaw = AssignLuggage::whereBetween('created_at', [$startDate, $endDate])
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
        $companyWise = AssignLuggage::select('company_id', DB::raw('count(*) as count'))
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

        return view('admin.dashboard', compact(
            'companiesCount',
            'stationsCount',
            'usersCount',
            'driversCount',
            'managersCount',
            'assignmentsCount',
            'pickupCount',
            'inProgressCount',
            'deliveredCount',
            'totalDistance',
            'avgTimeHours',
            'recentAssignments',
            'dailyTrend',
            'companyWise'
        ));
    }
}

