@extends('layouts.admin')

@section('title', 'Edit Luggage Assignment || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Assign Luggage</h5>
            </div>
            <ul class="breadcrumb d-none d-md-flex">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assign-luggage.index') }}">Assign Luggage</a></li>
                <li class="breadcrumb-item">Edit Assignment</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('assign-luggage.index') }}" class="btn btn-light">
                <i class="feather-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <div class="col-12">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="feather-alert-octagon me-2"></i>
                        <strong>Error!</strong> Please check the form errors below.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Modify Luggage Assignment</h5>
                    </div>
                    <div class="card-body">
                        <form id="luggage-assign-form" action="{{ route('assign-luggage.update', $assignment->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Retained Images Hidden Field -->
                            <input type="hidden" name="retained_images" id="retained_images" value="">

                            @php
                                $isDriver = false;
                                if (isset($loggedUser) && $loggedUser->role_id > 0 && $loggedUser->role) {
                                    $isDriver = (stripos($loggedUser->role->role_name, 'driver') !== false);
                                }
                            @endphp

                            <!-- Flight Company, Station, Driver Dropdowns -->
                            <div class="row mb-4">
                                <div class="{{ $isDriver ? 'col-md-3' : 'col-md-4' }} mb-3">
                                    <label class="form-label fw-semibold" for="company_id">Flight Company <span class="text-danger">*</span></label>
                                    <select name="company_id" id="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id', $assignment->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->company_name }} ({{ $company->company_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="{{ $isDriver ? 'col-md-3' : 'col-md-4' }} mb-3">
                                    <label class="form-label fw-semibold" for="station_id">Station <span class="text-danger">*</span></label>
                                    <select name="station_id" id="station_id" class="form-control @error('station_id') is-invalid @enderror" required>
                                        @foreach($stations as $station)
                                            <option value="{{ $station->id }}" {{ old('station_id', $assignment->station_id) == $station->id ? 'selected' : '' }}>
                                                {{ $station->station_name }} ({{ $station->station_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('station_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="{{ $isDriver ? 'col-md-3' : 'col-md-4' }} mb-3">
                                    <label class="form-label fw-semibold" for="driver_id">Driver <span class="text-danger">*</span></label>
                                    @if($isDriver)
                                        <select id="driver_id_display" class="form-control" disabled>
                                            <option value="{{ $loggedUser->id }}" selected>{{ $loggedUser->name }}</option>
                                        </select>
                                        <input type="hidden" name="driver_id" id="driver_id" value="{{ $loggedUser->id }}">
                                    @else
                                        <select name="driver_id" id="driver_id" class="form-control @error('driver_id') is-invalid @enderror" required>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}" {{ old('driver_id', $assignment->driver_id) == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('driver_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if($isDriver)
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-semibold" for="manager_id">Manager <span class="text-danger">*</span></label>
                                    <select name="manager_id" id="manager_id" class="form-control @error('manager_id') is-invalid @enderror" required>
                                        <option value="" disabled selected>Select Manager</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" {{ old('manager_id', $assignment->created_by) == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('manager_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endif
                            </div>

                            <!-- Pickup Location, Drop Location, Distance -->
                            <div class="row mb-4">
                                <div class="col-md-5 mb-3">
                                    <label class="form-label fw-semibold" for="pickup_location">Pickup Location <span class="text-danger">*</span></label>
                                    <input type="text" name="pickup_location" id="pickup_location" class="form-control @error('pickup_location') is-invalid @enderror" value="{{ old('pickup_location', $assignment->pickup_location) }}" placeholder="Search pickup location..." required autocomplete="off">
                                    <input type="hidden" name="pickup_latitude" id="pickup_latitude" value="{{ old('pickup_latitude', $assignment->pickup_latitude) }}">
                                    <input type="hidden" name="pickup_longitude" id="pickup_longitude" value="{{ old('pickup_longitude', $assignment->pickup_longitude) }}">
                                    @error('pickup_location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5 mb-3">
                                    <label class="form-label fw-semibold" for="drop_location">Drop Location <span class="text-danger">*</span></label>
                                    <input type="text" name="drop_location" id="drop_location" class="form-control @error('drop_location') is-invalid @enderror" value="{{ old('drop_location', $assignment->drop_location) }}" placeholder="Search drop location..." required autocomplete="off">
                                    <input type="hidden" name="drop_latitude" id="drop_latitude" value="{{ old('drop_latitude', $assignment->drop_latitude) }}">
                                    <input type="hidden" name="drop_longitude" id="drop_longitude" value="{{ old('drop_longitude', $assignment->drop_longitude) }}">
                                    @error('drop_location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label fw-semibold" for="distance_km">Distance (km)</label>
                                    <input type="number" step="0.01" name="distance_km" id="distance_km" class="form-control @error('distance_km') is-invalid @enderror" value="{{ old('distance_km', $assignment->distance_km) }}" placeholder="Distance" readonly>
                                    @error('distance_km')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Expected Delivery Date, Status -->
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="expected_delivery_date">Expected Delivery Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control @error('expected_delivery_date') is-invalid @enderror" value="{{ old('expected_delivery_date', $assignment->expected_delivery_date ? $assignment->expected_delivery_date->format('Y-m-d') : '') }}" required>
                                    @error('expected_delivery_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <div class="form-control-plaintext">
                                        @if($assignment->status === 'Pickup')
                                            <span class="badge bg-soft-warning text-warning px-3 py-2 fs-12 fw-semibold">Pickup</span>
                                        @elseif($assignment->status === 'In Progress')
                                            <span class="badge bg-soft-info text-info px-3 py-2 fs-12 fw-semibold">In Progress</span>
                                        @else
                                            <span class="badge bg-soft-success text-success px-3 py-2 fs-12 fw-semibold">Delivered</span>
                                        @endif
                                    </div>
                                    <input type="hidden" name="status" value="{{ $assignment->status }}">
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label fw-semibold" for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Add custom notes here...">{{ old('notes', $assignment->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Image Management -->
                            <div class="row mb-4">
                                <!-- Existing Images -->
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold">Current Images</label>
                                    <div id="existing-images-container" class="d-flex flex-wrap gap-3">
                                        @if($assignment->images && count($assignment->images) > 0)
                                            @foreach($assignment->images as $img)
                                                <div class="position-relative border rounded p-1 d-inline-block bg-white shadow-sm existing-img-wrapper" data-path="{{ $img }}" style="width: 80px; height: 80px;">
                                                    <img src="{{ asset($img) }}" class="w-100 h-100 rounded" style="object-fit: cover;">
                                                    <button type="button" class="btn-remove-existing position-absolute bg-danger text-white border-0 rounded-circle d-flex align-items-center justify-content-center" style="top: -5px; right: -5px; width: 20px; height: 20px; font-size: 12px; cursor: pointer;">
                                                        <i class="feather-x"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-muted fs-12">No images uploaded for this assignment.</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Upload New Images -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold" for="images">Upload New Images</label>
                                    <input type="file" name="images[]" id="images-input" class="form-control @error('images') is-invalid @enderror" multiple accept="image/jpeg,image/png,image/jpg,image/webp">
                                    <div class="form-text">Allowed formats: jpeg, png, jpg, webp. Max size per file: 2MB.</div>
                                    @error('images')
                                        <div class="invalid-feedback text-danger d-block">{{ $message }}</div>
                                    @enderror
                                    
                                    <!-- Previews container -->
                                    <div id="images-preview-container" class="d-flex flex-wrap gap-3 mt-3"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('assign-luggage.index') }}" class="btn btn-light px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4"><i class="feather-save me-2"></i>Update Assignment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
@endsection

@push('scripts')
<!-- Load Google Maps JavaScript API with Places Library -->
@if(env('GOOGLE_MAPS_API_KEY'))
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places"></script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --------------------------------------------------
    // Existing Images Removal State Management
    // --------------------------------------------------
    let retainedImages = @json($assignment->images ?? []);
    const retainedInput = document.getElementById('retained_images');
    
    // Initialize hidden input
    retainedInput.value = JSON.stringify(retainedImages);

    const removeExistingBtns = document.querySelectorAll('.btn-remove-existing');
    removeExistingBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const wrapper = btn.closest('.existing-img-wrapper');
            const path = wrapper.getAttribute('data-path');

            // Remove from array state
            retainedImages = retainedImages.filter(img => img !== path);
            retainedInput.value = JSON.stringify(retainedImages);

            // Remove from DOM
            wrapper.remove();

            if (retainedImages.length === 0) {
                document.getElementById('existing-images-container').innerHTML = '<div class="text-muted fs-12">No images uploaded for this assignment.</div>';
            }
        });
    });

    // --------------------------------------------------
    // Google Places Autocomplete & Distance logic
    // --------------------------------------------------
    const pickupInput = document.getElementById('pickup_location');
    const dropInput = document.getElementById('drop_location');
    const distanceInput = document.getElementById('distance_km');

    // Checks if the Google SDK loaded correctly
    const isGoogleMapsAvailable = (typeof google !== 'undefined' && google.maps && google.maps.places);

    if (isGoogleMapsAvailable) {
        // Initialize autocompletes
        const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
        const dropAutocomplete = new google.maps.places.Autocomplete(dropInput);

        // Bind events
        pickupAutocomplete.addListener('place_changed', function() {
            const place = pickupAutocomplete.getPlace();
            if (place.geometry && place.geometry.location) {
                document.getElementById('pickup_latitude').value = place.geometry.location.lat();
                document.getElementById('pickup_longitude').value = place.geometry.location.lng();
                calculateDistance();
            }
        });

        dropAutocomplete.addListener('place_changed', function() {
            const place = dropAutocomplete.getPlace();
            if (place.geometry && place.geometry.location) {
                document.getElementById('drop_latitude').value = place.geometry.location.lat();
                document.getElementById('drop_longitude').value = place.geometry.location.lng();
                calculateDistance();
            }
        });

        // Calculate Driving Distance using Distance Matrix API
        function calculateDistance() {
            const lat1 = document.getElementById('pickup_latitude').value;
            const lng1 = document.getElementById('pickup_longitude').value;
            const lat2 = document.getElementById('drop_latitude').value;
            const lng2 = document.getElementById('drop_longitude').value;

            if (lat1 && lng1 && lat2 && lng2) {
                const origin = new google.maps.LatLng(lat1, lng1);
                const destination = new google.maps.LatLng(lat2, lng2);
                const service = new google.maps.DistanceMatrixService();

                service.getDistanceMatrix({
                    origins: [origin],
                    destinations: [destination],
                    travelMode: google.maps.TravelMode.DRIVING,
                    unitSystem: google.maps.UnitSystem.METRIC,
                }, function(response, status) {
                    if (status === 'OK' && response.rows[0].elements[0].status === 'OK') {
                        const element = response.rows[0].elements[0];
                        const distanceInKm = element.distance.value / 1000;
                        distanceInput.value = distanceInKm.toFixed(2);
                    } else {
                        calculateHaversineDistance(lat1, lng1, lat2, lng2);
                    }
                });
            }
        }

        function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Earth's radius
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const d = R * c;
            distanceInput.value = d.toFixed(2);
        }
    } else {
        // Fallback: Make distance input editable if Google API failed
        distanceInput.removeAttribute('readonly');
        distanceInput.placeholder = 'Enter distance manually (km)';
    }

    // --------------------------------------------------
    // Multiple Images Upload Previews and Removals
    // --------------------------------------------------
    const fileInput = document.getElementById('images-input');
    const previewContainer = document.getElementById('images-preview-container');
    const form = document.getElementById('luggage-assign-form');

    let selectedFiles = [];

    fileInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        files.forEach(file => {
            const typePattern = /\/(jpeg|png|jpg|webp)$/i;
            if (typePattern.test(file.type)) {
                selectedFiles.push(file);
            }
        });

        renderPreviews();
        fileInput.value = '';
    });

    function renderPreviews() {
        previewContainer.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'position-relative border rounded p-1 d-inline-block bg-white shadow-sm';
            wrapper.style.width = '80px';
            wrapper.style.height = '80px';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'w-100 h-100 rounded';
            img.style.objectFit = 'cover';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'position-absolute bg-danger text-white border-0 rounded-circle d-flex align-items-center justify-content-center';
            removeBtn.style.top = '-5px';
            removeBtn.style.right = '-5px';
            removeBtn.style.width = '20px';
            removeBtn.style.height = '20px';
            removeBtn.style.fontSize = '12px';
            removeBtn.style.cursor = 'pointer';
            removeBtn.innerHTML = '<i class="feather-x"></i>';

            removeBtn.addEventListener('click', function() {
                selectedFiles.splice(index, 1);
                renderPreviews();
            });

            wrapper.appendChild(img);
            wrapper.appendChild(removeBtn);
            previewContainer.appendChild(wrapper);
        });
    }

    // Programmatically rebuild files array in the input on submit
    form.addEventListener('submit', function(e) {
        if (selectedFiles.length > 0) {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            fileInput.files = dataTransfer.files;
        }
    });
});
</script>
@endpush
