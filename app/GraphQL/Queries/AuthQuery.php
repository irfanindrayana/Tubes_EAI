<?php

namespace App\GraphQL\Queries;

use Illuminate\Support\Facades\Auth;

class AuthQuery
{
    /**
     * Get the currently authenticated user.
     */
    public function me(): ?\App\Models\User
    {
        return Auth::user();
    }
}
