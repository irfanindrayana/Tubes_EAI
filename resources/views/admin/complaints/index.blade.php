@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Manajemen Keluhan
                    </h5>
                </div>
                
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
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
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $stats['in_progress'] ?? 0 }}</h4>
                                            <p class="mb-0">Diproses</p>
                                        </div>
                                        <i class="fas fa-cog fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $stats['resolved'] ?? 0 }}</h4>
                                            <p class="mb-0">Selesai</p>
                                        </div>
                                        <i class="fas fa-check fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>{{ $stats['total'] ?? 0 }}</h4>
                                            <p class="mb-0">Total</p>
                                        </div>
                                        <i class="fas fa-list fa-2x"></i>
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
                                <option value="in_progress">Diproses</option>
                                <option value="resolved">Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="priorityFilter">
                                <option value="">Semua Prioritas</option>
                                <option value="low">Rendah</option>
                                <option value="medium">Sedang</option>
                                <option value="high">Tinggi</option>
                                <option value="urgent">Mendesak</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="categoryFilter">
                                <option value="">Semua Kategori</option>
                                <option value="service">Layanan</option>
                                <option value="payment">Pembayaran</option>
                                <option value="booking">Booking</option>
                                <option value="technical">Teknis</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchComplaint" placeholder="Cari subjek atau nama...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Complaints Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Pengirim</th>
                                    <th>Subjek</th>
                                    <th>Kategori</th>
                                    <th>Prioritas</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th>Rating</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($complaints as $complaint)
                                <tr class="{{ $complaint->priority == 'urgent' ? 'table-danger' : ($complaint->priority == 'high' ? 'table-warning' : '') }}">
                                    <td>#{{ $complaint->id }}</td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $complaint->user->name }}</div>
                                            <small class="text-muted">{{ $complaint->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ Str::limit($complaint->subject, 30) }}</div>
                                            <small class="text-muted">{{ Str::limit($complaint->description, 50) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($complaint->category) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $complaint->priority == 'urgent' ? 'danger' : 
                                            ($complaint->priority == 'high' ? 'warning' : 
                                            ($complaint->priority == 'medium' ? 'info' : 'secondary')) 
                                        }}">
                                            {{ ucfirst($complaint->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $complaint->status == 'resolved' ? 'success' : 
                                            ($complaint->status == 'in_progress' ? 'info' : 'warning') 
                                        }}">
                                            {{ ucfirst($complaint->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $complaint->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($complaint->rating)
                                        <div class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star{{ $i <= $complaint->rating ? '' : '-o' }}"></i>
                                            @endfor
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" onclick="viewComplaint({{ $complaint->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($complaint->status != 'resolved')
                                            <button type="button" class="btn btn-outline-primary" onclick="respondComplaint({{ $complaint->id }})">
                                                <i class="fas fa-reply"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success" onclick="resolveComplaint({{ $complaint->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada keluhan ditemukan</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Menampilkan {{ $complaints->firstItem() ?? 0 }} - {{ $complaints->lastItem() ?? 0 }} dari {{ $complaints->total() }} keluhan
                        </div>
                        {{ $complaints->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complaint Detail Modal -->
<div class="modal fade" id="complaintDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Keluhan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="complaintDetailContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tanggapi Keluhan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="responseForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="in_progress">Sedang Diproses</option>
                            <option value="resolved">Selesai</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggapan</label>
                        <textarea name="response" class="form-control" rows="4" required placeholder="Tulis tanggapan Anda..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Tanggapan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentComplaintId = null;

function viewComplaint(complaintId) {
    fetch(`/admin/complaints/${complaintId}`)
        .then(response => response.json())
        .then(data => {
            let responseHtml = '';
            if (data.admin_responses && data.admin_responses.length > 0) {
                responseHtml = '<h6 class="mt-4">Tanggapan Admin:</h6>';
                data.admin_responses.forEach(response => {
                    responseHtml += `
                        <div class="border p-3 mb-2 rounded">
                            <div class="d-flex justify-content-between">
                                <strong>${response.admin.name}</strong>
                                <small class="text-muted">${new Date(response.created_at).toLocaleDateString('id-ID')}</small>
                            </div>
                            <p class="mt-2 mb-0">${response.response}</p>
                        </div>
                    `;
                });
            }

            document.getElementById('complaintDetailContent').innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <h6>Detail Keluhan</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Subjek:</strong></td><td>${data.subject}</td></tr>
                            <tr><td><strong>Kategori:</strong></td><td><span class="badge bg-secondary">${data.category}</span></td></tr>
                            <tr><td><strong>Prioritas:</strong></td><td><span class="badge bg-warning">${data.priority}</span></td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge bg-info">${data.status}</span></td></tr>
                        </table>
                        <h6>Deskripsi:</h6>
                        <p class="border p-3 rounded bg-light">${data.description}</p>
                        ${responseHtml}
                    </div>
                    <div class="col-md-4">
                        <h6>Informasi Pengirim</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Nama:</strong></td><td>${data.user.name}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${data.user.email}</td></tr>
                            <tr><td><strong>Telepon:</strong></td><td>${data.user.phone || '-'}</td></tr>
                        </table>
                        <h6>Timeline</h6>
                        <small class="text-muted">Dibuat: ${new Date(data.created_at).toLocaleDateString('id-ID')}</small><br>
                        <small class="text-muted">Diupdate: ${new Date(data.updated_at).toLocaleDateString('id-ID')}</small>
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('complaintDetailModal')).show();
        });
}

function respondComplaint(complaintId) {
    currentComplaintId = complaintId;
    new bootstrap.Modal(document.getElementById('responseModal')).show();
}

function resolveComplaint(complaintId) {
    if (confirm('Tandai keluhan ini sebagai selesai?')) {
        fetch(`/admin/complaints/${complaintId}/resolve`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}

document.getElementById('responseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch(`/admin/complaints/${currentComplaintId}/respond`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    }).then(() => {
        bootstrap.Modal.getInstance(document.getElementById('responseModal')).hide();
        location.reload();
    });
});

// Filter functionality
document.getElementById('statusFilter').addEventListener('change', function() {
    // Implementation for status filter
});

document.getElementById('priorityFilter').addEventListener('change', function() {
    // Implementation for priority filter
});

document.getElementById('categoryFilter').addEventListener('change', function() {
    // Implementation for category filter
});

document.getElementById('searchComplaint').addEventListener('input', function() {
    // Implementation for search
});
</script>
@endsection
