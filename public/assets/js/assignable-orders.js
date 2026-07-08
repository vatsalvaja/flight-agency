(function ($) {
    'use strict';

    var selectors = {
        config: '#assignableOrdersConfig',
        alert: '#assignableOrdersAlert',
        list: '#orders-list',
        detailPanel: '#assignable-order-detail-panel',
        filterButton: '.js-filter-assignable-orders',
        pickupForm: '.js-assignable-order-pickup-form',
        card: '.js-assignable-order-card',
        backToList: '.js-assignable-orders-back',
        deliveryModal: '#assignableDeliveryModal',
        deliveryForm: '#assignableDeliveryForm',
        deliveryProofInput: '#assignableDeliveryProofInput',
        deliveryPreview: '#assignableDeliveryPreview',
        deliveryError: '.js-delivery-error',
        openDeliveryModal: '.js-open-delivery-modal'
    };

    var currentStatus = 'all';
    var currentOrder = null;
    var geoWatchId = null;
    var lastSentLat = null;
    var lastSentLng = null;
    var lastSentTime = 0;
    var lastGpsAccuracy = null;
    var trackingMap = null;
    var trackingDriverMarker = null;
    var trackingRouteLayer = null;
    var trackingHealthTimer = null;
    var MIN_DISTANCE_METERS = 3;
    var MIN_TIME_INTERVAL = 5000;
    var STATIONARY_HEARTBEAT = 30000;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? 'N/A' : value).html();
    }

    function valueOr(value, fallback) {
        return value === null || value === undefined || value === '' ? (fallback || 'N/A') : value;
    }

    function setText(id, value) {
        var $element = $('#' + id);

        if ($element.length) {
            $element.text(valueOr(value, '0'));
        }
    }

    function showMessage(type, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'feather-check-circle' : 'feather-alert-octagon';

        if ($(selectors.alert).length) {
            $(selectors.alert).html(
                '<div class="alert ' + alertClass + ' alert-dismissible fade show mb-4" role="alert">' +
                    '<i class="' + icon + ' me-2"></i>' +
                    escapeHtml(message || 'Something went wrong.') +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>'
            );
        }

        if (typeof Swal !== 'undefined') {
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            }).fire({
                icon: type,
                title: message || 'Done'
            });
        }
    }

    function loadingState() {
        return '<div class="col-12 w-100">' +
            '<div class="card shadow-sm border border-gray-3 text-center py-5" style="border-radius: 16px;">' +
                '<div class="card-body">' +
                    '<span class="spinner-border spinner-border-sm text-primary mb-3" role="status" aria-hidden="true"></span>' +
                    '<h5 class="fw-bold mb-2 text-dark">Loading Orders</h5>' +
                    '<p class="text-muted fs-13 mb-0">Fetching your latest assigned orders...</p>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function emptyState(message) {
        return '<div class="col-12 w-100 assignable-orders-empty-state">' +
            '<div class="card shadow-sm border border-gray-3 text-center py-5" style="border-radius: 16px;">' +
                '<div class="card-body">' +
                    '<i class="feather-truck fs-1 text-muted mb-3 d-block"></i>' +
                    '<h5 class="fw-bold mb-2 text-dark">No Orders Found</h5>' +
                    '<p class="text-muted fs-13 mb-0">' + escapeHtml(message || $(selectors.config).data('empty-message') || 'You do not have any orders assigned to you at this moment.') + '</p>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function errorState(message) {
        return '<div class="col-12 w-100">' +
            '<div class="card shadow-sm border border-danger text-center py-5" style="border-radius: 16px;">' +
                '<div class="card-body">' +
                    '<i class="feather-alert-octagon fs-1 text-danger mb-3 d-block"></i>' +
                    '<h5 class="fw-bold mb-2 text-danger">Unable to Load Orders</h5>' +
                    '<p class="text-muted fs-13 mb-0">' + escapeHtml(message || 'Please refresh and try again.') + '</p>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function statusBadge(status) {
        if (status === 'In Progress') {
            return '<span class="badge bg-soft-info text-info px-2.5 py-1.5 fs-10.5 rounded-pill fw-bold text-uppercase">In Transit</span>';
        }

        if (status === 'Pickup') {
            return '<span class="badge bg-soft-warning text-warning px-2.5 py-1.5 fs-10.5 rounded-pill fw-bold text-uppercase">Pickup</span>';
        }

        return '<span class="badge bg-soft-success text-success px-2.5 py-1.5 fs-10.5 rounded-pill fw-bold text-uppercase">Delivered</span>';
    }

    function companyAvatar(order) {
        order = order || {};
        var company = order.company || {};

        if (company.logo) {
            return '<img src="' + escapeHtml(company.logo) + '" alt="logo" class="rounded me-2.5" style="height: 30px; width: 30px; object-fit: cover;">';
        }

        return '<div class="avatar-text avatar-sm bg-soft-primary text-primary rounded me-2.5 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-weight: 700; font-size: 11px;">' +
            escapeHtml(company.initial || 'C') +
        '</div>';
    }

    function stepperWidth(status) {
        if (status === 'Pickup') {
            return '50%';
        }

        if (status === 'Delivered') {
            return '100%';
        }

        return '0%';
    }

    function stepperClass(status, step) {
        if (step === 'transit' && (status === 'In Progress' || status === 'Pickup' || status === 'Delivered')) {
            return 'active';
        }

        if (step === 'pickup' && (status === 'Pickup' || status === 'Delivered')) {
            return 'active-warning';
        }

        if (step === 'delivered' && status === 'Delivered') {
            return 'active-success';
        }

        return '';
    }

    function actionHtml(order) {
        order = order || {};

        if (order.status === 'In Progress') {
            return '<form action="' + escapeHtml(order.pickup_url || '#') + '" method="POST" class="mb-0 js-assignable-order-pickup-form">' +
                '<button type="submit" class="btn btn-primary w-100 py-3 rounded-0 fw-extrabold text-uppercase text-white d-flex align-items-center justify-content-center gap-2" style="font-size: 12.5px; letter-spacing: 0.5px; background-color: #3b82f6; border-color: #3b82f6;">' +
                    'Pickup Order <i class="feather-arrow-right fs-14"></i>' +
                '</button>' +
            '</form>';
        }

        if (order.status === 'Pickup') {
            return '<a href="' + escapeHtml(order.show_url || '#') + '" class="btn btn-warning w-100 py-3 rounded-0 fw-extrabold text-uppercase text-white d-flex align-items-center justify-content-center gap-2" style="font-size: 12.5px; letter-spacing: 0.5px; background-color: #f59e0b; border-color: #f59e0b;">' +
                'Mark as Delivered <i class="feather-check-circle fs-14"></i>' +
            '</a>';
        }

        return '<div class="bg-soft-success text-success w-100 py-3 text-center fw-extrabold text-uppercase fs-12.5 d-flex align-items-center justify-content-center gap-1.5" style="letter-spacing: 0.5px;">' +
            'Completed <i class="feather-check-circle fs-15"></i>' +
        '</div>';
    }

    function orderCard(order) {
        order = order || {};
        var company = order.company || {};
        var station = order.station || {};

        return '<div class="col order-card-wrapper" data-status="' + escapeHtml(order.status) + '">' +
            '<div class="card order-card h-100 shadow-sm border border-gray-3 overflow-hidden d-flex flex-column js-assignable-order-card" data-url="' + escapeHtml(order.data_url || order.show_url || '#') + '">' +
                '<div class="card-body p-4 flex-grow-1">' +
                    '<div class="d-flex justify-content-between align-items-center mb-3">' +
                        statusBadge(order.status) +
                        '<span class="fs-13.5 fw-extrabold text-muted">Order #' + escapeHtml(order.order_number) + '</span>' +
                    '</div>' +
                    '<div class="d-flex align-items-center mb-3 bg-light p-2.5 rounded-3 border border-gray-2">' +
                        companyAvatar(order) +
                        '<div style="line-height: 1.25; min-width: 0;">' +
                            '<span class="fw-extrabold text-dark fs-13 d-block line-clamp-1" title="' + escapeHtml(company.name || 'N/A') + '">' + escapeHtml(company.name || 'N/A') + '</span>' +
                            '<span class="text-muted fs-11 line-clamp-1" title="' + escapeHtml(station.name || 'N/A') + '">Station: ' + escapeHtml(station.name || 'N/A') + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="route-timeline mb-3.5">' +
                        '<div class="timeline-item">' +
                            '<span class="text-muted fs-9 d-block text-uppercase fw-bold mb-0.5">From Station (Pickup)</span>' +
                            '<span class="fw-bold text-dark fs-12 line-clamp-1" title="' + escapeHtml(order.pickup_location) + '">' + escapeHtml(order.pickup_location) + '</span>' +
                        '</div>' +
                        '<div class="timeline-item drop mt-2.5">' +
                            '<span class="text-muted fs-9 d-block text-uppercase fw-bold mb-0.5">To Station (Drop)</span>' +
                            '<span class="fw-bold text-dark fs-12 line-clamp-1" title="' + escapeHtml(order.drop_location) + '">' + escapeHtml(order.drop_location) + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="row g-2 border-top border-gray-2 pt-3 text-center">' +
                        '<div class="col-6 border-end border-gray-2">' +
                            '<span class="text-muted fs-9 text-uppercase d-block mb-0.5 fw-bold">Distance</span>' +
                            '<span class="fw-extrabold text-dark fs-12.5">' + escapeHtml(valueOr(order.distance_km, '0.00')) + ' km</span>' +
                        '</div>' +
                        '<div class="col-6">' +
                            '<span class="text-muted fs-9 text-uppercase d-block mb-0.5 fw-bold">Expected Delivery</span>' +
                            '<span class="fw-extrabold text-dark fs-12.5">' + escapeHtml(order.expected_delivery_date || 'N/A') + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="stepper-container mt-4">' +
                        '<div class="position-relative">' +
                            '<div class="stepper-line-bg"></div>' +
                            '<div class="stepper-line-active" style="width: ' + stepperWidth(order.status) + ';"></div>' +
                            '<div class="stepper-wrapper">' +
                                '<div class="stepper-item ' + stepperClass(order.status, 'transit') + '">' +
                                    '<div class="stepper-icon"><i class="feather-navigation"></i></div>' +
                                    '<span class="stepper-label">In Transit</span>' +
                                '</div>' +
                                '<div class="stepper-item ' + stepperClass(order.status, 'pickup') + '">' +
                                    '<div class="stepper-icon"><i class="feather-package"></i></div>' +
                                    '<span class="stepper-label">Pickup</span>' +
                                '</div>' +
                                '<div class="stepper-item ' + stepperClass(order.status, 'delivered') + '">' +
                                    '<div class="stepper-icon"><i class="feather-check"></i></div>' +
                                    '<span class="stepper-label">Delivered</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="mt-auto border-top border-gray-2">' +
                    actionHtml(order) +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function updateCounts(counts) {
        counts = counts || {};

        setText('assignable-orders-total-count', counts.all);
        setText('assignable-orders-in-progress-count', counts.in_progress);
        setText('assignable-orders-pickup-count', counts.pickup);
        setText('assignable-orders-delivered-count', counts.delivered);
        setText('assignable-orders-filter-all-count', counts.all);
        setText('assignable-orders-filter-in-progress-count', counts.in_progress);
        setText('assignable-orders-filter-pickup-count', counts.pickup);
        setText('assignable-orders-filter-delivered-count', counts.delivered);
    }

    function applyFilter() {
        var visibleCount = 0;

        $(selectors.filterButton).removeClass('active')
            .filter('[data-status="' + currentStatus + '"]')
            .addClass('active');

        $('.order-card-wrapper').each(function () {
            var $wrapper = $(this);
            var shouldShow = currentStatus === 'all' || $wrapper.data('status') === currentStatus;

            $wrapper.toggle(shouldShow);
            if (shouldShow) {
                visibleCount += 1;
            }
        });

        $('.assignable-orders-filter-empty').remove();
        if (visibleCount === 0 && $('.order-card-wrapper').length > 0) {
            $(selectors.list).append(
                '<div class="col-12 w-100 assignable-orders-filter-empty">' +
                    '<div class="card shadow-sm border border-gray-3 text-center py-5" style="border-radius: 16px;">' +
                        '<div class="card-body">' +
                            '<i class="feather-filter fs-1 text-muted mb-3 d-block"></i>' +
                            '<h5 class="fw-bold mb-2 text-dark">No Matching Orders</h5>' +
                            '<p class="text-muted fs-13 mb-0">No orders found for the selected status.</p>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        }
    }

    function renderOrders(orders) {
        var $list = $(selectors.list);

        if (!$list.length) {
            return;
        }

        orders = $.isArray(orders) ? orders : [];

        if (!orders.length) {
            $list.html(emptyState());
            return;
        }

        $list.html($.map(orders, function (order) {
            return orderCard(order);
        }).join(''));

        applyFilter();
    }

    function detailLoadingState() {
        return '<div class="card shadow-sm border border-gray-3 text-center py-5" style="border-radius: 16px;">' +
            '<div class="card-body">' +
                '<span class="spinner-border spinner-border-sm text-primary mb-3" role="status" aria-hidden="true"></span>' +
                '<h5 class="fw-bold mb-2 text-dark">Loading Order Details</h5>' +
                '<p class="text-muted fs-13 mb-0">Fetching the latest order information...</p>' +
            '</div>' +
        '</div>';
    }

    function detailErrorState(message) {
        return '<div class="card shadow-sm border border-danger text-center py-5" style="border-radius: 16px;">' +
            '<div class="card-body">' +
                '<i class="feather-alert-octagon fs-1 text-danger mb-3 d-block"></i>' +
                '<h5 class="fw-bold mb-2 text-danger">Unable to Load Order</h5>' +
                '<p class="text-muted fs-13 mb-3">' + escapeHtml(message || 'Please try again.') + '</p>' +
                '<button type="button" class="btn btn-light border rounded-pill px-4 js-assignable-orders-back">Back to Orders</button>' +
            '</div>' +
        '</div>';
    }

    function proofImagesHtml(order) {
        var images = $.isArray(order.delivery_proof_images) ? order.delivery_proof_images : [];

        if (order.status !== 'Delivered') {
            return '<div class="text-center text-muted py-4 fs-12">' +
                '<i class="feather-camera fs-3 d-block mb-2 text-primary"></i>' +
                'Proof image will be displayed here after delivery confirmation.' +
            '</div>';
        }

        if (!images.length) {
            return '<div class="text-center text-muted py-4 fs-12">' +
                '<i class="feather-alert-triangle fs-3 d-block mb-2 text-warning"></i>' +
                'Completed without proof images.' +
            '</div>';
        }

        return '<div class="row g-2">' + $.map(images, function (image) {
            return '<div class="col-6">' +
                '<a href="' + escapeHtml(image.url || '#') + '" target="_blank" class="d-block border rounded-3 p-1 overflow-hidden hover-proof-image bg-white" style="height: 100px;">' +
                    '<img src="' + escapeHtml(image.url || '') + '" class="w-100 h-100 rounded-2" style="object-fit: cover;">' +
                '</a>' +
            '</div>';
        }).join('') + '</div>';
    }

    function detailActionsHtml(order) {
        if (order.status === 'In Progress') {
            return '<form action="' + escapeHtml(order.pickup_url || '#') + '" method="POST" class="mb-0 js-assignable-order-pickup-form">' +
                '<button type="submit" class="btn btn-lg btn-primary w-100 py-3 rounded-3 fw-extrabold text-uppercase text-white d-flex align-items-center justify-content-center gap-2" style="font-size: 13px; letter-spacing: 0.5px; background-color: #3b82f6; border-color: #3b82f6;">' +
                    '<i class="feather-truck fs-14"></i> Pickup Order' +
                '</button>' +
            '</form>';
        }

        if (order.status === 'Pickup') {
            return '<div class="d-flex flex-column gap-2">' +
                '<button type="button" class="btn btn-lg w-100 py-3 rounded-3 fw-extrabold text-uppercase text-white d-flex align-items-center justify-content-center gap-2 js-open-delivery-modal" style="font-size: 13px; letter-spacing: 0.5px; background-color: #f59e0b; border-color: #f59e0b;">' +
                    '<i class="feather-check-circle fs-14"></i> Mark as Delivered' +
                '</button>' +
            '</div>';
        }

        return '<div class="bg-soft-success text-success w-100 py-3 text-center fw-extrabold text-uppercase fs-12.5 d-flex align-items-center justify-content-center gap-1.5 rounded-3" style="letter-spacing: 0.5px;">' +
            'Completed <i class="feather-check-circle fs-15"></i>' +
        '</div>';
    }

    function trackingPanelHtml(order) {
        if (order.status !== 'Pickup') {
            return '';
        }

        return '<div class="alert alert-light border d-flex align-items-start gap-3 mb-3" id="ajax-driver-live-tracking-panel">' +
            '<div class="rounded-circle d-flex align-items-center justify-content-center bg-soft-primary text-primary" style="width: 36px; height: 36px; flex: 0 0 36px;">' +
                '<i class="feather-radio" id="ajax-driver-tracking-icon"></i>' +
            '</div>' +
            '<div class="flex-grow-1">' +
                '<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">' +
                    '<span class="fw-bold text-dark fs-13">Live tracking</span>' +
                    '<span class="badge bg-soft-warning text-warning fs-11" id="ajax-driver-tracking-status">Starting GPS...</span>' +
                '</div>' +
                '<div class="text-muted fs-11 mt-1" id="ajax-driver-tracking-detail">Keep this order open while tracking. Mobile browsers may pause GPS when the phone is locked.</div>' +
                '<div class="d-flex flex-wrap gap-3 mt-2 fs-11 text-muted">' +
                    '<span>GPS: <strong id="ajax-driver-gps-permission">Checking</strong></span>' +
                    '<span>Last update: <strong id="ajax-driver-last-location-update">No ping yet</strong></span>' +
                    '<span id="ajax-driver-stationary-state">Waiting for movement</span>' +
                '</div>' +
            '</div>' +
        '</div>' +
        '<div id="ajax-delivery-map" class="border rounded-3 mb-4" style="height: 320px; display: none;"></div>';
    }

    function renderOrderDetail(order) {
        var company = order.company || {};
        var station = order.station || {};

        return '<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">' +
            '<button type="button" class="btn btn-sm btn-light rounded-pill border js-assignable-orders-back"><i class="feather-arrow-left me-1"></i> Orders</button>' +
            '<span class="fs-12 text-muted fw-bold">ORDER DETAILS</span>' +
        '</div>' +
        '<div class="card border border-gray-3 shadow-sm mb-4" style="border-radius: 16px;">' +
            '<div class="card-body p-4">' +
                '<h6 class="fw-extrabold text-dark text-center mb-3.5">Workflow Status</h6>' +
                '<div class="stepper-container" style="max-width: 500px; margin: 0 auto;">' +
                    '<div class="position-relative">' +
                        '<div class="stepper-line-bg" style="left: 40px; right: 40px; top: 24px;"></div>' +
                        '<div class="stepper-line-active" style="left: 40px; top: 24px; width: ' + stepperWidth(order.status) + ';"></div>' +
                        '<div class="stepper-wrapper">' +
                            '<div class="stepper-item ' + stepperClass(order.status, 'transit') + '" style="width: 80px;"><div class="stepper-icon"><i class="feather-navigation"></i></div><span class="stepper-label">In Transit</span></div>' +
                            '<div class="stepper-item ' + stepperClass(order.status, 'pickup') + '" style="width: 80px;"><div class="stepper-icon"><i class="feather-package"></i></div><span class="stepper-label">Pickup</span></div>' +
                            '<div class="stepper-item ' + stepperClass(order.status, 'delivered') + '" style="width: 80px;"><div class="stepper-icon"><i class="feather-check"></i></div><span class="stepper-label">Delivered</span></div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>' +
        trackingPanelHtml(order) +
        '<div class="row">' +
            '<div class="col-12 col-md-7 col-lg-8 mb-4">' +
                '<div class="card border border-gray-3 shadow-sm h-100 overflow-hidden d-flex flex-column" style="border-radius: 16px;">' +
                    '<div class="card-header bg-transparent border-bottom border-gray-2 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">' +
                        '<h5 class="card-title mb-0 fw-extrabold text-dark">Order Information</h5>' +
                        '<span class="fs-15 fw-extrabold text-primary order-id-badge">#' + escapeHtml(order.order_number) + '</span>' +
                    '</div>' +
                    '<div class="card-body p-4 flex-grow-1">' +
                        '<div class="d-flex align-items-center mb-4 bg-light p-3 rounded-3 border border-gray-2 company-info-banner">' +
                            companyAvatar(order) +
                            '<div class="company-info-text" style="line-height: 1.3; min-width: 0;">' +
                                '<h6 class="fw-extrabold text-dark mb-0.5">' + escapeHtml(company.name || 'N/A') + '</h6>' +
                                '<span class="text-muted fs-11.5">Station: ' + escapeHtml(station.name || 'N/A') + (station.code ? ' (' + escapeHtml(station.code) + ')' : '') + '</span>' +
                            '</div>' +
                        '</div>' +
                        '<h6 class="fw-extrabold text-dark mb-3">Delivery Route</h6>' +
                        '<div class="route-timeline mb-4">' +
                            '<div class="timeline-item"><span class="text-muted fs-9.5 d-block text-uppercase fw-semibold mb-0.5">Pickup From</span><span class="fw-semibold text-dark fs-12.5 d-block" style="line-height: 1.3;">' + escapeHtml(order.pickup_location) + '</span></div>' +
                            '<div class="timeline-item drop mt-3"><span class="text-muted fs-9.5 d-block text-uppercase fw-semibold mb-0.5">Drop To</span><span class="fw-semibold text-dark fs-12.5 d-block" style="line-height: 1.3;">' + escapeHtml(order.drop_location) + '</span></div>' +
                        '</div>' +
                        '<div class="row g-3 border-top border-gray-2 pt-3 order-details-grid">' +
                            detailItem('feather-map text-primary', 'Distance', valueOr(order.distance_km, '0.00') + ' km') +
                            detailItem('feather-calendar text-primary', 'Expected Date', order.expected_delivery_date || 'N/A') +
                            detailItem('feather-clock text-primary', 'Assigned Date', order.created_at || 'N/A') +
                            (order.delivered_at ? detailItem('feather-check-circle text-success', 'Delivered Date', order.delivered_at, 'text-success') : '') +
                        '</div>' +
                    '</div>' +
                    '<div class="card-footer p-3 bg-light border-top border-gray-3">' + detailActionsHtml(order) + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col-12 col-md-5 col-lg-4 mb-4">' +
                '<div class="d-flex flex-column gap-3 h-100">' +
                    '<div class="card border border-gray-3 shadow-sm" style="border-radius: 16px;">' +
                        '<div class="card-header bg-transparent border-bottom border-gray-2 py-3 px-4"><h6 class="card-title mb-0 fw-extrabold text-dark">Manager Notes</h6></div>' +
                        '<div class="card-body p-4 text-muted fs-12" style="line-height: 1.4; min-height: 100px;">' + escapeHtml(order.notes || 'No special handling instructions provided.') + '</div>' +
                    '</div>' +
                    '<div class="card border border-gray-3 shadow-sm flex-grow-1" style="border-radius: 16px;">' +
                        '<div class="card-header bg-transparent border-bottom border-gray-2 py-3 px-4"><h6 class="card-title mb-0 fw-extrabold text-dark">Delivery Proof</h6></div>' +
                        '<div class="card-body p-4 d-flex flex-column justify-content-center">' + proofImagesHtml(order) + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function detailItem(iconClass, label, value, valueClass) {
        return '<div class="col-12 col-sm-6">' +
            '<div class="order-detail-item">' +
                '<div class="order-detail-icon"><i class="' + escapeHtml(iconClass) + '"></i></div>' +
                '<div class="order-detail-content">' +
                    '<span class="order-detail-label">' + escapeHtml(label) + '</span>' +
                    '<span class="order-detail-value ' + escapeHtml(valueClass || '') + '">' + escapeHtml(value) + '</span>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function showListPanel() {
        stopLiveTracking();
        currentOrder = null;
        $(selectors.detailPanel).addClass('d-none').empty();
        $(selectors.list).removeClass('d-none');
        $('.filter-wrapper').removeClass('d-none');
    }

    function showDetailPanel(order) {
        currentOrder = order || null;
        $(selectors.list).addClass('d-none');
        $('.filter-wrapper').addClass('d-none');
        $(selectors.detailPanel).removeClass('d-none').html(renderOrderDetail(order || {}));

        $('html, body').animate({ scrollTop: $(selectors.detailPanel).offset().top - 90 }, 250);

        if (order && order.status === 'Pickup') {
            startLiveTracking(order);
        } else {
            stopLiveTracking();
        }
    }

    function loadOrderDetail(dataUrl) {
        if (!dataUrl || dataUrl === '#') {
            showMessage('error', 'Order detail URL is missing.');
            return;
        }

        stopLiveTracking();
        $(selectors.list).addClass('d-none');
        $('.filter-wrapper').addClass('d-none');
        $(selectors.detailPanel).removeClass('d-none').html(detailLoadingState());

        $.ajax({
            url: dataUrl,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    showDetailPanel(response.data || {});
                    return;
                }

                $(selectors.detailPanel).html(detailErrorState((response && response.message) || 'Unable to load order details.'));
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                $(selectors.detailPanel).html(detailErrorState(response.message || 'Unable to load order details.'));
            }
        });
    }

    function updateTrackingPanel(status, detail, active) {
        var $status = $('#ajax-driver-tracking-status');
        var $detail = $('#ajax-driver-tracking-detail');
        var $icon = $('#ajax-driver-tracking-icon');

        if ($status.length) {
            $status.text(status);
            $status.attr('class', active === true
                ? 'badge bg-soft-success text-success fs-11'
                : (active === false ? 'badge bg-soft-danger text-danger fs-11' : 'badge bg-soft-warning text-warning fs-11'));
        }

        if ($detail.length && detail) {
            $detail.text(detail);
        }

        if ($icon.length) {
            $icon.attr('class', active === true
                ? 'feather-radio text-success'
                : (active === false ? 'feather-wifi-off text-danger' : 'feather-loader text-warning'));
        }
    }

    function formatElapsed(timestamp) {
        if (!timestamp) return 'No ping yet';
        var seconds = Math.max(0, Math.round((Date.now() - timestamp) / 1000));
        if (seconds < 60) return seconds + 's ago';
        return Math.round(seconds / 60) + 'm ago';
    }

    function calculateDistanceMeters(lat1, lon1, lat2, lon2) {
        var radius = 6371e3;
        var phi1 = lat1 * Math.PI / 180;
        var phi2 = lat2 * Math.PI / 180;
        var deltaPhi = (lat2 - lat1) * Math.PI / 180;
        var deltaLambda = (lon2 - lon1) * Math.PI / 180;
        var a = Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
            Math.cos(phi1) * Math.cos(phi2) *
            Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return radius * c;
    }

    function updateLastLocationText(timestamp) {
        $('#ajax-driver-last-location-update').text(formatElapsed(timestamp));
    }

    function startTrackingHealthTimer() {
        if (trackingHealthTimer) {
            clearInterval(trackingHealthTimer);
        }

        trackingHealthTimer = setInterval(function () {
            updateLastLocationText(lastSentTime);
        }, 5000);
    }

    function initTrackingMap(order, lat, lng) {
        if (typeof L === 'undefined' || !$('#ajax-delivery-map').length) {
            return;
        }

        $('#ajax-delivery-map').show();

        if (!trackingMap) {
            trackingMap = L.map('ajax-delivery-map', {
                center: [lat, lng],
                zoom: 13,
                zoomControl: true,
                attributionControl: true
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(trackingMap);
        }

        if (!trackingDriverMarker) {
            trackingDriverMarker = L.marker([lat, lng]).addTo(trackingMap).bindPopup('Your Location');
        } else {
            trackingDriverMarker.setLatLng([lat, lng]);
        }

        if (order.drop_latitude && order.drop_longitude) {
            if (trackingRouteLayer) {
                trackingMap.removeLayer(trackingRouteLayer);
            }
            trackingRouteLayer = L.polyline([[lat, lng], [order.drop_latitude, order.drop_longitude]], {
                color: '#3b82f6',
                weight: 4,
                opacity: 0.75
            }).addTo(trackingMap);
            trackingMap.fitBounds([[lat, lng], [order.drop_latitude, order.drop_longitude]], { padding: [35, 35] });
        } else {
            trackingMap.setView([lat, lng], 14);
        }
    }

    function postLocation(order, lat, lng, speed, heading) {
        if (!order || !order.location_url) {
            updateTrackingPanel('Tracking error', 'Location endpoint is missing for this order.', false);
            return;
        }

        $.ajax({
            url: order.location_url,
            method: 'POST',
            dataType: 'json',
            data: {
                latitude: lat,
                longitude: lng,
                speed: speed,
                heading: heading,
                accuracy: lastGpsAccuracy
            },
            success: function (response) {
                if (!response || !response.success) {
                    updateTrackingPanel('API rejected', (response && response.message) || 'Server rejected the location update.', false);
                    return;
                }

                lastSentLat = lat;
                lastSentLng = lng;
                lastSentTime = Date.now();
                updateLastLocationText(lastSentTime);
                updateTrackingPanel(response.broadcasted === false ? 'Saved, broadcast offline' : 'Live', response.message || 'Location is being sent to the manager.', true);
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                updateTrackingPanel('Location failed', response.message || 'Network failed while sending location.', false);
            }
        });
    }

    function startLiveTracking(order) {
        stopLiveTracking();
        lastSentLat = null;
        lastSentLng = null;
        lastSentTime = 0;
        lastGpsAccuracy = null;

        if (!navigator.geolocation) {
            updateTrackingPanel('GPS unavailable', 'This browser does not support geolocation.', false);
            $('#ajax-driver-gps-permission').text('Not supported');
            return;
        }

        updateTrackingPanel('Starting GPS', 'Waiting for the first location coordinate.', null);
        $('#ajax-driver-gps-permission').text('Requesting');
        startTrackingHealthTimer();

        geoWatchId = navigator.geolocation.watchPosition(function (position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            var speedKmh = position.coords.speed !== null ? position.coords.speed * 3.6 : null;
            var heading = position.coords.heading;
            var now = Date.now();
            var shouldSend = false;
            var distanceMoved = null;

            lastGpsAccuracy = Math.round(position.coords.accuracy || 0);
            $('#ajax-driver-gps-permission').text('granted');
            updateTrackingPanel('GPS active', 'Coordinate received with about ' + lastGpsAccuracy + 'm accuracy.', true);
            initTrackingMap(order, lat, lng);

            if (lastSentLat === null || lastSentLng === null) {
                shouldSend = true;
            } else {
                distanceMoved = calculateDistanceMeters(lastSentLat, lastSentLng, lat, lng);
                if ((now - lastSentTime) >= MIN_TIME_INTERVAL && (distanceMoved >= MIN_DISTANCE_METERS || (speedKmh && speedKmh > 2))) {
                    shouldSend = true;
                } else if ((now - lastSentTime) >= STATIONARY_HEARTBEAT) {
                    shouldSend = true;
                }
            }

            $('#ajax-driver-stationary-state').text(distanceMoved !== null && distanceMoved < MIN_DISTANCE_METERS ? 'Driver is stationary' : 'Driver is moving');

            if (shouldSend) {
                postLocation(order, lat, lng, speedKmh, heading);
            }
        }, function (error) {
            $('#ajax-driver-gps-permission').text(error.code === error.PERMISSION_DENIED ? 'denied' : 'unavailable');
            updateTrackingPanel('GPS unavailable', error.message || 'Location permission or GPS signal is unavailable.', false);
        }, {
            enableHighAccuracy: true,
            maximumAge: 5000,
            timeout: 15000
        });
    }

    function stopLiveTracking() {
        if (geoWatchId !== null) {
            navigator.geolocation.clearWatch(geoWatchId);
            geoWatchId = null;
        }

        if (trackingHealthTimer) {
            clearInterval(trackingHealthTimer);
            trackingHealthTimer = null;
        }

        if (trackingMap) {
            trackingMap.remove();
            trackingMap = null;
            trackingDriverMarker = null;
            trackingRouteLayer = null;
        }
    }

    window.loadAssignableOrders = function () {
        var listUrl = $(selectors.config).data('list-url');

        if (!listUrl || !$(selectors.list).length) {
            return;
        }

        $(selectors.list).html(loadingState());

        $.ajax({
            url: listUrl,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    updateCounts(response.counts || {});
                    renderOrders(response.data || []);
                    return;
                }

                $(selectors.list).html(errorState((response && response.message) || 'Unable to load assigned orders.'));
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                var message = response.message || 'Unable to load assigned orders.';

                $(selectors.list).html(errorState(message));
                showMessage('error', message);
            }
        });
    };

    function submitPickup($form) {
        if (!$form.length) {
            return;
        }

        var actionUrl = $form.attr('action');
        var $button = $form.find('button[type="submit"]');
        var originalHtml = $button.html();

        if (!actionUrl) {
            showMessage('error', 'Pickup action URL is missing.');
            return;
        }

        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Updating...');

        $.ajax({
            url: actionUrl,
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                if (!response || !response.success) {
                    showMessage('error', (response && response.message) || 'Unable to pickup order.');
                    return;
                }

                if (response.data) {
                    showMessage('success', response.message || 'Order status updated to Pickup.');
                    showDetailPanel(response.data);
                    loadAssignableOrders();
                    return;
                }

                showMessage('success', response.message || 'Order status updated to Pickup.');
                loadAssignableOrders();
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                var message = response.message || 'Unable to pickup order.';

                showMessage('error', message);
                loadAssignableOrders();
            },
            complete: function () {
                $button.prop('disabled', false).html(originalHtml);
            }
        });
    }

    function openDeliveryModal() {
        if (!currentOrder || !currentOrder.deliver_url) {
            showMessage('error', 'Delivery action URL is missing.');
            return;
        }

        $(selectors.deliveryForm).attr('action', currentOrder.deliver_url);
        $(selectors.deliveryProofInput).val('');
        $(selectors.deliveryPreview).empty();
        $(selectors.deliveryError).text('');

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(document.querySelector(selectors.deliveryModal)).show();
            return;
        }

        $(selectors.deliveryModal).modal('show');
    }

    function closeDeliveryModal() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(document.querySelector(selectors.deliveryModal)).hide();
            return;
        }

        $(selectors.deliveryModal).modal('hide');
    }

    function renderDeliveryPreview(files) {
        var $preview = $(selectors.deliveryPreview);

        if (!$preview.length) {
            return;
        }

        files = files || [];
        $preview.empty();

        $.each(files, function (_, file) {
            if (!file || !file.type || file.type.indexOf('image/') !== 0) {
                return;
            }

            var url = URL.createObjectURL(file);
            $preview.append(
                '<div class="border rounded p-1 bg-white shadow-sm" style="width:70px;height:70px;">' +
                    '<img src="' + escapeHtml(url) + '" class="w-100 h-100 rounded" style="object-fit:cover;">' +
                '</div>'
            );
        });
    }

    function submitDelivery($form) {
        var actionUrl = $form.attr('action');
        var files = $(selectors.deliveryProofInput)[0] ? $(selectors.deliveryProofInput)[0].files : [];
        var $button = $form.find('button[type="submit"]');
        var originalHtml = $button.html();

        $(selectors.deliveryError).text('');

        if (!actionUrl) {
            $(selectors.deliveryError).text('Delivery action URL is missing.');
            return;
        }

        if (!files || files.length === 0) {
            $(selectors.deliveryError).text('You must upload at least one delivery proof image.');
            return;
        }

        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...');

        $.ajax({
            url: actionUrl,
            method: 'POST',
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (!response || !response.success) {
                    $(selectors.deliveryError).text((response && response.message) || 'Unable to complete delivery.');
                    return;
                }

                closeDeliveryModal();
                stopLiveTracking();
                showMessage('success', response.message || 'Order successfully delivered!');

                if (response.data) {
                    showDetailPanel(response.data);
                }

                loadAssignableOrders();
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                var message = response.message || 'Unable to complete delivery.';

                if (response.errors) {
                    $.each(response.errors, function (_, messages) {
                        message = $.isArray(messages) ? messages[0] : messages;
                        return false;
                    });
                }

                $(selectors.deliveryError).text(message);
                showMessage('error', message);
            },
            complete: function () {
                $button.prop('disabled', false).html(originalHtml);
            }
        });
    }

    $(function () {
        if (!$(selectors.config).length) {
            return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        loadAssignableOrders();

        $(document).on('click', selectors.filterButton, function () {
            currentStatus = $(this).data('status') || 'all';
            applyFilter();
        });

        $(document).on('click', selectors.card, function () {
            var url = $(this).data('url');

            loadOrderDetail(url);
        });

        $(document).on('click', selectors.card + ' a, ' + selectors.card + ' button, ' + selectors.card + ' form', function (event) {
            event.stopPropagation();
        });

        $(document).on('submit', selectors.pickupForm, function (event) {
            event.preventDefault();
            event.stopPropagation();
            submitPickup($(this));
        });

        $(document).on('click', selectors.backToList, function () {
            showListPanel();
        });

        $(document).on('click', selectors.openDeliveryModal, function () {
            openDeliveryModal();
        });

        $(document).on('change', selectors.deliveryProofInput, function () {
            renderDeliveryPreview(this.files);
        });

        $(document).on('submit', selectors.deliveryForm, function (event) {
            event.preventDefault();
            submitDelivery($(this));
        });

        $(window).on('beforeunload', function () {
            stopLiveTracking();
        });
    });
})(jQuery);
