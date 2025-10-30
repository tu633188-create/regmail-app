<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserTelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPeriodicTelegramNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:send-periodic {--hours=2 : Number of hours to check for stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send periodic Telegram notifications to users with daily_summary enabled';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');

        $this->info("Sending periodic notifications for last {$hours} hours...");
        $this->info("Current time: " . now()->format('Y-m-d H:i:s'));

        $users = User::whereHas('telegramSettings', function ($query) {
            $query->where('daily_summary', true)
                ->where('telegram_enabled', true);
        })->get();

        $sentCount = 0;

        foreach ($users as $user) {
            try {
                $stats = $this->getStatsForPeriod($user, $hours);

                $telegramService = new UserTelegramService($user);
                $success = $telegramService->sendPeriodicSummary($stats, $hours);

                if ($success) {
                    $sentCount++;
                    $this->line("✅ Sent to user: {$user->username} (registrations: {$stats['registrations']})");
                } else {
                    $this->warn("❌ Failed to send to user: {$user->username}");
                }
            } catch (\Exception $e) {
                $this->error("❌ Error for user {$user->username}: " . $e->getMessage());
                Log::error("Periodic notification error for user {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("📊 Sent {$sentCount} notifications out of {$users->count()} users");
    }

    public function getStatsForPeriod(User $user, int $hours): array
    {
        $startTime = now()->subHours($hours);

        $registrations = $user->registrations()
            ->where('created_at', '>=', $startTime)
            ->get();

        $total = $registrations->count();
        $success = $registrations->where('status', 'success')->count();
        $failed = $total - $success;
        $successRate = $total > 0 ? round(($success / $total) * 100, 1) : 0;

        // Get device statistics
        $deviceStats = [];
        $deviceRegistrations = $registrations->groupBy('device_fingerprint');

        foreach ($deviceRegistrations as $deviceFingerprint => $deviceRegs) {
            $device = $user->devices()
                ->where('device_fingerprint', $deviceFingerprint)
                ->first();

            $deviceStats[] = [
                'device_name' => $device ? $device->device_name : 'Unknown Device',
                'registrations' => $deviceRegs->count(),
            ];
        }

        // Sort by registration count (descending)
        usort($deviceStats, function ($a, $b) {
            return $b['registrations'] - $a['registrations'];
        });

        // Device coverage stats
        $devicesWithActivity = count($deviceStats);
        $devicesTotal = $user->devices()->count();

        return [
            'registrations' => $total,
            'success' => $success,
            'failed' => $failed,
            'success_rate' => $successRate,
            'device_stats' => $deviceStats,
            'devices_with_activity' => $devicesWithActivity,
            'devices_total' => $devicesTotal,
        ];
    }
}
