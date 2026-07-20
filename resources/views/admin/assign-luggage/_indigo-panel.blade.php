@php
    /**
     * IndiGo document upload + auto-filled baggage details.
     * Shown only when IndiGo is the selected Flight Company (toggled by indigo-ocr.js).
     *
     * Expects: $indigoCompanyId (int|null) and optional $assignment (AssignLuggage|null).
     */
    $indigoAssignment = $assignment ?? null;
    $indigoDocPath = old('indigo_document_path', $indigoAssignment->indigo_document_path ?? '');
    $indigoPickupDate = old('pickup_date', ($indigoAssignment && $indigoAssignment->pickup_date) ? $indigoAssignment->pickup_date->format('Y-m-d') : '');
    $indigoDeliveryDate = old('delivery_date', ($indigoAssignment && $indigoAssignment->delivery_date) ? $indigoAssignment->delivery_date->format('Y-m-d') : '');
@endphp

<style>
    /* IndiGo panel — theme-aware surface (light + app-skin-dark) */
    .indigo-ocr-panel { background-color: #f8f9fb; }
    html.app-skin-dark .indigo-ocr-panel {
        background-color: rgba(255, 255, 255, 0.045) !important;
        border-color: #334155 !important;
    }
    html.app-skin-dark .indigo-ocr-panel hr {
        border-color: #334155 !important;
        opacity: 1;
    }
    html.app-skin-dark .indigo-ocr-panel .avatar-text.bg-soft-primary {
        background-color: rgba(59, 130, 246, 0.15) !important;
        color: #93c5fd !important;
    }
</style>

<div id="indigoOcrConfig"
     data-extract-url="{{ route('assign-luggage.indigo-extract') }}"
     data-indigo-company-id="{{ $indigoCompanyId ?? '' }}"
     data-max-size-mb="8"
     class="d-none"></div>

<div id="indigo-panel" class="indigo-ocr-panel border rounded-3 p-3 p-md-4 mb-4" style="display: none;">
    <div class="d-flex align-items-center mb-3">
        <span class="avatar-text avatar-sm bg-soft-primary text-primary rounded me-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
            <i class="feather-file-text"></i>
        </span>
        <div>
            <h6 class="fw-bold text-dark mb-0">IndiGo Baggage Document</h6>
            <span class="fs-12 text-muted">Upload the IndiGo file to auto-fill the details below. Every field stays editable.</span>
        </div>
    </div>

    <!-- Upload IndiGo File Details -->
    <div class="mb-3">
        <label class="form-label fw-semibold" for="indigo_document_input">Upload IndiGo File Details</label>
        <input type="file" id="indigo_document_input" class="form-control" accept="image/jpeg,image/png,image/jpg,application/pdf">
        <div class="form-text">Allowed formats: JPG, JPEG, PNG, PDF. Max size: 8MB.</div>

        <input type="hidden" name="indigo_document_path" id="indigo_document_path" value="{{ $indigoDocPath }}">

        <div id="indigo-file-meta" class="d-flex align-items-center gap-2 mt-2 {{ $indigoDocPath ? '' : 'd-none' }}">
            <i class="feather-paperclip text-muted"></i>
            <a href="{{ $indigoDocPath ? asset($indigoDocPath) : '#' }}" target="_blank" id="indigo-file-link" class="fw-semibold text-primary text-truncate" style="max-width: 260px;">{{ $indigoDocPath ? 'View uploaded document' : '' }}</a>
            <button type="button" id="indigo-file-remove" class="btn btn-sm btn-light-danger py-0 px-2" title="Remove document"><i class="feather-x"></i></button>
        </div>

        <div id="indigo-status" class="mt-2 fs-12 d-none"></div>
    </div>

    <hr class="my-3">

    <h6 class="fw-semibold text-dark mb-3">Baggage &amp; Customer Details</h6>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label fw-semibold" for="reference_number">Reference Number</label>
            <input type="text" name="reference_number" id="reference_number" class="form-control @error('reference_number') is-invalid @enderror" value="{{ old('reference_number', $indigoAssignment->reference_number ?? '') }}" placeholder="File reference number">
            @error('reference_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label fw-semibold" for="number_of_bags">Number of Bags</label>
            <input type="number" min="0" name="number_of_bags" id="number_of_bags" class="form-control @error('number_of_bags') is-invalid @enderror" value="{{ old('number_of_bags', $indigoAssignment->number_of_bags ?? '') }}" placeholder="e.g. 2">
            @error('number_of_bags')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label fw-semibold" for="pnr_number">PNR Number</label>
            <input type="text" name="pnr_number" id="pnr_number" class="form-control @error('pnr_number') is-invalid @enderror" value="{{ old('pnr_number', $indigoAssignment->pnr_number ?? '') }}" placeholder="PNR">
            @error('pnr_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label fw-semibold" for="pickup_date">Pickup Date</label>
            <input type="date" name="pickup_date" id="pickup_date" class="form-control @error('pickup_date') is-invalid @enderror" value="{{ $indigoPickupDate }}">
            @error('pickup_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label fw-semibold" for="delivery_date">Delivery Date</label>
            <input type="date" name="delivery_date" id="delivery_date" class="form-control @error('delivery_date') is-invalid @enderror" value="{{ $indigoDeliveryDate }}">
            @error('delivery_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label fw-semibold" for="pincode">Pincode</label>
            <input type="text" name="pincode" id="pincode" class="form-control @error('pincode') is-invalid @enderror" value="{{ old('pincode', $indigoAssignment->pincode ?? '') }}" placeholder="Postal code">
            @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold" for="customer_name">Customer Name</label>
            <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', $indigoAssignment->customer_name ?? '') }}" placeholder="Passenger name">
            @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold" for="contact_number">Customer Contact Number</label>
            <input type="text" name="contact_number" id="contact_number" class="form-control @error('contact_number') is-invalid @enderror" value="{{ old('contact_number', $indigoAssignment->contact_number ?? '') }}" placeholder="Contact number">
            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 mb-1">
            <label class="form-label fw-semibold" for="customer_address">Customer Address</label>
            <textarea name="customer_address" id="customer_address" rows="2" class="form-control @error('customer_address') is-invalid @enderror" placeholder="Delivery address">{{ old('customer_address', $indigoAssignment->customer_address ?? '') }}</textarea>
            @error('customer_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
