(function ($) {
    'use strict';

    const selectors = {
        config: '#driverActivitiesConfig',
        form: '#driverActivitiesFilterForm',
        search: '#driverActivitiesSearchInput',
        driver: '#driverActivitiesDriverFilter',
        status: '#driverActivitiesStatusFilter',
        reset: '#driverActivitiesResetFilter',
        tableBody: '#driverActivitiesTableBody',
        pagination: '#driverActivitiesPagination',
        recordCount: '#driverActivitiesRecordCount',
        kpiTotal: '#driverActivitiesKpiTotal',
        kpiTransit: '#driverActivitiesKpiTransit',
        kpiPickup: '#driverActivitiesKpiPickup',
        kpiDelivered: '#driverActivitiesKpiDelivered'
    };

    let isLoading = false;

    function getConfig() {
        const $config = $(selectors.config);

        return {
            listUrl: $config.data('list-url') || ''
        };
    }

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function setupAjax() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json'
            }
        });
    }

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
    }

    function escapeAttribute(value) {
        return escapeHtml(value).replace(/"/g, '&quot;');
    }

    function safeText(value, fallback) {
        return value === null || value === undefined || value === '' ? fallback : value;
    }

    function notify(message, type) {
        const level = type || 'info';

        if (window.toastr) {
            window.toastr[level === 'error' ? 'error' : 'success'](message);
            return;
        }

        if (window.Swal) {
            window.Swal.fire({
                icon: level === 'error' ? 'error' : 'success',
                title: message,
                timer: 1800,
                showConfirmButton: false
            });
            return;
        }

        if (level === 'error') {
            console.error(message);
        }
    }

    function filterParams(page) {
        return {
            search: $(selectors.search).val() || '',
            driver_id: $(selectors.driver).val() || '',
            status: $(selectors.status).val() || '',
            page: page || 1
        };
    }

    function updateKpis(kpis) {
        if (!kpis) return;

        $(selectors.kpiTotal).text(kpis.total || 0);
        $(selectors.kpiTransit).text(kpis.transit || 0);
        $(selectors.kpiPickup).text(kpis.pickup || 0);
        $(selectors.kpiDelivered).text(kpis.delivered || 0);
    }

    function updateRecordCount(meta) {
        const showing = meta && meta.total ? (meta.to - meta.from + 1) : 0;
        $(selectors.recordCount).text(`Showing ${showing} records`);
    }

    function driverAvatar(activity) {
        if (activity.driver_photo_url) {
            return `<img src="${escapeAttribute(activity.driver_photo_url)}" alt="driver avatar" class="rounded-circle me-3 border border-2 border-primary" style="width: 38px; height: 38px; object-fit: cover;">`;
        }

        return `<div class="avatar-text avatar-md bg-soft-primary text-primary rounded-circle me-3 d-flex align-items-center justify-content-center border border-2 border-primary" style="width: 38px; height: 38px; font-weight: 700; font-size: 13px;">
            ${escapeHtml(activity.driver_initials || 'UN')}
        </div>`;
    }

    function companyLogo(activity) {
        const companyName = safeText(activity.company_name, 'N/A');

        if (activity.company_logo_url) {
            return `<img src="${escapeAttribute(activity.company_logo_url)}" alt="airline logo" class="rounded border p-0.5" style="width: 26px; height: 26px; object-fit: cover;">`;
        }

        return `<div class="bg-soft-primary text-primary rounded d-flex align-items-center justify-content-center border" style="width: 26px; height: 26px; font-size: 9px; font-weight: 800;">
            ${escapeHtml(String(companyName).charAt(0) || 'N')}
        </div>`;
    }

    function progressWidth(status) {
        if (status === 'Pickup') return '50%';
        if (status === 'Delivered') return '100%';
        return '0%';
    }

    function proofCell(activity) {
        const status = activity.status || '';

        if (status === 'Delivered') {
            const images = Array.isArray(activity.delivery_proof_images) ? activity.delivery_proof_images : [];
            const proofHtml = images.length
                ? `<div class="d-flex -space-x-8 hover-proof-container">
                    ${images.map((image, index) => `
                        <a href="javascript:void(0);"
                           onclick="showProofImage('${escapeAttribute(image.url)}', '${escapeAttribute(image.label)}')"
                           data-bs-toggle="modal"
                           data-bs-target="#proofImageModal"
                           class="proof-thumb-link rounded border shadow-sm p-0.5 bg-white overflow-hidden"
                           style="width: 38px; height: 38px; margin-left: ${index > 0 ? '-14px' : '0'}; z-index: ${10 - index};">
                            <img src="${escapeAttribute(image.url)}" class="w-100 h-100 rounded" style="object-fit: cover;">
                        </a>
                    `).join('')}
                </div>`
                : '<span class="fs-11 text-muted"><i class="feather-alert-circle text-warning me-1"></i>No images</span>';

            return `<div class="d-flex align-items-center gap-2.5">
                ${proofHtml}
                <div style="line-height: 1.35;">
                    <span class="badge bg-soft-success text-success px-2 py-0.5 fs-10.5 rounded fw-extrabold text-uppercase d-block mb-0.5 text-center">Delivered</span>
                    <span class="text-muted fs-10 fw-semibold d-block">${escapeHtml(activity.delivered_at || '')}</span>
                </div>
            </div>`;
        }

        if (status === 'Pickup') {
            return `<div class="d-flex align-items-center gap-2">
                <button type="button"
                        class="btn btn-sm btn-light-warning text-warning border-0 fw-bold fs-11 js-track-order"
                        data-order-id="${escapeAttribute(activity.id)}"
                        data-show-url="${escapeAttribute(activity.show_url)}">
                    <i class="feather-map-pin me-1"></i> Track Route
                </button>
                <span class="fs-11 text-muted">Live after pickup</span>
            </div>`;
        }

        return `<div class="d-flex align-items-center gap-2 text-muted">
            <i class="feather-clock fs-14"></i>
            <span class="fs-12 fw-medium">Pending delivery...</span>
        </div>`;
    }

    function rowHtml(activity) {
        const status = activity.status || '';
        const pickup = safeText(activity.pickup_location, 'N/A');
        const drop = safeText(activity.drop_location, 'N/A');
        const companyName = safeText(activity.company_name, 'N/A');
        const orderNumber = safeText(activity.order_number, '#ORD-00000');

        return `<tr>
            <td class="ps-4">
                <div class="d-flex align-items-center">
                    ${driverAvatar(activity)}
                    <div style="line-height: 1.3;">
                        <span class="fw-bold text-dark fs-12.5 d-block">${escapeHtml(safeText(activity.driver_name, 'Unassigned'))}</span>
                        <span class="text-muted fs-11.5">${escapeHtml(safeText(activity.driver_email, 'N/A'))}</span>
                    </div>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    ${companyLogo(activity)}
                    <div style="line-height: 1.25;">
                        <a href="${escapeAttribute(activity.show_url)}" class="fw-extrabold text-primary fs-12.5 d-block hover-underline">${escapeHtml(orderNumber)}</a>
                        <span class="text-muted fs-11">${escapeHtml(companyName)}</span>
                    </div>
                </div>
            </td>
            <td>
                <div style="line-height: 1.35; padding: 2px 0;">
                    <div class="d-flex align-items-baseline gap-1.5 mb-1.5">
                        <span class="badge bg-soft-info text-info p-0.5 rounded-circle fs-8 d-flex align-items-center justify-content-center" style="width: 11px; height: 11px;"><i class="feather-navigation"></i></span>
                        <span class="text-dark fw-medium fs-11.5 line-clamp-1" title="${escapeAttribute(pickup)}">${escapeHtml(pickup)}</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-1.5">
                        <span class="badge bg-soft-success text-success p-0.5 rounded-circle fs-8 d-flex align-items-center justify-content-center" style="width: 11px; height: 11px;"><i class="feather-map-pin"></i></span>
                        <span class="text-dark fw-medium fs-11.5 line-clamp-1" title="${escapeAttribute(drop)}">${escapeHtml(drop)}</span>
                    </div>
                </div>
            </td>
            <td>
                <div class="d-flex justify-content-center">
                    <div class="table-stepper">
                        <div class="position-relative">
                            <div class="table-stepper-line"></div>
                            <div class="table-stepper-line-active" style="width: ${progressWidth(status)};"></div>
                            <div class="table-stepper-wrapper">
                                <div class="table-stepper-item ${['In Progress', 'Pickup', 'Delivered'].indexOf(status) !== -1 ? 'active' : ''}">
                                    <div class="table-stepper-icon"><i class="feather-navigation"></i></div>
                                    <span class="table-stepper-label">Transit</span>
                                </div>
                                <div class="table-stepper-item ${['Pickup', 'Delivered'].indexOf(status) !== -1 ? 'active-warning' : ''}">
                                    <div class="table-stepper-icon"><i class="feather-package"></i></div>
                                    <span class="table-stepper-label">Pickup</span>
                                </div>
                                <div class="table-stepper-item ${status === 'Delivered' ? 'active-success' : ''}">
                                    <div class="table-stepper-icon"><i class="feather-check"></i></div>
                                    <span class="table-stepper-label">Done</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
            <td class="pe-4">${proofCell(activity)}</td>
        </tr>`;
    }

    function emptyRow() {
        return `<tr>
            <td colspan="5" class="text-center py-5">
                <div class="py-4">
                    <i class="feather-activity fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="fw-bold mb-2 text-dark">No Driver Activities Found</h5>
                    <p class="text-muted fs-12.5 mb-0">No luggage assignments match the specified search query or filters.</p>
                </div>
            </td>
        </tr>`;
    }

    function renderTable(items) {
        const activities = Array.isArray(items) ? items : [];
        $(selectors.tableBody).html(activities.length ? activities.map(rowHtml).join('') : emptyRow());

        if (window.feather) {
            window.feather.replace();
        }
    }

    function renderPagination(meta) {
        const $pagination = $(selectors.pagination);

        if (!meta || meta.last_page <= 1) {
            $pagination.addClass('d-none').removeClass('d-flex').html('');
            return;
        }

        const current = Number(meta.current_page || 1);
        const last = Number(meta.last_page || 1);
        let pages = [];

        for (let page = 1; page <= last; page += 1) {
            if (page === 1 || page === last || Math.abs(page - current) <= 2) {
                pages.push(page);
            }
        }

        pages = pages.filter((page, index) => pages.indexOf(page) === index);

        let html = '<nav><ul class="pagination mb-0">';
        html += `<li class="page-item ${current <= 1 ? 'disabled' : ''}"><a class="page-link driver-activities-page" href="#" data-page="${current - 1}">Previous</a></li>`;

        let previous = 0;
        pages.forEach(page => {
            if (previous && page - previous > 1) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }

            html += `<li class="page-item ${page === current ? 'active' : ''}"><a class="page-link driver-activities-page" href="#" data-page="${page}">${page}</a></li>`;
            previous = page;
        });

        html += `<li class="page-item ${current >= last ? 'disabled' : ''}"><a class="page-link driver-activities-page" href="#" data-page="${current + 1}">Next</a></li>`;
        html += '</ul></nav>';

        $pagination.removeClass('d-none').addClass('d-flex').html(html);
    }

    function setLoadingState(loading) {
        isLoading = loading;
        $(selectors.form).find('button, input, select').prop('disabled', loading);
        $(selectors.tableBody).toggleClass('opacity-50', loading);
    }

    window.loadDriverActivities = function loadDriverActivities(page) {
        const config = getConfig();

        if (!config.listUrl || isLoading) {
            return;
        }

        setLoadingState(true);

        $.ajax({
            url: config.listUrl,
            type: 'GET',
            data: filterParams(page || 1),
            success: function (response) {
                if (!response || response.success !== true) {
                    notify((response && response.message) || 'Unable to load driver activities.', 'error');
                    return;
                }

                updateKpis(response.kpis || {});
                updateRecordCount(response.meta || {});
                renderTable(response.data || []);
                renderPagination(response.meta || {});
            },
            error: function (xhr) {
                const response = xhr.responseJSON || {};
                notify(response.message || 'Unable to load driver activities.', 'error');
            },
            complete: function () {
                setLoadingState(false);
            }
        });
    };

    window.resetDriverActivitiesFilter = function resetDriverActivitiesFilter() {
        $(selectors.search).val('');
        $(selectors.driver).val('').trigger('change');
        $(selectors.status).val('').trigger('change');
        window.loadDriverActivities(1);
    };

    function bindEvents() {
        $(document).on('submit', selectors.form, function (event) {
            event.preventDefault();
            window.loadDriverActivities(1);
        });

        $(document).on('click', selectors.reset, function (event) {
            event.preventDefault();
            window.resetDriverActivitiesFilter();
        });

        $(document).on('click', '.driver-activities-page', function (event) {
            event.preventDefault();

            const page = Number($(this).data('page'));
            if (!page || $(this).closest('.page-item').hasClass('disabled')) {
                return;
            }

            window.loadDriverActivities(page);
        });

        $(document).on('click', `${selectors.tableBody} .js-track-order`, function () {
            const orderId = String($(this).data('order-id') || '');
            const $liveButton = $(`.live-route-item[data-order-id="${orderId}"]`);

            if ($liveButton.length) {
                $liveButton.first().trigger('click');
                return;
            }

            const showUrl = $(this).data('show-url');
            if (showUrl) {
                window.location.href = showUrl;
            }
        });
    }

    $(function () {
        if (!$(selectors.config).length) {
            return;
        }

        setupAjax();
        bindEvents();
    });
})(jQuery);
