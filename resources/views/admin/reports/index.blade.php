@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Laporan Keuangan
                    </h5>
                    <div>
                        <button type="button" class="btn btn-success" onclick="exportReport()">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                            <i class="fas fa-plus"></i> Generate Laporan
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Date Range Filter -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" id="startDate" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" id="endDate" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary d-block" onclick="filterReport()">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quick Range</label>
                            <select class="form-select" id="quickRange" onchange="setQuickRange()">
                                <option value="">Pilih Range</option>
                                <option value="today">Hari Ini</option>
                                <option value="week">Minggu Ini</option>
                                <option value="month">Bulan Ini</option>
                                <option value="quarter">Kuartal Ini</option>
                                <option value="year">Tahun Ini</option>
                            </select>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</h4>
                                            <p class="mb-0">Total Pendapatan</p>
                                        </div>
                                        <i class="fas fa-money-bill-wave fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $summary['total_bookings'] ?? 0 }}</h4>
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
                                            <h4>Rp {{ number_format($summary['avg_transaction'] ?? 0, 0, ',', '.') }}</h4>
                                            <p class="mb-0">Rata-rata Transaksi</p>
                                        </div>
                                        <i class="fas fa-calculator fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $summary['growth_rate'] ?? 0 }}%</h4>
                                            <p class="mb-0">Pertumbuhan</p>
                                        </div>
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Grafik Pendapatan Harian</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="revenueChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Pembagian Rute</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="routeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Reports Table -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Pendapatan per Rute</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Rute</th>
                                                    <th>Booking</th>
                                                    <th>Pendapatan</th>
                                                    <th>%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($routeRevenue ?? [] as $route)
                                                <tr>
                                                    <td>{{ $route['name'] }}</td>
                                                    <td>{{ $route['bookings'] }}</td>
                                                    <td>Rp {{ number_format($route['revenue'], 0, ',', '.') }}</td>
                                                    <td>{{ $route['percentage'] }}%</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">Tidak ada data</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Metode Pembayaran</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Metode</th>
                                                    <th>Transaksi</th>
                                                    <th>Nilai</th>
                                                    <th>%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($paymentMethods ?? [] as $method)
                                                <tr>
                                                    <td>{{ $method['name'] }}</td>
                                                    <td>{{ $method['count'] }}</td>
                                                    <td>Rp {{ number_format($method['amount'], 0, ',', '.') }}</td>
                                                    <td>{{ $method['percentage'] }}%</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">Tidak ada data</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Transaksi Terbaru</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Booking Code</th>
                                            <th>Penumpang</th>
                                            <th>Rute</th>
                                            <th>Metode</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentTransactions ?? [] as $transaction)
                                        <tr>
                                            <td>{{ $transaction['date'] }}</td>
                                            <td>{{ $transaction['booking_code'] }}</td>
                                            <td>{{ $transaction['customer'] }}</td>
                                            <td>{{ $transaction['route'] }}</td>
                                            <td>{{ $transaction['method'] }}</td>
                                            <td>Rp {{ number_format($transaction['amount'], 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ $transaction['status'] }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi</td>
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
</div>

<!-- Generate Report Modal -->
<div class="modal fade" id="generateReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.reports.generate') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipe Laporan</label>
                        <select name="report_type" class="form-select" required>
                            <option value="">Pilih Tipe</option>
                            <option value="revenue">Pendapatan</option>
                            <option value="booking">Booking</option>
                            <option value="route">Rute</option>
                            <option value="payment">Pembayaran</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select" required>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Keterangan laporan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: @json($chartData['labels'] ?? []),
        datasets: [{
            label: 'Pendapatan',
            data: @json($chartData['revenue'] ?? []),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                    }
                }
            }
        }
    }
});

// Route Chart
const routeCtx = document.getElementById('routeChart').getContext('2d');
const routeChart = new Chart(routeCtx, {
    type: 'doughnut',
    data: {
        labels: @json($pieData['labels'] ?? []),
        datasets: [{
            data: @json($pieData['data'] ?? []),
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#FF6384',
                '#36A2EB'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});

function setQuickRange() {
    const quickRange = document.getElementById('quickRange').value;
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const today = new Date();
    
    switch(quickRange) {
        case 'today':
            startDate.value = today.toISOString().split('T')[0];
            endDate.value = today.toISOString().split('T')[0];
            break;
        case 'week':
            const weekStart = new Date(today.setDate(today.getDate() - today.getDay()));
            startDate.value = weekStart.toISOString().split('T')[0];
            endDate.value = new Date().toISOString().split('T')[0];
            break;
        case 'month':
            startDate.value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            endDate.value = new Date().toISOString().split('T')[0];
            break;
        case 'quarter':
            const quarter = Math.floor(today.getMonth() / 3);
            startDate.value = new Date(today.getFullYear(), quarter * 3, 1).toISOString().split('T')[0];
            endDate.value = new Date().toISOString().split('T')[0];
            break;
        case 'year':
            startDate.value = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            endDate.value = new Date().toISOString().split('T')[0];
            break;
    }
}

function filterReport() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (startDate && endDate) {
        window.location.href = `{{ route('admin.reports.index') }}?start_date=${startDate}&end_date=${endDate}`;
    }
}

function exportReport() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    window.location.href = `{{ route('admin.reports.export') }}?start_date=${startDate}&end_date=${endDate}&format=pdf`;
}
</script>
@endsection
