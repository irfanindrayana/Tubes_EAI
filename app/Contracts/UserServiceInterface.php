<?php

namespace App\Contracts;

interface UserServiceInterface
{
    /**
     * Find user by ID
     */
    public function findById(int $userId): ?array;
    
    /**
     * Find multiple users by IDs
     */
    public function findByIds(array $userIds): array;
    
    /**
     * Get user profile information
     */
    public function getUserProfile(int $userId): ?array;
    
    /**
     * Validate user exists
     */
    public function userExists(int $userId): bool;
    
    /**
     * Get user basic info for cross-service usage
     */
    public function getUserBasicInfo(int $userId): ?array;
}
