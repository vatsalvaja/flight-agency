(function ($) {
    'use strict';

    const selectors = {
        config: '#reportsConfig',
        form: '#report-filter-form',
        reset: '#reportsResetFilter',
        pagination: '#reportsPagination',
        logBody: '#reportsLogTableBody',
        logCount: '#reportsLogCount',
        managerBody: '#reportsManagerTableBody',
        driverBody: '#reportsDriverTableBody',
        driverBodyManager: '#reportsDriverTableBodyManager',
        companyBody: '#reportsCompanyTableBody',
        stationBody: '#reportsStationTableBody'
    };

    let isLoading = false;

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
    }

    function escapeAttribute(value) {
        return escapeHtml(value).replace(/"/g, '&quot;');
    }

    function safe(value, fallback) {
        return value === null || value === undefined || value === '' ? fallback : value;
    }

    function setupAjax() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '',
                'Accept': 'application/json'
            }
        });
    }

    function notify(message, type) {
        if (window.toastr) {
            window.toastr[type === 'error' ? 'error' : 'success'](message);
            return;
        }

        if (window.Swal) {
            window.Swal.fire({
                icon: type === 'error' ? 'error' : 'success',
                title: message,
                timer: 1800,
                showConfirmButton: false
            });
            return;
        }

        if (type === 'error') {
            console.error(message);
        }
    }

    function listUrl() {
        return $(selectors.config).data('list-url') || '';
    }

    function formData(page) {
        const data = $(selectors.form).serializeArray();
        data.push({ name: 'page', value: page || 1 });
        return $.param(data);
    }

    function updateKpis(kpis) {
        if (!kpis) return;

        $('#reportsKpiTotal').text(kpis.total_assignments || 0);
        $('#reportsKpiDelivered').text(kpis.delivered_count || 0);
        $('#reportsKpiActive').text(kpis.active_count || 0);
        $('#reportsKpiAvgTime').text(`${safe(kpis.avg_time_hours, 0)} hrs`);
        $('#reportsTotalDistance').text(`${safe(kpis.total_distance, 0)} km`);
        $('#reportsDeliveredDistance').text(`${safe(kpis.delivered_distance, 0)} km`);
        $('#reportsAverageDistance').text(`${safe(kpis.average_distance, 0)} km`);
    }

    function updateCharts(charts, kpis) {
        if (!charts) return;

        const dailyTrend = Array.isArray(charts.daily_trend) ? charts.daily_trend : [];
        const categories = dailyTrend.map(item => safe(item.date, ''));

        if (window.reportsOpsChart) {
            window.reportsOpsChart.updateOptions({
                xaxis: { categories: categories }
            });
            window.reportsOpsChart.updateSeries([
                { name: 'In Progress', data: dailyTrend.map(item => item.in_progress || 0) },
                { name: 'Picked Up', data: dailyTrend.map(item => item.pickup || 0) },
                { name: 'Delivered', data: dailyTrend.map(item => item.delivered || 0) }
            ]);
        }

        if (window.reportsCompanyChart) {
            window.reportsCompanyChart.updateOptions({
                xaxis: { categories: charts.company_names || [] }
            });
            window.reportsCompanyChart.updateSeries([
                { name: 'Luggage Volume', data: charts.company_counts || [] }
            ]);
        }

        if (window.reportsDonutChart) {
            const distribution = charts.status_distribution || {};
            window.reportsDonutChart.updateOptions({
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                total: {
                                    show: true,
                                    label: 'Total Volume',
                                    formatter: function () {
                                        return kpis && kpis.total_assignments ? kpis.total_assignments : 0;
                                    }
                                }
                            }
                        }
                    }
                }
            });
            window.reportsDonutChart.updateSeries([
                distribution['In Progress'] || 0,
                distribution.Pickup || 0,
                distribution.Delivered || 0
            ]);
        }
    }

    function managerRows(items) {
        const rows = Array.isArray(items) ? items : [];
        $('#reportsManagerCount').text(`${rows.length} Managers`);

        if (!rows.length) {
            return '<tr><td colspan="3" class="text-center text-muted py-4">No manager data found for this range</td></tr>';
        }

        return rows.map(item => `<tr>
            <td class="fw-semibold text-dark">${escapeHtml(safe(item.name, 'System/Admin'))}</td>
            <td class="text-center"><span class="badge bg-soft-primary text-primary px-3 py-1.5">${escapeHtml(item.count || 0)}</span></td>
            <td class="text-end text-dark fw-medium">${escapeHtml(item.distance || 0)} km</td>
        </tr>`).join('');
    }

    function driverRows(items) {
        const rows = Array.isArray(items) ? items : [];
        $('#reportsDriverCount, #reportsDriverCountManager').text(`${rows.length} Drivers`);

        if (!rows.length) {
            return '<tr><td colspan="5" class="text-center text-muted py-4">No driver data found for this range</td></tr>';
        }

        return rows.map(item => `<tr>
            <td class="fw-semibold text-dark">${escapeHtml(safe(item.name, 'Unassigned'))}</td>
            <td class="text-center"><span class="badge bg-light text-dark px-2.5 py-1">${escapeHtml(item.total || 0)}</span></td>
            <td class="text-center"><span class="badge bg-soft-success text-success px-2.5 py-1">${escapeHtml(item.completed || 0)}</span></td>
            <td class="text-center font-weight-bold text-dark">${escapeHtml(item.rate || 0)}%</td>
            <td class="text-end text-dark fw-medium">${escapeHtml(item.distance || 0)} km</td>
        </tr>`).join('');
    }

    function simpleBreakdownRows(items, emptyText, badgeClass) {
        const rows = Array.isArray(items) ? items : [];

        if (!rows.length) {
            return `<tr><td colspan="3" class="text-center text-muted py-4">${escapeHtml(emptyText)}</td></tr>`;
        }

        return rows.map(item => `<tr>
            <td class="fw-semibold text-dark">${escapeHtml(safe(item.name, 'N/A'))}</td>
            <td class="text-center"><span class="badge ${badgeClass} px-3 py-1.5">${escapeHtml(item.count || 0)}</span></td>
            <td class="text-end text-dark fw-medium">${escapeHtml(item.distance || 0)} km</td>
        </tr>`).join('');
    }

    function statusBadge(status) {
        if (status === 'Delivered') {
            return '<span class="badge bg-soft-success text-success px-2.5 py-1 fs-11">Delivered</span>';
        }

        if (status === 'Pickup') {
            return '<span class="badge bg-soft-warning text-warning px-2.5 py-1 fs-11">Pickup</span>';
        }

        return '<span class="badge bg-soft-primary text-primary px-2.5 py-1 fs-11">In Progress</span>';
    }

    function logRows(items) {
        const rows = Array.isArray(items) ? items : [];

        if (!rows.length) {
            return `<tr>
                <td colspan="9" class="text-center text-muted py-5">
                    <i class="feather-alert-triangle fs-2 text-warning mb-2 d-block"></i>
                    No luggage assignments found matching selected filters.
                </td>
            </tr>`;
        }

        return rows.map(row => `<tr>
            <td class="fw-semibold text-dark">${escapeHtml(safe(row.ref_id, '#'))}</td>
            <td>${escapeHtml(safe(row.created_at, 'N/A'))}</td>
            <td><span class="fw-semibold text-dark">${escapeHtml(safe(row.company_name, 'N/A'))}</span></td>
            <td>
                <div class="d-flex flex-column">
                    <span class="fs-12 text-dark"><i class="feather-map-pin text-primary me-1 fs-10"></i>${escapeHtml(safe(row.station_name, 'N/A'))}</span>
                    <span class="fs-11 text-muted"><i class="feather-arrow-right text-muted me-1 fs-10"></i>${escapeHtml(safe(row.drop_location, 'N/A'))}</span>
                </div>
            </td>
            <td><span class="text-dark">${escapeHtml(safe(row.manager_name, 'System/Admin'))}</span></td>
            <td><span class="text-dark">${escapeHtml(safe(row.driver_name, 'Unassigned'))}</span></td>
            <td>${escapeHtml(safe(row.distance_km, 0))} km</td>
            <td>${statusBadge(row.status)}</td>
            <td>${row.has_proof ? '<span class="badge bg-light text-success fs-10" title="Proof uploaded"><i class="feather-image me-1"></i>Yes</span>' : '<span class="badge bg-light text-muted fs-10">-</span>'}</td>
        </tr>`).join('');
    }

    function updateBreakdowns(breakdowns) {
        if (!breakdowns) return;

        $(selectors.managerBody).html(managerRows(breakdowns.manager_wise));
        const driverHtml = driverRows(breakdowns.driver_wise);
        $(selectors.driverBody).html(driverHtml);
        $(selectors.driverBodyManager).html(driverHtml);
        $(selectors.companyBody).html(simpleBreakdownRows(breakdowns.company_wise, 'No company records available', 'bg-soft-primary text-primary'));
        $(selectors.stationBody).html(simpleBreakdownRows(breakdowns.station_wise, 'No station records available', 'bg-soft-info text-info'));
    }

    function updateLogs(assignments, meta) {
        $(selectors.logBody).html(logRows(assignments));

        const from = meta && meta.from ? meta.from : 0;
        const to = meta && meta.to ? meta.to : 0;
        const total = meta && meta.total ? meta.total : 0;
        $(selectors.logCount).text(`Showing ${from}-${to} of ${total} records`);

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
        html += `<li class="page-item ${current <= 1 ? 'disabled' : ''}"><a class="page-link reports-page-link" href="#" data-page="${current - 1}">Previous</a></li>`;

        let previous = 0;
        pages.forEach(page => {
            if (previous && page - previous > 1) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }

            html += `<li class="page-item ${page === current ? 'active' : ''}"><a class="page-link reports-page-link" href="#" data-page="${page}">${page}</a></li>`;
            previous = page;
        });

        html += `<li class="page-item ${current >= last ? 'disabled' : ''}"><a class="page-link reports-page-link" href="#" data-page="${current + 1}">Next</a></li>`;
        html += '</ul></nav>';

        $pagination.removeClass('d-none').addClass('d-flex').html(html);
    }

    function setLoading(loading) {
        isLoading = loading;
        $(selectors.form).find('button, input, select').prop('disabled', loading);
        $('.tab-content').toggleClass('opacity-50', loading);
    }

    window.loadReports = function loadReports(page) {
        if (!listUrl() || isLoading) return;

        setLoading(true);

        $.ajax({
            url: listUrl(),
            type: 'GET',
            data: formData(page || 1),
            success: function (response) {
                if (!response || response.success !== true || !response.data) {
                    notify((response && response.message) || 'Unable to load reports.', 'error');
                    return;
                }

                const data = response.data;
                updateKpis(data.kpis);
                updateCharts(data.charts, data.kpis);
                updateBreakdowns(data.breakdowns);
                updateLogs(data.assignments, data.meta);
                renderPagination(data.meta);

                if (data.date_range) {
                    $('#report-date-range').val(data.date_range);
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON || {};
                notify(response.message || 'Unable to load reports.', 'error');
            },
            complete: function () {
                setLoading(false);
            }
        });
    };

    window.resetReportsFilter = function resetReportsFilter() {
        const $form = $(selectors.form);

        $form.find('select[name="date_preset"]').val('monthly').trigger('change');
        $form.find('select[name="company_id"], select[name="station_id"], select[name="manager_id"], select[name="driver_id"], select[name="status"]').val('').trigger('change');
        $('#custom-date-container').hide();
        window.loadReports(1);
    };

    function bindEvents() {
        $(document).on('submit', selectors.form, function (event) {
            event.preventDefault();
            window.loadReports(1);
        });

        $(document).on('click', selectors.reset, function (event) {
            event.preventDefault();
            window.resetReportsFilter();
        });

        $(document).on('click', '.reports-page-link', function (event) {
            event.preventDefault();
            const page = Number($(this).data('page'));

            if (!page || $(this).closest('.page-item').hasClass('disabled')) {
                return;
            }

            window.loadReports(page);
        });
    }

    $(function () {
        if (!$(selectors.config).length) return;

        setupAjax();
        bindEvents();
    });
})(jQuery);
