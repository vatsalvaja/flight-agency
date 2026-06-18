@extends('layouts.admin')

@section('title', 'Assignment Details || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content assign-luggage-show">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Assign Luggage</h5>
            </div>
            <ul class="breadcrumb d-none d-md-flex">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assign-luggage.index') }}">Assign Luggage</a></li>
                <li class="breadcrumb-item">Assignment Details</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-inline-flex gap-2">
                <a href="{{ route('assign-luggage.index') }}" class="btn btn-light">
                    <i class="feather-arrow-left me-2"></i>Back to List
                </a>
                <a href="{{ route('assign-luggage.edit', $assignment->id) }}" class="btn btn-primary">
                    <i class="feather-edit me-2"></i>Edit Assignment
                </a>
            </div>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <!-- Details Card -->
            <div class="col-lg-8 mb-4">
                <div class="card stretch stretch-full h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Luggage Assignment Info</h5>
                        @if($assignment->status === 'Pickup')
                            <span class="badge bg-soft-warning text-warning px-3 py-2 fs-12 fw-semibold">Pickup</span>
                        @elseif($assignment->status === 'In Progress')
                            <span class="badge bg-soft-info text-info px-3 py-2 fs-12 fw-semibold">In Progress</span>
                        @else
                            <span class="badge bg-soft-success text-success px-3 py-2 fs-12 fw-semibold">Delivered</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3" style="width: 30%;">Flight Company</td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                @if($assignment->company->logo)
                                                    <img src="{{ asset('storage/' . $assignment->company->logo) }}" alt="logo" class="rounded me-2" style="height: 32px; width: 32px; object-fit: cover;">
                                                @else
                                                    <div class="avatar-text avatar-sm bg-soft-primary text-primary rounded me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 700; font-size: 13px;">
                                                        {{ substr($assignment->company->company_name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <span class="fw-bold text-dark">{{ $assignment->company->company_name }} ({{ $assignment->company->company_code }})</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Station</td>
                                        <td class="py-3 fw-medium text-dark">{{ $assignment->station->station_name }} ({{ $assignment->station->station_code }})</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Pickup Location</td>
                                        <td class="py-3">
                                            <div class="text-dark fw-medium">{{ $assignment->pickup_location }}</div>
                                            @if($assignment->pickup_latitude && $assignment->pickup_longitude)
                                                <span class="fs-11 text-muted">Coords: {{ $assignment->pickup_latitude }}, {{ $assignment->pickup_longitude }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Drop Location</td>
                                        <td class="py-3">
                                            <div class="text-dark fw-medium">{{ $assignment->drop_location }}</div>
                                            @if($assignment->drop_latitude && $assignment->drop_longitude)
                                                <span class="fs-11 text-muted">Coords: {{ $assignment->drop_latitude }}, {{ $assignment->drop_longitude }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Distance (Driving)</td>
                                        <td class="py-3"><span class="badge bg-soft-secondary text-dark fs-12 px-3 py-1 fw-bold">{{ $assignment->distance_km ?? '0.00' }} km</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Expected Delivery Date</td>
                                        <td class="py-3 fw-bold text-dark">{{ $assignment->expected_delivery_date->format('l, F d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Assigned Driver</td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                @if($assignment->driver->profile_photo)
                                                    <img src="{{ asset('storage/' . $assignment->driver->profile_photo) }}" alt="avatar" class="rounded-circle me-2" style="height: 32px; width: 32px; object-fit: cover;">
                                                @else
                                                    <div class="avatar-text avatar-sm bg-soft-info text-info rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 700; font-size: 13px;">
                                                        {{ $assignment->driver->getInitials() }}
                                                    </div>
                                                @endif
                                                <span class="fw-semibold text-dark">{{ $assignment->driver->name }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted py-3">Assigned By</td>
                                        <td class="py-3 text-muted">
                                            {{ $assignment->creator->name ?? 'N/A' }} on {{ $assignment->created_at->format('M d, Y H:i A') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Card: Notes & Images -->
            <div class="col-lg-4 mb-4">
                <div class="row g-4 h-100">
                    <!-- Notes card -->
                    <div class="col-12">
                        <div class="card stretch stretch-full" style="min-height: 180px;">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Custom Notes</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-dark bg-light p-3 rounded border" style="white-space: pre-wrap; font-size: 0.85rem;">{{ $assignment->notes ?? 'No custom notes provided.' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Images card -->
                    <div class="col-12 flex-grow-1">
                        <div class="card stretch stretch-full h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Uploaded Images ({{ count($assignment->images ?? []) }})</h5>
                            </div>
                            <div class="card-body">
                                @if($assignment->images && count($assignment->images) > 0)
                                    <div class="row g-2">
                                        @foreach($assignment->images as $img)
                                            <div class="col-4">
                                                <a href="{{ asset('storage/' . $img) }}" target="_blank" class="d-block border rounded p-1 hover-image" style="height: 90px; overflow: hidden; background: #fff;">
                                                    <img src="{{ asset('storage/' . $img) }}" class="w-100 h-100 rounded" style="object-fit: cover; transition: transform 0.2s ease;">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5 text-muted">
                                        <i class="feather-image fs-1 d-block mb-2 text-muted"></i>
                                        No images uploaded.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>

<style>
.hover-image img:hover {
    transform: scale(1.05);
}
</style>
@endsection
