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
        User::create([
            'username' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@regmail.com',
            'password' => Hash::make('admin123'),
            'status' => 'active',
            'role' => 'admin',
            'device_limit' => 10,
            'monthly_quota' => 1000,
            'used_quota' => 0,
            'quota_reset_at' => now()->addMonth(),
        ]);

        // Tạo một số test users
        User::create([
            'username' => 'testuser1',
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'role' => 'premium',
            'device_limit' => 3,
            'monthly_quota' => 500,
            'used_quota' => 50,
            'quota_reset_at' => now()->addMonth(),
        ]);

        User::create([
            'username' => 'testuser2',
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'role' => 'basic',
            'device_limit' => 1,
            'monthly_quota' => 100,
            'used_quota' => 25,
            'quota_reset_at' => now()->addMonth(),
        ]);
    }
}
