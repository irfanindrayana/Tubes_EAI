<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InboxService
{
    /**
     * Get inbox messages for a user with proper cross-database handling
     */
    public function getInboxMessages($user, $perPage = 15)
    {
        // Get messages where user is recipient or sender
        $messages = Message::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhereExists(function($q) use ($user) {
                      $q->select(\DB::raw(1))
                        ->from('message_recipients')
                        ->whereRaw('message_recipients.message_id = messages.id')
                        ->where('message_recipients.recipient_id', $user->id);
                  });
        })
        ->latest()
        ->paginate($perPage);

        // Load relationships manually for each message
        foreach ($messages as $message) {
            $this->loadMessageRelationships($message);
        }

        return $messages;
    }

    /**
     * Load relationships for a single message
     */
    public function loadMessageRelationships($message)
    {
        // Load sender information from user_management database
        if ($message->sender_id) {
            $message->sender = User::find($message->sender_id);
        }
          // Load recipients information
        $messageRecipients = MessageRecipient::where('message_id', $message->id)->get();
        $recipientIds = $messageRecipients->pluck('recipient_id');
        
        if ($recipientIds->isNotEmpty()) {
            $recipients = User::whereIn('id', $recipientIds)->get();
            
            // Add pivot data for each recipient
            foreach ($recipients as $recipient) {
                $pivotData = $messageRecipients->where('recipient_id', $recipient->id)->first();
                if ($pivotData) {
                    $recipient->pivot = $pivotData;
                }
            }
            
            // Ensure recipients is set as a collection
            $message->recipients = $recipients;
        } else {
            // Ensure recipients is always a collection, even if empty
            $message->recipients = collect();
        }
    }

    /**
     * Get unread message count for a user
     */
    public function getUnreadCount($user)
    {
        return MessageRecipient::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Mark message as read for a user
     */
    public function markAsRead($messageId, $userId)
    {
        return MessageRecipient::where('message_id', $messageId)
            ->where('recipient_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Check if user has access to a message
     */
    public function userHasAccess($message, $user)
    {
        return $message->sender_id === $user->id || 
               MessageRecipient::where('message_id', $message->id)
                   ->where('recipient_id', $user->id)
                   ->exists();
    }

    /**
     * Send a new message
     */
    public function sendMessage($data)
    {
        $user = Auth::user();

        // Map message_type to the correct type value
        $typeMapping = [
            'complaint' => 'support',
            'inquiry' => 'personal', 
            'feedback' => 'personal'
        ];

        // Create message
        $message = Message::create([
            'message_code' => 'MSG-' . now()->format('YmdHis') . '-' . $user->id,
            'sender_id' => $user->id,
            'subject' => $data['subject'],
            'content' => $data['body'],
            'type' => $typeMapping[$data['message_type']] ?? 'personal',
            'priority' => 'normal',
            'sent_at' => now(),
        ]);

        // Create recipient record
        MessageRecipient::create([
            'message_id' => $message->id,
            'recipient_id' => $data['recipient_id'],
            'read_at' => null,
            'is_starred' => false,
            'is_archived' => false,
        ]);        // Create notification for recipient
        Notification::create([
            'notification_code' => 'NOTIF-' . strtoupper(\Str::random(8)),
            'user_id' => $data['recipient_id'],
            'title' => 'New Message: ' . $data['subject'],
            'content' => 'You have received a new message from ' . $user->name,
            'type' => 'info',
            'sent_at' => now(),
        ]);

        return $message;
    }
}
