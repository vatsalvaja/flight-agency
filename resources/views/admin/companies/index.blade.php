@extends('layouts.admin')

@section('title', 'Flight Companies || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <div id="flightCompaniesConfig"
        data-list-url="{{ route('companies.list') }}"
        data-save-url="{{ route('companies.save') }}"
        data-create-url="{{ route('companies.create') }}"
        data-empty-message='No companies found. Click "Add Company" to register a new one.'>
    </div>

    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Flight Companies</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item">Flight Companies</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('companies.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i>Add Company
            </a>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div id="flightCompanyAlert"></div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="feather-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Flight Companies Directory</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Logo</th>
                                        <th>Company Name</th>
                                        <th>Code</th>
                                        <th>Contact Person</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="flightCompaniesTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                                            Loading companies...
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
<div class="modal fade" id="flightCompanyDetailsModal" tabindex="-1" aria-labelledby="flightCompanyDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="flightCompanyDetailsModalLabel">Company Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="flightCompanyDetailsBody">
                <div class="text-center py-5 text-muted">
                    <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                    Loading company details...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <a href="#" id="flightCompanyDetailsEdit" class="btn btn-primary">
                    <i class="feather-edit me-2"></i>Edit Company
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/flight-companies.js') }}"></script>
@endpush
