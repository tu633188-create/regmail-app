<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiVersionCheck
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
        // Skip version check if not required
        if (!config('app.api_version_required', true)) {
            return $next($request);
        }

        // Get required version from config
        $requiredVersion = config('app.api_version', '1.0.0');

        // Get version from request header
        $clientVersion = $request->header('X-API-Version');

        // Log version check attempt
        Log::info('API Version Check', [
            'required_version' => $requiredVersion,
            'client_version' => $clientVersion,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'endpoint' => $request->path()
        ]);

        // Check if version header is present
        if (!$clientVersion) {
            return response()->json([
                'success' => false,
                'message' => 'API version header is required',
                'error' => 'MISSING_VERSION_HEADER',
                'required_version' => $requiredVersion,
                'documentation' => 'Please include X-API-Version header in your request'
            ], 400);
        }

        // Check if version matches
        if ($clientVersion !== $requiredVersion) {
            return response()->json([
                'success' => false,
                'message' => 'API version mismatch',
                'error' => 'VERSION_MISMATCH',
                'required_version' => $requiredVersion,
                'client_version' => $clientVersion,
                'documentation' => 'Please use the correct API version'
            ], 400);
        }

        // Add version info to response headers
        $response = $next($request);
        $response->headers->set('X-API-Version', $requiredVersion);
        $response->headers->set('X-API-Version-Required', config('app.api_version_required', true) ? 'true' : 'false');

        return $response;
    }
}
