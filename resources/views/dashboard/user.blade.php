@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard
                    </h2>
                    <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name }}!</p>
                </div>
                <div>
                    <span class="badge bg-success fs-6">
                        <i class="bi bi-person-circle me-1"></i>{{ ucfirst(Auth::user()->role) }}
                    </span>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">My Bookings</h6>
                                    <h3 class="mb-0">{{ $recentBookings->count() }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-ticket-perforated display-4"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('ticketing.my-bookings') }}" class="text-white text-decoration-none">
                                View All <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Upcoming Trips</h6>
                                    <h3 class="mb-0">{{ $upcomingTrips->count() }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-calendar-check display-4"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('ticketing.my-bookings') }}" class="text-white text-decoration-none">
                                View Details <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Notifications</h6>
                                    <h3 class="mb-0">{{ $notifications->count() }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-bell display-4"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('inbox.index') }}" class="text-dark text-decoration-none">
                                View All <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Book New Ticket</h6>
                                    <p class="mb-0">Quick booking</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-plus-circle display-4"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('ticketing.routes') }}" class="text-white text-decoration-none">
                                Start Booking <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="row">
                <!-- Recent Bookings -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>Recent Bookings
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($recentBookings->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Booking Code</th>
                                                <th>Route</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentBookings as $booking)
                                                <tr>
                                                    <td>
                                                        <span class="fw-bold">{{ $booking->booking_code }}</span>
                                                    </td>
                                                    <td>
                                                        {{ $booking->schedule->route->origin }} → {{ $booking->schedule->route->destination }}
                                                    </td>
                                                    <td>
                                                        {{ $booking->schedule->departure_time->format('M d, Y H:i') }}
                                                    </td>
                                                    <td>
                                                        @if($booking->status === 'pending')
                                                            <span class="badge bg-warning">Pending</span>
                                                        @elseif($booking->status === 'confirmed')
                                                            <span class="badge bg-success">Confirmed</span>
                                                        @elseif($booking->status === 'cancelled')
                                                            <span class="badge bg-danger">Cancelled</span>
                                                        @else
                                                            <span class="badge bg-info">{{ ucfirst($booking->status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($booking->status === 'pending' && !$booking->payment)
                                                            <a href="{{ route('payment.create', $booking) }}" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-credit-card me-1"></i>Pay Now
                                                            </a>
                                                        @elseif($booking->payment)
                                                            <a href="{{ route('payment.status', $booking->payment) }}" class="btn btn-sm btn-outline-info">
                                                                <i class="bi bi-eye me-1"></i>View Payment
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-ticket-perforated display-1 text-muted"></i>
                                    <h5 class="mt-3 text-muted">No bookings yet</h5>
                                    <p class="text-muted">Start by booking your first ticket!</p>
                                    <a href="{{ route('ticketing.routes') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Book Ticket
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Upcoming Trips & Notifications -->
                <div class="col-md-4">
                    <!-- Upcoming Trips -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-check me-2"></i>Upcoming Trips
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($upcomingTrips->count() > 0)
                                @foreach($upcomingTrips as $trip)
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <div>
                                            <div class="fw-bold">{{ $trip->schedule->route->origin }} → {{ $trip->schedule->route->destination }}</div>
                                            <small class="text-muted">{{ $trip->schedule->departure_time->format('M d, H:i') }}</small>
                                        </div>
                                        <span class="badge bg-success">{{ $trip->status }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-3">
                                    <i class="bi bi-calendar-x text-muted"></i>
                                    <p class="text-muted mb-0 mt-2">No upcoming trips</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recent Notifications -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bell me-2"></i>Recent Notifications
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($notifications->count() > 0)
                                @foreach($notifications as $notification)
                                    <div class="d-flex align-items-start border-bottom py-2">
                                        <div class="me-2">
                                            <i class="bi bi-info-circle text-primary"></i>
                                        </div>                                        <div class="flex-grow-1">
                                            <div class="fw-bold">{{ $notification->title }}</div>
                                            <p class="mb-1 small">{{ $notification->content }}</p>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-3">
                                    <i class="bi bi-bell-slash text-muted"></i>
                                    <p class="text-muted mb-0 mt-2">No new notifications</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
