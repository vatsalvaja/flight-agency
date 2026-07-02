<!DOCTYPE html>
<html lang="zxx">
        
<!-- Mirrored from bestwpware.com/html/tf/duralux-php/index.php by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 24 Oct 2025 07:03:01 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->
<head>
    <script>
        (function () {
            var theme = localStorage.getItem('app-skin-dark');
            if (theme === 'app-skin-dark') {
                document.documentElement.classList.add('app-skin-dark');
            }
        })();
    </script>
    <style>
        html.app-skin-dark .dark-button {
            display: none !important;
        }
        html.app-skin-dark .light-button {
            display: inline-flex !important;
        }
    </style>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="maryinparis" />
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>@yield('title', isset($appSettings->application_name) ? $appSettings->application_name . ' || Dashboard' : ' || Dashboard')</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    @if(isset($appSettings->favicon) && !empty($appSettings->favicon))
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset($appSettings->favicon) }}" />
    @else
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}" />
    @endif
    <!--! END: Favicon-->
    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <!--! END: Bootstrap CSS-->
    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/daterangepicker.min.css') }}" />
	
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/jquery-jvectormap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/select2-theme.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/jquery.time-to.min.css') }}">	
	
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/tagify.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/tagify-data.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/quill.min.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/vendors/css/tui-calendar.min.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/vendors/css/tui-theme.min.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/vendors/css/tui-time-picker.min.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/vendors/css/tui-date-picker.min.css') }}">
	<link type="text/css" rel="stylesheet" href="{{ asset('assets/vendors/css/emojionearea.min.css') }}">	
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/jquery.time-to.min.css') }}">
	
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/dataTables.bs5.min.css') }}">	
    <!--! END: Vendors CSS-->
    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/theme.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/dual-view-listing.css') }}" />
    <!--! END: Custom CSS-->
    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
			<script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
    {{-- Page-specific head injections (e.g. map/charting CSS) --}}
    @stack('head')
    </head>
