@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-credit-card me-2"></i>My Payments</h2>
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

            <!-- Filter and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="monthFilter">
                                <option value="">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" placeholder="Search by booking code..." id="searchPayments">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                <i class="bi bi-x-circle me-2"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                @forelse($payments as $payment)
                <div class="col-lg-6 col-xl-4 mb-4 payment-card" 
                     data-status="{{ $payment->status }}" 
                     data-month="{{ $payment->created_at->month }}" 
                     data-booking-code="{{ $payment->booking->booking_code }}">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $payment->booking->booking_code }}</div>
                                <small class="text-muted">Payment #{{ $payment->id }}</small>
                            </div>
                            <span class="badge {{ $payment->status === 'verified' ? 'bg-success' : ($payment->status === 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">
                                {{ $payment->booking->schedule->route->origin }} → {{ $payment->booking->schedule->route->destination }}
                            </h6>
                            
                            <div class="mb-2">
                                <small class="text-muted">Travel Date</small>
                                <div>{{ \Carbon\Carbon::parse($payment->booking->schedule->departure_time)->format('M d, Y - H:i') }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <small class="text-muted">Payment Method</small>
                                    <div class="fw-bold">{{ $payment->paymentMethod->name }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Amount</small>
                                    <div class="fw-bold text-success">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">Payment Date</small>
                                <div>{{ $payment->created_at->format('M d, Y H:i') }}</div>
                            </div>

                            @if($payment->verified_at)
                            <div class="mb-2">
                                <small class="text-muted">Verified Date</small>
                                <div>{{ $payment->verified_at->format('M d, Y H:i') }}</div>
                            </div>                            @endif
                            
                            @if($payment->status === 'rejected' && $payment->admin_notes)
                            <div class="mb-2">
                                <small class="text-muted">Admin Note</small>
                                <div class="text-danger small">{{ Str::limit($payment->admin_notes, 50) }}</div>
                            </div>
                            @endif

                            @if($payment->proof_image)
                            <div class="mb-2">
                                <small class="text-muted">Payment Proof</small>
                                <div>
                                    <i class="bi bi-image text-success"></i> Uploaded
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex gap-2">
                                <a href="{{ route('payment.status', $payment) }}" class="btn btn-primary btn-sm flex-fill">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </a>
                                
                                @if($payment->status === 'rejected')
                                    <a href="{{ route('payment.create', $payment->booking) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="bi bi-arrow-repeat me-1"></i>Retry
                                    </a>
                                @elseif($payment->status === 'verified')
                                    <button class="btn btn-outline-success btn-sm" onclick="downloadTicket({{ $payment->id }})">
                                        <i class="bi bi-download me-1"></i>Ticket
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
                            <i class="bi bi-credit-card display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">No Payments Found</h4>
                            <p class="text-muted">You haven't made any payments yet.</p>
                            <a href="{{ route('ticketing.routes') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Book Your First Ticket
                            </a>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>

            {{ $payments->links() }}

            <!-- Payment Summary -->
            @if($payments->count() > 0)
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Payment Summary</h5>
                    <div class="row text-center">
                        @php
                            $totalAmount = $payments->sum('amount');
                            $verifiedCount = $payments->where('status', 'verified')->count();
                            $pendingCount = $payments->where('status', 'pending')->count();
                            $rejectedCount = $payments->where('status', 'rejected')->count();
                        @endphp
                        <div class="col-md-3">
                            <div class="border-end">
                                <div class="h4 text-success">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
                                <div class="text-muted">Total Paid</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <div class="h4 text-success">{{ $verifiedCount }}</div>
                                <div class="text-muted">Verified</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <div class="h4 text-warning">{{ $pendingCount }}</div>
                                <div class="text-muted">Pending</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="h4 text-danger">{{ $rejectedCount }}</div>
                            <div class="text-muted">Rejected</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const monthFilter = document.getElementById('monthFilter');
    const searchInput = document.getElementById('searchPayments');
    const paymentCards = document.querySelectorAll('.payment-card');

    // Filter function
    function filterPayments() {
        const statusValue = statusFilter.value.toLowerCase();
        const monthValue = monthFilter.value;
        const searchValue = searchInput.value.toLowerCase();

        paymentCards.forEach(card => {
            const cardStatus = card.dataset.status;
            const cardMonth = card.dataset.month;
            const cardBookingCode = card.dataset.bookingCode.toLowerCase();

            const statusMatch = !statusValue || cardStatus === statusValue;
            const monthMatch = !monthValue || cardMonth === monthValue;
            const searchMatch = !searchValue || cardBookingCode.includes(searchValue);

            if (statusMatch && monthMatch && searchMatch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Event listeners
    statusFilter.addEventListener('change', filterPayments);
    monthFilter.addEventListener('change', filterPayments);
    searchInput.addEventListener('input', filterPayments);
});

function clearFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('monthFilter').value = '';
    document.getElementById('searchPayments').value = '';
    
    document.querySelectorAll('.payment-card').forEach(card => {
        card.style.display = 'block';
    });
}

function downloadTicket(paymentId) {
    // In a real implementation, this would generate and download a PDF ticket
    alert('Ticket download feature will be implemented.');
    // window.location.href = `/payment/${paymentId}/download-ticket`;
}
</script>
@endpush
@endsection
