@extends('layouts.admin')

@section('title', 'Admin Dashboard || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10 text-dark fw-bold">Wings Control Center</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item">Dashboard</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-soft-success text-success px-3 py-2 fs-12 fw-semibold rounded-pill">
                    <i class="feather-check-circle me-1"></i> System Online & Secure
                </span>
            </div>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <!-- Row 1: Core Entity KPI Cards -->
        <div class="row g-4 mb-4">
            <!-- Flight Companies -->
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('companies.index') }}" class="card-link-wrapper text-decoration-none">
                    <div class="card stretch stretch-full h-100 kpi-card hover-animate">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <span class="fs-12 text-muted text-uppercase d-block mb-1">Flight Companies</span>
                                <h3 class="fw-bold mb-0 text-dark">{{ $companiesCount }}</h3>
                            </div>
                            <div class="avatar-text avatar-md bg-soft-primary text-primary rounded p-3 fs-4 shadow-sm">
                                <i class="feather-briefcase"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Stations -->
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('stations.index') }}" class="card-link-wrapper text-decoration-none">
                    <div class="card stretch stretch-full h-100 kpi-card hover-animate">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <span class="fs-12 text-muted text-uppercase d-block mb-1">Active Stations</span>
                                <h3 class="fw-bold mb-0 text-dark">{{ $stationsCount }}</h3>
                            </div>
                            <div class="avatar-text avatar-md bg-soft-info text-info rounded p-3 fs-4 shadow-sm">
                                <i class="feather-map-pin"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Assignments -->
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('assign-luggage.index') }}" class="card-link-wrapper text-decoration-none">
                    <div class="card stretch stretch-full h-100 kpi-card hover-animate">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <span class="fs-12 text-muted text-uppercase d-block mb-1">Total Assignments</span>
                                <h3 class="fw-bold mb-0 text-dark">{{ $assignmentsCount }}</h3>
                            </div>
                            <div class="avatar-text avatar-md bg-soft-success text-success rounded p-3 fs-4 shadow-sm">
                                <i class="feather-package"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Users Directory -->
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('users.index') }}" class="card-link-wrapper text-decoration-none">
                    <div class="card stretch stretch-full h-100 kpi-card hover-animate">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <span class="fs-12 text-muted text-uppercase d-block mb-1">Staff Directory</span>
                                <h3 class="fw-bold mb-0 text-dark">{{ $usersCount }}</h3>
                                <div class="mt-1 fs-11 text-muted">
                                    <span class="badge bg-light text-dark me-1">{{ $managersCount }} Mgrs</span>
                                    <span class="badge bg-light text-dark">{{ $driversCount }} Dvr</span>
                                </div>
                            </div>
                            <div class="avatar-text avatar-md bg-soft-warning text-warning rounded p-3 fs-4 shadow-sm">
                                <i class="feather-users"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Row 2: Logistics Operations Sub-metrics -->
        <div class="row g-4 mb-4">
            <!-- Pending Pickup -->
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm d-flex flex-row align-items-center gap-3 p-4 h-100 metric-border-pickup">
                    <div class="avatar bg-soft-warning text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                        <i class="feather-clock fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $pickupCount }}</h4>
                        <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Pending Pickup</span>
                    </div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm d-flex flex-row align-items-center gap-3 p-4 h-100 metric-border-progress">
                    <div class="avatar bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                        <i class="feather-truck fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $inProgressCount }}</h4>
                        <span class="text-muted fs-11 text-uppercase fw-semibold d-block">In Transit</span>
                    </div>
                </div>
            </div>

            <!-- Delivered -->
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm d-flex flex-row align-items-center gap-3 p-4 h-100 metric-border-delivered">
                    <div class="avatar bg-soft-success text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                        <i class="feather-check-circle fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $deliveredCount }}</h4>
                        <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Delivered</span>
                    </div>
                </div>
            </div>

            <!-- Fleet Performance -->
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm d-flex flex-row align-items-center gap-3 p-4 h-100 metric-border-distance">
                    <div class="avatar bg-soft-purple text-purple rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                        <i class="feather-activity fs-4"></i>
                    </div>
                    <div class="w-100">
                        <h4 class="fw-bold mb-0 text-dark">{{ $totalDistance }} km</h4>
                        <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Logged Mileage</span>
                        <span class="text-purple fs-10 fw-semibold d-block mt-0.5"><i class="feather-clock me-0.5"></i>Avg: {{ $avgTimeHours }}h</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Analytical Charts Section -->
        <div class="row g-4 mb-4">
            <!-- Weekly Operations Activity Trend -->
            <div class="col-lg-8">
                <div class="card h-100 shadow-sm">
                    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-dark fw-bold mb-0">Daily Operational Activity Tracker</h6>
                            <span class="fs-12 text-muted">Tracking daily volume flows by status breakdowns for the last 15 days</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="dashboard-ops-trend-chart"></div>
                    </div>
                </div>
            </div>

            <!-- Status Distribution Donut -->
            <div class="col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header border-0 pb-0">
                        <h6 class="card-title text-dark fw-bold mb-0">Operational Status Ratio</h6>
                        <span class="fs-12 text-muted">Proportional status analysis of all scheduled luggage</span>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="dashboard-status-ratio-chart"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 4: Recent Activities & Administrative Console -->
        <div class="row g-4">
            <!-- Recent Bookings Table -->
            <div class="col-lg-8">
                <div class="card stretch stretch-full shadow-sm h-100">
                    <div class="card-header border-bottom d-flex align-items-center justify-content-between py-3">
                        <h6 class="card-title text-dark fw-bold mb-0"><i class="feather-package text-primary me-2"></i>Recent Luggage Bookings</h6>
                        <a href="{{ route('assign-luggage.index') }}" class="btn btn-sm btn-light-brand py-1 px-3 fs-11">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Company</th>
                                        <th>Route</th>
                                        <th>Driver</th>
                                        <th>Distance</th>
                                        <th>Status</th>
                                        <th class="pe-4 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAssignments as $row)
                                        <tr>
                                            <td class="ps-4 fw-semibold text-dark">#{{ $row->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($row->company && $row->company->logo)
                                                        <img src="{{ asset($row->company->logo) }}" alt="logo" class="rounded" style="height: 24px; width: 24px; object-fit: cover;">
                                                    @else
                                                        <div class="avatar-text avatar-xs bg-soft-secondary text-secondary rounded d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 10px;">
                                                            {{ $row->company ? substr($row->company->company_name, 0, 1) : 'C' }}
                                                        </div>
                                                    @endif
                                                    <span class="fw-semibold text-dark fs-12">{{ $row->company ? $row->company->company_name : 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fs-12 text-dark"><i class="feather-map-pin text-primary me-1 fs-10"></i>{{ $row->station ? $row->station->station_name : 'N/A' }}</span>
                                                    <span class="fs-11 text-muted"><i class="feather-arrow-right text-muted me-1 fs-10"></i>{{ Str::limit($row->drop_location, 25) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-dark fs-12 fw-medium">{{ $row->driver ? $row->driver->name : 'Unassigned' }}</span>
                                            </td>
                                            <td>{{ $row->distance_km }} km</td>
                                            <td>
                                                @if($row->status === 'Delivered')
                                                    <span class="badge bg-soft-success text-success px-2 py-0.5 fs-11">Delivered</span>
                                                @elseif($row->status === 'Pickup')
                                                    <span class="badge bg-soft-warning text-warning px-2 py-0.5 fs-11">Pickup</span>
                                                @else
                                                    <span class="badge bg-soft-primary text-primary px-2 py-0.5 fs-11">In Transit</span>
                                                @endif
                                            </td>
                                            <td class="pe-4 text-end">
                                                <a href="{{ route('assign-luggage.show', $row->id) }}" class="btn btn-xs btn-light-brand" title="View Booking">
                                                    <i class="feather-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">
                                                <i class="feather-alert-triangle fs-2 text-warning mb-2 d-block"></i>
                                                No luggage assignments found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Console & Admin Details -->
            <div class="col-lg-4">
                <div class="d-flex flex-column gap-4 h-100">
                    <!-- Quick Admin Controls -->
                    <div class="card shadow-sm">
                        <div class="card-header border-bottom py-3">
                            <h6 class="card-title text-dark fw-bold mb-0"><i class="feather-sliders text-primary me-2"></i>Quick Actions Console</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('assign-luggage.create') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 hover-shadow">
                                    <i class="feather-plus-circle"></i> New Luggage Assignment
                                </a>
                                <a href="{{ route('companies.create') }}" class="btn btn-light-brand w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="feather-briefcase"></i> Add Flight Company
                                </a>
                                <a href="{{ route('stations.create') }}" class="btn btn-light-info w-100 d-flex align-items-center justify-content-center gap-2 text-info" style="background-color: rgba(6, 182, 212, 0.1);">
                                    <i class="feather-map-pin"></i> Register New Station
                                </a>
                                <a href="{{ route('users.create') }}" class="btn btn-light-warning w-100 d-flex align-items-center justify-content-center gap-2 text-warning" style="background-color: rgba(245, 158, 11, 0.1);">
                                    <i class="feather-user-plus"></i> Create User Account
                                </a>
                                <a href="{{ route('reports.index') }}" class="btn btn-light-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="feather-bar-chart-2"></i> Operations Reports Center
                                </a>
                                <a href="{{ route('settings.edit') }}" class="btn btn-light w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="feather-settings"></i> Edit Application Settings
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Session Overview -->
                    <div class="card shadow-sm flex-fill">
                        <div class="card-header border-bottom py-3">
                            <h6 class="card-title text-dark fw-bold mb-0"><i class="feather-shield text-primary me-2"></i>Officer Account Profile</h6>
                        </div>
                        <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center">
                            @if(isset($loggedUser) && $loggedUser->profile_photo)
                                <img src="{{ asset($loggedUser->profile_photo) }}" alt="profile" class="img-fluid rounded-circle mb-3 shadow-sm border border-2 border-primary" style="width: 72px; height: 72px; object-fit: cover;" />
                            @elseif(isset($loggedUser))
                                <div class="avatar-text bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 72px; height: 72px; font-weight: 700; font-size: 24px;">
                                    {{ $loggedUser->getInitials() }}
                                </div>
                            @endif
                            <h5 class="fw-bold text-dark mb-1">{{ $loggedUser->name ?? 'Administrator' }}</h5>
                            <span class="badge bg-soft-primary text-primary px-3 py-1 fs-11 rounded-pill mb-3">{{ $loggedUser->designation ?? 'System Administrator' }}</span>
                            
                            <div class="border-top pt-3 w-100 text-start">
                                <div class="d-flex justify-content-between mb-1.5 fs-12 text-muted">
                                    <span>Last Login Time:</span>
                                    <span class="fw-semibold text-dark">{{ $loggedUser->last_login_at ? \Carbon\Carbon::parse($loggedUser->last_login_at)->format('Y-m-d H:i') : now()->format('Y-m-d H:i') }}</span>
                                </div>
                                <div class="d-flex justify-content-between fs-12 text-muted mb-1.5">
                                    <span>Last Login IP:</span>
                                    <span class="fw-semibold text-dark">{{ $loggedUser->last_login_ip ?? '127.0.0.1' }}</span>
                                </div>
                                <div class="d-flex justify-content-between fs-12 text-muted">
                                    <span>Contact Number:</span>
                                    <span class="fw-semibold text-dark">{{ $loggedUser->phone ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
@endsection

@push('head')
<style>
    /* Premium aesthetics for cards */
    .kpi-card {
        border: 0 !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 8px;
    }
    .hover-animate:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
    
    /* Status borders on operations counters */
    .metric-border-pickup {
        border-left: 4px solid #f59e0b !important;
        border-radius: 4px 8px 8px 4px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04);
    }
    .metric-border-progress {
        border-left: 4px solid #3b82f6 !important;
        border-radius: 4px 8px 8px 4px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04);
    }
    .metric-border-delivered {
        border-left: 4px solid #10b981 !important;
        border-radius: 4px 8px 8px 4px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04);
    }
    .metric-border-distance {
        border-left: 4px solid #8b5cf6 !important;
        border-radius: 4px 8px 8px 4px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04);
    }

    .hover-shadow:hover {
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3), 0 2px 4px -1px rgba(59, 130, 246, 0.1) !important;
    }
    
    /* Support dark skin background for dynamic colors */
    html.app-skin-dark .card {
        background-color: #111c2d !important;
        border: 0;
    }
    html.app-skin-dark .table-light {
        background-color: #1e293b !important;
        color: #f8fafc !important;
    }
    html.app-skin-dark .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.02) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Stacked Spline Area Chart: Outbound Shipping Trend for last 15 days
        var trendData = @json($dailyTrend);
        
        var trendOptions = {
            series: [{
                name: 'Picked Up',
                data: trendData.map(item => item.pickup)
            }, {
                name: 'In Transit',
                data: trendData.map(item => item.in_progress)
            }, {
                name: 'Delivered',
                data: trendData.map(item => item.delivered)
            }],
            chart: {
                type: 'area',
                height: 330,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            colors: ['#f59e0b', '#3b82f6', '#10b981'],
            xaxis: {
                categories: trendData.map(item => item.date),
                labels: {
                    style: { fontSize: '10px' }
                }
            },
            yaxis: {
                title: { text: 'Luggage Volume' },
                tickAmount: 5,
                labels: {
                    formatter: function(val) { return Math.round(val); }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            legend: {
                position: 'bottom',
                fontFamily: 'Inter, sans-serif'
            },
            grid: {
                borderColor: '#e2e8f0',
                strokeDashArray: 4
            }
        };

        var trendChart = new ApexCharts(document.querySelector("#dashboard-ops-trend-chart"), trendOptions);
        trendChart.render();

        // 2. Status Distribution Donut
        var donutOptions = {
            series: [
                {{ $inProgressCount }}, 
                {{ $pickupCount }}, 
                {{ $deliveredCount }}
            ],
            chart: {
                type: 'donut',
                height: 330,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false }
            },
            labels: ['In Transit', 'Picked Up', 'Delivered'],
            colors: ['#3b82f6', '#f59e0b', '#10b981'],
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return Math.round(val) + "%";
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Volume',
                                formatter: function (w) {
                                    return {{ $assignmentsCount }};
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                fontFamily: 'Inter, sans-serif'
            }
        };

        var donutChart = new ApexCharts(document.querySelector("#dashboard-status-ratio-chart"), donutOptions);
        donutChart.render();
    });
</script>
@endpush
