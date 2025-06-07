@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-center py-5">
            <h1 class="display-4 fw-bold text-primary mb-2">
                <i class="bi bi-bus-front me-2"></i> Bus Trans Bandung
            </h1>
            <h2 class="text-muted mb-4">Microservices Architecture</h2>
            <p class="lead">Modern transportation management system built with Laravel and GraphQL</p>
        </div>
    </div>

    <!-- Introduction Section -->
    <div class="row mb-5">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <h3 class="card-title mb-4 text-primary"><i class="bi bi-buildings me-2"></i>About Our Microservices</h3>
                    <p class="card-text">
                        Our Bus Trans Bandung system is built on a modern microservices architecture, providing scalable, resilient and maintainable services for public transportation management. Each service is independently deployable and maintains its own database.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('login') }}" class="btn btn-primary me-2">
                            <i class="bi bi-key me-1"></i> Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-person-plus me-1"></i> Register
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Microservices Section -->
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2 class="h2 text-primary">Our Microservices</h2>
            <p class="text-muted">Each service operates independently with its own database</p>
        </div>
    </div>

    <!-- User Management Service -->
    <div class="row mb-5">
        <div class="col-md-6 offset-md-3">
            <div class="card mb-4 border-primary h-100">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0"><i class="bi bi-people me-2"></i>User Management Service</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="bg-light p-3 rounded-circle mb-2 mx-auto" style="width: 80px; height: 80px;">
                                <i class="bi bi-person-circle text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <h4 class="h6 text-primary mb-3">Core Functionality</h4>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>User Authentication & Authorization</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>User Profile Management</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Role-Based Access Control</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>User Preferences & Settings</li>
                            </ul>
                            <div class="mt-3">
                                <span class="badge bg-primary">Database: transbandung_users</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Ticketing Service -->
        <div class="col-md-4">
            <div class="card mb-4 border-success h-100">
                <div class="card-header bg-success text-white">
                    <h3 class="h5 mb-0"><i class="bi bi-ticket-perforated me-2"></i>Ticketing Service</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-light p-3 rounded-circle mb-2 mx-auto" style="width: 70px; height: 70px;">
                            <i class="bi bi-ticket-detailed text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <h4 class="h6 text-success mb-3">Core Functionality</h4>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Route Management</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Schedule Planning</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Seat Reservation</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Booking Management</li>
                    </ul>
                    <div class="mt-3">
                        <span class="badge bg-success">Database: transbandung_ticketing</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Service -->
        <div class="col-md-4">
            <div class="card mb-4 border-warning h-100">
                <div class="card-header bg-warning text-dark">
                    <h3 class="h5 mb-0"><i class="bi bi-credit-card me-2"></i>Payment Service</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-light p-3 rounded-circle mb-2 mx-auto" style="width: 70px; height: 70px;">
                            <i class="bi bi-cash-coin text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <h4 class="h6 text-warning mb-3">Core Functionality</h4>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Payment Processing</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Multiple Payment Methods</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Transaction History</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Payment Verification</li>
                    </ul>
                    <div class="mt-3">
                        <span class="badge bg-warning text-dark">Database: transbandung_payments</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review & Rating Service -->
        <div class="col-md-4">
            <div class="card mb-4 border-info h-100">
                <div class="card-header bg-info text-white">
                    <h3 class="h5 mb-0"><i class="bi bi-star me-2"></i>Review & Rating Service</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-light p-3 rounded-circle mb-2 mx-auto" style="width: 70px; height: 70px;">
                            <i class="bi bi-star-half text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <h4 class="h6 text-info mb-3">Core Functionality</h4>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Customer Reviews</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Rating System</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Complaint Management</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Service Improvement Tracking</li>
                    </ul>
                    <div class="mt-3">
                        <span class="badge bg-info">Database: transbandung_reviews</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inbox Service -->
    <div class="row mb-5">
        <div class="col-md-6 offset-md-3">
            <div class="card mb-4 border-secondary h-100">
                <div class="card-header bg-secondary text-white">
                    <h3 class="h5 mb-0"><i class="bi bi-envelope me-2"></i>Inbox Service</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="bg-light p-3 rounded-circle mb-2 mx-auto" style="width: 70px; height: 70px;">
                                <i class="bi bi-chat-dots text-secondary" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <h4 class="h6 text-secondary mb-3">Core Functionality</h4>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>User Messaging</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>System Notifications</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Announcements</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Customer Support Communication</li>
                            </ul>
                            <div class="mt-3">
                                <span class="badge bg-secondary">Database: transbandung_inbox</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Architecture Diagram -->
    <div class="row mb-5">
        <div class="col-md-10 offset-md-1">
            <div class="card border-0 shadow">
                <div class="card-header bg-dark text-white">
                    <h3 class="h5 mb-0"><i class="bi bi-diagram-3 me-2"></i>Microservices Architecture Diagram</h3>
                </div>
                <div class="card-body p-4 bg-light">
                    <div class="text-center">
                        <div class="architecture-diagram py-4">
                            <div class="row justify-content-center mb-4">
                                <div class="col-8">
                                    <div class="card border-dark">
                                        <div class="card-body text-center py-2">
                                            <h5><i class="bi bi-browser me-2"></i>Client Applications</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-center mb-4">
                                <div class="col-8">
                                    <div class="card border-dark bg-dark text-white">
                                        <div class="card-body text-center py-2">
                                            <h5><i class="bi bi-code-slash me-2"></i>GraphQL API Gateway</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-center text-center">
                                <div class="col">
                                    <div class="card border-primary mb-2">
                                        <div class="card-body py-2 bg-primary text-white">
                                            User Service
                                        </div>
                                    </div>
                                    <div class="card border-primary">
                                        <div class="card-body py-1 bg-light">
                                            <small>DB: transbandung_users</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="card border-success mb-2">
                                        <div class="card-body py-2 bg-success text-white">
                                            Ticketing Service
                                        </div>
                                    </div>
                                    <div class="card border-success">
                                        <div class="card-body py-1 bg-light">
                                            <small>DB: transbandung_ticketing</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="card border-warning mb-2">
                                        <div class="card-body py-2 bg-warning text-dark">
                                            Payment Service
                                        </div>
                                    </div>
                                    <div class="card border-warning">
                                        <div class="card-body py-1 bg-light">
                                            <small>DB: transbandung_payments</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="card border-info mb-2">
                                        <div class="card-body py-2 bg-info text-white">
                                            Review Service
                                        </div>
                                    </div>
                                    <div class="card border-info">
                                        <div class="card-body py-1 bg-light">
                                            <small>DB: transbandung_reviews</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="card border-secondary mb-2">
                                        <div class="card-body py-2 bg-secondary text-white">
                                            Inbox Service
                                        </div>
                                    </div>
                                    <div class="card border-secondary">
                                        <div class="card-body py-1 bg-light">
                                            <small>DB: transbandung_inbox</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Stack -->
    <div class="row mb-5">
        <div class="col-md-10 offset-md-1">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0"><i class="bi bi-stack me-2"></i>Technical Stack</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="text-center mb-2">
                                <div class="bg-light p-3 rounded-circle mb-2 mx-auto" style="width: 60px; height: 60px;">
                                    <i class="bi bi-hdd-stack text-primary" style="font-size: 1.8rem;"></i>
                                </div>
                                <h5>Backend</h5>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Laravel PHP Framework</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>MySQL (Multiple Databases)</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>GraphQL API</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>RESTful Endpoints</li>
                            </ul>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="text-center mb-2">
                                <div class="bg-light p-3 rounded-circle mb-2 mx-auto" style="width: 60px; height: 60px;">
                                    <i class="bi bi-window text-primary" style="font-size: 1.8rem;"></i>
                                </div>
                                <h5>Frontend</h5>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Bootstrap 5</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Blade Templates</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Vite for Asset Compilation</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>SASS Styling</li>
                            </ul>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="text-center mb-2">
                                <div class="bg-light p-3 rounded-circle mb-2 mx-auto" style="width: 60px; height: 60px;">
                                    <i class="bi bi-tools text-primary" style="font-size: 1.8rem;"></i>
                                </div>
                                <h5>DevOps & Tools</h5>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Git Version Control</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Composer for Dependencies</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>NPM for Frontend</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Artisan CLI Tools</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="row mb-5">
        <div class="col-md-6 offset-md-3">
            <div class="card border-0 shadow bg-primary text-white">
                <div class="card-body p-5 text-center">
                    <h3 class="mb-3">Ready to Explore Bus Trans Bandung?</h3>
                    <p class="lead mb-4">Login to access the full functionality of our microservices architecture</p>
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Get Started
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="row">
        <div class="col-12 text-center mb-4">
            <p class="text-muted">
                <small>© 2025 Bus Trans Bandung • Built with Laravel Microservices Architecture</small>
            </p>
        </div>
    </div>
</div>
@endsection
