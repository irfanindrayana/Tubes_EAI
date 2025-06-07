<div class="table-responsive">
    <table class="table table-striped table-hover">        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Role</th>
                <th>Bergabung</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                <span class="text-white fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold">{{ $user->name }}</div>
                            @if($user->userProfile)
                            <small class="text-muted">{{ $user->userProfile->address }}</small>
                            @endif
                        </div>
                    </div>
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone ?? '-' }}</td>                <td>
                    <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : 'primary' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td>{{ $user->created_at->format('d M Y') }}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-info" onclick="viewUser({{ $user->id }})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="editUser({{ $user->id }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        @if($user->id != auth()->id())
                        <button type="button" class="btn btn-outline-danger" onclick="deleteUser({{ $user->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Tidak ada pengguna ditemukan</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
