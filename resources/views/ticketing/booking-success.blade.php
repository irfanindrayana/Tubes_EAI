@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h2 class="text-success mt-3">Booking Successful!</h2>
                <p class="text-muted">Your ticket has been reserved. Please complete the payment to confirm your booking.</p>
            </div>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-ticket-perforated me-2"></i>Booking Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Booking Code:</strong>
                            <div class="h4 text-primary">{{ $booking->booking_code }}</div>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <div>
                                <span class="badge bg-warning">{{ ucfirst($booking->status) }}</span>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Trip Information</h6>
                            <p><strong>Route:</strong> {{ $booking->schedule->route->origin }} → {{ $booking->schedule->route->destination }}</p>
                            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('l, F d, Y') }}</p>
                            <p><strong>Departure:</strong> {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('H:i') }}</p>
                            <p><strong>Arrival:</strong> {{ \Carbon\Carbon::parse($booking->schedule->arrival_time)->format('H:i') }}</p>
                            @if($booking->schedule->bus_number)
                            <p><strong>Bus Number:</strong> {{ $booking->schedule->bus_number }}</p>
                            @endif
                        </div>                        <div class="col-md-6">
                            <h6>Passenger Information</h6>
                            @if($booking->seat_count == 1)
                                <p><strong>Name:</strong> {{ $booking->passenger_details[0]['name'] }}</p>
                                <p><strong>Phone:</strong> {{ $booking->passenger_details[0]['phone'] }}</p>
                                <p><strong>Seat Number:</strong> {{ $booking->seat_numbers[0] }}</p>
                            @else
                                <p><strong>Passengers:</strong> {{ $booking->seat_count }}</p>
                                <div class="border rounded p-2 mb-2" style="max-height: 150px; overflow-y: auto;">
                                    @foreach($booking->passenger_details as $index => $passenger)
                                        <div class="mb-1">
                                            <small><strong>{{ $passenger['name'] }}</strong> - Seat {{ $booking->seat_numbers[$index] ?? $passenger['seat_number'] }}</small><br>
                                            <small class="text-muted">{{ $passenger['phone'] }}</small>
                                        </div>
                                        @if(!$loop->last)<hr class="my-1">@endif
                                    @endforeach
                                </div>
                            @endif
                            <p><strong>Total Price:</strong> <span class="h5 text-success">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-credit-card text-primary" style="font-size: 2rem;"></i>
                            <h5 class="mt-3">Complete Payment</h5>
                            <p class="text-muted">Pay now to confirm your booking</p>
                            <a href="{{ route('payment.create', $booking) }}" class="btn btn-primary">
                                <i class="bi bi-arrow-right me-2"></i>Pay Now
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-list-check text-secondary" style="font-size: 2rem;"></i>
                            <h5 class="mt-3">View My Bookings</h5>
                            <p class="text-muted">Check all your bookings and their status</p>
                            <a href="{{ route('ticketing.my-bookings') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-list me-2"></i>My Bookings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4">
                <h6><i class="bi bi-info-circle me-2"></i>Important Notes:</h6>
                <ul class="mb-0">
                    <li>Please complete payment within 1 hour to secure your booking</li>
                    <li>Your booking code is: <strong>{{ $booking->booking_code }}</strong></li>
                    <li>Arrive at the departure point 15 minutes before departure time</li>
                    <li>Bring valid identification documents</li>
                </ul>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('ticketing.routes') }}" class="btn btn-outline-primary me-3">
                    <i class="bi bi-arrow-left me-2"></i>Book Another Ticket
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-house me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
