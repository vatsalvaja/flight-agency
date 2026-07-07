@extends('layouts.admin')

@section('title', 'Station Details || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
@php
    $stationData = isset($station) ? $station : null;
@endphp
<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Station Details</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('stations.index') }}">Stations</a></li>
                <li class="breadcrumb-item">Details</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-inline-flex gap-2">
                <a href="{{ route('stations.index') }}" class="btn btn-light">
                    <i class="feather-arrow-left me-2"></i>Back to List
                </a>
                @isset($stationData->id)
                    <a href="{{ route('stations.edit', $stationData->id) }}" class="btn btn-primary">
                        <i class="feather-edit me-2"></i>Edit Station
                    </a>
                @endisset
            </div>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <div class="col-md-4 col-sm-12 mb-4">
                <div class="card stretch stretch-full h-100">
                    <div class="card-body text-center d-flex flex-column align-items-center justify-content-center py-5">
                        <div class="avatar-text avatar-xl bg-soft-primary text-primary rounded mb-4 d-flex align-items-center justify-content-center fs-1" style="width: 100px; height: 100px; background-color: rgba(59, 130, 246, 0.1);">
                            {{ substr($stationData->station_name ?? '?', 0, 1) }}
                        </div>
                        <h4 class="fw-bold mb-1 text-dark">{{ $stationData->station_name ?? 'N/A' }}</h4>
                        <span class="fs-12 text-muted mb-3">Code: <code>{{ $stationData->station_code ?? 'N/A' }}</code></span>
                        
                        @if(($stationData->status ?? null) === 'active')
                            <span class="badge bg-soft-success text-success px-3 py-2 fs-12 fw-semibold">Active</span>
                        @else
                            <span class="badge bg-soft-danger text-danger px-3 py-2 fs-12 fw-semibold">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-8 col-sm-12 mb-4">
                <div class="card stretch stretch-full h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">General Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-sm-4 text-muted fw-medium">City</div>
                            <div class="col-sm-8 text-dark fw-semibold">{{ $stationData->city ?? 'N/A' }}</div>
                        </div>
                        
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-sm-4 text-muted fw-medium">State / Region</div>
                            <div class="col-sm-8 text-dark fw-semibold">{{ $stationData->state ?? 'N/A' }}</div>
                        </div>

                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-sm-4 text-muted fw-medium">Country</div>
                            <div class="col-sm-8 text-dark fw-semibold">{{ $stationData->country ?? 'N/A' }}</div>
                        </div>

                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-sm-4 text-muted fw-medium">Contact Number</div>
                            <div class="col-sm-8 text-dark fw-semibold">
                                @if(isset($stationData->contact_number) && $stationData->contact_number)
                                    <i class="feather-phone me-1 text-muted"></i>{{ $stationData->contact_number }}
                                @else
                                    <span class="text-muted">Not Specified</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-sm-4 text-muted fw-medium">Email Address</div>
                            <div class="col-sm-8">
                                @if(isset($stationData->email) && $stationData->email)
                                    <a href="mailto:{{ $stationData->email }}" class="text-primary fw-semibold"><i class="feather-mail me-1"></i>{{ $stationData->email }}</a>
                                @else
                                    <span class="text-muted">Not Specified</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-sm-4 text-muted fw-medium">Address</div>
                            <div class="col-sm-8 text-dark" style="white-space: pre-line;">{{ $stationData->address ?? 'Not Specified' }}</div>
                        </div>

                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-sm-4 text-muted fw-medium">Created At</div>
                            <div class="col-sm-8 text-dark">{{ isset($stationData->created_at) && $stationData->created_at ? $stationData->created_at->format('M d, Y h:i A') : 'N/A' }}</div>
                        </div>

                        <div class="row">
                            <div class="col-sm-4 text-muted fw-medium">Last Updated</div>
                            <div class="col-sm-8 text-dark">{{ isset($stationData->updated_at) && $stationData->updated_at ? $stationData->updated_at->format('M d, Y h:i A') : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
@endsection
