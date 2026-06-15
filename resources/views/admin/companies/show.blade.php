@extends('layouts.admin')

@section('title', 'Company Details || ' . ($appSettings->application_name ?? 'SkyTrack'))

@section('content')
<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Company Details</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Companies</a></li>
                <li class="breadcrumb-item">Details</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-inline-flex gap-2">
                <a href="{{ route('companies.index') }}" class="btn btn-light">
                    <i class="feather-arrow-left me-2"></i>Back to List
                </a>
                <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-primary">
                    <i class="feather-edit me-2"></i>Edit Company
                </a>
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
                        @if($company->logo)
                            <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" class="img-fluid rounded mb-4 shadow-sm" style="max-height: 120px; object-fit: contain;">
                        @else
                            <div class="avatar-text avatar-xl bg-soft-primary text-primary rounded mb-4 d-flex align-items-center justify-content-center fs-1" style="width: 100px; height: 100px; background-color: rgba(59, 130, 246, 0.1);">
                                {{ substr($company->company_name, 0, 1) }}
                            </div>
                        @endif
                        <h4 class="fw-bold mb-1 text-dark">{{ $company->company_name }}</h4>
                        <span class="fs-12 text-muted mb-3">Code: <code>{{ $company->company_code ?? 'N/A' }}</code></span>
                        
                        @if($company->status === 'active')
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
                            <div class="col-sm-4 text-muted fw-medium">Contact Person</div>
                            <div class="col-sm-8 text-dark fw-semibold">{{ $company->contact_person ?? 'Not Specified' }}</div>
                        </div>
                        
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-sm-4 text-muted fw-medium">Email Address</div>
                            <div class="col-sm-8">
                                @if($company->email)
                                    <a href="mailto:{{ $company->email }}" class="text-primary fw-semibold"><i class="feather-mail me-1"></i>{{ $company->email }}</a>
                                @else
                                    <span class="text-muted">Not Specified</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-sm-4 text-muted fw-medium">Phone Number</div>
                            <div class="col-sm-8 text-dark fw-semibold">
                                @if($company->phone)
                                    <i class="feather-phone me-1 text-muted"></i>{{ $company->phone }}
                                @else
                                    <span class="text-muted">Not Specified</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-sm-4 text-muted fw-medium">Address</div>
                            <div class="col-sm-8 text-dark" style="white-space: pre-line;">{{ $company->address ?? 'Not Specified' }}</div>
                        </div>

                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-sm-4 text-muted fw-medium">Created At</div>
                            <div class="col-sm-8 text-dark">{{ $company->created_at ? $company->created_at->format('M d, Y h:i A') : 'N/A' }}</div>
                        </div>

                        <div class="row">
                            <div class="col-sm-4 text-muted fw-medium">Last Updated</div>
                            <div class="col-sm-8 text-dark">{{ $company->updated_at ? $company->updated_at->format('M d, Y h:i A') : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
@endsection
