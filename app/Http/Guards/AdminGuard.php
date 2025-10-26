<?php

namespace App\Http\Guards;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;

class AdminGuard extends SessionGuard
{
    /**
     * Log the user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(Authenticatable $user, $remember = false)
    {
        // Check if user is admin before allowing login
        if ($user->role !== 'admin') {
            Log::warning('Non-admin user attempted to login to admin panel', [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            throw new \Exception('Access denied. Admin privileges required.');
        }

        // Log successful admin login
        Log::info('Admin user logged into dashboard', [
            'user_id' => $user->id,
            'username' => $user->username,
            'ip' => request()->ip()
        ]);

        parent::login($user, $remember);
    }
}
