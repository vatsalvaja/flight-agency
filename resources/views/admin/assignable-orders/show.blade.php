@extends('layouts.admin')

@section('title', 'Order Details || ' . ($appSettings->application_name ?? 'Wings'))

@push('head')
{{-- Leaflet.js Map CSS -- required for embedded navigation map --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="nxl-content order-details-page">
    <div class="main-content py-4">
        <div class="container-fluid px-4" style="max-width: 900px; margin: 0 auto;">
            
            <!-- Header bar with Back button -->
            <div class="d-flex align-items-center justify-content-between mb-4 page-detail-header flex-wrap gap-2">
                <a href="{{ route('assignable-orders.index') }}" class="btn btn-sm btn-light rounded-pill border">
                    <i class="feather-arrow-left me-1"></i> Dashboard
                </a>
                <span class="fs-12 text-muted fw-bold">ORDER DETAILS</span>
            </div>

            <!-- Success/Error Alerts -->
            @if(session('success_delivered'))
                <div class="card bg-soft-success text-success border border-success-20 shadow-sm mb-4" style="border-radius: 16px;">
                    <div class="card-body p-4 text-center">
                        <div class="checkmark-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 54px; height: 54px; border-radius: 50%;">
                            <i class="feather-check fs-2"></i>
                        </div>
                        <h4 class="fw-extrabold text-success mb-2">Order Delivered Successfully!</h4>
                        <p class="fs-13 text-muted mb-0">Delivery timestamp and photo proofs are recorded. Well done!</p>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4 border border-success-10" role="alert" style="border-radius: 12px;">
                    <i class="feather-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4 border border-danger-10" role="alert" style="border-radius: 12px;">
                    <i class="feather-alert-octagon me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Visual Horizontal Progress Tracker (Mockup Matching) -->
            <div class="card border border-gray-3 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h6 class="fw-extrabold text-dark text-center mb-3.5">Workflow Status</h6>
                    
                    <div class="stepper-container" style="max-width: 500px; margin: 0 auto;">
                        <div class="position-relative">
                            <!-- Connect Line bg -->
                            <div class="stepper-line-bg" style="left: 40px; right: 40px; top: 24px;"></div>
                            <!-- Active Overlay Line -->
                            <div class="stepper-line-active" style="left: 40px; top: 24px; width: {{ $assignment->status === 'In Progress' ? '0%' : ($assignment->status === 'Pickup' ? '50%' : '100%') }};"></div>
                            
                            <!-- Nodes -->
                            <div class="stepper-wrapper">
                                <!-- Step 1 -->
                                <div class="stepper-item {{ in_array($assignment->status, ['In Progress', 'Pickup', 'Delivered']) ? 'active' : '' }}" style="width: 80px;">
                                    <div class="stepper-icon">
                                        <i class="feather-navigation"></i>
                                    </div>
                                    <span class="stepper-label">In Transit</span>
                                </div>
                                <!-- Step 2 -->
                                <div class="stepper-item {{ in_array($assignment->status, ['Pickup', 'Delivered']) ? 'active-warning' : '' }}" style="width: 80px;">
                                    <div class="stepper-icon">
                                        <i class="feather-package"></i>
                                    </div>
                                    <span class="stepper-label">Pickup</span>
                                </div>
                                <!-- Step 3 -->
                                <div class="stepper-item {{ $assignment->status === 'Delivered' ? 'active-success' : '' }}" style="width: 80px;">
                                    <div class="stepper-icon">
                                        <i class="feather-check"></i>
                                    </div>
                                    <span class="stepper-label">Delivered</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Split Grid Layout -->
            <div class="row">
                <!-- Left side details (8 cols on large screen) -->
                <div class="col-12 col-md-7 col-lg-8 mb-4">
                    <div class="card border border-gray-3 shadow-sm h-100 overflow-hidden d-flex flex-column" style="border-radius: 16px;">
                        
                        <div class="card-header bg-transparent border-bottom border-gray-2 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="card-title mb-0 fw-extrabold text-dark">Order Information</h5>
                            <span class="fs-15 fw-extrabold text-primary order-id-badge">#ORD-{{ str_pad($assignment->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>

                        <div class="card-body p-4 flex-grow-1">
                            <!-- Company banner -->
                            <div class="d-flex align-items-center mb-4 bg-light p-3 rounded-3 border border-gray-2 company-info-banner">
                                @if($assignment->company->logo)
                                    <img src="{{ asset($assignment->company->logo) }}" alt="logo" class="rounded me-3 flex-shrink-0" style="height: 38px; width: 38px; object-fit: cover;">
                                @else
                                    <div class="avatar-text bg-soft-primary text-primary rounded me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; font-weight: 700; font-size: 14px;">
                                        {{ substr($assignment->company->company_name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="company-info-text" style="line-height: 1.3; min-width: 0;">
                                    <h6 class="fw-extrabold text-dark mb-0.5">{{ $assignment->company->company_name }}</h6>
                                    <span class="text-muted fs-11.5">Station: {{ $assignment->station->station_name }} ({{ $assignment->station->station_code }})</span>
                                </div>
                            </div>

                            <!-- Visual Timeline Journey -->
                            <h6 class="fw-extrabold text-dark mb-3">Delivery Route</h6>
                            <div class="route-timeline mb-4">
                                <div class="timeline-item">
                                    <span class="text-muted fs-9.5 d-block text-uppercase fw-semibold mb-0.5">Pickup From</span>
                                    <span class="fw-semibold text-dark fs-12.5 d-block" style="line-height: 1.3;">{{ $assignment->pickup_location }}</span>
                                </div>
                                <div class="timeline-item drop mt-3">
                                    <span class="text-muted fs-9.5 d-block text-uppercase fw-semibold mb-0.5">Drop To</span>
                                    <span class="fw-semibold text-dark fs-12.5 d-block" style="line-height: 1.3;">{{ $assignment->drop_location }}</span>
                                </div>
                            </div>

                            <!-- Numerical Details Grid -->
                            <div class="row g-3 border-top border-gray-2 pt-3 order-details-grid">
                                <div class="col-12 col-sm-6">
                                    <div class="order-detail-item">
                                        <div class="order-detail-icon">
                                            <i class="feather-map text-primary"></i>
                                        </div>
                                        <div class="order-detail-content">
                                            <span class="order-detail-label">Distance</span>
                                            <span class="order-detail-value">{{ $assignment->distance_km ?? '0.00' }} km</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="order-detail-item">
                                        <div class="order-detail-icon">
                                            <i class="feather-calendar text-primary"></i>
                                        </div>
                                        <div class="order-detail-content">
                                            <span class="order-detail-label">Expected Date &amp; Time</span>
                                            <span class="order-detail-value">{{ $assignment->expected_delivery_date->format('d M, Y h:i A') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="order-detail-item">
                                        <div class="order-detail-icon">
                                            <i class="feather-clock text-primary"></i>
                                        </div>
                                        <div class="order-detail-content">
                                            <span class="order-detail-label">Assigned Date</span>
                                            <span class="order-detail-value">{{ $assignment->created_at->format('d M, Y H:i A') }}</span>
                                        </div>
                                    </div>
                                </div>
                                @if($assignment->delivered_at)
                                    <div class="col-12 col-sm-6">
                                        <div class="order-detail-item">
                                            <div class="order-detail-icon order-detail-icon-success">
                                                <i class="feather-check-circle text-success"></i>
                                            </div>
                                            <div class="order-detail-content">
                                                <span class="order-detail-label">Delivered Date</span>
                                                <span class="order-detail-value text-success">{{ $assignment->delivered_at->format('d M, Y H:i A') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Card Footer: Tappable Full-width Action Control -->
                        @if($assignment->status !== 'Delivered')
                            <div class="card-footer p-3 bg-light border-top border-gray-3">
                                @if($assignment->status === 'In Progress')
                                    <form action="{{ route('assignable-orders.pickup', $assignment->id) }}" method="POST" class="mb-0">
                                        @csrf
                                        <button type="submit" class="btn btn-lg btn-primary w-100 py-3 rounded-3 fw-extrabold text-uppercase text-white d-flex align-items-center justify-content-center gap-2" style="font-size: 13px; letter-spacing: 0.5px; background-color: #3b82f6; border-color: #3b82f6;">
                                            <i class="feather-truck fs-14"></i> Pickup Order
                                        </button>
                                    </form>
                                @elseif($assignment->status === 'Pickup')
                                    <div class="d-flex flex-column gap-2">
                                        {{-- Navigate Button: Opens embedded map for route navigation --}}
                                        <button type="button" id="navigate-toggle-btn" class="btn btn-lg w-100 py-3 rounded-3 fw-extrabold text-uppercase text-white d-flex align-items-center justify-content-center gap-2" onclick="toggleNavigationMap()" style="font-size: 13px; letter-spacing: 0.5px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; box-shadow: 0 4px 14px rgba(16,185,129,0.35);">
                                            <i class="feather-map-pin fs-14" id="nav-btn-icon"></i>
                                            <span id="nav-btn-label">Navigate to Delivery</span>
                                        </button>
                                        {{-- Google Maps Button: Direct external navigation --}}
                                        <button type="button" class="btn btn-lg w-100 py-3 rounded-3 fw-extrabold text-uppercase text-white d-flex align-items-center justify-content-center gap-2" onclick="openGoogleMapsDirections()" style="font-size: 13px; letter-spacing: 0.5px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border: none; box-shadow: 0 4px 14px rgba(37,99,235,0.35);">
                                            <i class="feather-map fs-14"></i> Open in Google Maps
                                        </button>
                                        <button type="button" class="btn btn-lg btn-warning text-white w-100 py-3 rounded-3 fw-extrabold text-uppercase text-white d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#deliveryModal" style="font-size: 13px; letter-spacing: 0.5px; background-color: #f59e0b; border-color: #f59e0b;">
                                            <i class="feather-check-circle fs-14"></i> Mark as Delivered
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>

                <!-- Right side details: Notes & Proof uploads (4 cols on large screen) -->
                <div class="col-12 col-md-5 col-lg-4 mb-4">
                    <div class="d-flex flex-column gap-3 h-100">
                        
                        <!-- Notes Card -->
                        <div class="card border border-gray-3 shadow-sm" style="border-radius: 16px;">
                            <div class="card-header bg-transparent border-bottom border-gray-2 py-3 px-4">
                                <h6 class="card-title mb-0 fw-extrabold text-dark">Manager Notes</h6>
                            </div>
                            <div class="card-body p-4 text-muted fs-12" style="line-height: 1.4; min-height: 100px;">
                                {{ $assignment->notes ?? 'No special handling instructions provided.' }}
                            </div>
                        </div>

                        <!-- Proof of Delivery Display Card -->
                        <div class="card border border-gray-3 shadow-sm flex-grow-1" style="border-radius: 16px;">
                            <div class="card-header bg-transparent border-bottom border-gray-2 py-3 px-4">
                                <h6 class="card-title mb-0 fw-extrabold text-dark">Delivery Proof</h6>
                            </div>
                            <div class="card-body p-4 d-flex flex-column justify-content-center">
                                @if($assignment->status === 'Delivered')
                                    @if($assignment->delivery_proof_images && count($assignment->delivery_proof_images) > 0)
                                        <div class="row g-2">
                                            @foreach($assignment->delivery_proof_images as $img)
                                                <div class="col-6">
                                                    <a href="{{ asset($img) }}" target="_blank" class="d-block border rounded-3 p-1 overflow-hidden hover-proof-image bg-white" style="height: 100px;">
                                                        <img src="{{ asset($img) }}" class="w-100 h-100 rounded-2" style="object-fit: cover;">
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4 fs-12">
                                            <i class="feather-alert-triangle fs-3 d-block mb-2 text-warning"></i>
                                            Completed without proof images.
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center text-muted py-4 fs-12">
                                        <i class="feather-camera fs-3 d-block mb-2 text-primary"></i>
                                        Proof image will be displayed here after delivery confirmation.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div> <!-- closes col-12 col-md-5 col-lg-4 (line 163) -->
            </div> <!-- closes row (line 79) -->

            @if($assignment->status === 'Pickup')
            <div class="alert alert-light border d-flex align-items-start gap-3 mb-3" id="driver-live-tracking-panel">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-soft-primary text-primary" style="width: 36px; height: 36px; flex: 0 0 36px;">
                    <i class="feather-radio" id="driver-tracking-icon"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="fw-bold text-dark fs-13">Live tracking</span>
                        <span class="badge bg-soft-warning text-warning fs-11" id="driver-tracking-status">Starting GPS...</span>
                    </div>
                    <div class="text-muted fs-11 mt-1" id="driver-tracking-detail">Keep this page open after pickup. Mobile browsers may pause GPS when the phone is locked or battery optimization is active.</div>
                    <div class="d-flex flex-wrap gap-3 mt-2 fs-11 text-muted">
                        <span>GPS: <strong id="driver-gps-permission">Checking</strong></span>
                        <span>Last update: <strong id="driver-last-location-update">No ping yet</strong></span>
                        <span id="driver-stationary-state">Waiting for movement</span>
                    </div>
                </div>
            </div>
            {{-- ===== EMBEDDED NAVIGATION MAP PANEL ===== --}}
            <div id="navigation-map-panel" class="nav-map-panel" style="display: none;">
                <!-- Map Panel Header with Route Summary -->
                <div class="nav-map-header">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-3 nav-map-header-top">
                        <div class="d-flex align-items-center gap-2 nav-map-title-group">
                            <div class="nav-header-icon">
                                <i class="feather-navigation"></i>
                            </div>
                            <div class="nav-map-title-text">
                                <h6 class="fw-extrabold text-dark mb-0" style="font-size: 14px;">Live Navigation</h6>
                                <span class="text-muted fs-11">Real-time route to delivery point</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 nav-map-header-actions">
                            <button type="button" class="btn btn-sm btn-primary border rounded-pill px-3 text-white d-flex align-items-center justify-content-center gap-1" onclick="openGoogleMapsDirections()" style="font-size: 11px; background-color: #3b82f6; border-color: #3b82f6;">
                                <i class="feather-map"></i> Open Google Maps
                            </button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 d-flex align-items-center justify-content-center" onclick="toggleNavigationMap()" style="font-size: 11px;">
                                <i class="feather-x me-1"></i> Close Map
                            </button>
                        </div>
                    </div>

                    <!-- Route Info Stats Strip -->
                    <div class="nav-stats-strip">
                        <div class="nav-stat-item">
                            <i class="feather-map text-primary"></i>
                            <div>
                                <span class="nav-stat-label">Distance</span>
                                <span class="nav-stat-value" id="nav-distance">Calculating...</span>
                            </div>
                        </div>
                        <div class="nav-stat-divider"></div>
                        <div class="nav-stat-item">
                            <i class="feather-clock text-warning"></i>
                            <div>
                                <span class="nav-stat-label">Est. Travel Time</span>
                                <span class="nav-stat-value" id="nav-duration">Calculating...</span>
                            </div>
                        </div>
                        <div class="nav-stat-divider"></div>
                        <div class="nav-stat-item">
                            <i class="feather-radio text-success" id="gps-status-icon"></i>
                            <div>
                                <span class="nav-stat-label">GPS Status</span>
                                <span class="nav-stat-value" id="nav-gps-status">Locating...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Route Addresses -->
                    <div class="nav-route-addresses mt-3">
                        <div class="nav-route-point pickup-point">
                            <div class="nav-route-dot pickup-dot"></div>
                            <div class="nav-route-text">
                                <span class="nav-route-label">Pickup</span>
                                <span class="nav-route-addr">{{ $assignment->pickup_location }}</span>
                            </div>
                        </div>
                        <div class="nav-route-connector"></div>
                        <div class="nav-route-point drop-point">
                            <div class="nav-route-dot drop-dot"></div>
                            <div class="nav-route-text">
                                <span class="nav-route-label">Destination</span>
                                <span class="nav-route-addr">{{ $assignment->drop_location }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leaflet Map Container -->
                <div id="delivery-map" class="nav-map-canvas"></div>

                <!-- Map Legend -->
                <div class="nav-map-legend">
                    <div class="legend-item"><span class="legend-dot" style="background:#3b82f6;"></span> Pickup Point</div>
                    <div class="legend-item"><span class="legend-dot" style="background:#10b981;"></span> Delivery Point</div>
                    <div class="legend-item"><span class="legend-dot" style="background:#f59e0b; border-radius: 50%;"></span> Your Location</div>
                    <div class="legend-item"><span class="legend-line"></span> Route</div>
                </div>
            </div>
            @endif

        </div> <!-- closes container-fluid (line 8) -->
    </div> <!-- closes main-content (line 7) -->
</div> <!-- closes nxl-content (line 6) -->
@endsection

@section('modals')
<!-- Modal Upload Container -->
@if($assignment->status === 'Pickup')
<div class="modal fade" id="deliveryModal" tabindex="-1" aria-labelledby="deliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header py-3 px-4 border-bottom border-gray-2">
                <h5 class="modal-title fw-extrabold text-dark" id="deliveryModalLabel">Delivery Proof</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopWebcam()"></button>
            </div>
            
            <form id="delivery-submit-form" action="{{ route('assignable-orders.deliver', $assignment->id) }}" method="POST" enctype="multipart/form-data" class="mb-0">
                @csrf
                <div class="modal-body p-4">
                    <p class="fs-12 text-muted mb-4">Please upload or capture a photo showing the delivered luggage clearly. At least one image is mandatory.</p>

                    <!-- Live Webcam Stream View -->
                    <div id="webcam-container" style="display: none;" class="mb-4 text-center">
                        <video id="webcam-preview" autoplay playsinline class="w-100 rounded-3 border border-gray-3 mb-2" style="max-height: 240px; object-fit: cover; background: #000; transform: scaleX(-1);"></video>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary flex-fill fw-bold py-2 rounded-pill" onclick="captureWebcamFrame()">
                                <i class="feather-camera me-1"></i> Capture Photo
                            </button>
                            <button type="button" class="btn btn-light fw-bold py-2 rounded-pill border" onclick="stopWebcam()">
                                <i class="feather-x me-1"></i> Cancel
                            </button>
                        </div>
                    </div>

                    <!-- Fallback Upload Container (File selector) -->
                    <div id="fallback-upload-container" style="display: none;" class="mb-4 text-center">
                        <div class="border border-dashed border-primary rounded-3 p-4 bg-light cursor-pointer" onclick="triggerGallery()" style="border-width: 2px !important;">
                            <i class="feather-upload-cloud fs-1 text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1 text-dark">Camera Unavailable / Permission Denied</h6>
                            <p class="fs-12 text-muted mb-0">Click here to upload delivery proof image(s) from your device.</p>
                        </div>
                        <div class="mt-3 text-center">
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" onclick="stopWebcam()">
                                <i class="feather-arrow-left me-1"></i> Back to options
                            </button>
                        </div>
                    </div>

                    <!-- Camera and Gallery Option Buttons -->
                    <div id="selection-buttons" class="row g-3 mb-4">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary w-100 py-3.5 d-flex flex-column align-items-center gap-1 rounded-3" onclick="startWebcam()">
                                <i class="feather-camera fs-3"></i>
                                <span class="fs-11 fw-bold">Capture Photo</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary w-100 py-3.5 d-flex flex-column align-items-center gap-1 rounded-3" onclick="triggerGallery()">
                                <i class="feather-image fs-3"></i>
                                <span class="fs-11 fw-bold">From Gallery</span>
                            </button>
                        </div>
                    </div>

                    <!-- Input Controls -->
                    <input type="file" id="camera-input" accept="image/*" capture="environment" style="display: none;">
                    <input type="file" id="gallery-input" accept="image/*" multiple style="display: none;">
                    
                    <!-- Consolidated Input Submitted by Form -->
                    <input type="file" name="proof_images[]" id="final-files-input" multiple style="display: none;">

                    <!-- Thumbnail Previews -->
                    <span class="text-muted fs-10 fw-bold d-block mb-2 text-uppercase">Proof Image Preview</span>
                    <div id="proof-previews" class="d-flex flex-wrap gap-2 p-3 bg-light rounded-3 border border-gray-2" style="min-height: 90px;">
                        <div class="text-center w-100 py-3 text-muted fs-11.5 id-empty-preview">
                            <i class="feather-image fs-4 d-block mb-1"></i>
                            Select or capture photo
                        </div>
                    </div>
                </div>

                <div class="modal-footer p-3 bg-light border-top border-gray-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal" onclick="stopWebcam()">Cancel</button>
                    <button type="submit" id="submit-delivery-btn" class="btn btn-success rounded-pill px-4 fw-extrabold text-uppercase text-white" disabled style="font-size: 11px; letter-spacing: 0.5px;">
                        Submit & Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let selectedFilesArray = [];
let webcamStream = null;

// =========================================================
// NAVIGATION MAP – Global State
// =========================================================
let navMap = null;                 // Leaflet map instance
let navRouteLayer = null;          // Polyline for OSRM route
let driverMarker = null;           // Live driver position marker
let geoWatchId = null;             // geolocation watcher ID
let mapInitialised = false;

// Coordinates and addresses from server (passed as PHP variables)
const PICKUP_LAT  = {{ $assignment->pickup_latitude  ?? 'null' }};
const PICKUP_LNG  = {{ $assignment->pickup_longitude ?? 'null' }};
const DROP_LAT    = {{ $assignment->drop_latitude    ?? 'null' }};
const DROP_LNG    = {{ $assignment->drop_longitude   ?? 'null' }};
const PICKUP_ADDR = {!! json_encode($assignment->pickup_location) !!};
const DROP_ADDR   = {!! json_encode($assignment->drop_location) !!};

/**
 * Opens Google Maps directions in a new tab.
 */
function openGoogleMapsDirections() {
    var url = 'https://www.google.com/maps/dir/?api=1';
    
    // Set destination
    if (DROP_LAT !== null && DROP_LNG !== null) {
        url += '&destination=' + DROP_LAT + ',' + DROP_LNG;
    } else {
        url += '&destination=' + encodeURIComponent(DROP_ADDR);
    }
    
    // Set origin (use live position if driver marker is active)
    if (driverMarker) {
        var latlng = driverMarker.getLatLng();
        url += '&origin=' + latlng.lat + ',' + latlng.lng;
    } else if (PICKUP_LAT !== null && PICKUP_LNG !== null) {
        url += '&origin=' + PICKUP_LAT + ',' + PICKUP_LNG;
    } else {
        url += '&origin=' + encodeURIComponent(PICKUP_ADDR);
    }
    
    const mapsWindow = window.open(url, '_blank', 'noopener');
    if (!mapsWindow) {
        updateDriverTrackingPanel('Tracking still active', 'Allow popups to open Google Maps separately. Staying on this page keeps live tracking running.', null);
        trackingDebug('Google Maps popup was blocked; stayed on tracking page to avoid stopping GPS.');
    }
    
}

/**
 * Toggle the visibility of the embedded navigation map panel.
 */
function toggleNavigationMap() {
    const panel = document.getElementById('navigation-map-panel');
    const btnLabel = document.getElementById('nav-btn-label');
    const btnIcon  = document.getElementById('nav-btn-icon');

    if (!panel) return;

    const isHidden = panel.style.display === 'none' || panel.style.display === '';

    if (isHidden) {
        panel.style.display = 'block';
        // Scroll smoothly to map
        setTimeout(() => panel.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
        btnLabel.textContent = 'Hide Map';
        btnIcon.className = 'feather-x fs-14';
        initNavigationMap();
    } else {
        panel.style.display = 'none';
        btnLabel.textContent = 'Navigate to Delivery';
        btnIcon.className = 'feather-map-pin fs-14';
        stopGeoWatch();
    }
}

/**
 * Initialise the Leaflet map (runs only once).
 */
function initNavigationMap() {
    if (mapInitialised) {
        // Map already created – just resume geo tracking
        startGeoWatch();
        return;
    }

    // Fallback centre: use pickup coords or world centre
    const centreLat = PICKUP_LAT || 20;
    const centreLng = PICKUP_LNG || 0;

    navMap = L.map('delivery-map', {
        center: [centreLat, centreLng],
        zoom: PICKUP_LAT ? 13 : 2,
        zoomControl: true,
        attributionControl: true
    });

    // OpenStreetMap tile layer (free, no API key)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
    }).addTo(navMap);

    // ---------- Pickup Marker ----------
    if (PICKUP_LAT && PICKUP_LNG) {
        const pickupIcon = L.divIcon({
            className: '',
            html: `<div class="map-marker pickup-marker"><i class="feather-navigation"></i></div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 36]
        });
        L.marker([PICKUP_LAT, PICKUP_LNG], { icon: pickupIcon })
            .addTo(navMap)
            .bindPopup(`<b>📦 Pickup Point</b><br><small>{{ $assignment->pickup_location }}</small>`);
    }

    // ---------- Drop / Delivery Marker ----------
    if (DROP_LAT && DROP_LNG) {
        const dropIcon = L.divIcon({
            className: '',
            html: `<div class="map-marker drop-marker"><i class="feather-map-pin"></i></div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 36]
        });
        L.marker([DROP_LAT, DROP_LNG], { icon: dropIcon })
            .addTo(navMap)
            .bindPopup(`<b>🏁 Delivery Destination</b><br><small>{{ $assignment->drop_location }}</small>`);
    }

    // Fit map bounds to show both markers
    if (PICKUP_LAT && PICKUP_LNG && DROP_LAT && DROP_LNG) {
        navMap.fitBounds(
            [[PICKUP_LAT, PICKUP_LNG], [DROP_LAT, DROP_LNG]],
            { padding: [48, 48] }
        );
        // Fetch OSRM driving route
        fetchOSRMRoute(PICKUP_LAT, PICKUP_LNG, DROP_LAT, DROP_LNG);
    }

    mapInitialised = true;
    startGeoWatch();
}

/**
 * Fetch a driving route from OSRM (free, open-source routing engine)
 * and draw it on the map as a styled polyline.
 */
function fetchOSRMRoute(fromLat, fromLng, toLat, toLng) {
    const url = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                console.warn('OSRM route not found:', data);
                setNavStats('N/A', 'N/A');
                return;
            }
            const route = data.routes[0];

            // Draw route polyline
            if (navRouteLayer) navMap.removeLayer(navRouteLayer);
            navRouteLayer = L.geoJSON(route.geometry, {
                style: {
                    color: '#3b82f6',
                    weight: 5,
                    opacity: 0.85,
                    lineCap: 'round',
                    lineJoin: 'round',
                    dashArray: null
                }
            }).addTo(navMap);

            // Distance & duration
            const distKm = (route.distance / 1000).toFixed(1);
            const durationMin = Math.round(route.duration / 60);
            const durationText = durationMin >= 60
                ? `${Math.floor(durationMin / 60)}h ${durationMin % 60}m`
                : `${durationMin} min`;

            setNavStats(`${distKm} km`, durationText);
        })
        .catch(err => {
            console.warn('OSRM fetch error (offline or CORS):', err);
            // Fallback: draw straight dashed line
            if (navRouteLayer) navMap.removeLayer(navRouteLayer);
            navRouteLayer = L.polyline(
                [[fromLat, fromLng], [toLat, toLng]],
                { color: '#3b82f6', weight: 3, dashArray: '8 6', opacity: 0.7 }
            ).addTo(navMap);
            setNavStats('See map', 'N/A (offline)');
        });
}

/**
 * Update the stats strip with computed distance and travel time.
 */
function setNavStats(distance, duration) {
    const distEl = document.getElementById('nav-distance');
    const durEl  = document.getElementById('nav-duration');
    if (distEl) distEl.textContent = distance;
    if (durEl)  durEl.textContent  = duration;
}

/**
 * Start watching the driver's GPS location and update the map in real time.
 */
/**
 * Start watching the driver's GPS location and update the map/server in real time.
 */
let lastSentLat = null;
let lastSentLng = null;
let lastSentTime = 0;
let lastGpsLat = null;
let lastGpsLng = null;
let lastGpsTime = 0;
let lastGpsAccuracy = null;
let trackingHealthTimer = null;
const MIN_DISTANCE_METERS = 3;
const MIN_TIME_INTERVAL = 5000;
const STATIONARY_HEARTBEAT = 30000;
const GPS_STALE_WARNING = 45000;
let wakeLock = null;

function trackingDebug(message, context = {}) {
    console.log('[DriverTracking]', message, context);
}

function formatElapsed(timestamp) {
    if (!timestamp) return 'No ping yet';
    const seconds = Math.max(0, Math.round((Date.now() - timestamp) / 1000));
    if (seconds < 60) return `${seconds}s ago`;
    return `${Math.round(seconds / 60)}m ago`;
}

function updateDriverTrackingPanel(status, detail, active = null) {
    const statusEl = document.getElementById('driver-tracking-status');
    const detailEl = document.getElementById('driver-tracking-detail');
    const iconEl = document.getElementById('driver-tracking-icon');

    if (statusEl) {
        statusEl.textContent = status;
        statusEl.className = active === true
            ? 'badge bg-soft-success text-success fs-11'
            : (active === false ? 'badge bg-soft-danger text-danger fs-11' : 'badge bg-soft-warning text-warning fs-11');
    }

    if (detailEl && detail) detailEl.textContent = detail;
    if (iconEl) {
        iconEl.className = active === true
            ? 'feather-radio text-success'
            : (active === false ? 'feather-wifi-off text-danger' : 'feather-loader text-warning');
    }
}

function updateLastLocationText(timestamp) {
    const el = document.getElementById('driver-last-location-update');
    if (el) el.textContent = formatElapsed(timestamp);
}

function updateStationaryState(isStationary, distanceMoved = null) {
    const el = document.getElementById('driver-stationary-state');
    if (!el) return;

    if (isStationary) {
        el.textContent = 'Driver is stationary';
        el.className = 'text-warning';
    } else {
        el.textContent = distanceMoved !== null ? `Moving (${distanceMoved.toFixed(1)}m)` : 'Moving';
        el.className = 'text-success';
    }
}

function updateGpsPermissionStatus(value) {
    const el = document.getElementById('driver-gps-permission');
    if (el) el.textContent = value;
}

function startTrackingHealthTimer() {
    if (trackingHealthTimer) return;

    trackingHealthTimer = setInterval(() => {
        updateLastLocationText(lastSentTime || lastGpsTime);

        if (lastGpsTime && Date.now() - lastGpsTime > GPS_STALE_WARNING) {
            updateDriverTrackingPanel(
                'GPS not updating',
                'Tracking may be paused because the screen is locked, the tab is inactive, or battery optimization stopped location updates.',
                false
            );
            trackingDebug('GPS stale warning shown.', { secondsSinceGps: Math.round((Date.now() - lastGpsTime) / 1000) });
        }
    }, 5000);
}

// Haversine formula to compute distance in meters
function calculateDistanceMeters(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Earth's radius in meters
    const phi1 = lat1 * Math.PI / 180;
    const phi2 = lat2 * Math.PI / 180;
    const deltaPhi = (lat2 - lat1) * Math.PI / 180;
    const deltaLambda = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
              Math.cos(phi1) * Math.cos(phi2) *
              Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c; // Distance in meters
}

async function requestWakeLock() {
    try {
        if ('wakeLock' in navigator) {
            wakeLock = await navigator.wakeLock.request('screen');
            trackingDebug('Screen Wake Lock active.');
        }
    } catch (err) {
        trackingDebug('Wake lock request failed.', { message: err.message });
    }
}

// Re-request wake lock when page is focused again
document.addEventListener('visibilitychange', async () => {
    trackingDebug('Page visibility changed.', { visibilityState: document.visibilityState });
    if (wakeLock !== null && document.visibilityState === 'visible') {
        await requestWakeLock();
    }

    if (document.visibilityState !== 'visible') {
        updateDriverTrackingPanel(
            'Tracking may pause',
            'Keep the browser visible when possible. Some phones pause GPS while locked or in the background.',
            null
        );
    }
});

function sendLocationToServer(lat, lng, speed, heading) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Attempt battery API if available
    if (navigator.getBattery) {
        navigator.getBattery().then(function(battery) {
            const batteryLevel = Math.round(battery.level * 100);
            postUpdate(lat, lng, speed, heading, batteryLevel);
        }).catch(() => {
            postUpdate(lat, lng, speed, heading, null);
        });
    } else {
        postUpdate(lat, lng, speed, heading, null);
    }
    
    function postUpdate(lat, lng, speed, heading, batteryLevel) {
        const orderId = "{{ $assignment->id }}";
        trackingDebug('Location API request sent.', { orderId, lat, lng, speed, heading, batteryLevel });
        fetch(`/admin/assignable-orders/${orderId}/location`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                latitude: lat,
                longitude: lng,
                speed: speed,
                heading: heading,
                battery_level: batteryLevel,
                accuracy: lastGpsAccuracy
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                trackingDebug('Location API success.', data);
                lastSentLat = lat;
                lastSentLng = lng;
                lastSentTime = Date.now();
                if (data.broadcasted === false) {
                    updateDriverTrackingPanel('Saved, broadcast offline', 'Location was saved. Start Laravel Reverb for instant manager updates; manager polling can still refresh it.', null);
                } else {
                    updateDriverTrackingPanel('Live', 'Location is being sent to the manager.', true);
                }
                updateLastLocationText(lastSentTime);
            } else {
                trackingDebug('Location API rejected.', data);
                updateDriverTrackingPanel('API rejected', data.message || 'Server rejected the location update.', false);
            }
        })
        .catch(err => {
            trackingDebug('Location API failed.', { message: err.message });
            updateDriverTrackingPanel('Offline queueing', 'Network failed. Latest location will retry when the browser is online.', false);
            queueOfflineLocation(lat, lng, speed, heading);
        });
    }
}

function queueOfflineLocation(lat, lng, speed, heading) {
    try {
        let offlineQueue = JSON.parse(localStorage.getItem('offline_locations') || '[]');
        offlineQueue.push({
            latitude: lat,
            longitude: lng,
            speed: speed,
            heading: heading,
            timestamp: new Date().toISOString()
        });
        if (offlineQueue.length > 100) offlineQueue.shift();
        localStorage.setItem('offline_locations', JSON.stringify(offlineQueue));
    } catch (e) {
        console.warn('Failed to queue location offline:', e);
    }
}

function syncOfflineLocations() {
    try {
        const offlineQueue = JSON.parse(localStorage.getItem('offline_locations') || '[]');
        if (offlineQueue.length === 0) return;
        if (!navigator.onLine) return;
        
        console.log('Syncing ' + offlineQueue.length + ' offline location coordinates...');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const orderId = "{{ $assignment->id }}";
        const latestOffline = offlineQueue[offlineQueue.length - 1];
        
        fetch(`/admin/assignable-orders/${orderId}/location`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                latitude: latestOffline.latitude,
                longitude: latestOffline.longitude,
                speed: latestOffline.speed,
                heading: latestOffline.heading
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                localStorage.removeItem('offline_locations');
                console.log('Offline queue synced successfully.');
            }
        })
        .catch(err => {
            console.warn('Sync failed, will retry later:', err);
        });
    } catch (e) {
        console.warn('Offline sync error:', e);
    }
}

