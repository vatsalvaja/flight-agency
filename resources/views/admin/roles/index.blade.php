@extends('layouts.admin')

@section('title', 'Role Management || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <div id="rolesConfig"
        data-list-url="{{ route('roles.list') }}"
        data-save-url="{{ route('roles.save') }}"
        data-create-url="{{ route('roles.create') }}"
        data-empty-message="No custom roles configured yet.">
    </div>

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Role Management</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item">Roles</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('roles.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i>Add Role
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div id="roleAlert"></div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="feather-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="feather-alert-octagon me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title mb-0">System Roles</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width: 80px;">ID</th>
                                        <th>Role Name</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th class="text-end pe-4" style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="rolesTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                                            Loading roles...
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
</div>
@endsection

@section('modals')
<div class="modal fade" id="roleDetailsModal" tabindex="-1" aria-labelledby="roleDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleDetailsModalLabel">Role Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="roleDetailsBody">
                <div class="text-center py-5 text-muted">
                    <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                    Loading role details...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <a href="#" id="roleDetailsEdit" class="btn btn-primary">
                    <i class="feather-edit me-2"></i>Edit Role
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/roles.js') }}"></script>
@endpush
