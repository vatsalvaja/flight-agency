(function ($) {
    'use strict';

    var selectors = {
        config: '#usersConfig',
        alert: '#userAlert',
        tableBody: '#usersTableBody',
        gridRow: '.dual-view-grid-container .row',
        form: '#userForm',
        detailsModal: '#userDetailsModal',
        detailsBody: '#userDetailsBody',
        detailsEdit: '#userDetailsEdit'
    };

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? 'N/A' : value).html();
    }

    function showUserMessage(type, message) {
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
        return String(status) === '0'
            ? '<span class="badge bg-soft-success text-success px-2 py-1">Active</span>'
            : '<span class="badge bg-soft-danger text-danger px-2 py-1">Inactive</span>';
    }

    function roleBadge(user) {
        user = user || {};
        if (parseInt(user.role_id, 10) === 0) {
            return '<span class="badge bg-soft-primary text-primary px-2 py-1">Admin (System)</span>';
        }
        return '<span class="badge bg-soft-info text-info px-2 py-1">' + escapeHtml(user.role_name) + '</span>';
    }

    function avatar(user) {
        return '<div class="avatar-text bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;font-weight:700;">' +
            escapeHtml(user && user.initials ? user.initials : '?') +
        '</div>';
    }

    function emptyRow(message) {
        return '<tr><td colspan="6" class="text-center py-5 text-muted">' +
            '<i class="feather-alert-circle fs-3 d-block mb-2"></i>' + escapeHtml(message) +
        '</td></tr>';
    }

    window.loadUsers = function () {
        var listUrl = $(selectors.config).data('list-url');
        if (!listUrl || !$(selectors.tableBody).length) return;

        $.ajax({
            url: listUrl,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    renderUsers(response.data || []);
                    return;
                }
                $(selectors.tableBody).html(emptyRow(response.message || 'Unable to load users.'));
                renderUsersGrid([]);
            },
            error: function () {
                $(selectors.tableBody).html(emptyRow('Unable to load users.'));
                renderUsersGrid([]);
            }
        });
    };

    function renderUsers(users) {
        if (!users.length) {
            $(selectors.tableBody).html(emptyRow($(selectors.config).data('empty-message')));
            renderUsersGrid([]);
            return;
        }

        $(selectors.tableBody).html(users.map(function (user) {
            user = user || {};
            return '<tr data-user-id="' + escapeHtml(user.id) + '">' +
                '<td class="ps-4"><code>#' + escapeHtml(user.id) + '</code></td>' +
                '<td class="fw-semibold text-dark">' + escapeHtml(user.name) + '</td>' +
                '<td>' + escapeHtml(user.email) + '</td>' +
                '<td>' + roleBadge(user) + '</td>' +
                '<td>' + statusBadge(user.status) + '</td>' +
                '<td class="text-end pe-4"><div class="d-inline-flex gap-2">' +
                    '<button type="button" class="btn btn-sm btn-light-brand js-view-user" data-id="' + escapeHtml(user.id) + '" data-url="' + escapeHtml(user.data_url) + '" title="View Details"><i class="feather-eye"></i></button>' +
                    '<a href="' + escapeHtml(user.edit_url) + '" class="btn btn-sm btn-light-brand" title="Edit User"><i class="feather-edit"></i></a>' +
                    '<button type="button" class="btn btn-sm btn-light-danger js-delete-user" data-id="' + escapeHtml(user.id) + '" data-url="' + escapeHtml(user.delete_url) + '" title="Delete User"><i class="feather-trash-2"></i></button>' +
                '</div></td>' +
            '</tr>';
        }).join(''));
        renderUsersGrid(users);
    }

    function renderUsersGrid(users) {
        var $gridRow = $(selectors.gridRow);
        if (!$gridRow.length) return;

        if (!users.length) {
            $gridRow.html('<div class="col-12 text-center py-5 text-muted card shadow-none border">' + escapeHtml($(selectors.config).data('empty-message')) + '</div>');
            return;
        }

        $gridRow.html(users.map(function (user) {
            user = user || {};
            return '<div class="col-12 col-md-6 col-lg-4 col-xl-4">' +
                '<div class="dual-view-card card-hover-effect">' +
                    '<div class="card-body">' +
                        '<div class="dual-view-card-header">' +
                            '<div class="dual-view-card-avatar">' + avatar(user) + '</div>' +
                            '<div class="dual-view-card-title-area"><div class="dual-view-card-title">' + escapeHtml(user.name) + '</div><div class="dual-view-card-subtitle">' + escapeHtml(user.email) + '</div></div>' +
                        '</div>' +
                        '<div class="dual-view-card-details">' +
                            gridDetail('Role', roleBadge(user), true) +
                            gridDetail('Status', statusBadge(user.status), true) +
                            gridDetail('Created', user.created_at) +
                        '</div>' +
                    '</div>' +
                    '<div class="card-footer">' +
                        '<button type="button" class="btn btn-sm btn-light-brand js-view-user" data-id="' + escapeHtml(user.id) + '" data-url="' + escapeHtml(user.data_url) + '" title="View Details"><i class="feather-eye"></i></button>' +
                        '<a href="' + escapeHtml(user.edit_url) + '" class="btn btn-sm btn-light-brand" title="Edit User"><i class="feather-edit"></i></a>' +
                        '<button type="button" class="btn btn-sm btn-light-danger js-delete-user" data-id="' + escapeHtml(user.id) + '" data-url="' + escapeHtml(user.delete_url) + '" title="Delete User"><i class="feather-trash-2"></i></button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join(''));
    }

    function gridDetail(label, value, html) {
        return '<div class="dual-view-card-detail-item"><span class="dual-view-card-detail-label">' + escapeHtml(label) + '</span><span class="dual-view-card-detail-value">' + (html ? value : escapeHtml(value)) + '</span></div>';
    }

    window.editUser = function (id) {
        var $form = $(selectors.form);
        var dataUrl = $form.data('data-url') || (id ? '/admin/users/' + id + '/data' : '');
        if (!dataUrl || !$form.length) return;

        $.getJSON(dataUrl).done(function (response) {
            if (!response.success) {
                showUserMessage('error', response.message || 'Unable to load user.');
                return;
            }
            populateUserForm(response.data || {});
        }).fail(function () {
            showUserMessage('error', 'Unable to load user.');
        });
    };

    function populateUserForm(user) {
        $('#user_id').val(user.id || '');
        $('#name').val(user.name || '');
        $('#email').val(user.email || '');
        $('#password').val('');
        $('#role_id').val(String(user.role_id || '0'));
        $('#status').val(String(user.status || '0'));
    }

    window.saveUser = function () {
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
                    showUserMessage('error', response.message || 'Unable to save user.');
                    return;
                }
                redirectToIndex(response.message);
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                if (xhr.status === 422 && response.errors) {
                    displayValidationErrors(response.errors);
                    showUserMessage('error', response.message || 'Please check the form errors below.');
                    return;
                }
                showUserMessage('error', response.message || 'Unable to save user.');
            },
            complete: function () {
                $button.prop('disabled', false).html(original);
            }
        });
    };

    function redirectToIndex(message) {
        var indexUrl = $(selectors.form).data('index-url');
        if (!indexUrl) {
            loadUsers();
            return;
        }
        try { window.sessionStorage.setItem('userMessage', message || 'User saved successfully.'); } catch (error) {}
        window.location.href = indexUrl;
    }

    window.viewUser = function (id, dataUrl) {
        dataUrl = dataUrl || (id ? '/admin/users/' + id + '/data' : '');
        if (!dataUrl || !$(selectors.detailsModal).length) return;

        $(selectors.detailsBody).html('<div class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading user details...</div>');
        $(selectors.detailsEdit).attr('href', '#').addClass('disabled');
        openModal();

        $.getJSON(dataUrl).done(function (response) {
            if (!response.success) {
                $(selectors.detailsBody).html('<div class="alert alert-danger mb-0">' + escapeHtml(response.message || 'Unable to load user details.') + '</div>');
                return;
            }
            var user = response.data || {};
            $(selectors.detailsEdit).attr('href', user.edit_url || '#').toggleClass('disabled', !user.edit_url);
            $(selectors.detailsBody).html(
                '<div class="text-center mb-4">' + avatar(user) + '<h5 class="mt-3 mb-1 text-dark">' + escapeHtml(user.name) + '</h5><div class="text-muted fs-12">' + escapeHtml(user.email) + '</div></div>' +
                detailRow('User ID', '#' + escapeHtml(user.id)) +
                detailRow('Role', roleBadge(user)) +
                detailRow('Status', statusBadge(user.status)) +
                detailRow('Created At', escapeHtml(user.created_at)) +
                detailRow('Last Updated', escapeHtml(user.updated_at))
            );
        }).fail(function () {
            $(selectors.detailsBody).html('<div class="alert alert-danger mb-0">Unable to load user details.</div>');
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

    window.deleteUser = function (id, deleteUrl) {
        if (!id && !deleteUrl) return;
        deleteUrl = deleteUrl || ('/admin/users/' + id);

        function runDelete() {
            $.ajax({
                url: deleteUrl,
                method: 'POST',
                data: { _method: 'DELETE' },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showUserMessage('success', response.message);
                        loadUsers();
                        return;
                    }
                    showUserMessage('error', response.message || 'Unable to delete user.');
                },
                error: function (xhr) {
                    var response = xhr.responseJSON || {};
                    showUserMessage('error', response.message || 'Unable to delete user.');
                }
            });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete User',
                text: 'Are you sure you want to delete this user?',
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

        if (window.confirm('Are you sure you want to delete this user?')) runDelete();
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
        loadUsers();

        try {
            var storedMessage = window.sessionStorage.getItem('userMessage');
            if (storedMessage) {
                window.sessionStorage.removeItem('userMessage');
                showUserMessage('success', storedMessage);
            }
        } catch (error) {}

        var $form = $(selectors.form);
        if ($form.length && $form.data('user-id')) editUser($form.data('user-id'));

        $(document).on('submit', selectors.form, function (event) {
            event.preventDefault();
            saveUser();
        });
        $(document).on('click', '.js-view-user', function () {
            viewUser($(this).data('id'), $(this).data('url'));
        });
        $(document).on('click', '.js-delete-user', function () {
            deleteUser($(this).data('id'), $(this).data('url'));
        });
    });
})(jQuery);
