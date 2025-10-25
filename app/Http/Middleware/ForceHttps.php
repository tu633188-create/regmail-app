<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ForceHttps
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
        // Force HTTPS in production
        if (app()->environment('production') && !$request->secure()) {
            $redirectUrl = 'https://' . $request->getHost() . $request->getRequestUri();

            Log::info('Force HTTPS redirect', [
                'from' => $request->url(),
                'to' => $redirectUrl,
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);

            return redirect()->secure($request->getRequestUri(), 301);
        }

        // Set security headers
        $response = $next($request);

        // Add security headers
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
