@extends('layouts.admin')

@section('title', 'Assign Luggage || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Assign Luggage</h5>
            </div>
            <ul class="breadcrumb">
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
                        <form action="{{ route('assign-luggage.index') }}" method="GET" class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <span class="input-group-text bg-light border-end-0"><i class="feather-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search locations, status..." value="{{ $search ?? '' }}">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Search</button>
                            @if($search)
                                <a href="{{ route('assign-luggage.index') }}" class="btn btn-sm btn-secondary">Clear</a>
                            @endif
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
                                <tbody>
                                    @forelse($assignments as $assignment)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    @if($assignment->company->logo)
                                                        <img src="{{ asset('storage/' . $assignment->company->logo) }}" alt="logo" class="rounded me-2" style="height: 28px; width: 28px; object-fit: cover;">
                                                    @else
                                                        <div class="avatar-text avatar-sm bg-soft-primary text-primary rounded me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-weight: 700; font-size: 11px;">
                                                            {{ substr($assignment->company->company_name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <span class="fw-semibold text-dark">{{ $assignment->company->company_name }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $assignment->station->station_name }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($assignment->driver->profile_photo)
                                                        <img src="{{ asset('storage/' . $assignment->driver->profile_photo) }}" alt="avatar" class="rounded-circle me-2" style="height: 28px; width: 28px; object-fit: cover;">
                                                    @else
                                                        <div class="avatar-text avatar-sm bg-soft-info text-info rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-weight: 700; font-size: 11px;">
                                                            {{ $assignment->driver->getInitials() }}
                                                        </div>
                                                    @endif
                                                    <span>{{ $assignment->driver->name }}</span>
                                                </div>
                                            </td>
                                            <td title="{{ $assignment->pickup_location }}">{{ \Illuminate\Support\Str::limit($assignment->pickup_location, 25) }}</td>
                                            <td title="{{ $assignment->drop_location }}">{{ \Illuminate\Support\Str::limit($assignment->drop_location, 25) }}</td>
                                            <td><code>{{ $assignment->distance_km ?? '0.00' }} km</code></td>
                                            <td>{{ $assignment->expected_delivery_date->format('M d, Y') }}</td>
                                            <td>
                                                @if($assignment->status === 'Pickup')
                                                    <span class="badge bg-soft-warning text-warning px-2 py-1">Pickup</span>
                                                @elseif($assignment->status === 'In Progress')
                                                    <span class="badge bg-soft-info text-info px-2 py-1">In Progress</span>
                                                @else
                                                    <span class="badge bg-soft-success text-success px-2 py-1">Delivered</span>
                                                @endif
                                            </td>
                                            <td>{{ $assignment->created_at->format('M d, Y') }}</td>
                                            @if(isset($loggedUser) && $loggedUser->role_id === 0)
                                                <td>
                                                    <span class="badge bg-soft-info text-info px-2 py-1 fw-semibold">{{ $assignment->creator->name ?? 'System' }}</span>
                                                </td>
                                            @endif
                                            <td class="text-end pe-4">
                                                <div class="d-inline-flex gap-2">
                                                    <a href="{{ route('assign-luggage.show', $assignment->id) }}" class="btn btn-sm btn-light-brand" title="View Details">
                                                        <i class="feather-eye"></i>
                                                    </a>
                                                    <a href="{{ route('assign-luggage.edit', $assignment->id) }}" class="btn btn-sm btn-light-brand" title="Edit Assignment">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <form action="{{ route('assign-luggage.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this luggage assignment?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-light-danger" title="Delete Assignment">
                                                            <i class="feather-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ (isset($loggedUser) && $loggedUser->role_id === 0) ? 11 : 10 }}" class="text-center py-5 text-muted">
                                                <i class="feather-alert-circle fs-3 d-block mb-2"></i>
                                                No luggage assignments found. Click "Assign Luggage" to create a new one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($assignments->hasPages())
                        <div class="card-footer d-flex justify-content-end py-3">
                            {{ $assignments->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
@endsection
