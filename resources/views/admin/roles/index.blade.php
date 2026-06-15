@extends('layouts.admin')

@section('title', 'Role Management || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
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
                                <tbody>
                                    @forelse($roles as $role)
                                        <tr>
                                            <td class="ps-4"><code>#{{ $role->id }}</code></td>
                                            <td class="fw-semibold text-dark">{{ $role->role_name }}</td>
                                            <td>
                                                @if($role->status == 0)
                                                    <span class="badge bg-soft-success text-success px-2 py-1">Active</span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger px-2 py-1">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $role->created_at ? $role->created_at->format('M d, Y') : 'N/A' }}</td>
                                            <td class="text-end pe-4">
                                                <div class="d-inline-flex gap-2">
                                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-light-brand" title="Edit Role">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this role?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-light-danger" title="Delete Role">
                                                            <i class="feather-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="feather-alert-circle fs-3 d-block mb-2"></i>
                                                No custom roles configured yet.
                                            </td>
                                        </tr>
                                    @endforelse
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
