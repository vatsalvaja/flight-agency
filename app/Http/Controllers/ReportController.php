<?php

namespace App\Http\Controllers;

use App\Models\AssignLuggage;
use App\Models\Company;
use App\Models\Setting;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the logistics operations reports center.
     */
    public function index(Request $request)
    {
        // Get application settings and logged user
        $appSettings = Setting::first();
        $loggedUser = auth()->user();
        if (!$loggedUser) {
            $userId = session('user_id');
            $loggedUser = User::find($userId);
        }

        // Authorization check: Only Admin (role_id = 0), Manager, or Driver can view reports
        $isAdmin = $loggedUser && $loggedUser->role_id === 0;
        $isManager = false;
        $isDriver = false;
        if ($loggedUser && $loggedUser->role_id > 0 && $loggedUser->role) {
            $isManager = (stripos($loggedUser->role->role_name, 'manager') !== false);
            $isDriver = (stripos($loggedUser->role->role_name, 'driver') !== false);
        }

        if (!$isAdmin && !$isManager && !$isDriver) {
            abort(403, 'Unauthorized action. The Reports module is restricted to Administrators, Managers, and Drivers.');
        }

        // 1. Handle Date Presets
        $datePreset = $request->input('date_preset', 'monthly'); // default to last 30 days
        $startDate = null;
        $endDate = null;

        switch ($datePreset) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'weekly':
                $startDate = Carbon::now()->subDays(6)->startOfDay(); // last 7 days including today
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'monthly':
                $startDate = Carbon::now()->subDays(29)->startOfDay(); // last 30 days including today
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'yearly':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'custom':
            default:
                if ($request->filled('date_range')) {
                    $dates = explode(' - ', $request->date_range);
                    if (count($dates) === 2) {
                        try {
                            $startDate = Carbon::createFromFormat('m/d/Y', trim($dates[0]))->startOfDay();
                            $endDate = Carbon::createFromFormat('m/d/Y', trim($dates[1]))->endOfDay();
                        } catch (\Exception $e) {
                            try {
                                $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
                                $endDate = Carbon::parse(trim($dates[1]))->endOfDay();
                            } catch (\Exception $ex) {
                                // Fallback
                            }
                        }
                    }
                }
                
                // Fallback if custom date range parsing fails
                if (!$startDate || !$endDate) {
                    $startDate = Carbon::now()->subDays(29)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    $datePreset = 'monthly';
                }
                break;
        }

        // Update the date_range request string to match values for picker display
        $request->merge(['date_range' => $startDate->format('m/d/Y') . ' - ' . $endDate->format('m/d/Y')]);

        // 2. Build Base Query and Apply Filter Parameters
        $query = AssignLuggage::with(['company', 'station', 'driver', 'creator']);

        // Scope data for Managers (only see assignments they created) or Drivers (only see assignments assigned to them)
        if ($isManager) {
            $query->where('created_by', $loggedUser->id);
        } elseif ($isDriver) {
            $query->where('driver_id', $loggedUser->id);
        }

        // Apply date scope
        $query->whereBetween('created_at', [$startDate, $endDate]);

        // Filter: Flight Company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filter: Station
        if ($request->filled('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        // Filter: Driver
        if (!$isDriver && $request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter: Manager (created_by)
        if (!$isManager && !$isDriver && $request->filled('manager_id')) {
            $query->where('created_by', $request->manager_id);
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Resolve Applied Filter Label Names (For Excel and PDF exports metadata)
        $selectedCompanyName = 'All Companies';
        if ($request->filled('company_id')) {
            $item = Company::find($request->company_id);
            if ($item) $selectedCompanyName = $item->company_name;
        }

        $selectedStationName = 'All Stations';
        if ($request->filled('station_id')) {
            $item = Station::find($request->station_id);
            if ($item) $selectedStationName = $item->station_name;
        }

        $selectedDriverManagerName = 'All Managers';
        if ($isManager) {
            $selectedDriverManagerName = $loggedUser->name;
        } elseif ($request->filled('manager_id')) {
            $item = User::find($request->manager_id);
            if ($item) $selectedDriverManagerName = $item->name;
        }

        $selectedDriverName = 'All Drivers';
        if ($isDriver) {
            $selectedDriverName = $loggedUser->name;
        } elseif ($request->filled('driver_id')) {
            $item = User::find($request->driver_id);
            if ($item) $selectedDriverName = $item->name;
        }

        // 4. Compute Executive & Analytical KPI Statistics
        $kpiQuery = clone $query;
        
        $totalAssignments = $kpiQuery->count();
        $deliveredCount = (clone $kpiQuery)->where('status', 'Delivered')->count();
        $pickupCount = (clone $kpiQuery)->where('status', 'Pickup')->count();
        $inProgressCount = (clone $kpiQuery)->where('status', 'In Progress')->count();
        $totalDistance = round($kpiQuery->sum('distance_km'), 2);

        // Delivery Speed KPI (Average duration in hours for delivered assignments)
        $avgQuery = (clone $kpiQuery)
            ->where('status', 'Delivered')
            ->whereNotNull('delivered_at');

        if (DB::getDriverName() === 'sqlite') {
            $avgTimeHours = $avgQuery->selectRaw('ROUND(AVG(strftime("%s", delivered_at) - strftime("%s", created_at)) / 3600, 1) as avg_hours')
                ->value('avg_hours') ?? 0;
        } else {
            $avgTimeHours = $avgQuery->selectRaw('ROUND(AVG(TIMESTAMPDIFF(SECOND, created_at, delivered_at)) / 3600, 1) as avg_hours')
                ->value('avg_hours') ?? 0;
        }

        // 5. Query Redesigned Visual Chart Data (Daily Ops Stacked Activity)
        $dailyTrendRaw = (clone $kpiQuery)
            ->select(DB::raw('DATE(created_at) as date_label'), 'status', DB::raw('count(*) as count'))
            ->groupBy('date_label', 'status')
            ->orderBy('date_label', 'asc')
            ->get();

        // Build status lookup hash map
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
                'in_progress' => $trendLookup[$formattedDate]['In Progress'] ?? 0,
                'pickup' => $trendLookup[$formattedDate]['Pickup'] ?? 0,
                'delivered' => $trendLookup[$formattedDate]['Delivered'] ?? 0
            ];
            $currentDate->addDay();
        }

        // Status ratio distribution (Donut chart data)
        $statusDistribution = [
            'In Progress' => $inProgressCount,
            'Pickup' => $pickupCount,
            'Delivered' => $deliveredCount
        ];

        // 6. Corporate Management Breakdowns
        // Manager-wise Assignments List
        $managerWise = (clone $kpiQuery)
            ->select('created_by', DB::raw('count(*) as count'), DB::raw('sum(distance_km) as total_distance'))
            ->groupBy('created_by')
            ->orderBy('count', 'desc')
            ->with('creator')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->creator ? $item->creator->name : 'System/Admin',
                    'count' => $item->count,
                    'distance' => round($item->total_distance, 2)
                ];
            });

        // Driver-wise Performance metrics (Deliveries, completion rate, distance)
        $driverWise = (clone $kpiQuery)
            ->select('driver_id', DB::raw('count(*) as count'), DB::raw('sum(distance_km) as total_distance'), DB::raw('sum(case when status="Delivered" then 1 else 0 end) as completed_count'))
            ->groupBy('driver_id')
            ->orderBy('completed_count', 'desc')
            ->with('driver')
            ->get()
            ->map(function($item) {
                $compRate = $item->count > 0 ? round(($item->completed_count / $item->count) * 100, 1) : 0;
                return [
                    'name' => $item->driver ? $item->driver->name : 'Unassigned',
                    'total' => $item->count,
                    'completed' => $item->completed_count,
                    'rate' => $compRate,
                    'distance' => round($item->total_distance, 2)
                ];
            });

        // Company-wise Reports list
        $companyWise = (clone $kpiQuery)
            ->select('company_id', DB::raw('count(*) as count'), DB::raw('sum(distance_km) as total_distance'))
            ->groupBy('company_id')
            ->orderBy('count', 'desc')
            ->with('company')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->company ? $item->company->company_name : 'Unknown Company',
                    'count' => $item->count,
                    'distance' => round($item->total_distance, 2)
                ];
            });

        // Station-wise Traffic reports
        $stationWise = (clone $kpiQuery)
            ->select('station_id', DB::raw('count(*) as count'), DB::raw('sum(distance_km) as total_distance'))
            ->groupBy('station_id')
            ->orderBy('count', 'desc')
            ->with('station')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->station ? $item->station->station_name : 'Unknown Station',
                    'count' => $item->count,
                    'distance' => round($item->total_distance, 2)
                ];
            });

        // 7. Structured CSV / Excel Report Streamer
        if ($request->input('export') === 'csv') {
            $csvHeaders = [
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename=wings_management_report_' . now()->format('YmdHis') . '.csv',
                'Expires' => '0',
                'Pragma' => 'public'
            ];

            $allAssignments = $query->orderBy('created_at', 'desc')->get();

            $callback = function() use ($allAssignments, $totalAssignments, $deliveredCount, $inProgressCount, $pickupCount, $totalDistance, $avgTimeHours, $startDate, $endDate, $datePreset, $loggedUser, $selectedCompanyName, $selectedStationName, $selectedDriverManagerName, $selectedDriverName) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM to ensure Excel opens symbols and format correctly
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // A. Professional Report Branding Metadata Header
                fputcsv($file, ['WINGS LOGISTICS OPERATIONS - MANAGEMENT SUMMARY REPORT']);
                fputcsv($file, ['Generated Date', now()->format('Y-m-d H:i:s')]);
                fputcsv($file, ['Author / Officer', $loggedUser->name]);
                fputcsv($file, ['Date Scope Preset', ucfirst($datePreset)]);
                fputcsv($file, ['Date Range Span', $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d')]);
                fputcsv($file, ['Applied Filter Constraints', "Company: {$selectedCompanyName} | Station: {$selectedStationName} | Manager: {$selectedDriverManagerName} | Driver: {$selectedDriverName}"]);
                fputcsv($file, []); // Spacer line

                // B. Summary Cards Block
                fputcsv($file, ['EXECUTIVE PERFORMANCE METRICS']);
                fputcsv($file, ['Operational Indicator', 'Value', 'Unit']);
                fputcsv($file, ['Total Luggage Assignments', $totalAssignments, 'orders']);
                fputcsv($file, ['Completed Deliveries', $deliveredCount, 'orders']);
                fputcsv($file, ['Active In-Transit Logistics', $inProgressCount + $pickupCount, 'orders']);
                fputcsv($file, ['Total Distance Logged', $totalDistance, 'km']);
                fputcsv($file, ['Average Delivery Time Speed', $avgTimeHours, 'hours']);
                fputcsv($file, []);

                // C. Detailed Records Log Header
                fputcsv($file, ['DETAILED AUDIT TRAIL OF ASSIGNMENTS']);
                fputcsv($file, ['Ref ID', 'Created Date Time', 'Expected Delivery Date', 'Flight Company', 'Pickup Station', 'Dropoff Location', 'Assigned Driver', 'Distance (km)', 'Current Status', 'Delivered At']);

                foreach ($allAssignments as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->created_at->format('Y-m-d H:i:s'),
                        $row->expected_delivery_date ? $row->expected_delivery_date->format('Y-m-d') : 'N/A',
                        $row->company ? $row->company->company_name : 'N/A',
                        $row->station ? $row->station->station_name : 'N/A',
                        $row->drop_location,
                        $row->driver ? $row->driver->name : 'N/A',
                        $row->distance_km,
                        $row->status,
                        $row->delivered_at ? $row->delivered_at->format('Y-m-d H:i:s') : 'N/A'
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $csvHeaders);
        }

        // 8. Grab the full unpaginated filtered dataset for the print view
        $assignmentsForPrint = (clone $query)->orderBy('created_at', 'desc')->get();

        // 9. Grab paginated log dataset for the interactive DOM table
        $assignments = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // 10. Fetch filter entities
        $companiesList = Company::where('status', 'active')->orderBy('company_name', 'asc')->get();
        $stationsList = Station::where('status', 'active')->orderBy('station_name', 'asc')->get();
        
        $managersList = User::whereHas('role', function($q) {
            $q->where('role_name', 'like', '%manager%');
        })->where('status', 0)->orderBy('name', 'asc')->get();

        $driversList = User::whereHas('role', function($q) {
            $q->where('role_name', 'like', '%driver%');
        })->where('status', 0)->orderBy('name', 'asc')->get();

        return view('admin.reports.index', compact(
            'assignments',
            'assignmentsForPrint',
            'companiesList',
            'stationsList',
            'managersList',
            'driversList',
            'totalAssignments',
            'deliveredCount',
            'pickupCount',
            'inProgressCount',
            'totalDistance',
            'avgTimeHours',
            'statusDistribution',
            'dailyTrend',
            'managerWise',
            'driverWise',
            'companyWise',
            'stationWise',
            'datePreset',
            'selectedCompanyName',
            'selectedStationName',
            'selectedDriverManagerName',
            'selectedDriverName',
            'appSettings',
            'loggedUser',
            'isAdmin',
            'isManager',
            'isDriver'
        ));
    }

    /**
     * Return filtered report data for AJAX refresh.
     */
    public function list(Request $request)
    {
        [$loggedUser, $isAdmin, $isManager, $isDriver] = $this->reportContext();
        [$datePreset, $startDate, $endDate, $dateRange] = $this->reportDateRange($request);

        $query = $this->reportQuery($request, $loggedUser, $isManager, $isDriver, $startDate, $endDate);
        $metrics = $this->reportMetrics($query, $startDate, $endDate);
        $assignments = (clone $query)->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Reports loaded successfully.',
            'data' => [
                'date_preset' => $datePreset,
                'date_range' => $dateRange,
                'kpis' => [
                    'total_assignments' => $metrics['totalAssignments'],
                    'delivered_count' => $metrics['deliveredCount'],
                    'pickup_count' => $metrics['pickupCount'],
                    'in_progress_count' => $metrics['inProgressCount'],
                    'active_count' => $metrics['inProgressCount'] + $metrics['pickupCount'],
                    'total_distance' => $metrics['totalDistance'],
                    'delivered_distance' => $metrics['deliveredDistance'],
                    'average_distance' => $metrics['totalAssignments'] > 0 ? round($metrics['totalDistance'] / $metrics['totalAssignments'], 1) : 0,
                    'avg_time_hours' => $metrics['avgTimeHours'],
                ],
                'charts' => [
                    'daily_trend' => $metrics['dailyTrend'],
                    'status_distribution' => $metrics['statusDistribution'],
                    'company_names' => $metrics['companyWise']->pluck('name')->values(),
                    'company_counts' => $metrics['companyWise']->pluck('count')->values(),
                ],
                'breakdowns' => [
                    'manager_wise' => $metrics['managerWise']->values(),
                    'driver_wise' => $metrics['driverWise']->values(),
                    'company_wise' => $metrics['companyWise']->values(),
                    'station_wise' => $metrics['stationWise']->values(),
                ],
                'assignments' => $assignments->getCollection()->map(function (AssignLuggage $assignment) {
                    return $this->formatAssignmentRow($assignment);
                })->values(),
                'meta' => [
                    'current_page' => $assignments->currentPage(),
                    'last_page' => $assignments->lastPage(),
                    'per_page' => $assignments->perPage(),
                    'total' => $assignments->total(),
                    'from' => $assignments->firstItem(),
                    'to' => $assignments->lastItem(),
                ],
                'permissions' => [
                    'is_admin' => $isAdmin,
                    'is_manager' => $isManager,
                    'is_driver' => $isDriver,
                ],
            ],
        ]);
    }

    private function reportContext(): array
    {
        $loggedUser = auth()->user();
        if (!$loggedUser) {
            $loggedUser = User::find(session('user_id'));
        }

        $isAdmin = $loggedUser && $loggedUser->role_id === 0;
        $isManager = false;
        $isDriver = false;

        if ($loggedUser && $loggedUser->role_id > 0 && $loggedUser->role) {
            $isManager = stripos($loggedUser->role->role_name, 'manager') !== false;
            $isDriver = stripos($loggedUser->role->role_name, 'driver') !== false;
        }

        if (!$isAdmin && !$isManager && !$isDriver) {
            abort(403, 'Unauthorized action. The Reports module is restricted to Administrators, Managers, and Drivers.');
        }

        return [$loggedUser, $isAdmin, $isManager, $isDriver];
    }

    private function reportDateRange(Request $request): array
    {
        $datePreset = $request->input('date_preset', 'monthly');
        $startDate = null;
        $endDate = null;

        switch ($datePreset) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'weekly':
                $startDate = Carbon::now()->subDays(6)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'yearly':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'custom':
                if ($request->filled('date_range')) {
                    $dates = explode(' - ', $request->date_range);
                    if (count($dates) === 2) {
                        try {
                            $startDate = Carbon::createFromFormat('m/d/Y', trim($dates[0]))->startOfDay();
                            $endDate = Carbon::createFromFormat('m/d/Y', trim($dates[1]))->endOfDay();
                        } catch (\Exception $e) {
                            try {
                                $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
                                $endDate = Carbon::parse(trim($dates[1]))->endOfDay();
                            } catch (\Exception $ignored) {
                                $startDate = null;
                                $endDate = null;
                            }
                        }
                    }
                }
                break;
            case 'monthly':
            default:
                $datePreset = 'monthly';
                $startDate = Carbon::now()->subDays(29)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
        }

        if (!$startDate || !$endDate) {
            $datePreset = 'monthly';
            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        return [$datePreset, $startDate, $endDate, $startDate->format('m/d/Y') . ' - ' . $endDate->format('m/d/Y')];
    }

    private function reportQuery(Request $request, ?User $loggedUser, bool $isManager, bool $isDriver, Carbon $startDate, Carbon $endDate)
    {
        $query = AssignLuggage::with(['company', 'station', 'driver', 'creator'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($isManager && $loggedUser) {
            $query->where('created_by', $loggedUser->id);
        } elseif ($isDriver && $loggedUser) {
            $query->where('driver_id', $loggedUser->id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        if (!$isDriver && $request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if (!$isManager && !$isDriver && $request->filled('manager_id')) {
            $query->where('created_by', $request->manager_id);
        }

        if ($request->filled('status') && in_array($request->status, ['In Progress', 'Pickup', 'Delivered'], true)) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    private function reportMetrics($query, Carbon $startDate, Carbon $endDate): array
    {
        $kpiQuery = clone $query;
        $totalAssignments = (clone $kpiQuery)->count();
        $deliveredCount = (clone $kpiQuery)->where('status', 'Delivered')->count();
        $pickupCount = (clone $kpiQuery)->where('status', 'Pickup')->count();
        $inProgressCount = (clone $kpiQuery)->where('status', 'In Progress')->count();
        $totalDistance = round((float) (clone $kpiQuery)->sum('distance_km'), 2);
        $deliveredDistance = round((float) (clone $kpiQuery)->where('status', 'Delivered')->sum('distance_km'), 2);

        $avgQuery = (clone $kpiQuery)->where('status', 'Delivered')->whereNotNull('delivered_at');
        if (DB::getDriverName() === 'sqlite') {
            $avgTimeHours = $avgQuery->selectRaw('ROUND(AVG(strftime("%s", delivered_at) - strftime("%s", created_at)) / 3600, 1) as avg_hours')->value('avg_hours') ?? 0;
        } else {
            $avgTimeHours = $avgQuery->selectRaw('ROUND(AVG(TIMESTAMPDIFF(SECOND, created_at, delivered_at)) / 3600, 1) as avg_hours')->value('avg_hours') ?? 0;
        }

        $dailyTrendRaw = (clone $kpiQuery)
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
                'in_progress' => $trendLookup[$formattedDate]['In Progress'] ?? 0,
                'pickup' => $trendLookup[$formattedDate]['Pickup'] ?? 0,
                'delivered' => $trendLookup[$formattedDate]['Delivered'] ?? 0,
            ];
            $currentDate->addDay();
        }

        $managerWise = (clone $kpiQuery)
            ->select('created_by', DB::raw('count(*) as count'), DB::raw('sum(distance_km) as total_distance'))
            ->groupBy('created_by')
            ->orderBy('count', 'desc')
            ->with('creator')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->creator->name ?? 'System/Admin',
                    'count' => (int) ($item->count ?? 0),
                    'distance' => round((float) ($item->total_distance ?? 0), 2),
                ];
            });

        $driverWise = (clone $kpiQuery)
            ->select('driver_id', DB::raw('count(*) as count'), DB::raw('sum(distance_km) as total_distance'), DB::raw('sum(case when status="Delivered" then 1 else 0 end) as completed_count'))
            ->groupBy('driver_id')
            ->orderBy('completed_count', 'desc')
            ->with('driver')
            ->get()
            ->map(function ($item) {
                $count = (int) ($item->count ?? 0);
                $completed = (int) ($item->completed_count ?? 0);

                return [
                    'name' => $item->driver->name ?? 'Unassigned',
                    'total' => $count,
                    'completed' => $completed,
                    'rate' => $count > 0 ? round(($completed / $count) * 100, 1) : 0,
                    'distance' => round((float) ($item->total_distance ?? 0), 2),
                ];
            });

        $companyWise = (clone $kpiQuery)
            ->select('company_id', DB::raw('count(*) as count'), DB::raw('sum(distance_km) as total_distance'))
            ->groupBy('company_id')
            ->orderBy('count', 'desc')
            ->with('company')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->company->company_name ?? 'Unknown Company',
                    'count' => (int) ($item->count ?? 0),
                    'distance' => round((float) ($item->total_distance ?? 0), 2),
                ];
            });

        $stationWise = (clone $kpiQuery)
            ->select('station_id', DB::raw('count(*) as count'), DB::raw('sum(distance_km) as total_distance'))
            ->groupBy('station_id')
            ->orderBy('count', 'desc')
            ->with('station')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->station->station_name ?? 'Unknown Station',
                    'count' => (int) ($item->count ?? 0),
                    'distance' => round((float) ($item->total_distance ?? 0), 2),
                ];
            });

        return [
            'totalAssignments' => $totalAssignments,
            'deliveredCount' => $deliveredCount,
            'pickupCount' => $pickupCount,
            'inProgressCount' => $inProgressCount,
            'totalDistance' => $totalDistance,
            'deliveredDistance' => $deliveredDistance,
            'avgTimeHours' => $avgTimeHours,
            'dailyTrend' => $dailyTrend,
            'statusDistribution' => [
                'In Progress' => $inProgressCount,
                'Pickup' => $pickupCount,
                'Delivered' => $deliveredCount,
            ],
            'managerWise' => $managerWise,
            'driverWise' => $driverWise,
            'companyWise' => $companyWise,
            'stationWise' => $stationWise,
        ];
    }

    private function formatAssignmentRow(AssignLuggage $assignment): array
    {
        $assignment->loadMissing(['company', 'station', 'driver', 'creator']);
        $proofImages = is_array($assignment->delivery_proof_images) ? $assignment->delivery_proof_images : [];

        return [
            'id' => $assignment->id,
            'ref_id' => '#' . $assignment->id,
            'created_at' => $assignment->created_at ? $assignment->created_at->format('Y-m-d H:i') : 'N/A',
            'company_name' => $assignment->company->company_name ?? 'N/A',
            'station_name' => $assignment->station->station_name ?? 'N/A',
            'drop_location' => $assignment->drop_location ?? 'N/A',
            'manager_name' => $assignment->creator->name ?? 'System/Admin',
            'driver_name' => $assignment->driver->name ?? 'Unassigned',
            'distance_km' => round((float) ($assignment->distance_km ?? 0), 2),
            'status' => $assignment->status ?? 'In Progress',
            'has_proof' => count($proofImages) > 0,
        ];
    }
}
