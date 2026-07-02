@extends('layouts.admin')

@section('title', 'SMTP Settings || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">SMTP Settings</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('settings.edit') }}">Settings</a></li>
                <li class="breadcrumb-item">SMTP Settings</li>
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
                        <h5 class="card-title mb-0">SMTP Mail Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.smtp.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- SMTP Host -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="mail_host">SMTP Host <span class="text-danger">*</span></label>
                                    <input type="text" name="mail_host" id="mail_host" class="form-control @error('mail_host') is-invalid @enderror" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" placeholder="e.g. smtp.gmail.com" required>
                                    @error('mail_host')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- SMTP Port -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="mail_port">SMTP Port <span class="text-danger">*</span></label>
                                    <input type="number" name="mail_port" id="mail_port" class="form-control @error('mail_port') is-invalid @enderror" value="{{ old('mail_port', $settings['mail_port'] ?? '') }}" placeholder="e.g. 587 or 465" required>
                                    @error('mail_port')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <!-- SMTP Username -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="mail_username">SMTP Username <span class="text-danger">*</span></label>
                                    <input type="text" name="mail_username" id="mail_username" class="form-control @error('mail_username') is-invalid @enderror" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" placeholder="e.g. admin@example.com" required>
                                    @error('mail_username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- SMTP Password -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="mail_password">SMTP Password <span class="text-danger">*</span></label>
                                    <input type="password" name="mail_password" id="mail_password" class="form-control @error('mail_password') is-invalid @enderror" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" placeholder="Enter SMTP password" required>
                                    @error('mail_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>



                            <div class="row">
                                <!-- Email Encryption -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="mail_encryption">Email Encryption</label>
                                    <select name="mail_encryption" id="mail_encryption" class="form-control @error('mail_encryption') is-invalid @enderror">
                                        <option value="">None</option>
                                        <option value="tls" {{ old('mail_encryption', $settings['mail_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    </select>
                                    @error('mail_encryption')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email Charset -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="mail_charset">Email Charset</label>
                                    <input type="text" name="mail_charset" id="mail_charset" class="form-control @error('mail_charset') is-invalid @enderror" value="{{ old('mail_charset', $settings['mail_charset'] ?? 'UTF-8') }}" placeholder="e.g. UTF-8">
                                    @error('mail_charset')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="submit" class="btn btn-primary px-4"><i class="feather-save me-2"></i>Save SMTP Settings</button>
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
