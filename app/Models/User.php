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
        'device_limit',
        'monthly_quota',
        'used_quota',
        'quota_reset_at',
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
            'quota_reset_at' => 'datetime',
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

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function hasQuota()
    {
        return $this->used_quota < $this->monthly_quota;
    }

    public function canAddDevice()
    {
        return $this->devices()->where('is_active', true)->count() < $this->device_limit;
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
            'device_limit' => $this->device_limit,
        ];
    }
}
