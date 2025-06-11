<?php

namespace App\Services\UserManagement;

use App\Contracts\UserServiceInterface;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Cache;

class UserService implements UserServiceInterface
{
    /**
     * Find user by ID
     */
    public function findById(int $userId): ?array
    {
        $cacheKey = "user:{$userId}";
        
        return Cache::remember($cacheKey, 300, function () use ($userId) {
            $user = User::find($userId);
            
            if (!$user) {
                return null;
            }
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'address' => $user->address,
                'birth_date' => $user->birth_date,
                'gender' => $user->gender,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });
    }
    
    /**
     * Find multiple users by IDs
     */
    public function findByIds(array $userIds): array
    {
        $users = User::whereIn('id', $userIds)->get();
        
        return $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
            ];
        })->keyBy('id')->toArray();
    }
    
    /**
     * Get user profile information
     */
    public function getUserProfile(int $userId): ?array
    {
        $user = User::with('profile')->find($userId);
        
        if (!$user) {
            return null;
        }
        
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'address' => $user->address,
                'birth_date' => $user->birth_date,
                'gender' => $user->gender,
            ],
            'profile' => $user->profile ? [
                'bio' => $user->profile->bio,
                'avatar' => $user->profile->avatar,
                'preferences' => $user->profile->preferences,
            ] : null,
        ];
    }
    
    /**
     * Validate user exists
     */
    public function userExists(int $userId): bool
    {
        return User::where('id', $userId)->exists();
    }
    
    /**
     * Get user basic info for cross-service usage
     */
    public function getUserBasicInfo(int $userId): ?array
    {
        $user = User::find($userId);
        
        if (!$user) {
            return null;
        }
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }
}
