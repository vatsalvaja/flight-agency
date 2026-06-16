@extends('layouts.admin')

@section('title', 'Stations || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
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
                        <form action="{{ route('stations.index') }}" method="GET" class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <span class="input-group-text bg-light border-end-0"><i class="feather-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search name, code, city..." value="{{ $search ?? '' }}">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Search</button>
                            @if($search)
                                <a href="{{ route('stations.index') }}" class="btn btn-sm btn-secondary">Clear</a>
                            @endif
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
                                <tbody>
                                    @forelse($stations as $station)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-text avatar-sm bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; font-weight: 700;">
                                                        {{ substr($station->station_name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold text-dark d-block">{{ $station->station_name }}</span>
                                                        <span class="fs-11 text-muted">{{ $station->address }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><code>{{ $station->station_code }}</code></td>
                                            <td>
                                                <span class="d-block text-dark">{{ $station->city }}, {{ $station->state }}</span>
                                                <span class="fs-11 text-muted">{{ $station->country }}</span>
                                            </td>
                                            <td>{{ $station->contact_number ?? 'N/A' }}</td>
                                            <td>{{ $station->email ?? 'N/A' }}</td>
                                            <td>
                                                @if($station->status === 'active')
                                                    <span class="badge bg-soft-success text-success px-2 py-1">Active</span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger px-2 py-1">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-inline-flex gap-2">
                                                    <a href="{{ route('stations.show', $station->id) }}" class="btn btn-sm btn-light-brand" title="View Details">
                                                        <i class="feather-eye"></i>
                                                    </a>
                                                    <a href="{{ route('stations.edit', $station->id) }}" class="btn btn-sm btn-light-brand" title="Edit Station">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <form action="{{ route('stations.destroy', $station->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this station?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-light-danger" title="Delete Station">
                                                            <i class="feather-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="feather-alert-circle fs-3 d-block mb-2"></i>
                                                No stations found. Click "Add Station" to register a new one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($stations->hasPages())
                        <div class="card-footer d-flex justify-content-end py-3">
                            {{ $stations->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
@endsection
