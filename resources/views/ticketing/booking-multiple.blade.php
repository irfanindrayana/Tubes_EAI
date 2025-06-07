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
                    <!-- Trip Summary -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Trip Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Route:</strong> {{ $schedule->route->origin }} → {{ $schedule->route->destination }}</p>
                                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($schedule->departure_time)->format('l, F d, Y') }}</p>
                                    <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Selected Seats:</strong> {{ $seats->pluck('seat_number')->join(', ') }}</p>
                                    @if($schedule->bus_number)
                                    <p><strong>Bus Number:</strong> {{ $schedule->bus_number }}</p>
                                    @endif
                                    <p><strong>Total Price:</strong> <span class="h5 text-success">Rp {{ number_format($schedule->price * $seats->count(), 0, ',', '.') }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Form -->                    <form action="{{ route('ticketing.process-booking') }}" method="POST">
                        @csrf
                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                        <input type="hidden" name="seat_ids" value="{{ $seats->pluck('id')->join(',') }}">
                        <input type="hidden" name="travel_date" value="{{ $travelDate }}">
                        <input type="hidden" name="is_multiple" value="1">

                        <h5 class="mb-3">Passenger Information</h5>
                        
                        @foreach($seats as $index => $seat)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Passenger {{ $index + 1 }} - Seat {{ $seat->seat_number }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="passenger_name_{{ $index }}" class="form-label">Passenger Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error("passengers.{$index}.name") is-invalid @enderror" 
                                               id="passenger_name_{{ $index }}" name="passengers[{{ $index }}][name]" 
                                               value="{{ old("passengers.{$index}.name", $index == 0 ? Auth::user()->name : '') }}" required>
                                        @error("passengers.{$index}.name")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="passenger_phone_{{ $index }}" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control @error("passengers.{$index}.phone") is-invalid @enderror" 
                                               id="passenger_phone_{{ $index }}" name="passengers[{{ $index }}][phone]" 
                                               value="{{ old("passengers.{$index}.phone", $index == 0 ? Auth::user()->phone : '') }}" required>
                                        @error("passengers.{$index}.phone")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <input type="hidden" name="passengers[{{ $index }}][seat_id]" value="{{ $seat->id }}">
                                <input type="hidden" name="passengers[{{ $index }}][seat_number]" value="{{ $seat->seat_number }}">
                            </div>
                        </div>
                        @endforeach

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
                                <i class="bi bi-credit-card me-2"></i>Complete Booking
                                (Rp {{ number_format($schedule->price * $seats->count(), 0, ',', '.') }})
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Booking Terms</h6>
                <ul>
                    <li>All bookings are subject to availability</li>
                    <li>Please arrive at the departure point 15 minutes before departure time</li>
                    <li>Cancellation is allowed up to 2 hours before departure</li>
                    <li>Refunds will be processed within 3-5 business days</li>
                    <li>Valid ID is required during travel</li>
                </ul>
                
                <h6>Payment Terms</h6>
                <ul>
                    <li>Payment must be completed within 24 hours of booking</li>
                    <li>Unpaid bookings will be automatically cancelled</li>
                    <li>Payment confirmation is required before travel</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Agree</button>
            </div>
        </div>
    </div>
</div>
@endsection
