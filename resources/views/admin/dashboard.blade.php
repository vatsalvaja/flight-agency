@extends('layouts.admin')

@php
    $isAdmin = isset($isAdmin) ? (bool) $isAdmin : false;
    $isManager = isset($isManager) ? (bool) $isManager : false;
    $isDriver = isset($isDriver) ? (bool) $isDriver : false;
    $dashboardTitle = 'Wings Control Center';
    if ($isManager) {
        $dashboardTitle = 'Manager Operations Dashboard';
    } elseif ($isDriver) {
        $dashboardTitle = 'Driver Logistics Dashboard';
    }
@endphp

@section('title', $dashboardTitle . ' || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <div id="dashboardConfig"
        data-url="{{ route('admin.dashboard.data') }}"
        data-is-driver="{{ $isDriver ? '1' : '0' }}"
        data-empty-message="No allocated shipments found.">
    </div>

    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10 text-dark fw-bold">{{ $dashboardTitle }}</h5>
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
        <!-- Row 1: KPI Cards Grid -->
        <div class="row g-4 mb-4">
            @if($isAdmin)
                <!-- Admin KPIs -->
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('companies.index') }}" class="card-link-wrapper text-decoration-none">
                        <div class="card stretch stretch-full h-100 kpi-card hover-animate">
                            <div class="card-body d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <span class="fs-12 text-muted text-uppercase d-block mb-1">Flight Companies</span>
                                    <h3 class="fw-bold mb-0 text-dark" id="dashboard-companies-count">--</h3>
                                </div>
                                <div class="avatar-text avatar-md bg-soft-primary text-primary rounded p-3 fs-4 shadow-sm">
                                    <i class="feather-briefcase"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('stations.index') }}" class="card-link-wrapper text-decoration-none">
                        <div class="card stretch stretch-full h-100 kpi-card hover-animate">
                            <div class="card-body d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <span class="fs-12 text-muted text-uppercase d-block mb-1">Active Stations</span>
                                    <h3 class="fw-bold mb-0 text-dark" id="dashboard-stations-count">--</h3>
                                </div>
                                <div class="avatar-text avatar-md bg-soft-info text-info rounded p-3 fs-4 shadow-sm">
                                    <i class="feather-map-pin"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('assign-luggage.index') }}" class="card-link-wrapper text-decoration-none">
                        <div class="card stretch stretch-full h-100 kpi-card hover-animate">
                            <div class="card-body d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <span class="fs-12 text-muted text-uppercase d-block mb-1">Total Shipments</span>
                                    <h3 class="fw-bold mb-0 text-dark" id="dashboard-assignments-count">--</h3>
                                </div>
                                <div class="avatar-text avatar-md bg-soft-success text-success rounded p-3 fs-4 shadow-sm">
                                    <i class="feather-package"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('users.index') }}" class="card-link-wrapper text-decoration-none">
                        <div class="card stretch stretch-full h-100 kpi-card hover-animate">
                            <div class="card-body d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <span class="fs-12 text-muted text-uppercase d-block mb-1">Staff Directory</span>
                                    <h3 class="fw-bold mb-0 text-dark" id="dashboard-users-count">--</h3>
                                    <div class="mt-1 fs-11 text-muted">
                                        <span class="badge bg-light text-dark me-1"><span id="dashboard-managers-count">--</span> Mgr</span>
                                        <span class="badge bg-light text-dark"><span id="dashboard-drivers-count">--</span> Dvr</span>
                                    </div>
                                </div>
                                <div class="avatar-text avatar-md bg-soft-warning text-warning rounded p-3 fs-4 shadow-sm">
                                    <i class="feather-users"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @elseif($isManager)
                <!-- Manager KPIs -->
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('assign-luggage.index') }}" class="card-link-wrapper text-decoration-none">
                        <div class="card stretch stretch-full h-100 kpi-card hover-animate">
                            <div class="card-body d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <span class="fs-12 text-muted text-uppercase d-block mb-1">My Created Jobs</span>
                                    <h3 class="fw-bold mb-0 text-dark" id="dashboard-assignments-count">--</h3>
                                </div>
                                <div class="avatar-text avatar-md bg-soft-primary text-primary rounded p-3 fs-4 shadow-sm">
                                    <i class="feather-package"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card stretch stretch-full h-100 kpi-card">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <span class="fs-12 text-muted text-uppercase d-block mb-1">Flight Companies</span>
                                <h3 class="fw-bold mb-0 text-dark" id="dashboard-companies-count">--</h3>
                            </div>
                            <div class="avatar-text avatar-md bg-soft-info text-info rounded p-3 fs-4 shadow-sm">
                                <i class="feather-briefcase"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card stretch stretch-full h-100 kpi-card">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <span class="fs-12 text-muted text-uppercase d-block mb-1">Operating Stations</span>
                                <h3 class="fw-bold mb-0 text-dark" id="dashboard-stations-count">--</h3>
                            </div>
                            <div class="avatar-text avatar-md bg-soft-success text-success rounded p-3 fs-4 shadow-sm">
                                <i class="feather-map-pin"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('driver-activities.index') }}" class="card-link-wrapper text-decoration-none">
                        <div class="card stretch stretch-full h-100 kpi-card hover-animate">
                            <div class="card-body d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <span class="fs-12 text-muted text-uppercase d-block mb-1">Active Drivers</span>
                                    <h3 class="fw-bold mb-0 text-dark" id="dashboard-drivers-count">--</h3>
                                </div>
                                <div class="avatar-text avatar-md bg-soft-warning text-warning rounded p-3 fs-4 shadow-sm">
                                    <i class="feather-truck"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @else
                <!-- Driver KPIs -->
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('assignable-orders.index') }}" class="card-link-wrapper text-decoration-none">
                        <div class="card stretch stretch-full h-100 kpi-card hover-animate">
                            <div class="card-body d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <span class="fs-12 text-muted text-uppercase d-block mb-1">My Allocated Jobs</span>
                                    <h3 class="fw-bold mb-0 text-dark" id="dashboard-assignments-count">--</h3>
                                </div>
                                <div class="avatar-text avatar-md bg-soft-primary text-primary rounded p-3 fs-4 shadow-sm">
                                    <i class="feather-package"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card stretch stretch-full h-100 kpi-card">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <span class="fs-12 text-muted text-uppercase d-block mb-1">Pending Pickups</span>
                                <h3 class="fw-bold mb-0 text-dark" id="dashboard-pickup-count">--</h3>
                            </div>
                            <div class="avatar-text avatar-md bg-soft-warning text-warning rounded p-3 fs-4 shadow-sm">
                                <i class="feather-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card stretch stretch-full h-100 kpi-card">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <span class="fs-12 text-muted text-uppercase d-block mb-1">Completed Jobs</span>
                                <h3 class="fw-bold mb-0 text-dark" id="dashboard-delivered-count">--</h3>
                            </div>
                            <div class="avatar-text avatar-md bg-soft-success text-success rounded p-3 fs-4 shadow-sm">
                                <i class="feather-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card stretch stretch-full h-100 kpi-card">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <span class="fs-12 text-muted text-uppercase d-block mb-1">Travel Mileage</span>
                                <h3 class="fw-bold mb-0 text-dark"><span id="dashboard-total-distance">--</span> km</h3>
                                <span class="text-purple fs-10 d-block mt-0.5 d-none" id="dashboard-driver-avg-time"><i class="feather-clock me-0.5"></i>Avg Speed: <span id="dashboard-avg-time-hours">--</span>h</span>
                            </div>
                            <div class="avatar-text avatar-md bg-soft-purple text-purple rounded p-3 fs-4 shadow-sm">
                                <i class="feather-activity"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Row 2: Secondary Status Metrics (Only for Admin & Manager) -->
        @if(!$isDriver)
            <div class="row g-4 mb-4">
                <!-- Pending Pickup -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm d-flex flex-row align-items-center gap-3 p-4 h-100 metric-border-pickup">
                        <div class="avatar bg-soft-warning text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                            <i class="feather-clock fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark" id="dashboard-secondary-pickup-count">--</h4>
                            <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Pending Pickups</span>
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
                            <h4 class="fw-bold mb-0 text-dark" id="dashboard-in-progress-count">--</h4>
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
                            <h4 class="fw-bold mb-0 text-dark" id="dashboard-secondary-delivered-count">--</h4>
                            <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Delivered</span>
                        </div>
                    </div>
                </div>

                <!-- Total Distance Covered -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm d-flex flex-row align-items-center gap-3 p-4 h-100 metric-border-distance">
                        <div class="avatar bg-soft-purple text-purple rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                            <i class="feather-activity fs-4"></i>
                        </div>
                        <div class="w-100">
                            <h4 class="fw-bold mb-0 text-dark"><span id="dashboard-secondary-total-distance">--</span> km</h4>
                            <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Logged Mileage</span>
                            <span class="text-purple fs-10 fw-semibold d-block mt-0.5"><i class="feather-clock me-0.5"></i>Avg Speed: <span id="dashboard-secondary-avg-time-hours">--</span>h</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

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
                        <span class="fs-12 text-muted">Proportional status analysis of scheduled luggage assignments</span>
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
                        <h6 class="card-title text-dark fw-bold mb-0">
                            <i class="feather-package text-primary me-2"></i>
                            @if($isDriver)
                                My Active Job Allocations
                            @else
                                My Recent Luggage Bookings
                            @endif
                        </h6>
                        @if($isDriver)
                            <a href="{{ route('assignable-orders.index') }}" class="btn btn-sm btn-light-brand py-1 px-3 fs-11">View All Orders</a>
                        @else
                            <a href="{{ route('assign-luggage.index') }}" class="btn btn-sm btn-light-brand py-1 px-3 fs-11">View All Bookings</a>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 js-skip-dual-view" data-skip-dual-view="true">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Company</th>
                                        <th>Route</th>
                                        @if(!$isDriver)
                                            <th>Driver</th>
                                        @endif
                                        <th>Distance</th>
                                        <th>Status</th>
                                        <th class="pe-4 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="dashboard-recent-assignments">
                                    <tr>
                                        <td colspan="{{ $isDriver ? '6' : '7' }}" class="text-center text-muted py-5">
                                            <i class="feather-loader fs-2 text-primary mb-2 d-block"></i>
                                            Loading dashboard data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Console -->
            <div class="col-lg-4">
                <!-- Quick Controls -->
                <div class="card shadow-sm h-100">
                    <div class="card-header border-bottom py-3">
                        <h6 class="card-title text-dark fw-bold mb-0"><i class="feather-sliders text-primary me-2"></i>Quick Console</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-2">
                            @if($isAdmin)
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
                            @elseif($isManager)
                                <a href="{{ route('assign-luggage.create') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 hover-shadow">
                                    <i class="feather-plus-circle"></i> New Luggage Assignment
                                </a>
                                <a href="{{ route('driver-activities.index') }}" class="btn btn-light-brand w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="feather-activity"></i> Track Driver Activities
                                </a>
                                <a href="{{ route('reports.index') }}" class="btn btn-light-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="feather-bar-chart-2"></i> Operations Reports Center
                                </a>
                                <a href="{{ route('profile.edit') }}" class="btn btn-light w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="feather-user"></i> View My Profile Details
                                </a>
                            @else
                                <!-- Driver Console -->
                                <a href="{{ route('assignable-orders.index') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 hover-shadow">
                                    <i class="feather-truck"></i> My Assignable Orders
                                </a>
                                <a href="{{ route('reports.index') }}" class="btn btn-light-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="feather-bar-chart-2"></i> My Performance Reports
                                </a>
                                <a href="{{ route('profile.edit') }}" class="btn btn-light-brand w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="feather-user"></i> Edit Profile Details
                                </a>
                                <a href="{{ route('account-settings.edit') }}" class="btn btn-light w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="feather-settings"></i> Account Password Settings
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
@endsection

@section('modals')
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
<script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/vendors/js/circle-progress.min.js') }}"></script>
<script src="{{ asset('assets/js/dashboard.js') }}"></script>
@endpush
