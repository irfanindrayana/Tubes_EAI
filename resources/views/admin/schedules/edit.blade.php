@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12 mb-4">
            <a href="{{ route('admin.routes.schedules', $schedule->route_id) }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Jadwal Rute
            </a>
        </div>
        
        <div class="col-12">
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
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Edit Jadwal
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Rute</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-map"></i></span>
                                    <select name="route_id" class="form-select" required>
                                        <option value="">Pilih Rute</option>
                                        @foreach($routes as $route)
                                        <option value="{{ $route->id }}" {{ $schedule->route_id == $route->id ? 'selected' : '' }}>
                                            {{ $route->origin }} → {{ $route->destination }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('route_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Bus</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-truck-front"></i></span>
                                    <input type="text" name="bus_number" class="form-control" placeholder="B 1234 CD" 
                                        value="{{ old('bus_number', $schedule->bus_number) }}">
                                </div>
                                @error('bus_number')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            @php
                                $departure = \Carbon\Carbon::parse($schedule->departure_time);
                                $arrival = $schedule->arrival_time ? \Carbon\Carbon::parse($schedule->arrival_time) : null;
                            @endphp
                            
                            <div class="col-md-4">
                                <label class="form-label">Tanggal</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                    <input type="date" name="schedule_date" class="form-control" required
                                        value="{{ old('schedule_date', $departure->format('Y-m-d')) }}">
                                </div>
                                @error('schedule_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Waktu Berangkat</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="time" name="departure_time" class="form-control" required
                                        value="{{ old('departure_time', $departure->format('H:i')) }}">
                                </div>
                                @error('departure_time')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Waktu Tiba (Estimasi)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                    <input type="time" name="arrival_time" class="form-control"
                                        value="{{ old('arrival_time', $arrival ? $arrival->format('H:i') : '') }}">
                                </div>
                                @error('arrival_time')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kapasitas Kursi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="number" name="capacity" class="form-control" min="1" max="100" required
                                        value="{{ old('capacity', $schedule->capacity) }}">
                                    <span class="input-group-text">kursi</span>
                                </div>
                                @error('capacity')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                
                                @if($schedule->bookings_count > 0)
                                    <div class="alert alert-warning mt-2">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        Ada {{ $schedule->bookings_count }} booking pada jadwal ini. 
                                        Pengurangan kapasitas di bawah jumlah tersebut tidak dianjurkan.
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga Kursi</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="price" class="form-control" min="0" required
                                        value="{{ old('price', $schedule->price) }}">
                                </div>
                                @error('price')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>                        </div>                        <!-- Operation Date Section -->
                        <div class="mb-3">
                            <label class="form-label">Tanggal Operasi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <input type="date" name="operation_date" class="form-control" required 
                                    min="{{ now()->format('Y-m-d') }}" 
                                    value="{{ old('operation_date', $schedule->scheduleDates->first() ? $schedule->scheduleDates->first()->scheduled_date : '') }}">
                            </div>
                            @error('operation_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Jadwal</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="scheduleActiveSwitch"
                                    {{ old('is_active', $schedule->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="scheduleActiveSwitch">Jadwal Aktif</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.routes.schedules', $schedule->route_id) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const scheduleForm = document.querySelector('form[action*="schedules"]');
    if (scheduleForm) {
        scheduleForm.addEventListener('submit', function(e) {
            // Check if operation date is selected
            const operationDate = document.querySelector('input[name="operation_date"]').value;
            if (!operationDate) {
                e.preventDefault();
                alert('Anda harus memilih tanggal operasi.');
                return false;
            }
            
            // Validate departure and arrival times
            const departureTime = document.querySelector('input[name="departure_time"]').value;
            const arrivalTime = document.querySelector('input[name="arrival_time"]').value;
            
            if (arrivalTime && arrivalTime <= departureTime) {
                e.preventDefault();
                alert('Waktu tiba harus lebih besar dari waktu berangkat.');
                return false;
            }
            
            // Validate price
            const price = parseFloat(document.querySelector('input[name="price"]').value);
            if (isNaN(price) || price <= 0) {
                e.preventDefault();
                alert('Harga harus lebih besar dari 0.');
                return false;
            }
            
            // Validate capacity
            const capacity = parseInt(document.querySelector('input[name="capacity"]').value);
            const bookingsCount = {{ $schedule->bookings_count ?? 0 }};
            
            if (capacity < bookingsCount) {
                if (!confirm(`Ada ${bookingsCount} kursi yang sudah dipesan. Mengurangi kapasitas di bawah ini dapat menyebabkan masalah. Anda yakin ingin melanjutkan?`)) {
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    }
});
</script>
@endpush
