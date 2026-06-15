@extends('layouts.admin')

@section('title', 'Locations || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Locations</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item">Locations</li>
            </ul>
        </div>
    </div>
    
    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body text-center py-5">
                        <i class="feather-navigation fs-1 text-warning mb-4 d-block"></i>
                        <h2 class="fw-bold mb-2 text-dark">Location Module</h2>
                        <p class="fs-14 text-muted mb-4">Location module is under planning.</p>
                        <span class="badge bg-soft-warning text-warning px-3 py-2 fs-12 fw-semibold">Planned Feature</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
