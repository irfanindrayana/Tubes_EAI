@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $message->subject }}</h5>
                        <small class="text-muted">{{ $message->created_at->format('d M Y H:i') }}</small>
                    </div>
                    <a href="{{ route('inbox.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Message Thread -->
                    <div class="message-thread">
                        <!-- Original Message -->
                        <div class="message-item mb-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="message-sender">
                                    <strong>{{ $message->sender->name }}</strong>
                                    <small class="text-muted d-block">{{ $message->sender->email }}</small>
                                </div>
                                <small class="text-muted">{{ $message->created_at->format('d M Y H:i') }}</small>
                            </div>                            <div class="message-content mt-3 p-3 bg-light rounded">
                                {!! nl2br(e($message->content)) !!}
                            </div>
                            
                            @if($message->attachment_path)
                            <div class="message-attachment mt-2">
                                <i class="fas fa-paperclip"></i>
                                <a href="{{ Storage::url($message->attachment_path) }}" target="_blank" class="text-decoration-none">
                                    {{ basename($message->attachment_path) }}
                                </a>
                            </div>
                            @endif
                        </div>                        <!-- Admin responses functionality not available for inbox messages -->
                    </div>

                    <!-- Reply Form (Admin Only) -->
                    @if(auth()->user()->isAdmin())
                    <div class="reply-section mt-4 pt-4 border-top">
                        <h6>Balas Pesan</h6>
                        <form action="{{ route('admin.messages.reply', $message) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea name="response" class="form-control" rows="4" placeholder="Tulis balasan..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-reply"></i> Kirim Balasan
                            </button>
                        </form>
                    </div>
                    @endif

                    <!-- Customer Actions -->
                    @if(!auth()->user()->isAdmin() && $message->sender_id == auth()->id())
                    <div class="customer-actions mt-4 pt-4 border-top">
                        @if($message->status != 'resolved')
                        <form action="{{ route('messages.resolve', $message) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Tandai Selesai
                            </button>
                        </form>
                        @endif
                        
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ratingModal">
                            <i class="fas fa-star"></i> Beri Rating
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Beri Rating</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.messages.rate', $message) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                            <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" required>
                            <label for="star{{ $i }}" class="star">
                                <i class="fas fa-star"></i>
                            </label>
                            @endfor
                        </div>
                    </div>                    <div class="mb-3">
                        <label class="form-label">Komentar (opsional)</label>
                        <textarea name="feedback" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Rating</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.rating-stars {
    display: flex;
    gap: 5px;
}

.rating-stars input[type="radio"] {
    display: none;
}

.rating-stars .star {
    font-size: 1.5rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-stars input[type="radio"]:checked ~ .star,
.rating-stars .star:hover,
.rating-stars .star:hover ~ .star {
    color: #ffc107;
}

.message-item {
    border-left: 3px solid #e9ecef;
    padding-left: 1rem;
}

.message-thread .message-item:first-child {
    border-left-color: #007bff;
}

.message-thread .message-item.ms-4 {
    border-left-color: #28a745;
}
</style>
@endsection
