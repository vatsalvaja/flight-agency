@extends('layouts.admin')

@section('title', 'Assignable Orders || ' . ($appSettings->application_name ?? 'Wings'))

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="nxl-content">
    <div id="assignableOrdersConfig"
        data-list-url="{{ route('assignable-orders.list') }}"
        data-empty-message="You do not have any orders assigned to you at this moment.">
    </div>

    <div class="main-content py-4">
        <div class="container-fluid px-4">
            <div id="assignableOrdersAlert"></div>
            
            <!-- Dashboard Header Section -->
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center">
                    @if(isset($loggedUser) && $loggedUser->profile_photo)
                        <img src="{{ asset($loggedUser->profile_photo) }}" alt="driver photo" class="rounded-circle me-3 border border-2 border-primary" style="width: 46px; height: 46px; object-fit: cover;">
                    @elseif(isset($loggedUser))
                        <div class="avatar-text bg-soft-primary text-primary rounded-circle me-3 d-flex align-items-center justify-content-center border border-2 border-primary" style="width: 46px; height: 46px; font-weight: 800; font-size: 15px;">
                            {{ $loggedUser->getInitials() }}
                        </div>
                    @endif
                    <div>
                        <h4 class="fw-extrabold text-dark mb-0.5">Assignable Orders</h4>
                        <span class="fs-12 text-muted fw-semibold">Rider: <span class="text-primary">{{ $loggedUser->name ?? 'Driver' }}</span></span>
                    </div>
                </div>
                <div>
                    <span class="badge bg-soft-primary text-primary px-3 py-2 fs-11 rounded-pill fw-bold">Active Shift</span>
                </div>
            </div>

            <!-- Compact Metrics Row -->
            <div class="row row-cols-2 row-cols-sm-4 g-3 mb-4">
                <!-- Total Orders KPI -->
                <div class="col">
                    <div class="card border border-gray-3 shadow-sm mb-0 rounded-3 p-3 kpi-item">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-10 text-muted d-block text-uppercase fw-bold mb-0.5">Total Orders</span>
                                <span class="fs-18 fw-extrabold text-dark" id="assignable-orders-total-count">--</span>
                            </div>
                            <div class="bg-soft-secondary text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="feather-grid fs-13"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- In Transit KPI -->
                <div class="col">
                    <div class="card border border-gray-3 shadow-sm mb-0 rounded-3 p-3 kpi-item-transit">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-10 text-muted d-block text-uppercase fw-bold mb-0.5">In Transit</span>
                                <span class="fs-18 fw-extrabold text-primary" id="assignable-orders-in-progress-count">--</span>
                            </div>
                            <div class="bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="feather-clock fs-13"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Pickup KPI -->
                <div class="col">
                    <div class="card border border-gray-3 shadow-sm mb-0 rounded-3 p-3 kpi-item-pickup">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-10 text-muted d-block text-uppercase fw-bold mb-0.5">Pickup</span>
                                <span class="fs-18 fw-extrabold text-warning" id="assignable-orders-pickup-count">--</span>
                            </div>
                            <div class="bg-soft-warning text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="feather-truck fs-13"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Delivered KPI -->
                <div class="col">
                    <div class="card border border-gray-3 shadow-sm mb-0 rounded-3 p-3 kpi-item-delivered">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-10 text-muted d-block text-uppercase fw-bold mb-0.5">Delivered</span>
                                <span class="fs-18 fw-extrabold text-success" id="assignable-orders-delivered-count">--</span>
                            </div>
                            <div class="bg-soft-success text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="feather-check fs-13"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="filter-wrapper mb-4">
                <div class="filter-pills d-flex align-items-center gap-2">
                    <button type="button" class="filter-pill active js-filter-assignable-orders" data-status="all">
                        All <span class="badge rounded-circle px-1.5 py-0.5 fs-10" id="assignable-orders-filter-all-count">--</span>
                    </button>
                    <button type="button" class="filter-pill js-filter-assignable-orders" data-status="In Progress">
                        In Transit <span class="badge rounded-circle px-1.5 py-0.5 fs-10" id="assignable-orders-filter-in-progress-count">--</span>
                    </button>
                    <button type="button" class="filter-pill js-filter-assignable-orders" data-status="Pickup">
                        Pickup <span class="badge rounded-circle px-1.5 py-0.5 fs-10" id="assignable-orders-filter-pickup-count">--</span>
                    </button>
                    <button type="button" class="filter-pill js-filter-assignable-orders" data-status="Delivered">
                        Delivered <span class="badge rounded-circle px-1.5 py-0.5 fs-10" id="assignable-orders-filter-delivered-count">--</span>
                    </button>
                </div>
            </div>

            <!-- Assigned Orders primary focus (Desktop: 2 cards, Tablet: 2 cards, Mobile: 1 card) -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 g-4" id="orders-list">
                <div class="col-12 w-100">
                    <div class="card shadow-sm border border-gray-3 text-center py-5" style="border-radius: 16px;">
                        <div class="card-body">
                            <span class="spinner-border spinner-border-sm text-primary mb-3" role="status" aria-hidden="true"></span>
                            <h5 class="fw-bold mb-2 text-dark">Loading Orders</h5>
                            <p class="text-muted fs-13 mb-0">Fetching your latest assigned orders...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="assignable-order-detail-panel" class="mt-4 d-none"></div>
            
        </div>
    </div>
