@extends('layouts.admin')

@section('title', 'Order Details || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <div class="main-content py-4">
        <div class="container-fluid px-4" style="max-width: 900px; margin: 0 auto;">
            
            <!-- Header bar with Back button -->
            <div class="d-flex align-items-center justify-content-between mb-4">
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
                        
                        <div class="card-header bg-transparent border-bottom border-gray-2 py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fw-extrabold text-dark">Order Information</h5>
                            <span class="fs-15 fw-extrabold text-primary">#ORD-{{ str_pad($assignment->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>

                        <div class="card-body p-4 flex-grow-1">
                            <!-- Company banner -->
                            <div class="d-flex align-items-center mb-4 bg-light p-3 rounded-3 border border-gray-2">
                                @if($assignment->company->logo)
                                    <img src="{{ asset('storage/' . $assignment->company->logo) }}" alt="logo" class="rounded me-3" style="height: 38px; width: 38px; object-fit: cover;">
                                @else
                                    <div class="avatar-text bg-soft-primary text-primary rounded me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-weight: 700; font-size: 14px;">
                                        {{ substr($assignment->company->company_name, 0, 1) }}
                                    </div>
                                @endif
                                <div style="line-height: 1.3;">
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
                            <div class="row g-3 border-top border-gray-2 pt-3">
                                <div class="col-6">
                                    <span class="text-muted fs-10 d-block text-uppercase mb-0.5">Distance</span>
                                    <span class="fw-bold text-dark fs-13"><i class="feather-map text-primary me-1.5"></i>{{ $assignment->distance_km ?? '0.00' }} km</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted fs-10 d-block text-uppercase mb-0.5">Expected Date</span>
                                    <span class="fw-bold text-dark fs-13"><i class="feather-calendar text-primary me-1.5"></i>{{ $assignment->expected_delivery_date->format('d M, Y') }}</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted fs-10 d-block text-uppercase mb-0.5">Assigned Date</span>
                                    <span class="fw-bold text-dark fs-13"><i class="feather-clock text-primary me-1.5"></i>{{ $assignment->created_at->format('d M, Y H:i A') }}</span>
                                </div>
                                @if($assignment->delivered_at)
                                    <div class="col-6">
                                        <span class="text-muted fs-10 d-block text-uppercase mb-0.5">Delivered Date</span>
                                        <span class="fw-bold text-success fs-13"><i class="feather-check-circle text-success me-1.5"></i>{{ $assignment->delivered_at->format('d M, Y H:i A') }}</span>
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
                                    <button type="button" class="btn btn-lg btn-warning text-white w-100 py-3 rounded-3 fw-extrabold text-uppercase text-white d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#deliveryModal" style="font-size: 13px; letter-spacing: 0.5px; background-color: #f59e0b; border-color: #f59e0b;">
                                        <i class="feather-check-circle fs-14"></i> Mark as Delivered
                                    </button>
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
                                                    <a href="{{ asset('storage/' . $img) }}" target="_blank" class="d-block border rounded-3 p-1 overflow-hidden hover-proof-image bg-white" style="height: 100px;">
                                                        <img src="{{ asset('storage/' . $img) }}" class="w-100 h-100 rounded-2" style="object-fit: cover;">
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

<script>
let selectedFilesArray = [];
let webcamStream = null;

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

@media (max-width: 576px) {
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
</style>
@endsection
