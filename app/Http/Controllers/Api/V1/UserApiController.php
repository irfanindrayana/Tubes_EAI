<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Contracts\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * User Management API Controller
 * Handles all user-related operations for microservice communication
 */
class UserApiController extends Controller
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get user by ID
     */
    public function show(int $userId): JsonResponse
    {
        $user = $this->userService->findById($userId);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        return response()->json(['data' => $user]);
    }

    /**
     * Get multiple users by IDs
     */
    public function getMultiple(Request $request): JsonResponse
    {
        $userIds = $request->input('user_ids', []);
        
        if (empty($userIds)) {
            return response()->json(['error' => 'User IDs required'], 400);
        }
        
        $users = $this->userService->findByIds($userIds);
        
        return response()->json(['data' => $users]);
    }

    /**
     * Check if user exists
     */
    public function exists(int $userId): JsonResponse
    {
        $exists = $this->userService->userExists($userId);
        
        return response()->json(['exists' => $exists]);
    }

    /**
     * Get user basic info
     */
    public function basicInfo(int $userId): JsonResponse
    {
        $user = $this->userService->getUserBasicInfo($userId);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        return response()->json(['data' => $user]);
    }
}
