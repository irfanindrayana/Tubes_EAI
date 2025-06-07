@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-gear me-2 text-primary"></i>Admin Dashboard
                    </h2>
                    <p class="text-muted mb-0">System Overview & Management</p>
                </div>
                <div>
                    <span class="badge bg-warning text-dark fs-6">
                        <i class="bi bi-shield-check me-1"></i>Administrator
                    </span>
                </div>
            </div>

            <!-- Admin Stats -->
            <div class="row mb-4">
                <div class="col-md-2-4">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people display-3 mb-2"></i>
                            <h3 class="mb-1">{{ $stats['total_users'] }}</h3>
                            <p class="mb-0">Total Users</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2-4">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-ticket-perforated display-3 mb-2"></i>
                            <h3 class="mb-1">{{ $stats['total_bookings'] }}</h3>
                            <p class="mb-0">Total Bookings</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2-4">
                    <div class="card bg-warning text-dark h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-clock display-3 mb-2"></i>
                            <h3 class="mb-1">{{ $stats['pending_payments'] }}</h3>
                            <p class="mb-0">Pending Payments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2-4">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-cash-coin display-3 mb-2"></i>
                            <h3 class="mb-1">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</h3>
                            <p class="mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2-4">
                    <div class="card bg-secondary text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-map display-3 mb-2"></i>
                            <h3 class="mb-1">{{ $stats['active_routes'] }}</h3>
                            <p class="mb-0">Active Routes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Management Sections -->
            <div class="row">
                <!-- Recent Bookings -->
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-list-check me-2"></i>Recent Bookings
                            </h5>
                            <a href="{{ route('admin.bookings') }}" class="btn btn-sm btn-outline-primary">
                                View All
                            </a>
                        </div>
                        <div class="card-body">
                            @if($recentBookings->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Booking Code</th>
                                                <th>User</th>
                                                <th>Route</th>
                                                <th>Status</th>
                                                <th>Payment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentBookings as $booking)
                                                <tr>
                                                    <td>
                                                        <span class="fw-bold">{{ $booking->booking_code }}</span>
                                                    </td>
                                                    <td>{{ $booking->user->name }}</td>
                                                    <td>
                                                        <small>{{ $booking->schedule->route->origin }} → {{ $booking->schedule->route->destination }}</small>
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
                                                        @if($booking->payment)
                                                            @if($booking->payment->status === 'pending')
                                                                <span class="badge bg-warning">Pending</span>
                                                            @elseif($booking->payment->status === 'verified')
                                                                <span class="badge bg-success">Verified</span>
                                                            @elseif($booking->payment->status === 'rejected')
                                                                <span class="badge bg-danger">Rejected</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">No Payment</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-list-check display-1 text-muted"></i>
                                    <h5 class="mt-3 text-muted">No recent bookings</h5>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Pending Payments -->
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>Pending Payments
                            </h5>
                            <a href="{{ route('admin.payments.pending') }}" class="btn btn-sm btn-warning">
                                Review All
                            </a>
                        </div>
                        <div class="card-body">
                            @if($pendingPayments->count() > 0)
                                @foreach($pendingPayments as $payment)
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <div>
                                            <div class="fw-bold">{{ $payment->booking->booking_code }}</div>
                                            <small class="text-muted">{{ $payment->user->name }}</small>
                                            <br>
                                            <small class="text-muted">Rp {{ number_format($payment->amount, 0, ',', '.') }}</small>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.payments.pending') }}" class="btn btn-sm btn-outline-primary">
                                                Review
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-3">
                                    <i class="bi bi-check-circle text-success"></i>
                                    <p class="text-muted mb-0 mt-2">No pending payments</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-lightning me-2"></i>Quick Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.payments.pending') }}" class="btn btn-warning">
                                    <i class="bi bi-cash-coin me-2"></i>Review Payments
                                </a>
                                <a href="{{ route('admin.users') }}" class="btn btn-info">
                                    <i class="bi bi-people me-2"></i>Manage Users
                                </a>
                                <a href="{{ route('admin.bookings') }}" class="btn btn-success">
                                    <i class="bi bi-list-check me-2"></i>View All Bookings
                                </a>
                                <a href="{{ route('admin.complaints') }}" class="btn btn-secondary">
                                    <i class="bi bi-chat-square-text me-2"></i>Handle Complaints
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.col-md-2-4 {
    flex: 0 0 auto;
    width: 20%;
}

@media (max-width: 768px) {
    .col-md-2-4 {
        width: 50%;
        margin-bottom: 1rem;
    }
}
</style>
@endsection
