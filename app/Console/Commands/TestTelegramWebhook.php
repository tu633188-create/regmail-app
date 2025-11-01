<?php

namespace App\Console\Commands;

use App\Models\UserTelegramSettings;
use Illuminate\Console\Command;

class TestTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-webhook {--command=/help : Command to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate test curl command for Telegram webhook with real data from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $command = $this->option('command');
        
        $this->info('📋 Available Telegram Settings:');
        $this->newLine();

        $settings = UserTelegramSettings::where('telegram_enabled', true)
            ->with('user')
            ->get();

        if ($settings->isEmpty()) {
            $this->error('❌ No enabled Telegram settings found in database.');
            $this->info('💡 Create settings via Filament admin panel first.');
            return 1;
        }

        $this->table(
            ['ID', 'User', 'Chat ID', 'Bot Token (last 10)'],
            $settings->map(function ($s) {
                return [
                    $s->id,
                    $s->user->username ?? 'N/A',
                    $s->telegram_chat_id,
                    substr($s->telegram_bot_token, -10) ?? 'N/A',
                ];
            })->toArray()
        );

        $this->newLine();
        $this->info('🧪 Test Commands:');
        $this->newLine();

        foreach ($settings as $setting) {
            $chatId = $setting->telegram_chat_id;
            $botToken = $setting->telegram_bot_token;
            $domain = config('app.url') ?: 'https://trananhtu.vn';
            
            // Extract token from full token (format: 123456789:ABCdefGHI...)
            $tokenParts = explode(':', $botToken);
            $tokenForUrl = count($tokenParts) > 1 ? urlencode($botToken) : urlencode($botToken);

            $updateId = time();
            $date = time();

            $payload = [
                'update_id' => $updateId,
                'message' => [
                    'message_id' => 1,
                    'from' => [
                        'id' => (int) $chatId,
                        'is_bot' => false,
                        'first_name' => $setting->user->username ?? 'Test',
                        'username' => $setting->user->username ?? 'testuser',
                    ],
                    'chat' => [
                        'id' => (int) $chatId,
                        'first_name' => $setting->user->username ?? 'Test',
                        'username' => $setting->user->username ?? 'testuser',
                        'type' => 'private',
                    ],
                    'date' => $date,
                    'text' => $command,
                ],
            ];

            $this->line("👤 User: <fg=cyan>{$setting->user->username}</fg=cyan>");
            $this->line("   Chat ID: <fg=yellow>{$chatId}</fg=yellow>");
            $this->newLine();
            
            // Option 1: With token in URL
            $this->line("<fg=green>Option 1: With token in URL</fg=green>");
            $curlCmd1 = sprintf(
                "curl -X POST %s/api/telegram/webhook/%s \\\n  -H 'Content-Type: application/json' \\\n  -d '%s'",
                $domain,
                $tokenForUrl,
                json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            );
            $this->line($curlCmd1);
            $this->newLine();

            // Option 2: Without token (finds by chat_id only)
            $this->line("<fg=green>Option 2: Without token (finds by chat_id only)</fg=green>");
            $curlCmd2 = sprintf(
                "curl -X POST %s/api/telegram/webhook \\\n  -H 'Content-Type: application/json' \\\n  -d '%s'",
                $domain,
                json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            );
            $this->line($curlCmd2);
            $this->newLine();

            // Pretty JSON for manual testing
            $this->line("<fg=green>JSON Payload:</fg=green>");
            $this->line(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            $this->newLine();
            $this->line('---');
            $this->newLine();
        }

        return 0;
    }
}
