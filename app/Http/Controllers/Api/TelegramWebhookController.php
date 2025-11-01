<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserTelegramSettings;
use App\Services\TelegramCommandHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected TelegramCommandHandler $commandHandler;

    public function __construct(TelegramCommandHandler $commandHandler)
    {
        $this->commandHandler = $commandHandler;
    }

    /**
     * Handle Telegram webhook requests
     */
    public function handle(Request $request, ?string $token = null)
    {
        try {
            $update = $request->all();

            // Log incoming webhook for debugging
            Log::debug('Telegram webhook received', [
                'update_id' => $update['update_id'] ?? null,
                'has_message' => isset($update['message']),
                'token' => $token ? 'provided' : 'none',
            ]);

            // Handle message updates
            if (isset($update['message'])) {
                return $this->handleMessage($update['message'], $token);
            }

            // Handle callback queries (inline button clicks)
            if (isset($update['callback_query'])) {
                return $this->handleCallbackQuery($update['callback_query'], $token);
            }

            // Unknown update type - acknowledge it
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['ok' => false, 'error' => 'Internal error'], 500);
        }
    }

    /**
     * Handle incoming message
     */
    protected function handleMessage(array $message, ?string $token = null): JsonResponse
    {
        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? null;
        $from = $message['from'] ?? [];

        if (!$chatId || !$text) {
            return response()->json(['ok' => true]);
        }

        // Find user by chat_id and bot token
        $settings = $this->findUserSettingsByChatId($chatId, $token);

        if (!$settings) {
            Log::warning('Telegram webhook: User not found', [
                'chat_id' => $chatId,
                'token_provided' => $token !== null,
            ]);
            return response()->json(['ok' => true]);
        }

        // Process command
        $this->commandHandler->handle($text, $settings);

        return response()->json(['ok' => true]);
    }

    /**
     * Handle callback query (inline button clicks)
     */
    protected function handleCallbackQuery(array $callbackQuery, ?string $token = null): JsonResponse
    {
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $data = $callbackQuery['data'] ?? null;

        if (!$chatId || !$data) {
            return response()->json(['ok' => true]);
        }

        $settings = $this->findUserSettingsByChatId($chatId, $token);

        if (!$settings) {
            return response()->json(['ok' => true]);
        }

        // Process callback as command
        $this->commandHandler->handle($data, $settings);

        return response()->json(['ok' => true]);
    }

    /**
     * Find user settings by chat_id and optionally bot token
     */
    protected function findUserSettingsByChatId(string $chatId, ?string $token = null): ?UserTelegramSettings
    {
        $query = UserTelegramSettings::where('telegram_chat_id', $chatId)
            ->where('telegram_enabled', true);

        if ($token) {
            $query->where('telegram_bot_token', $token);
        }

        return $query->first();
    }
}
