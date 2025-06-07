<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Bus Trans Bandung') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    
    <!-- jQuery - make sure it loads before Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    @yield('styles')
    
    <!-- Ensure Select2 is initialized properly -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.jQuery && jQuery().select2) {
                console.log("jQuery and Select2 loaded correctly");
            } else {
                console.error("jQuery or Select2 not loaded");
                // Attempt to load them if missing
                if (!window.jQuery) {
                    console.log("Loading jQuery...");
                    var script = document.createElement('script');
                    script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
                    document.head.appendChild(script);
                }
            }
        });
    </script>
    
    <style>
        /* Custom styles for search form */
        body {
            background-color: #f8f9fa;
        }
        
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            padding: 0.75rem 1.5rem;
            position: relative;
        }
        
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
        }
        
        .form-floating > label {
            padding-left: 1.5rem;
        }
        
        .form-floating > .form-control,
        .form-floating > .form-select {
            padding-left: 1.5rem;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: none;
        }
        
        .route-card {
            transition: all 0.3s ease;
        }
        
        .route-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
        }
        
        .card-header.bg-primary {
            background: linear-gradient(45deg, #007bff, #0056b3) !important;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #007bff, #0056b3);
            border: none;
        }
        
        /* Hero section style for transport search */
        .hero-section {
            background-image: url('https://source.unsplash.com/random/1200x600/?bus,transport,bandung');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 4rem 0;
            position: relative;
            margin-bottom: 2rem;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }
        
        .hero-section h1 {
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
        }
        
        /* Custom styles for Select2 */
        .select2-container {
            width: 100% !important;
        }
        
        .form-floating .select2-container .select2-selection {
            height: 58px;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
            padding-left: 0.75rem;
            display: flex;
            align-items: center;
            background-color: #fff;
        }
        
        .form-floating .select2-container .select2-selection--single .select2-selection__rendered {
            color: #212529;
            padding-left: 0;
            line-height: 1.5;
        }
        
        .form-floating .select2-container .select2-selection__arrow {
            height: 100%;
            top: 0;
        }
        
        /* Adjustments for the dropdown */
        .select2-dropdown {
            border-color: #86b7fe;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
            z-index: 9999;
        }
        
        .select2-container--open .select2-dropdown {
            margin-top: 5px;
        }
        
        .select2-results__option {
            padding: 8px 12px;
        }
        
        .select2-search--dropdown .select2-search__field {
            padding: 8px;
            border-radius: 4px;
        }
        
        /* Fix label position in form floating */
        .form-floating > label {
            z-index: 0;
        }
        
        /* Fix z-index for the dropdown */
        .form-floating {
            position: relative;
        }
        
        .select2-container--open {
            z-index: 9999;
        }
    </style>
    
    <!-- Theme for Select2 Bootstrap 5 -->
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            width: 100%;
            min-height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                    <i class="bi bi-bus-front me-2"></i>Bus Trans Bandung
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('ticketing.routes') }}">
                                    <i class="bi bi-ticket-perforated me-1"></i>Book Ticket
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('ticketing.my-bookings') }}">
                                    <i class="bi bi-list-check me-1"></i>My Bookings
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('payment.my-payments') }}">
                                    <i class="bi bi-credit-card me-1"></i>My Payments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('inbox.index') }}">
                                    <i class="bi bi-envelope me-1"></i>Inbox
                                </a>
                            </li>
                            @if(Auth::user()->isAdmin())
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-gear me-1"></i>Admin
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.users') }}">
                                            <i class="bi bi-people me-1"></i>Users
                                        </a></li>
                                        <!-- <li><a class="dropdown-item" href="{{ route('admin.bookings') }}">
                                            <i class="bi bi-calendar-check me-1"></i>Bookings
                                        </a></li> -->
                                        <li><a class="dropdown-item" href="{{ route('admin.payments.pending') }}">
                                            <i class="bi bi-cash-coin me-1"></i>Payment Verification
                                        </a></li>
                                        <!-- <li><a class="dropdown-item" href="{{ route('admin.complaints') }}">
                                            <i class="bi bi-chat-square-text me-1"></i>Complaints
                                        </a></li> -->
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.routes') }}">
                                            <i class="bi bi-map me-1"></i>Routes
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.schedules') }}">
                                            <i class="bi bi-clock me-1"></i>Schedules
                                        </a></li>
                                    </ul>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                                    @if(Auth::user()->isAdmin())
                                        <span class="badge bg-warning text-dark ms-1">Admin</span>
                                    @endif
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <h6 class="dropdown-header">
                                        <i class="bi bi-person me-1"></i>{{ Auth::user()->name }}
                                        <br><small class="text-muted">{{ Auth::user()->email }}</small>
                                    </h6>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('dashboard') }}">
                                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="$('#profileModal').modal('show')">
                                        <i class="bi bi-person-gear me-1"></i>Profile Settings
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-1"></i>{{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>

            @yield('content')
        </main>
    </div>
    
    @stack('scripts')
</body>
</html>
