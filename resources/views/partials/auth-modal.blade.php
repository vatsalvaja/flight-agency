<!-- Modal -->
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
            <!-- Modal Header -->
            <div class="modal-header border-0 bg-light p-4 pb-0 d-flex flex-column align-items-center">
                <div class="text-center mb-3">
                    <span class="fs-4 fw-bolder text-primary tracking-tight" style="font-family: 'Inter', sans-serif;">Wings</span>
                    <div class="fs-12 text-muted">Administrative Portal Access</div>
                </div>
                
                <!-- Nav tabs -->
                <ul class="nav nav-pills nav-fill w-100 bg-soft-secondary rounded p-1 mb-2" id="authTabs" role="tablist" style="background-color: #f1f5f9;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold py-2 rounded-3" id="signin-tab" data-bs-toggle="tab" data-bs-target="#signin" type="button" role="tab" aria-controls="signin" aria-selected="true">Sign In</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold py-2 rounded-3" id="signup-tab" data-bs-toggle="tab" data-bs-target="#signup" type="button" role="tab" aria-controls="signup" aria-selected="false">Sign Up</button>
                    </li>
                </ul>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-4 pt-3">
                <!-- Session Alerts -->
                @if(session('auth_error'))
                    <div class="alert alert-danger py-2 px-3 fs-13 mb-3 border-0 d-flex align-items-center rounded-3" role="alert">
                        <i class="feather-alert-circle me-2"></i>
                        {{ session('auth_error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success py-2 px-3 fs-13 mb-3 border-0 d-flex align-items-center rounded-3" role="alert">
                        <i class="feather-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif
                @if($errors->any() && !session('auth_error'))
                    <div class="alert alert-danger py-2 px-3 fs-13 mb-3 border-0 rounded-3" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Tab Panes -->
                <div class="tab-content mt-2" id="authTabsContent">
                    <!-- Sign In Pane -->
                    <div class="tab-pane fade show active" id="signin" role="tabpanel" aria-labelledby="signin-tab">
                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="login_email" class="form-label fw-semibold fs-13 text-secondary">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="feather-mail"></i></span>
                                    <input type="email" name="email" id="login_email" class="form-control border-start-0" placeholder="admin@example.com" value="{{ old('email') }}" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="login_password" class="form-label fw-semibold fs-13 text-secondary">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="feather-lock"></i></span>
                                    <input type="password" name="password" id="login_password" class="form-control border-start-0" placeholder="••••••••" required>
                                </div>
                                <div class="form-text fs-11 text-muted mt-2">
                                    Demo Credentials: <code>admin@example.com</code> / <code>admin123</code>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 mb-2">
                                <i class="feather-log-in me-2"></i>Access Console
                            </button>
                        </form>
                    </div>
                    
                    <!-- Sign Up Pane -->
                    <div class="tab-pane fade" id="signup" role="tabpanel" aria-labelledby="signup-tab">
                        <form action="{{ route('register') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="reg_name" class="form-label fw-semibold fs-13 text-secondary">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="feather-user"></i></span>
                                    <input type="text" name="name" id="reg_name" class="form-control border-start-0" placeholder="e.g. John Doe" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="reg_email" class="form-label fw-semibold fs-13 text-secondary">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="feather-mail"></i></span>
                                    <input type="email" name="email" id="reg_email" class="form-control border-start-0" placeholder="name@domain.com" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="reg_password" class="form-label fw-semibold fs-13 text-secondary">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="feather-lock"></i></span>
                                    <input type="password" name="password" id="reg_password" class="form-control border-start-0" placeholder="Min. 6 characters" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="reg_password_conf" class="form-label fw-semibold fs-13 text-secondary">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="feather-shield"></i></span>
                                    <input type="password" name="password_confirmation" id="reg_password_conf" class="form-control border-start-0" placeholder="Repeat password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3">
                                <i class="feather-user-plus me-2"></i>Create Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