window.addEventListener('online', syncOfflineLocations);
setInterval(syncOfflineLocations, 30000);

function startGeoWatch() {
    if (!navigator.geolocation) {
        updateGpsStatus('Not supported', false);
        updateGpsPermissionStatus('Not supported');
        updateDriverTrackingPanel('GPS unavailable', 'This browser does not support geolocation.', false);
        trackingDebug('Geolocation is not supported.');
        return;
    }

    if (geoWatchId !== null) {
        trackingDebug('Tracking start skipped because watchPosition is already running.', { geoWatchId });
        return; // Already active
    }

    updateGpsStatus('Acquiring...', null);
    updateGpsPermissionStatus('Requesting');
    updateDriverTrackingPanel('Starting GPS', 'Waiting for the first location coordinate.', null);
    requestWakeLock();
    startTrackingHealthTimer();

    if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({ name: 'geolocation' })
            .then(permission => {
                updateGpsPermissionStatus(permission.state);
                trackingDebug('GPS permission status.', { state: permission.state });
                permission.onchange = () => {
                    updateGpsPermissionStatus(permission.state);
                    trackingDebug('GPS permission changed.', { state: permission.state });
                };
            })
            .catch(error => trackingDebug('GPS permission query failed.', { message: error.message }));
    }

    trackingDebug('Tracking started.');

    geoWatchId = navigator.geolocation.watchPosition(
        function(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const accuracy = Math.round(pos.coords.accuracy);
            const speed = pos.coords.speed; // m/s
            const speedKmh = speed !== null ? (speed * 3.6) : null;
            const heading = pos.coords.heading;
            const previousGpsLat = lastGpsLat;
            const previousGpsLng = lastGpsLng;
            const movedSinceLastGps = previousGpsLat !== null && previousGpsLng !== null
                ? calculateDistanceMeters(previousGpsLat, previousGpsLng, lat, lng)
                : null;

            lastGpsLat = lat;
            lastGpsLng = lng;
            lastGpsTime = Date.now();
            lastGpsAccuracy = accuracy;

            updateGpsStatus(`Active (±${accuracy}m)`, true);
            
            updateGpsPermissionStatus('granted');
            updateDriverTrackingPanel('GPS active', `Coordinate received with about ${accuracy}m accuracy.`, true);
            updateLastLocationText(lastGpsTime);
            updateStationaryState(movedSinceLastGps !== null && movedSinceLastGps < MIN_DISTANCE_METERS, movedSinceLastGps);
            trackingDebug('GPS coordinate received.', {
                lat,
                lng,
                accuracy,
                speedKmh,
                heading,
                movedSinceLastGps
            });

            if (mapInitialised && navMap) {
                updateDriverMarker(lat, lng);
                if (DROP_LAT && DROP_LNG) {
                    fetchOSRMRoute(lat, lng, DROP_LAT, DROP_LNG);
                }
            }

            const now = Date.now();
            const timeDiff = now - lastSentTime;
            let shouldSend = false;

            if (lastSentLat === null || lastSentLng === null) {
                shouldSend = true;
            } else {
                const distanceMoved = calculateDistanceMeters(lastSentLat, lastSentLng, lat, lng);
                if (timeDiff >= MIN_TIME_INTERVAL && (distanceMoved >= MIN_DISTANCE_METERS || (speedKmh && speedKmh > 2))) {
                    shouldSend = true;
                } else if (timeDiff >= STATIONARY_HEARTBEAT) {
                    shouldSend = true;
                }
            }

            if (shouldSend) {
                sendLocationToServer(lat, lng, speedKmh, heading);
            } else {
                trackingDebug('Location API request skipped by movement throttle.', {
                    secondsSinceLastSent: Math.round(timeDiff / 1000),
                    minIntervalSeconds: MIN_TIME_INTERVAL / 1000
                });
            }
        },
        function(err) {
            trackingDebug('Geolocation error.', { code: err.code, message: err.message });
            updateGpsStatus('Unavailable', false);
            updateGpsPermissionStatus(err.code === err.PERMISSION_DENIED ? 'denied' : 'unavailable');
            updateDriverTrackingPanel('GPS unavailable', err.message || 'Location permission or GPS signal is unavailable.', false);
        },
        { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 }
    );

    trackingDebug('watchPosition registered.', { geoWatchId });
}

