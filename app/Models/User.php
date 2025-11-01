<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'status',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function jwtTokens()
    {
        return $this->hasMany(JwtToken::class);
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function telegramSettings()
    {
        return $this->hasMany(UserTelegramSettings::class);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function canAddDevice()
    {
        // No device limit - always allow adding devices
        return true;
    }

    public function getDeviceLimit()
    {
        // Return device limit based on role
        switch ($this->role) {
            case 'admin':
                return 1000;
            case 'premium':
                return 100;
            default:
                return 10; // Free users
        }
    }

    public function getTelegramSettings(?string $botToken = null): ?UserTelegramSettings
    {
        if ($botToken) {
            return $this->telegramSettings()->where('telegram_bot_token', $botToken)->first();
        }

        // Return first configured settings or return null (don't auto-create)
        // Settings should be created explicitly through Filament admin or API
        return $this->telegramSettings()->where('telegram_enabled', true)->first();
    }

    public function getTelegramSettingsByChatId(string $chatId): ?UserTelegramSettings
    {
        return $this->telegramSettings()
            ->where('telegram_chat_id', $chatId)
            ->where('telegram_enabled', true)
            ->first();
    }

    public function hasTelegramConfigured(?string $botToken = null): bool
    {
        $settings = $this->getTelegramSettings($botToken);
        return $settings && $settings->isConfigured();
    }

    public function canReceiveTelegramNotifications(?string $botToken = null): bool
    {
        $settings = $this->getTelegramSettings($botToken);
        return $settings && $settings->canReceiveNotifications();
    }

    // JWT Subject methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'status' => $this->status,
        ];
    }
}