<body>
		<!-- Left sidebar -->
            <!--! ================================================================ !-->
    <!--! [Start] Navigation Manu !-->
    <!--! ================================================================ !-->
    <nav class="nxl-navigation">
        <div class="navbar-wrapper">
            <div class="m-header">
                <a href="{{ url('/admin') }}" class="b-brand">
                    @if(isset($appSettings->application_logo) && !empty($appSettings->application_logo))
                        <img src="{{ asset($appSettings->application_logo) }}" alt="{{ $appSettings->application_name }}" class="logo logo-lg" style="max-height: 42px; max-width: 145px; object-fit: contain; -webkit-text-fill-color: initial; background: transparent;">
                    @else
                        <span class="logo logo-lg fw-bolder fs-3 text-dark">{{ isset($appSettings->application_name) ? $appSettings->application_name : 'Dashboard' }}</span>
                    @endif

                    @if(isset($appSettings->favicon) && !empty($appSettings->favicon))
                        <img src="{{ asset($appSettings->favicon) }}" alt="{{ $appSettings->application_name }}" class="logo logo-sm" style="max-height: 30px; max-width: 30px; object-fit: contain; -webkit-text-fill-color: initial; background: transparent;">
                    @else
                        <span class="logo logo-sm fw-bolder fs-4 text-dark">{{ isset($appSettings->application_name) ? substr($appSettings->application_name, 0, 1) : 'D' }}</span>
                    @endif
                </a>
            </div>
            <div class="navbar-content">
                <ul class="nxl-navbar">
                    <li class="nxl-item nxl-caption">
                        <label>Navigation</label>
                    </li>
                    
                    @php
                        $isAdmin = isset($loggedUser) && $loggedUser->role_id === 0;
                        $isManager = false;
                        $isDriver = false;
                        if (isset($loggedUser) && $loggedUser->role_id > 0 && $loggedUser->role) {
                            $isManager = (stripos($loggedUser->role->role_name, 'manager') !== false);
                            $isDriver = (stripos($loggedUser->role->role_name, 'driver') !== false);
                        }
                    @endphp

                    @if($isAdmin || $isManager || $isDriver)
                    <li class="nxl-item {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-airplay"></i></span>
                            <span class="nxl-mtext">Dashboard</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin)
                    <li class="nxl-item {{ Request::routeIs('companies.*') ? 'active' : '' }}">
                        <a href="{{ route('companies.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-briefcase"></i></span>
                            <span class="nxl-mtext">Flight Companies</span>
                        </a>
                    </li>
                    <li class="nxl-item {{ Request::routeIs('stations.*') ? 'active' : '' }}">
                        <a href="{{ route('stations.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-map-pin"></i></span>
                            <span class="nxl-mtext">Stations</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin || $isManager)
                    <li class="nxl-item {{ Request::routeIs('assign-luggage.*') ? 'active' : '' }}">
                        <a href="{{ route('assign-luggage.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-package"></i></span>
                            <span class="nxl-mtext">Assign Luggage</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin || $isManager)
                    <li class="nxl-item {{ Request::routeIs('driver-activities.*') ? 'active' : '' }}">
                        <a href="{{ route('driver-activities.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-activity"></i></span>
                            <span class="nxl-mtext">Driver Activities</span>
                        </a>
                    </li>
                    @endif

                    @if($isDriver)
                    <li class="nxl-item {{ Request::routeIs('assignable-orders.*') ? 'active' : '' }}">
                        <a href="{{ route('assignable-orders.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-truck"></i></span>
                            <span class="nxl-mtext">Assignable Orders</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin)
                    <li class="nxl-item {{ Request::routeIs('roles.*') ? 'active' : '' }}">
                        <a href="{{ route('roles.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-shield"></i></span>
                            <span class="nxl-mtext">Role Management</span>
                        </a>
                    </li>
                    <li class="nxl-item {{ Request::routeIs('users.*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-users"></i></span>
                            <span class="nxl-mtext">User Management</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin || $isManager || $isDriver)
                    <li class="nxl-item {{ Request::routeIs('reports.*') ? 'active' : '' }}">
                        <a href="{{ route('reports.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-bar-chart-2"></i></span>
                            <span class="nxl-mtext">Reports</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin)
                    <li class="nxl-item nxl-hasmenu {{ Request::routeIs('settings.*') ? 'active' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-settings"></i></span>
                            <span class="nxl-mtext">Settings</span>
                            <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item {{ Request::routeIs('settings.edit') ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('settings.edit') }}">App Settings</a>
                            </li>
                            <li class="nxl-item {{ Request::routeIs('settings.smtp.edit') ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('settings.smtp.edit') }}">SMTP Settings</a>
                            </li>
                        </ul>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
    <!--! ================================================================ !-->
    <!--! [End]  Navigation Manu !-->
    <!--! ================================================================ !-->	
	<!-- Header Section Start -->
            <!--! ================================================================ !-->
    <!--! [Start] Header !-->
    <!--! ================================================================ !-->
    <header class="nxl-header">
        <div class="header-wrapper">
            <!--! [Start] Header Left !-->
            <div class="header-left d-flex align-items-center gap-4">
                <!--! [Start] nxl-head-mobile-toggler !-->
                <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn">
                        <div class="hamburger-box">
                            <div class="hamburger-inner"></div>
                        </div>
                    </div>
                </a>
                <!--! [Start] nxl-head-mobile-toggler !-->
                <!--! [Start] nxl-navigation-toggle !-->
                <div class="nxl-navigation-toggle">
                    <a href="javascript:void(0);" id="menu-mini-button">
                        <i class="feather-align-left"></i>
                    </a>
                    <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                        <i class="feather-arrow-right"></i>
                    </a>
                </div>
                <!--! [End] nxl-navigation-toggle !-->
            </div>
            <!--! [End] Header Left !-->
            <!--! [Start] Header Right !-->
            <div class="header-right ms-auto">
                <div class="d-flex align-items-center">
                    <div class="nxl-h-item d-none d-sm-flex">
                        <div class="full-screen-switcher">
                            <a href="javascript:void(0);" class="nxl-head-link me-0" onclick="$('body').fullScreenHelper('toggle');">
                                <i class="feather-maximize maximize"></i>
                                <i class="feather-minimize minimize"></i>
                            </a>
                        </div>
                    </div>
                    <div class="nxl-h-item dark-light-theme">
                        <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                            <i class="feather-moon"></i>
                        </a>
                        <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                            <i class="feather-sun"></i>
                        </a>
                    </div>
                    <div class="dropdown nxl-h-item">
                        <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                            @if(isset($loggedUser) && $loggedUser->profile_photo)
                                <img src="{{ asset($loggedUser->profile_photo) }}" alt="user-image" class="img-fluid user-avtar me-0" style="width: 35px; height: 35px; object-fit: cover; border-radius: 50%;" />
                            @elseif(isset($loggedUser))
                                <div class="avatar-text bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center me-0" style="width: 35px; height: 35px; font-weight: 700; font-size: 14px;">
                                    {{ $loggedUser->getInitials() }}
                                </div>
                            @else
                                <img src="{{ asset('assets/images/avatar/1.png') }}" alt="user-image" class="img-fluid user-avtar me-0" />
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                            <div class="dropdown-header">
                                <div class="d-flex align-items-center">
                                    @if(isset($loggedUser) && $loggedUser->profile_photo)
                                        <img src="{{ asset($loggedUser->profile_photo) }}" alt="user-image" class="img-fluid user-avtar" style="width: 45px; height: 45px; object-fit: cover; border-radius: 50%;" />
                                    @elseif(isset($loggedUser))
                                        <div class="avatar-text bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; font-weight: 700; font-size: 16px;">
                                            {{ $loggedUser->getInitials() }}
                                        </div>
                                    @else
                                        <img src="{{ asset('assets/images/avatar/1.png') }}" alt="user-image" class="img-fluid user-avtar" />
                                    @endif
                                    <div>
                                        <h6 class="text-dark mb-0">{{ $loggedUser->name ?? 'Administrator' }}</h6>
                                        <span class="fs-12 fw-medium text-muted">{{ $loggedUser->email ?? 'admin@wings.com' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="feather-user"></i>
                                <span>Profile Details</span>
                            </a>
                            <a href="{{ route('account-settings.edit') }}" class="dropdown-item">
                                <i class="feather-settings"></i>
                                <span>Account Settings</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="feather-log-out"></i>
                                <span>Logout</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--! [End] Header Right !-->
        </div>
    </header>
    <!--! ================================================================ !-->
    <!--! [End] Header !-->
    <!--! ================================================================ !-->    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="nxl-container">
        @yield('content')
	<!--<< Footer Section Start >>-->
	        <!-- [ Footer ] start -->
        <footer class="footer">
            <p class="fs-11 text-muted fw-medium text-uppercase mb-0 copyright">
                <span>Copyright &copy;</span>
                <script>
                    document.write(new Date().getFullYear());
                </script>
                <span>{{ isset($appSettings->application_name) ? $appSettings->application_name : 'Dashboard' }}</span>
            </p>
            <div class="d-flex align-items-center gap-4">
                <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Help</a>
                <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Terms</a>
                <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Privacy</a>
            </div>
        </footer>
    </main>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    @yield('modals')
	<!--<< Footer Section Start >>-->
	
    <!--! ================================================================ !-->	<!--<< All JS Plugins >>-->
	    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <!-- vendors.min.js {always must need to be top} -->
    <script src="{{ asset('assets/vendors/js/daterangepicker.min.js') }}"></script>	
    <script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>	
    <script src="{{ asset('assets/vendors/js/circle-progress.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/js/jquery.time-to.min.js') }}"></script>
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    <script src="{{ asset('assets/js/common-init.min.js') }}"></script>
    @stack('scripts')
    <script src="{{ asset('assets/js/dual-view-listing.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var darkButton = document.querySelector(".dark-button");
            var lightButton = document.querySelector(".light-button");
            var htmlElement = document.documentElement;

            if (darkButton) {
                darkButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    htmlElement.classList.add("app-skin-dark");
                    localStorage.setItem("app-skin-dark", "app-skin-dark");
                });
            }

            if (lightButton) {
                lightButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    htmlElement.classList.remove("app-skin-dark");
                    localStorage.setItem("app-skin-dark", "app-skin-light");
                });
            }
        });
    </script>


    <!-- Wings Global Flight Aviation Loader -->
    @include('partials.loader')
    <script src="{{ asset('assets/js/wings-loader.js') }}"></script>
	
</body>
<!-- Mirrored from bestwpware.com/html/tf/duralux-php/index.php by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 24 Oct 2025 07:04:58 GMT -->
</html>
