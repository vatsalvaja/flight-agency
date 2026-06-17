@extends('layouts.admin')

@section('title', 'Driver Activities || ' . ($appSettings->application_name ?? 'Wings'))

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
                                                <img src="{{ asset('storage/' . $assignment->driver->profile_photo) }}" alt="driver avatar" class="rounded-circle me-3 border border-2 border-primary" style="width: 38px; height: 38px; object-fit: cover;">
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
                                                <img src="{{ asset('storage/' . $assignment->company->logo) }}" alt="airline logo" class="rounded border p-0.5" style="width: 26px; height: 26px; object-fit: cover;">
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
                                                               onclick="showProofImage('{{ asset('storage/' . $img) }}', 'Order #ORD-{{ str_pad($assignment->id, 5, '0', STR_PAD_LEFT) }} Proof Photo {{ $idx + 1 }}')"
                                                               data-bs-toggle="modal" 
                                                               data-bs-target="#proofImageModal"
                                                               class="proof-thumb-link rounded border shadow-sm p-0.5 bg-white overflow-hidden" 
                                                               style="width: 38px; height: 38px; margin-left: {{ $idx > 0 ? '-14px' : '0' }}; z-index: {{ 10 - $idx }};">
                                                                <img src="{{ asset('storage/' . $img) }}" class="w-100 h-100 rounded" style="object-fit: cover;">
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

@push('scripts')
<!-- Include Select2 JS script -->
<script src="{{ asset('assets/vendors/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 selectors (Professional bootstrap-5 themed dropdowns)
        $('.select2-select').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('.filter-card')
        });
    });
</script>
@endpush
