<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\Notification;
use App\Models\User;
use App\Contracts\InboxServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class InboxController extends Controller
{
    protected InboxServiceInterface $inboxService;

    public function __construct(InboxServiceInterface $inboxService)
    {
        $this->middleware('auth');
        $this->inboxService = $inboxService;
    }

    /**
     * Display inbox messages.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get messages using the inbox service
        $messagesData = $this->inboxService->getUserMessages($user->id);
        
        // Convert messagesData array to Laravel Collection for view compatibility
        $messages = collect($messagesData['data'])->map(function ($messageArray) {
            // Convert array back to Message model-like object
            $message = new Message($messageArray);
            
            // IMPORTANT: Ensure ID is preserved from array
            if (isset($messageArray['id'])) {
                $message->id = $messageArray['id'];
            }
            
            // Set relationships
            if (isset($messageArray['sender'])) {
                $message->sender = (object) $messageArray['sender'];
            }
            
            if (isset($messageArray['recipients'])) {
                $message->recipients = collect($messageArray['recipients'])->map(function ($recipient) {
                    $recipientObj = (object) $recipient;
                    // Ensure pivot data is available as an object
                    if (isset($recipient['pivot'])) {
                        $recipientObj->pivot = (object) $recipient['pivot'];
                    }
                    return $recipientObj;
                });
            } else {
                $message->recipients = collect();
            }
            
            // Ensure dates are properly formatted
            if (isset($messageArray['created_at'])) {
                $message->created_at = \Carbon\Carbon::parse($messageArray['created_at']);
            }
            if (isset($messageArray['updated_at'])) {
                $message->updated_at = \Carbon\Carbon::parse($messageArray['updated_at']);
            }
            
            return $message;
        });
        
        // Create pagination object
        $messages = new \Illuminate\Pagination\LengthAwarePaginator(
            $messages,
            $messagesData['total'],
            $messagesData['per_page'],
            $messagesData['current_page'],
            ['path' => request()->url()]
        );
        
        // Get notifications
        $notificationsData = $this->inboxService->getUserNotifications($user->id);
        $notifications = collect($notificationsData)->map(function ($notif) {
            return (object) $notif;
        });
        
        // Get unread count
        $unreadCount = MessageRecipient::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('inbox.index', compact('messages', 'notifications', 'unreadCount'));
    }

    /**
     * Show specific message.
     */
    public function show(Message $message)
    {
        $user = Auth::user();
        
        // Check if user has access to this message
        if (!$this->inboxService->userHasAccess($message, $user)) {
            abort(403);
        }

        // Mark as read if user is recipient
        $this->inboxService->markAsRead($message->id, $user->id);

        // Load relationships using the service
        $this->inboxService->loadMessageRelationships($message);

        return view('inbox.show', compact('message'));
    }

    /**
     * Send new message.
     */
    public function send(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'message_type' => 'required|in:complaint,inquiry,feedback'
        ]);

        return DB::transaction(function () use ($request) {
            $result = $this->inboxService->sendMessage($request->all());
            
            if ($result['success']) {
                return redirect()->route('inbox.index')
                    ->with('success', 'Message sent successfully!');
            } else {
                return redirect()->back()
                    ->with('error', 'Failed to send message: ' . $result['error'])
                    ->withInput();
            }
        });
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(Message $message)
    {
        $user = Auth::user();
        
        $this->inboxService->markAsRead($message->id, $user->id);

        return response()->json(['success' => true]);
    }

    /**
     * Reply to a message (Admin only).
     */
    public function reply(Message $message, Request $request)
    {
        // Ensure only admin can reply
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'response' => 'required|string|max:5000',
        ]);

        try {
            // Create a new message as reply
            $replyData = [
                'subject' => 'Re: ' . $message->subject,
                'content' => $request->response,
                'recipient_id' => $message->sender_id,
                'type' => 'personal',
                'priority' => 'normal'
            ];

            $this->inboxService->createMessage($replyData, Auth::user());

            return redirect()->route('inbox.show', $message)
                ->with('success', 'Reply sent successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send reply: ' . $e->getMessage());
        }
    }

    /**
     * Rate a message (Customer only).
     */
    public function rate(Message $message, Request $request)
    {
        $user = Auth::user();
        
        // Check if user has access to this message (either sender or recipient)
        if (!$this->inboxService->userHasAccess($message, $user)) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        try {
            // Store rating in message_recipients table or create a separate ratings table
            // For now, we'll add it as a notification to admin
            Notification::create([
                'notification_code' => 'RATING-' . strtoupper(\Str::random(8)),
                'user_id' => 1, // Assuming admin user ID is 1
                'title' => 'New Message Rating',
                'content' => "Message '{$message->subject}' was rated {$request->rating}/5 stars" . 
                           ($request->feedback ? " with feedback: {$request->feedback}" : ''),
                'type' => 'info',
                'sent_at' => now(),
            ]);

            return redirect()->route('inbox.show', $message)
                ->with('success', 'Thank you for your rating!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to submit rating: ' . $e->getMessage());
        }
    }
}
