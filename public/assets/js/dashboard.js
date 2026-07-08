(function ($) {
    'use strict';

    var selectors = {
        config: '#dashboardConfig',
        tableBody: '#dashboard-recent-assignments',
        trendChart: '#dashboard-ops-trend-chart',
        donutChart: '#dashboard-status-ratio-chart',
        driverAvgTime: '#dashboard-driver-avg-time'
    };

    var trendChart = null;
    var donutChart = null;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? 'N/A' : value).html();
    }

    function valueOr(value, fallback) {
        return value === null || value === undefined || value === '' ? (fallback || 'N/A') : value;
    }

    function isDriverDashboard() {
        return String($(selectors.config).data('is-driver')) === '1';
    }

    function columnCount() {
        return isDriverDashboard() ? 6 : 7;
    }

    function truncate(value, length) {
        value = valueOr(value, '');
        length = length || 25;

        return String(value).length > length ? String(value).substring(0, length - 3) + '...' : value;
    }

    function setText(id, value) {
        var $element = $('#' + id);

        if ($element.length) {
            $element.text(valueOr(value, '0'));
        }
    }

    function statusBadge(status) {
        if (status === 'Delivered') {
            return '<span class="badge bg-soft-success text-success px-2 py-0.5 fs-11">Delivered</span>';
        }

        if (status === 'Pickup') {
            return '<span class="badge bg-soft-warning text-warning px-2 py-0.5 fs-11">Pickup</span>';
        }

        return '<span class="badge bg-soft-primary text-primary px-2 py-0.5 fs-11">In Transit</span>';
    }

    function emptyRow(message, cssClass, iconClass) {
        return '<tr>' +
            '<td colspan="' + columnCount() + '" class="text-center ' + escapeHtml(cssClass || 'text-muted') + ' py-5">' +
                '<i class="' + escapeHtml(iconClass || 'feather-alert-triangle text-warning') + ' fs-2 mb-2 d-block"></i>' +
                escapeHtml(message || $(selectors.config).data('empty-message') || 'No allocated shipments found.') +
            '</td>' +
        '</tr>';
    }

    function renderCounts(counts) {
        counts = counts || {};

        setText('dashboard-companies-count', counts.companies);
        setText('dashboard-stations-count', counts.stations);
        setText('dashboard-users-count', counts.users);
        setText('dashboard-drivers-count', counts.drivers);
        setText('dashboard-managers-count', counts.managers);
        setText('dashboard-assignments-count', counts.assignments);
        setText('dashboard-pickup-count', counts.pickup);
        setText('dashboard-secondary-pickup-count', counts.pickup);
        setText('dashboard-in-progress-count', counts.in_progress);
        setText('dashboard-delivered-count', counts.delivered);
        setText('dashboard-secondary-delivered-count', counts.delivered);
        setText('dashboard-total-distance', counts.total_distance);
        setText('dashboard-secondary-total-distance', counts.total_distance);
        setText('dashboard-avg-time-hours', counts.avg_time_hours);
        setText('dashboard-secondary-avg-time-hours', counts.avg_time_hours);

        if ($(selectors.driverAvgTime).length) {
            $(selectors.driverAvgTime).toggleClass('d-none', Number(counts.delivered || 0) <= 0);
        }
    }

    function companyAvatar(row) {
        row = row || {};
        var company = row.company || {};

        if (company.logo) {
            return '<img src="' + escapeHtml(company.logo) + '" alt="logo" class="rounded" style="height: 24px; width: 24px; object-fit: cover;">';
        }

        return '<div class="avatar-text avatar-xs bg-soft-secondary text-secondary rounded d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 10px;">' +
            escapeHtml(company.initial || 'C') +
        '</div>';
    }

    function renderRecentAssignments(assignments) {
        var $body = $(selectors.tableBody);

        if (!$body.length) {
            return;
        }

        assignments = $.isArray(assignments) ? assignments : [];

        if (!assignments.length) {
            $body.html(emptyRow($(selectors.config).data('empty-message')));
            return;
        }

        var rows = $.map(assignments, function (row) {
            row = row || {};

            var company = row.company || {};
            var station = row.station || {};
            var driver = row.driver || {};
            var driverCell = isDriverDashboard() ? '' : '<td><span class="text-dark fs-12 fw-medium">' + escapeHtml(driver.name || 'Unassigned') + '</span></td>';
            var actionIcon = isDriverDashboard() ? 'feather-edit' : 'feather-eye';
            var actionTitle = isDriverDashboard() ? 'Manage Order' : 'View Booking';

            return '<tr>' +
                '<td class="ps-4 fw-semibold text-dark">#' + escapeHtml(row.id) + '</td>' +
                '<td>' +
                    '<div class="d-flex align-items-center gap-2">' +
                        companyAvatar(row) +
                        '<span class="fw-semibold text-dark fs-12">' + escapeHtml(company.name || 'N/A') + '</span>' +
                    '</div>' +
                '</td>' +
                '<td>' +
                    '<div class="d-flex flex-column">' +
                        '<span class="fs-12 text-dark"><i class="feather-map-pin text-primary me-1 fs-10"></i>' + escapeHtml(station.name || 'N/A') + '</span>' +
                        '<span class="fs-11 text-muted" title="' + escapeHtml(row.drop_location || '') + '"><i class="feather-arrow-right text-muted me-1 fs-10"></i>' + escapeHtml(truncate(row.drop_location, 25)) + '</span>' +
                    '</div>' +
                '</td>' +
                driverCell +
                '<td>' + escapeHtml(valueOr(row.distance_km, '0')) + ' km</td>' +
                '<td>' + statusBadge(row.status) + '</td>' +
                '<td class="pe-4 text-end">' +
                    '<a href="' + escapeHtml(row.action_url || '#') + '" class="btn btn-xs btn-light-brand" title="' + escapeHtml(actionTitle) + '">' +
                        '<i class="' + escapeHtml(actionIcon) + '"></i>' +
                    '</a>' +
                '</td>' +
            '</tr>';
        }).join('');

        $body.html(rows);
    }

    function renderTrendChart(trendData) {
        var $chart = $(selectors.trendChart);

        if (!$chart.length || typeof ApexCharts === 'undefined') {
            return;
        }

        trendData = $.isArray(trendData) ? trendData : [];

        if (trendChart) {
            trendChart.destroy();
        }

        trendChart = new ApexCharts($chart[0], {
            series: [{
                name: 'Picked Up',
                data: $.map(trendData, function (item) { return Number((item || {}).pickup || 0); })
            }, {
                name: 'In Transit',
                data: $.map(trendData, function (item) { return Number((item || {}).in_progress || 0); })
            }, {
                name: 'Delivered',
                data: $.map(trendData, function (item) { return Number((item || {}).delivered || 0); })
            }],
            chart: {
                type: 'area',
                height: 330,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            colors: ['#f59e0b', '#3b82f6', '#10b981'],
            xaxis: {
                categories: $.map(trendData, function (item) { return (item || {}).date || ''; }),
                labels: { style: { fontSize: '10px' } }
            },
            yaxis: {
                title: { text: 'Luggage Volume' },
                tickAmount: 5,
                labels: {
                    formatter: function (value) {
                        return Math.round(value);
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            legend: {
                position: 'bottom',
                fontFamily: 'Inter, sans-serif'
            },
            grid: {
                borderColor: '#e2e8f0',
                strokeDashArray: 4
            }
        });

        trendChart.render();
    }

    function renderDonutChart(counts) {
        var $chart = $(selectors.donutChart);

        if (!$chart.length || typeof ApexCharts === 'undefined') {
            return;
        }

        counts = counts || {};

        if (donutChart) {
            donutChart.destroy();
        }

        donutChart = new ApexCharts($chart[0], {
            series: [
                Number(counts.in_progress || 0),
                Number(counts.pickup || 0),
                Number(counts.delivered || 0)
            ],
            chart: {
                type: 'donut',
                height: 330,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false }
            },
            labels: ['In Transit', 'Picked Up', 'Delivered'],
            colors: ['#3b82f6', '#f59e0b', '#10b981'],
            dataLabels: {
                enabled: true,
                formatter: function (value) {
                    return Math.round(value) + '%';
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Jobs',
                                formatter: function () {
                                    return Number(counts.assignments || 0);
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                fontFamily: 'Inter, sans-serif'
            }
        });

        donutChart.render();
    }

    function renderDashboard(data) {
        data = data || {};

        renderCounts(data.counts || {});
        renderRecentAssignments(data.recent_assignments || []);
        renderTrendChart(data.daily_trend || []);
        renderDonutChart(data.counts || {});
    }

    function showDashboardError(message) {
        if ($(selectors.tableBody).length) {
            $(selectors.tableBody).html(emptyRow(message || 'Unable to load dashboard data.', 'text-danger', 'feather-alert-octagon'));
        }
    }

    window.loadDashboard = function () {
        var dataUrl = $(selectors.config).data('url');

        if (!dataUrl || !$(selectors.config).length) {
            return;
        }

        $.ajax({
            url: dataUrl,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response && response.success) {
                    renderDashboard(response.data || {});
                    return;
                }

                showDashboardError((response && response.message) || 'Unable to load dashboard data.');
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                showDashboardError(response.message || 'Unable to load dashboard data.');
            }
        });
    };

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

        loadDashboard();
    });
})(jQuery);
