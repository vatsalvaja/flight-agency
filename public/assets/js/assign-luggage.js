(function ($) {
    'use strict';

    var selectors = {
        config: '#assignLuggageConfig',
        alert: '#assignLuggageAlert',
        tableBody: '#assignLuggageTableBody',
        gridRow: '.dual-view-grid-container .row',
        form: '#luggage-assign-form',
        searchForm: '#assignLuggageSearchForm',
        searchInput: '#assignLuggageSearchInput',
        searchClear: '#assignLuggageSearchClear',
        detailsModal: '#assignLuggageDetailsModal',
        detailsBody: '#assignLuggageDetailsBody',
        detailsEdit: '#assignLuggageDetailsEdit',
        detailsFull: '#assignLuggageDetailsFull'
    };

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? 'N/A' : value).html();
    }

    function valueOr(value, fallback) {
        return value === null || value === undefined || value === '' ? (fallback || 'N/A') : value;
    }

    function truncate(value, length) {
        value = valueOr(value, '');
        return String(value).length > length ? String(value).substring(0, length - 3) + '...' : value;
    }

    function isAdmin() {
        return String($(selectors.config).data('is-admin')) === '1';
    }

    function columnCount() {
        return isAdmin() ? 11 : 10;
    }

    function showAssignLuggageMessage(type, message) {
        var icon = type === 'success' ? 'feather-check-circle' : 'feather-alert-octagon';
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

        if ($(selectors.alert).length) {
            $(selectors.alert).html(
                '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                    '<i class="' + icon + ' me-2"></i>' + escapeHtml(message || 'Something went wrong.') +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>'
            );
        }

        if (typeof Swal !== 'undefined') {
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true })
                .fire({ icon: type, title: message || 'Done' });
        }
    }

    function statusBadge(status) {
        if (status === 'Pickup') {
            return '<span class="badge bg-soft-warning text-warning px-2 py-1">Pickup</span>';
        }
        if (status === 'Delivered') {
            return '<span class="badge bg-soft-success text-success px-2 py-1">Delivered</span>';
        }
        return '<span class="badge bg-soft-info text-info px-2 py-1">In Progress</span>';
    }

    function companyAvatar(assignment) {
        assignment = assignment || {};
        if (assignment.company_logo_url) {
            return '<img src="' + escapeHtml(assignment.company_logo_url) + '" alt="logo" class="rounded me-2" style="height: 28px; width: 28px; object-fit: cover;">';
        }
        return '<div class="avatar-text avatar-sm bg-soft-primary text-primary rounded me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-weight: 700; font-size: 11px;">' +
            escapeHtml(String(valueOr(assignment.company_name, '?')).charAt(0).toUpperCase()) +
        '</div>';
    }

    function driverAvatar(assignment) {
        assignment = assignment || {};
        if (assignment.driver_photo_url) {
            return '<img src="' + escapeHtml(assignment.driver_photo_url) + '" alt="avatar" class="rounded-circle me-2" style="height: 28px; width: 28px; object-fit: cover;">';
        }
        return '<div class="avatar-text avatar-sm bg-soft-info text-info rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-weight: 700; font-size: 11px;">' +
            escapeHtml(assignment.driver_initials || 'NA') +
        '</div>';
    }

    function emptyRow(message) {
        return '<tr><td colspan="' + columnCount() + '" class="text-center py-5 text-muted">' +
            '<i class="feather-alert-circle fs-3 d-block mb-2"></i>' + escapeHtml(message) +
        '</td></tr>';
    }

    window.loadAssignLuggage = function () {
        var listUrl = $(selectors.config).data('list-url');
        if (!listUrl || !$(selectors.tableBody).length) return;

        $.ajax({
            url: listUrl,
            method: 'GET',
            data: { search: $(selectors.searchInput).val() || '' },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    renderAssignLuggage(response.data || []);
                    return;
                }
                $(selectors.tableBody).html(emptyRow(response.message || 'Unable to load luggage assignments.'));
                renderAssignLuggageGrid([]);
            },
            error: function () {
                $(selectors.tableBody).html(emptyRow('Unable to load luggage assignments.'));
                renderAssignLuggageGrid([]);
            }
        });
    };

    function renderAssignLuggage(assignments) {
        if (!assignments.length) {
            $(selectors.tableBody).html(emptyRow($(selectors.config).data('empty-message')));
            renderAssignLuggageGrid([]);
            return;
        }

        var rows = assignments.map(function (assignment) {
            assignment = assignment || {};
            var assignedByCell = isAdmin()
                ? '<td><span class="badge bg-soft-info text-info px-2 py-1 fw-semibold">' + escapeHtml(assignment.creator_name || 'System') + '</span></td>'
                : '';

            return '<tr data-assignment-id="' + escapeHtml(assignment.id) + '">' +
                '<td class="ps-4"><div class="d-flex align-items-center">' + companyAvatar(assignment) + '<span class="fw-semibold text-dark">' + escapeHtml(assignment.company_name) + '</span></div></td>' +
                '<td>' + escapeHtml(assignment.station_name) + '</td>' +
                '<td><div class="d-flex align-items-center">' + driverAvatar(assignment) + '<span>' + escapeHtml(assignment.driver_name) + '</span></div></td>' +
                '<td title="' + escapeHtml(assignment.pickup_location) + '">' + escapeHtml(truncate(assignment.pickup_location, 25)) + '</td>' +
                '<td title="' + escapeHtml(assignment.drop_location) + '">' + escapeHtml(truncate(assignment.drop_location, 25)) + '</td>' +
                '<td><code>' + escapeHtml(valueOr(assignment.distance_km, '0.00')) + ' km</code></td>' +
                '<td>' + escapeHtml(assignment.expected_delivery_display) + '</td>' +
                '<td>' + statusBadge(assignment.status) + '</td>' +
                '<td>' + escapeHtml(assignment.created_at) + '</td>' +
                assignedByCell +
                '<td class="text-end pe-4"><div class="d-inline-flex gap-2">' +
                    '<button type="button" class="btn btn-sm btn-light-brand js-view-assign-luggage" data-id="' + escapeHtml(assignment.id) + '" data-url="' + escapeHtml(assignment.data_url) + '" title="View Details"><i class="feather-eye"></i></button>' +
                    '<a href="' + escapeHtml(assignment.edit_url) + '" class="btn btn-sm btn-light-brand" title="Edit Assignment"><i class="feather-edit"></i></a>' +
                    '<button type="button" class="btn btn-sm btn-light-danger js-delete-assign-luggage" data-id="' + escapeHtml(assignment.id) + '" data-url="' + escapeHtml(assignment.delete_url) + '" title="Delete Assignment"><i class="feather-trash-2"></i></button>' +
                '</div></td>' +
            '</tr>';
        }).join('');

        $(selectors.tableBody).html(rows);
        renderAssignLuggageGrid(assignments);
    }

    function renderAssignLuggageGrid(assignments) {
        var $gridRow = $(selectors.gridRow);
        if (!$gridRow.length) return;

        if (!assignments.length) {
            $gridRow.html('<div class="col-12 text-center py-5 text-muted card shadow-none border">' + escapeHtml($(selectors.config).data('empty-message')) + '</div>');
            return;
        }

        $gridRow.html(assignments.map(function (assignment) {
            assignment = assignment || {};
            return '<div class="col-12 col-md-6 col-lg-4 col-xl-4">' +
                '<div class="dual-view-card card-hover-effect">' +
                    '<div class="card-body">' +
                        '<div class="dual-view-card-header">' +
                            '<div class="dual-view-card-avatar">' + companyAvatar(assignment) + '</div>' +
                            '<div class="dual-view-card-title-area"><div class="dual-view-card-title">' + escapeHtml(assignment.company_name) + '</div><div class="dual-view-card-subtitle">' + escapeHtml(assignment.pickup_location) + '</div></div>' +
                        '</div>' +
                        '<div class="dual-view-card-details">' +
                            gridDetail('Station', assignment.station_name) +
                            gridDetail('Driver', assignment.driver_name) +
                            gridDetail('Distance', valueOr(assignment.distance_km, '0.00') + ' km') +
                            gridDetail('Status', statusBadge(assignment.status), true) +
                        '</div>' +
                    '</div>' +
                    '<div class="card-footer">' +
                        '<button type="button" class="btn btn-sm btn-light-brand js-view-assign-luggage" data-id="' + escapeHtml(assignment.id) + '" data-url="' + escapeHtml(assignment.data_url) + '" title="View Details"><i class="feather-eye"></i></button>' +
                        '<a href="' + escapeHtml(assignment.edit_url) + '" class="btn btn-sm btn-light-brand" title="Edit Assignment"><i class="feather-edit"></i></a>' +
                        '<button type="button" class="btn btn-sm btn-light-danger js-delete-assign-luggage" data-id="' + escapeHtml(assignment.id) + '" data-url="' + escapeHtml(assignment.delete_url) + '" title="Delete Assignment"><i class="feather-trash-2"></i></button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join(''));
    }

    function gridDetail(label, value, html) {
        return '<div class="dual-view-card-detail-item"><span class="dual-view-card-detail-label">' + escapeHtml(label) + '</span><span class="dual-view-card-detail-value">' + (html ? value : escapeHtml(value)) + '</span></div>';
    }

    window.saveAssignLuggage = function () {
        var $form = $(selectors.form);
        if (!$form.length) return;

        clearValidationErrors();
        var $button = $form.find('button[type="submit"]');
        var original = $button.html();
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (!response.success) {
                    showAssignLuggageMessage('error', response.message || 'Unable to save luggage assignment.');
                    return;
                }
                redirectToIndex(response.message);
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                if (xhr.status === 422 && response.errors) {
                    displayValidationErrors(response.errors);
                    showAssignLuggageMessage('error', response.message || 'Please check the form errors below.');
                    return;
                }
                showAssignLuggageMessage('error', response.message || 'Unable to save luggage assignment.');
            },
            complete: function () {
                $button.prop('disabled', false).html(original);
            }
        });
    };

    function redirectToIndex(message) {
        var indexUrl = $(selectors.form).data('index-url');
        if (!indexUrl) {
            loadAssignLuggage();
            return;
        }
        try { window.sessionStorage.setItem('assignLuggageMessage', message || 'Luggage assignment saved successfully.'); } catch (error) {}
        window.location.href = indexUrl;
    }

    window.viewAssignLuggage = function (id, dataUrl) {
        dataUrl = dataUrl || (id ? '/admin/assign-luggage/' + id + '/data' : '');
        if (!dataUrl || !$(selectors.detailsModal).length) return;

        $(selectors.detailsBody).html('<div class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading assignment details...</div>');
        $(selectors.detailsEdit).attr('href', '#').addClass('disabled');
        $(selectors.detailsFull).attr('href', '#').addClass('disabled');
        openModal();

        $.getJSON(dataUrl).done(function (response) {
            if (!response.success) {
                $(selectors.detailsBody).html('<div class="alert alert-danger mb-0">' + escapeHtml(response.message || 'Unable to load assignment details.') + '</div>');
                return;
            }
            renderDetails(response.data || {});
        }).fail(function (xhr) {
            var response = xhr.responseJSON || {};
            $(selectors.detailsBody).html('<div class="alert alert-danger mb-0">' + escapeHtml(response.message || 'Unable to load assignment details.') + '</div>');
        });
    };

    function renderDetails(assignment) {
        $(selectors.detailsEdit).attr('href', assignment.edit_url || '#').toggleClass('disabled', !assignment.edit_url);
        $(selectors.detailsFull).attr('href', assignment.show_url || '#').toggleClass('disabled', !assignment.show_url);

        var imageHtml = '';
        if (assignment.images && assignment.images.length) {
            imageHtml = '<div class="row g-2">' + assignment.images.map(function (image) {
                return '<div class="col-3 col-md-2"><a href="' + escapeHtml(image.url) + '" target="_blank" class="d-block border rounded p-1" style="height:80px;overflow:hidden;background:#fff;"><img src="' + escapeHtml(image.url) + '" class="w-100 h-100 rounded" style="object-fit:cover;"></a></div>';
            }).join('') + '</div>';
        } else {
            imageHtml = '<div class="text-muted">No images uploaded.</div>';
        }

        $(selectors.detailsBody).html(
            '<div class="row g-4">' +
                '<div class="col-lg-8">' +
                    '<div class="table-responsive"><table class="table table-bordered align-middle mb-0"><tbody>' +
                        detailRow('Flight Company', escapeHtml(assignment.company_name) + ' (' + escapeHtml(valueOr(assignment.company_code, 'N/A')) + ')') +
                        detailRow('Station', escapeHtml(assignment.station_name) + ' (' + escapeHtml(valueOr(assignment.station_code, 'N/A')) + ')') +
                        detailRow('Driver', escapeHtml(assignment.driver_name)) +
                        detailRow('Pickup Location', escapeHtml(assignment.pickup_location)) +
                        detailRow('Drop Location', escapeHtml(assignment.drop_location)) +
                        detailRow('Distance', '<code>' + escapeHtml(valueOr(assignment.distance_km, '0.00')) + ' km</code>') +
                        detailRow('Expected Delivery', escapeHtml(assignment.expected_delivery_display)) +
                        detailRow('Status', statusBadge(assignment.status)) +
                        detailRow('Assigned By', escapeHtml(assignment.creator_name || 'System') + ' on ' + escapeHtml(assignment.created_at_full)) +
                    '</tbody></table></div>' +
                '</div>' +
                '<div class="col-lg-4">' +
                    '<div class="mb-4"><h6 class="fw-bold text-dark mb-2">Notes</h6><div class="bg-light border rounded p-3" style="white-space:pre-wrap;">' + escapeHtml(valueOr(assignment.notes, 'No custom notes provided.')) + '</div></div>' +
                    '<div><h6 class="fw-bold text-dark mb-2">Uploaded Images</h6>' + imageHtml + '</div>' +
                '</div>' +
            '</div>'
        );
    }

    function detailRow(label, valueHtml) {
        return '<tr><td class="fw-semibold text-muted py-3" style="width:30%;">' + escapeHtml(label) + '</td><td class="py-3">' + valueHtml + '</td></tr>';
    }

    function openModal() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(document.querySelector(selectors.detailsModal)).show();
            return;
        }
        $(selectors.detailsModal).modal('show');
    }

    window.deleteAssignLuggage = function (id, deleteUrl) {
        if (!id && !deleteUrl) return;
        deleteUrl = deleteUrl || ('/admin/assign-luggage/' + id);

        function runDelete() {
            $.ajax({
                url: deleteUrl,
                method: 'POST',
                data: { _method: 'DELETE' },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showAssignLuggageMessage('success', response.message);
                        loadAssignLuggage();
                        return;
                    }
                    showAssignLuggageMessage('error', response.message || 'Unable to delete luggage assignment.');
                },
                error: function (xhr) {
                    var response = xhr.responseJSON || {};
                    showAssignLuggageMessage('error', response.message || 'Unable to delete luggage assignment.');
                }
            });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Assignment',
                text: 'Are you sure you want to delete this luggage assignment?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-danger m-1', cancelButton: 'btn btn-light m-1' },
                buttonsStyling: false
            }).then(function (result) {
                if (result.isConfirmed || result.value) runDelete();
            });
            return;
        }

        if (window.confirm('Are you sure you want to delete this luggage assignment?')) runDelete();
    };

    function clearValidationErrors() {
        var $form = $(selectors.form);
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.js-validation-error').remove();
    }

    function displayValidationErrors(errors) {
        $.each(errors, function (field, messages) {
            var fieldName = field.replace(/\.\d+$/, '[]').replace(/\./g, '\\.');
            var $field = $('[name="' + fieldName + '"]');
            if (!$field.length && field.indexOf('images.') === 0) {
                $field = $('[name="images[]"]');
            }
            var message = $.isArray(messages) ? messages[0] : messages;
            $field.addClass('is-invalid');
            $field.after('<div class="invalid-feedback js-validation-error d-block">' + escapeHtml(message) + '</div>');
        });
    }

    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });

        loadAssignLuggage();

        try {
            var storedMessage = window.sessionStorage.getItem('assignLuggageMessage');
            if (storedMessage) {
                window.sessionStorage.removeItem('assignLuggageMessage');
                showAssignLuggageMessage('success', storedMessage);
            }
        } catch (error) {}

        $(document).on('submit', selectors.form, function (event) {
            event.preventDefault();
            saveAssignLuggage();
        });

        $(document).on('submit', selectors.searchForm, function (event) {
            event.preventDefault();
            $(selectors.searchClear).toggleClass('d-none', !$(selectors.searchInput).val());
            loadAssignLuggage();
        });

        $(document).on('click', selectors.searchClear, function () {
            $(selectors.searchInput).val('');
            $(this).addClass('d-none');
            loadAssignLuggage();
        });

        $(document).on('click', '.js-view-assign-luggage', function () {
            viewAssignLuggage($(this).data('id'), $(this).data('url'));
        });

        $(document).on('click', '.js-delete-assign-luggage', function () {
            deleteAssignLuggage($(this).data('id'), $(this).data('url'));
        });
    });
})(jQuery);
