<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run AdminUserSeeder
        $this->call(AdminUserSeeder::class);
        
        // Run RegistrationTestSeeder for test data
        $this->call(RegistrationTestSeeder::class);
    }
}
