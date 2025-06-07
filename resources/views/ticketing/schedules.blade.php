@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('ticketing.routes') }}">Routes</a></li>
                    <li class="breadcrumb-item active">{{ $route->origin }} → {{ $route->destination }}</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="bi bi-calendar-check me-2"></i>Available Schedules</h2>
                    <p class="text-muted mb-0">{{ $route->origin }} → {{ $route->destination }}</p>
                </div>
                <a href="{{ route('ticketing.routes') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Routes
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
                @forelse($schedules as $schedule)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock me-2"></i>
                                {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}
                            </h5>
                            <span class="badge bg-info">
                                {{ \Carbon\Carbon::parse($schedule->departure_time)->format('M d, Y') }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Departure</small>
                                    <div class="fw-bold">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Arrival</small>
                                    <div class="fw-bold">{{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Available Seats</small>
                                    <div class="fw-bold {{ $schedule->available_seats <= 5 ? 'text-warning' : 'text-success' }}">
                                        {{ $schedule->available_seats }} / {{ $schedule->total_seats }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Price</small>
                                    <div class="h5 text-success mb-0">
                                        Rp {{ number_format($schedule->price, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            @if($schedule->bus_number)
                            <div class="mb-3">
                                <small class="text-muted">Bus Number</small>
                                <div class="fw-bold">{{ $schedule->bus_number }}</div>
                            </div>
                            @endif

                            @if($schedule->notes)
                            <div class="mb-3">
                                <small class="text-muted">Notes</small>
                                <p class="small mb-0">{{ Str::limit($schedule->notes, 80) }}</p>
                            </div>
                            @endif

                            <div class="mb-3">
                                <span class="badge {{ $schedule->status === 'scheduled' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($schedule->status) }}
                                </span>
                                @if($schedule->status === 'unavailable' && !$schedule->is_active)
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle me-1"></i>Schedule is inactive
                                    </small>
                                @endif
                            </div>
                        </div>                        <div class="card-footer bg-light">
                            @if($schedule->status === 'scheduled' && $schedule->available_seats > 0)
                                @php
                                    $seatParams = [];
                                    if(isset($seatCount) && $seatCount > 1) $seatParams['seat_count'] = $seatCount;
                                    if(isset($travelDate)) $seatParams['travel_date'] = $travelDate;
                                    $queryString = !empty($seatParams) ? '?' . http_build_query($seatParams) : '';
                                @endphp
                                <a href="{{ route('ticketing.seats', $schedule) }}{{ $queryString }}" class="btn btn-primary w-100">
                                    <i class="bi bi-bookmark-check me-2"></i>Select Seat
                                </a>
                            @elseif($schedule->available_seats <= 0)
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-x-circle me-2"></i>Fully Booked
                                </button>
                            @elseif(!$schedule->is_active)
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-x-circle me-2"></i>Schedule Inactive
                                </button>
                            @else
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-x-circle me-2"></i>Unavailable for Today
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @empty                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">No Schedules Available</h4>
                            
                            @php
                                // Check if there are inactive schedules
                                $totalSchedulesCount = \App\Models\Schedule::where('route_id', $route->id)
                                    ->count();
                                $inactiveCount = \App\Models\Schedule::where('route_id', $route->id)
                                    ->where('is_active', 0)
                                    ->count();
                                $currentDayOfWeek = date('w'); // 0 (Sunday) to 6 (Saturday)
                                $daysText = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            @endphp
                            
                            @if($totalSchedulesCount > 0)
                                <p class="text-muted">
                                    This route has {{ $totalSchedulesCount }} schedule(s), but none are available for today 
                                    ({{ $daysText[$currentDayOfWeek] }}).
                                </p>
                                @if($inactiveCount > 0)
                                    <div class="alert alert-info d-inline-block">
                                        <i class="bi bi-info-circle me-2"></i>
                                        {{ $inactiveCount }} schedule(s) are currently inactive
                                    </div>
                                @endif
                            @else
                                <p class="text-muted">There are currently no schedules available for this route.</p>
                            @endif
                            
                            <a href="{{ route('ticketing.routes') }}" class="btn btn-primary mt-3">
                                <i class="bi bi-arrow-left me-2"></i>Back to Routes
                            </a>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