function stopGeoWatch() {
    // Keep tracking active in background when the user closes the Leaflet map panel
    trackingDebug('Background tracking remains active after closing the map panel.');
}

// Clean up geo watch on window unload
window.addEventListener('beforeunload', function() {
    if (geoWatchId !== null) {
        navigator.geolocation.clearWatch(geoWatchId);
        geoWatchId = null;
    }
});

/**
 * Place or update the animated driver marker on the map.
 */
function updateDriverMarker(lat, lng) {
    if (!navMap) return;

    if (!driverMarker) {
        const driverIcon = L.divIcon({
            className: '',
            html: `<div class="map-marker driver-marker"><div class="driver-pulse"></div><i class="feather-navigation"></i></div>`,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
        driverMarker = L.marker([lat, lng], { icon: driverIcon, zIndexOffset: 1000 })
            .addTo(navMap)
            .bindPopup('<b>🚗 Your Location</b><br><small>Live GPS</small>');
    } else {
        driverMarker.setLatLng([lat, lng]);
    }

    trackingDebug('Driver marker updated on driver map.', { lat, lng });
}

/**
 * Update the GPS status label and icon in the stats strip.
 */
function updateGpsStatus(text, active) {
    const el   = document.getElementById('nav-gps-status');
    const icon = document.getElementById('gps-status-icon');
    if (el) el.textContent = text;
    if (icon) {
        icon.className = active === true
            ? 'feather-radio text-success'
            : (active === false ? 'feather-wifi-off text-danger' : 'feather-loader text-muted');
    }
}

function triggerCamera() {
    document.getElementById('camera-input').click();
}

function triggerGallery() {
    document.getElementById('gallery-input').click();
}

function startWebcam() {
    const video = document.getElementById('webcam-preview');
    const webcamContainer = document.getElementById('webcam-container');
    const fallbackContainer = document.getElementById('fallback-upload-container');
    const selectionButtons = document.getElementById('selection-buttons');
    
    if (fallbackContainer) fallbackContainer.style.display = 'none';

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(function(stream) {
                webcamStream = stream;
                video.srcObject = stream;
                webcamContainer.style.display = 'block';
                selectionButtons.style.display = 'none';
            })
            .catch(function(err) {
                console.warn("Webcam access denied or unavailable, falling back to upload container: ", err);
                showUploadFallback();
            });
    } else {
        console.warn("navigator.mediaDevices not supported in this browser, falling back.");
        showUploadFallback();
    }
}

function showUploadFallback() {
    const webcamContainer = document.getElementById('webcam-container');
    const fallbackContainer = document.getElementById('fallback-upload-container');
    const selectionButtons = document.getElementById('selection-buttons');
    
    if (webcamContainer) webcamContainer.style.display = 'none';
    if (fallbackContainer) fallbackContainer.style.display = 'block';
    if (selectionButtons) selectionButtons.style.display = 'none';
    
    // Programmatically trigger the click as a best effort
    try {
        document.getElementById('gallery-input').click();
    } catch(e) {
        console.warn("Programmatic click blocked by browser popup settings: ", e);
    }
}

function stopWebcam() {
    const video = document.getElementById('webcam-preview');
    const webcamContainer = document.getElementById('webcam-container');
    const fallbackContainer = document.getElementById('fallback-upload-container');
    const selectionButtons = document.getElementById('selection-buttons');
    
    if (webcamStream) {
        webcamStream.getTracks().forEach(track => track.stop());
        webcamStream = null;
    }
    if (video) {
        video.srcObject = null;
    }
    if (webcamContainer) {
        webcamContainer.style.display = 'none';
    }
    if (fallbackContainer) {
        fallbackContainer.style.display = 'none';
    }
    if (selectionButtons) {
        selectionButtons.style.display = 'flex';
    }
}

function captureWebcamFrame() {
    const video = document.getElementById('webcam-preview');
    if (!video || !video.srcObject) return;
    
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    
    const ctx = canvas.getContext('2d');
    
    // Draw mirrored image if scaleX(-1) was used
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    canvas.toBlob(function(blob) {
        if (blob) {
            const file = new File([blob], "captured_proof_" + Date.now() + ".jpg", { type: "image/jpeg" });
            selectedFilesArray.push(file);
            renderPreviews();
            stopWebcam();
        }
    }, 'image/jpeg', 0.9);
}

function renderPreviews() {
    const previewContainer = document.getElementById('proof-previews');
    const submitBtn = document.getElementById('submit-delivery-btn');
    
    if (!previewContainer) return;
    
    previewContainer.innerHTML = '';
    
    if (selectedFilesArray.length === 0) {
        previewContainer.innerHTML = `
            <div class="text-center w-100 py-3 text-muted fs-11.5 id-empty-preview">
                <i class="feather-image fs-4 d-block mb-1"></i>
                Select or capture photo
            </div>
        `;
        if (submitBtn) submitBtn.disabled = true;
        return;
    }

    if (submitBtn) submitBtn.disabled = false;

    selectedFilesArray.forEach((file, index) => {
        const cardWrapper = document.createElement('div');
        cardWrapper.className = 'position-relative border rounded p-1 bg-white shadow-sm';
        cardWrapper.style.width = '70px';
        cardWrapper.style.height = '70px';

        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.className = 'w-100 h-100 rounded';
        img.style.objectFit = 'cover';

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'position-absolute bg-danger text-white border-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm';
        removeBtn.style.top = '-5px';
        removeBtn.style.right = '-5px';
        removeBtn.style.width = '20px';
        removeBtn.style.height = '20px';
        removeBtn.style.fontSize = '12px';
        removeBtn.style.cursor = 'pointer';
        removeBtn.innerHTML = '<i class="feather-x"></i>';

        removeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            selectedFilesArray.splice(index, 1);
            renderPreviews();
        });

        cardWrapper.appendChild(img);
        cardWrapper.appendChild(removeBtn);
        previewContainer.appendChild(cardWrapper);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Automatically start tracking if order is Picked Up (in status 'Pickup')
    const ORDER_STATUS = "{{ $assignment->status }}";
    if (ORDER_STATUS === 'Pickup') {
        startGeoWatch();
    }

    const cameraInput = document.getElementById('camera-input');
    const galleryInput = document.getElementById('gallery-input');
    const form = document.getElementById('delivery-submit-form');
    const finalFilesInput = document.getElementById('final-files-input');
    const deliveryModalEl = document.getElementById('deliveryModal');

    if (deliveryModalEl) {
        deliveryModalEl.addEventListener('hidden.bs.modal', function () {
            stopWebcam();
        });
    }

    // Handle Camera photo addition
    if (cameraInput) {
        cameraInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                selectedFilesArray.push(e.target.files[0]);
                renderPreviews();
                cameraInput.value = ''; // Reset input
            }
        });
    }

    // Handle Gallery files addition
    if (galleryInput) {
        galleryInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files.length > 0) {
                Array.from(e.target.files).forEach(file => {
                    const typePattern = /\/(jpeg|png|jpg|webp)$/i;
                    if (typePattern.test(file.type)) {
                        selectedFilesArray.push(file);
                    }
                });
                renderPreviews();
                galleryInput.value = ''; // Reset input
            }
        });
    }

    // Intercept form submission and append gathered files
    if (form) {
        form.addEventListener('submit', function(e) {
            if (selectedFilesArray.length === 0) {
                e.preventDefault();
                alert('Please select or capture at least one image before submitting.');
                return;
            }

            const dataTransfer = new DataTransfer();
            selectedFilesArray.forEach(file => {
                dataTransfer.items.add(file);
            });

            finalFilesInput.files = dataTransfer.files;
        });
    }
});
</script>

