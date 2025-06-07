@extends('layouts.app')

@section('styles')
<style>
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.2rem;
}

.btn-group-sm .btn i {
    font-size: 0.875rem;
}

.input-group .btn i {
    font-size: 0.875rem;
}

.card-header i {
    margin-right: 0.5rem;
}

.fas, .far, .fab {
    font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 6 Brands" !important;
}

.table .btn-group {
    white-space: nowrap;
}

.pagination-container {
    margin-top: 1rem;
}

.filter-section {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> Manajemen Pengguna
                    </h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus"></i> Tambah Pengguna
                    </button>
                </div>
                  <div class="card-body">                    <!-- Filters -->
                    <div class="filter-section">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Filter Role</label>
                                <select class="form-select" id="roleFilter">
                                    <option value="">Semua Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="konsumen">Konsumen</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pencarian</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchUser" placeholder="Cari nama atau email...">
                                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div><!-- Users Table -->
                    <div id="usersTableContainer">
                        @include('admin.users.table')
                    </div>                    <!-- Pagination -->
                    <div id="paginationContainer" class="pagination-container">
                        @include('admin.users.pagination')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>            <form action="{{ route('admin.users.store') }}" method="POST" id="addUserForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="konsumen">Konsumen</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="birth_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="gender" class="form-select">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="male">Laki-laki</option>
                                    <option value="female">Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- User Detail Modal -->
<div class="modal fade" id="userDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="userDetailContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
@include('admin.users.edit-modal')

@include('admin.users.edit-modal')

<script>
// Global variables for managing filters
let searchTimeout;

// View user details
function viewUser(userId) {
    fetch(`/admin/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('userDetailContent').innerHTML = `
                <div class="row">
                    <div class="col-md-8">                        <h6>Informasi Pribadi</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Nama:</strong></td><td>${data.name}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${data.email}</td></tr>
                            <tr><td><strong>Telepon:</strong></td><td>${data.phone || '-'}</td></tr>
                            <tr><td><strong>Alamat:</strong></td><td>${data.address || '-'}</td></tr>
                            <tr><td><strong>Tanggal Lahir:</strong></td><td>${data.birth_date ? new Date(data.birth_date).toLocaleDateString('id-ID') : '-'}</td></tr>
                            <tr><td><strong>Jenis Kelamin:</strong></td><td>${data.gender ? (data.gender === 'male' ? 'Laki-laki' : 'Perempuan') : '-'}</td></tr>
                            <tr><td><strong>Role:</strong></td><td><span class="badge bg-${data.role == 'admin' ? 'danger' : 'primary'}">${data.role}</span></td></tr>
                            <tr><td><strong>Bergabung:</strong></td><td>${new Date(data.created_at).toLocaleDateString('id-ID')}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h6>Statistik</h6>
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="h4 text-primary">${data.bookings_count || 0}</div>
                                    <small>Total Booking</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('userDetailModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat detail pengguna');
        });
}

// Edit user
function editUser(userId) {
    fetch(`/admin/users/${userId}`)
        .then(response => response.json())        .then(data => {
            // Populate edit form
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_phone').value = data.phone || '';
            document.getElementById('edit_role').value = data.role;
            document.getElementById('edit_address').value = data.address || '';
            document.getElementById('edit_birth_date').value = data.birth_date || '';
            document.getElementById('edit_gender').value = data.gender || '';
            
            // Update form action
            document.getElementById('editUserForm').action = `/admin/users/${userId}`;
            
            // Show modal
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data pengguna');
        });
}

// Delete user
function deleteUser(userId) {
    if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pengguna berhasil dihapus');
                filterUsers();
            } else {
                alert(data.message || 'Gagal menghapus pengguna');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menghapus pengguna');
        });
    }
}

// Filter users with AJAX
function filterUsers() {
    const search = document.getElementById('searchUser').value;
    const role = document.getElementById('roleFilter').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (role) params.append('role', role);
    
    // Show loading indicator
    document.getElementById('usersTableContainer').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Memuat data...</p></div>';
    
    fetch(`/admin/users?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        document.getElementById('usersTableContainer').innerHTML = data.html;
        document.getElementById('paginationContainer').innerHTML = data.pagination;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('usersTableContainer').innerHTML = '<div class="alert alert-danger">Gagal memuat data pengguna. Silakan coba lagi.</div>';
    });
}

// Search functionality with debounce
document.getElementById('searchUser').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filterUsers();
    }, 500);
});

// Search button click
document.getElementById('searchButton').addEventListener('click', function() {
    filterUsers();
});

// Role filter
document.getElementById('roleFilter').addEventListener('change', function() {
    filterUsers();
});

// Handle edit form submission
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));
            
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            filterUsers();
        } else {
            throw new Error(data.message || 'Gagal mengupdate pengguna');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Handle add user form submission
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    submitBtn.disabled = true;
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));
            
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            this.reset(); // Reset form
            filterUsers();
        } else {
            throw new Error(data.message || 'Gagal menambahkan pengguna');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Handle pagination clicks
document.addEventListener('click', function(e) {
    if (e.target.closest('.pagination a')) {
        e.preventDefault();
        const url = e.target.closest('.pagination a').href;
        
        // Show loading indicator
        document.getElementById('usersTableContainer').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Memuat data...</p></div>';
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('usersTableContainer').innerHTML = data.html;
            document.getElementById('paginationContainer').innerHTML = data.pagination;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('usersTableContainer').innerHTML = '<div class="alert alert-danger">Gagal memuat data pengguna. Silakan coba lagi.</div>';
        });
    }
});
</script>
@endsection
