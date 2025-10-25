<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\UserTelegramService;

class Registration extends Model
{
    protected $fillable = [
        'user_id',
        'device_fingerprint',
        'email',
        'recovery_email',
        'password',
        'status',
        'error_message',
        'metadata',
        'proxy_ip',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class, 'device_fingerprint', 'device_fingerprint');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsSuccess(): void
    {
        $this->update([
            'status' => 'success',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    protected static function booted()
    {
        static::updated(function (Registration $registration) {
            // Send Telegram notification when status changes
            if ($registration->wasChanged('status')) {
                $user = $registration->user;
                $telegram = new UserTelegramService($user);

                if ($registration->isSuccess()) {
                    $registrationTime = $registration->metadata['registration_time_seconds'] ?? null;

                    // Get device name
                    $deviceName = 'Unknown Device';
                    $device = \App\Models\UserDevice::where('device_fingerprint', $registration->device_fingerprint)
                        ->where('user_id', $registration->user_id)
                        ->first();
                    if ($device) {
                        $deviceName = $device->device_name ?? 'Unknown Device';
                    }

                    $telegram->sendRegistrationNotification(
                        $registration->email,
                        'success',
                        $registrationTime,
                        $deviceName
                    );
                } elseif ($registration->isFailed()) {
                    $telegram->sendErrorNotification(
                        $registration->error_message ?? 'Unknown error',
                        'Email registration failed'
                    );
                }
            }
        });
    }
}
