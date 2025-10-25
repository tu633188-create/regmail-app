<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTelegramSettings extends Model
{
    protected $fillable = [
        'user_id',
        'telegram_bot_token',
        'telegram_chat_id',
        'telegram_enabled',
        'registration_notifications',
        'error_notifications',
        'quota_notifications',
        'daily_summary',
        'notification_language',
        'custom_templates',
    ];

    protected $casts = [
        'telegram_enabled' => 'boolean',
        'registration_notifications' => 'boolean',
        'error_notifications' => 'boolean',
        'quota_notifications' => 'boolean',
        'daily_summary' => 'boolean',
        'custom_templates' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConfigured(): bool
    {
        return $this->telegram_enabled &&
            $this->telegram_bot_token &&
            $this->telegram_chat_id;
    }

    public function canReceiveNotifications(): bool
    {
        return $this->isConfigured() && (
            $this->registration_notifications ||
            $this->error_notifications ||
            $this->quota_notifications ||
            $this->daily_summary
        );
    }
}
