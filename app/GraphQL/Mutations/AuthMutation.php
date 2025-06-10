<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AuthMutation
{
    /**
     * Login user and return user instance.
     */
    public function login($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): User
    {
        $credentials = [
            'email' => $args['email'],
            'password' => $args['password']
        ];

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        
        // Start session for web
        // request()->session()->regenerate();

        return $user;
    }

    /**
     * Register new user.
     */
    public function register($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): User
    {
        $user = User::create([
            'name' => $args['name'],
            'email' => $args['email'],
            'password' => Hash::make($args['password']),
            'role' => $args['role'],
            'phone' => $args['phone'] ?? null,
            'address' => $args['address'] ?? null,
            'birth_date' => $args['birth_date'] ?? null,
            'gender' => $args['gender'] ?? null,
        ]);

        // Auto login after registration
        Auth::login($user);
        request()->session()->regenerate();

        return $user;
    }

    /**
     * Logout user.
     */
    public function logout($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): string
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return 'Successfully logged out';
    }
}
