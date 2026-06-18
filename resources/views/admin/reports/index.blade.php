@extends('layouts.admin')

@section('title', 'Operations Reports Center || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content d-print-none reports-page">
    <!-- Page Header (Screen) -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10 text-dark fw-bold">Logistics Reports Center</h5>
            </div>
            <ul class="breadcrumb d-none d-md-flex">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item">Operations Reports</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="exportReportCSV()" class="btn btn-light-brand d-flex align-items-center gap-1 fw-semibold">
                    <i class="feather-download-cloud"></i> Export Excel/CSV
                </button>
                <button type="button" onclick="window.print()" class="btn btn-primary d-flex align-items-center gap-1 fw-semibold">
                    <i class="feather-file-text"></i> Print / Save PDF
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- Professional Reports Filters Card -->
        <div class="card stretch stretch-full filter-card mb-4">
            <div class="card-header border-bottom-0 pb-0">
                <h6 class="text-uppercase text-primary fw-bold fs-11 mb-0"><i class="feather-sliders me-2"></i>Report Configuration</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('reports.index') }}" method="GET" id="report-filter-form">
                    <div class="row g-3">
                        <!-- Date Scope Preset Selector -->
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label fw-bold text-dark fs-12">Reporting Scope</label>
                            <select name="date_preset" id="report-date-preset" class="form-select select2-select">
                                <option value="today" {{ request('date_preset') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="weekly" {{ request('date_preset') == 'weekly' ? 'selected' : '' }}>Weekly (7 Days)</option>
                                <option value="monthly" {{ request('date_preset') == 'monthly' ? 'selected' : '' }}>Monthly (30 Days)</option>
                                <option value="yearly" {{ request('date_preset') == 'yearly' ? 'selected' : '' }}>Yearly (This Year)</option>
                                <option value="custom" {{ request('date_preset') == 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                            </select>
                        </div>

                        <!-- Custom Date Range picker (Toggled by Preset) -->
                        <div class="col-sm-6 col-md-4 col-lg-3" id="custom-date-container" style="display: {{ request('date_preset') == 'custom' ? 'block' : 'none' }};">
                            <label class="form-label fw-bold text-dark fs-12">Custom Date Range</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="feather-calendar"></i></span>
                                <input type="text" name="date_range" id="report-date-range" class="form-control" value="{{ request('date_range') }}">
                            </div>
                        </div>

                        <!-- Company Select -->
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label fw-bold text-dark fs-12">Flight Company</label>
                            <select name="company_id" class="form-select select2-select">
                                <option value="">All Companies</option>
                                @foreach($companiesList as $c)
                                    <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Station Select -->
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label fw-bold text-dark fs-12">Pickup Station</label>
                            <select name="station_id" class="form-select select2-select">
                                <option value="">All Stations</option>
                                @foreach($stationsList as $s)
                                    <option value="{{ $s->id }}" {{ request('station_id') == $s->id ? 'selected' : '' }}>{{ $s->station_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Manager Select -->
                        @if($isAdmin)
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label fw-bold text-dark fs-12">Manager</label>
                            <select name="manager_id" class="form-select select2-select">
                                <option value="">All Managers</option>
                                @foreach($managersList as $m)
                                    <option value="{{ $m->id }}" {{ request('manager_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Driver Select -->
                        @if(!$isDriver)
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label fw-bold text-dark fs-12">Driver</label>
                            <select name="driver_id" class="form-select select2-select">
                                <option value="">All Drivers</option>
                                @foreach($driversList as $d)
                                    <option value="{{ $d->id }}" {{ request('driver_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Status Select -->
                        <div class="col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label fw-bold text-dark fs-12">Status</label>
                            <select name="status" class="form-select select2-select">
                                <option value="">All Statuses</option>
                                <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Pickup" {{ request('status') === 'Pickup' ? 'selected' : '' }}>Pickup</option>
                                <option value="Delivered" {{ request('status') === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                            </select>
                        </div>

                        <!-- Action Buttons (Expanded to resolve reload overflow issues) -->
                        <div class="col-sm-6 col-md-4 col-lg-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary flex-fill py-2 d-flex align-items-center justify-content-center gap-1 font-weight-bold">
                                <i class="feather-check"></i> Filter
                            </button>
                            <a href="{{ route('reports.index') }}" class="btn btn-light py-2 px-3 d-flex align-items-center justify-content-center" title="Reset Filters"><i class="feather-rotate-ccw"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabbed Reporting Navigation -->
        <ul class="nav nav-tabs card-header-tabs border-bottom mb-4" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold px-4" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-pane" type="button" role="tab" aria-controls="overview-pane" aria-selected="true">
                    <i class="feather-grid me-2 text-primary"></i>{{ $isDriver ? 'My Summary' : 'Executive Summary' }}
                </button>
            </li>
            @if(!$isDriver)
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4" id="team-tab" data-bs-toggle="tab" data-bs-target="#team-pane" type="button" role="tab" aria-controls="team-pane" aria-selected="false">
                    <i class="feather-users me-2 text-primary"></i>Team Performance
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4" id="entities-tab" data-bs-toggle="tab" data-bs-target="#entities-pane" type="button" role="tab" aria-controls="entities-pane" aria-selected="false">
                    <i class="feather-map me-2 text-primary"></i>Companies & Stations
                </button>
            </li>
            @endif
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-pane" type="button" role="tab" aria-controls="logs-pane" aria-selected="false">
                    <i class="feather-file-text me-2 text-primary"></i>{{ $isDriver ? 'My Deliveries Log' : 'Detailed Audit Log' }}
                </button>
            </li>
        </ul>

        <!-- Tab Contents -->
        <div class="tab-content" id="reportTabsContent">
            <!-- TAB 1: EXECUTIVE SUMMARY -->
            <div class="tab-pane fade show active" id="overview-pane" role="tabpanel" aria-labelledby="overview-tab">
                <!-- Uniform Metric Cards Grid (Equal Heights) -->
                <div class="row row-deck g-4 mb-4">
                    <!-- Total Volume -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm d-flex flex-row align-items-center gap-3 p-4 h-100" style="border-left: 4px solid #3b82f6 !important;">
                            <div class="avatar bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="feather-package fs-4"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1 text-dark">{{ $totalAssignments }}</h4>
                                <span class="text-muted fs-12 text-uppercase fw-semibold">Total Assignments</span>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Volume -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm d-flex flex-row align-items-center gap-3 p-4 h-100" style="border-left: 4px solid #10b981 !important;">
                            <div class="avatar bg-soft-success text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="feather-check-circle fs-4"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1 text-dark">{{ $deliveredCount }}</h4>
                                <span class="text-muted fs-12 text-uppercase fw-semibold">Delivered Volume</span>
                            </div>
                        </div>
                    </div>

                    <!-- Active In-Transit -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm d-flex flex-row align-items-center gap-3 p-4 h-100" style="border-left: 4px solid #f59e0b !important;">
                            <div class="avatar bg-soft-warning text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="feather-truck fs-4"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1 text-dark">{{ $inProgressCount + $pickupCount }}</h4>
                                <span class="text-muted fs-12 text-uppercase fw-semibold">Active In-Transit</span>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery speed / performance metric -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm d-flex flex-row align-items-center gap-3 p-4 h-100" style="border-left: 4px solid #8b5cf6 !important;">
                            <div class="avatar bg-soft-purple text-purple rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="feather-clock fs-4"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1 text-dark">{{ $avgTimeHours }} hrs</h4>
                                <span class="text-muted fs-12 text-uppercase fw-semibold">Avg Delivery Speed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logistics Business Intelligence Charts -->
                <div class="row g-4 mb-4">
                    <!-- Daily operational Activity Chart (Stacked status breakdown) -->
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header border-0 pb-0">
                                <h6 class="card-title text-dark fw-bold mb-0">Daily Operational Activity Tracker</h6>
                                <span class="fs-12 text-muted">Tracking daily volume flows by status breakdowns</span>
                            </div>
                            <div class="card-body">
                                <div id="operational-activity-chart"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Distribution Donut -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header border-0 pb-0">
                                <h6 class="card-title text-dark fw-bold mb-0">Operational Status Ratio</h6>
                                <span class="fs-12 text-muted">Proportional status analysis</span>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <div id="status-donut-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: TEAM PERFORMANCE -->
            @if(!$isDriver)
            <div class="tab-pane fade" id="team-pane" role="tabpanel" aria-labelledby="team-tab">
                <div class="row g-4">
                    @if($isAdmin)
                    <!-- Manager-wise assignments -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="card-title text-dark fw-bold mb-0"><i class="feather-shield text-primary me-2"></i>Manager-wise Order Creation</h6>
                                <span class="badge bg-soft-primary text-primary">{{ $managerWise->count() }} Managers</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Manager Name</th>
                                                <th class="text-center">Orders Created</th>
                                                <th class="text-end">Total Assigned Distance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($managerWise as $mw)
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $mw['name'] }}</td>
                                                    <td class="text-center"><span class="badge bg-soft-primary text-primary px-3 py-1.5">{{ $mw['count'] }}</span></td>
                                                    <td class="text-end text-dark fw-medium">{{ $mw['distance'] }} km</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4">No manager data found for this range</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Driver performance -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="card-title text-dark fw-bold mb-0"><i class="feather-truck text-primary me-2"></i>Driver Logistics Performance</h6>
                                <span class="badge bg-soft-success text-success">{{ $driverWise->count() }} Drivers</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Driver Name</th>
                                                <th class="text-center">Total Orders</th>
                                                <th class="text-center">Delivered</th>
                                                <th class="text-center">Completion Rate</th>
                                                <th class="text-end">Distance Travelled</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($driverWise as $dw)
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $dw['name'] }}</td>
                                                    <td class="text-center"><span class="badge bg-light text-dark px-2.5 py-1">{{ $dw['total'] }}</span></td>
                                                    <td class="text-center"><span class="badge bg-soft-success text-success px-2.5 py-1">{{ $dw['completed'] }}</span></td>
                                                    <td class="text-center font-weight-bold text-dark">{{ $dw['rate'] }}%</td>
                                                    <td class="text-end text-dark fw-medium">{{ $dw['distance'] }} km</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No driver data found for this range</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Driver performance (Full-width for Manager view) -->
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="card-title text-dark fw-bold mb-0"><i class="feather-truck text-primary me-2"></i>Driver Logistics Performance</h6>
                                <span class="badge bg-soft-success text-success">{{ $driverWise->count() }} Drivers</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Driver Name</th>
                                                <th class="text-center">Total Orders</th>
                                                <th class="text-center">Delivered</th>
                                                <th class="text-center">Completion Rate</th>
                                                <th class="text-end">Distance Travelled</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($driverWise as $dw)
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $dw['name'] }}</td>
                                                    <td class="text-center"><span class="badge bg-light text-dark px-2.5 py-1">{{ $dw['total'] }}</span></td>
                                                    <td class="text-center"><span class="badge bg-soft-success text-success px-2.5 py-1">{{ $dw['completed'] }}</span></td>
                                                    <td class="text-center font-weight-bold text-dark">{{ $dw['rate'] }}%</td>
                                                    <td class="text-end text-dark fw-medium">{{ $dw['distance'] }} km</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No driver data found for this range</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- TAB 3: COMPANIES & STATIONS -->
            @if(!$isDriver)
            <div class="tab-pane fade" id="entities-pane" role="tabpanel" aria-labelledby="entities-tab">
                <!-- Companies and Stations Charts / lists -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h6 class="card-title text-dark fw-bold mb-0">Flight Companies Traffic Comparison</h6>
                                <span class="fs-12 text-muted">Comparison analysis of volume load per aviation agency</span>
                            </div>
                            <div class="card-body">
                                <div id="company-volume-chart"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center">
                                <div class="avatar bg-soft-info text-info rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                    <i class="feather-navigation fs-1"></i>
                                </div>
                                <h4 class="fw-bold text-dark mb-1">{{ $totalDistance }} km</h4>
                                <p class="text-muted fs-12 text-uppercase fw-semibold mb-3">Operational Fleet Distance</p>
                                <div class="border-top pt-3 w-100 text-start">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted fs-12">Delivered Orders Distance</span>
                                        <span class="fw-bold text-dark fs-12">{{ round($assignments->where('status', 'Delivered')->sum('distance_km'), 2) }} km</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted fs-12">Average Distance/Order</span>
                                        <span class="fw-bold text-dark fs-12">{{ $totalAssignments > 0 ? round($totalDistance / $totalAssignments, 1) : 0 }} km</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Company list details -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h6 class="card-title text-dark fw-bold mb-0"><i class="feather-briefcase text-primary me-2"></i>Flight Company-wise Volume</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Company Name</th>
                                                <th class="text-center">Assignments count</th>
                                                <th class="text-end">Cumulated Distance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($companyWise as $cw)
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $cw['name'] }}</td>
                                                    <td class="text-center"><span class="badge bg-soft-primary text-primary px-3 py-1.5">{{ $cw['count'] }}</span></td>
                                                    <td class="text-end text-dark fw-medium">{{ $cw['distance'] }} km</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4">No company records available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Station list details -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h6 class="card-title text-dark fw-bold mb-0"><i class="feather-map-pin text-primary me-2"></i>Station Transit Traffic</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Station Name</th>
                                                <th class="text-center">Transit Volume</th>
                                                <th class="text-end">Logged Outbound Distance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($stationWise as $sw)
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $sw['name'] }}</td>
                                                    <td class="text-center"><span class="badge bg-soft-info text-info px-3 py-1.5">{{ $sw['count'] }}</span></td>
                                                    <td class="text-end text-dark fw-medium">{{ $sw['distance'] }} km</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4">No station records available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- TAB 4: DETAILED AUDIT LOG -->
            <div class="tab-pane fade" id="logs-pane" role="tabpanel" aria-labelledby="logs-tab">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title text-dark fw-bold mb-0">Detailed Assignments Log</h6>
                        <span class="fs-12 text-muted">Showing {{ $assignments->firstItem() ?? 0 }}-{{ $assignments->lastItem() ?? 0 }} of {{ $assignments->total() }} records</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ref ID</th>
                                        <th>Created At</th>
                                        <th>Flight Company</th>
                                        <th>Logistics Route</th>
                                        <th>Manager</th>
                                        <th>Assigned Driver</th>
                                        <th>Distance</th>
                                        <th>Status</th>
                                        <th>Proof</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($assignments as $row)
                                        <tr>
                                            <td class="fw-semibold text-dark">#{{ $row->id }}</td>
                                            <td>{{ $row->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <span class="fw-semibold text-dark">{{ $row->company ? $row->company->company_name : 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fs-12 text-dark"><i class="feather-map-pin text-primary me-1 fs-10"></i>{{ $row->station ? $row->station->station_name : 'N/A' }}</span>
                                                    <span class="fs-11 text-muted"><i class="feather-arrow-right text-muted me-1 fs-10"></i>{{ $row->drop_location }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-dark">{{ $row->creator ? $row->creator->name : 'System/Admin' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-dark">{{ $row->driver ? $row->driver->name : 'Unassigned' }}</span>
                                            </td>
                                            <td>{{ $row->distance_km }} km</td>
                                            <td>
                                                @if($row->status === 'Delivered')
                                                    <span class="badge bg-soft-success text-success px-2.5 py-1 fs-11">Delivered</span>
                                                @elseif($row->status === 'Pickup')
                                                    <span class="badge bg-soft-warning text-warning px-2.5 py-1 fs-11">Pickup</span>
                                                @else
                                                    <span class="badge bg-soft-primary text-primary px-2.5 py-1 fs-11">In Progress</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($row->delivery_proof_images && count($row->delivery_proof_images) > 0)
                                                    <span class="badge bg-light text-success fs-10" title="Proof uploaded"><i class="feather-image me-1"></i>Yes</span>
                                                @else
                                                    <span class="badge bg-light text-muted fs-10">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-5">
                                                <i class="feather-alert-triangle fs-2 text-warning mb-2 d-block"></i>
                                                No luggage assignments found matching selected filters.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Pagination Footer -->
                    @if($assignments->hasPages())
                        <div class="card-footer d-flex justify-content-end py-3">
                            {{ $assignments->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     DEDICATED PRINTABLE REPORT CONTAINER (Only visible in Print/PDF Mode)
     ========================================================================== -->
<div id="printable-report-area" class="d-none">
    <!-- Header Branding -->
    <div class="print-branding border-bottom pb-4 mb-4">
        <div class="row align-items-center">
            <div class="col-6">
                <h1 class="print-logo fw-bold mb-1 text-primary">{{ $appSettings->application_name ?? 'Wings' }}</h1>
                <p class="text-uppercase text-muted fs-10 letter-spacing-1 mb-0">Logistics & Operations Management</p>
            </div>
            <div class="col-6 text-end">
                <h3 class="fw-bold mb-1 text-dark">{{ $isDriver ? 'DRIVER ACTIVITY REPORT' : 'MANAGEMENT REPORT' }}</h3>
                <p class="mb-0 text-muted fs-11">Date Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
                <p class="mb-0 text-muted fs-11">{{ $isDriver ? 'Driver' : 'Officer / Creator' }}: {{ $loggedUser->name }}</p>
            </div>
        </div>
    </div>

    <!-- Metadata Grid -->
    <div class="card p-3 mb-4 bg-light border-0">
        <h6 class="text-uppercase text-primary fw-bold fs-11 border-bottom pb-1 mb-2">Report Constraints & Metadata</h6>
        <table class="table table-sm table-borderless mb-0 fs-11">
            <tr>
                <td width="15%" class="fw-bold">Date Preset Scope:</td>
                <td width="35%">{{ ucfirst($datePreset) }}</td>
                <td width="15%" class="fw-bold">Date Range Span:</td>
                <td width="35%">{{ request('date_range') }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Flight Company:</td>
                <td>{{ $selectedCompanyName }}</td>
                <td class="fw-bold">Transit Station:</td>
                <td>{{ $selectedStationName }}</td>
            </tr>
            @if(!$isDriver)
            <tr>
                <td class="fw-bold">Staff Manager:</td>
                <td>{{ $selectedDriverManagerName }}</td>
                <td class="fw-bold">Assigned Driver:</td>
                <td>{{ $selectedDriverName }}</td>
            </tr>
            @else
            <tr>
                <td class="fw-bold">Assigned Driver:</td>
                <td colspan="3">{{ $selectedDriverName }}</td>
            </tr>
            @endif
            <tr>
                <td class="fw-bold">Logistics Status:</td>
                <td colspan="3">{{ request('status') ?: 'All Statuses' }}</td>
            </tr>
        </table>
    </div>

    <!-- Section 1: Executive KPI Metrics Table -->
    <h5 class="fw-bold text-dark mb-2 border-bottom pb-1">1. Executive Summaries</h5>
    <table class="table table-bordered align-middle mb-4 print-kpi-table fs-11">
        <thead class="table-light">
            <tr>
                <th width="35%">Logistics Performance Indicator</th>
                <th width="20%" class="text-center">Aggregated Count</th>
                <th width="15%" class="text-center">Unit</th>
                <th width="30%">Operational Significance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="fw-bold">Total Luggage Assignments</td>
                <td class="text-center fw-bold text-primary">{{ $totalAssignments }}</td>
                <td class="text-center">orders</td>
                <td>Cumulative logistics tasks scheduled</td>
            </tr>
            <tr>
                <td class="fw-bold">Delivered / Completed orders</td>
                <td class="text-center fw-bold text-success">{{ $deliveredCount }}</td>
                <td class="text-center">orders</td>
                <td>Successfully cleared target luggage consignments</td>
            </tr>
            <tr>
                <td class="fw-bold">Active In-Transit Logistics</td>
                <td class="text-center fw-bold text-warning">{{ $inProgressCount + $pickupCount }}</td>
                <td class="text-center">orders</td>
                <td>Orders currently in movement or waiting pickup</td>
            </tr>
            <tr>
                <td class="fw-bold">Total Logged Distance</td>
                <td class="text-center fw-bold text-info">{{ $totalDistance }}</td>
                <td class="text-center">km</td>
                <td>Outbound fleet mileage recorded</td>
            </tr>
            <tr>
                <td class="fw-bold">Average Delivery Speed Duration</td>
                <td class="text-center fw-bold text-purple">{{ $avgTimeHours }}</td>
                <td class="text-center">hours</td>
                <td>Average duration from assignment to proof submission</td>
            </tr>
        </tbody>
    </table>

    @if(!$isDriver)
    <div class="page-break-print"></div>

    <!-- Section 2: Management Breakdowns -->
    <h5 class="fw-bold text-dark mb-2 border-bottom pb-1">2. Team Performance Breakdowns</h5>
    
    @if($isAdmin)
    <h6 class="fw-bold text-muted mb-2 mt-3">Manager-wise Order Creation</h6>
    <table class="table table-sm table-bordered mb-4 fs-11">
        <thead class="table-light">
            <tr>
                <th>Manager Name</th>
                <th class="text-center">Orders Created</th>
                <th class="text-end">Total Assigned Distance (km)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($managerWise as $mw)
                <tr>
                    <td class="fw-semibold text-dark">{{ $mw['name'] }}</td>
                    <td class="text-center">{{ $mw['count'] }}</td>
                    <td class="text-end">{{ $mw['distance'] }} km</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-2">No manager data found for this range</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <h6 class="fw-bold text-muted mb-2">Driver Logistics Efficiency</h6>
    <table class="table table-sm table-bordered mb-4 fs-11">
        <thead class="table-light">
            <tr>
                <th>Driver Name</th>
                <th class="text-center">Total Orders</th>
                <th class="text-center">Completed</th>
                <th class="text-center">Completion Rate</th>
                <th class="text-end">Distance Travelled</th>
            </tr>
        </thead>
        <tbody>
            @forelse($driverWise as $dw)
                <tr>
                    <td class="fw-semibold text-dark">{{ $dw['name'] }}</td>
                    <td class="text-center">{{ $dw['total'] }}</td>
                    <td class="text-center">{{ $dw['completed'] }}</td>
                    <td class="text-center fw-bold">{{ $dw['rate'] }}%</td>
                    <td class="text-end">{{ $dw['distance'] }} km</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-2">No driver data found for this range</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h5 class="fw-bold text-dark mb-2 border-bottom pb-1 mt-4">3. Corporate Volume Rankings</h5>
    <div class="row">
        <div class="col-6">
            <h6 class="fw-bold text-muted mb-2">Flight Company Vol.</h6>
            <table class="table table-sm table-bordered fs-11">
                <thead class="table-light">
                    <tr>
                        <th>Company</th>
                        <th class="text-center">Count</th>
                        <th class="text-end">Distance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companyWise as $cw)
                        <tr>
                            <td class="fw-semibold">{{ $cw['name'] }}</td>
                            <td class="text-center">{{ $cw['count'] }}</td>
                            <td class="text-end">{{ $cw['distance'] }} km</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No company records</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="col-6">
            <h6 class="fw-bold text-muted mb-2">Station Transit Traffic</h6>
            <table class="table table-sm table-bordered fs-11">
                <thead class="table-light">
                    <tr>
                        <th>Station</th>
                        <th class="text-center">Count</th>
                        <th class="text-end">Distance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stationWise as $sw)
                        <tr>
                            <td class="fw-semibold">{{ $sw['name'] }}</td>
                            <td class="text-center">{{ $sw['count'] }}</td>
                            <td class="text-end">{{ $sw['distance'] }} km</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No station records</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="page-break-print"></div>

    <!-- Section 3: Detailed Auditable Log of Assignments -->
    <h5 class="fw-bold text-dark mb-2 border-bottom pb-1">{{ $isDriver ? '2. Detailed Auditable Log of Deliveries' : '4. Detailed Auditable Log of Assignments' }}</h5>
    <table class="table table-bordered table-sm align-middle fs-9">
        <thead class="table-light">
            <tr>
                <th>Ref ID</th>
                <th>Created Date</th>
                <th>Company</th>
                <th>Pickup Station</th>
                <th>Dropoff Location</th>
                <th>Manager</th>
                <th>Driver</th>
                <th>Distance</th>
                <th>Status</th>
                <th>Delivered At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignmentsForPrint as $row)
                <tr>
                    <td class="fw-semibold text-dark">#{{ $row->id }}</td>
                    <td>{{ $row->created_at->format('Y-m-d H:i') }}</td>
                    <td class="fw-medium">{{ $row->company ? $row->company->company_name : 'N/A' }}</td>
                    <td>{{ $row->station ? $row->station->station_name : 'N/A' }}</td>
                    <td>{{ $row->drop_location }}</td>
                    <td>{{ $row->creator ? $row->creator->name : 'System/Admin' }}</td>
                    <td>{{ $row->driver ? $row->driver->name : 'Unassigned' }}</td>
                    <td>{{ $row->distance_km }} km</td>
                    <td>{{ $row->status }}</td>
                    <td>{{ $row->delivered_at ? $row->delivered_at->format('Y-m-d H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">No assignments matched filter criteria</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Block -->
    <div class="print-signatures mt-5 pt-5">
        <div class="row">
            <div class="col-6">
                <div class="signature-line" style="border-top: 1px solid #94a3b8; width: 80%; padding-top: 5px; margin-top: 30px;">
                    <p class="mb-0 fw-bold fs-11 text-dark">Report Prepared By</p>
                    <p class="text-muted fs-10 mb-0">Officer Name: {{ $loggedUser->name }}</p>
                    <p class="text-muted fs-10 mb-0">Signature Date: ___________________</p>
                </div>
            </div>
            <div class="col-6 text-end d-flex flex-column align-items-end">
                <div class="signature-line text-start" style="border-top: 1px solid #94a3b8; width: 80%; padding-top: 5px; margin-top: 30px;">
                    <p class="mb-0 fw-bold fs-11 text-dark">Management Approval</p>
                    <p class="text-muted fs-10 mb-0">Approving Signature: ___________________</p>
                    <p class="text-muted fs-10 mb-0">Signature Date: ___________________</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<!-- Include Select2 CSS inside Head stack -->
<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/select2.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/select2-theme.min.css') }}">

<style>
    /* Styling for print template and screen display overrides */
    @media screen {
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            height: 40px;
            display: flex;
            align-items: center;
        }
        
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #0f172a;
            padding-left: 12px;
        }

        /* Support dark skin for Select2 */
        html.app-skin-dark .select2-container--bootstrap-5 .select2-selection {
            background-color: #0f172a !important;
            border: 1px solid #475569 !important;
        }
        
        html.app-skin-dark .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #f8fafc !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            z-index: 1050;
        }
        
        html.app-skin-dark .select2-container--bootstrap-5 .select2-dropdown {
            background-color: #1e293b !important;
            border: 1px solid #334155 !important;
        }
        
        html.app-skin-dark .select2-container--bootstrap-5 .select2-results__option {
            color: #cbd5e1 !important;
        }
        
        html.app-skin-dark .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #3b82f6 !important;
            color: #ffffff !important;
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: #64748b;
            background: transparent;
            padding: 12px 20px;
            transition: all 0.25s ease;
        }

        .nav-tabs .nav-link:hover {
            color: #3b82f6;
            border-bottom: 2px solid rgba(59, 130, 246, 0.2);
        }

        .nav-tabs .nav-link.active {
            color: #3b82f6;
            border-bottom: 2px solid #3b82f6;
            background: transparent;
        }
        
        html.app-skin-dark .nav-tabs .nav-link {
            color: #94a3b8;
        }
        html.app-skin-dark .nav-tabs .nav-link:hover {
            color: #60a5fa;
        }
        html.app-skin-dark .nav-tabs .nav-link.active {
            color: #60a5fa;
            border-bottom: 2px solid #60a5fa;
        }
        
        /* Table enhancements */
        .table-responsive {
            border-radius: 0 0 8px 8px;
        }
    }

    /* Professional Print Layout Overrides */
    @media print {
        /* Hide entire web layouts, dashboard elements, filters, customizers */
        body {
            background-color: #ffffff !important;
            color: #000000 !important;
            font-family: 'Times New Roman', Times, Georgia, serif !important;
            font-size: 11px !important;
        }
        
        .nxl-navigation,
        .nxl-header,
        .footer,
        .theme-customizer,
        .customizer-handle,
        .page-header,
        .filter-card,
        .nav-tabs,
        .tab-content,
        .d-print-none {
            display: none !important;
        }

        /* Show dedicated print container */
        #printable-report-area {
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .nxl-container {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .main-content {
            padding: 0 !important;
        }

        /* Formal Border and Tables styling */
        .table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-bottom: 20px !important;
        }
        
        .table th {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            font-weight: bold !important;
            border: 1px solid #94a3b8 !important;
            padding: 6px 8px !important;
        }
        
        .table td {
            border: 1px solid #cbd5e1 !important;
            padding: 6px 8px !important;
            color: #000000 !important;
        }
        
        .print-kpi-table th {
            background-color: #e2e8f0 !important;
        }

        .page-break-print {
            page-break-before: always !important;
            break-before: page !important;
            height: 0;
            margin: 0;
            border: 0;
        }
        
        /* Font scales for print detailed table */
        .fs-9 {
            font-size: 9px !important;
        }
    }
</style>
@endpush

@push('scripts')
<!-- Include Select2 JS script -->
<script src="{{ asset('assets/vendors/js/select2.min.js') }}"></script>

<script>
    $(document).ready(function() {
        // Initialize Date Range Picker
        $('#report-date-range').daterangepicker({
            autoUpdateInput: true,
            locale: {
                format: 'MM/DD/YYYY',
                cancelLabel: 'Clear'
            }
        });

        // Initialize Select2 selectors (Professional bootstrap-5 themed dropdowns)
        $('.select2-select').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('.filter-card')
        });

        // Toggle custom date range container based on presets
        $('#report-date-preset').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#custom-date-container').slideDown(200);
            } else {
                $('#custom-date-container').slideUp(200);
            }
        });

        // Redesigned Chart 1: Daily Operational activity (Stacked bar chart)
        var opsOptions = {
            series: [{
                name: 'In Progress',
                data: @json(collect($dailyTrend)->pluck('in_progress'))
            }, {
                name: 'Picked Up',
                data: @json(collect($dailyTrend)->pluck('pickup'))
            }, {
                name: 'Delivered',
                data: @json(collect($dailyTrend)->pluck('delivered'))
            }],
            chart: {
                type: 'bar',
                height: 320,
                stacked: true,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 4
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 1,
                colors: ['transparent']
            },
            colors: ['#3b82f6', '#f59e0b', '#10b981'], // Premium Status Colors
            xaxis: {
                categories: @json(collect($dailyTrend)->pluck('date')),
                labels: {
                    rotate: -45,
                    style: { fontSize: '10px' }
                }
            },
            yaxis: {
                title: { text: 'Luggage Count' },
                tickAmount: 5,
                labels: {
                    formatter: function(val) { return Math.round(val); }
                }
            },
            fill: { opacity: 1 },
            legend: {
                position: 'bottom',
                fontFamily: 'Inter, sans-serif'
            }
        };

        var opsChart = new ApexCharts(document.querySelector("#operational-activity-chart"), opsOptions);
        opsChart.render();

        // Redesigned Chart 2: Corporate Luggage volume comparison (Horizontal bar chart)
        @if(!$isDriver)
        var companyOptions = {
            series: [{
                name: 'Luggage Volume',
                data: @json($companyWise->pluck('count'))
            }],
            chart: {
                type: 'bar',
                height: 300,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '55%',
                    borderRadius: 4
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val + " orders";
                },
                style: {
                    colors: ['#ffffff'],
                    fontSize: '11px'
                }
            },
            colors: ['#6366f1'], // Indigo theme color
            xaxis: {
                categories: @json($companyWise->pluck('name')),
                labels: {
                    style: { fontSize: '10px' }
                }
            },
            grid: {
                xaxis: { lines: { show: true } }
            }
        };

        var companyChart = new ApexCharts(document.querySelector("#company-volume-chart"), companyOptions);
        companyChart.render();
        @endif

        // Redesigned Chart 3: Proportional Status Donut
        var donutOptions = {
            series: [
                {{ $statusDistribution['In Progress'] }}, 
                {{ $statusDistribution['Pickup'] }}, 
                {{ $statusDistribution['Delivered'] }}
            ],
            chart: {
                type: 'donut',
                height: 320,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false }
            },
            labels: ['In Progress', 'Picked Up', 'Delivered'],
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
                                    return {{ $totalAssignments }};
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

        var donutChart = new ApexCharts(document.querySelector("#status-donut-chart"), donutOptions);
        donutChart.render();
    });

    // Excel CSV Export Trigger
    function exportReportCSV() {
        var queryParams = $('#report-filter-form').serialize();
        var exportUrl = "{{ route('reports.index') }}?" + queryParams + "&export=csv";
        window.location.href = exportUrl;
    }
</script>
@endpush
