<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            Log::warning('Unauthorized dashboard access attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->path()
            ]);

            return redirect()->route('login')->with('error', 'Please login to access the dashboard.');
        }

        $user = Auth::user();

        // Check if user is admin
        if ($user->role !== 'admin') {
            Log::warning('Non-admin user attempted dashboard access', [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->path()
            ]);

            return redirect()->back()->with('error', 'Access denied. Admin privileges required.');
        }

        // Log successful admin access
        Log::info('Admin dashboard access', [
            'user_id' => $user->id,
            'username' => $user->username,
            'ip' => $request->ip(),
            'path' => $request->path()
        ]);

        return $next($request);
    }
}
