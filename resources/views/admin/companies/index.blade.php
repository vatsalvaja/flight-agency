@extends('layouts.admin')

@section('title', 'Companies || ' . ($appSettings->application_name ?? 'SkyTrack'))

@section('content')
<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Companies</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item">Companies</li>
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
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="feather-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Company Directory</h5>
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
                                <tbody>
                                    @forelse($companies as $company)
                                        <tr>
                                            <td class="ps-4">
                                                @if($company->logo)
                                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="logo" class="rounded" style="height: 36px; width: 36px; object-fit: cover;">
                                                @else
                                                    <div class="avatar-text avatar-sm bg-soft-secondary text-secondary rounded d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                        {{ substr($company->company_name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="fw-semibold text-dark">{{ $company->company_name }}</td>
                                            <td><code>{{ $company->company_code ?? 'N/A' }}</code></td>
                                            <td>{{ $company->contact_person ?? 'N/A' }}</td>
                                            <td>{{ $company->email ?? 'N/A' }}</td>
                                            <td>{{ $company->phone ?? 'N/A' }}</td>
                                            <td>
                                                @if($company->status === 'active')
                                                    <span class="badge bg-soft-success text-success px-2 py-1">Active</span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger px-2 py-1">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-inline-flex gap-2">
                                                    <a href="{{ route('companies.show', $company->id) }}" class="btn btn-sm btn-light-brand" title="View Details">
                                                        <i class="feather-eye"></i>
                                                    </a>
                                                    <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-sm btn-light-brand" title="Edit Company">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <form action="{{ route('companies.destroy', $company->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this company?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-light-danger" title="Delete Company">
                                                            <i class="feather-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <i class="feather-alert-circle fs-3 d-block mb-2"></i>
                                                No companies found. Click "Add Company" to register a new one.
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
    <!-- [ Main Content ] end -->
</div>
@endsection
