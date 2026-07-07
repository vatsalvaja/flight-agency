(function ($) {
    'use strict';

    var selectors = {
        config: '#rolesConfig',
        alert: '#roleAlert',
        tableBody: '#rolesTableBody',
        gridRow: '.dual-view-grid-container .row',
        form: '#roleForm',
        detailsModal: '#roleDetailsModal',
        detailsBody: '#roleDetailsBody',
        detailsEdit: '#roleDetailsEdit'
    };

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? 'N/A' : value).html();
    }

    function showRoleMessage(type, message) {
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
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true })
                .fire({ icon: type, title: message || 'Done' });
        }
    }

    function statusBadge(status) {
        return String(status) === '0'
            ? '<span class="badge bg-soft-success text-success px-2 py-1">Active</span>'
            : '<span class="badge bg-soft-danger text-danger px-2 py-1">Inactive</span>';
    }

    function emptyRow(message) {
        return '<tr><td colspan="5" class="text-center py-5 text-muted">' +
            '<i class="feather-alert-circle fs-3 d-block mb-2"></i>' + escapeHtml(message) +
        '</td></tr>';
    }

    window.loadRoles = function () {
        var listUrl = $(selectors.config).data('list-url');

        if (!listUrl || !$(selectors.tableBody).length) {
            return;
        }

        $.ajax({
            url: listUrl,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    renderRoles(response.data || []);
                    return;
                }

                $(selectors.tableBody).html(emptyRow(response.message || 'Unable to load roles.'));
                renderRolesGrid([]);
            },
            error: function () {
                $(selectors.tableBody).html(emptyRow('Unable to load roles.'));
                renderRolesGrid([]);
            }
        });
    };

    function renderRoles(roles) {
        if (!roles.length) {
            $(selectors.tableBody).html(emptyRow($(selectors.config).data('empty-message')));
            renderRolesGrid([]);
            return;
        }

        var rows = roles.map(function (role) {
            role = role || {};

            return '<tr data-role-id="' + escapeHtml(role.id) + '">' +
                '<td class="ps-4"><code>#' + escapeHtml(role.id) + '</code></td>' +
                '<td class="fw-semibold text-dark">' + escapeHtml(role.role_name) + '</td>' +
                '<td>' + statusBadge(role.status) + '</td>' +
                '<td>' + escapeHtml(role.created_at) + '</td>' +
                '<td class="text-end pe-4">' +
                    '<div class="d-inline-flex gap-2">' +
                        '<button type="button" class="btn btn-sm btn-light-brand js-view-role" data-id="' + escapeHtml(role.id) + '" data-url="' + escapeHtml(role.data_url) + '" title="View Details"><i class="feather-eye"></i></button>' +
                        '<a href="' + escapeHtml(role.edit_url) + '" class="btn btn-sm btn-light-brand" title="Edit Role"><i class="feather-edit"></i></a>' +
                        '<button type="button" class="btn btn-sm btn-light-danger js-delete-role" data-id="' + escapeHtml(role.id) + '" data-url="' + escapeHtml(role.delete_url) + '" title="Delete Role"><i class="feather-trash-2"></i></button>' +
                    '</div>' +
                '</td>' +
            '</tr>';
        }).join('');

        $(selectors.tableBody).html(rows);
        renderRolesGrid(roles);
    }

    function renderRolesGrid(roles) {
        var $gridRow = $(selectors.gridRow);
        if (!$gridRow.length) return;

        if (!roles.length) {
            $gridRow.html('<div class="col-12 text-center py-5 text-muted card shadow-none border">' + escapeHtml($(selectors.config).data('empty-message')) + '</div>');
            return;
        }

        $gridRow.html(roles.map(function (role) {
            role = role || {};
            return '<div class="col-12 col-md-6 col-lg-4 col-xl-4">' +
                '<div class="dual-view-card card-hover-effect">' +
                    '<div class="card-body">' +
                        '<div class="dual-view-card-header">' +
                            '<div class="dual-view-card-avatar"><div class="avatar-text bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;font-weight:700;">' + escapeHtml((role.role_name || '?').charAt(0).toUpperCase()) + '</div></div>' +
                            '<div class="dual-view-card-title-area"><div class="dual-view-card-title">' + escapeHtml(role.role_name) + '</div><div class="dual-view-card-subtitle">#' + escapeHtml(role.id) + '</div></div>' +
                        '</div>' +
                        '<div class="dual-view-card-details">' +
                            gridDetail('Status', statusBadge(role.status), true) +
                            gridDetail('Users', role.users_count) +
                            gridDetail('Created', role.created_at) +
                        '</div>' +
                    '</div>' +
                    '<div class="card-footer">' +
                        '<button type="button" class="btn btn-sm btn-light-brand js-view-role" data-id="' + escapeHtml(role.id) + '" data-url="' + escapeHtml(role.data_url) + '" title="View Details"><i class="feather-eye"></i></button>' +
                        '<a href="' + escapeHtml(role.edit_url) + '" class="btn btn-sm btn-light-brand" title="Edit Role"><i class="feather-edit"></i></a>' +
                        '<button type="button" class="btn btn-sm btn-light-danger js-delete-role" data-id="' + escapeHtml(role.id) + '" data-url="' + escapeHtml(role.delete_url) + '" title="Delete Role"><i class="feather-trash-2"></i></button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join(''));
    }

    function gridDetail(label, value, html) {
        return '<div class="dual-view-card-detail-item"><span class="dual-view-card-detail-label">' + escapeHtml(label) + '</span><span class="dual-view-card-detail-value">' + (html ? value : escapeHtml(value)) + '</span></div>';
    }

    window.editRole = function (id) {
        var $form = $(selectors.form);
        var dataUrl = $form.data('data-url') || (id ? '/admin/roles/' + id + '/data' : '');
        if (!dataUrl || !$form.length) return;

        $.getJSON(dataUrl).done(function (response) {
            if (response.success) {
                $('#role_id').val(response.data.id || '');
                $('#role_name').val(response.data.role_name || '');
                $('#status').val(String(response.data.status || '0'));
            } else {
                showRoleMessage('error', response.message || 'Unable to load role.');
            }
        }).fail(function () {
            showRoleMessage('error', 'Unable to load role.');
        });
    };

    window.saveRole = function () {
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
                    showRoleMessage('error', response.message || 'Unable to save role.');
                    return;
                }
                redirectToIndex(response.message);
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                if (xhr.status === 422 && response.errors) {
                    displayValidationErrors(response.errors);
                    showRoleMessage('error', response.message || 'Please check the form errors below.');
                    return;
                }
                showRoleMessage('error', response.message || 'Unable to save role.');
            },
            complete: function () {
                $button.prop('disabled', false).html(original);
            }
        });
    };

    function redirectToIndex(message) {
        var indexUrl = $(selectors.form).data('index-url');
        if (!indexUrl) {
            loadRoles();
            return;
        }
        try { window.sessionStorage.setItem('roleMessage', message || 'Role saved successfully.'); } catch (error) {}
        window.location.href = indexUrl;
    }

    window.viewRole = function (id, dataUrl) {
        dataUrl = dataUrl || (id ? '/admin/roles/' + id + '/data' : '');
        if (!dataUrl || !$(selectors.detailsModal).length) return;

        $(selectors.detailsBody).html('<div class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading role details...</div>');
        $(selectors.detailsEdit).attr('href', '#').addClass('disabled');
        openModal();

        $.getJSON(dataUrl).done(function (response) {
            if (!response.success) {
                $(selectors.detailsBody).html('<div class="alert alert-danger mb-0">' + escapeHtml(response.message || 'Unable to load role details.') + '</div>');
                return;
            }
            var role = response.data || {};
            $(selectors.detailsEdit).attr('href', role.edit_url || '#').toggleClass('disabled', !role.edit_url);
            $(selectors.detailsBody).html(
                detailRow('Role ID', '#' + escapeHtml(role.id)) +
                detailRow('Role Name', escapeHtml(role.role_name)) +
                detailRow('Status', statusBadge(role.status)) +
                detailRow('Assigned Users', escapeHtml(role.users_count)) +
                detailRow('Created At', escapeHtml(role.created_at)) +
                detailRow('Last Updated', escapeHtml(role.updated_at))
            );
        }).fail(function () {
            $(selectors.detailsBody).html('<div class="alert alert-danger mb-0">Unable to load role details.</div>');
        });
    };

    function detailRow(label, value) {
        return '<div class="row mb-3 pb-3 border-bottom"><div class="col-sm-4 text-muted fw-medium">' + escapeHtml(label) + '</div><div class="col-sm-8 text-dark fw-semibold">' + value + '</div></div>';
    }

    function openModal() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(document.querySelector(selectors.detailsModal)).show();
            return;
        }
        $(selectors.detailsModal).modal('show');
    }

    window.deleteRole = function (id, deleteUrl) {
        if (!id && !deleteUrl) return;
        deleteUrl = deleteUrl || ('/admin/roles/' + id);

        function runDelete() {
            $.ajax({
                url: deleteUrl,
                method: 'POST',
                data: { _method: 'DELETE' },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showRoleMessage('success', response.message);
                        loadRoles();
                        return;
                    }
                    showRoleMessage('error', response.message || 'Unable to delete role.');
                },
                error: function (xhr) {
                    var response = xhr.responseJSON || {};
                    showRoleMessage('error', response.message || 'Unable to delete role.');
                }
            });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Role',
                text: 'Are you sure you want to delete this role?',
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

        if (window.confirm('Are you sure you want to delete this role?')) runDelete();
    };

    function clearValidationErrors() {
        var $form = $(selectors.form);
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.js-validation-error').remove();
    }

    function displayValidationErrors(errors) {
        $.each(errors, function (field, messages) {
            var $field = $('[name="' + field.replace(/\./g, '\\.') + '"]');
            var message = $.isArray(messages) ? messages[0] : messages;
            $field.addClass('is-invalid').after('<div class="invalid-feedback js-validation-error">' + escapeHtml(message) + '</div>');
        });
    }

    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });
        loadRoles();

        try {
            var storedMessage = window.sessionStorage.getItem('roleMessage');
            if (storedMessage) {
                window.sessionStorage.removeItem('roleMessage');
                showRoleMessage('success', storedMessage);
            }
        } catch (error) {}

        var $form = $(selectors.form);
        if ($form.length && $form.data('role-id')) editRole($form.data('role-id'));

        $(document).on('submit', selectors.form, function (event) {
            event.preventDefault();
            saveRole();
        });
        $(document).on('click', '.js-view-role', function () {
            viewRole($(this).data('id'), $(this).data('url'));
        });
        $(document).on('click', '.js-delete-role', function () {
            deleteRole($(this).data('id'), $(this).data('url'));
        });
    });
})(jQuery);
