<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserTelegramSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TelegramCommandHandler
{
    protected UserTelegramService $telegramService;

    public function __construct(UserTelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle incoming command
     */
    public function handle(string $text, UserTelegramSettings $settings): void
    {
        $text = trim($text);
        
        if (!str_starts_with($text, '/')) {
            return;
        }

        // Parse command and arguments
        $parts = preg_split('/\s+/', $text, 3);
        $command = strtolower($parts[0]);
        $args = array_slice($parts, 1);

        $user = $settings->user;

        try {
            switch ($command) {
                case '/start':
                    $this->handleStart($user, $settings);
                    break;

                case '/help':
                    $this->handleHelp($user, $settings);
                    break;

                case '/devices':
                    $period = $args[0] ?? null;
                    $filter = $args[1] ?? null;
                    $this->handleDevices($user, $settings, $period, $filter);
                    break;

                default:
                    $this->sendMessage($settings, "❌ Unknown command. Use /help to see available commands.");
            }
        } catch (\Exception $e) {
            Log::error('Telegram command error', [
                'command' => $command,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            $this->sendMessage($settings, "❌ Error: " . $e->getMessage());
        }
    }

    /**
     * Handle /start command
     */
    protected function handleStart(User $user, UserTelegramSettings $settings): void
    {
        if (!$settings->isConfigured()) {
            $message = "👋 Chào mừng đến với Email Registration Bot!\n\n";
            $message .= "❌ Telegram chưa được cấu hình hoàn chỉnh.\n";
            $message .= "Vui lòng vào admin panel để setup bot token và chat ID.";
            $this->sendMessage($settings, $message);
            return;
        }

        $message = "👋 Chào mừng đến với Email Registration Bot!\n\n";
        $message .= "📋 <b>Command có sẵn:</b>\n\n";
        $message .= "<b>/devices [period] [filter]</b>\n";
        $message .= "Xem danh sách thiết bị và thống kê email registration\n\n";
        $message .= "<b>Parameters:</b>\n";
        $message .= "• [period] - today, week, month, &lt;n&gt;h, &lt;n&gt;m, &lt;n&gt;d\n";
        $message .= "• [filter] - active (có email), inactive (không có email)\n\n";
        $message .= "<b>Ví dụ:</b>\n";
        $message .= "/devices              → Tất cả devices\n";
        $message .= "/devices today        → Devices hôm nay\n";
        $message .= "/devices 2h           → Devices trong 2 giờ gần đây\n";
        $message .= "/devices 30m          → Devices trong 30 phút gần đây\n";
        $message .= "/devices week active  → Chỉ active devices trong tuần\n";
        $message .= "/devices 1h inactive → Chỉ inactive devices trong 1 giờ\n\n";
        $message .= "📖 Gõ /help để xem tài liệu đầy đủ";

        $this->sendMessage($settings, $message);
    }

    /**
     * Handle /help command
     */
    protected function handleHelp(User $user, UserTelegramSettings $settings): void
    {
        $message = "📖 <b>Tài liệu hướng dẫn /devices</b>\n\n";
        $message .= "<b>Command:</b> /devices [period] [filter]\n\n";
        $message .= "<b>Mục đích:</b>\n";
        $message .= "Danh sách thiết bị và thống kê email registration theo thời gian\n\n";
        $message .= "<b>Parameters:</b>\n\n";
        $message .= "<b>[period] - Thời gian:</b>\n";
        $message .= "• today - Hôm nay (00:00 - hiện tại)\n";
        $message .= "• week - Tuần này (Monday 00:00 - hiện tại)\n";
        $message .= "• month - Tháng này (ngày 1 00:00 - hiện tại)\n";
        $message .= "• &lt;n&gt;h - N giờ gần đây (vd: 1h, 2h, 24h)\n";
        $message .= "• &lt;n&gt;m - N phút gần đây (vd: 30m, 60m, 120m)\n";
        $message .= "• &lt;n&gt;d - N ngày gần đây (vd: 7d, 30d)\n";
        $message .= "• (để trống) - Tất cả thời gian\n\n";
        $message .= "<b>[filter] - Lọc:</b>\n";
        $message .= "• active - Chỉ thiết bị có hoạt động (có email)\n";
        $message .= "• inactive - Chỉ thiết bị không có hoạt động (không có email)\n";
        $message .= "• (để trống) - Hiển thị cả active và inactive\n\n";
        $message .= "<b>Ví dụ sử dụng:</b>\n";
        $message .= "/devices\n";
        $message .= "/devices today\n";
        $message .= "/devices 2h\n";
        $message .= "/devices 30m\n";
        $message .= "/devices week active\n";
        $message .= "/devices month inactive\n";
        $message .= "/devices 1h active\n";
        $message .= "/devices 30m inactive\n\n";
        $message .= "Gõ /start để xem hướng dẫn nhanh";

        $this->sendMessage($settings, $message);
    }

    /**
     * Handle /devices command
     */
    protected function handleDevices(User $user, UserTelegramSettings $settings, ?string $period, ?string $filter): void
    {
        // Parse period to get start time
        $startTime = $this->parsePeriod($period);
        $periodLabel = $this->getPeriodLabel($period);

        // Get registrations in period
        $query = $user->registrations();
        if ($startTime) {
            $query->where('created_at', '>=', $startTime);
        }
        $registrations = $query->get();

        // Group by device fingerprint
        $fingerprintToCount = [];
        foreach ($registrations->groupBy('device_fingerprint') as $fingerprint => $regs) {
            $fingerprintToCount[$fingerprint] = $regs->count();
        }

        // Get all user devices
        $userDevices = $user->devices()->get(['device_fingerprint', 'device_name']);
        
        if ($userDevices->isEmpty()) {
            $this->sendMessage($settings, "📱 Không tìm thấy thiết bị nào trong hệ thống.");
            return;
        }

        // Build device stats
        $deviceStats = [];
        foreach ($userDevices as $device) {
            $count = $fingerprintToCount[$device->device_fingerprint] ?? 0;
            $deviceStats[] = [
                'device_name' => $device->device_name ?: 'Unknown Device',
                'registrations' => $count,
            ];
        }

        // Sort by registration count (descending)
        usort($deviceStats, function ($a, $b) {
            return $b['registrations'] - $a['registrations'];
        });

        // Filter by active/inactive
        if ($filter === 'active') {
            $deviceStats = array_filter($deviceStats, fn($d) => $d['registrations'] > 0);
        } elseif ($filter === 'inactive') {
            $deviceStats = array_filter($deviceStats, fn($d) => $d['registrations'] === 0);
        }

        $deviceStats = array_values($deviceStats);

        if (empty($deviceStats)) {
            $filterText = $filter ? " ({$filter})" : "";
            $this->sendMessage($settings, "📱 Không tìm thấy thiết bị{$filterText} trong khoảng thời gian {$periodLabel}.");
            return;
        }

        // Format message
        $message = "📱 <b>Device Statistics [{$periodLabel}]</b>\n\n";

        $activeDevices = array_filter($deviceStats, fn($d) => $d['registrations'] > 0);
        $inactiveDevices = array_filter($deviceStats, fn($d) => $d['registrations'] === 0);

        if ($filter === 'active') {
            $message .= "<b>Active Devices (" . count($activeDevices) . "):</b>\n";
            foreach ($activeDevices as $device) {
                $message .= "• <code>{$device['device_name']}</code>: <b>{$device['registrations']}</b> emails\n";
            }
            $message .= "\nTotal: " . count($activeDevices) . " devices with activity";
        } elseif ($filter === 'inactive') {
            $message .= "<b>Inactive Devices (" . count($inactiveDevices) . "):</b>\n";
            foreach ($inactiveDevices as $device) {
                $message .= "• <code>{$device['device_name']}</code>: <b>{$device['registrations']}</b> emails\n";
            }
            $message .= "\nTotal: " . count($inactiveDevices) . " devices without activity";
        } else {
            if (!empty($activeDevices)) {
                $message .= "<b>Active Devices (" . count($activeDevices) . "):</b>\n";
                foreach ($activeDevices as $device) {
                    $message .= "• <code>{$device['device_name']}</code>: <b>{$device['registrations']}</b> emails\n";
                }
                $message .= "\n";
            }

            if (!empty($inactiveDevices)) {
                $message .= "<b>Inactive Devices (" . count($inactiveDevices) . "):</b>\n";
                foreach ($inactiveDevices as $device) {
                    $message .= "• <code>{$device['device_name']}</code>: <b>{$device['registrations']}</b> emails\n";
                }
                $message .= "\n";
            }

            $message .= "📊 <b>Summary:</b>\n";
            $message .= "Total Devices: " . count($deviceStats) . "\n";
            $message .= "Active: " . count($activeDevices) . " (" . round(count($activeDevices) / count($deviceStats) * 100, 1) . "%)\n";
            $message .= "Inactive: " . count($inactiveDevices) . " (" . round(count($inactiveDevices) / count($deviceStats) * 100, 1) . "%)";
        }

        $this->sendMessage($settings, $message);
    }

    /**
     * Parse period string to Carbon datetime
     */
    protected function parsePeriod(?string $period): ?Carbon
    {
        if (!$period) {
            return null;
        }

        $period = strtolower(trim($period));

        switch ($period) {
            case 'today':
                return Carbon::today();
            
            case 'week':
                return Carbon::now()->startOfWeek();
            
            case 'month':
                return Carbon::now()->startOfMonth();
            
            default:
                // Try to parse hours: 1h, 2h, 24h
                if (preg_match('/^(\d+)h$/i', $period, $matches)) {
                    return Carbon::now()->subHours((int)$matches[1]);
                }
                
                // Try to parse minutes: 30m, 60m
                if (preg_match('/^(\d+)m$/i', $period, $matches)) {
                    return Carbon::now()->subMinutes((int)$matches[1]);
                }
                
                // Try to parse days: 7d, 30d
                if (preg_match('/^(\d+)d$/i', $period, $matches)) {
                    return Carbon::now()->subDays((int)$matches[1]);
                }
                
                throw new \InvalidArgumentException("Invalid period format: {$period}");
        }
    }

    /**
     * Get human-readable period label
     */
    protected function getPeriodLabel(?string $period): string
    {
        if (!$period) {
            return 'All Time';
        }

        $period = strtolower(trim($period));

        switch ($period) {
            case 'today':
                return 'Today';
            case 'week':
                return 'This Week';
            case 'month':
                return 'This Month';
            default:
                return ucfirst($period);
        }
    }

    /**
     * Send message via Telegram service
     */
    protected function sendMessage(UserTelegramSettings $settings, string $message): void
    {
        $user = $settings->user;
        $telegramService = new UserTelegramService($user, $settings);
        $telegramService->sendMessage($message);
    }
}
