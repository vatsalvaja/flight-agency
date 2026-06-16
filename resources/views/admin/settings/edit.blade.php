@extends('layouts.admin')

@section('title', 'Application Settings || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Settings</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
            </ul>
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

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="feather-alert-octagon me-2"></i>
                        <strong>Error!</strong> Please check the fields below.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title mb-0">System Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-4">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold" for="application_name">System Name <span class="text-danger">*</span></label>
                                    <input type="text" name="application_name" id="application_name" class="form-control @error('application_name') is-invalid @enderror" value="{{ old('application_name', $setting->application_name) }}" required>
                                    @error('application_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="application_logo">Application Logo</label>
                                    <input type="file" name="application_logo" id="application_logo" class="form-control @error('application_logo') is-invalid @enderror">
                                    <div class="form-text">Recommended size: 200x50 px. Allowed types: jpeg, png, jpg, gif, svg, webp. Max: 2MB.</div>
                                    @error('application_logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    @if($setting->application_logo)
                                        <div class="mt-3 p-2 border rounded text-center bg-light" style="max-width: 250px;">
                                            <div class="fs-12 text-muted mb-2">Current Logo:</div>
                                            <img src="{{ asset('storage/' . $setting->application_logo) }}" alt="Logo Preview" class="img-fluid rounded" style="max-height: 50px;">
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="favicon">Favicon</label>
                                    <input type="file" name="favicon" id="favicon" class="form-control @error('favicon') is-invalid @enderror">
                                    <div class="form-text">Recommended size: 32x32 px. Allowed types: jpeg, png, jpg, gif, svg, webp, ico. Max: 1MB.</div>
                                    @error('favicon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    @if($setting->favicon)
                                        <div class="mt-3 p-2 border rounded text-center bg-light" style="max-width: 150px;">
                                            <div class="fs-12 text-muted mb-2">Current Favicon:</div>
                                            <img src="{{ asset('storage/' . $setting->favicon) }}" alt="Favicon Preview" class="img-fluid rounded" style="max-height: 32px; width: 32px;">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="submit" class="btn btn-primary px-4"><i class="feather-save me-2"></i>Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
@endsection
