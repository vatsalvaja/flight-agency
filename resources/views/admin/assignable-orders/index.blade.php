@extends('layouts.admin')

@section('title', 'Assignable Orders || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <div class="main-content py-4">
        <div class="container-fluid px-4">
            
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
                                <span class="fs-18 fw-extrabold text-dark">{{ $assignments->count() }}</span>
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
                                <span class="fs-18 fw-extrabold text-primary">{{ $assignments->where('status', 'In Progress')->count() }}</span>
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
                                <span class="fs-18 fw-extrabold text-warning">{{ $assignments->where('status', 'Pickup')->count() }}</span>
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
                                <span class="fs-18 fw-extrabold text-success">{{ $assignments->where('status', 'Delivered')->count() }}</span>
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
                    <button class="filter-pill active" onclick="filterStatus('all')">
                        All <span class="badge rounded-circle px-1.5 py-0.5 fs-10">{{ $assignments->count() }}</span>
                    </button>
                    <button class="filter-pill" onclick="filterStatus('In Progress')">
                        In Transit <span class="badge rounded-circle px-1.5 py-0.5 fs-10">{{ $assignments->where('status', 'In Progress')->count() }}</span>
                    </button>
                    <button class="filter-pill" onclick="filterStatus('Pickup')">
                        Pickup <span class="badge rounded-circle px-1.5 py-0.5 fs-10">{{ $assignments->where('status', 'Pickup')->count() }}</span>
                    </button>
                    <button class="filter-pill" onclick="filterStatus('Delivered')">
                        Delivered <span class="badge rounded-circle px-1.5 py-0.5 fs-10">{{ $assignments->where('status', 'Delivered')->count() }}</span>
                    </button>
                </div>
            </div>

            <!-- Assigned Orders primary focus (Desktop: 2 cards, Tablet: 2 cards, Mobile: 1 card) -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 g-4" id="orders-list">
                @forelse($assignments as $assignment)
                    <div class="col order-card-wrapper" data-status="{{ $assignment->status }}">
                        <div class="card order-card h-100 shadow-sm border border-gray-3 overflow-hidden d-flex flex-column" onclick="window.location='{{ route('assignable-orders.show', $assignment->id) }}'">
                            
                            <div class="card-body p-4 flex-grow-1">
                                <!-- Badge & Order ID -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    @if($assignment->status === 'In Progress')
                                        <span class="badge bg-soft-info text-info px-2.5 py-1.5 fs-10.5 rounded-pill fw-bold text-uppercase">In Transit</span>
                                    @elseif($assignment->status === 'Pickup')
                                        <span class="badge bg-soft-warning text-warning px-2.5 py-1.5 fs-10.5 rounded-pill fw-bold text-uppercase">Pickup</span>
                                    @else
                                        <span class="badge bg-soft-success text-success px-2.5 py-1.5 fs-10.5 rounded-pill fw-bold text-uppercase">Delivered</span>
                                    @endif
                                    
                                    <span class="fs-13.5 fw-extrabold text-muted">Order #ORD-{{ str_pad($assignment->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </div>

                                <!-- Company Banner -->
                                <div class="d-flex align-items-center mb-3 bg-light p-2.5 rounded-3 border border-gray-2">
                                    @if($assignment->company->logo)
                                        <img src="{{ asset($assignment->company->logo) }}" alt="logo" class="rounded me-2.5" style="height: 30px; width: 30px; object-fit: cover;">
                                    @else
                                        <div class="avatar-text avatar-sm bg-soft-primary text-primary rounded me-2.5 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-weight: 700; font-size: 11px;">
                                            {{ substr($assignment->company->company_name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div style="line-height: 1.25;">
                                        <span class="fw-extrabold text-dark fs-13 d-block">{{ $assignment->company->company_name }}</span>
                                        <span class="text-muted fs-11">Station: {{ $assignment->station->station_name }}</span>
                                    </div>
                                </div>

                                <!-- Visual Journey Paths -->
                                <div class="route-timeline mb-3.5">
                                    <div class="timeline-item">
                                        <span class="text-muted fs-9 d-block text-uppercase fw-bold mb-0.5">From Station (Pickup)</span>
                                        <span class="fw-bold text-dark fs-12 line-clamp-1" title="{{ $assignment->pickup_location }}">{{ $assignment->pickup_location }}</span>
                                    </div>
                                    <div class="timeline-item drop mt-2.5">
                                        <span class="text-muted fs-9 d-block text-uppercase fw-bold mb-0.5">To Station (Drop)</span>
                                        <span class="fw-bold text-dark fs-12 line-clamp-1" title="{{ $assignment->drop_location }}">{{ $assignment->drop_location }}</span>
                                    </div>
                                </div>

                                <!-- Distance & Expected row -->
                                <div class="row g-2 border-top border-gray-2 pt-3 text-center">
                                    <div class="col-6 border-end border-gray-2">
                                        <span class="text-muted fs-9 text-uppercase d-block mb-0.5 fw-bold">Distance</span>
                                        <span class="fw-extrabold text-dark fs-12.5">{{ $assignment->distance_km ?? '0.00' }} km</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted fs-9 text-uppercase d-block mb-0.5 fw-bold">Expected Delivery</span>
                                        <span class="fw-extrabold text-dark fs-12.5">{{ $assignment->expected_delivery_date->format('d M, Y') }}</span>
                                    </div>
                                </div>

                                <!-- Visual Status Workflow progress stepper line -->
                                <div class="stepper-container mt-4">
                                    <div class="position-relative">
                                        <!-- Connect Line bg -->
                                        <div class="stepper-line-bg"></div>
                                        <!-- Active Overlay Line -->
                                        <div class="stepper-line-active" style="width: {{ $assignment->status === 'In Progress' ? '0%' : ($assignment->status === 'Pickup' ? '50%' : '100%') }};"></div>
                                        
                                        <!-- Nodes -->
                                        <div class="stepper-wrapper">
                                            <!-- Step 1 -->
                                            <div class="stepper-item {{ in_array($assignment->status, ['In Progress', 'Pickup', 'Delivered']) ? 'active' : '' }}">
                                                <div class="stepper-icon">
                                                    <i class="feather-navigation"></i>
                                                </div>
                                                <span class="stepper-label">In Transit</span>
                                            </div>
                                            <!-- Step 2 -->
                                            <div class="stepper-item {{ in_array($assignment->status, ['Pickup', 'Delivered']) ? 'active-warning' : '' }}">
                                                <div class="stepper-icon">
                                                    <i class="feather-package"></i>
                                                </div>
                                                <span class="stepper-label">Pickup</span>
                                            </div>
                                            <!-- Step 3 -->
                                            <div class="stepper-item {{ $assignment->status === 'Delivered' ? 'active-success' : '' }}">
                                                <div class="stepper-icon">
                                                    <i class="feather-check"></i>
                                                </div>
                                                <span class="stepper-label">Delivered</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Large, full-width Action Area -->
                            <div class="mt-auto border-top border-gray-2">
                                @if($assignment->status === 'In Progress')
                                    <form action="{{ route('assignable-orders.pickup', $assignment->id) }}" method="POST" class="mb-0" onclick="event.stopPropagation();">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-0 fw-extrabold text-uppercase text-white d-flex align-items-center justify-content-center gap-2" style="font-size: 12.5px; letter-spacing: 0.5px; background-color: #3b82f6; border-color: #3b82f6;">
                                            Pickup Order <i class="feather-arrow-right fs-14"></i>
                                        </button>
                                    </form>
                                @elseif($assignment->status === 'Pickup')
                                    <a href="{{ route('assignable-orders.show', $assignment->id) }}" class="btn btn-warning w-100 py-3 rounded-0 fw-extrabold text-uppercase text-white d-flex align-items-center justify-content-center gap-2" onclick="event.stopPropagation();" style="font-size: 12.5px; letter-spacing: 0.5px; background-color: #f59e0b; border-color: #f59e0b;">
                                        Mark as Delivered <i class="feather-check-circle fs-14"></i>
                                    </a>
                                @else
                                    <div class="bg-soft-success text-success w-100 py-3 text-center fw-extrabold text-uppercase fs-12.5 d-flex align-items-center justify-content-center gap-1.5" style="letter-spacing: 0.5px;">
                                        Completed <i class="feather-check-circle fs-15"></i>
                                    </div>
                                @endif
                            </div>
                            
                        </div>
                    </div>
                @empty
                    <div class="col-12 w-100">
                        <div class="card shadow-sm border border-gray-3 text-center py-5" style="border-radius: 16px;">
                            <div class="card-body">
                                <i class="feather-truck fs-1 text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold mb-2 text-dark">No Orders Found</h5>
                                <p class="text-muted fs-13 mb-0">You do not have any orders assigned to you at this moment.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
            
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

<script>
function filterStatus(status) {
    // Update active class on filter buttons
    const buttons = document.querySelectorAll('.filter-pill');
    buttons.forEach(btn => {
        if (btn.innerText.includes(status) || (status === 'all' && btn.innerText.includes('All'))) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Filter cards
    const wrappers = document.querySelectorAll('#orders-list > .order-card-wrapper');
    wrappers.forEach(wrap => {
        const cardStatus = wrap.getAttribute('data-status');
        if (status === 'all' || cardStatus === status) {
            wrap.style.display = 'block';
        } else {
            wrap.style.display = 'none';
        }
    });
}
</script>
@endsection