</div>

<style>
/* Filters Pills Style */
.filter-pills {
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 2px;
}
.filter-pills::-webkit-scrollbar {
    display: none;
}
.filter-pill {
    flex: 0 0 auto;
    padding: 8px 18px;
    border-radius: 50px;
    background: transparent;
    color: #64748b;
    font-weight: 700;
    font-size: 12.5px;
    border: 1px solid #cbd5e1;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}
.filter-pill:hover {
    background: rgba(100, 116, 139, 0.05);
}
.filter-pill.active {
    background: #3b82f6;
    color: #ffffff;
    border-color: #3b82f6;
    box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
}
.filter-pill .badge {
    margin-left: 6px;
    background: #e2e8f0;
    color: #475569;
}
.filter-pill.active .badge {
    background: #ffffff;
    color: #3b82f6 !important;
}

/* Dark mode adjustments for pills */
html.app-skin-dark .filter-pill {
    border-color: #334155;
    color: #94a3b8;
}
html.app-skin-dark .filter-pill:hover {
    background: rgba(255, 255, 255, 0.03);
}
html.app-skin-dark .filter-pill.active {
    background: #3b82f6;
    color: #ffffff;
    border-color: #3b82f6;
}
html.app-skin-dark .filter-pill .badge {
    background: #334155;
    color: #cbd5e1;
}
html.app-skin-dark .filter-pill.active .badge {
    background: #ffffff;
    color: #3b82f6 !important;
}

/* KPI metric item colorings */
.kpi-item {
    border-left: 3px solid #64748b !important;
}
.kpi-item-transit {
    border-left: 3px solid #3b82f6 !important;
}
.kpi-item-pickup {
    border-left: 3px solid #f59e0b !important;
}
.kpi-item-delivered {
    border-left: 3px solid #10b981 !important;
}

/* Order card effects */
.order-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 16px;
    cursor: pointer;
}
.order-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.1) !important;
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
    top: 24px;
    left: 28px;
    right: 28px;
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
    top: 24px;
    left: 28px;
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
    width: 60px;
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

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}

.order-detail-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    height: 100%;
}
html.app-skin-dark .order-detail-item {
    background: rgba(15, 23, 42, 0.35);
    border-color: #334155;
}
.order-detail-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
html.app-skin-dark .order-detail-icon {
    background: #1e293b;
}
.order-detail-content {
    min-width: 0;
    line-height: 1.25;
}
.order-detail-label {
    display: block;
    color: #64748b;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}
.order-detail-value {
    display: block;
    color: #0f172a;
    font-size: 12px;
    font-weight: 800;
}
html.app-skin-dark .order-detail-value {
    color: #f8fafc;
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
    
    /* Make order cards grid full-width with 12px edge margin */
    #orders-list {
        margin-left: -6px !important;
        margin-right: -6px !important;
    }
    .order-card-wrapper {
        padding-left: 6px !important;
        padding-right: 6px !important;
        margin-bottom: 12px !important;
    }
    .order-card {
        border-radius: 12px !important;
    }
    .order-card .card-body {
        padding: 16px !important;
    }
    
    /* Adapt compact progress stepper on mobile screens */
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
        box-shadow: 0 0 0 1.5px #f59e0b, 0 0 6px rgba(245, 158, 11, 0.4) !important;
    }
    .stepper-item.active-success .stepper-icon {
        box-shadow: 0 0 0 1.5px #10b981, 0 0 6px rgba(16, 185, 129, 0.4) !important;
    }
    .stepper-label {
        font-size: 8px !important;
        margin-top: 4px !important;
    }
}
</style>

@endsection

@section('modals')
<div class="modal fade" id="assignableDeliveryModal" tabindex="-1" aria-labelledby="assignableDeliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header py-3 px-4 border-bottom border-gray-2">
                <h5 class="modal-title fw-extrabold text-dark" id="assignableDeliveryModalLabel">Delivery Proof</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignableDeliveryForm" method="POST" enctype="multipart/form-data" class="mb-0">
                @csrf
                <div class="modal-body p-4">
                    <p class="fs-12 text-muted mb-4">Upload at least one proof image showing the delivered luggage clearly.</p>
                    <input type="file" name="proof_images[]" id="assignableDeliveryProofInput" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp" multiple required>
                    <div class="invalid-feedback d-block js-delivery-error mt-2"></div>
                    <div id="assignableDeliveryPreview" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>
                <div class="modal-footer p-3 bg-light border-top border-gray-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-extrabold text-uppercase text-white" style="font-size: 11px; letter-spacing: 0.5px;">
                        Submit & Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('assets/js/assignable-orders.js') }}"></script>
@endpush
