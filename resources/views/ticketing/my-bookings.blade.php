@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-list-check me-2"></i>My Bookings</h2>
                <a href="{{ route('ticketing.routes') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>New Booking
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                @forelse($bookings as $booking)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="fw-bold">{{ $booking->booking_code }}</div>
                            <span class="badge {{ $booking->status === 'confirmed' ? 'bg-success' : ($booking->status === 'cancelled' ? 'bg-danger' : 'bg-warning') }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">
                                {{ $booking->schedule->route->origin }} → {{ $booking->schedule->route->destination }}
                            </h6>
                            
                            <div class="mb-2">
                                <small class="text-muted">Date & Time</small>
                                <div>{{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('M d, Y - H:i') }}</div>
                            </div>                            <div class="row mb-2">
                                <div class="col-6">
                                    <small class="text-muted">Seat{{ $booking->seat_count > 1 ? 's' : '' }}</small>
                                    @if($booking->seat_count == 1)
                                        <div class="fw-bold">{{ $booking->seat_numbers[0] ?? 'N/A' }}</div>
                                    @else
                                        <div class="fw-bold">{{ implode(', ', $booking->seat_numbers) }}</div>
                                    @endif
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Price</small>
                                    <div class="fw-bold text-success">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</div>
                                </div>
                            </div><div class="mb-2">
                                <small class="text-muted">Passenger{{ $booking->seat_count > 1 ? 's' : '' }}</small>
                                @if($booking->seat_count == 1)
                                    <div>{{ $booking->passenger_details[0]['name'] ?? 'N/A' }}</div>
                                @else
                                    <div>{{ $booking->seat_count }} passengers</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">Booking Date</small>
                                <div>{{ $booking->booking_date->format('M d, Y H:i') }}</div>
                            </div>

                            @if($booking->payment)
                                <div class="mb-2">
                                    <small class="text-muted">Payment Status</small>
                                    <div>
                                        <span class="badge {{ $booking->payment->status === 'verified' ? 'bg-success' : ($booking->payment->status === 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                                            {{ ucfirst($booking->payment->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex gap-2">
                                @if(!$booking->payment && $booking->status === 'pending')
                                    <a href="{{ route('payment.create', $booking) }}" class="btn btn-primary btn-sm flex-fill">
                                        <i class="bi bi-credit-card me-1"></i>Pay
                                    </a>
                                @endif
                                
                                @if($booking->payment)
                                    <a href="{{ route('payment.status', $booking->payment) }}" class="btn btn-outline-secondary btn-sm flex-fill">
                                        <i class="bi bi-eye me-1"></i>Payment
                                    </a>
                                @endif

                                @if($booking->status === 'confirmed' && \Carbon\Carbon::parse($booking->schedule->departure_time)->isFuture())
                                    <button class="btn btn-outline-danger btn-sm" onclick="cancelBooking({{ $booking->id }})">
                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-ticket-perforated display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">No Bookings Found</h4>
                            <p class="text-muted">You haven't made any bookings yet.</p>
                            <a href="{{ route('ticketing.routes') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Book Your First Ticket
                            </a>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>

            {{ $bookings->links() }}
        </div>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this booking?</p>
                <p class="text-muted">This action cannot be undone. Refund processing may take 3-5 business days.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Booking</button>
                <button type="button" class="btn btn-danger" id="confirmCancel">Yes, Cancel</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let bookingToCancel = null;

function cancelBooking(bookingId) {
    bookingToCancel = bookingId;
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

document.getElementById('confirmCancel').addEventListener('click', function() {
    if (bookingToCancel) {
        // In a real implementation, you'd make an AJAX call to cancel the booking
        alert('Booking cancellation feature will be implemented via API.');
        // fetch(`/bookings/${bookingToCancel}/cancel`, { method: 'POST' })
        //     .then(response => response.json())
        //     .then(data => window.location.reload());
    }
});
</script>
@endpush
@endsection
