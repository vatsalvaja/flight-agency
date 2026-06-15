@extends('layouts.admin')

@section('title', 'Assignable Orders || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Assignable Orders</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item">Assignable Orders</li>
            </ul>
        </div>
    </div>
    
    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body text-center py-5">
                        <i class="feather-truck fs-1 text-success mb-4 d-block"></i>
                        <h2 class="fw-bold mb-2 text-dark">Assignable Orders</h2>
                        <p class="fs-14 text-muted mb-4">Assignable Orders module is under planning.</p>
                        <span class="badge bg-soft-success text-success px-3 py-2 fs-12 fw-semibold">Driver Access Module</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
