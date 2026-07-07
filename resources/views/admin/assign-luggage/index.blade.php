@extends('layouts.admin')

@section('title', 'Assign Luggage || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <div id="assignLuggageConfig"
        data-list-url="{{ route('assign-luggage.list') }}"
        data-save-url="{{ route('assign-luggage.save') }}"
        data-create-url="{{ route('assign-luggage.create') }}"
        data-is-admin="{{ (isset($loggedUser) && $loggedUser->role_id === 0) ? '1' : '0' }}"
        data-empty-message='No luggage assignments found. Click "Assign Luggage" to create a new one.'>
    </div>

    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Assign Luggage</h5>
            </div>
            <ul class="breadcrumb d-none d-md-flex">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item">Assign Luggage</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('assign-luggage.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i>Assign Luggage
            </a>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div id="assignLuggageAlert"></div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="feather-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="card-title mb-0">Luggage Assignment Directory</h5>
                        <form id="assignLuggageSearchForm" action="{{ route('assign-luggage.index') }}" method="GET" class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <span class="input-group-text bg-light border-end-0"><i class="feather-search text-muted"></i></span>
                                <input type="text" name="search" id="assignLuggageSearchInput" class="form-control border-start-0" placeholder="Search locations, status..." value="{{ $search ?? '' }}">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Search</button>
                            <button type="button" id="assignLuggageSearchClear" class="btn btn-sm btn-secondary {{ empty($search) ? 'd-none' : '' }}">Clear</button>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Company</th>
                                        <th>Station</th>
                                        <th>Driver</th>
                                        <th>Pickup Location</th>
                                        <th>Drop Location</th>
                                        <th>Distance</th>
                                        <th>Expected Delivery</th>
                                        <th>Status</th>
                                        <th>Created Date</th>
                                        @if(isset($loggedUser) && $loggedUser->role_id === 0)
                                            <th>Assigned By</th>
                                        @endif
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="assignLuggageTableBody">
                                    <tr>
                                        <td colspan="{{ (isset($loggedUser) && $loggedUser->role_id === 0) ? 11 : 10 }}" class="text-center py-5 text-muted">
                                            <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                                            Loading luggage assignments...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
@endsection

@section('modals')
<div class="modal fade" id="assignLuggageDetailsModal" tabindex="-1" aria-labelledby="assignLuggageDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignLuggageDetailsModalLabel">Luggage Assignment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="assignLuggageDetailsBody">
                <div class="text-center py-5 text-muted">
                    <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                    Loading assignment details...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <a href="#" id="assignLuggageDetailsFull" class="btn btn-light-brand">
                    <i class="feather-external-link me-2"></i>Full Details
                </a>
                <a href="#" id="assignLuggageDetailsEdit" class="btn btn-primary">
                    <i class="feather-edit me-2"></i>Edit Assignment
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/assign-luggage.js') }}"></script>
@endpush
