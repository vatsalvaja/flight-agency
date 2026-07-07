(function ($) {
    'use strict';

    var selectors = {
        config: '#stationsConfig',
        alert: '#stationAlert',
        tableBody: '#stationsTableBody',
        gridRow: '.dual-view-grid-container .row',
        form: '#stationForm',
        detailsModal: '#stationDetailsModal',
        detailsBody: '#stationDetailsBody',
        detailsEdit: '#stationDetailsEdit',
        searchForm: '#stationSearchForm',
        searchInput: '#stationSearchInput',
        searchClear: '#stationSearchClear'
    };

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? 'N/A' : value).html();
    }

    function displayValue(value, fallback) {
        if (value === null || value === undefined || value === '') {
            return fallback || 'N/A';
        }

        return value;
    }

    function showStationMessage(type, message) {
        var icon = type === 'success' ? 'feather-check-circle' : 'feather-alert-octagon';
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

        if ($(selectors.alert).length) {
            $(selectors.alert).html(
                '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
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

    function stationInitial(stationName) {
        var name = stationName || '';
        return escapeHtml(name.charAt(0).toUpperCase() || '?');
    }

    function statusBadge(status) {
        if (status === 'active') {
            return '<span class="badge bg-soft-success text-success px-2 py-1">Active</span>';
        }

        return '<span class="badge bg-soft-danger text-danger px-2 py-1">Inactive</span>';
    }

    function stationAvatar(station) {
        station = station || {};

        return '<div class="avatar-text avatar-sm bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; font-weight: 700;">' +
            stationInitial(station.station_name) +
        '</div>';
    }

    function emptyRow(message) {
        return '<tr>' +
            '<td colspan="7" class="text-center py-5 text-muted">' +
                '<i class="feather-alert-circle fs-3 d-block mb-2"></i>' +
                escapeHtml(message) +
            '</td>' +
        '</tr>';
    }

    window.loadStations = function () {
        var listUrl = $(selectors.config).data('list-url');

        if (!listUrl || !$(selectors.tableBody).length) {
            return;
        }

        $.ajax({
            url: listUrl,
            method: 'GET',
            data: {
                search: $(selectors.searchInput).val() || ''
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    renderStations(response.data);
                    return;
                }

                $(selectors.tableBody).html(emptyRow(response.message || 'Unable to load stations.'));
                renderStationsGrid([]);
            },
            error: function () {
                $(selectors.tableBody).html(emptyRow('Unable to load stations.'));
                renderStationsGrid([]);
            }
        });
    };

    function renderStations(stations) {
        var $body = $(selectors.tableBody);

        if (!$body.length) {
            return;
        }

        if (!stations || !stations.length) {
            $body.html(emptyRow($(selectors.config).data('empty-message')));
            renderStationsGrid(stations);
            return;
        }

        var rows = stations.map(function (station) {
            station = station || {};

            return '<tr data-station-id="' + escapeHtml(station.id) + '">' +
                '<td class="ps-4">' +
                    '<div class="d-flex align-items-center">' +
                        stationAvatar(station) +
                        '<div>' +
                            '<span class="fw-semibold text-dark d-block">' + escapeHtml(station.station_name) + '</span>' +
                            '<span class="fs-11 text-muted">' + escapeHtml(station.address) + '</span>' +
                        '</div>' +
                    '</div>' +
                '</td>' +
                '<td><code>' + escapeHtml(station.station_code) + '</code></td>' +
                '<td>' +
                    '<span class="d-block text-dark">' + escapeHtml(displayValue(station.city, '')) + ', ' + escapeHtml(displayValue(station.state, '')) + '</span>' +
                    '<span class="fs-11 text-muted">' + escapeHtml(station.country) + '</span>' +
                '</td>' +
                '<td>' + escapeHtml(station.contact_number) + '</td>' +
                '<td>' + escapeHtml(station.email) + '</td>' +
                '<td>' + statusBadge(station.status) + '</td>' +
                '<td class="text-end pe-4">' +
                    '<div class="d-inline-flex gap-2">' +
                        '<button type="button" class="btn btn-sm btn-light-brand js-view-station" data-id="' + escapeHtml(station.id) + '" data-url="' + escapeHtml(station.data_url) + '" title="View Details">' +
                            '<i class="feather-eye"></i>' +
                        '</button>' +
                        '<a href="' + escapeHtml(station.edit_url) + '" class="btn btn-sm btn-light-brand js-edit-station" data-id="' + escapeHtml(station.id) + '" title="Edit Station">' +
                            '<i class="feather-edit"></i>' +
                        '</a>' +
                        '<button type="button" class="btn btn-sm btn-light-danger js-delete-station" data-id="' + escapeHtml(station.id) + '" data-url="' + escapeHtml(station.delete_url) + '" title="Delete Station">' +
                            '<i class="feather-trash-2"></i>' +
                        '</button>' +
                    '</div>' +
                '</td>' +
            '</tr>';
        }).join('');

        $body.html(rows);
        renderStationsGrid(stations);
    }

    function renderStationsGrid(stations) {
        var $gridRow = $(selectors.gridRow);

        if (!$gridRow.length) {
            return;
        }

        if (!stations || !stations.length) {
            $gridRow.html(
                '<div class="col-12 text-center py-5 text-muted card shadow-none border">' +
                    '<i class="feather-alert-circle fs-3 d-block mb-2"></i>' +
                    escapeHtml($(selectors.config).data('empty-message')) +
                '</div>'
            );
            return;
        }

        var cards = stations.map(function (station) {
            station = station || {};

            return '<div class="col-12 col-md-6 col-lg-4 col-xl-4">' +
                '<div class="dual-view-card card-hover-effect">' +
                    '<div class="card-body">' +
                        '<div class="dual-view-card-header">' +
                            '<div class="dual-view-card-avatar">' + stationAvatar(station) + '</div>' +
                            '<div class="dual-view-card-title-area">' +
                                '<div class="dual-view-card-title" title="' + escapeHtml(station.station_name) + '">' + escapeHtml(station.station_name) + '</div>' +
                                '<div class="dual-view-card-subtitle" title="' + escapeHtml(station.station_code) + '">Code: ' + escapeHtml(station.station_code) + '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="dual-view-card-details">' +
                            gridDetail('Location', displayValue(station.city, '') + ', ' + displayValue(station.state, '')) +
                            gridDetail('Country', station.country) +
                            gridDetail('Contact', station.contact_number) +
                            gridDetail('Email', station.email) +
                            gridDetail('Status', statusBadge(station.status), true) +
                        '</div>' +
                    '</div>' +
                    '<div class="card-footer">' +
                        '<button type="button" class="btn btn-sm btn-light-brand js-view-station" data-id="' + escapeHtml(station.id) + '" data-url="' + escapeHtml(station.data_url) + '" title="View Details">' +
                            '<i class="feather-eye"></i>' +
                        '</button>' +
                        '<a href="' + escapeHtml(station.edit_url) + '" class="btn btn-sm btn-light-brand js-edit-station" data-id="' + escapeHtml(station.id) + '" title="Edit Station">' +
                            '<i class="feather-edit"></i>' +
                        '</a>' +
                        '<button type="button" class="btn btn-sm btn-light-danger js-delete-station" data-id="' + escapeHtml(station.id) + '" data-url="' + escapeHtml(station.delete_url) + '" title="Delete Station">' +
                            '<i class="feather-trash-2"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');

        $gridRow.html(cards);
    }

    function gridDetail(label, value, isHtml) {
        return '<div class="dual-view-card-detail-item">' +
            '<span class="dual-view-card-detail-label">' + escapeHtml(label) + '</span>' +
            '<span class="dual-view-card-detail-value">' + (isHtml ? value : escapeHtml(value)) + '</span>' +
        '</div>';
    }

    window.resetStationForm = function () {
        var $form = $(selectors.form);

        if (!$form.length) {
            return;
        }

        var stationId = $('#station_id').val();
        $form[0].reset();
        clearValidationErrors();
        $('#station_id').val(stationId || '');
    };

    window.editStation = function (id) {
        var $form = $(selectors.form);
        var dataUrl = $form.data('data-url') || (id ? '/admin/stations/' + id + '/data' : '');

        if (!dataUrl || !$form.length) {
            return;
        }

        $.ajax({
            url: dataUrl,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (!response.success) {
                    showStationMessage('error', response.message || 'Unable to load station.');
                    return;
                }

                populateStationForm(response.data || {});
            },
            error: function () {
                showStationMessage('error', 'Unable to load station.');
            }
        });
    };

    function populateStationForm(station) {
        station = station || {};

        $('#station_id').val(station.id || '');
        $('#station_name').val(station.station_name || '');
        $('#station_code').val(station.station_code || '');
        $('#contact_number').val(station.contact_number || '');
        $('#email').val(station.email || '');
        $('#status').val(station.status || 'active');
        $('#city').val(station.city || '');
        $('#state').val(station.state || '');
        $('#country').val(station.country || '');
        $('#address').val(station.address || '');
    }

    window.saveStation = function () {
        var $form = $(selectors.form);

        if (!$form.length) {
            return;
        }

        clearValidationErrors();

        var $submitButton = $form.find('button[type="submit"]');
        var originalButtonHtml = $submitButton.html();

        $submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (!response.success) {
                    showStationMessage('error', response.message || 'Unable to save station.');
                    return;
                }

                showStationMessage('success', response.message);
                redirectToStationIndex(response.message);
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};

                if (xhr.status === 422 && response.errors) {
                    displayValidationErrors(response.errors);
                    showStationMessage('error', response.message || 'Please check the form errors below.');
                    return;
                }

                showStationMessage('error', response.message || 'Unable to save station.');
            },
            complete: function () {
                $submitButton.prop('disabled', false).html(originalButtonHtml);
            }
        });
    };

    function redirectToStationIndex(message) {
        var indexUrl = $(selectors.form).data('index-url');

        if (!indexUrl) {
            loadStations();
            return;
        }

        try {
            window.sessionStorage.setItem('stationMessage', message || 'Station saved successfully.');
        } catch (error) {
            // Ignore storage issues and continue with the redirect.
        }

        window.location.href = indexUrl;
    }

    window.viewStation = function (id, dataUrl) {
        dataUrl = dataUrl || (id ? '/admin/stations/' + id + '/data' : '');

        if (!dataUrl || !$(selectors.detailsModal).length) {
            return;
        }

        $(selectors.detailsBody).html(
            '<div class="text-center py-5 text-muted">' +
                '<span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>' +
                'Loading station details...' +
            '</div>'
        );
        $(selectors.detailsEdit).attr('href', '#').addClass('disabled');
        openDetailsModal();

        $.ajax({
            url: dataUrl,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (!response.success) {
                    $(selectors.detailsBody).html('<div class="alert alert-danger mb-0">' + escapeHtml(response.message || 'Unable to load station details.') + '</div>');
                    return;
                }

                renderStationDetails(response.data || {});
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                $(selectors.detailsBody).html('<div class="alert alert-danger mb-0">' + escapeHtml(response.message || 'Unable to load station details.') + '</div>');
            }
        });
    };

    function openDetailsModal() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(document.querySelector(selectors.detailsModal)).show();
            return;
        }

        $(selectors.detailsModal).modal('show');
    }

    function renderStationDetails(station) {
        var emailHtml = station.email
            ? '<a href="mailto:' + escapeHtml(station.email) + '" class="text-primary fw-semibold"><i class="feather-mail me-1"></i>' + escapeHtml(station.email) + '</a>'
            : '<span class="text-muted">Not Specified</span>';

        var phoneHtml = station.contact_number
            ? '<i class="feather-phone me-1 text-muted"></i>' + escapeHtml(station.contact_number)
            : '<span class="text-muted">Not Specified</span>';

        $(selectors.detailsEdit).attr('href', station.edit_url || '#').toggleClass('disabled', !station.edit_url);

        $(selectors.detailsBody).html(
            '<div class="row">' +
                '<div class="col-md-4 col-sm-12 mb-4 mb-md-0">' +
                    '<div class="text-center d-flex flex-column align-items-center justify-content-center py-3">' +
                        '<div class="avatar-text avatar-xl bg-soft-primary text-primary rounded mb-4 d-flex align-items-center justify-content-center fs-1" style="width: 100px; height: 100px; background-color: rgba(59, 130, 246, 0.1);">' + stationInitial(station.station_name) + '</div>' +
                        '<h4 class="fw-bold mb-1 text-dark">' + escapeHtml(station.station_name) + '</h4>' +
                        '<span class="fs-12 text-muted mb-3">Code: <code>' + escapeHtml(station.station_code) + '</code></span>' +
                        statusBadge(station.status) +
                    '</div>' +
                '</div>' +
                '<div class="col-md-8 col-sm-12">' +
                    detailRow('City', escapeHtml(displayValue(station.city, 'N/A'))) +
                    detailRow('State / Region', escapeHtml(displayValue(station.state, 'N/A'))) +
                    detailRow('Country', escapeHtml(displayValue(station.country, 'N/A'))) +
                    detailRow('Contact Number', phoneHtml) +
                    detailRow('Email Address', emailHtml) +
                    detailRow('Address', '<span style="white-space: pre-line;">' + escapeHtml(displayValue(station.address, 'Not Specified')) + '</span>') +
                    detailRow('Created At', escapeHtml(displayValue(station.created_at, 'N/A'))) +
                    '<div class="row">' +
                        '<div class="col-sm-4 text-muted fw-medium">Last Updated</div>' +
                        '<div class="col-sm-8 text-dark">' + escapeHtml(displayValue(station.updated_at, 'N/A')) + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );
    }

    function detailRow(label, valueHtml) {
        return '<div class="row mb-3 pb-3 border-bottom">' +
            '<div class="col-sm-4 text-muted fw-medium">' + escapeHtml(label) + '</div>' +
            '<div class="col-sm-8 text-dark fw-semibold">' + valueHtml + '</div>' +
        '</div>';
    }

    window.deleteStation = function (id, deleteUrl) {
        if (!id && !deleteUrl) {
            return;
        }

        deleteUrl = deleteUrl || ('/admin/stations/' + id);

        function runDelete() {
            $.ajax({
                url: deleteUrl,
                method: 'POST',
                data: {
                    _method: 'DELETE'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showStationMessage('success', response.message);
                        loadStations();
                        return;
                    }

                    showStationMessage('error', response.message || 'Unable to delete station.');
                },
                error: function (xhr) {
                    var response = xhr.responseJSON || {};
                    showStationMessage('error', response.message || 'Unable to delete station.');
                }
            });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Station',
                text: 'Are you sure you want to delete this station?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger m-1',
                    cancelButton: 'btn btn-light m-1'
                },
                buttonsStyling: false
            }).then(function (result) {
                if (result.isConfirmed || result.value) {
                    runDelete();
                }
            });
            return;
        }

        if (window.confirm('Are you sure you want to delete this station?')) {
            runDelete();
        }
    };

    function clearValidationErrors() {
        var $form = $(selectors.form);

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.js-validation-error').remove();
    }

    function displayValidationErrors(errors) {
        $.each(errors, function (field, messages) {
            var normalizedField = field.replace(/\./g, '\\.');
            var $field = $('[name="' + normalizedField + '"]');
            var message = $.isArray(messages) ? messages[0] : messages;

            $field.addClass('is-invalid');
            $field.after('<div class="invalid-feedback js-validation-error">' + escapeHtml(message) + '</div>');
        });
    }

    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json'
            }
        });

        loadStations();

        try {
            var storedMessage = window.sessionStorage.getItem('stationMessage');
            if (storedMessage) {
                window.sessionStorage.removeItem('stationMessage');
                showStationMessage('success', storedMessage);
            }
        } catch (error) {
            // Ignore storage issues; the AJAX flow still works.
        }

        var $form = $(selectors.form);
        if ($form.length && $form.data('station-id')) {
            editStation($form.data('station-id'));
        }

        $(document).on('submit', selectors.form, function (event) {
            event.preventDefault();
            saveStation();
        });

        $(document).on('submit', selectors.searchForm, function (event) {
            event.preventDefault();
            $(selectors.searchClear).toggleClass('d-none', !$(selectors.searchInput).val());
            loadStations();
        });

        $(document).on('click', selectors.searchClear, function () {
            $(selectors.searchInput).val('');
            $(this).addClass('d-none');
            loadStations();
        });

        $(document).on('click', '.js-view-station', function () {
            viewStation($(this).data('id'), $(this).data('url'));
        });

        $(document).on('click', '.js-delete-station', function () {
            deleteStation($(this).data('id'), $(this).data('url'));
        });
    });
})(jQuery);
