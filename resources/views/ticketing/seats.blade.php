@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('ticketing.routes') }}">Routes</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ticketing.schedules', $schedule->route) }}">Schedules</a></li>
                    <li class="breadcrumb-item active">Seat Selection</li>
                </ol>
            </nav>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bookmark me-2"></i>Select Your Seat
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Bus Layout -->
                            <div class="bus-layout bg-light p-4 rounded">
                                <div class="text-center mb-3">
                                    <div class="badge bg-secondary px-3 py-2">
                                        <i class="bi bi-steering-wheel me-2"></i>Driver
                                    </div>
                                </div>
                                
                                <div class="seats-container">
                                    <div class="row g-2">
                                        @php
                                            $seatRows = $seats->chunk(4); // 4 seats per row (2 left, 2 right)
                                        @endphp
                                        
                                        @foreach($seatRows as $rowIndex => $seatRow)
                                        <div class="col-12 mb-2">
                                            <div class="d-flex justify-content-center gap-2">
                                                <div class="d-flex gap-1">
                                                    @foreach($seatRow->take(2) as $seat)
                                                    <button class="btn seat-btn {{ $seat->is_available ? 'btn-outline-success' : 'btn-secondary' }}" 
                                                            data-seat-id="{{ $seat->id }}" 
                                                            data-seat-number="{{ $seat->seat_number }}"
                                                            {{ !$seat->is_available ? 'disabled' : '' }}>
                                                        {{ $seat->seat_number }}
                                                    </button>
                                                    @endforeach
                                                </div>
                                                
                                                <!-- Aisle -->
                                                <div style="width: 30px;"></div>
                                                
                                                <div class="d-flex gap-1">
                                                    @foreach($seatRow->skip(2)->take(2) as $seat)
                                                    <button class="btn seat-btn {{ $seat->is_available ? 'btn-outline-success' : 'btn-secondary' }}" 
                                                            data-seat-id="{{ $seat->id }}" 
                                                            data-seat-number="{{ $seat->seat_number }}"
                                                            {{ !$seat->is_available ? 'disabled' : '' }}>
                                                        {{ $seat->seat_number }}
                                                    </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Legend -->
                                <div class="mt-4 d-flex justify-content-center gap-4">
                                    <div class="d-flex align-items-center">
                                        <button class="btn btn-outline-success btn-sm me-2" disabled></button>
                                        <small>Available</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <button class="btn btn-secondary btn-sm me-2" disabled></button>
                                        <small>Occupied</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <button class="btn btn-primary btn-sm me-2" disabled></button>
                                        <small>Selected</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-info-circle me-2"></i>Trip Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Route:</strong><br>
                                {{ $schedule->route->origin }} → {{ $schedule->route->destination }}
                            </div>
                              <div class="mb-3">
                                <strong>Date:</strong><br>
                                {{ \Carbon\Carbon::parse($travelDate)->format('l, F d, Y') }}
                            </div>
                            
                            <div class="mb-3">
                                <strong>Passengers:</strong><br>
                                {{ $seatCount }} {{ $seatCount > 1 ? 'passengers' : 'passenger' }}
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>Departure:</strong><br>
                                    {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}
                                </div>
                                <div class="col-6">
                                    <strong>Arrival:</strong><br>
                                    {{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}
                                </div>
                            </div>

                            @if($schedule->bus_number)
                            <div class="mb-3">
                                <strong>Bus Number:</strong><br>
                                {{ $schedule->bus_number }}
                            </div>
                            @endif

                            <div class="mb-4">
                                <strong>Price:</strong><br>
                                <span class="h4 text-success">Rp {{ number_format($schedule->price, 0, ',', '.') }}</span>
                            </div>                            <div id="selected-seat-info" class="alert alert-info" style="display: none;">
                                <strong>Selected Seats:</strong> <span id="selected-seat-numbers"></span><br>
                                <small><span id="selected-count">0</span> of {{ $seatCount }} seats selected</small>
                            </div>

                            <div class="d-grid">
                                <button id="proceed-btn" class="btn btn-primary btn-lg" disabled>
                                    <i class="bi bi-arrow-right me-2"></i>Proceed to Booking
                                </button>
                            </div>
                        </div>
                    </div>

                    @if($schedule->available_seats <= 5)
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Only {{ $schedule->available_seats }} seats left!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.seat-btn {
    width: 45px;
    height: 45px;
    font-size: 12px;
    font-weight: bold;
}

.bus-layout {
    max-width: 400px;
    margin: 0 auto;
}

.seats-container {
    border: 2px solid #dee2e6;
    border-radius: 20px;
    padding: 20px 10px;
    background: white;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const seatButtons = document.querySelectorAll('.seat-btn:not([disabled])');
    const proceedBtn = document.getElementById('proceed-btn');
    const selectedSeatInfo = document.getElementById('selected-seat-info');
    const selectedSeatNumbers = document.getElementById('selected-seat-numbers');
    const selectedCountSpan = document.getElementById('selected-count');
    const maxSeats = {{ $seatCount }};
    let selectedSeats = [];

    seatButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const seatId = this.dataset.seatId;
            const seatNumber = this.dataset.seatNumber;
            
            // Check if seat is already selected
            const seatIndex = selectedSeats.findIndex(seat => seat.id === seatId);
            
            if (seatIndex > -1) {
                // Deselect seat
                selectedSeats.splice(seatIndex, 1);
                this.classList.remove('btn-primary');
                this.classList.add('btn-outline-success');
            } else {
                // Check if we can select more seats
                if (selectedSeats.length >= maxSeats) {
                    alert(`You can only select ${maxSeats} seat${maxSeats > 1 ? 's' : ''}.`);
                    return;
                }
                
                // Select seat
                selectedSeats.push({
                    id: seatId,
                    number: seatNumber
                });
                this.classList.remove('btn-outline-success');
                this.classList.add('btn-primary');
            }
            
            // Update display
            updateSelectedSeatsDisplay();
        });
    });
    
    function updateSelectedSeatsDisplay() {
        if (selectedSeats.length > 0) {
            const seatNumbers = selectedSeats.map(seat => seat.number).join(', ');
            selectedSeatNumbers.textContent = seatNumbers;
            selectedCountSpan.textContent = selectedSeats.length;
            selectedSeatInfo.style.display = 'block';
            proceedBtn.disabled = selectedSeats.length !== maxSeats;
        } else {
            selectedSeatInfo.style.display = 'none';
            proceedBtn.disabled = true;
        }
    }    proceedBtn.addEventListener('click', function() {
        if (selectedSeats.length === maxSeats) {
            // For multiple seats, we'll redirect to a booking page that handles multiple seats
            if (selectedSeats.length === 1) {
                window.location.href = `/ticketing/booking/{{ $schedule->id }}/${selectedSeats[0].id}?travel_date={{ $travelDate }}`;
            } else {
                // For multiple seats, pass seat IDs as comma-separated string
                const seatIds = selectedSeats.map(seat => seat.id).join(',');
                window.location.href = `/ticketing/booking/{{ $schedule->id }}?seats=${seatIds}&travel_date={{ $travelDate }}`;
            }
        }
    });
});
</script>
@endpush
@endsection
