<?php

namespace App\GraphQL\Mutations;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class NotificationMutation
{
    /**
     * Mark notification as read.
     */
    public function markAsRead($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Notification
    {
        $user = Auth::user();
        
        $notification = Notification::where('id', $args['id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $notification->load(['user']);
    }
}
