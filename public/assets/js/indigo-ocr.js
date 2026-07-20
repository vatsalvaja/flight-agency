(function ($) {
    'use strict';

    // Maps the AJAX response keys to the IndiGo panel input ids.
    var FIELD_MAP = {
        reference_number: 'reference_number',
        number_of_bags: 'number_of_bags',
        pickup_date: 'pickup_date',
        delivery_date: 'delivery_date',
        pnr_number: 'pnr_number',
        customer_name: 'customer_name',
        contact_number: 'contact_number',
        address: 'customer_address',
        pincode: 'pincode'
    };

    var ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    var GENERIC_FAILURE = 'Unable to read the uploaded document. Please upload a clearer image or enter the details manually.';

    function config() {
        return document.getElementById('indigoOcrConfig');
    }

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function escapeText(value) {
        return $('<span>').text(value == null ? '' : value).html();
    }

    // ----- IndiGo detection & panel toggle -------------------------------------
    function selectedIsIndigo() {
        var select = document.getElementById('company_id');
        var cfg = config();
        if (!select || !cfg) return false;

        var option = select.options[select.selectedIndex];
        if (option && option.getAttribute('data-indigo') === '1') return true;

        var indigoId = cfg.getAttribute('data-indigo-company-id');
        return indigoId && String(select.value) === String(indigoId);
    }

    function togglePanel() {
        var panel = document.getElementById('indigo-panel');
        if (!panel) return;
        panel.style.display = selectedIsIndigo() ? '' : 'none';
    }

    // ----- Status + toast ------------------------------------------------------
    function setStatus(state, message) {
        var $status = $('#indigo-status');
        if (!$status.length) return;

        if (!message) {
            $status.addClass('d-none').html('');
            return;
        }

        var icons = {
            loading: '<span class="spinner-border spinner-border-sm text-primary me-2"></span>',
            success: '<i class="feather-check-circle me-1"></i>',
            error: '<i class="feather-alert-triangle me-1"></i>'
        };
        var color = state === 'error' ? 'text-danger' : (state === 'success' ? 'text-success' : 'text-muted');

        $status
            .removeClass('d-none text-danger text-success text-muted')
            .addClass(color)
            .html((icons[state] || '') + escapeText(message));
    }

    function toast(type, message) {
        if (typeof Swal === 'undefined') return;
        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true })
            .fire({ icon: type, title: message });
    }

    // ----- Fill the form -------------------------------------------------------
    function fillForm(data) {
        Object.keys(FIELD_MAP).forEach(function (key) {
            var value = data[key];
            if (value === null || value === undefined || value === '') return;
            var el = document.getElementById(FIELD_MAP[key]);
            if (el) el.value = value;
        });

        // Shared existing fields: only fill when empty so manual input is never clobbered.
        if (data.address) {
            var drop = document.getElementById('drop_location');
            if (drop && !drop.value.trim()) drop.value = data.address;
        }
        if (data.delivery_date) {
            var expected = document.getElementById('expected_delivery_date');
            if (expected && !expected.value.trim()) {
                expected.value = expected.type === 'datetime-local' ? data.delivery_date + 'T00:00' : data.delivery_date;
            }
        }

        if (data.document_path) {
            var hidden = document.getElementById('indigo_document_path');
            if (hidden) hidden.value = data.document_path;
            showFileMeta(data.document_url || (hidden ? hidden.value : ''));
        }
    }

    function showFileMeta(url) {
        var $meta = $('#indigo-file-meta');
        if (!$meta.length) return;
        $meta.removeClass('d-none');
        $('#indigo-file-link').attr('href', url || '#').text('View uploaded document');
    }

    function resetFileMeta() {
        $('#indigo-file-meta').addClass('d-none');
        $('#indigo-file-link').attr('href', '#').text('');
        var hidden = document.getElementById('indigo_document_path');
        if (hidden) hidden.value = '';
    }

    // ----- Validation + upload -------------------------------------------------
    function validateFile(file, maxMb) {
        if (!file) return 'Please choose a document to upload.';
        var type = (file.type || '').toLowerCase();
        var okType = ALLOWED_TYPES.indexOf(type) !== -1 || /\.(jpe?g|png|pdf)$/i.test(file.name || '');
        if (!okType) return 'Only JPG, JPEG, PNG or PDF files are allowed.';
        if (file.size > maxMb * 1024 * 1024) return 'The document must not be larger than ' + maxMb + 'MB.';
        return null;
    }

    function upload(file) {
        var cfg = config();
        if (!cfg) return;

        var url = cfg.getAttribute('data-extract-url');
        var maxMb = parseInt(cfg.getAttribute('data-max-size-mb'), 10) || 8;

        var validationError = validateFile(file, maxMb);
        if (validationError) {
            setStatus('error', validationError);
            toast('error', validationError);
            return;
        }

        var formData = new FormData();
        formData.append('indigo_document', file);

        setStatus('loading', 'Uploading...');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable && e.loaded >= e.total) {
                            setStatus('loading', 'Reading document...');
                        }
                    });
                    xhr.upload.addEventListener('load', function () {
                        setStatus('loading', 'Reading document...');
                    });
                }
                return xhr;
            },
            success: function (response) {
                if (!response || !response.success) {
                    var failMsg = (response && response.message) || GENERIC_FAILURE;
                    setStatus('error', failMsg);
                    toast('error', failMsg);
                    return;
                }
                setStatus('loading', 'Filling form...');
                fillForm(response.data || {});
                var okMsg = response.message || 'Document details extracted successfully.';
                setStatus('success', okMsg);
                toast('success', okMsg);
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                var msg = response.message || GENERIC_FAILURE;
                setStatus('error', msg);
                toast('error', msg);
            }
        });
    }

    $(function () {
        togglePanel();

        $(document).on('change', '#company_id', function () {
            togglePanel();
        });

        $(document).on('change', '#indigo_document_input', function (e) {
            var file = e.target.files && e.target.files[0];
            if (!file) return;
            upload(file);
            // Reset so re-selecting the same file fires change again.
            e.target.value = '';
        });

        $(document).on('click', '#indigo-file-remove', function () {
            resetFileMeta();
            setStatus(null, '');
        });
    });
})(jQuery);