<style>
/* =========================================================
   ORDER DETAILS PAGE – SHARED COMPONENTS
   ========================================================= */
.order-details-page {
    overflow-x: hidden;
}

/* Route timeline */
.route-timeline {
    position: relative;
    padding-left: 20px;
}
.route-timeline::before {
    content: '';
    position: absolute;
    left: 4px;
    top: 10px;
    bottom: 10px;
    width: 2px;
    border-left: 2px dotted #94a3b8;
}
.timeline-item {
    position: relative;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #3b82f6;
    background: #ffffff;
    z-index: 2;
}
.timeline-item.drop::before {
    border-color: #10b981;
    background: #10b981;
}
html.app-skin-dark .timeline-item::before {
    background: #1e293b;
}
html.app-skin-dark .timeline-item.drop::before {
    background: #10b981;
}

/* Numerical details grid */
.order-detail-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    height: 100%;
}
html.app-skin-dark .order-detail-item {
    background: rgba(15, 23, 42, 0.45);
    border-color: #334155;
}
.order-detail-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(59, 130, 246, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.order-detail-icon i {
    font-size: 16px;
    line-height: 1;
}
.order-detail-icon-success {
    background: rgba(16, 185, 129, 0.12);
}
.order-detail-content {
    min-width: 0;
    flex: 1;
}
.order-detail-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    color: #94a3b8;
    margin-bottom: 4px;
    line-height: 1.2;
}
.order-detail-value {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
    word-break: break-word;
}
html.app-skin-dark .order-detail-value {
    color: #f1f5f9;
}

