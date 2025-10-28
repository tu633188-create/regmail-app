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
        ]);

        // Tạo một số test users
        User::create([
            'username' => 'testuser1',
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'role' => 'premium',
        ]);

        User::create([
            'username' => 'testuser2',
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'role' => 'basic',
        ]);
    }
}
