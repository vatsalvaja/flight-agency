(function ($) {
    'use strict';

    var selectors = {
        config: '#flightCompaniesConfig',
        alert: '#flightCompanyAlert',
        tableBody: '#flightCompaniesTableBody',
        gridRow: '.dual-view-grid-container .row',
        form: '#flightCompanyForm',
        currentLogoPreview: '#currentLogoPreview',
        detailsModal: '#flightCompanyDetailsModal',
        detailsBody: '#flightCompanyDetailsBody',
        detailsEdit: '#flightCompanyDetailsEdit'
    };

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function showFlightCompanyMessage(type, message) {
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

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? 'N/A' : value).html();
    }

    function displayValue(value, fallback) {
        if (value === null || value === undefined || value === '') {
            return fallback || 'N/A';
        }

        return value;
    }

    function companyInitial(companyName) {
        var name = companyName || '';
        return escapeHtml(name.charAt(0).toUpperCase() || '?');
    }

    function statusBadge(status) {
        if (status === 'active') {
            return '<span class="badge bg-soft-success text-success px-2 py-1">Active</span>';
        }

        return '<span class="badge bg-soft-danger text-danger px-2 py-1">Inactive</span>';
    }

    function logoCell(company) {
        company = company || {};

        if (company.logo_url) {
            return '<img src="' + escapeHtml(company.logo_url) + '" alt="logo" class="rounded" style="height: 36px; width: 36px; object-fit: cover;">';
        }

        return '<div class="avatar-text avatar-sm bg-soft-secondary text-secondary rounded d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">' +
            companyInitial(company.company_name) +
        '</div>';
    }

    function emptyRow(message) {
        return '<tr>' +
            '<td colspan="8" class="text-center py-5 text-muted">' +
                '<i class="feather-alert-circle fs-3 d-block mb-2"></i>' +
                escapeHtml(message) +
            '</td>' +
        '</tr>';
    }

    function renderFlightCompanies(companies) {
        var $body = $(selectors.tableBody);

        if (!$body.length) {
            return;
        }

        if (!companies || !companies.length) {
            $body.html(emptyRow($(selectors.config).data('empty-message')));
            renderFlightCompaniesGrid(companies);
            return;
        }

        var rows = companies.map(function (company) {
            company = company || {};

            return '<tr data-company-id="' + escapeHtml(company.id) + '">' +
                '<td class="ps-4">' + logoCell(company) + '</td>' +
                '<td class="fw-semibold text-dark">' + escapeHtml(company.company_name) + '</td>' +
                '<td><code>' + escapeHtml(company.company_code) + '</code></td>' +
                '<td>' + escapeHtml(company.contact_person) + '</td>' +
                '<td>' + escapeHtml(company.email) + '</td>' +
                '<td>' + escapeHtml(company.phone) + '</td>' +
                '<td>' + statusBadge(company.status) + '</td>' +
                '<td class="text-end pe-4">' +
                    '<div class="d-inline-flex gap-2">' +
                        '<button type="button" class="btn btn-sm btn-light-brand js-view-flight-company" data-id="' + escapeHtml(company.id) + '" data-url="' + escapeHtml(company.data_url) + '" title="View Details">' +
                            '<i class="feather-eye"></i>' +
                        '</button>' +
                        '<a href="' + escapeHtml(company.edit_url) + '" class="btn btn-sm btn-light-brand js-edit-flight-company" data-id="' + escapeHtml(company.id) + '" title="Edit Company">' +
                            '<i class="feather-edit"></i>' +
                        '</a>' +
                        '<button type="button" class="btn btn-sm btn-light-danger js-delete-flight-company" data-id="' + escapeHtml(company.id) + '" data-url="' + escapeHtml(company.delete_url) + '" title="Delete Company">' +
                            '<i class="feather-trash-2"></i>' +
                        '</button>' +
                    '</div>' +
                '</td>' +
            '</tr>';
        }).join('');

        $body.html(rows);
        renderFlightCompaniesGrid(companies);
    }

    function renderFlightCompaniesGrid(companies) {
        var $gridRow = $(selectors.gridRow);

        if (!$gridRow.length) {
            return;
        }

        if (!companies || !companies.length) {
            $gridRow.html(
                '<div class="col-12 text-center py-5 text-muted card shadow-none border">' +
                    '<i class="feather-alert-circle fs-3 d-block mb-2"></i>' +
                    escapeHtml($(selectors.config).data('empty-message')) +
                '</div>'
            );
            return;
        }

        var cards = companies.map(function (company) {
            company = company || {};

            return '<div class="col-12 col-md-6 col-lg-4 col-xl-4">' +
                '<div class="dual-view-card card-hover-effect">' +
                    '<div class="card-body">' +
                        '<div class="dual-view-card-header">' +
                            '<div class="dual-view-card-avatar">' + logoCell(company) + '</div>' +
                            '<div class="dual-view-card-title-area">' +
                                '<div class="dual-view-card-title" title="' + escapeHtml(company.company_name) + '">' + escapeHtml(company.company_name) + '</div>' +
                                '<div class="dual-view-card-subtitle" title="' + escapeHtml(company.company_code) + '">Code: ' + escapeHtml(company.company_code) + '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="dual-view-card-details">' +
                            gridDetail('Contact Person', company.contact_person) +
                            gridDetail('Email', company.email) +
                            gridDetail('Phone', company.phone) +
                            gridDetail('Status', statusBadge(company.status)) +
                        '</div>' +
                    '</div>' +
                    '<div class="card-footer">' +
                        '<button type="button" class="btn btn-sm btn-light-brand js-view-flight-company" data-id="' + escapeHtml(company.id) + '" data-url="' + escapeHtml(company.data_url) + '" title="View Details">' +
                            '<i class="feather-eye"></i>' +
                        '</button>' +
                        '<a href="' + escapeHtml(company.edit_url) + '" class="btn btn-sm btn-light-brand js-edit-flight-company" data-id="' + escapeHtml(company.id) + '" title="Edit Company">' +
                            '<i class="feather-edit"></i>' +
                        '</a>' +
                        '<button type="button" class="btn btn-sm btn-light-danger js-delete-flight-company" data-id="' + escapeHtml(company.id) + '" data-url="' + escapeHtml(company.delete_url) + '" title="Delete Company">' +
                            '<i class="feather-trash-2"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');

        $gridRow.html(cards);
    }

    function gridDetail(label, value) {
        var valueHtml = label === 'Status' ? value : escapeHtml(value);

        return '<div class="dual-view-card-detail-item">' +
            '<span class="dual-view-card-detail-label">' + escapeHtml(label) + '</span>' +
            '<span class="dual-view-card-detail-value">' + valueHtml + '</span>' +
        '</div>';
    }

    window.loadFlightCompanies = function () {
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
                    renderFlightCompanies(response.data);
                    return;
                }

                $(selectors.tableBody).html(emptyRow(response.message || 'Unable to load companies.'));
            },
            error: function () {
                $(selectors.tableBody).html(emptyRow('Unable to load companies.'));
            }
        });
    };

    window.resetFlightCompanyForm = function () {
        var $form = $(selectors.form);

        if (!$form.length) {
            return;
        }

        var companyId = $('#company_id').val();
        $form[0].reset();
        clearValidationErrors();

        if (!companyId) {
            $('#company_id').val('');
            $(selectors.currentLogoPreview).addClass('d-none').find('img').attr('src', '');
        } else {
            $('#company_id').val(companyId);
        }
    };

    window.editFlightCompany = function (id) {
        var $form = $(selectors.form);
        var dataUrl = $form.data('data-url');

        if (!dataUrl && id) {
            dataUrl = '/admin/companies/' + id + '/data';
        }

        if (!dataUrl || !$form.length) {
            return;
        }

        $.ajax({
            url: dataUrl,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (!response.success) {
                    showFlightCompanyMessage('error', response.message || 'Unable to load company.');
                    return;
                }

                populateFlightCompanyForm(response.data);
            },
            error: function () {
                showFlightCompanyMessage('error', 'Unable to load company.');
            }
        });
    };

    function populateFlightCompanyForm(company) {
        company = company || {};

        $('#company_id').val(company.id || '');
        $('#company_name').val(company.company_name || '');
        $('#company_code').val(company.company_code || '');
        $('#contact_person').val(company.contact_person || '');
        $('#email').val(company.email || '');
        $('#phone').val(company.phone || '');
        $('#status').val(company.status || 'active');
        $('#address').val(company.address || '');

        if (company.logo_url) {
            $(selectors.currentLogoPreview).removeClass('d-none').find('img').attr('src', company.logo_url);
        } else {
            $(selectors.currentLogoPreview).addClass('d-none').find('img').attr('src', '');
        }
    }

    window.viewFlightCompany = function (id, dataUrl) {
        dataUrl = dataUrl || (id ? '/admin/companies/' + id + '/data' : '');

        if (!dataUrl || !$(selectors.detailsModal).length) {
            return;
        }

        $(selectors.detailsBody).html(
            '<div class="text-center py-5 text-muted">' +
                '<span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>' +
                'Loading company details...' +
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
                    $(selectors.detailsBody).html('<div class="alert alert-danger mb-0">' + escapeHtml(response.message || 'Unable to load company details.') + '</div>');
                    return;
                }

                renderFlightCompanyDetails(response.data || {});
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                $(selectors.detailsBody).html('<div class="alert alert-danger mb-0">' + escapeHtml(response.message || 'Unable to load company details.') + '</div>');
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

    function renderFlightCompanyDetails(company) {
        var logoHtml = company.logo_url
            ? '<img src="' + escapeHtml(company.logo_url) + '" alt="Logo" class="img-fluid rounded mb-4 shadow-sm" style="max-height: 120px; object-fit: contain;">'
            : '<div class="avatar-text avatar-xl bg-soft-primary text-primary rounded mb-4 d-flex align-items-center justify-content-center fs-1" style="width: 100px; height: 100px; background-color: rgba(59, 130, 246, 0.1);">' + companyInitial(company.company_name) + '</div>';

        var emailHtml = company.email
            ? '<a href="mailto:' + escapeHtml(company.email) + '" class="text-primary fw-semibold"><i class="feather-mail me-1"></i>' + escapeHtml(company.email) + '</a>'
            : '<span class="text-muted">Not Specified</span>';

        var phoneHtml = company.phone
            ? '<i class="feather-phone me-1 text-muted"></i>' + escapeHtml(company.phone)
            : '<span class="text-muted">Not Specified</span>';

        $(selectors.detailsEdit).attr('href', company.edit_url || '#').toggleClass('disabled', !company.edit_url);

        $(selectors.detailsBody).html(
            '<div class="row">' +
                '<div class="col-md-4 col-sm-12 mb-4 mb-md-0">' +
                    '<div class="text-center d-flex flex-column align-items-center justify-content-center py-3">' +
                        logoHtml +
                        '<h4 class="fw-bold mb-1 text-dark">' + escapeHtml(company.company_name) + '</h4>' +
                        '<span class="fs-12 text-muted mb-3">Code: <code>' + escapeHtml(company.company_code) + '</code></span>' +
                        statusBadge(company.status) +
                    '</div>' +
                '</div>' +
                '<div class="col-md-8 col-sm-12">' +
                    detailRow('Contact Person', escapeHtml(displayValue(company.contact_person, 'Not Specified'))) +
                    detailRow('Email Address', emailHtml) +
                    detailRow('Phone Number', phoneHtml) +
                    detailRow('Address', '<span style="white-space: pre-line;">' + escapeHtml(displayValue(company.address, 'Not Specified')) + '</span>') +
                    detailRow('Created At', escapeHtml(displayValue(company.created_at, 'N/A'))) +
                    '<div class="row">' +
                        '<div class="col-sm-4 text-muted fw-medium">Last Updated</div>' +
                        '<div class="col-sm-8 text-dark">' + escapeHtml(displayValue(company.updated_at, 'N/A')) + '</div>' +
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

    window.saveFlightCompany = function () {
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
                    showFlightCompanyMessage('error', response.message || 'Unable to save company.');
                    return;
                }

                showFlightCompanyMessage('success', response.message);

                redirectToCompanyIndex(response.message);
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};

                if (xhr.status === 422 && response.errors) {
                    displayValidationErrors(response.errors);
                    showFlightCompanyMessage('error', response.message || 'Please check the form errors below.');
                    return;
                }

                showFlightCompanyMessage('error', response.message || 'Unable to save company.');
            },
            complete: function () {
                $submitButton.prop('disabled', false).html(originalButtonHtml);
            }
        });
    };

    function redirectToCompanyIndex(message) {
        var indexUrl = $(selectors.form).data('index-url');

        if (!indexUrl) {
            loadFlightCompanies();
            return;
        }

        try {
            window.sessionStorage.setItem('flightCompanyMessage', message || 'Company saved successfully.');
        } catch (error) {
            // Ignore storage issues and continue with the redirect.
        }

        window.location.href = indexUrl;
    }

    window.deleteFlightCompany = function (id, deleteUrl) {
        if (!id && !deleteUrl) {
            return;
        }

        deleteUrl = deleteUrl || ('/admin/companies/' + id);

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
                        showFlightCompanyMessage('success', response.message);
                        loadFlightCompanies();
                        return;
                    }

                    showFlightCompanyMessage('error', response.message || 'Unable to delete company.');
                },
                error: function (xhr) {
                    var response = xhr.responseJSON || {};
                    showFlightCompanyMessage('error', response.message || 'Unable to delete company.');
                }
            });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Company',
                text: 'Are you sure you want to delete this company?',
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

        if (window.confirm('Are you sure you want to delete this company?')) {
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

        loadFlightCompanies();

        try {
            var storedMessage = window.sessionStorage.getItem('flightCompanyMessage');
            if (storedMessage) {
                window.sessionStorage.removeItem('flightCompanyMessage');
                showFlightCompanyMessage('success', storedMessage);
            }
        } catch (error) {
            // Ignore storage issues; the AJAX flow still works.
        }

        var $form = $(selectors.form);
        if ($form.length && $form.data('company-id')) {
            editFlightCompany($form.data('company-id'));
        }

        $(document).on('submit', selectors.form, function (event) {
            event.preventDefault();
            saveFlightCompany();
        });

        $(document).on('click', '.js-delete-flight-company', function () {
            deleteFlightCompany($(this).data('id'), $(this).data('url'));
        });

        $(document).on('click', '.js-view-flight-company', function () {
            viewFlightCompany($(this).data('id'), $(this).data('url'));
        });
    });
})(jQuery);
