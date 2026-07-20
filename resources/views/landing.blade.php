<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $appSettings->application_name }} - Operations Management System</title>
    
    <!-- Favicon -->
    @if($appSettings->favicon)
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset($appSettings->favicon) }}" />
    @else
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}" />
    @endif
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Feather Icons -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}" />
    
    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-blue-hover: #1d4ed8;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .login-wrapper {
            position: relative;
            width: 100vw;
            min-height: 100vh;
            background-image: url('{{ asset('assets/images/airplane_tarmac.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
        }

        .dark-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(8, 18, 41, 0.7);
            z-index: 1;
        }

        .login-container {
            position: relative;
            z-index: 2;
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Left side is open to display the background plane image */
        .left-empty-pane {
            flex: 1;
        }

        /* Right pane holds the centered white card (occupies ~40% space on desktop) */
        .right-pane {
            width: 40%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px 32px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            flex-direction: column;
        }

        /* Branding header inside the card */
        .card-brand-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .wings-logo {
            margin-bottom: 14px;
        }

        .wings-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #0b1736;
            margin: 0 0 4px;
            text-transform: uppercase;
        }

        .wings-subtitle {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            margin: 0;
            letter-spacing: 0.5px;
        }

        .card-divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 24px 0;
            width: 100%;
        }

        .welcome-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .welcome-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        /* Form styling */
        .form-group-custom {
            margin-bottom: 18px;
        }

        .form-label-custom {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #475569;
            margin-bottom: 6px;
        }

        .input-container {
            position: relative;
            width: 100%;
        }

        .form-control-custom {
            width: 100%;
            height: 40px;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background-color: #ffffff;
            color: var(--text-main);
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control-custom:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 1px var(--primary-blue);
        }

        .form-control-custom.password-input {
            padding-right: 40px;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            user-select: none;
        }

        .password-toggle:hover {
            color: var(--text-main);
        }

        /* Checkbox Row */
        .action-row {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .remember-label {
            font-size: 0.85rem;
            color: #475569;
            cursor: pointer;
            user-select: none;
            display: flex;
            align-items: center;
        }

        .remember-checkbox {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            margin-right: 8px;
            cursor: pointer;
            accent-color: var(--primary-blue);
        }

        /* Button */
        .btn-signin {
            background-color: var(--primary-blue);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.9rem;
            width: 100%;
            height: 40px;
            border: none;
            border-radius: 6px;
            transition: background-color 0.2s;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-signin:hover {
            background-color: var(--primary-blue-hover);
        }

        .btn-signin:active {
            transform: scale(0.99);
        }

        /* Error Alert Box */
        .error-alert {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #ef4444;
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .error-alert i {
            font-size: 0.95rem;
        }

        /* Responsive Layout */
        @media (max-width: 991px) {
            .left-empty-pane {
                display: none;
            }
            .right-pane {
                width: 100%;
                padding: 24px;
            }
            .auth-card {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="dark-overlay"></div>
        
        <div class="login-container">
            <!-- Left Side (Empty to showcase background airplane) -->
            <div class="left-empty-pane"></div>
            
            <!-- Right Side (Login Card Panel) -->
            <div class="right-pane">
                <div class="auth-card">
                    
                    <!-- Branding Block inside Card -->
                    <div class="card-brand-block">
                        @if($appSettings->application_logo)
                            <img class="wings-logo" src="{{ asset($appSettings->application_logo) }}" alt="{{ $appSettings->application_name }}" style="max-height: 64px; max-width: 100%; object-fit: contain;">
                        @else
                            <svg class="wings-logo" width="64" height="64" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="wingGrad" x1="20" y1="20" x2="100" y2="100" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="#60a5fa" />
                                        <stop offset="100%" stop-color="#2563eb" />
                                    </linearGradient>
                                </defs>
                                <path d="M25 70 C25 70 52 60 85 30 C74 48 58 58 58 58 L42 55 L25 70 Z" fill="url(#wingGrad)" />
                                <path d="M38 82 C38 82 62 72 95 42 C84 60 70 70 70 70 L55 67 L38 82 Z" fill="url(#wingGrad)" opacity="0.85" />
                            </svg>
                        @endif
                        <h1 class="wings-title">{{ $appSettings->application_name }}</h1>
                        <p class="wings-subtitle">Operations Management System</p>
                    </div>
                    
                    <div class="card-divider"></div>
                    
                    <h2 class="welcome-title">Welcome Back</h2>
                    <p class="welcome-subtitle">Please sign in to continue</p>
                    
                    <!-- Error Alert -->
                    @if(session('auth_error'))
                        <div class="error-alert">
                            <i class="feather-alert-circle"></i>
                            <span>{{ session('auth_error') }}</span>
                        </div>
                    @elseif($errors->any())
                        <div class="error-alert">
                            <i class="feather-alert-circle"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <!-- Form -->
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        
                        <div class="form-group-custom">
                            <label for="login_identifier" class="form-label-custom">Email or Phone Number</label>
                            <div class="input-container">
                                <input type="text" name="login" id="login_identifier" class="form-control-custom" placeholder="name@company.com or +91 98765 43210" value="{{ old('login') }}" required autocomplete="username" autofocus>
                            </div>
                        </div>
                        
                        <div class="form-group-custom">
                            <label for="login_password" class="form-label-custom">Password</label>
                            <div class="input-container">
                                <input type="password" name="password" id="login_password" class="form-control-custom password-input" placeholder="••••••••" required autocomplete="current-password">
                                <span class="password-toggle" id="password_eye_toggle" onclick="togglePassword()">
                                    <i class="feather-eye" id="password_eye_icon"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="action-row">
                            <label class="remember-label">
                                <input type="checkbox" name="remember" class="remember-checkbox" {{ old('remember') ? 'checked' : '' }}>
                                Remember Me
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-signin">
                            Sign In
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    
    <script>
        function togglePassword() {
            var passwordField = document.getElementById('login_password');
            var eyeIcon = document.getElementById('password_eye_icon');
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("feather-eye");
                eyeIcon.classList.add("feather-eye-off");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("feather-eye-off");
                eyeIcon.classList.add("feather-eye");
            }
        }
    </script>

    <!-- Wings Global Flight Aviation Loader -->
    @include('partials.loader')
    <script src="{{ asset('assets/js/wings-loader.js') }}"></script>
</body>
</html>
