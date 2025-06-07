@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-cash-coin me-2"></i>Payment Verification</h2>
                <div class="d-flex gap-2">
                    <span class="badge bg-warning fs-6">{{ $payments->total() }} Pending Payments</span>
                </div>
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
                @forelse($payments as $payment)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">Payment #{{ $payment->id }}</div>
                                <small class="text-muted">{{ $payment->booking->booking_code }}</small>
                            </div>
                            <span class="badge bg-warning">Pending</span>
                        </div>
                        <div class="card-body">
                            <!-- Customer Info -->
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Customer</h6>
                                <div class="fw-bold">{{ $payment->user->name }}</div>
                                <small class="text-muted">{{ $payment->user->email }}</small>
                            </div>

                            <!-- Trip Info -->                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Trip Details</h6>
                                <div class="fw-bold">{{ $payment->booking->schedule->route->origin }} → {{ $payment->booking->schedule->route->destination }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($payment->booking->schedule->departure_time)->format('M d, Y - H:i') }}</small>
                                <div>
                                    <strong>Seat{{ $payment->booking->seat_count > 1 ? 's' : '' }}:</strong> 
                                    @if($payment->booking->seat_count == 1)
                                        {{ $payment->booking->seat_numbers[0] ?? 'N/A' }}
                                    @else
                                        {{ implode(', ', $payment->booking->seat_numbers) }}
                                    @endif
                                </div>
                            </div>

                            <!-- Payment Info -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Amount</small>
                                    <div class="h5 text-success">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Method</small>
                                    <div class="fw-bold">{{ $payment->paymentMethod->name }}</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">Payment Date</small>
                                <div>{{ $payment->created_at->format('M d, Y H:i') }}</div>
                            </div>                            @if($payment->proof_image)
                            <div class="mb-3">
                                <small class="text-muted">Payment Proof</small>
                                <div class="mt-1">
                                    <img src="{{ Storage::url($payment->proof_image) }}" 
                                         alt="Payment Proof" 
                                         class="img-thumbnail"
                                         style="max-height: 150px; cursor: pointer;"
                                         onclick="showProofModal({{ $payment->id }}, '{{ Storage::url($payment->proof_image) }}')">
                                </div>
                            </div>
                            @else
                            <div class="mb-3">
                                <small class="text-danger">No payment proof uploaded</small>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-sm flex-fill" 
                                        onclick="verifyPayment({{ $payment->id }}, 'verified')">
                                    <i class="bi bi-check-circle me-1"></i>Verify
                                </button>
                                <button class="btn btn-danger btn-sm flex-fill" 
                                        onclick="verifyPayment({{ $payment->id }}, 'rejected')">
                                    <i class="bi bi-x-circle me-1"></i>Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-check-circle display-1 text-success mb-3"></i>
                            <h4 class="text-muted">All Payments Verified!</h4>
                            <p class="text-muted">There are no pending payments to review at the moment.</p>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>

            {{ $payments->links() }}
        </div>
    </div>
</div>

<!-- Payment Proof Modal -->
<div class="modal fade" id="proofModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Proof - Payment #<span id="proof-payment-id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="proof-image" src="" alt="Payment Proof" class="img-fluid">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="verifyFromModal('verified')">
                    <i class="bi bi-check-circle me-2"></i>Verify Payment
                </button>
                <button type="button" class="btn btn-danger" onclick="verifyFromModal('rejected')">
                    <i class="bi bi-x-circle me-2"></i>Reject Payment
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Verification Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verification-title">Verify Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="verification-form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="verification-status" name="status">
                    
                    <div class="mb-3">
                        <label for="verification-notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="verification-notes" name="notes" rows="3" 
                                  placeholder="Add any notes about this verification..."></textarea>
                    </div>

                    <div id="rejection-warning" class="alert alert-warning" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Rejecting this payment will allow the customer to submit a new payment.
                        Please provide a clear reason in the notes above.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="verification-btn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPaymentId = null;

function showProofModal(paymentId, imageSrc) {
    document.getElementById('proof-payment-id').textContent = paymentId;
    document.getElementById('proof-image').src = imageSrc;
    currentPaymentId = paymentId;
    new bootstrap.Modal(document.getElementById('proofModal')).show();
}

function verifyPayment(paymentId, status) {
    currentPaymentId = paymentId;
    showVerificationModal(status);
}

function verifyFromModal(status) {
    bootstrap.Modal.getInstance(document.getElementById('proofModal')).hide();
    showVerificationModal(status);
}

function showVerificationModal(status) {
    const modal = document.getElementById('verificationModal');
    const title = document.getElementById('verification-title');
    const statusInput = document.getElementById('verification-status');
    const btn = document.getElementById('verification-btn');
    const form = document.getElementById('verification-form');
    const rejectionWarning = document.getElementById('rejection-warning');
    const notesField = document.getElementById('verification-notes');    // Set form action
    form.action = `/admin/payments/verify/${currentPaymentId}`;
    
    // Configure modal based on status
    if (status === 'verified') {
        title.textContent = 'Verify Payment';
        btn.textContent = 'Verify Payment';
        btn.className = 'btn btn-success';
        rejectionWarning.style.display = 'none';
        notesField.placeholder = 'Add any verification notes...';
    } else {
        title.textContent = 'Reject Payment';
        btn.textContent = 'Reject Payment';
        btn.className = 'btn btn-danger';
        rejectionWarning.style.display = 'block';
        notesField.placeholder = 'Please provide reason for rejection...';
    }

    statusInput.value = status;
    notesField.value = '';
    
    new bootstrap.Modal(modal).show();
}

// Auto-refresh page every 30 seconds to check for new payments
setInterval(function() {
    if (!document.querySelector('.modal.show')) {
        window.location.reload();
    }
}, 30000);
</script>
@endpush
@endsection
