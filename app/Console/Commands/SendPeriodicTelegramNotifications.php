<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserTelegramSettings;
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
    protected $signature = 'telegram:send-periodic {--hours=1 : Number of hours to check for stats}';

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

        // Get all enabled Telegram settings with daily_summary enabled
        // This handles the case where a user can have multiple bots
        $settings = UserTelegramSettings::where('daily_summary', true)
            ->where('telegram_enabled', true)
            ->with('user')
            ->get();

        $sentCount = 0;
        $totalSettings = $settings->count();

        foreach ($settings as $setting) {
            try {
                $user = $setting->user;

                if (!$user) {
                    $this->warn("⚠️  Skipping settings ID {$setting->id}: User not found");
                    continue;
                }

                $stats = $this->getStatsForPeriod($user, $hours);

                // Pass specific settings to ensure we send to the correct bot
                $telegramService = new UserTelegramService($user, $setting);
                $success = $telegramService->sendPeriodicSummary($stats, $hours);

                if ($success) {
                    $sentCount++;
                    $botInfo = substr($setting->telegram_bot_token, -10) ?? 'N/A';
                    $this->line("✅ Sent to user: {$user->username} (bot: ...{$botInfo}, registrations: {$stats['registrations']})");
                } else {
                    $this->warn("❌ Failed to send to user: {$user->username} (settings ID: {$setting->id})");
                }
            } catch (\Exception $e) {
                $userName = $setting->user->username ?? 'Unknown';
                $this->error("❌ Error for user {$userName} (settings ID: {$setting->id}): " . $e->getMessage());
                Log::error("Periodic notification error for settings {$setting->id}: " . $e->getMessage());
            }
        }

        $this->info("📊 Sent {$sentCount} notifications out of {$totalSettings} bot configurations");
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

        // Build map of fingerprint => registrations count for period
        $fingerprintToCount = [];
        foreach ($deviceRegistrations as $deviceFingerprint => $deviceRegs) {
            $fingerprintToCount[$deviceFingerprint] = $deviceRegs->count();
        }

        // Include ALL user's devices, even with 0 in this period
        $userDevices = $user->devices()->get(['device_fingerprint', 'device_name']);
        foreach ($userDevices as $dev) {
            $count = $fingerprintToCount[$dev->device_fingerprint] ?? 0;
            $deviceStats[] = [
                'device_name' => $dev->device_name ?: 'Unknown Device',
                'registrations' => $count,
            ];
            // Mark as consumed
            unset($fingerprintToCount[$dev->device_fingerprint]);
        }

        // Any fingerprints that appeared in registrations but are not in user's devices table
        // will be ignored here as requirement focuses on "devices with 0 also shown" for user's devices.

        // Sort by registration count (descending)
        usort($deviceStats, function ($a, $b) {
            return $b['registrations'] - $a['registrations'];
        });

        // Device coverage stats
        $devicesWithActivity = collect($deviceStats)->where('registrations', '>', 0)->count();
        $devicesTotal = $userDevices->count();

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
