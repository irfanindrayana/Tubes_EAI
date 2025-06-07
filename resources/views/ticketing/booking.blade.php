@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('ticketing.routes') }}">Routes</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ticketing.schedules', $schedule->route) }}">Schedules</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ticketing.seats', $schedule) }}">Seats</a></li>
                    <li class="breadcrumb-item active">Booking</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-ticket-perforated me-2"></i>Complete Your Booking
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Trip Summary -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Trip Summary</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Route:</strong> {{ $schedule->route->origin }} → {{ $schedule->route->destination }}</p>
                                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($schedule->departure_time)->format('l, F d, Y') }}</p>
                                    <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Seat Number:</strong> {{ $seat->seat_number }}</p>
                                    @if($schedule->bus_number)
                                    <p><strong>Bus Number:</strong> {{ $schedule->bus_number }}</p>
                                    @endif
                                    <p><strong>Price:</strong> <span class="h5 text-success">Rp {{ number_format($schedule->price, 0, ',', '.') }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Form -->                    <form action="{{ route('ticketing.process-booking') }}" method="POST">
                        @csrf
                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                        <input type="hidden" name="seat_id" value="{{ $seat->id }}">
                        <input type="hidden" name="travel_date" value="{{ $travelDate }}">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="passenger_name" class="form-label">Passenger Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('passenger_name') is-invalid @enderror" 
                                       id="passenger_name" name="passenger_name" value="{{ old('passenger_name', Auth::user()->name) }}" required>
                                @error('passenger_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="passenger_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('passenger_phone') is-invalid @enderror" 
                                       id="passenger_phone" name="passenger_phone" value="{{ old('passenger_phone', Auth::user()->phone) }}" required>
                                @error('passenger_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('ticketing.seats', $schedule) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Seat Selection
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Confirm Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Bus Trans Bandung - Terms and Conditions</h6>
                <div class="mt-3">
                    <h6>1. Booking Policy</h6>
                    <ul>
                        <li>All bookings are subject to seat availability</li>
                        <li>Booking confirmation will be sent via email/SMS</li>
                        <li>Payment must be completed within 1 hour of booking</li>
                    </ul>

                    <h6>2. Cancellation Policy</h6>
                    <ul>
                        <li>Cancellations are allowed up to 2 hours before departure</li>
                        <li>Refund processing may take 3-5 business days</li>
                        <li>Cancellation fees may apply</li>
                    </ul>

                    <h6>3. Travel Guidelines</h6>
                    <ul>
                        <li>Arrive at the departure point 15 minutes early</li>
                        <li>Bring valid identification documents</li>
                        <li>Follow health and safety protocols</li>
                    </ul>

                    <h6>4. Liability</h6>
                    <ul>
                        <li>Bus Trans Bandung is not liable for delays due to traffic or weather</li>
                        <li>Passengers are responsible for their personal belongings</li>
                        <li>Insurance coverage as per company policy</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
