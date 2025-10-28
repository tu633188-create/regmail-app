<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDevice;
use App\Models\Registration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RegistrationTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users
        $admin = User::where('username', 'admin')->first();
        $testUser1 = User::where('username', 'testuser1')->first();
        $testUser2 = User::where('username', 'testuser2')->first();

        if (!$admin || !$testUser1 || !$testUser2) {
            $this->command->error('Please run AdminUserSeeder first!');
            return;
        }

        // Create test devices
        $devices = [
            [
                'user_id' => $testUser1->id,
                'device_fingerprint' => 'device_abc123xyz',
                'device_name' => 'iPhone 15 Pro',
                'device_type' => 'mobile',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
                'ip_address' => '192.168.1.100',
                'last_active_at' => now(),
            ],
            [
                'user_id' => $testUser1->id,
                'device_fingerprint' => 'device_def456uvw',
                'device_name' => 'MacBook Pro M3',
                'device_type' => 'desktop',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'ip_address' => '192.168.1.101',
                'last_active_at' => now(),
            ],
            [
                'user_id' => $testUser2->id,
                'device_fingerprint' => 'device_ghi789rst',
                'device_name' => 'Samsung Galaxy S24',
                'device_type' => 'mobile',
                'user_agent' => 'Mozilla/5.0 (Linux; Android 14; SM-S921B)',
                'ip_address' => '192.168.1.102',
                'last_active_at' => now(),
            ],
        ];

        foreach ($devices as $deviceData) {
            UserDevice::create($deviceData);
        }

        // Create test registrations
        $registrations = [
            // Successful registrations
            [
                'user_id' => $testUser1->id,
                'device_fingerprint' => 'device_abc123xyz',
                'email' => 'john.doe@gmail.com',
                'recovery_email' => 'john.recovery@gmail.com',
                'password' => 'MySecurePass123!',
                'status' => 'success',
                'metadata' => [
                    'registration_time_seconds' => 45,
                    'steps_completed' => 5,
                    'captcha_solved' => true,
                ],
                'proxy_ip' => '203.0.113.1',
                'started_at' => now()->subHours(2),
                'completed_at' => now()->subHours(2)->addSeconds(45),
            ],
            [
                'user_id' => $testUser1->id,
                'device_fingerprint' => 'device_def456uvw',
                'email' => 'jane.smith@outlook.com',
                'recovery_email' => 'jane.backup@yahoo.com',
                'password' => 'StrongPassword456!',
                'status' => 'success',
                'metadata' => [
                    'registration_time_seconds' => 78,
                    'steps_completed' => 5,
                    'captcha_solved' => true,
                ],
                'proxy_ip' => '203.0.113.2',
                'started_at' => now()->subHours(1),
                'completed_at' => now()->subHours(1)->addSeconds(78),
            ],
            [
                'user_id' => $testUser2->id,
                'device_fingerprint' => 'device_ghi789rst',
                'email' => 'bob.wilson@yahoo.com',
                'recovery_email' => 'bob.alternative@gmail.com',
                'password' => 'BobPass789!',
                'status' => 'success',
                'metadata' => [
                    'registration_time_seconds' => 92,
                    'steps_completed' => 5,
                    'captcha_solved' => true,
                ],
                'proxy_ip' => '203.0.113.3',
                'started_at' => now()->subMinutes(30),
                'completed_at' => now()->subMinutes(30)->addSeconds(92),
            ],
            // Failed registrations
            [
                'user_id' => $testUser1->id,
                'device_fingerprint' => 'device_abc123xyz',
                'email' => 'failed.email@gmail.com',
                'recovery_email' => 'failed.recovery@gmail.com',
                'password' => 'FailedPass123!',
                'status' => 'failed',
                'error_message' => 'Email already exists in the system',
                'metadata' => [
                    'registration_time_seconds' => 23,
                    'steps_completed' => 2,
                    'captcha_solved' => false,
                ],
                'proxy_ip' => '203.0.113.4',
                'started_at' => now()->subMinutes(15),
                'completed_at' => now()->subMinutes(15)->addSeconds(23),
            ],
            [
                'user_id' => $testUser2->id,
                'device_fingerprint' => 'device_ghi789rst',
                'email' => 'another.failed@outlook.com',
                'recovery_email' => 'another.backup@gmail.com',
                'password' => 'AnotherFailed456!',
                'status' => 'failed',
                'error_message' => 'Captcha verification failed',
                'metadata' => [
                    'registration_time_seconds' => 15,
                    'steps_completed' => 1,
                    'captcha_solved' => false,
                ],
                'proxy_ip' => '203.0.113.5',
                'started_at' => now()->subMinutes(5),
                'completed_at' => now()->subMinutes(5)->addSeconds(15),
            ],
            // Pending registrations
            [
                'user_id' => $testUser1->id,
                'device_fingerprint' => 'device_def456uvw',
                'email' => 'pending.email@gmail.com',
                'recovery_email' => 'pending.recovery@gmail.com',
                'password' => 'PendingPass123!',
                'status' => 'pending',
                'metadata' => [
                    'registration_time_seconds' => 0,
                    'steps_completed' => 0,
                    'captcha_solved' => false,
                ],
                'proxy_ip' => '203.0.113.6',
                'started_at' => now()->subMinutes(2),
                'completed_at' => null,
            ],
            [
                'user_id' => $testUser2->id,
                'device_fingerprint' => 'device_ghi789rst',
                'email' => 'another.pending@yahoo.com',
                'recovery_email' => 'another.backup@gmail.com',
                'password' => 'AnotherPending456!',
                'status' => 'pending',
                'metadata' => [
                    'registration_time_seconds' => 0,
                    'steps_completed' => 0,
                    'captcha_solved' => false,
                ],
                'proxy_ip' => '203.0.113.7',
                'started_at' => now()->subMinutes(1),
                'completed_at' => null,
            ],
            // Cancelled registrations
            [
                'user_id' => $testUser1->id,
                'device_fingerprint' => 'device_abc123xyz',
                'email' => 'cancelled.email@gmail.com',
                'recovery_email' => 'cancelled.recovery@gmail.com',
                'password' => 'CancelledPass123!',
                'status' => 'cancelled',
                'error_message' => 'User cancelled the registration process',
                'metadata' => [
                    'registration_time_seconds' => 12,
                    'steps_completed' => 1,
                    'captcha_solved' => false,
                ],
                'proxy_ip' => '203.0.113.8',
                'started_at' => now()->subMinutes(10),
                'completed_at' => now()->subMinutes(10)->addSeconds(12),
            ],
        ];

        foreach ($registrations as $registrationData) {
            Registration::create($registrationData);
        }

        $this->command->info('Created ' . count($devices) . ' test devices and ' . count($registrations) . ' test registrations');
    }
}
