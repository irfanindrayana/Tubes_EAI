@extends('layouts.app')

@push('styles')
<style>
    .specific-dates-container {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        background-color: #f8f9fa;
    }
    
    .selected-date-badge {
        background-color: #0d6efd;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        margin: 2px;
        display: inline-block;
        font-size: 0.875rem;
    }
    
    .selected-date-badge .remove-date {
        margin-left: 8px;
        cursor: pointer;
        font-weight: bold;
    }
    
    .selected-date-badge .remove-date:hover {
        color: #ffc107;
    }
    
    .operation-info-badge {
        font-size: 0.875rem;
        padding: 6px 12px;
    }
    
    .progress-custom {
        height: 25px;
        background-color: #e9ecef;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .modal-schedule-details .table td {
        border-top: 1px solid #dee2e6;
        padding: 8px 12px;
    }
    
    .modal-schedule-details .table td:first-child {
        background-color: #f8f9fa;
        font-weight: 600;
        width: 35%;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-4">
            <a href="{{ route('admin.routes') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Rute
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
            
            <!-- Route Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-map me-2"></i>Detail Rute
                    </h5>
                    <a href="{{ route('admin.routes.edit', $route) }}" class="btn btn-light btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit Rute
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2 mb-2">Info Utama</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td width="40%"><strong>Nama:</strong></td>
                                    <td>{{ $route->route_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Asal:</strong></td>
                                    <td>{{ $route->origin }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tujuan:</strong></td>
                                    <td>{{ $route->destination }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jarak:</strong></td>
                                    <td>{{ $route->distance }} km</td>
                                </tr>
                                <tr>
                                    <td><strong>Durasi:</strong></td>
                                    <td>{{ $route->estimated_duration }} menit</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2 mb-2">Harga & Status</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td width="40%"><strong>Harga Dasar:</strong></td>
                                    <td>Rp {{ number_format($route->base_price, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $route->is_active ? 'success' : 'danger' }}">
                                            {{ $route->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Total Jadwal:</strong></td>
                                    <td>{{ $schedules->total() }} jadwal</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2 mb-2">Pemberhentian</h6>
                            @if($route->stops && is_array($route->stops) && count($route->stops) > 0)
                                <ol class="ps-3">
                                    @foreach($route->stops as $stop)
                                        <li>{{ $stop }}</li>
                                    @endforeach
                                </ol>
                            @else
                                <p class="text-muted">Tidak ada pemberhentian yang tercatat</p>
                            @endif
                            
                            @if($route->description)
                                <h6 class="border-bottom pb-2 mb-2 mt-4">Deskripsi</h6>
                                <p>{{ $route->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Schedules Card -->
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-week me-2"></i>Jadwal Rute: {{ $route->origin }} → {{ $route->destination }}
                    </h5>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Jadwal
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Jadwal</th>
                                    <th>Bus</th>
                                    <th>Kapasitas</th>
                                    <th>Terisi</th>
                                    <th>Hari Operasi</th>
                                    <th class="text-center">Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->id }}</td>
                                    <td>
                                        <div class="fw-bold">
                                            {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($schedule->departure_time)->format('d M Y') }}
                                        </small>
                                    </td>
                                    <td>{{ $schedule->bus_code ?? 'BUS-' . $schedule->id }}</td>
                                    <td>{{ $schedule->seats_count ?? $schedule->capacity }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            @php
                                                // Calculate the booked seats correctly
                                                $booked = $schedule->bookings_count ?? 0;
                                                $capacity = $schedule->total_seats ?? 40;
                                                // Ensure we don't divide by zero
                                                $percent = $capacity > 0 ? min(($booked / $capacity) * 100, 100) : 0;
                                                $bgClass = $percent > 85 ? 'bg-danger' : ($percent > 60 ? 'bg-warning' : 'bg-success');
                                            @endphp
                                            <div class="progress-bar {{ $bgClass }}" role="progressbar" 
                                                 style="width: {{ $percent }}%">
                                                {{ $booked }}/{{ $capacity }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $specificDates = $schedule->scheduleDates()->where('is_active', true)->orderBy('scheduled_date')->take(3)->get();
                                        @endphp
                                        @if($specificDates->count() > 0)
                                            @foreach($specificDates as $scheduleDate)
                                                <span class="badge bg-success me-1 mb-1">{{ \Carbon\Carbon::parse($scheduleDate->scheduled_date)->format('d/m') }}</span>
                                            @endforeach
                                            @if($schedule->scheduleDates()->where('is_active', true)->count() > 3)
                                                <span class="badge bg-secondary">+{{ $schedule->scheduleDates()->where('is_active', true)->count() - 3 }}</span>
                                            @endif
                                            <br><small class="text-muted">Tanggal Spesifik</small>
                                        @else
                                            <span class="text-muted">Tidak ada tanggal aktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $schedule->is_active ? 'success' : 'secondary' }} px-3 py-2 fs-6">
                                            {{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" onclick="viewScheduleDetails({{ $schedule->id }})">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-outline-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('admin.schedules.fix', $schedule) }}" class="btn btn-outline-primary">
                                                <i class="bi bi-wrench"></i>
                                            </a>
                                            <form action="{{ route('admin.schedules.toggle', $schedule) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn {{ $schedule->is_active ? 'btn-outline-danger' : 'btn-success' }}">
                                                    <i class="bi bi-{{ $schedule->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                                                    {{ $schedule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-calendar-x display-3 text-muted mb-3 d-block"></i>
                                        <h5 class="text-muted">Belum ada jadwal untuk rute ini</h5>
                                        <p class="text-muted mb-3">Klik tombol "Tambah Jadwal" untuk membuat jadwal baru</p>
                                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                                            <i class="bi bi-plus-circle me-1"></i>Tambah Jadwal
                                        </button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $schedules->links() }}
                    </div>
                </div>
            </div>
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
            <form action="{{ route('admin.schedules.store') }}" method="POST" id="addScheduleForm">
                @csrf
                <div class="modal-body">
                    <!-- Hidden route_id field -->
                    <input type="hidden" name="route_id" value="{{ $route->id }}">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Membuat jadwal untuk rute: <strong>{{ $route->origin }} → {{ $route->destination }}</strong>
                    </div>
                    
                    <!-- Schedule Form Section -->
                    <div class="mb-3">
                        <label class="form-label">Waktu Berangkat & Tiba</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="time" name="departure_time" class="form-control" required>
                                    <span class="input-group-text">Berangkat</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                    <input type="time" name="arrival_time" class="form-control">
                                    <span class="input-group-text">Tiba</span>
                                </div>
                            </div>
                        </div>
                    </div>
                      <div class="mb-3">
                        <label class="form-label">Tanggal Operasi</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                            <input type="date" name="operation_date" id="operationDate" class="form-control" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <small class="form-text text-muted">Pilih satu tanggal untuk operasi jadwal ini</small>
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
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Harga Kursi</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" class="form-control" min="0" required value="{{ $route->base_price }}">
                            </div>
                            <small class="text-muted">Default dari harga dasar rute</small>
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
            <div class="modal-body" id="scheduleDetailsBody">
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
                <a href="#" id="fixScheduleLink" class="btn btn-info">
                    <i class="bi bi-wrench me-1"></i>Fix Jadwal
                </a>
                <a href="#" id="editScheduleLink" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i>Edit Jadwal
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Make sure Bootstrap is loaded
if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap library is not loaded. Please include it in your layout.');
}

// Helper function to format datetime properly
function formatDateTime(datetime) {
    if (!datetime) return 'Tidak ditetapkan';
    
    try {
        // Handle different time formats
        if (typeof datetime === 'string') {
            // If it's just time format (H:i), create today's date with that time
            if (datetime.match(/^\d{2}:\d{2}$/)) {
                const today = new Date();
                const [hours, minutes] = datetime.split(':');
                today.setHours(parseInt(hours), parseInt(minutes), 0, 0);
                return today.toLocaleDateString('id-ID') + ' ' + today.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
            }
        }
        
        // For full datetime strings
        const date = new Date(datetime);
        if (isNaN(date.getTime())) {
            // If invalid date, try to parse as time only
            if (datetime.includes(':')) {
                return 'Hari ini ' + datetime;
            }
            return 'Format tidak valid';
        }
        
        return date.toLocaleDateString('id-ID') + ' ' + date.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
    } catch (error) {
        console.error('Error formatting datetime:', error);
        return 'Error format tanggal';
    }
}

function viewScheduleDetails(scheduleId) {
    // Pastikan modal ada di halaman sebelum menampilkan
    const modalElement = document.getElementById('scheduleDetailsModal');
    if (!modalElement) {
        console.error('Modal element not found!');
        alert('Detail jadwal tidak tersedia. Silakan coba lagi nanti.');
        return;
    }
    
    // Show loading spinner
    document.getElementById('scheduleDetailsBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data jadwal...</p>
        </div>
    `;
    
    // Show the schedule details modal
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Load schedule details with AJAX
    fetch(`/admin/schedules/${scheduleId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Format specific dates info
            let operationInfo = '';
            
            if (data.schedule_dates && data.schedule_dates.length > 0) {
                const activeDates = data.schedule_dates
                    .filter(sd => sd.is_active)
                    .map(sd => new Date(sd.scheduled_date).toLocaleDateString('id-ID'))
                    .slice(0, 5); // Show first 5 dates
                
                operationInfo = activeDates.join(', ');
                if (data.schedule_dates.filter(sd => sd.is_active).length > 5) {
                    operationInfo += ` (+${data.schedule_dates.filter(sd => sd.is_active).length - 5} lainnya)`;
                }
                operationInfo += ' <small class="text-muted">(Tanggal Spesifik)</small>';
            } else {
                operationInfo = 'Tidak ada tanggal spesifik';
            }
            
            // Format schedule details
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Informasi Jadwal</h5>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>Rute:</strong></td>
                                <td>${data.route?.route_name || ''}</td>
                            </tr>
                            <tr>
                                <td><strong>Asal → Tujuan:</strong></td>
                                <td>${data.route?.origin || ''} → ${data.route?.destination || ''}</td>
                            </tr>
                            <tr>
                                <td><strong>Waktu Berangkat:</strong></td>
                                <td>${data.departure_time_formatted ? 'Hari ini ' + data.departure_time_formatted : formatDateTime(data.departure_time)}</td>
                            </tr>
                            <tr>
                                <td><strong>Waktu Tiba:</strong></td>
                                <td>${data.arrival_time_formatted ? 'Hari ini ' + data.arrival_time_formatted : (data.arrival_time ? formatDateTime(data.arrival_time) : 'Belum ditetapkan')}</td>
                            </tr>
                            <tr>
                                <td><strong>Bus:</strong></td>
                                <td>${data.bus_number || data.bus_code || 'BUS-' + data.id}</td>
                            </tr>
                            <tr>
                                <td><strong>Operasi:</strong></td>
                                <td>${operationInfo}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Status Booking</h5>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1 me-2">
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar ${
                                        (data.total_seats - data.available_seats) / data.total_seats > 0.8 ? 'bg-danger' : 
                                        (data.total_seats - data.available_seats) / data.total_seats > 0.5 ? 'bg-warning' : 'bg-success'
                                    }" 
                                         role="progressbar" 
                                         style="width: ${data.available_seats < data.total_seats ? ((data.total_seats - data.available_seats) / data.total_seats) * 100 : 0}%">
                                        ${data.total_seats - data.available_seats}/${data.total_seats}
                                    </div>
                                </div>
                            </div>
                            <span class="badge ${data.is_active ? 'bg-success' : 'bg-secondary'} px-3 py-2 ms-2">
                                ${data.is_active ? 'Aktif' : 'Nonaktif'}
                            </span>
                        </div>
                        <p><strong>Tanggal Operasional:</strong></p>
                        <div class="d-flex flex-wrap">
                            ${data.schedule_dates && data.schedule_dates.length > 0 ? 
                                data.schedule_dates
                                    .filter(sd => sd.is_active)
                                    .slice(0, 10)
                                    .map(sd => 
                                        `<span class="badge bg-primary me-1 mb-1">${new Date(sd.scheduled_date).toLocaleDateString('id-ID')}</span>`
                                    ).join('') 
                                : '<span class="text-muted">Tidak ada tanggal aktif</span>'
                            }
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('scheduleDetailsContent').innerHTML = html;
            
            // Set edit link
            const editLink = document.getElementById('editScheduleLink');
            if (editLink) {
                editLink.href = `/admin/schedules/${data.id}/edit`;
            }
            
            // Set fix link
            const fixLink = document.getElementById('fixScheduleLink');
            if (fixLink) {
                fixLink.href = `/admin/schedules/${data.id}/fix`;
            }
        })
        .catch(error => {
            console.error('Error fetching schedule details:', error);
            document.getElementById('scheduleDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Error: Tidak dapat memuat data jadwal
                </div>
            `;
        });
}

// Handle form submission and validation
document.addEventListener('DOMContentLoaded', function() {
    const scheduleForm = document.getElementById('addScheduleForm');
    
    if (scheduleForm) {
        scheduleForm.addEventListener('submit', function(e) {
            // Validate operation date
            const operationDate = document.querySelector('input[name="operation_date"]').value;
            
            if (!operationDate) {
                e.preventDefault();
                alert('Anda harus memilih tanggal operasi.');
                return false;
            }
            
            // Validate date is not in the past
            const selectedDate = new Date(operationDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                e.preventDefault();
                alert('Tidak dapat memilih tanggal yang sudah lewat.');
                return false;
            }
            
            // Validate departure and arrival times
            const departureTime = document.querySelector('input[name="departure_time"]').value;
            const arrivalTime = document.querySelector('input[name="arrival_time"]').value;
            
            if (!departureTime) {
                e.preventDefault();
                alert('Waktu berangkat harus diisi.');
                return false;
            }
            
            if (arrivalTime && arrivalTime <= departureTime) {
                e.preventDefault();
                alert('Waktu tiba harus lebih besar dari waktu berangkat.');
                return false;
            }
            
            console.log('Form submitted with date:', operationDate);
            return true;
        });
    }
});
</script>
@endpush
@endsection
