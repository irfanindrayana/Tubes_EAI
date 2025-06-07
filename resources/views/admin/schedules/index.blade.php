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
                        <i class="bi bi-calendar me-2"></i> Manajemen Jadwal
                    </h5>
                    <a href="{{ route('admin.routes') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Rute
                    </a>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Rute</th>
                                    <th>Tanggal & Waktu Berangkat</th>
                                    <th>Waktu Tiba</th>
                                    <th>Bus</th>
                                    <th>Kapasitas</th>
                                    <th>Booking</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->id }}</td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $schedule->route->origin }} → {{ $schedule->route->destination }}</div>
                                            <small class="text-muted">{{ $schedule->route->route_name ?? 'Route '.$schedule->route->id }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('d M Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($schedule->arrival_time)
                                            {{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $schedule->bus_number ?? 'BUS-' . $schedule->id }}</td>
                                    <td>{{ $schedule->capacity ?? $schedule->seats_count }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            @php
                                                $booked = $schedule->bookings_count ?? 0;
                                                $capacity = $schedule->capacity ?? $schedule->seats_count ?? 1;
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
                                            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-outline-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.schedules.toggle', $schedule) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn {{ $schedule->is_active ? 'btn-outline-danger' : 'btn-success' }}">
                                                    <i class="bi bi-{{ $schedule->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="bi bi-calendar-x display-3 text-muted mb-3 d-block"></i>
                                        <h5 class="text-muted">Belum ada jadwal tersedia</h5>
                                        <p class="text-muted">Klik "Manajemen Rute" untuk membuat jadwal baru.</p>
                                        <a href="{{ route('admin.routes') }}" class="btn btn-primary mt-2">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah Jadwal
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Menampilkan {{ $schedules->firstItem() ?? 0 }} - {{ $schedules->lastItem() ?? 0 }} dari {{ $schedules->total() }} jadwal
                        </div>
                        {{ $schedules->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
