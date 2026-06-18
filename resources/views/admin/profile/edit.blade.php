@extends('layouts.admin')

@section('title', 'Profile Details || ' . ($appSettings->application_name ?? 'Wings'))

@section('content')
<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Profile Details</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Home</a></li>
                <li class="breadcrumb-item">Profile Details</li>
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
                        <h5 class="card-title mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="email">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="phone">Mobile Number</label>
                                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold" for="designation">Designation</label>
                                    <input type="text" name="designation" id="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $user->designation) }}" placeholder="e.g. System Administrator">
                                    @error('designation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold" for="address">Address</label>
                                    <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3" placeholder="Enter your full physical address">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold" for="profile_photo">Profile Photo</label>
                                    <input type="file" name="profile_photo" id="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" onchange="previewImage(event)">
                                    <div class="form-text">Allowed types: jpeg, png, jpg, gif, svg, webp. Max: 2MB.</div>
                                    @error('profile_photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    <div class="mt-3 p-2 border rounded bg-light d-inline-block text-center" style="min-width: 150px;">
                                        <div class="fs-12 text-muted mb-2">Photo Preview:</div>
                                        <div id="imagePreviewContainer" class="d-flex justify-content-center align-items-center">
                                            @if($user->profile_photo)
                                                <img id="imagePreview" src="{{ asset($user->profile_photo) }}" alt="Profile Photo" class="rounded-circle shadow-sm" style="height: 100px; width: 100px; object-fit: cover;">
                                            @else
                                                <div id="initialsAvatar" class="avatar-text bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px; font-weight: 700; font-size: 32px;">
                                                    {{ $user->getInitials() }}
                                                </div>
                                                <img id="imagePreview" src="" alt="Profile Photo" class="rounded-circle shadow-sm d-none" style="height: 100px; width: 100px; object-fit: cover;">
                                            @endif
                                        </div>
                                    </div>
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

@push('scripts')
<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('imagePreview');
        var initials = document.getElementById('initialsAvatar');
        
        output.src = reader.result;
        output.classList.remove('d-none');
        
        if (initials) {
            initials.classList.add('d-none');
        }
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endpush
@endsection
