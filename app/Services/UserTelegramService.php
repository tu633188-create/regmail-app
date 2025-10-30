<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserTelegramSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserTelegramService
{
    protected $user;
    protected $settings;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->settings = $user->getTelegramSettings();
    }

    public function isEnabled(): bool
    {
        return $this->settings->isConfigured();
    }

    public function sendMessage(string $message, string $parseMode = 'HTML'): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->settings->telegram_bot_token}/sendMessage", [
                'chat_id' => $this->settings->telegram_chat_id,
                'text' => $message,
                'parse_mode' => $parseMode
            ]);

            if ($response->successful()) {
                Log::info("Telegram message sent to user {$this->user->id}");
                return true;
            } else {
                Log::error("Telegram API error for user {$this->user->id}: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Telegram send failed for user {$this->user->id}: " . $e->getMessage());
            return false;
        }
    }

    public function sendRegistrationNotification(string $email, string $status, int $registrationTime = null, string $deviceName = null): bool
    {
        if (!$this->settings->registration_notifications) {
            return false;
        }

        $message = $this->getRegistrationMessage($email, $status, $registrationTime, $deviceName);
        return $this->sendMessage($message);
    }

    public function sendErrorNotification(string $error, string $context = ''): bool
    {
        if (!$this->settings->error_notifications) {
            return false;
        }

        $message = $this->getErrorMessage($error, $context);
        return $this->sendMessage($message);
    }

    public function sendDailySummary(array $stats): bool
    {
        if (!$this->settings->daily_summary) {
            return false;
        }

        $message = "📊 <b>Daily Summary</b>\n\n";
        $message .= "📧 Registrations: <b>{$stats['registrations']}</b>\n";
        $message .= "✅ Success: <b>{$stats['success']}</b>\n";
        $message .= "⏰ Date: " . now()->format('Y-m-d');

        return $this->sendMessage($message);
    }

    public function sendPeriodicSummary(array $stats, int $hours = 4): bool
    {
        if (!$this->settings->daily_summary) {
            return false;
        }

        $message = "📊 <b>Periodic Summary ({$hours}h)</b>\n\n";

        // Device statistics
        if (!empty($stats['device_stats'])) {
            $message .= "📱 <b>Device Statistics:</b>\n";
            foreach ($stats['device_stats'] as $device) {
                $deviceName = $device['device_name'] ?: 'Unnamed Device';
                $message .= "• <code>{$deviceName}</code>: <b>{$device['registrations']}</b> emails\n";
            }
            $message .= "\n";
        }

        // Overall statistics
        $message .= "📧 Total Registrations: <b>{$stats['registrations']}</b>\n";
        if (isset($stats['devices_with_activity'], $stats['devices_total'])) {
            $message .= "🖥️ Devices Active: <b>{$stats['devices_with_activity']}</b>/<b>{$stats['devices_total']}</b>\n";
        }
        $message .= "⏰ Period: " . now()->subHours($hours)->format('H:i') . " - " . now()->format('H:i');

        return $this->sendMessage($message);
    }

    private function getRegistrationMessage(string $email, string $status, int $registrationTime = null, string $deviceName = null): string
    {
        $templates = $this->settings->custom_templates ?? [];
        $template = $templates['registration_success'] ?? null;

        if ($template) {
            return str_replace(['{email}', '{device}'], [$email, $deviceName ?? 'Unknown'], $template);
        }

        $message = "📧 <b>Email Registration Update</b>\n\n";
        $message .= "📮 Email: <code>{$email}</code>\n";
        $message .= "📊 Status: <b>{$status}</b>\n";

        if ($deviceName) {
            $message .= "📱 Device: <code>{$deviceName}</code>\n";
        }

        if ($registrationTime) {
            $hours = floor($registrationTime / 3600);
            $minutes = floor(($registrationTime % 3600) / 60);
            $seconds = $registrationTime % 60;
            $message .= "⏱️ Time: <code>{$hours}h {$minutes}m {$seconds}s</code>\n";
        }

        $message .= "⏰ Completed: " . now()->format(format: 'Y-m-d H:i:s');

        return $message;
    }

    private function getErrorMessage(string $error, string $context = ''): string
    {
        $templates = $this->settings->custom_templates ?? [];
        $template = $templates['error'] ?? null;

        if ($template) {
            return str_replace(['{error}', '{context}'], [$error, $context], $template);
        }

        $message = "🚨 <b>Registration Error</b>\n\n";
        $message .= "❌ Error: <code>{$error}</code>\n";
        if ($context) {
            $message .= "📍 Context: <code>{$context}</code>\n";
        }
        $message .= "⏰ Time: " . now()->format('Y-m-d H:i:s');

        return $message;
    }
}
