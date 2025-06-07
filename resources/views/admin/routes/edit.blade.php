@extends('layouts.app')

@section('content')
<div class="container">
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

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Edit Rute
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.routes.update', $route) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Nama Rute</label>
                                <input type="text" name="route_name" class="form-control" required 
                                    value="{{ old('route_name', $route->route_name) }}">
                                @error('route_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kota Asal</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                    <input type="text" name="origin" class="form-control" required 
                                        value="{{ old('origin', $route->origin) }}">
                                </div>
                                @error('origin')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kota Tujuan</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                    <input type="text" name="destination" class="form-control" required 
                                        value="{{ old('destination', $route->destination) }}">
                                </div>
                                @error('destination')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Jarak (km)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-rulers"></i></span>
                                    <input type="number" name="distance" class="form-control" step="0.1" min="1" required 
                                        value="{{ old('distance', $route->distance) }}">
                                    <span class="input-group-text">km</span>
                                </div>
                                @error('distance')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estimasi Durasi (menit)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="number" name="estimated_duration" class="form-control" min="1" required 
                                        value="{{ old('estimated_duration', $route->estimated_duration) }}">
                                    <span class="input-group-text">menit</span>
                                </div>
                                @error('estimated_duration')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Harga Dasar</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="base_price" class="form-control" min="0" required 
                                    value="{{ old('base_price', $route->base_price) }}">
                            </div>
                            @error('base_price')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Rute</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="routeActiveSwitch" 
                                    {{ old('is_active', $route->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="routeActiveSwitch">Rute Aktif</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pemberhentian</label>
                            <div id="stopsContainer" class="mb-2">                                @if($route->stops && is_array($route->stops) && count($route->stops) > 0)
                                    @foreach($route->stops as $stop)
                                        <div class="input-group mb-2 stop-item">
                                            <span class="input-group-text"><i class="bi bi-signpost-2"></i></span>
                                            <input type="text" name="stops[]" class="form-control" value="{{ $stop }}">
                                            <button type="button" class="btn btn-outline-danger remove-stop"><i class="bi bi-dash"></i></button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 stop-item">
                                        <span class="input-group-text"><i class="bi bi-signpost-2"></i></span>
                                        <input type="text" name="stops[]" class="form-control" placeholder="Nama Pemberhentian">
                                        <button type="button" class="btn btn-outline-danger remove-stop" disabled><i class="bi bi-dash"></i></button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary add-stop">
                                <i class="bi bi-plus-lg"></i> Tambah Pemberhentian
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Rute (Opsional)</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $route->description) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.routes') }}" class="btn btn-secondary">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add stop button functionality
    document.querySelector('.add-stop').addEventListener('click', function() {
        const container = document.getElementById('stopsContainer');
        const newItem = document.createElement('div');
        newItem.className = 'input-group mb-2 stop-item';
        newItem.innerHTML = `
            <span class="input-group-text"><i class="bi bi-signpost-2"></i></span>
            <input type="text" name="stops[]" class="form-control" placeholder="Nama Pemberhentian">
            <button type="button" class="btn btn-outline-danger remove-stop"><i class="bi bi-dash"></i></button>
        `;
        
        container.appendChild(newItem);
        
        // Enable all remove buttons
        updateRemoveButtons();
    });

    // Remove stop button functionality - use event delegation
    document.getElementById('stopsContainer').addEventListener('click', function(e) {
        const button = e.target.closest('.remove-stop');
        if (button) {
            button.closest('.stop-item').remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const buttons = document.querySelectorAll('.remove-stop');
        const disableButtons = buttons.length <= 1;
        
        buttons.forEach(btn => {
            btn.disabled = disableButtons;
        });
    }

    // Form validation
    const routeForm = document.querySelector('form[action="{{ route('admin.routes.update', $route) }}"]');
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
            
            const distance = parseFloat(document.querySelector('input[name="distance"]').value);
            if (isNaN(distance) || distance <= 0) {
                e.preventDefault();
                alert('Jarak harus lebih besar dari 0.');
                return false;
            }
            
            const duration = parseFloat(document.querySelector('input[name="estimated_duration"]').value);
            if (isNaN(duration) || duration <= 0) {
                e.preventDefault();
                alert('Estimasi durasi harus lebih besar dari 0.');
                return false;
            }
            
            return true;
        });
    }

    // Initialize
    updateRemoveButtons();
});
</script>
@endpush
@endsection
