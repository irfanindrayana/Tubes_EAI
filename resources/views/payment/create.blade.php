@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('ticketing.my-bookings') }}">My Bookings</a></li>
                    <li class="breadcrumb-item active">Payment</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-credit-card me-2"></i>Complete Payment
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Booking Summary -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Booking Summary</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Booking Code:</strong> {{ $booking->booking_code }}</p>
                                    <p><strong>Route:</strong> {{ $booking->schedule->route->origin }} → {{ $booking->schedule->route->destination }}</p>
                                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('l, F d, Y') }}</p>
                                    <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->schedule->arrival_time)->format('H:i') }}</p>
                                </div>                                <div class="col-md-6">
                                    @if($booking->seat_count == 1)
                                        <p><strong>Passenger:</strong> {{ $booking->passenger_details[0]['name'] ?? 'N/A' }}</p>
                                        <p><strong>Seat:</strong> {{ $booking->seat_numbers[0] ?? 'N/A' }}</p>
                                    @else
                                        <p><strong>Passengers:</strong> {{ $booking->seat_count }}</p>
                                        <p><strong>Seats:</strong> {{ implode(', ', $booking->seat_numbers) }}</p>
                                    @endif
                                    @if($booking->schedule->bus_number)
                                    <p><strong>Bus Number:</strong> {{ $booking->schedule->bus_number }}</p>
                                    @endif
                                    <p><strong>Total Amount:</strong> <span class="h4 text-success">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form action="{{ route('payment.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                        <div class="mb-4">
                            <label class="form-label">Select Payment Method <span class="text-danger">*</span></label>
                            <div class="row">
                                @foreach($paymentMethods as $method)
                                <div class="col-md-6 mb-3">
                                    <div class="card payment-method-card" style="cursor: pointer;">
                                        <div class="card-body text-center">
                                            <input type="radio" name="payment_method_id" value="{{ $method->id }}" 
                                                   id="method_{{ $method->id }}" class="form-check-input d-none" required>
                                            <label for="method_{{ $method->id }}" class="w-100 h-100 d-flex flex-column justify-content-center" style="cursor: pointer;">
                                                <i class="bi bi-{{ $method->method_type === 'bank_transfer' ? 'bank' : ($method->method_type === 'e_wallet' ? 'wallet2' : 'credit-card') }} fs-1 mb-2"></i>
                                                <h6>{{ $method->name }}</h6>
                                                <small class="text-muted">{{ $method->description }}</small>
                                                @if($method->account_number)
                                                    <small class="fw-bold mt-1">{{ $method->account_number }}</small>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('payment_method_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Payment Instructions -->
                        <div id="payment-instructions" class="alert alert-info" style="display: none;">
                            <h6><i class="bi bi-info-circle me-2"></i>Payment Instructions:</h6>
                            <ol id="instruction-list">
                                <!-- Will be populated by JavaScript -->
                            </ol>
                        </div>

                        <!-- Payment Proof Upload -->
                        <div class="mb-4">
                            <label for="payment_proof" class="form-label">Upload Payment Proof</label>
                            <input type="file" class="form-control @error('payment_proof') is-invalid @enderror" 
                                   id="payment_proof" name="payment_proof" accept="image/*">
                            <div class="form-text">Upload a clear image of your payment receipt/screenshot (JPG, PNG, max 2MB)</div>
                            @error('payment_proof')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div id="image-preview" class="mb-4" style="display: none;">
                            <label class="form-label">Preview:</label>
                            <div class="text-center">
                                <img id="preview-img" src="" alt="Payment Proof Preview" class="img-thumbnail" style="max-height: 300px;">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirm_payment" required>
                                <label class="form-check-label" for="confirm_payment">
                                    I confirm that I have completed the payment and uploaded the correct proof <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('ticketing.my-bookings') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Bookings
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Submit Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="alert alert-warning mt-4">
                <h6><i class="bi bi-exclamation-triangle me-2"></i>Important Notes:</h6>
                <ul class="mb-0">
                    <li>Complete payment within 1 hour to secure your booking</li>
                    <li>Payment verification may take 1-2 hours during business hours</li>
                    <li>Keep your payment receipt for reference</li>
                    <li>Contact customer service if you encounter any issues</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.payment-method-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.payment-method-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
}

.payment-method-card.selected {
    border-color: #007bff;
    background-color: #f8f9fa;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = @json($paymentMethods);
    const paymentCards = document.querySelectorAll('.payment-method-card');
    const paymentInstructions = document.getElementById('payment-instructions');
    const instructionList = document.getElementById('instruction-list');
    const paymentProofInput = document.getElementById('payment_proof');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');

    // Payment method selection
    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove previous selection
            paymentCards.forEach(c => c.classList.remove('selected'));
            
            // Select current card
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;

            // Show instructions
            const methodId = radio.value;
            const method = paymentMethods.find(m => m.id == methodId);
            
            if (method) {
                showPaymentInstructions(method);
            }
        });
    });

    // Image preview
    paymentProofInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });

    function showPaymentInstructions(method) {
        let instructions = [];
        
        if (method.method_type === 'bank_transfer') {
            instructions = [
                `Transfer the exact amount of Rp {{ number_format($booking->total_amount, 0, ',', '.') }} to:`,
                `Bank: ${method.name}`,
                `Account Number: ${method.account_number || 'Will be provided'}`,
                `Account Name: Bus Trans Bandung`,
                `Reference: ${method.description || 'Bus Ticket Payment'}`,
                'Take a screenshot or photo of the transfer receipt',
                'Upload the payment proof above'
            ];
        } else if (method.method_type === 'e_wallet') {
            instructions = [
                `Send payment of Rp {{ number_format($booking->total_amount, 0, ',', '.') }} to:`,
                `${method.name}: ${method.account_number || 'Will be provided'}`,
                `Message/Note: Bus Ticket - {{ $booking->booking_code }}`,
                'Take a screenshot of the successful transaction',
                'Upload the payment proof above'
            ];
        } else {
            instructions = [
                'Follow the payment gateway instructions',
                'Complete the payment process',
                'Take a screenshot of the payment confirmation',
                'Upload the payment proof above'
            ];
        }

        instructionList.innerHTML = instructions.map(inst => `<li>${inst}</li>`).join('');
        paymentInstructions.style.display = 'block';
    }
});
</script>
@endpush
@endsection
