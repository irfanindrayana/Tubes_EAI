@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('payment.my-payments') }}">My Payments</a></li>
                    <li class="breadcrumb-item active">Payment Status</li>
                </ol>
            </nav>

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

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-receipt me-2"></i>Payment Status
                    </h4>
                    <span class="badge fs-6 {{ $payment->status === 'verified' ? 'bg-success' : ($payment->status === 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <!-- Status Alert -->
                    @if($payment->status === 'pending')
                        <div class="alert alert-warning">
                            <i class="bi bi-clock me-2"></i>
                            <strong>Payment Under Review</strong><br>
                            Your payment is being verified by our team. This usually takes 1-2 hours during business hours.
                        </div>
                    @elseif($payment->status === 'verified')
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Payment Verified!</strong><br>
                            Your payment has been confirmed and your booking is now active.
                            @if($payment->verified_by)
                                <br><small>Verified by: {{ $payment->verifiedBy->name }} on {{ $payment->verified_at->format('M d, Y H:i') }}</small>
                            @endif
                        </div>
                    @elseif($payment->status === 'rejected')
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle me-2"></i>                            <strong>Payment Rejected</strong><br>
                            Your payment could not be verified. Please check your payment proof and try again.
                            @if($payment->admin_notes)
                                <br><strong>Reason:</strong> {{ $payment->admin_notes }}
                            @endif
                        </div>
                    @endif

                    <!-- Payment Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Payment Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Payment ID:</strong></td>
                                    <td>{{ $payment->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td class="h5 text-success">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>{{ $payment->paymentMethod->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Date:</strong></td>
                                    <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @if($payment->verified_at)
                                <tr>
                                    <td><strong>Verified Date:</strong></td>
                                    <td>{{ $payment->verified_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6>Booking Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Booking Code:</strong></td>
                                    <td>{{ $payment->booking->booking_code }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Route:</strong></td>
                                    <td>{{ $payment->booking->schedule->route->origin }} → {{ $payment->booking->schedule->route->destination }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($payment->booking->schedule->departure_time)->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Time:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($payment->booking->schedule->departure_time)->format('H:i') }}</td>
                                </tr>                                <tr>
                                    <td><strong>Seat{{ $payment->booking->seat_count > 1 ? 's' : '' }}:</strong></td>
                                    <td>
                                        @if($payment->booking->seat_count == 1)
                                            {{ $payment->booking->seat_numbers[0] ?? 'N/A' }}
                                        @else
                                            {{ implode(', ', $payment->booking->seat_numbers) }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>                    <!-- Payment Proof -->
                    @if($payment->proof_image)
                    <div class="mt-4">
                        <h6>Payment Proof</h6>
                        <div class="text-center">
                            @php
                                // Check if the proof_image is a URL or a file path
                                if (filter_var($payment->proof_image, FILTER_VALIDATE_URL)) {
                                    $imageUrl = $payment->proof_image;
                                } elseif (str_starts_with($payment->proof_image, 'data:image')) {
                                    $imageUrl = $payment->proof_image;
                                } elseif (file_exists(storage_path('app/public/' . $payment->proof_image))) {
                                    $imageUrl = Storage::url($payment->proof_image);
                                } elseif (file_exists(public_path($payment->proof_image))) {
                                    $imageUrl = asset($payment->proof_image);
                                } else {
                                    $imageUrl = null;
                                }
                            @endphp
                            
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" 
                                     alt="Payment Proof" 
                                     class="img-thumbnail"
                                     style="max-height: 400px; cursor: pointer;"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#proofModal">
                                <div class="mt-2">
                                    <small class="text-muted">Click to view full size</small>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Payment proof image not found. File: {{ $payment->proof_image }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif<!-- Admin Notes -->
                    @if($payment->admin_notes)
                    <div class="mt-4">
                        <h6>Admin Notes</h6>
                        <div class="alert alert-info">
                            {{ $payment->admin_notes }}
                        </div>
                    </div>
                    @endif
                </div>

                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('payment.my-payments') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Payments
                            </a>
                            <a href="{{ route('ticketing.my-bookings') }}" class="btn btn-outline-primary">
                                <i class="bi bi-list-check me-2"></i>My Bookings
                            </a>
                        </div>

                        @if($payment->status === 'rejected')
                            <a href="{{ route('payment.create', $payment->booking) }}" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat me-2"></i>Submit New Payment
                            </a>
                        @elseif($payment->status === 'verified')
                            <button class="btn btn-success" onclick="generateTicket()">
                                <i class="bi bi-printer me-2"></i>Print Ticket
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            @if($payment->status === 'verified')
            <!-- E-Ticket -->
            <div class="card mt-4" id="e-ticket">
                <div class="card-header bg-success text-white text-center">
                    <h5 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i>Bus Trans Bandung - E-Ticket</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>Booking Code</strong><br>
                                    <span class="h4 text-primary">{{ $payment->booking->booking_code }}</span>
                                </div>                                <div class="col-6">
                                    <strong>Passenger{{ $payment->booking->seat_count > 1 ? 's' : '' }}</strong><br>
                                    @if($payment->booking->seat_count == 1)
                                        {{ $payment->booking->passenger_details[0]['name'] ?? 'N/A' }}
                                    @else
                                        {{ $payment->booking->seat_count }} passengers
                                    @endif
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>From</strong><br>
                                    {{ $payment->booking->schedule->route->origin }}
                                </div>
                                <div class="col-6">
                                    <strong>To</strong><br>
                                    {{ $payment->booking->schedule->route->destination }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>Date & Time</strong><br>
                                    {{ \Carbon\Carbon::parse($payment->booking->schedule->departure_time)->format('l, F d, Y') }}<br>
                                    {{ \Carbon\Carbon::parse($payment->booking->schedule->departure_time)->format('H:i') }}
                                </div>                                <div class="col-6">
                                    <strong>Seat Number{{ $payment->booking->seat_count > 1 ? 's' : '' }}</strong><br>
                                    @if($payment->booking->seat_count == 1)
                                        <span class="h4">{{ $payment->booking->seat_numbers[0] ?? 'N/A' }}</span>
                                    @else
                                        <span class="h4">{{ implode(', ', $payment->booking->seat_numbers) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-center">
                            <div id="qr-code" class="mb-3"></div>
                            <small class="text-muted">Show this QR code to the conductor</small>
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <small><strong>Bus Number:</strong> {{ $payment->booking->schedule->bus_number ?? 'TBD' }}</small>
                            </div>
                            <div class="col-md-6 text-end">
                                <small><strong>Total Paid:</strong> Rp {{ number_format($payment->amount, 0, ',', '.') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Payment Proof Modal -->
@if($payment->proof_image)
<div class="modal fade" id="proofModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Proof</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                @php
                    // Use the same logic as above for the modal
                    if (filter_var($payment->proof_image, FILTER_VALIDATE_URL)) {
                        $modalImageUrl = $payment->proof_image;
                    } elseif (str_starts_with($payment->proof_image, 'data:image')) {
                        $modalImageUrl = $payment->proof_image;
                    } elseif (file_exists(storage_path('app/public/' . $payment->proof_image))) {
                        $modalImageUrl = Storage::url($payment->proof_image);
                    } elseif (file_exists(public_path($payment->proof_image))) {
                        $modalImageUrl = asset($payment->proof_image);
                    } else {
                        $modalImageUrl = null;
                    }
                @endphp
                
                @if($modalImageUrl)
                    <img src="{{ $modalImageUrl }}" alt="Payment Proof" class="img-fluid">
                @else
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Payment proof image not found.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
@if($payment->status === 'verified')
// Generate QR Code
QRCode.toCanvas(document.getElementById('qr-code'), '{{ $payment->booking->booking_code }}', {
    width: 128,
    margin: 2
}, function (error) {
    if (error) console.error(error);
});

function generateTicket() {
    const ticketElement = document.getElementById('e-ticket');
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Bus Trans Bandung - E-Ticket</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        body { font-size: 12px; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                ${ticketElement.outerHTML}
                <script>window.print();</script>
            </body>
        </html>
    `);
    printWindow.document.close();
}
@endif
</script>
@endpush
@endsection
