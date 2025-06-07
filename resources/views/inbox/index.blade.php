@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <!-- Sidebar -->
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Inbox</h6>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('inbox.index') }}" class="list-group-item list-group-item-action active">
                            <i class="bi bi-inbox me-2"></i>All Messages
                            @if($unreadCount > 0)
                                <span class="badge bg-primary rounded-pill float-end">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#composeModal">
                            <i class="bi bi-plus-circle me-2"></i>Compose Message
                        </a>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            @if($notifications->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Recent Notifications</h6>
                </div>
                <div class="card-body">
                    @foreach($notifications->take(5) as $notification)
                    <div class="d-flex align-items-start mb-2 {{ $notification->is_read ? 'text-muted' : '' }}">
                        <i class="bi bi-{{ $notification->type === 'payment' ? 'credit-card' : ($notification->type === 'booking' ? 'ticket-perforated' : 'envelope') }} me-2 mt-1"></i>                        <div class="flex-grow-1">
                            <div class="fw-bold small">{{ $notification->title }}</div>
                            <div class="small">{{ Str::limit($notification->content, 50) }}</div>
                            <div class="text-muted small">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-envelope me-2"></i>Messages</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeModal">
                    <i class="bi bi-plus-circle me-2"></i>New Message
                </button>
            </div>

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
                <div class="card-body p-0">
                    @forelse($messages as $message)                        @php
                            $recipients = $message->recipients ?? collect();
                            $isRecipient = $recipients->where('id', Auth::id())->first();
                            $isUnread = $isRecipient && $isRecipient->pivot && !$isRecipient->pivot->read_at;
                            $isSender = $message->sender_id === Auth::id();
                        @endphp
                        <div class="d-flex align-items-center p-3 border-bottom message-item {{ $isUnread ? 'bg-light' : '' }}" 
                             style="cursor: pointer;" 
                             onclick="window.location.href='{{ route('inbox.show', $message) }}'">
                            
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>                                        <div class="fw-bold {{ $isUnread ? 'text-primary' : '' }}">
                                            @php
                                                $recipients = $message->recipients ?? collect();
                                            @endphp
                                            {{ $isSender ? 'To: ' . ($recipients->first() ? $recipients->first()->name : 'Unknown') : 'From: ' . ($message->sender ? $message->sender->name : 'Unknown') }}
                                        </div>
                                        <div class="text-truncate {{ $isUnread ? 'fw-bold' : '' }}" style="max-width: 400px;">
                                            {{ $message->subject }}
                                        </div>                                        <div class="text-muted small text-truncate" style="max-width: 500px;">
                                            {{ Str::limit(strip_tags($message->content), 80) }}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted small">{{ $message->created_at->diffForHumans() }}</div>                                        <div>
                                            <span class="badge bg-{{ $message->type === 'support' ? 'danger' : ($message->type === 'personal' ? 'primary' : 'info') }}">
                                                {{ ucfirst($message->type) }}
                                            </span>
                                            @if($isUnread)
                                                <span class="badge bg-warning ms-1">New</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-envelope display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">No Messages</h4>
                            <p class="text-muted">You don't have any messages yet.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeModal">
                                <i class="bi bi-plus-circle me-2"></i>Send Your First Message
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>

            {{ $messages->links() }}
        </div>
    </div>
</div>

<!-- Compose Message Modal -->
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compose Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inbox.send') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="recipient_id" class="form-label">To <span class="text-danger">*</span></label>
                        <select class="form-select" id="recipient_id" name="recipient_id" required>
                            <option value="">Select Recipient</option>
                            @if(Auth::user()->isAdmin())
                                @php
                                    $users = \App\Models\User::where('role', 'konsumen')->get();
                                @endphp
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            @else
                                @php
                                    $admins = \App\Models\User::where('role', 'admin')->get();
                                @endphp
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }} (Admin)</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="message_type" class="form-label">Message Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="message_type" name="message_type" required>
                            <option value="">Select Type</option>
                            <option value="complaint">Complaint</option>
                            <option value="inquiry">Inquiry</option>
                            <option value="feedback">Feedback</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>

                    <div class="mb-3">
                        <label for="body" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="body" name="body" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-2"></i>Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.message-item:hover {
    background-color: #f8f9fa !important;
}
</style>
@endpush
@endsection
