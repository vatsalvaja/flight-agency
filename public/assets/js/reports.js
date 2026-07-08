(function ($) {
    'use strict';

    const selectors = {
        config: '#reportsConfig',
        form: '#report-filter-form',
        reset: '#reportsResetFilter',
        exportCsv: '#reportsExportCsv',
        print: '#reportsPrint',
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
    let currentReportData = null;

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
    }

    function escapeAttribute(value) {
        return escapeHtml(value).replace(/"/g, '&quot;');
    }

    function safe(value, fallback) {
        return value === null || value === undefined || value === '' ? fallback : value;
    }

    function normalizeRows(items) {
        if (Array.isArray(items)) {
            return items;
        }

        if (items && Array.isArray(items.data)) {
            return items.data;
        }

        if (items && typeof items === 'object') {
            return Object.keys(items)
                .filter(key => !Number.isNaN(Number(key)))
                .sort((a, b) => Number(a) - Number(b))
                .map(key => items[key]);
        }

        return [];
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

    function exportUrl() {
        return $(selectors.config).data('export-url') || '';
    }

    function configValue(name, fallback) {
        const value = $(selectors.config).data(name);
        return value === null || value === undefined || value === '' ? fallback : value;
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
        const rows = normalizeRows(items);
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
        const rows = normalizeRows(items);
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
        const rows = normalizeRows(items);

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
        const rows = normalizeRows(items);

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

    function initPlugins() {
        if ($.fn.daterangepicker && $('#report-date-range').length) {
            $('#report-date-range').daterangepicker({
                autoUpdateInput: true,
                locale: {
                    format: 'MM/DD/YYYY',
                    cancelLabel: 'Clear'
                }
            });
        }

        if ($.fn.select2) {
            $('.select2-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('.filter-card')
            });
        }

        $('#report-date-preset').on('change', function () {
            if ($(this).val() === 'custom') {
                $('#custom-date-container').slideDown(200);
            } else {
                $('#custom-date-container').slideUp(200);
            }
        });
    }

    function initCharts() {
        if (typeof ApexCharts === 'undefined') {
            return;
        }

        const opsElement = document.querySelector('#operational-activity-chart');
        if (opsElement && !window.reportsOpsChart) {
            window.reportsOpsChart = new ApexCharts(opsElement, {
                series: [
                    { name: 'In Progress', data: [] },
                    { name: 'Picked Up', data: [] },
                    { name: 'Delivered', data: [] }
                ],
                chart: {
                    type: 'bar',
                    height: 320,
                    stacked: true,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false }
                },
                plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 1, colors: ['transparent'] },
                colors: ['#3b82f6', '#f59e0b', '#10b981'],
                xaxis: { categories: [], labels: { rotate: -45, style: { fontSize: '10px' } } },
                yaxis: {
                    title: { text: 'Luggage Count' },
                    tickAmount: 5,
                    labels: { formatter: function (value) { return Math.round(value); } }
                },
                fill: { opacity: 1 },
                legend: { position: 'bottom', fontFamily: 'Inter, sans-serif' }
            });
            window.reportsOpsChart.render();
        }

        const companyElement = document.querySelector('#company-volume-chart');
        if (companyElement && !window.reportsCompanyChart) {
            window.reportsCompanyChart = new ApexCharts(companyElement, {
                series: [{ name: 'Luggage Volume', data: [] }],
                chart: { type: 'bar', height: 300, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
                dataLabels: {
                    enabled: true,
                    formatter: function (value) { return value + ' orders'; },
                    style: { colors: ['#ffffff'], fontSize: '11px' }
                },
                colors: ['#6366f1'],
                xaxis: { categories: [], labels: { style: { fontSize: '10px' } } },
                grid: { xaxis: { lines: { show: true } } }
            });
            window.reportsCompanyChart.render();
        }

        const donutElement = document.querySelector('#status-donut-chart');
        if (donutElement && !window.reportsDonutChart) {
            window.reportsDonutChart = new ApexCharts(donutElement, {
                series: [0, 0, 0],
                chart: { type: 'donut', height: 320, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
                labels: ['In Progress', 'Picked Up', 'Delivered'],
                colors: ['#3b82f6', '#f59e0b', '#10b981'],
                dataLabels: {
                    enabled: true,
                    formatter: function (value) { return Math.round(value) + '%'; }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Volume',
                                    formatter: function () { return 0; }
                                }
                            }
                        }
                    }
                },
                legend: { position: 'bottom', fontFamily: 'Inter, sans-serif' }
            });
            window.reportsDonutChart.render();
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
                currentReportData = data;
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

    function downloadBlob(blob, filename) {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = url;
        link.download = filename || 'wings_report.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    }

    function resolveFilename(xhr) {
        const disposition = xhr.getResponseHeader('Content-Disposition') || '';
        const match = disposition.match(/filename="?([^"]+)"?/i);
        return match && match[1] ? match[1] : 'wings_management_report.csv';
    }

    window.exportReportsCsv = function exportReportsCsv() {
        if (!exportUrl()) {
            notify('Export URL is missing.', 'error');
            return;
        }

        const $button = $(selectors.exportCsv);
        const original = $button.html();
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Exporting...');

        $.ajax({
            url: exportUrl(),
            type: 'GET',
            data: $(selectors.form).serialize(),
            xhrFields: { responseType: 'blob' },
            success: function (blob, status, xhr) {
                downloadBlob(blob, resolveFilename(xhr));
            },
            error: function () {
                notify('Unable to export report.', 'error');
            },
            complete: function () {
                $button.prop('disabled', false).html(original);
            }
        });
    };

    function renderPrintRows(items, columns, emptyText) {
        const rows = Array.isArray(items) ? items : [];

        if (!rows.length) {
            return `<tr><td colspan="${columns.length}" class="text-center text-muted py-2">${escapeHtml(emptyText || 'No records found')}</td></tr>`;
        }

        return rows.map(item => '<tr>' + columns.map(column => `<td>${column.html ? column.value(item) : escapeHtml(column.value(item))}</td>`).join('') + '</tr>').join('');
    }

    function renderPrintableReport(data) {
        data = data || currentReportData || {};
        const kpis = data.kpis || {};
        const breakdowns = data.breakdowns || {};
        const assignments = normalizeRows(data.assignments);
        const isDriver = String(configValue('is-driver', '0')) === '1';

        $('#printable-report-area').html(
            '<div class="print-branding border-bottom pb-4 mb-4">' +
                '<div class="row align-items-center">' +
                    '<div class="col-6"><h1 class="print-logo fw-bold mb-1 text-primary">' + escapeHtml(configValue('application-name', 'Wings')) + '</h1><p class="text-uppercase text-muted fs-10 letter-spacing-1 mb-0">Logistics & Operations Management</p></div>' +
                    '<div class="col-6 text-end"><h3 class="fw-bold mb-1 text-dark">' + escapeHtml(configValue('report-title', 'MANAGEMENT REPORT')) + '</h3><p class="mb-0 text-muted fs-11">Date Generated: ' + escapeHtml(new Date().toLocaleString()) + '</p><p class="mb-0 text-muted fs-11">Officer: ' + escapeHtml(configValue('officer-name', 'N/A')) + '</p></div>' +
                '</div>' +
            '</div>' +
            '<h5 class="fw-bold text-dark mb-2 border-bottom pb-1">1. Executive Summaries</h5>' +
            '<table class="table table-bordered align-middle mb-4 print-kpi-table fs-11"><tbody>' +
                '<tr><td class="fw-bold">Total Luggage Assignments</td><td class="text-center fw-bold text-primary">' + escapeHtml(kpis.total_assignments || 0) + '</td><td>orders</td></tr>' +
                '<tr><td class="fw-bold">Delivered / Completed orders</td><td class="text-center fw-bold text-success">' + escapeHtml(kpis.delivered_count || 0) + '</td><td>orders</td></tr>' +
                '<tr><td class="fw-bold">Active In-Transit Logistics</td><td class="text-center fw-bold text-warning">' + escapeHtml(kpis.active_count || 0) + '</td><td>orders</td></tr>' +
                '<tr><td class="fw-bold">Total Logged Distance</td><td class="text-center fw-bold text-info">' + escapeHtml(kpis.total_distance || 0) + '</td><td>km</td></tr>' +
                '<tr><td class="fw-bold">Average Delivery Speed Duration</td><td class="text-center fw-bold text-purple">' + escapeHtml(kpis.avg_time_hours || 0) + '</td><td>hours</td></tr>' +
            '</tbody></table>' +
            (isDriver ? '' :
                '<h5 class="fw-bold text-dark mb-2 border-bottom pb-1">2. Team Performance Breakdowns</h5>' +
                '<h6 class="fw-bold text-muted mb-2 mt-3">Driver Logistics Efficiency</h6>' +
                '<table class="table table-sm table-bordered mb-4 fs-11"><thead><tr><th>Driver</th><th>Total</th><th>Completed</th><th>Rate</th><th>Distance</th></tr></thead><tbody>' +
                    renderPrintRows(breakdowns.driver_wise, [
                        { value: row => safe(row.name, 'Unassigned') },
                        { value: row => row.total || 0 },
                        { value: row => row.completed || 0 },
                        { value: row => `${row.rate || 0}%` },
                        { value: row => `${row.distance || 0} km` }
                    ], 'No driver data found') +
                '</tbody></table>'
            ) +
            '<div class="page-break-print"></div>' +
            '<h5 class="fw-bold text-dark mb-2 border-bottom pb-1">' + (isDriver ? '2. Detailed Auditable Log of Deliveries' : '3. Detailed Auditable Log of Assignments') + '</h5>' +
            '<table class="table table-bordered table-sm align-middle fs-9"><thead><tr><th>Ref ID</th><th>Created Date</th><th>Company</th><th>Pickup Station</th><th>Dropoff Location</th><th>Manager</th><th>Driver</th><th>Distance</th><th>Status</th></tr></thead><tbody>' +
                renderPrintRows(assignments, [
                    { value: row => safe(row.ref_id, '#') },
                    { value: row => safe(row.created_at, 'N/A') },
                    { value: row => safe(row.company_name, 'N/A') },
                    { value: row => safe(row.station_name, 'N/A') },
                    { value: row => safe(row.drop_location, 'N/A') },
                    { value: row => safe(row.manager_name, 'System/Admin') },
                    { value: row => safe(row.driver_name, 'Unassigned') },
                    { value: row => `${safe(row.distance_km, 0)} km` },
                    { value: row => safe(row.status, 'N/A') }
                ], 'No assignments matched filter criteria') +
            '</tbody></table>'
        );
    }

    window.printReports = function printReports() {
        if (!listUrl()) {
            window.print();
            return;
        }

        $.ajax({
            url: listUrl(),
            type: 'GET',
            data: `${$(selectors.form).serialize()}&per_page=all`,
            success: function (response) {
                if (response && response.success && response.data) {
                    renderPrintableReport(response.data);
                } else {
                    renderPrintableReport(currentReportData);
                }
                window.print();
            },
            error: function () {
                renderPrintableReport(currentReportData);
                window.print();
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

        $(document).on('click', selectors.exportCsv, function (event) {
            event.preventDefault();
            window.exportReportsCsv();
        });

        $(document).on('click', selectors.print, function (event) {
            event.preventDefault();
            window.printReports();
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
        initPlugins();
        initCharts();
        bindEvents();
        window.loadReports(1);
    });
})(jQuery);
