@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Alerts -->
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
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-map me-2"></i> Manajemen Rute & Jadwal
                    </h5>
                    <div>
                        <button type="button" class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                            <i class="bi bi-clock me-1"></i> Tambah Jadwal
                        </button>
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addRouteModal">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Rute
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Routes Section -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-geo-alt me-2"></i>Daftar Rute
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Rute</th>
                                        <th>Asal</th>
                                        <th>Tujuan</th>
                                        <th>Pemberhentian</th>
                                        <th>Jarak (km)</th>
                                        <th>Durasi</th>
                                        <th>Harga</th>
                                        <th>Status</th>
                                        <th>Jadwal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>                                    @forelse($routes as $route)                                    <tr>
                                        <td>{{ $route->id }}</td>
                                        <td>{{ $route->route_name ?? 'Route '.$route->id }}</td>
                                        <td>{{ $route->origin }}</td>
                                        <td>{{ $route->destination }}</td>
                                        <td>
                                            @if($route->stops && is_array($route->stops) && count($route->stops) > 0)
                                                <div class="small">
                                                    @foreach($route->stops as $index => $stop)
                                                        @if($index < 2)
                                                            <span class="badge bg-secondary me-1 mb-1">{{ $stop }}</span>
                                                        @endif
                                                    @endforeach
                                                    @if(count($route->stops) > 2)
                                                        <span class="badge bg-light text-dark">+{{ count($route->stops) - 2 }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $route->distance ?? '-' }} km</td>
                                        <td>{{ $route->estimated_duration ?? '-' }} min</td>
                                        <td>Rp {{ number_format($route->base_price, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $route->is_active ? 'success' : 'danger' }}">
                                                {{ $route->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $route->schedules_count ?? 0 }} jadwal</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-info" onclick="viewRouteSchedules({{ $route->id }})">
                                                    <i class="bi bi-calendar-check"></i>
                                                </button>
                                                <a href="{{ route('admin.routes.edit', $route) }}" class="btn btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.routes.toggle', $route) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-{{ $route->is_active ? 'danger' : 'success' }}" title="{{ $route->is_active ? 'Nonaktifkan' : 'Aktifkan' }} rute">
                                                        <i class="bi bi-{{ $route->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                                    </button>
                                                </form>                                                <form action="{{ route('admin.routes.destroy', $route) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Hapus rute ini? Rute dengan jadwal tidak dapat dihapus.')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <i class="bi bi-map display-3 text-muted mb-3 d-block"></i>
                                            <h5 class="text-muted">Belum ada rute tersedia</h5>
                                            <p class="text-muted">Klik tombol "Tambah Rute" untuk membuat rute baru.</p>
                                            <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addRouteModal">
                                                <i class="bi bi-plus-circle me-1"></i> Tambah Rute Baru
                                            </button>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>                    <!-- Schedules Section -->
                    <div class="mt-5">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-calendar2-week me-2"></i>Jadwal Hari Ini
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Rute</th>
                                        <th>Waktu Berangkat</th>
                                        <th>Bus</th>
                                        <th>Kapasitas</th>
                                        <th>Terisi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($todaySchedules as $schedule)                                    <tr>
                                        <td>
                                            <div>
                                                <div class="fw-bold">{{ $schedule->route->origin }} → {{ $schedule->route->destination }}</div>
                                                <small class="text-muted">{{ $schedule->route->estimated_duration ?? 'N/A' }} menit</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') ?? '-' }}</small>
                                        </td>
                                        <td>{{ $schedule->bus_number ?? 'BUS-' . $schedule->id }}</td>
                                        <td>{{ $schedule->capacity ?? $schedule->seats()->count() }}</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                @php
                                                    $booked = $schedule->bookings_count ?? 0;
                                                    $capacity = $schedule->capacity ?? 1;
                                                    $percent = min(($booked / $capacity) * 100, 100);
                                                    $bgClass = $percent > 85 ? 'bg-danger' : ($percent > 60 ? 'bg-warning' : 'bg-success');
                                                @endphp
                                                <div class="progress-bar {{ $bgClass }}" role="progressbar" 
                                                     style="width: {{ $percent }}%">
                                                    {{ $booked }}/{{ $capacity }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $schedule->is_active ? 'success' : 'secondary' }}">
                                                {{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-info" onclick="viewScheduleDetails({{ $schedule->id }})">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-warning" onclick="editSchedule({{ $schedule->id }})">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="bi bi-calendar-x display-4 text-muted mb-3 d-block"></i>
                                            <h5 class="text-muted">Tidak ada jadwal untuk hari ini</h5>
                                            <p class="text-muted mb-3">Klik tombol "Tambah Jadwal" untuk menambahkan jadwal baru</p>
                                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                                                <i class="bi bi-plus-circle me-1"></i> Tambah Jadwal
                                            </button>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Route Modal -->
<div class="modal fade" id="addRouteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Rute Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.routes.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nama Rute</label>
                            <input type="text" name="route_name" class="form-control" required placeholder="Contoh: Trans Bandung Route 1">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kota Asal</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                    <input type="text" name="origin" class="form-control" list="originList" required placeholder="Contoh: Bandung">
                                    <datalist id="originList">
                                        @foreach($locations as $location)
                                            <option value="{{ $location }}">
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kota Tujuan</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                    <input type="text" name="destination" class="form-control" list="destinationList" required placeholder="Contoh: Jakarta">
                                    <datalist id="destinationList">
                                        @foreach($locations as $location)
                                            <option value="{{ $location }}">
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">                            <div class="mb-3">
                                <label class="form-label">Jarak (km)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-rulers"></i></span>
                                    <input type="number" name="distance" class="form-control" step="0.1" min="1" required placeholder="150">
                                    <span class="input-group-text">km</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estimasi Durasi (menit)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="number" name="estimated_duration" class="form-control" min="1" required placeholder="180">
                                    <span class="input-group-text">menit</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Harga Dasar</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="base_price" class="form-control" min="0" required placeholder="50000">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status Rute</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="routeActiveSwitch" checked>
                                    <label class="form-check-label" for="routeActiveSwitch">Rute Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pemberhentian (Opsional)</label>
                        <div class="stops-container mb-2">
                            <div class="input-group mb-2 stop-item">
                                <span class="input-group-text"><i class="bi bi-signpost-2"></i></span>
                                <input type="text" name="stops[]" class="form-control" placeholder="Nama Pemberhentian">
                                <button type="button" class="btn btn-outline-danger remove-stop" disabled><i class="bi bi-dash"></i></button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary add-stop">
                            <i class="bi bi-plus-lg"></i> Tambah Pemberhentian
                        </button>
                    </div>
                      <div class="mb-3">
                        <label class="form-label">Deskripsi Rute (Opsional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi tambahan tentang rute"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Rute
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-plus me-2"></i>Tambah Jadwal Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.schedules.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rute</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-map"></i></span>
                            <select name="route_id" class="form-select" required>
                                <option value="">Pilih Rute</option>
                                @foreach($routes as $route)
                                <option value="{{ $route->id }}">{{ $route->origin }} → {{ $route->destination }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                    <input type="date" name="schedule_date" class="form-control" required value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Waktu Berangkat</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="time" name="departure_time" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Waktu Tiba (Estimasi)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                    <input type="time" name="arrival_time" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nomor Bus</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-truck-front"></i></span>
                                    <input type="text" name="bus_number" class="form-control" placeholder="B 1234 CD">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jumlah Kursi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="number" name="total_seats" class="form-control" value="40" min="1" max="100" required>
                                    <span class="input-group-text">kursi</span>
                                </div>
                            </div>
                        </div>
                    </div>                    <div class="mb-3">
                        <label class="form-label mb-2">Hari Operasional</label>
                        <div class="row">
                            @php
                                $days = [
                                    0 => 'Minggu',
                                    1 => 'Senin', 
                                    2 => 'Selasa', 
                                    3 => 'Rabu', 
                                    4 => 'Kamis', 
                                    5 => 'Jumat', 
                                    6 => 'Sabtu'
                                ];
                            @endphp
                            
                            @foreach($days as $day_num => $dayName)
                            <div class="col-md-3 col-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days_of_week[]" value="{{ $day_num }}" id="day{{ $day_num }}" 
                                        {{ $day_num >= 1 && $day_num <= 5 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="day{{ $day_num }}">{{ $dayName }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Harga Kursi</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" class="form-control" min="0" required placeholder="50000">
                            </div>
                            <small class="text-muted">Harga per kursi, default dari harga dasar rute</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="scheduleActiveSwitch" checked>
                                <label class="form-check-label" for="scheduleActiveSwitch">Jadwal Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i>Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Details Modal -->
<div class="modal fade" id="scheduleDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>Detail Jadwal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="scheduleDetailsContent" class="p-2">
                    <!-- Content will be loaded via AJAX -->
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add stop button functionality
    document.querySelector('.add-stop')?.addEventListener('click', function() {
        const container = document.querySelector('.stops-container');
        const newItem = document.createElement('div');
        newItem.className = 'input-group mb-2 stop-item';
        newItem.innerHTML = `
            <span class="input-group-text"><i class="bi bi-signpost-2"></i></span>
            <input type="text" name="stops[]" class="form-control" placeholder="Nama Pemberhentian">
            <button type="button" class="btn btn-outline-danger remove-stop"><i class="bi bi-dash"></i></button>
        `;
        
        container.appendChild(newItem);
        
        // Enable all remove buttons
        document.querySelectorAll('.remove-stop').forEach(btn => {
            btn.disabled = document.querySelectorAll('.stop-item').length <= 1;
            btn.addEventListener('click', function() {
                this.closest('.stop-item').remove();
                
                // Disable the last remove button if only one item remains
                if (document.querySelectorAll('.stop-item').length <= 1) {
                    document.querySelector('.remove-stop').disabled = true;
                }
            });
        });
    });

    // Form validation for route creation
    const routeForm = document.querySelector('form[action="{{ route('admin.routes.store') }}"]');
    if (routeForm) {
        routeForm.addEventListener('submit', function(e) {
            const origin = document.querySelector('input[name="origin"]').value.trim();
            const destination = document.querySelector('input[name="destination"]').value.trim();
            
            if (origin === destination) {
                e.preventDefault();
                alert('Kota asal dan tujuan tidak boleh sama.');
                return false;
            }
            
            const basePrice = parseFloat(document.querySelector('input[name="base_price"]').value);
            if (isNaN(basePrice) || basePrice <= 0) {
                e.preventDefault();
                alert('Harga dasar harus lebih besar dari 0.');
                return false;
            }
            
            return true;
        });
    }
});

function viewRouteSchedules(routeId) {
    // Open a new page to view schedules for this route
    window.location.href = `/admin/routes/${routeId}/schedules`;
}

function editRoute(routeId) {
    // Redirect to the edit route page
    window.location.href = `/admin/routes/${routeId}/edit`;
}

function viewScheduleDetails(scheduleId) {
    // Show the schedule details modal
    const modal = new bootstrap.Modal(document.getElementById('scheduleDetailsModal'));
    modal.show();
    
    // Load schedule details with AJAX
    fetch(`/admin/schedules/${scheduleId}`)
        .then(response => response.json())
        .then(data => {
            // Format schedule details
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Informasi Jadwal</h5>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>Rute:</strong></td>
                                <td>${data.route?.origin || ''} → ${data.route?.destination || ''}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal:</strong></td>
                                <td>${new Date(data.departure_time).toLocaleDateString('id-ID')}</td>
                            </tr>
                            <tr>
                                <td><strong>Waktu Berangkat:</strong></td>
                                <td>${new Date(data.departure_time).toLocaleTimeString('id-ID')}</td>
                            </tr>
                            <tr>
                                <td><strong>Waktu Tiba:</strong></td>
                                <td>${data.arrival_time ? new Date(data.arrival_time).toLocaleTimeString('id-ID') : 'Belum ditetapkan'}</td>
                            </tr>
                            <tr>
                                <td><strong>Bus:</strong></td>
                                <td>${data.bus_number || 'BUS-' + data.id}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Status Booking</h5>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1 me-2">
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar ${data.bookings_count / data.capacity > 0.8 ? 'bg-danger' : 'bg-success'}" 
                                         role="progressbar" 
                                         style="width: ${(data.bookings_count / data.capacity) * 100}%">
                                        ${data.bookings_count}/${data.capacity}
                                    </div>
                                </div>
                            </div>
                            <span class="badge ${data.is_active ? 'bg-success' : 'bg-secondary'}">
                                ${data.is_active ? 'Aktif' : 'Nonaktif'}
                            </span>
                        </div>                        <p><strong>Jadwal operasional:</strong></p>
                        <div class="d-flex flex-wrap">
                            ${data.days_of_week ? (Array.isArray(data.days_of_week) ? data.days_of_week : JSON.parse(data.days_of_week)).map(day => 
                                `<span class="badge bg-primary me-1 mb-1">${['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][day]}</span>`
                            ).join('') : ''}
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('scheduleDetailsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('scheduleDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Error: Tidak dapat memuat data jadwal
                </div>
            `;
        });
}

function editSchedule(scheduleId) {
    // Redirect to the edit schedule page
    window.location.href = `/admin/schedules/${scheduleId}/edit`;
}
</script>
@endsection
