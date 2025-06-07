@extends('layouts.app')

@section('styles')
<style>
    /* Custom styles for location dropdowns */
    .location-container {
        position: relative;
    }
    
    .location-input {
        height: 58px;
    }
    
    .location-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 0 0 8px 8px;
        z-index: 1000;
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }
    
    .location-dropdown.show {
        display: block;
    }
    
    .location-item {
        padding: 10px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f1f1f1;
        display: flex;
        align-items: center;
    }
    
    .location-item:hover, .location-item.active {
        background-color: #f8f9fa;
    }
    
    .location-item .location-icon {
        margin-right: 10px;
        color: #0d6efd;
    }
    
    .location-item:last-child {
        border-bottom: none;
    }
    
    /* Ensure the switch button is properly aligned */
    #switchLocations {
        margin-top: 10px;
        height: 58px;
        width: 42px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }
    
    /* Style for 'no results' message */
    .no-results {
        padding: 12px 16px;
        color: #6c757d;
        text-align: center;
        font-style: italic;
    }
    
    /* Schedule card animations and hover effects */
    .route-card {
        transition: all 0.3s ease;
    }
    
    .route-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }
    
    .schedule-card {
        transition: all 0.2s ease;
        min-height: 200px;
    }
    
    .schedule-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .schedule-card .card-header {
        border-radius: 0.375rem 0.375rem 0 0;
    }
    
    /* Status indicators */
    .status-available {
        background: linear-gradient(45deg, #28a745, #20c997);
    }
    
    .status-unavailable {
        background: linear-gradient(45deg, #6c757d, #5a6268);
    }
    
    /* Price highlight */
    .price-highlight {
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    /* Seat availability colors */
    .seats-high { color: #28a745; }
    .seats-medium { color: #ffc107; }
    .seats-low { color: #fd7e14; }
    .seats-none { color: #dc3545; }
    
    /* Button improvements */
    .btn-book-now {
        background: linear-gradient(45deg, #28a745, #20c997);
        border: none;
        transition: all 0.2s ease;
    }
    
    .btn-book-now:hover {
        background: linear-gradient(45deg, #20c997, #28a745);
        transform: translateY(-1px);
    }
</style>
</style>
@endsection

@section('content')
<div class="hero-section mb-4">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-lg-6">
                <h1 class="display-5 fw-bold mb-4">Yuk, cari tiket bus dan travel terbaik untuk kebutuhanmu.</h1>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white p-2 me-3">
                            <img src="https://www.svgrepo.com/show/533292/ticket.svg" alt="Ticket Icon" width="24" height="24">
                        </div>
                        <h5 class="mb-0">Partner Resmi dan Terpercaya. Tiket dijamin resmi, bebas khawatir!</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-end">
                        <div class="col-lg-12">
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-search me-2"></i>Cari tiket untuk perjalanan Anda
                            </h5>
                        </div>
                    </div>

                    <form action="{{ route('ticketing.routes') }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <!-- Tab Nav -->
                            <div class="col-12 mb-2">
                                <ul class="nav nav-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active px-4 fw-bold" href="#" id="sekaliJalan">
                                            Sekali jalan
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link px-4" href="#" id="pulangPergi">
                                            Pulang pergi
                                        </a>
                                    </li>
                                </ul>
                            </div>                            <!-- Origin -->
                            <div class="col-md-3">
                                <div class="form-floating location-container position-relative">
                                    <input type="text" class="form-control location-input" id="origin" name="origin" 
                                        placeholder="Pilih kota asal" autocomplete="off"
                                        value="{{ request('origin') }}">
                                    <label for="origin">
                                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>From
                                    </label>
                                    <div id="originDropdown" class="location-dropdown shadow-sm"></div>
                                </div>
                            </div>
                            
                            <!-- Switch button -->
                            <div class="col-auto d-none d-md-block">
                                <button type="button" class="btn btn-outline-secondary h-100" id="switchLocations">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            </div>
                            
                            <!-- Destination -->
                            <div class="col-md-3">
                                <div class="form-floating location-container position-relative">
                                    <input type="text" class="form-control location-input" id="destination" name="destination" 
                                        placeholder="Pilih kota tujuan" autocomplete="off"
                                        value="{{ request('destination') }}">
                                    <label for="destination">
                                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>To
                                    </label>
                                    <div id="destinationDropdown" class="location-dropdown shadow-sm"></div>
                                </div>
                            </div>
                            
                            <!-- Travel Date -->
                            <div class="col-md-2">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="travel_date" name="travel_date" value="{{ request('travel_date', now()->format('Y-m-d')) }}">
                                    <label for="travel_date">
                                        <i class="bi bi-calendar-event text-primary me-2"></i>Date
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Passenger Count -->
                            <div class="col-md-2">
                                <div class="form-floating">
                                    <select class="form-select" id="seat_count" name="seat_count">
                                        @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ request('seat_count', 1) == $i ? 'selected' : '' }}>{{ $i }} Kursi</option>
                                        @endfor
                                    </select>
                                    <label for="seat_count">
                                        <i class="bi bi-person text-primary me-2"></i>Passengers
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Search Button -->
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100 py-3">
                                    <i class="bi bi-search me-2"></i>Cari bus dan travel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
              <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="bi bi-geo-alt me-2"></i>Available Routes & Schedules</h2>
                    @if($origin || $destination || $travelDate)
                    <p class="text-muted mb-0">
                        Search results for:
                        @if($origin) from <strong>{{ $origin }}</strong>@endif
                        @if($destination) to <strong>{{ $destination }}</strong>@endif
                        @if($travelDate) on <strong>{{ \Carbon\Carbon::parse($travelDate)->format('l, F d, Y') }}</strong>@endif
                        @if($seatCount > 1) for <strong>{{ $seatCount }} passengers</strong>@endif
                    </p>
                    @endif
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
            @endif            <div class="row">
                @forelse($routes as $route)
                <div class="col-12 mb-4">
                    <div class="card shadow-sm route-card">
                        <div class="card-header bg-primary text-white">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-arrow-right me-2"></i>
                                        {{ $route->origin }} → {{ $route->destination }}
                                    </h5>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge {{ $route->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $route->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="badge bg-info">
                                        <i class="bi bi-calendar2-check me-1"></i>{{ $route->schedules_count }} trips
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Route Info Row -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <small class="text-muted">
                                        <i class="bi bi-rulers me-1"></i>Distance
                                    </small>
                                    <div class="fw-bold">{{ $route->distance ?? '0' }} km</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>Duration
                                    </small>
                                    <div class="fw-bold">{{ $route->estimated_duration ?? '60' }} min</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">
                                        <i class="bi bi-tag me-1"></i>Starting Price
                                    </small>
                                    <div class="h6 text-success mb-0">
                                        Rp {{ number_format($route->base_price, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    @if($route->is_active)
                                        <a href="{{ route('ticketing.schedules', $route) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-calendar-check me-1"></i>All Schedules
                                        </a>
                                    @else
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="bi bi-x-circle me-1"></i>Unavailable
                                        </button>
                                    @endif
                                </div>
                            </div>

                            @if($route->description)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>Description
                                    </small>
                                    <p class="small mb-0">{{ $route->description }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Matching Schedules -->
                            @if($route->schedules->count() > 0)
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bi bi-calendar-check me-2"></i>Available Schedules
                                        @if($travelDate)
                                            for {{ \Carbon\Carbon::parse($travelDate)->format('l, F d, Y') }}
                                        @endif
                                    </h6>
                                    <div class="row">                                        @foreach($route->schedules as $schedule)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card border schedule-card {{ $schedule->status == 'available' ? 'border-success' : 'border-secondary' }}">
                                                <div class="card-header {{ $schedule->status == 'available' ? 'status-available' : 'status-unavailable' }} text-white py-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-0">
                                                                <i class="bi bi-clock me-1"></i>
                                                                {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}
                                                            </h6>
                                                            <small>
                                                                {{ \Carbon\Carbon::parse($schedule->departure_time)->format('M d, Y') }}
                                                            </small>
                                                        </div>
                                                        @if($schedule->status == 'available')
                                                            <i class="bi bi-check-circle-fill"></i>
                                                        @else
                                                            <i class="bi bi-x-circle-fill"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="card-body py-2">
                                                    <div class="row mb-2">
                                                        <div class="col-6">
                                                            <small class="text-muted">Departure</small>
                                                            <div class="fw-bold small">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted">Arrival</small>
                                                            <div class="fw-bold small">{{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-2">
                                                        <div class="col-6">
                                                            <small class="text-muted">Available Seats</small>
                                                            <div class="fw-bold small 
                                                                @if($schedule->available_seats > 10) seats-high
                                                                @elseif($schedule->available_seats > 5) seats-medium  
                                                                @elseif($schedule->available_seats > 0) seats-low
                                                                @else seats-none
                                                                @endif">
                                                                {{ $schedule->available_seats }}
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted">Price</small>
                                                            <div class="fw-bold small price-highlight text-success">
                                                                Rp {{ number_format($schedule->price, 0, ',', '.') }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if($schedule->bus_number)
                                                    <div class="mb-2">
                                                        <small class="text-muted">Bus</small>
                                                        <div class="fw-bold small">
                                                            <i class="bi bi-bus-front me-1"></i>{{ $schedule->bus_number }}
                                                        </div>
                                                    </div>
                                                    @endif                                                    <div class="d-grid">
                                                        @if($schedule->status == 'available' && $schedule->available_seats >= ($seatCount ?? 1))
                                                            <a href="{{ route('ticketing.seats', $schedule) }}?seat_count={{ $seatCount ?? 1 }}&travel_date={{ $travelDate ?? now()->format('Y-m-d') }}" class="btn btn-success btn-sm btn-book-now">
                                                                <i class="bi bi-ticket me-1"></i>Book Now
                                                            </a>
                                                        @elseif($schedule->available_seats < ($seatCount ?? 1))
                                                            <button class="btn btn-outline-warning btn-sm" disabled>
                                                                <i class="bi bi-exclamation-triangle me-1"></i>Not Enough Seats
                                                            </button>
                                                        @else
                                                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                                                <i class="bi bi-x-circle me-1"></i>Not Available
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No schedules available for the selected criteria.
                                        @if($travelDate || $seatCount > 1)
                                            Try adjusting your search filters or 
                                            <a href="{{ route('ticketing.schedules', $route) }}" class="alert-link">view all schedules</a> for this route.
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-geo-alt display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">No Routes Available</h4>
                            <p class="text-muted">There are currently no routes available for booking.</p>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>

            {{ $routes->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
// Add animation to route cards when they come into view
function animateOnScroll() {
    const routes = document.querySelectorAll('.route-card');
    
    routes.forEach((route, index) => {
        // Add a small delay based on index for a cascade effect
        setTimeout(() => {
            route.classList.add('animate__animated', 'animate__fadeInUp');
            route.style.opacity = 1;
        }, index * 100);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Set initial state for animation
    const routes = document.querySelectorAll('.route-card');
    routes.forEach(route => {
        route.style.opacity = 0;
    });
    
    // Trigger animation after a short delay
    setTimeout(animateOnScroll, 300);
    
    // Tab switching functionality
    const sekaliJalan = document.getElementById('sekaliJalan');
    const pulangPergi = document.getElementById('pulangPergi');
    
    sekaliJalan.addEventListener('click', function(e) {
        e.preventDefault();
        this.classList.add('active', 'fw-bold');
        pulangPergi.classList.remove('active', 'fw-bold');
        // Hide return date field if it exists
        const returnDateField = document.getElementById('return_date_container');
        if (returnDateField) {
            returnDateField.style.display = 'none';
        }
    });
    
    pulangPergi.addEventListener('click', function(e) {
        e.preventDefault();
        this.classList.add('active', 'fw-bold');
        sekaliJalan.classList.remove('active', 'fw-bold');
        
        // Add return date field if not exists
        let returnDateField = document.getElementById('return_date_container');
        if (!returnDateField) {
            const travelDateCol = document.getElementById('travel_date').closest('.col-md-2');
            returnDateField = document.createElement('div');
            returnDateField.id = 'return_date_container';
            returnDateField.className = 'col-md-2';
            returnDateField.innerHTML = `
                <div class="form-floating">
                    <input type="date" class="form-control" id="return_date" name="return_date" value="">
                    <label for="return_date">
                        <i class="bi bi-calendar-event text-primary me-2"></i>Return Date
                    </label>
                </div>
            `;
            travelDateCol.after(returnDateField);
            
            // Set default return date to next day
            const travelDate = document.getElementById('travel_date').value;
            if (travelDate) {
                const nextDay = new Date(travelDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const yyyy = nextDay.getFullYear();
                const mm = String(nextDay.getMonth() + 1).padStart(2, '0');
                const dd = String(nextDay.getDate()).padStart(2, '0');
                document.getElementById('return_date').value = `${yyyy}-${mm}-${dd}`;
            }
        } else {
            returnDateField.style.display = 'block';
        }
    });
    
    // Daftar lokasi stasiun/terminal
    const locations = @json($locations);
    
    // Fungsi untuk menampilkan dropdown
    function showDropdown(inputId, dropdownId, searchText = '') {
        const dropdown = document.getElementById(dropdownId);
        dropdown.innerHTML = ''; // Clear previous content
        dropdown.classList.add('show');
        
        // Filter locations based on search text
        const filteredLocations = locations.filter(location => 
            location.toLowerCase().includes(searchText.toLowerCase())
        );
        
        if (filteredLocations.length === 0) {
            dropdown.innerHTML = '<div class="no-results">Tidak ada hasil yang cocok</div>';
            return;
        }
        
        // Create dropdown items
        filteredLocations.forEach(location => {
            const item = document.createElement('div');
            item.className = 'location-item';
            item.innerHTML = `
                <span class="location-icon"><i class="bi bi-geo-alt-fill"></i></span>
                <span>${location}</span>
            `;
            
            item.addEventListener('click', () => {
                document.getElementById(inputId).value = location;
                dropdown.classList.remove('show');
            });
            
            dropdown.appendChild(item);
        });
    }
    
    // Event listeners for origin input
    const originInput = document.getElementById('origin');
    const originDropdown = document.getElementById('originDropdown');
    
    originInput.addEventListener('focus', () => {
        showDropdown('origin', 'originDropdown', originInput.value);
    });
    
    originInput.addEventListener('input', () => {
        showDropdown('origin', 'originDropdown', originInput.value);
    });
    
    originInput.addEventListener('blur', (e) => {
        // Delay hiding to allow clicking on dropdown items
        setTimeout(() => {
            originDropdown.classList.remove('show');
        }, 200);
    });
    
    // Event listeners for destination input
    const destinationInput = document.getElementById('destination');
    const destinationDropdown = document.getElementById('destinationDropdown');
    
    destinationInput.addEventListener('focus', () => {
        showDropdown('destination', 'destinationDropdown', destinationInput.value);
    });
    
    destinationInput.addEventListener('input', () => {
        showDropdown('destination', 'destinationDropdown', destinationInput.value);
    });
    
    destinationInput.addEventListener('blur', (e) => {
        // Delay hiding to allow clicking on dropdown items
        setTimeout(() => {
            destinationDropdown.classList.remove('show');
        }, 200);
    });
    
    // Switch locations functionality (simple value swap)
    document.getElementById('switchLocations').addEventListener('click', function() {
        const originInput = document.getElementById('origin');
        const destinationInput = document.getElementById('destination');
        
        // Simply swap the values
        const tempValue = originInput.value;
        originInput.value = destinationInput.value;
        destinationInput.value = tempValue;
    });
    
    // Set min date for travel date
    const travelDateInput = document.getElementById('travel_date');
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    travelDateInput.min = `${yyyy}-${mm}-${dd}`;
    
    // Update return date when travel date changes
    travelDateInput.addEventListener('change', function() {
        const returnDateInput = document.getElementById('return_date');
        if (returnDateInput) {
            const travelDate = new Date(this.value);
            if (travelDate) {
                const nextDay = new Date(travelDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const yyyy = nextDay.getFullYear();
                const mm = String(nextDay.getMonth() + 1).padStart(2, '0');
                const dd = String(nextDay.getDate()).padStart(2, '0');
                returnDateInput.value = `${yyyy}-${mm}-${dd}`;
                returnDateInput.min = this.value; // Cannot select a return date before travel date
            }
        }
    });
});
</script>
@endpush
@endsection
