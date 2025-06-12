<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Contracts\InboxServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Inbox Management API Controller
 * Handles all inbox-related operations for microservice communication
 */
class InboxApiController extends Controller
{
    protected InboxServiceInterface $inboxService;

    public function __construct(InboxServiceInterface $inboxService)
    {
        $this->inboxService = $inboxService;
    }

    /**
     * Get messages for a user
     */
    public function getUserMessages(int $userId): JsonResponse
    {
        try {
            $messages = $this->inboxService->getUserMessages($userId);
            return response()->json(['data' => $messages]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch messages'], 500);
        }
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_id' => 'required|integer',
            'message' => 'required|string|max:1000',
            'type' => 'string|in:notification,alert,info'
        ]);

        try {
            $message = $this->inboxService->sendMessage(
                $request->input('recipient_id'),
                $request->input('message'),
                $request->input('type', 'notification')
            );
            
            return response()->json(['data' => $message], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead(int $messageId): JsonResponse
    {
        try {
            $this->inboxService->markAsRead($messageId);
            return response()->json(['message' => 'Message marked as read']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to mark message as read'], 500);
        }
    }

    /**
     * Get unread message count for user
     */
    public function getUnreadCount(int $userId): JsonResponse
    {
        try {
            $count = $this->inboxService->getUnreadCount($userId);
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get unread count'], 500);
        }
    }

    /**
     * Delete a message
     */
    public function deleteMessage(int $messageId): JsonResponse
    {
        try {
            $this->inboxService->deleteMessage($messageId);
            return response()->json(['message' => 'Message deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete message'], 500);
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendBroadcast(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer',
            'message' => 'required|string|max:1000',
            'type' => 'string|in:notification,alert,info'
        ]);

        try {
            $results = $this->inboxService->sendBroadcast(
                $request->input('user_ids'),
                $request->input('message'),
                $request->input('type', 'notification')
            );
            
            return response()->json(['data' => $results], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send broadcast'], 500);
        }
    }
}
