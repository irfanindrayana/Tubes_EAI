<?php

namespace App\Services\Inbox;

use App\Contracts\InboxServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InboxService implements InboxServiceInterface
{
    protected UserServiceInterface $userService;
    
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }
      /**
     * Send message
     */
    public function sendMessage(array $messageData): array
    {
        try {
            $user = Auth::user();
            
            // Handle different input formats
            if (isset($messageData['body'])) {
                // Format from inbox form submission
                $typeMapping = [
                    'complaint' => 'support',
                    'inquiry' => 'personal', 
                    'feedback' => 'personal'
                ];
                
                $message = Message::create([
                    'message_code' => $this->generateMessageCode(),
                    'sender_id' => $user->id,
                    'subject' => $messageData['subject'],
                    'content' => $messageData['body'],
                    'type' => $typeMapping[$messageData['message_type']] ?? 'personal',
                    'priority' => 'normal',
                    'sent_at' => now(),
                ]);

                // Create recipient record
                MessageRecipient::create([
                    'message_id' => $message->id,
                    'recipient_id' => $messageData['recipient_id'],
                    'read_at' => null,
                    'is_starred' => false,
                    'is_archived' => false,
                ]);

                // Create notification for recipient
                Notification::create([
                    'notification_code' => $this->generateNotificationCode(),
                    'user_id' => $messageData['recipient_id'],
                    'title' => 'New Message: ' . $messageData['subject'],
                    'content' => 'You have received a new message from ' . $user->name,
                    'type' => 'info',
                    'sent_at' => now(),
                ]);
                
                return [
                    'success' => true,
                    'message' => [
                        'id' => $message->id,
                        'message_code' => $message->message_code,
                        'subject' => $message->subject,
                        'content' => $message->content,
                        'type' => $message->type,
                        'sent_at' => $message->sent_at,
                    ],
                ];
            } else {
                // Format from API or other sources
                $message = Message::create([
                    'message_code' => $this->generateMessageCode(),
                    'sender_id' => $messageData['sender_id'],
                    'subject' => $messageData['subject'],
                    'content' => $messageData['content'],
                    'type' => $messageData['type'] ?? 'message',
                    'priority' => $messageData['priority'] ?? 'normal',
                    'sent_at' => now(),
                ]);
                
                // Create recipients
                if (isset($messageData['recipient_ids'])) {
                    foreach ($messageData['recipient_ids'] as $recipientId) {
                        MessageRecipient::create([
                            'message_id' => $message->id,
                            'recipient_id' => $recipientId,
                        ]);
                    }
                }
                
                return [
                    'success' => true,
                    'message' => [
                        'id' => $message->id,
                        'message_code' => $message->message_code,
                        'subject' => $message->subject,
                        'content' => $message->content,
                        'type' => $message->type,
                        'sent_at' => $message->sent_at,
                    ],
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get user messages
     */
    public function getUserMessages(int $userId, int $perPage = 15): array
    {
        // Get messages where user is recipient or sender
        $messages = Message::where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->orWhereExists(function($q) use ($userId) {
                      $q->select(\DB::raw(1))
                        ->from('message_recipients')
                        ->whereRaw('message_recipients.message_id = messages.id')
                        ->where('message_recipients.recipient_id', $userId);
                  });
        })
        ->latest()
        ->paginate($perPage);

        // Load relationships manually using UserService
        $messageData = [];
        foreach ($messages as $message) {
            $messageArray = $message->toArray();
            
            // Load sender information via UserService
            if ($message->sender_id) {
                $senderInfo = $this->userService->getUserBasicInfo($message->sender_id);
                $messageArray['sender'] = $senderInfo;
            }
              // Load recipients information with pivot data
            $recipients = MessageRecipient::where('message_id', $message->id)->get();
            $recipientIds = $recipients->pluck('recipient_id')->toArray();
            
            if (!empty($recipientIds)) {
                $recipientsInfo = $this->userService->findByIds($recipientIds);
                
                // Add pivot data to each recipient
                $recipientsWithPivot = [];
                foreach ($recipientsInfo as $recipientInfo) {
                    $pivotData = $recipients->where('recipient_id', $recipientInfo['id'])->first();
                    $recipientInfo['pivot'] = $pivotData ? [
                        'read_at' => $pivotData->read_at,
                        'is_starred' => $pivotData->is_starred ?? false,
                        'is_archived' => $pivotData->is_archived ?? false,
                    ] : null;
                    $recipientsWithPivot[] = $recipientInfo;
                }
                
                $messageArray['recipients'] = $recipientsWithPivot;
            }
            
            $messageData[] = $messageArray;
        }

        return [
            'data' => $messageData,
            'total' => $messages->total(),
            'per_page' => $messages->perPage(),
            'current_page' => $messages->currentPage(),
            'last_page' => $messages->lastPage(),
        ];
    }
    
    /**
     * Mark message as read
     */
    public function markAsRead(int $messageId, int $userId): bool
    {
        try {
            MessageRecipient::where('message_id', $messageId)
                ->where('recipient_id', $userId)
                ->update(['read_at' => now()]);
                
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if user has access to a message
     */
    public function userHasAccess($message, $user): bool
    {
        return $message->sender_id === $user->id || 
               MessageRecipient::where('message_id', $message->id)
                   ->where('recipient_id', $user->id)
                   ->exists();
    }
    
    /**
     * Load relationships for a single message
     */
    public function loadMessageRelationships($message): void
    {
        // Load sender information from UserService
        if ($message->sender_id) {
            $senderInfo = $this->userService->getUserBasicInfo($message->sender_id);
            $message->sender = (object) $senderInfo;
        }
        
        // Load recipients information with pivot data
        $recipients = MessageRecipient::where('message_id', $message->id)->get();
        $recipientIds = $recipients->pluck('recipient_id')->toArray();
        
        if (!empty($recipientIds)) {
            $recipientsInfo = $this->userService->findByIds($recipientIds);
            
            // Add pivot data to each recipient
            $recipientsWithPivot = collect();
            foreach ($recipientsInfo as $recipientInfo) {
                $pivotData = $recipients->where('recipient_id', $recipientInfo['id'])->first();
                $recipient = (object) $recipientInfo;
                $recipient->pivot = $pivotData ? (object) [
                    'read_at' => $pivotData->read_at,
                    'is_starred' => $pivotData->is_starred ?? false,
                    'is_archived' => $pivotData->is_archived ?? false,
                ] : null;
                $recipientsWithPivot->push($recipient);
            }
            
            $message->recipients = $recipientsWithPivot;
        } else {
            $message->recipients = collect();
        }
    }
    
    /**
     * Send notification
     */
    public function sendNotification(array $notificationData): array
    {
        try {
            $notification = Notification::create([
                'notification_code' => $this->generateNotificationCode(),
                'user_id' => $notificationData['user_id'],
                'title' => $notificationData['title'],
                'content' => $notificationData['content'],
                'type' => $notificationData['type'] ?? 'info',
                'is_read' => false,
            ]);
            
            return [
                'success' => true,
                'notification' => [
                    'id' => $notification->id,
                    'notification_code' => $notification->notification_code,
                    'title' => $notification->title,
                    'content' => $notification->content,
                    'type' => $notification->type,
                    'created_at' => $notification->created_at,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Create message (for admin replies)
     */
    public function createMessage(array $messageData, $user): array
    {
        try {
            $message = Message::create([
                'message_code' => $this->generateMessageCode(),
                'sender_id' => $user->id,
                'subject' => $messageData['subject'],
                'content' => $messageData['content'],
                'type' => $messageData['type'] ?? 'personal',
                'priority' => $messageData['priority'] ?? 'normal',
                'sent_at' => now(),
            ]);

            // Create recipient record
            MessageRecipient::create([
                'message_id' => $message->id,
                'recipient_id' => $messageData['recipient_id'],
                'read_at' => null,
                'is_starred' => false,
                'is_archived' => false,
            ]);

            // Create notification for recipient
            Notification::create([
                'notification_code' => $this->generateNotificationCode(),
                'user_id' => $messageData['recipient_id'],
                'title' => 'New Reply: ' . $messageData['subject'],
                'content' => 'You have received a reply from ' . $user->name,
                'type' => 'info',
                'sent_at' => now(),
            ]);
            
            return [
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message_code' => $message->message_code,
                    'subject' => $message->subject,
                    'content' => $message->content,
                    'type' => $message->type,
                    'sent_at' => $message->sent_at,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications(int $userId): array
    {
        $notifications = Notification::where('user_id', $userId)
            ->latest()
            ->take(20)
            ->get();
            
        return $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'notification_code' => $notification->notification_code,
                'title' => $notification->title,
                'content' => $notification->content,
                'type' => $notification->type,
                'is_read' => $notification->is_read,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        })->toArray();
    }
    
    /**
     * Generate unique message code
     */
    private function generateMessageCode(): string
    {
        do {
            $code = 'MSG' . strtoupper(Str::random(8));
        } while (Message::where('message_code', $code)->exists());
        
        return $code;
    }
    
    /**
     * Generate unique notification code
     */
    private function generateNotificationCode(): string
    {
        do {
            $code = 'NOT' . strtoupper(Str::random(8));
        } while (Notification::where('notification_code', $code)->exists());
        
        return $code;
    }
}
