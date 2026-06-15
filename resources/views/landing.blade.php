<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Wings - Aviation Administrative Operations</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Feather Icons -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(15, 23, 42) 0%, rgb(9, 14, 28) 90.1%);
            color: #cbd5e1;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            color: #f8fafc;
        }

        /* Glassmorphism Navbar */
        .landing-navbar {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            background: rgba(15, 23, 42, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        .brand-text {
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Hero Section */
        .hero-section {
            padding: 160px 0 100px;
            position: relative;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
            background: linear-gradient(to right, #ffffff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-badge {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 8px 16px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .hero-subtitle {
            font-size: 1.15rem;
            line-height: 1.7;
            color: #94a3b8;
            max-width: 550px;
            margin-bottom: 2.5rem;
        }

        /* Glass Cards */
        .glass-card {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            backdrop-filter: blur(8px);
            padding: 2.5rem;
            height: 100%;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            border-color: rgba(59, 130, 246, 0.25);
        }

        .card-icon {
            font-size: 2rem;
            color: #3b82f6;
            margin-bottom: 1.5rem;
            background: rgba(59, 130, 246, 0.1);
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        /* Radar Grid CSS Background Effect */
        .bg-radar {
            position: absolute;
            top: 10%;
            right: -10%;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            border: 1px dashed rgba(59, 130, 246, 0.1);
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: rotateRadar 60s linear infinite;
        }

        .bg-radar::before {
            content: '';
            position: absolute;
            width: 80%;
            height: 80%;
            border-radius: 50%;
            border: 1px solid rgba(59, 130, 246, 0.05);
        }

        .bg-radar::after {
            content: '';
            position: absolute;
            width: 50%;
            height: 50%;
            border-radius: 50%;
            border: 1px dashed rgba(59, 130, 246, 0.08);
        }

        @keyframes rotateRadar {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Buttons styling */
        .btn-premium {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
            transition: all 0.2s ease-in-out;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            color: #ffffff;
        }

        .btn-outline-premium {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #f8fafc;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
        }

        .btn-outline-premium:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.3);
            color: #ffffff;
        }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg landing-navbar fixed-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <span class="brand-text">Wings</span>
            </a>
            
            <div class="ms-auto d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-premium px-3 py-2" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchTab('signin')">
                    Log In
                </button>
                <button class="btn btn-sm btn-premium px-3 py-2" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchTab('signup')">
                    Register
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="bg-radar"></div>
            
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-badge">
                        <i class="feather-activity me-2"></i>Aviation Operations Portal
                    </div>
                    <h1 class="hero-title">
                        Flight Logistics & master Control panel
                    </h1>
                    <p class="hero-subtitle">
                        Streamlining station configurations, master routes, country listings, and airline directory profiles in a secure, high-contrast system hub.
                    </p>
                    <div class="d-flex gap-3">
                        <button class="btn btn-premium px-4 py-3" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchTab('signin')">
                            <i class="feather-log-in me-2"></i>Access Admin Center
                        </button>
                        <button class="btn btn-outline-premium px-4 py-3" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchTab('signup')">
                            <i class="feather-user-plus me-2"></i>Sign Up
                        </button>
                    </div>
                </div>
                
                <div class="col-lg-5 offset-lg-1 mt-5 mt-lg-0">
                    <div class="glass-card">
                        <div class="card-icon">
                            <i class="feather-shield"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Secure Gateway</h4>
                        <p class="text-muted fs-14 mb-4">
                            All administrative features, aircraft directory lists, company profiles, and reports are fully protected with session encryption layers.
                        </p>
                        
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center bg-soft-success rounded-circle" style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.1);">
                                <i class="feather-check text-success"></i>
                            </div>
                            <span class="fs-13 text-secondary">Aviation master templates integration</span>
                        </div>
                        <div class="d-flex align-items-center gap-3 mt-3">
                            <div class="d-flex align-items-center justify-content-center bg-soft-success rounded-circle" style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.1);">
                                <i class="feather-check text-success"></i>
                            </div>
                            <span class="fs-13 text-secondary">Real-time status configurations</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Include Modal Partial -->
    @include('partials.auth-modal')

    <!-- Footer script and libraries -->
    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    
    <script>
        function switchTab(tabId) {
            // Find tabs trigger element
            var triggerEl = document.querySelector('#authTabs button[id="' + tabId + '-tab"]');
            if (triggerEl) {
                var tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }
        
        // Auto open modal on authentication errors or redirects
        @if(session('auth_error') || session('success') || $errors->any())
            document.addEventListener("DOMContentLoaded", function() {
                var myModal = new bootstrap.Modal(document.getElementById('authModal'));
                myModal.show();
                
                // If it's a validation error or login failure, activate Sign In tab
                @if(session('auth_error') || $errors->has('email') || $errors->has('password') || $errors->has('login_error'))
                    switchTab('signin');
                @elseif(session('success'))
                    // Keep the signup/signin modal tab as active depending on success context
                    switchTab('signin');
                @endif
            });
        @endif
    </script>
</body>
</html>
