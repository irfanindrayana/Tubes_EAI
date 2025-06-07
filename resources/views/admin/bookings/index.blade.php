@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-ticket-alt"></i> Manajemen Booking
                    </h5>
                </div>
                
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $stats['total'] ?? 0 }}</h4>
                                            <p class="mb-0">Total Booking</p>
                                        </div>
                                        <i class="fas fa-ticket-alt fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $stats['pending'] ?? 0 }}</h4>
                                            <p class="mb-0">Menunggu</p>
                                        </div>
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $stats['confirmed'] ?? 0 }}</h4>
                                            <p class="mb-0">Dikonfirmasi</p>
                                        </div>
                                        <i class="fas fa-check fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $stats['cancelled'] ?? 0 }}</h4>
                                            <p class="mb-0">Dibatalkan</p>
                                        </div>
                                        <i class="fas fa-times fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <select class="form-select" id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="confirmed">Dikonfirmasi</option>
                                <option value="cancelled">Dibatalkan</option>
                                <option value="completed">Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="routeFilter">
                                <option value="">Semua Rute</option>
                                @foreach($routes as $route)
                                <option value="{{ $route->id }}">{{ $route->origin }} - {{ $route->destination }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="dateFilter" placeholder="Tanggal booking">
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchBooking" placeholder="Cari booking code atau nama...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Bookings Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Booking Code</th>
                                    <th>Penumpang</th>
                                    <th>Rute</th>
                                    <th>Tanggal</th>
                                    <th>Kursi</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Booking Time</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bookings as $booking)
                                <tr>
                                    <td>
                                        <strong>{{ $booking->booking_code }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $booking->user->name }}</div>
                                            <small class="text-muted">{{ $booking->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $booking->schedule->route->origin }} - {{ $booking->schedule->route->destination }}</div>
                                            <small class="text-muted">{{ $booking->schedule->departure_time }}</small>
                                        </div>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($booking->travel_date)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $booking->seats->pluck('seat_number')->join(', ') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $booking->status == 'confirmed' ? 'success' : 
                                            ($booking->status == 'pending' ? 'warning' : 
                                            ($booking->status == 'cancelled' ? 'danger' : 'secondary')) 
                                        }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</td>
                                    <td>{{ $booking->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" onclick="viewBooking('{{ $booking->booking_code }}')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            @if($booking->status == 'pending')
                                            <button type="button" class="btn btn-outline-success" onclick="confirmBooking('{{ $booking->booking_code }}')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="cancelBooking('{{ $booking->booking_code }}')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada booking ditemukan</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Menampilkan {{ $bookings->firstItem() ?? 0 }} - {{ $bookings->lastItem() ?? 0 }} dari {{ $bookings->total() }} booking
                        </div>
                        {{ $bookings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Detail Modal -->
<div class="modal fade" id="bookingDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="bookingDetailContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewBooking(bookingCode) {
    fetch(`/admin/bookings/${bookingCode}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('bookingDetailContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Booking</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Booking Code:</strong></td><td>${data.booking_code}</td></tr>
                            <tr><td><strong>Penumpang:</strong></td><td>${data.user.name}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${data.user.email}</td></tr>
                            <tr><td><strong>Telepon:</strong></td><td>${data.user.phone || '-'}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge bg-success">${data.status}</span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Informasi Perjalanan</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Rute:</strong></td><td>${data.schedule.route.origin} - ${data.schedule.route.destination}</td></tr>
                            <tr><td><strong>Tanggal:</strong></td><td>${new Date(data.travel_date).toLocaleDateString('id-ID')}</td></tr>
                            <tr><td><strong>Waktu:</strong></td><td>${data.schedule.departure_time}</td></tr>
                            <tr><td><strong>Kursi:</strong></td><td>${data.seats.map(s => s.seat_number).join(', ')}</td></tr>
                            <tr><td><strong>Total:</strong></td><td>Rp ${new Intl.NumberFormat('id-ID').format(data.total_amount)}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('bookingDetailModal')).show();
        });
}

function confirmBooking(bookingCode) {
    if (confirm('Konfirmasi booking ini?')) {
        fetch(`/admin/bookings/${bookingCode}/confirm`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}

function cancelBooking(bookingCode) {
    if (confirm('Batalkan booking ini?')) {
        fetch(`/admin/bookings/${bookingCode}/cancel`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}

// Filter functionality
document.getElementById('statusFilter').addEventListener('change', function() {
    // Implementation for status filter
});

document.getElementById('routeFilter').addEventListener('change', function() {
    // Implementation for route filter
});

document.getElementById('dateFilter').addEventListener('change', function() {
    // Implementation for date filter
});

document.getElementById('searchBooking').addEventListener('input', function() {
    // Implementation for search
});
</script>
@endsection
