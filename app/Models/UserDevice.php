<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_name',
        'device_fingerprint',
        'device_type',
        'os',
        'browser',
        'ip_address',
        'user_agent',
        'is_active',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(JwtToken::class, 'device_fingerprint', 'device_fingerprint');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'device_fingerprint', 'device_fingerprint');
    }

    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
        // Also blacklist all tokens for this device
        $this->tokens()->update(['is_blacklisted' => true]);
    }
}
