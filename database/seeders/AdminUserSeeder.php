<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin Bus Trans Bandung',
            'email' => 'admin@transbandung.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '08123456789',
            'address' => 'Bandung City Hall',
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);

        // Create test user
        User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('user123'),
            'role' => 'konsumen',
            'phone' => '08987654321',
            'address' => 'Jl. Test No. 123, Bandung',
            'birth_date' => '1995-01-01',
            'gender' => 'female',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Admin and test users created successfully!');
    }
}
