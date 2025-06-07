@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-speedometer2 me-2 text-primary"></i>Bus Trans Bandung Dashboard
                    </h2>
                    <p class="text-muted mb-0">Microservices Architecture Overview</p>
                </div>
                <div>
                    <span class="badge bg-success fs-6">
                        <i class="bi bi-check-circle me-1"></i>All Services Online
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Microservices Status Cards -->
    <div class="row mb-4">
        <!-- User Management Service -->
        <div class="col-md-4 mb-3">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>User Management Service
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Users:</span>
                        <span class="fw-bold">{{ $stats['total_users'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Admin Users:</span>
                        <span class="fw-bold">{{ $stats['admin_users'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Active Sessions:</span>
                        <span class="fw-bold text-success">{{ $stats['active_sessions'] ?? 1 }}</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row text-center">
                        <div class="col">
                            <small class="text-muted">GraphQL Endpoint</small><br>
                            <span class="badge bg-light text-dark">/graphql</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticketing Service -->
        <div class="col-md-4 mb-3">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-ticket-perforated me-2"></i>Ticketing Service
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Active Routes:</span>
                        <span class="fw-bold">{{ $stats['active_routes'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Bookings:</span>
                        <span class="fw-bold">{{ $stats['total_bookings'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Today's Bookings:</span>
                        <span class="fw-bold text-info">{{ $stats['today_bookings'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row text-center">
                        <div class="col">
                            <a href="{{ route('ticketing.routes') }}" class="btn btn-sm btn-success">
                                <i class="bi bi-arrow-right"></i> View Routes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Service -->
        <div class="col-md-4 mb-3">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>Payment Service
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Revenue:</span>
                        <span class="fw-bold">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Pending Payments:</span>
                        <span class="fw-bold text-warning">{{ $stats['pending_payments'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Completed Today:</span>
                        <span class="fw-bold text-success">{{ $stats['today_payments'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row text-center">
                        <div class="col">
                            <a href="{{ route('payment.my-payments') }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-arrow-right"></i> View Payments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Review & Rating Service -->
        <div class="col-md-6 mb-3">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-star me-2"></i>Review & Rating Service
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Reviews:</span>
                        <span class="fw-bold">{{ $stats['total_reviews'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Average Rating:</span>
                        <span class="fw-bold text-success">
                            {{ number_format($stats['average_rating'] ?? 0, 1) }}/5.0
                            <i class="bi bi-star-fill text-warning"></i>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Complaints:</span>
                        <span class="fw-bold text-danger">{{ $stats['total_complaints'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inbox Service -->
        <div class="col-md-6 mb-3">
            <div class="card border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope me-2"></i>Inbox Service
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Messages:</span>
                        <span class="fw-bold">{{ $stats['total_messages'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Unread Messages:</span>
                        <span class="fw-bold text-warning">{{ $stats['unread_messages'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Notifications:</span>
                        <span class="fw-bold text-info">{{ $stats['total_notifications'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row text-center">
                        <div class="col">
                            <a href="{{ route('inbox.index') }}" class="btn btn-sm btn-secondary">
                                <i class="bi bi-arrow-right"></i> View Inbox
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GraphQL API Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-code-slash me-2"></i>GraphQL API Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h3 class="text-primary">{{ $stats['total_queries'] ?? 15 }}</h3>
                            <p class="text-muted mb-0">Available Queries</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h3 class="text-success">{{ $stats['total_mutations'] ?? 12 }}</h3>
                            <p class="text-muted mb-0">Available Mutations</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h3 class="text-info">{{ $stats['api_calls_today'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">API Calls Today</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <a href="/graphiql" class="btn btn-dark" target="_blank">
                                <i class="bi bi-play-circle me-1"></i>GraphQL Playground
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('ticketing.routes') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-map me-2"></i>Browse Routes
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('ticketing.my-bookings') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-ticket me-2"></i>My Bookings
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('payment.my-payments') }}" class="btn btn-outline-warning w-100">
                                <i class="bi bi-credit-card me-2"></i>My Payments
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('inbox.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-envelope me-2"></i>Inbox
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Microservice Status Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-server me-2"></i>Microservice Architecture Status
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($microservices))
                        <div class="row">
                            @foreach($microservices as $key => $service)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border-success h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $service['name'] }}</h6>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">Database:</small>
                                                <code class="bg-light p-1 rounded">{{ $service['database'] }}</code>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">Status:</small>
                                                <span class="badge bg-{{ $service['status'] === 'active' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($service['status']) }}
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Models:</small>
                                                <small class="fw-bold">{{ count($service['models']) }} models</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
