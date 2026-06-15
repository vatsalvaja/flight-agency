@extends('layouts.admin')

@section('content')
        <div class="nxl-content">
            <!-- [ page-header ] start -->
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Dashboard</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                        <li class="breadcrumb-item">Dashboard</li>
                    </ul>
                </div>
            </div>
            <!-- [ page-header ] end -->
            <div class="main-content">
                <div class="row">
                    <!-- Total Companies Card -->
                    <div class="col-md-4 col-sm-12 mb-4">
                        <div class="card stretch stretch-full h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fs-12 text-muted text-uppercase d-block mb-1">Total Companies</span>
                                    <h2 class="fw-bold mb-0 text-dark">{{ $companiesCount }}</h2>
                                </div>
                                <div class="avatar-text avatar-md bg-soft-primary text-primary rounded p-2 fs-4" style="background-color: rgba(59, 130, 246, 0.1);">
                                    <i class="feather-briefcase"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Stations Card -->
                    <div class="col-md-4 col-sm-12 mb-4">
                        <div class="card stretch stretch-full h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fs-12 text-muted text-uppercase d-block mb-1">Total Stations</span>
                                    <h2 class="fw-bold mb-0 text-dark">0</h2>
                                    <span class="fs-11 text-warning d-block mt-1"><i class="feather-alert-circle me-1"></i>Under planning</span>
                                </div>
                                <div class="avatar-text avatar-md bg-soft-warning text-warning rounded p-2 fs-4" style="background-color: rgba(245, 158, 11, 0.1);">
                                    <i class="feather-map-pin"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Locations Card -->
                    <div class="col-md-4 col-sm-12 mb-4">
                        <div class="card stretch stretch-full h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fs-12 text-muted text-uppercase d-block mb-1">Total Locations</span>
                                    <h2 class="fw-bold mb-0 text-dark">0</h2>
                                    <span class="fs-11 text-warning d-block mt-1"><i class="feather-alert-circle me-1"></i>Under planning</span>
                                </div>
                                <div class="avatar-text avatar-md bg-soft-warning text-warning rounded p-2 fs-4" style="background-color: rgba(245, 158, 11, 0.1);">
                                    <i class="feather-navigation"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card stretch stretch-full">
                            <div class="card-body text-center py-5">
                                <i class="feather-airplay fs-1 text-primary mb-4 d-block"></i>
                                <h2 class="fw-bold mb-2 text-dark">Welcome to SkyTrack</h2>
                                <p class="fs-14 text-muted mb-4">Project setup completed successfully.</p>
                                <span class="badge bg-soft-success text-success px-3 py-2 fs-12 fw-semibold">This admin panel is ready for future modules</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] end -->
        </div>
@endsection
