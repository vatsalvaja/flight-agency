
@extends('layouts.admin')

@section('title', 'Driver Activities || ' . ($appSettings->application_name ?? 'Wings'))

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="nxl-content">
    <div class="main-content py-4">
        <div class="container-fluid px-4">
            
            <!-- Breadcrumbs and Header -->
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div>
                    <h4 class="fw-extrabold text-dark mb-1">Driver Activities</h4>
                    <span class="fs-12 text-muted fw-semibold">Monitor real-time driver delivery activities and proof logs</span>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Driver Activities</li>
                    </ol>
                </nav>
            </div>

            <!-- KPI Metric Widget Row -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-4">
                <!-- Total Orders Assigned -->
                <div class="col">
                    <div class="card border border-gray-3 shadow-sm mb-0 rounded-3 p-3 kpi-total">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-10 text-muted d-block text-uppercase fw-bold mb-0.5">Total Assigned</span>
                                <span class="fs-20 fw-extrabold text-dark">{{ $kpis['total'] }}</span>
                            </div>
                            <div class="bg-soft-secondary text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="feather-grid fs-14"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- In Transit (In Progress) -->
                <div class="col">
                    <div class="card border border-gray-3 shadow-sm mb-0 rounded-3 p-3 kpi-transit">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-10 text-muted d-block text-uppercase fw-bold mb-0.5">In Transit</span>
                                <span class="fs-20 fw-extrabold text-primary">{{ $kpis['transit'] }}</span>
                            </div>
                            <div class="bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="feather-navigation fs-14"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Pickup -->
                <div class="col">
                    <div class="card border border-gray-3 shadow-sm mb-0 rounded-3 p-3 kpi-pickup">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-10 text-muted d-block text-uppercase fw-bold mb-0.5">At Pickup</span>
                                <span class="fs-20 fw-extrabold text-warning">{{ $kpis['pickup'] }}</span>
                            </div>
                            <div class="bg-soft-warning text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="feather-package fs-14"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Delivered -->
                <div class="col">
                    <div class="card border border-gray-3 shadow-sm mb-0 rounded-3 p-3 kpi-delivered">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-10 text-muted d-block text-uppercase fw-bold mb-0.5">Delivered</span>
                                <span class="fs-20 fw-extrabold text-success">{{ $kpis['delivered'] }}</span>
                            </div>
                            <div class="bg-soft-success text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="feather-check fs-14"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Filters Container -->
            <div class="card border border-gray-3 shadow-sm mb-4 filter-card" style="border-radius: 12px;">
                <div class="card-body p-3.5">
                    <form action="{{ route('driver-activities.index') }}" method="GET" class="row g-3 align-items-center">
                        <!-- Search term input -->
                        <div class="col-12 col-md-3 col-lg-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="feather-search fs-13"></i></span>
                                <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0 fs-12.5" placeholder="Search locations, driver or airline...">
                            </div>
                        </div>
                        
                        <!-- Driver dropdown selector -->
                        <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                            <select name="driver_id" class="form-select select2-select fs-12.5">
                                <option value="">All Drivers</option>
                                @foreach($drivers as $d)
                                    <option value="{{ $d->id }}" {{ $driverId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status filter selector -->
                        <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                            <select name="status" class="form-select select2-select fs-12.5">
                                <option value="">All Statuses</option>
                                <option value="In Progress" {{ $status === 'In Progress' ? 'selected' : '' }}>In Transit</option>
                                <option value="Pickup" {{ $status === 'Pickup' ? 'selected' : '' }}>Pickup</option>
                                <option value="Delivered" {{ $status === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                            </select>
                        </div>

                        <!-- Filter action buttons -->
                        <div class="col-12 col-md-3 col-lg-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill fs-12 fw-bold text-white text-nowrap"><i class="feather-filter me-1"></i> Filter</button>
                            <a href="{{ route('driver-activities.index') }}" class="btn btn-light border flex-fill fs-12 fw-bold text-nowrap"><i class="feather-rotate-ccw me-1"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            @php
                $trackingPayload = $liveTrackingAssignments->map(function ($assignment) use ($currentLocations) {
                    $location = $currentLocations->get($assignment->driver_id);

                    return [
                        'id' => $assignment->id,
                        'order_number' => '#ORD-' . str_pad($assignment->id, 5, '0', STR_PAD_LEFT),
                        'driver' => [
                            'name' => optional($assignment->driver)->name ?? 'Unassigned',
                            'email' => optional($assignment->driver)->email ?? 'N/A',
                            'initials' => $assignment->driver ? $assignment->driver->getInitials() : 'UN',
                            'photo' => ($assignment->driver && $assignment->driver->profile_photo) ? asset($assignment->driver->profile_photo) : null,
                        ],
                        'company' => optional($assignment->company)->company_name ?? 'N/A',
                        'pickup' => [
                            'address' => $assignment->pickup_location,
                            'lat' => $assignment->pickup_latitude !== null ? (float) $assignment->pickup_latitude : null,
                            'lng' => $assignment->pickup_longitude !== null ? (float) $assignment->pickup_longitude : null,
                        ],
                        'destination' => [
                            'address' => $assignment->drop_location,
                            'lat' => $assignment->drop_latitude !== null ? (float) $assignment->drop_latitude : null,
                            'lng' => $assignment->drop_longitude !== null ? (float) $assignment->drop_longitude : null,
                        ],
                        'last_location' => $location ? [
                            'lat' => (float) $location->latitude,
                            'lng' => (float) $location->longitude,
                            'speed' => $location->speed !== null ? (float) $location->speed : null,
                            'heading' => $location->heading !== null ? (float) $location->heading : null,
                            'battery_level' => $location->battery_level !== null ? (int) $location->battery_level : null,
                            'updated_at' => optional($location->updated_at)->toDateTimeString(),
                        ] : null,
                        'tracking_url' => route('assign-luggage.tracking-data', $assignment->id),
                    ];
                })->values();
            @endphp

            <!-- Live Delivery Tracking Section -->
            <div class="card border border-gray-3 shadow-sm overflow-hidden mb-4 live-delivery-card" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom border-gray-2 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0 fw-extrabold text-dark">Live Delivery Tracking</h5>
                        <span class="text-muted fs-12">Track all drivers after pickup order confirmation</span>
                    </div>
                    <span class="badge bg-soft-warning text-warning px-3 py-1.5 fs-11 rounded-pill fw-bold">{{ $liveTrackingAssignments->count() }} live routes</span>
                </div>

                @if($liveTrackingAssignments->count() > 0)
                    <div class="row g-0 live-delivery-grid">
                        <div class="col-12 col-xl-4 border-end border-gray-2 live-delivery-list">
                            <div class="p-3 d-flex flex-column gap-2">
                                @foreach($liveTrackingAssignments as $idx => $trackingAssignment)
                                    @php
                                        $trackingLocation = $currentLocations->get($trackingAssignment->driver_id);
                                        $isFresh = $trackingLocation && $trackingLocation->updated_at && $trackingLocation->updated_at->gt(now()->subMinutes(2));
                                    @endphp
                                    <button type="button"
                                            class="live-route-item {{ $idx === 0 ? 'active' : '' }}"
                                            data-order-id="{{ $trackingAssignment->id }}">
                                        <div class="d-flex align-items-start gap-2.5 min-w-0">
                                            @if($trackingAssignment->driver && $trackingAssignment->driver->profile_photo)
                                                <img src="{{ asset($trackingAssignment->driver->profile_photo) }}" alt="driver avatar" class="rounded-circle live-route-avatar">
                                            @elseif($trackingAssignment->driver)
                                                <span class="avatar-text bg-soft-primary text-primary rounded-circle live-route-avatar d-flex align-items-center justify-content-center fw-bold">{{ $trackingAssignment->driver->getInitials() }}</span>
                                            @else
                                                <span class="avatar-text bg-soft-secondary text-muted rounded-circle live-route-avatar d-flex align-items-center justify-content-center fw-bold">UN</span>
                                            @endif
                                            <span class="min-w-0 flex-grow-1">
                                                <span class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                                    <span class="fw-extrabold text-dark fs-12.5 text-truncate">{{ $trackingAssignment->driver->name ?? 'Unassigned' }}</span>
                                                    <span class="badge {{ $isFresh ? 'bg-soft-success text-success' : 'bg-soft-secondary text-secondary' }} fs-10 rounded-pill live-route-status">
                                                        {{ $isFresh ? 'Live' : 'Waiting' }}
                                                    </span>
                                                </span>
                                                <span class="d-block text-primary fw-bold fs-11 mb-1">#ORD-{{ str_pad($trackingAssignment->id, 5, '0', STR_PAD_LEFT) }} · {{ $trackingAssignment->company->company_name ?? 'N/A' }}</span>
                                                <span class="d-block text-muted fs-11 line-clamp-1" title="{{ $trackingAssignment->drop_location }}">
                                                    <i class="feather-map-pin me-1"></i>{{ $trackingAssignment->drop_location }}
                                                </span>
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12 col-xl-8">
                            <div class="live-map-toolbar px-4 py-3 border-bottom border-gray-2 d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                <div class="min-w-0">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="avatar-text avatar-sm bg-soft-warning text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                            <i class="feather-navigation"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <h6 class="fw-extrabold text-dark mb-0 live-selected-title">Select a live route</h6>
                                            <span class="text-muted fs-11 live-selected-subtitle">Pickup and delivery route will appear here</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="live-map-stats">
                                    <span><strong id="live-map-speed">0.0</strong> km/h</span>
                                    <span><strong id="live-map-battery">--</strong>% battery</span>
                                    <span id="live-map-updated">No ping yet</span>
                                </div>
                            </div>
                            <div id="driver-activities-live-map" class="driver-activities-live-map"></div>
                            <div class="live-map-legend">
                                <span><i class="legend-dot pickup-dot"></i> Pickup</span>
                                <span><i class="legend-dot drop-dot"></i> Destination</span>
                                <span><i class="legend-dot driver-dot"></i> Driver</span>
                                <span><i class="legend-line planned-line"></i> Pickup to delivery</span>
                                <span><i class="legend-line history-line"></i> Route history</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-5 text-center">
                        <i class="feather-map-pin fs-1 text-muted mb-3 d-block"></i>
                        <h6 class="fw-bold text-dark mb-1">No Live Deliveries</h6>
                        <p class="text-muted fs-12.5 mb-0">Live routes appear here after a driver picks up an order.</p>
                    </div>
                @endif
            </div>

            <!-- Main Listing Table Card -->
            <div class="card border border-gray-3 shadow-sm overflow-hidden" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom border-gray-2 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 fw-extrabold text-dark">Activity Log List</h5>
                    <span class="badge bg-soft-primary text-primary px-3 py-1.5 fs-11 rounded-pill fw-bold">Showing {{ $assignments->count() }} records</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="min-width: 900px;">
                        <thead>
                            <tr class="table-light fs-11 fw-bold text-uppercase text-muted">
                                <th class="ps-4 py-3" style="width: 220px;">Driver</th>
                                <th class="py-3" style="width: 180px;">Order & Airline</th>
                                <th class="py-3" style="width: 250px;">Route Timeline</th>
                                <th class="py-3" style="width: 220px; text-align: center;">Live Progress</th>
                                <th class="pe-4 py-3" style="width: 250px;">Delivery Proof</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr>
                                    <!-- Driver Info Column -->
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            @if($assignment->driver && $assignment->driver->profile_photo)
                                                <img src="{{ asset($assignment->driver->profile_photo) }}" alt="driver avatar" class="rounded-circle me-3 border border-2 border-primary" style="width: 38px; height: 38px; object-fit: cover;">
                                            @elseif($assignment->driver)
                                                <div class="avatar-text avatar-md bg-soft-primary text-primary rounded-circle me-3 d-flex align-items-center justify-content-center border border-2 border-primary" style="width: 38px; height: 38px; font-weight: 700; font-size: 13px;">
                                                    {{ $assignment->driver->getInitials() }}
                                                </div>
                                            @else
                                                <div class="avatar-text avatar-md bg-soft-secondary text-muted rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-weight: 700; font-size: 13px;">
                                                    UN
                                                </div>
                                            @endif
                                            <div style="line-height: 1.3;">
                                                <span class="fw-bold text-dark fs-12.5 d-block">{{ $assignment->driver->name ?? 'Unassigned' }}</span>
                                                <span class="text-muted fs-11.5">{{ $assignment->driver->email ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Order & Airline Info Column -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($assignment->company->logo)
                                                <img src="{{ asset($assignment->company->logo) }}" alt="airline logo" class="rounded border p-0.5" style="width: 26px; height: 26px; object-fit: cover;">
                                            @else
                                                <div class="bg-soft-primary text-primary rounded d-flex align-items-center justify-content-center border" style="width: 26px; height: 26px; font-size: 9px; font-weight: 800;">
                                                    {{ substr($assignment->company->company_name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div style="line-height: 1.25;">
                                                <a href="{{ route('assign-luggage.show', $assignment->id) }}" class="fw-extrabold text-primary fs-12.5 d-block hover-underline">#ORD-{{ str_pad($assignment->id, 5, '0', STR_PAD_LEFT) }}</a>
                                                <span class="text-muted fs-11">{{ $assignment->company->company_name }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Route Timeline Info Column -->
                                    <td>
                                        <div style="line-height: 1.35; padding: 2px 0;">
                                            <div class="d-flex align-items-baseline gap-1.5 mb-1.5">
                                                <span class="badge bg-soft-info text-info p-0.5 rounded-circle fs-8 d-flex align-items-center justify-content-center" style="width: 11px; height: 11px;"><i class="feather-navigation"></i></span>
                                                <span class="text-dark fw-medium fs-11.5 line-clamp-1" title="{{ $assignment->pickup_location }}">{{ $assignment->pickup_location }}</span>
                                            </div>
                                            <div class="d-flex align-items-baseline gap-1.5">
                                                <span class="badge bg-soft-success text-success p-0.5 rounded-circle fs-8 d-flex align-items-center justify-content-center" style="width: 11px; height: 11px;"><i class="feather-map-pin"></i></span>
                                                <span class="text-dark fw-medium fs-11.5 line-clamp-1" title="{{ $assignment->drop_location }}">{{ $assignment->drop_location }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Compact Visual Stepper column -->
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <div class="table-stepper">
                                                <div class="position-relative">
                                                    <!-- Connect Line bg -->
                                                    <div class="table-stepper-line"></div>
                                                    <!-- Active Overlay Line -->
                                                    <div class="table-stepper-line-active" style="width: {{ $assignment->status === 'In Progress' ? '0%' : ($assignment->status === 'Pickup' ? '50%' : '100%') }};"></div>
                                                    
                                                    <!-- Nodes -->
                                                    <div class="table-stepper-wrapper">
                                                        <!-- Node 1 -->
                                                        <div class="table-stepper-item {{ in_array($assignment->status, ['In Progress', 'Pickup', 'Delivered']) ? 'active' : '' }}">
                                                            <div class="table-stepper-icon">
                                                                <i class="feather-navigation"></i>
                                                            </div>
                                                            <span class="table-stepper-label">Transit</span>
                                                        </div>
                                                        <!-- Node 2 -->
                                                        <div class="table-stepper-item {{ in_array($assignment->status, ['Pickup', 'Delivered']) ? 'active-warning' : '' }}">
                                                            <div class="table-stepper-icon">
                                                                <i class="feather-package"></i>
                                                            </div>
                                                            <span class="table-stepper-label">Pickup</span>
                                                        </div>
                                                        <!-- Node 3 -->
                                                        <div class="table-stepper-item {{ $assignment->status === 'Delivered' ? 'active-success' : '' }}">
                                                            <div class="table-stepper-icon">
                                                                <i class="feather-check"></i>
                                                            </div>
                                                            <span class="table-stepper-label">Done</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Delivery Proof Display column -->
                                    <td class="pe-4">
                                        @if($assignment->status === 'Delivered')
                                            <div class="d-flex align-items-center gap-2.5">
                                                @if($assignment->delivery_proof_images && count($assignment->delivery_proof_images) > 0)
                                                    <div class="d-flex -space-x-8 hover-proof-container">
                                                        @foreach($assignment->delivery_proof_images as $idx => $img)
                                                            <a href="javascript:void(0);" 
                                                               onclick="showProofImage('{{ asset($img) }}', 'Order #ORD-{{ str_pad($assignment->id, 5, '0', STR_PAD_LEFT) }} Proof Photo {{ $idx + 1 }}')"
                                                               data-bs-toggle="modal" 
                                                               data-bs-target="#proofImageModal"
                                                               class="proof-thumb-link rounded border shadow-sm p-0.5 bg-white overflow-hidden" 
                                                               style="width: 38px; height: 38px; margin-left: {{ $idx > 0 ? '-14px' : '0' }}; z-index: {{ 10 - $idx }};">
                                                                <img src="{{ asset($img) }}" class="w-100 h-100 rounded" style="object-fit: cover;">
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="fs-11 text-muted"><i class="feather-alert-circle text-warning me-1"></i>No images</span>
                                                @endif
                                                <div style="line-height: 1.35;">
                                                    <span class="badge bg-soft-success text-success px-2 py-0.5 fs-10.5 rounded fw-extrabold text-uppercase d-block mb-0.5 text-center">Delivered</span>
                                                    <span class="text-muted fs-10 fw-semibold d-block">{{ $assignment->delivered_at ? $assignment->delivered_at->format('d M, H:i') : '' }}</span>
                                                </div>
                                            </div>
                                        @elseif($assignment->status === 'Pickup')
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button"
                                                        class="btn btn-sm btn-light-warning text-warning border-0 fw-bold fs-11 js-track-order"
                                                        data-order-id="{{ $assignment->id }}">
                                                    <i class="feather-map-pin me-1"></i> Track Route
                                                </button>
                                                <span class="fs-11 text-muted">Live after pickup</span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center gap-2 text-muted">
                                                <i class="feather-clock fs-14"></i>
                                                <span class="fs-12 fw-medium">Pending delivery...</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="feather-activity fs-1 text-muted mb-3 d-block"></i>
                                            <h5 class="fw-bold mb-2 text-dark">No Driver Activities Found</h5>
                                            <p class="text-muted fs-12.5 mb-0">No luggage assignments match the specified search query or filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                @if($assignments->hasPages())
                    <div class="card-footer bg-transparent border-top border-gray-2 p-3 px-4 d-flex justify-content-center">
                        {{ $assignments->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- Delivery Proof High-Res Lightbox Modal -->
<div class="modal fade" id="proofImageModal" tabindex="-1" aria-labelledby="proofImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header py-3 px-4 border-bottom border-gray-2">
                <h5 class="modal-title fw-extrabold text-dark" id="proofImageModalLabel">Delivery Proof Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center bg-light" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <img id="modal-proof-img" src="" class="img-fluid rounded border shadow-sm w-100" style="max-height: 480px; object-fit: contain; background-color: #000;">
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Set full image source and label header inside proof modal lightbox
 */
function showProofImage(imageSrc, label) {
    const modalImg = document.getElementById('modal-proof-img');
    const modalLabel = document.getElementById('proofImageModalLabel');
    if (modalImg) {
        modalImg.src = imageSrc;
    }
    if (modalLabel) {
        modalLabel.innerText = label;
    }
}
</script>

<style>
/* Dashboard KPI Border Highlights */
.kpi-total {
    border-left: 3.5px solid #64748b !important;
}
.kpi-transit {
    border-left: 3.5px solid #3b82f6 !important;
}
.kpi-pickup {
    border-left: 3.5px solid #f59e0b !important;
}
.kpi-delivered {
    border-left: 3.5px solid #10b981 !important;
}

.live-delivery-grid {
    min-height: 440px;
}
.live-delivery-list {
    max-height: 520px;
    overflow-y: auto;
    background: #f8fafc;
}
html.app-skin-dark .live-delivery-list {
    background: rgba(15, 23, 42, 0.32);
}
.live-route-item {
    width: 100%;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    border-radius: 10px;
    padding: 12px;
    text-align: left;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}
.live-route-item:hover,
.live-route-item.active {
    border-color: #f59e0b;
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.14);
    transform: translateY(-1px);
}
html.app-skin-dark .live-route-item {
    background: #1e293b;
    border-color: #334155;
}
.live-route-avatar {
    width: 36px;
    height: 36px;
    object-fit: cover;
    flex: 0 0 36px;
    font-size: 12px;
}
.live-route-status {
    flex: 0 0 auto;
}
.driver-activities-live-map {
    width: 100%;
    height: 390px;
    background: #e2e8f0;
}
.live-map-toolbar {
    min-height: 74px;
}
.live-map-stats {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
}
.live-map-stats span {
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: 5px 10px;
    background: #ffffff;
}
html.app-skin-dark .live-map-stats span {
    background: #0f172a;
    border-color: #334155;
}
.live-map-legend {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    padding: 10px 16px;
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 10.5px;
    font-weight: 700;
}
.legend-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 5px;
}
.legend-line {
    display: inline-block;
    width: 18px;
    height: 3px;
    border-radius: 3px;
    margin-right: 5px;
    vertical-align: middle;
}
.legend-line.planned-line {
    background: #2563eb;
}
.legend-line.history-line {
    background: #f59e0b;
}
.pickup-dot {
    background: #3b82f6;
}
.drop-dot {
    background: #10b981;
}
.driver-dot {
    background: #f59e0b;
}
.live-map-marker {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    border: 2px solid #ffffff;
    box-shadow: 0 5px 14px rgba(15, 23, 42, 0.24);
}
.live-map-marker.pickup {
    background: #3b82f6;
}
.live-map-marker.drop {
    background: #10b981;
}
.live-map-marker.driver {
    background: #f59e0b;
    position: relative;
}
.live-map-marker.driver::before {
    content: '';
    position: absolute;
    inset: -8px;
    border-radius: 50%;
    background: rgba(245, 158, 11, 0.28);
    animation: liveDriverPulse 1.8s ease-out infinite;
}
.live-map-marker.driver i {
    position: relative;
    z-index: 1;
}
@keyframes liveDriverPulse {
    0% { transform: scale(0.7); opacity: 0.9; }
    100% { transform: scale(1.8); opacity: 0; }
}

/* Hover-zoom effects for table proof thumbnails */
.proof-thumb-link {
    display: inline-block;
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s ease, margin 0.2s ease;
    cursor: pointer;
}
.proof-thumb-link:hover {
    transform: translateY(-4px) scale(1.18);
    box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.15) !important;
    z-index: 99 !important; /* Bring to front on hover */
}
.hover-proof-container:hover .proof-thumb-link:not(:hover) {
    opacity: 0.65;
    transform: scale(0.95);
}

/* Custom Table Row line clamp description */
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}

/* Premium Compact Table Progress Stepper */
.table-stepper {
    position: relative;
    padding: 6px 4px;
    background: transparent;
    width: 200px;
}
.table-stepper-line {
    position: absolute;
    height: 3px;
    background: #cbd5e1;
    top: 15px;
    left: 20px;
    right: 20px;
    z-index: 1;
    border-radius: 3px;
}
html.app-skin-dark .table-stepper-line {
    background: #475569;
}
.table-stepper-line-active {
    position: absolute;
    height: 3px;
    background: linear-gradient(90deg, #3b82f6 0%, #f59e0b 50%, #10b981 100%);
    top: 15px;
    left: 20px;
    z-index: 1;
    border-radius: 3px;
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.table-stepper-wrapper {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 2;
}

.table-stepper-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 40px;
}

.table-stepper-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #cbd5e1;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    border: 2px solid #ffffff;
    box-shadow: 0 0 0 1px #cbd5e1;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
html.app-skin-dark .table-stepper-icon {
    background: #475569;
    color: #94a3b8;
    border-color: #1e293b;
    box-shadow: 0 0 0 1px #475569;
}

/* Stepper Status Indicators */
.table-stepper-item.active .table-stepper-icon {
    background: #3b82f6;
    color: #ffffff;
    border-color: #ffffff;
    box-shadow: 0 0 0 1px #3b82f6, 0 0 5px rgba(59, 130, 246, 0.4);
}
html.app-skin-dark .table-stepper-item.active .table-stepper-icon {
    border-color: #1e293b;
}

.table-stepper-item.active-warning .table-stepper-icon {
    background: #f59e0b;
    color: #ffffff;
    border-color: #ffffff;
    box-shadow: 0 0 0 1px #f59e0b, 0 0 5px rgba(245, 158, 11, 0.4);
}
html.app-skin-dark .table-stepper-item.active-warning .table-stepper-icon {
    border-color: #1e293b;
}

.table-stepper-item.active-success .table-stepper-icon {
    background: #10b981;
    color: #ffffff;
    border-color: #ffffff;
    box-shadow: 0 0 0 1px #10b981, 0 0 5px rgba(16, 185, 129, 0.4);
}
html.app-skin-dark .table-stepper-item.active-success .table-stepper-icon {
    border-color: #1e293b;
}

.table-stepper-label {
    font-size: 8px;
    font-weight: 800;
    color: #64748b;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    transition: color 0.3s ease;
}
html.app-skin-dark .table-stepper-label {
    color: #94a3b8;
}

.table-stepper-item.active .table-stepper-label {
    color: #3b82f6;
}
.table-stepper-item.active-warning .table-stepper-label {
    color: #f59e0b;
}
.table-stepper-item.active-success .table-stepper-label {
    color: #10b981;
}

/* Stacked avatars margins */
.-space-x-8 > * + * {
    margin-left: -12px !important;
}

@media (max-width: 576px) {
    /* ── LAYER 1: Kill outer layout container padding ────────────────── */
    .nxl-container,
    .nxl-content {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* ── LAYER 2: Main content vertical rhythm ──────────────────────── */
    .main-content {
        padding-top: 10px !important;
        padding-bottom: 10px !important;
    }

    /* ── LAYER 3: Primary page container — tight 12px side gutters ─── */
    .container-fluid {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    /* ── Header row: wrap nicely on small screens ─────────────────── */
    .d-flex.align-items-center.justify-content-between.mb-4 {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 6px !important;
    }

    /* ── KPI cards: 2-up grid on mobile ──────────────────────────── */
    .row-cols-1 > .col {
        flex: 0 0 50% !important;
        max-width: 50% !important;
    }

    /* ── Activity Log list card: full-width, no margin leakage ────── */
    .card {
        border-radius: 12px !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-bottom: 14px !important;
        width: 100% !important;
    }
    .card-body {
        padding: 12px !important;
    }
    .card-header {
        padding: 10px 12px !important;
    }
    .card-footer {
        padding: 10px 12px !important;
    }

    .live-delivery-grid {
        min-height: auto;
    }

    .live-delivery-list {
        max-height: 290px;
        border-right: 0 !important;
        border-bottom: 1px solid #e2e8f0;
    }

    .driver-activities-live-map {
        height: 320px;
    }

    .live-map-toolbar {
        align-items: flex-start !important;
    }

    .live-map-stats {
        width: 100%;
    }

    /* ── Table: horizontal scroll within tight container ─────────── */
    .table-responsive {
        border-radius: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    .table td,
    .table th {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
    /* Override Bootstrap ps-4 / pe-4 on first/last cells */
    .table td.ps-4,
    .table th.ps-4 {
        padding-left: 10px !important;
    }
    .table td.pe-4,
    .table th.pe-4 {
        padding-right: 10px !important;
    }

    /* ── Badge / label sizes ──────────────────────────────────────── */
    .badge {
        font-size: 10px !important;
    }

    /* ── Breadcrumb: compact on mobile ───────────────────────────── */
    .breadcrumb {
        font-size: 11px !important;
    }
}
</style>
@endsection

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/select2.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/select2-theme.min.css') }}">
@endpush

@push('scripts')
<!-- Include Select2 JS script -->
<script src="{{ asset('assets/vendors/js/select2.min.js') }}"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 selectors (Professional bootstrap-5 themed dropdowns)
        $('.select2-select').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('.filter-card')
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const trackingOrders = @json($trackingPayload);
        const trackingById = new Map(trackingOrders.map(order => [String(order.id), order]));
        const mapElement = document.getElementById('driver-activities-live-map');
        const listButtons = document.querySelectorAll('.live-route-item');
        const trackButtons = document.querySelectorAll('.js-track-order');

        if (!mapElement || typeof L === 'undefined') {
            return;
        }

        let map = null;
        let pickupMarker = null;
        let dropMarker = null;
        let driverMarker = null;
        let plannedRoutePolyline = null;
        let routePolyline = null;
        let boundsGroup = null;
        let activeOrderId = trackingOrders.length ? String(trackingOrders[0].id) : null;
        let pollingTimer = null;
        let lastLivePingAt = null;
        let lastDashboardDriverLatLng = null;
        let subscribedChannel = null;

        function dashboardTrackingDebug(message, context = {}) {
            console.log('[DriverActivitiesTracking]', message, context);
        }

        function initEcho() {
            if (window.LaravelEcho || typeof Echo === 'undefined') {
                return window.LaravelEcho || null;
            }

            try {
                window.LaravelEcho = new Echo({
                    broadcaster: 'reverb',
                    key: '{{ env("REVERB_APP_KEY") }}',
                    wsHost: '{{ env("REVERB_HOST") ?: "127.0.0.1" }}',
                    wsPort: {{ env("REVERB_PORT") ?: 8080 }},
                    wssPort: {{ env("REVERB_PORT") ?: 8080 }},
                    forceTLS: {{ env("REVERB_SCHEME") === 'https' ? 'true' : 'false' }},
                    enabledTransports: ['ws', 'wss'],
                });
                dashboardTrackingDebug('Echo initialized for driver activities tracking.');
                return window.LaravelEcho;
            } catch (error) {
                console.warn('Live tracking broadcast setup skipped:', error);
                dashboardTrackingDebug('Echo initialization failed.', { message: error.message });
                return null;
            }
        }

        function initMap() {
            if (map) return;

            map = L.map(mapElement, {
                center: [20, 0],
                zoom: 2,
                zoomControl: true,
                attributionControl: true
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
            }).addTo(map);
        }

        function makeIcon(type, iconName) {
            return L.divIcon({
                className: '',
                html: `<div class="live-map-marker ${type}"><i class="feather-${iconName}"></i></div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17],
                popupAnchor: [0, -18]
            });
        }

        function clearMapLayers() {
            [pickupMarker, dropMarker, driverMarker, plannedRoutePolyline, routePolyline].forEach(layer => {
                if (layer && map) {
                    map.removeLayer(layer);
                }
            });

            pickupMarker = null;
            dropMarker = null;
            driverMarker = null;
            plannedRoutePolyline = null;
            routePolyline = null;
            if (boundsGroup && map) {
                map.removeLayer(boundsGroup);
            }
            boundsGroup = L.featureGroup().addTo(map);
        }

        function setText(selector, value) {
            const element = document.querySelector(selector);
            if (element) element.textContent = value;
        }

        function formatPing(updatedAt) {
            if (!updatedAt) return 'No ping yet';

            const timestamp = new Date(updatedAt.replace(' ', 'T')).getTime();
            if (!timestamp) return 'Last ping recorded';

            const seconds = Math.max(0, Math.round((Date.now() - timestamp) / 1000));
            if (seconds < 60) return `Ping ${seconds}s ago`;
            const minutes = Math.round(seconds / 60);
            if (minutes < 60) return `Ping ${minutes}m ago`;
            return `Ping ${Math.round(minutes / 60)}h ago`;
        }

        setInterval(() => {
            if (!lastLivePingAt) return;

            const seconds = Math.round((Date.now() - lastLivePingAt) / 1000);
            if (seconds > 60) {
                setText('#live-map-updated', 'Tracking paused / Driver offline');
                dashboardTrackingDebug('Dashboard tracking marked stale.', { seconds });
            } else if (seconds > 10) {
                setText('#live-map-updated', `Last updated ${seconds}s ago`);
            }
        }, 5000);

        function updateSummary(order, data) {
            setText('.live-selected-title', `${order.order_number} · ${order.driver.name}`);
            setText('.live-selected-subtitle', `${order.pickup.address} to ${order.destination.address}`);

            const lastLocation = data.last_location || order.last_location;
            setText('#live-map-speed', lastLocation && lastLocation.speed !== null ? Number(lastLocation.speed).toFixed(1) : '0.0');
            setText('#live-map-battery', lastLocation && lastLocation.battery_level !== null ? lastLocation.battery_level : '--');
            setText('#live-map-updated', formatPing(lastLocation ? lastLocation.updated_at : null));
        }

        function addPointMarker(kind, point, iconName, popupTitle) {
            if (!point || point.lat === null || point.lng === null) return null;

            const marker = L.marker([point.lat, point.lng], {
                icon: makeIcon(kind, iconName)
            }).addTo(map).bindPopup(`<strong>${popupTitle}</strong><br><small>${point.address || ''}</small>`);

            boundsGroup.addLayer(marker);
            return marker;
        }

        function updateDriverLocation(location, order) {
            if (!location || location.lat === null || location.lng === null || !map) return;

            const latLng = [location.lat, location.lng];
            const previous = lastDashboardDriverLatLng;
            const movedMeters = previous ? map.distance(previous, L.latLng(latLng)) : null;
            const isStationary = movedMeters !== null && movedMeters < 3 && (location.speed === null || Number(location.speed) < 2);
            lastDashboardDriverLatLng = L.latLng(latLng);
            const updateTimestamp = location.updated_at ? new Date(String(location.updated_at).replace(' ', 'T')).getTime() : Date.now();
            lastLivePingAt = updateTimestamp || Date.now();
            const updateAgeSeconds = Math.round((Date.now() - lastLivePingAt) / 1000);

            if (!driverMarker) {
                driverMarker = L.marker(latLng, {
                    icon: makeIcon('driver', 'navigation'),
                    zIndexOffset: 1000
                }).addTo(map).bindPopup(`<strong>${order.driver.name}</strong><br><small>Live driver location</small>`);
            } else {
                driverMarker.setLatLng(latLng);
            }

            dashboardTrackingDebug('Marker updated on dashboard map.', {
                orderId: order.id,
                lat: location.lat,
                lng: location.lng,
                speed: location.speed,
                movedMeters,
                isStationary
            });

            if (boundsGroup) {
                boundsGroup.addLayer(driverMarker);
            }

            if (updateAgeSeconds > 60) {
                setText('#live-map-updated', 'Tracking paused / Driver offline');
            } else {
                setText('#live-map-updated', isStationary ? 'Driver is stationary' : 'Last updated just now');
            }
        }

        function drawPlannedRoute(pickup, destination) {
            if (!pickup || !destination || pickup.lat === null || pickup.lng === null || destination.lat === null || destination.lng === null || !map) {
                dashboardTrackingDebug('Planned route skipped because pickup or destination coordinates are missing.', { pickup, destination });
                return;
            }

            const fallbackPath = [[pickup.lat, pickup.lng], [destination.lat, destination.lng]];

            function drawFallbackRoute() {
                plannedRoutePolyline = L.polyline(fallbackPath, {
                    color: '#2563eb',
                    weight: 4,
                    opacity: 0.75,
                    dashArray: '8 7',
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(map);

                boundsGroup.addLayer(plannedRoutePolyline);
                dashboardTrackingDebug('Planned route fallback line drawn.', { pickup, destination });
            }

            const routeUrl = `https://router.project-osrm.org/route/v1/driving/${pickup.lng},${pickup.lat};${destination.lng},${destination.lat}?overview=full&geometries=geojson`;

            dashboardTrackingDebug('Planned route request sent.', { routeUrl });

            fetch(routeUrl)
                .then(response => response.json())
                .then(data => {
                    const geometry = data && data.routes && data.routes[0] ? data.routes[0].geometry : null;

                    if (!geometry) {
                        drawFallbackRoute();
                        return;
                    }

                    plannedRoutePolyline = L.geoJSON(geometry, {
                        style: {
                            color: '#2563eb',
                            weight: 4,
                            opacity: 0.82,
                            lineCap: 'round',
                            lineJoin: 'round'
                        }
                    }).addTo(map);

                    boundsGroup.addLayer(plannedRoutePolyline);
                    dashboardTrackingDebug('Planned pickup-to-delivery road route drawn.', {
                        distanceMeters: data.routes[0].distance,
                        durationSeconds: data.routes[0].duration
                    });
                })
                .catch(error => {
                    dashboardTrackingDebug('Planned route request failed; drawing fallback line.', { message: error.message });
                    drawFallbackRoute();
                });
        }

        function drawRouteHistory(history) {
            if (!history || history.length === 0) return;

            const path = history
                .filter(point => point.lat !== null && point.lng !== null)
                .map(point => [point.lat, point.lng]);

            if (path.length === 0) return;

            routePolyline = L.polyline(path, {
                color: '#f59e0b',
                weight: 5,
                opacity: 0.85,
                lineCap: 'round',
                lineJoin: 'round'
            }).addTo(map);

            boundsGroup.addLayer(routePolyline);
        }

        function renderTrackingData(order, data) {
            initMap();
            clearMapLayers();
            lastDashboardDriverLatLng = null;
            updateSummary(order, data);

            pickupMarker = addPointMarker('pickup', data.pickup || order.pickup, 'package', 'Pickup Point');
            dropMarker = addPointMarker('drop', data.destination || order.destination, 'map-pin', 'Delivery Destination');
            drawPlannedRoute(data.pickup || order.pickup, data.destination || order.destination);
            drawRouteHistory(data.route_history || []);
            updateDriverLocation(data.last_location || order.last_location, order);

            if (boundsGroup && boundsGroup.getLayers().length > 0) {
                map.fitBounds(boundsGroup.getBounds(), { padding: [42, 42], maxZoom: 15 });
            }

            setTimeout(() => map.invalidateSize(), 100);
        }

        function setActiveButton(orderId) {
            listButtons.forEach(button => {
                button.classList.toggle('active', button.dataset.orderId === String(orderId));
            });
        }

        function subscribeToOrder(orderId, order) {
            const echo = initEcho();
            if (!echo) return;

            try {
                if (subscribedChannel && window.LaravelEcho.leave) {
                    window.LaravelEcho.leave(`order.tracking.${subscribedChannel}`);
                }

                subscribedChannel = String(orderId);
                dashboardTrackingDebug('Subscribing to dashboard tracking channel.', { channel: `order.tracking.${orderId}` });
                echo.private(`order.tracking.${orderId}`)
                    .listen('.DriverLocationUpdated', event => {
                        if (String(activeOrderId) !== String(orderId)) return;
                        dashboardTrackingDebug('Manager received DriverLocationUpdated event on dashboard.', event);

                        const liveLocation = {
                            lat: Number(event.latitude),
                            lng: Number(event.longitude),
                            speed: event.speed,
                            heading: event.heading,
                            battery_level: event.batteryLevel,
                            updated_at: event.updatedAt
                        };

                        updateDriverLocation(liveLocation, order);
                        setText('#live-map-speed', liveLocation.speed !== null ? Number(liveLocation.speed).toFixed(1) : '0.0');
                        setText('#live-map-battery', liveLocation.battery_level !== null ? liveLocation.battery_level : '--');
                    });
            } catch (error) {
                console.warn('Live tracking subscription failed:', error);
                dashboardTrackingDebug('Dashboard tracking subscription failed.', { message: error.message });
            }
        }

        function startPolling(orderId, order) {
            if (pollingTimer) {
                clearInterval(pollingTimer);
            }

            pollingTimer = setInterval(() => {
                if (String(activeOrderId) !== String(orderId)) return;

                fetch(order.tracking_url || `/admin/assign-luggage/${orderId}/tracking-data`, {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            dashboardTrackingDebug('Polling tracking data refreshed.', { orderId });
                            renderTrackingData(order, data);
                        }
                    })
                    .catch(error => console.warn('Tracking refresh failed:', error));
            }, 15000);
        }

        function loadOrder(orderId) {
            const id = String(orderId);
            let order = trackingById.get(id);

            if (!order) {
                order = {
                    id: id,
                    order_number: `#ORD-${id.padStart(5, '0')}`,
                    driver: { name: 'Driver', email: 'N/A', initials: 'DR', photo: null },
                    pickup: { address: '', lat: null, lng: null },
                    destination: { address: '', lat: null, lng: null },
                    last_location: null,
                    tracking_url: `/admin/assign-luggage/${id}/tracking-data`
                };
            }

            activeOrderId = id;
            setActiveButton(id);

            const card = document.querySelector('.live-delivery-card');
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            fetch(order.tracking_url || `/admin/assign-luggage/${id}/tracking-data`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Tracking data unavailable.');
                    }

                    renderTrackingData(order, data);
                    subscribeToOrder(id, order);
                    startPolling(id, order);
                })
                .catch(error => {
                    console.warn('Initial tracking load failed:', error);
                    renderTrackingData(order, {
                        pickup: order.pickup,
                        destination: order.destination,
                        last_location: order.last_location,
                        route_history: []
                    });
                });
        }

        listButtons.forEach(button => {
            button.addEventListener('click', () => loadOrder(button.dataset.orderId));
        });

        trackButtons.forEach(button => {
            button.addEventListener('click', () => loadOrder(button.dataset.orderId));
        });

        if (activeOrderId) {
            loadOrder(activeOrderId);
        }
    });
</script>
@endpush
