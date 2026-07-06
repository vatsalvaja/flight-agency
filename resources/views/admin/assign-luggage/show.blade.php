@extends('layouts.admin')

@section('title', 'Assignment Details || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content assign-luggage-show">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Assign Luggage</h5>
            </div>
            <ul class="breadcrumb d-none d-md-flex">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assign-luggage.index') }}">Assign Luggage</a></li>
                <li class="breadcrumb-item">Assignment Details</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-inline-flex gap-2">
                <a href="{{ route('assign-luggage.index') }}" class="btn btn-light">
                    <i class="feather-arrow-left me-2"></i>Back to List
                </a>
                <a href="{{ route('assign-luggage.edit', $assignment->id) }}" class="btn btn-primary">
                    <i class="feather-edit me-2"></i>Edit Assignment
                </a>
            </div>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <!-- Details Card -->
            <div class="col-lg-8 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Luggage Assignment Info</h5>
                        @if($assignment->status === 'Pickup')
                            <span class="badge bg-soft-warning text-warning px-3 py-2 fs-12 fw-semibold">Pickup</span>
                        @elseif($assignment->status === 'In Progress')
                            <span class="badge bg-soft-info text-info px-3 py-2 fs-12 fw-semibold">In Progress</span>
                        @else
                            <span class="badge bg-soft-success text-success px-3 py-2 fs-12 fw-semibold">Delivered</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3" style="width: 30%;">Flight Company</td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                @if($assignment->company->logo)
                                                    <img src="{{ asset($assignment->company->logo) }}" alt="logo" class="rounded me-2" style="height: 32px; width: 32px; object-fit: cover;">
                                                @else
                                                    <div class="avatar-text avatar-sm bg-soft-primary text-primary rounded me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 700; font-size: 13px;">
                                                        {{ substr($assignment->company->company_name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <span class="fw-bold text-dark">{{ $assignment->company->company_name }} ({{ $assignment->company->company_code }})</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Station</td>
                                        <td class="py-3 fw-medium text-dark">{{ $assignment->station->station_name }} ({{ $assignment->station->station_code }})</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Pickup Location</td>
                                        <td class="py-3">
                                            <div class="text-dark fw-medium">{{ $assignment->pickup_location }}</div>
                                            @if($assignment->pickup_latitude && $assignment->pickup_longitude)
                                                <span class="fs-11 text-muted">Coords: {{ $assignment->pickup_latitude }}, {{ $assignment->pickup_longitude }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Drop Location</td>
                                        <td class="py-3">
                                            <div class="text-dark fw-medium">{{ $assignment->drop_location }}</div>
                                            @if($assignment->drop_latitude && $assignment->drop_longitude)
                                                <span class="fs-11 text-muted">Coords: {{ $assignment->drop_latitude }}, {{ $assignment->drop_longitude }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Distance (Driving)</td>
                                        <td class="py-3"><span class="badge bg-soft-secondary text-dark fs-12 px-3 py-1 fw-bold">{{ $assignment->distance_km ?? '0.00' }} km</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Expected Delivery Date</td>
                                        <td class="py-3 fw-bold text-dark">{{ $assignment->expected_delivery_date->format('l, F d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Assigned Driver</td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                @if($assignment->driver->profile_photo)
                                                    <img src="{{ asset($assignment->driver->profile_photo) }}" alt="avatar" class="rounded-circle me-2" style="height: 32px; width: 32px; object-fit: cover;">
                                                @else
                                                    <div class="avatar-text avatar-sm bg-soft-info text-info rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 700; font-size: 13px;">
                                                        {{ $assignment->driver->getInitials() }}
                                                    </div>
                                                @endif
                                                <span class="fw-semibold text-dark">{{ $assignment->driver->name }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Assigned By</td>
                                        <td class="py-3 text-muted">
                                            {{ $assignment->creator->name ?? 'N/A' }} on {{ $assignment->created_at->format('M d, Y H:i A') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if($assignment->status === 'Pickup' || $assignment->status === 'Delivered')
                <!-- Real-Time Map Card -->
                <div class="card mt-4" style="border-radius: 16px; overflow: hidden;">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3 px-4 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-text avatar-sm bg-soft-warning text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="feather-map-pin"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0 fw-extrabold text-dark">Live Delivery Tracking</h5>
                                <span class="text-muted fs-11" id="tracking-subtitle">
                                    {{ $assignment->status === 'Pickup' ? 'Real-time driver location updates' : 'Completed delivery route' }}
                                </span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-12 fw-bold text-muted d-none d-sm-inline" id="last-ping-hud">Loading telemetry...</span>
                            <span class="badge bg-soft-secondary text-secondary px-2.5 py-1.5 fs-11" id="driver-status-badge">Offline</span>
                        </div>
                    </div>
                    <div class="card-body p-0 position-relative">
                        <!-- HUD Overlay Stats -->
                        <div class="map-hud-overlay">
                            <div class="hud-stat-item">
                                <span class="hud-label">SPEED</span>
                                <span class="hud-val" id="hud-speed">0.0 km/h</span>
                            </div>
                            <div class="hud-divider"></div>
                            <div class="hud-stat-item">
                                <span class="hud-label">ETA</span>
                                <span class="hud-val" id="hud-eta">Calculating...</span>
                            </div>
                            <div class="hud-divider"></div>
                            <div class="hud-stat-item">
                                <span class="hud-label">DISTANCE</span>
                                <span class="hud-val" id="hud-distance">0.0 km</span>
                            </div>
                            <div class="hud-divider d-none d-sm-block"></div>
                            <div class="hud-stat-item d-none d-sm-block">
                                <span class="hud-label">BATTERY</span>
                                <span class="hud-val" id="hud-battery">--%</span>
                            </div>
                        </div>
                        
                        <!-- Map container -->
                        <div id="manager-tracking-map" style="width: 100%; height: 420px; background: #eaeaea;"></div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Side Card: Notes & Images -->
            <div class="col-lg-4 mb-4">
                <div class="row g-4 h-100">
                    <!-- Notes card -->
                    <div class="col-12">
                        <div class="card stretch stretch-full" style="min-height: 180px;">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Custom Notes</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-dark bg-light p-3 rounded border" style="white-space: pre-wrap; font-size: 0.85rem;">{{ $assignment->notes ?? 'No custom notes provided.' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Images card -->
                    <div class="col-12 flex-grow-1">
                        <div class="card stretch stretch-full h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Uploaded Images ({{ count($assignment->images ?? []) }})</h5>
                            </div>
                            <div class="card-body">
                                @if($assignment->images && count($assignment->images) > 0)
                                    <div class="row g-2">
                                        @foreach($assignment->images as $img)
                                            <div class="col-4">
                                                <a href="{{ asset($img) }}" target="_blank" class="d-block border rounded p-1 hover-image" style="height: 90px; overflow: hidden; background: #fff;">
                                                    <img src="{{ asset($img) }}" class="w-100 h-100 rounded" style="object-fit: cover; transition: transform 0.2s ease;">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5 text-muted">
                                        <i class="feather-image fs-1 d-block mb-2 text-muted"></i>
                                        No images uploaded.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>

<style>
.hover-image img:hover {
    transform: scale(1.05);
}

/* Real-Time Map HUD Overlay */
.map-hud-overlay {
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 5;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    padding: 10px 18px;
    gap: 15px;
    pointer-events: none; /* Let clicks pass through to map */
}
html.app-skin-dark .map-hud-overlay {
    background: rgba(15, 23, 42, 0.95);
    border-color: rgba(51, 65, 85, 0.8);
}
.hud-stat-item {
    display: flex;
    flex-direction: column;
}
.hud-label {
    font-size: 9px;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}
.hud-val {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
}
html.app-skin-dark .hud-val {
    color: #f1f5f9;
}
.hud-divider {
    width: 1px;
    height: 24px;
    background: #e2e8f0;
}
html.app-skin-dark .hud-divider {
    background: #334155;
}
</style>

@if($assignment->status === 'Pickup' || $assignment->status === 'Delivered')
@push('scripts')
<script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
@if(env('GOOGLE_MAPS_API_KEY'))
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,geometry"></script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Echo
    if (typeof Echo !== 'undefined') {
        window.LaravelEcho = new Echo({
            broadcaster: 'reverb',
            key: '{{ env("REVERB_APP_KEY") }}',
            wsHost: '{{ env("REVERB_HOST") ?: "127.0.0.1" }}',
            wsPort: {{ env("REVERB_PORT") ?: 8080 }},
            wssPort: {{ env("REVERB_PORT") ?: 8080 }},
            forceTLS: {{ env("REVERB_SCHEME") === 'https' ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss'],
        });
    }

    let map = null;
    let driverMarker = null;
    let pickupMarker = null;
    let destinationMarker = null;
    let historyPolyline = null;
    let directionsRenderer = null;
    let directionsService = null;
    let lastPingTime = 0;
    let offlineTimer = null;
    let trackingRouteHistory = [];
    
    const orderId = "{{ $assignment->id }}";

    // Haversine distance helper
    function getDistance(p1, p2) {
        const R = 6371e3; // Earth radius in meters
        const dLat = (p2.lat() - p1.lat()) * Math.PI / 180;
        const dLng = (p2.lng() - p1.lng()) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(p1.lat() * Math.PI / 180) * Math.cos(p2.lat() * Math.PI / 180) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Google Maps marker animation helper
    function interpolateMarker(marker, startPos, endPos, duration) {
        const startTime = performance.now();
        function step(timestamp) {
            const elapsed = timestamp - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // easeInOutQuad
            const ease = progress < 0.5 ? 2 * progress * progress : -1 + (4 - 2 * progress) * progress;
            
            const currentLat = startPos.lat() + (endPos.lat() - startPos.lat()) * ease;
            const currentLng = startPos.lng() + (endPos.lng() - startPos.lng()) * ease;
            const currentPos = new google.maps.LatLng(currentLat, currentLng);
            
            marker.setPosition(currentPos);
            
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }
        requestAnimationFrame(step);
    }

    // Main map initializer
    function initTracking() {
        fetch(`/admin/assign-luggage/${orderId}/tracking-data`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    console.warn(data.message);
                    return;
                }
                renderMap(data);
            })
            .catch(err => console.error('Failed to load tracking data:', err));
    }

    function renderMap(data) {
        const mapContainer = document.getElementById('manager-tracking-map');
        if (!mapContainer || typeof google === 'undefined') return;

        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: '#3b82f6',
                strokeOpacity: 0.6,
                strokeWeight: 4,
                style: google.maps.StrokeStyle ? google.maps.StrokeStyle.DASHED : {}
            }
        });

        // Fallback center
        const defaultCenter = new google.maps.LatLng(
            data.pickup.lat || 20,
            data.pickup.lng || 0
        );

        map = new google.maps.Map(mapContainer, {
            center: defaultCenter,
            zoom: 13,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
            styles: [
                {
                    "featureType": "poi",
                    "elementType": "labels",
                    "stylers": [{ "visibility": "off" }]
                }
            ]
        });

        directionsRenderer.setMap(map);

        // 1. Pickup Marker
        if (data.pickup.lat && data.pickup.lng) {
            const pickupPos = new google.maps.LatLng(data.pickup.lat, data.pickup.lng);
            pickupMarker = new google.maps.Marker({
                position: pickupPos,
                map: map,
                title: "Pickup Point: " + data.pickup.address,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#3b82f6',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2,
                    scale: 8
                }
            });
        }

        // 2. Destination Marker
        if (data.destination.lat && data.destination.lng) {
            const destPos = new google.maps.LatLng(data.destination.lat, data.destination.lng);
            destinationMarker = new google.maps.Marker({
                position: destPos,
                map: map,
                title: "Delivery Point: " + data.destination.address,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#10b981',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2,
                    scale: 9
                }
            });
        }

        // 3. Driver Location Marker
        if (data.last_location) {
            updateDriverMarkerState(
                data.last_location.lat,
                data.last_location.lng,
                data.last_location.heading,
                data.last_location.speed,
                data.last_location.battery_level,
                data.last_location.updated_at
            );
            
            // Mark online since we have a valid last location within 60s
            const lastUpdate = new Date(data.last_location.updated_at).getTime();
            if (Date.now() - lastUpdate < 60000) {
                setOnlineBadge(true);
            }
        }

        // 4. Traveled Route History Polyline
        if (data.route_history && data.route_history.length > 0) {
            trackingRouteHistory = data.route_history.map(pt => new google.maps.LatLng(pt.lat, pt.lng));
            historyPolyline = new google.maps.Polyline({
                path: trackingRouteHistory,
                geodesic: true,
                strokeColor: '#f59e0b',
                strokeOpacity: 0.9,
                strokeWeight: 5,
                map: map
            });
        }

        // 5. Fit Map Bounds
        fitMapBounds(data);

        // 6. Draw Directions
        calculateDirections(data);
    }

    function fitMapBounds(data) {
        const bounds = new google.maps.LatLngBounds();
        let hasBounds = false;

        if (data.pickup.lat && data.pickup.lng) {
            bounds.extend(new google.maps.LatLng(data.pickup.lat, data.pickup.lng));
            hasBounds = true;
        }
        if (data.destination.lat && data.destination.lng) {
            bounds.extend(new google.maps.LatLng(data.destination.lat, data.destination.lng));
            hasBounds = true;
        }
        if (data.last_location) {
            bounds.extend(new google.maps.LatLng(data.last_location.lat, data.last_location.lng));
            hasBounds = true;
        }

        if (hasBounds && map) {
            map.fitBounds(bounds);
            // Don't zoom in too close immediately
            const listener = google.maps.event.addListener(map, "idle", function() {
                if (map.getZoom() > 16) map.setZoom(15);
                google.maps.event.removeListener(listener);
            });
        }
    }

    function calculateDirections(data) {
        if (!directionsService || !directionsRenderer) return;

        let originPos = null;
        if (data.last_location) {
            originPos = new google.maps.LatLng(data.last_location.lat, data.last_location.lng);
        } else if (data.pickup.lat && data.pickup.lng) {
            originPos = new google.maps.LatLng(data.pickup.lat, data.pickup.lng);
        }

        if (!originPos || !data.destination.lat || !data.destination.lng) return;

        const destPos = new google.maps.LatLng(data.destination.lat, data.destination.lng);

        directionsService.route({
            origin: originPos,
            destination: destPos,
            travelMode: google.maps.TravelMode.DRIVING
        }, function(response, status) {
            if (status === 'OK') {
                directionsRenderer.setDirections(response);
                
                // Update ETA & Distance HUD
                const leg = response.routes[0].legs[0];
                document.getElementById('hud-distance').textContent = leg.distance.text;
                document.getElementById('hud-eta').textContent = leg.duration.text;
            } else {
                console.warn('Google Directions request failed: ' + status);
            }
        });
    }

    function updateDriverMarkerState(lat, lng, heading, speed, battery, updatedAt) {
        if (!map) return;

        const newPos = new google.maps.LatLng(lat, lng);
        lastPingTime = Date.now();

        // Arrowhead symbol pointing direction
        const arrowSymbol = {
            path: 'M 0,-15 L 9,8 L 0,2 L -9,8 Z',
            fillColor: '#f59e0b',
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2,
            scale: 1.1,
            rotation: heading || 0
        };

        if (!driverMarker) {
            driverMarker = new google.maps.Marker({
                position: newPos,
                map: map,
                title: "Driver Location",
                icon: arrowSymbol,
                zIndex: 1000
            });
        } else {
            const startPos = driverMarker.getPosition();
            interpolateMarker(driverMarker, startPos, newPos, 1500);
            
            // Update icon with rotation
            driverMarker.setIcon(arrowSymbol);
        }

        // Add to history line path
        if (historyPolyline) {
            const path = historyPolyline.getPath();
            path.push(newPos);
        }

        // Update stats HUD
        if (speed !== null) {
            document.getElementById('hud-speed').textContent = parseFloat(speed).toFixed(1) + ' km/h';
        }
        if (battery !== null) {
            document.getElementById('hud-battery').textContent = battery + '%';
        }
        
        // Update last updated timer
        document.getElementById('last-ping-hud').textContent = 'Ping: 1s ago';

        // Recalculate remaining path directions from new coordinate
        if (directionsService && destinationMarker) {
            directionsService.route({
                origin: newPos,
                destination: destinationMarker.getPosition(),
                travelMode: google.maps.TravelMode.DRIVING
            }, function(response, status) {
                if (status === 'OK') {
                    directionsRenderer.setDirections(response);
                    const leg = response.routes[0].legs[0];
                    document.getElementById('hud-distance').textContent = leg.distance.text;
                    document.getElementById('hud-eta').textContent = leg.duration.text;
                }
            });
        }
    }

    function setOnlineBadge(online) {
        const badge = document.getElementById('driver-status-badge');
        if (!badge) return;

        if (online) {
            badge.className = 'badge bg-soft-success text-success px-2.5 py-1.5 fs-11';
            badge.textContent = 'Active';
        } else {
            badge.className = 'badge bg-soft-secondary text-secondary px-2.5 py-1.5 fs-11';
            badge.textContent = 'Offline';
        }
    }

    // Periodically update the last updated text hud
    setInterval(function() {
        if (lastPingTime === 0) return;
        const diffSecs = Math.round((Date.now() - lastPingTime) / 1000);
        const hud = document.getElementById('last-ping-hud');
        
        if (diffSecs < 60) {
            hud.textContent = 'Ping: ' + diffSecs + 's ago';
        } else {
            hud.textContent = 'Ping: ' + Math.floor(diffSecs / 60) + 'm ago';
        }

        // Mark offline if > 45 seconds have passed without location updates
        if (diffSecs > 45) {
            setOnlineBadge(false);
        }
    }, 5000);

    // Initialize Map and Load Telemetry
    initTracking();

    // 2. Listen to real-time broadcasts
    if (window.LaravelEcho) {
        window.LaravelEcho.private(`order.tracking.${orderId}`)
            .listen('.DriverLocationUpdated', (e) => {
                console.log('Real-time location received:', e);
                setOnlineBadge(true);
                updateDriverMarkerState(
                    e.latitude,
                    e.longitude,
                    e.heading,
                    e.speed,
                    e.batteryLevel,
                    e.updatedAt
                );
            });
    }
});
</script>
@endpush
@endif
@endsection