.company-info-text h6,
.company-info-text span,
.nav-route-addr {
    word-break: break-word;
}

/* =========================================================
   NAVIGATION MAP PANEL STYLES
   ========================================================= */
.nav-map-panel {
    margin-top: 24px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(16, 185, 129, 0.14), 0 2px 8px rgba(0,0,0,0.08);
    border: 1.5px solid rgba(16, 185, 129, 0.22);
    background: #ffffff;
    animation: navPanelSlideIn 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
html.app-skin-dark .nav-map-panel {
    background: #1e293b;
    border-color: rgba(16, 185, 129, 0.28);
}
@keyframes navPanelSlideIn {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

.nav-map-header {
    padding: 20px 20px 16px;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border-bottom: 1px solid rgba(16, 185, 129, 0.18);
}
html.app-skin-dark .nav-map-header {
    background: linear-gradient(135deg, rgba(16,185,129,0.12) 0%, rgba(5,150,105,0.08) 100%);
    border-bottom-color: rgba(16, 185, 129, 0.2);
}

.nav-header-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
}

/* Stats Strip */
.nav-stats-strip {
    display: flex;
    align-items: center;
    gap: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
html.app-skin-dark .nav-stats-strip {
    background: #0f172a;
    border-color: #334155;
}

.nav-stat-item {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    min-width: 0;
}
.nav-stat-item > div {
    min-width: 0;
    flex: 1;
}
.nav-stat-item i {
    font-size: 18px;
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: rgba(59, 130, 246, 0.08);
}
.nav-stat-item:nth-child(1) i { background: rgba(59, 130, 246, 0.1); }
.nav-stat-item:nth-child(3) i { background: rgba(245, 158, 11, 0.12); }
.nav-stat-item:nth-child(5) i { background: rgba(16, 185, 129, 0.12); }
.nav-stat-label {
    display: block;
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #94a3b8;
    margin-bottom: 2px;
}
.nav-stat-value {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
    word-break: break-word;
}
html.app-skin-dark .nav-stat-value {
    color: #f1f5f9;
}
.nav-stat-divider {
    width: 1px;
    height: 36px;
    background: #e2e8f0;
    flex-shrink: 0;
}
html.app-skin-dark .nav-stat-divider {
    background: #334155;
}

/* Route address display */
.nav-route-addresses {
    display: flex;
    flex-direction: column;
    gap: 0;
    padding: 0 4px;
}
.nav-route-point {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.nav-route-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 3px;
    box-shadow: 0 0 0 3px rgba(255,255,255,0.8), 0 0 0 4px currentColor;
}
.pickup-dot  { background: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
.drop-dot    { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
.nav-route-connector {
    width: 1.5px;
    height: 18px;
    background: repeating-linear-gradient(to bottom, #cbd5e1 0px, #cbd5e1 4px, transparent 4px, transparent 8px);
    margin-left: 5.5px;
}
.nav-route-label {
    display: block;
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.4px;
    margin-bottom: 1px;
}
.nav-route-addr {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.35;
}
html.app-skin-dark .nav-route-addr {
    color: #e2e8f0;
}

/* Map Canvas */
.nav-map-canvas {
    width: 100%;
    height: 380px;
    background: #e8f4f8;
}
@media (max-width: 767.98px) {
    .nav-map-canvas { height: 280px; }

    .nav-map-header {
        padding: 16px 14px 14px;
    }

    .nav-map-header-top {
        flex-direction: column;
        align-items: stretch !important;
    }

    .nav-map-title-group {
        width: 100%;
    }

    .nav-map-header-actions {
        width: 100%;
    }

    .nav-map-header-actions .btn {
        flex: 1;
        min-height: 38px;
        white-space: nowrap;
    }

    .nav-stats-strip {
        flex-direction: column;
        align-items: stretch;
        overflow: visible;
    }

    .nav-stat-divider {
        display: none;
    }

    .nav-stat-item {
        width: 100%;
        padding: 14px 16px;
        gap: 14px;
        border-bottom: 1px solid #e2e8f0;
    }

    html.app-skin-dark .nav-stat-item {
        border-bottom-color: #334155;
    }

    .nav-stat-item:last-child {
        border-bottom: none;
    }

    .nav-stat-item i {
        width: 40px;
        height: 40px;
        font-size: 17px;
    }

    .nav-stat-label {
        font-size: 10px;
        margin-bottom: 3px;
    }

    .nav-stat-value {
        font-size: 13px;
    }

    .nav-map-legend {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 12px;
        padding: 12px 14px;
    }

    .nav-route-addresses {
        padding: 0;
    }

    .nav-route-addr {
        font-size: 11.5px;
    }
}

/* Map Legend */
.nav-map-legend {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    padding: 10px 16px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    font-size: 10.5px;
    font-weight: 600;
    color: #64748b;
}
html.app-skin-dark .nav-map-legend {
    background: #1e293b;
    border-top-color: #334155;
    color: #94a3b8;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
}
.legend-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
.legend-line {
    display: inline-block;
    width: 18px;
    height: 3px;
    background: #3b82f6;
    border-radius: 2px;
}

/* Custom Leaflet Markers */
.map-marker {
    width: 36px;
    height: 36px;
    border-radius: 50% 50% 50% 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.25);
    transform: rotate(-45deg);
    border: 2.5px solid rgba(255,255,255,0.9);
    transition: transform 0.2s ease;
}
.map-marker i {
    transform: rotate(45deg);
}
.pickup-marker {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}
.drop-marker {
    background: linear-gradient(135deg, #10b981, #047857);
}
.driver-marker {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    transform: none;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    position: relative;
    overflow: visible;
    box-shadow: 0 4px 14px rgba(245,158,11,0.4);
}
.driver-marker i {
    transform: none;
    font-size: 16px;
}
.driver-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(245, 158, 11, 0.4);
    animation: driverPulse 1.8s ease-out infinite;
    top: 0;
    left: 0;
}
@keyframes driverPulse {
    0%   { transform: scale(1);   opacity: 0.8; }
    70%  { transform: scale(2.2); opacity: 0; }
    100% { transform: scale(2.2); opacity: 0; }
}

/* Navigate Toggle Button Hover State */
#navigate-toggle-btn:hover {
    box-shadow: 0 6px 20px rgba(16,185,129,0.45) !important;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

/* Premium Stepper Progress Tracker */
.stepper-container {
    position: relative;
    padding: 12px 10px;
    background: rgba(248, 250, 252, 0.6);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
html.app-skin-dark .stepper-container {
    background: rgba(15, 23, 42, 0.35);
    border: 1px solid #334155;
}

.stepper-line-bg {
    position: absolute;
    height: 4px;
    background: #cbd5e1;
    z-index: 1;
    border-radius: 4px;
}
html.app-skin-dark .stepper-line-bg {
    background: #475569;
}

.stepper-line-active {
    position: absolute;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6 0%, #f59e0b 50%, #10b981 100%);
    z-index: 1;
    border-radius: 4px;
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.stepper-wrapper {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 2;
}

.stepper-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.stepper-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #cbd5e1;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    border: 2.5px solid #ffffff;
    box-shadow: 0 0 0 2px #cbd5e1;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
html.app-skin-dark .stepper-icon {
    background: #475569;
    color: #94a3b8;
    border-color: #1e293b;
    box-shadow: 0 0 0 2px #475569;
}

/* Status-specific states */
.stepper-item.active .stepper-icon {
    background: #3b82f6;
    color: #ffffff;
    border-color: #ffffff;
    box-shadow: 0 0 0 2px #3b82f6, 0 0 8px rgba(59, 130, 246, 0.5);
    transform: scale(1.1);
}
html.app-skin-dark .stepper-item.active .stepper-icon {
    border-color: #1e293b;
    box-shadow: 0 0 0 2px #3b82f6, 0 0 8px rgba(59, 130, 246, 0.5);
}

.stepper-item.active-warning .stepper-icon {
    background: #f59e0b;
    color: #ffffff;
    border-color: #ffffff;
    box-shadow: 0 0 0 2px #f59e0b, 0 0 8px rgba(245, 158, 11, 0.5);
    transform: scale(1.1);
}
html.app-skin-dark .stepper-item.active-warning .stepper-icon {
    border-color: #1e293b;
    box-shadow: 0 0 0 2px #f59e0b, 0 0 8px rgba(245, 158, 11, 0.5);
}

.stepper-item.active-success .stepper-icon {
    background: #10b981;
    color: #ffffff;
    border-color: #ffffff;
    box-shadow: 0 0 0 2px #10b981, 0 0 8px rgba(16, 185, 129, 0.5);
    transform: scale(1.1);
}
html.app-skin-dark .stepper-item.active-success .stepper-icon {
    border-color: #1e293b;
    box-shadow: 0 0 0 2px #10b981, 0 0 8px rgba(16, 185, 129, 0.5);
}

.stepper-label {
    font-size: 9px;
    font-weight: 800;
    color: #64748b;
    margin-top: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    transition: color 0.3s ease;
}
html.app-skin-dark .stepper-label {
    color: #94a3b8;
}

.stepper-item.active .stepper-label {
    color: #3b82f6;
}
.stepper-item.active-warning .stepper-label {
    color: #f59e0b;
}
.stepper-item.active-success .stepper-label {
    color: #10b981;
}

@media (max-width: 767.98px) {
    /* Reduce page container paddings to 12px margins */
    .nxl-container {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .main-content {
        padding-top: 12px !important;
        padding-bottom: 12px !important;
    }
    .container-fluid {
        padding-left: 12px !important;
        padding-right: 12px !important;
        max-width: 100% !important;
    }

    .page-detail-header {
        width: 100%;
    }

    .order-id-badge {
        font-size: 13px !important;
    }
    
    /* Optimize the details page cards paddings on mobile */
    .card {
        border-radius: 12px !important;
    }
    .card .card-body {
        padding: 16px !important;
    }
    .card .card-header, .card .card-footer {
        padding: 12px 16px !important;
    }

    .order-detail-item {
        padding: 12px;
        gap: 10px;
    }

    .order-detail-icon {
        width: 34px;
        height: 34px;
    }

    .order-detail-value {
        font-size: 12.5px;
    }

    .card-footer .btn-lg {
        font-size: 12px !important;
        padding-top: 0.85rem !important;
        padding-bottom: 0.85rem !important;
        letter-spacing: 0.3px !important;
    }

    .nav-map-panel {
        margin-top: 16px;
        border-radius: 16px;
    }

    #deliveryModal .modal-footer {
        flex-wrap: wrap;
        gap: 8px;
    }

    #deliveryModal .modal-footer .btn {
        flex: 1 1 auto;
        min-width: 120px;
    }
    
    /* Adapt details progress stepper for mobile screens */
    .stepper-container {
        padding: 8px 6px !important;
    }
    .stepper-line-bg {
        left: 20px !important;
        right: 20px !important;
        top: 20px !important;
        height: 3px !important;
    }
    .stepper-line-active {
        left: 20px !important;
        top: 20px !important;
        height: 3px !important;
    }
    .stepper-item {
        width: 52px !important;
    }
    .stepper-icon {
        width: 22px !important;
        height: 22px !important;
        font-size: 8.5px !important;
        border-width: 2px !important;
        box-shadow: 0 0 0 1.5px #cbd5e1 !important;
    }
    html.app-skin-dark .stepper-icon {
        box-shadow: 0 0 0 1.5px #475569 !important;
    }
    .stepper-item.active .stepper-icon {
        box-shadow: 0 0 0 1.5px #3b82f6, 0 0 6px rgba(59, 130, 246, 0.4) !important;
    }
    .stepper-item.active-warning .stepper-icon {
        box-shadow: 0 0 0 1.5px #f59e0b, 0 0 6px rgba(245, 158, 11, 0.5) !important;
    }
    .stepper-item.active-success .stepper-icon {
        box-shadow: 0 0 0 1.5px #10b981, 0 0 6px rgba(16, 185, 129, 0.5) !important;
    }
    .stepper-label {
        font-size: 8px !important;
        margin-top: 4px !important;
    }
}

@media (max-width: 575.98px) {
    .order-details-grid {
        --bs-gutter-y: 0.75rem;
    }
}
</style>
@endsection
