<?php

namespace App\GraphQL\Mutations;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class MessageMutation
{
    /**
     * Create a new message.
     */
    public function create($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Message
    {
        return DB::transaction(function () use ($args) {
            $sender = Auth::user();

            // Create message
            $message = Message::create([
                'sender_id' => $sender->id,
                'subject' => $args['subject'],
                'body' => $args['body'],
                'status' => 'sent',
            ]);

            // Create message recipients
            foreach ($args['recipient_ids'] as $recipientId) {
                MessageRecipient::create([
                    'message_id' => $message->id,
                    'recipient_id' => $recipientId,
                    'is_read' => false,
                ]);

                // Create notification for recipient
                Notification::create([
                    'user_id' => $recipientId,
                    'title' => 'New Message',
                    'message' => "You have received a new message from {$sender->name}",
                    'type' => 'message',
                    'is_read' => false,
                ]);
            }

            return $message->load(['sender', 'recipients.recipient']);
        });
    }

    /**
     * Mark message as read.
     */
    public function markAsRead($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): MessageRecipient
    {
        $user = Auth::user();
        
        $messageRecipient = MessageRecipient::where('message_id', $args['message_id'])
            ->where('recipient_id', $user->id)
            ->firstOrFail();

        $messageRecipient->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $messageRecipient->load(['message.sender', 'recipient']);
    }
}
