<?php

namespace App\Contracts;

interface InboxServiceInterface
{
    /**
     * Send message
     */
    public function sendMessage(array $messageData): array;
    
    /**
     * Get user messages
     */
    public function getUserMessages(int $userId, int $perPage = 15): array;
    
    /**
     * Mark message as read
     */
    public function markAsRead(int $messageId, int $userId): bool;
    
    /**
     * Send notification
     */
    public function sendNotification(array $notificationData): array;
    
    /**
     * Get user notifications
     */
    public function getUserNotifications(int $userId): array;
}
