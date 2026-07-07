@extends('layouts.admin')

@section('title', 'Stations || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <div id="stationsConfig"
        data-list-url="{{ route('stations.list') }}"
        data-save-url="{{ route('stations.save') }}"
        data-create-url="{{ route('stations.create') }}"
        data-empty-message='No stations found. Click "Add Station" to register a new one.'>
    </div>

    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Stations</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item">Stations</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('stations.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i>Add Station
            </a>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div id="stationAlert"></div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="feather-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="card-title mb-0">Station Directory</h5>
                        <form id="stationSearchForm" action="{{ route('stations.index') }}" method="GET" class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <span class="input-group-text bg-light border-end-0"><i class="feather-search text-muted"></i></span>
                                <input type="text" name="search" id="stationSearchInput" class="form-control border-start-0" placeholder="Search name, code, city..." value="{{ $search ?? '' }}">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Search</button>
                            <button type="button" id="stationSearchClear" class="btn btn-sm btn-secondary {{ empty($search) ? 'd-none' : '' }}">Clear</button>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Station Name</th>
                                        <th>Code</th>
                                        <th>Location</th>
                                        <th>Contact Number</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="stationsTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                                            Loading stations...
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
<div class="modal fade" id="stationDetailsModal" tabindex="-1" aria-labelledby="stationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stationDetailsModalLabel">Station Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="stationDetailsBody">
                <div class="text-center py-5 text-muted">
                    <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                    Loading station details...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <a href="#" id="stationDetailsEdit" class="btn btn-primary">
                    <i class="feather-edit me-2"></i>Edit Station
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/stations.js') }}"></script>
@endpush
