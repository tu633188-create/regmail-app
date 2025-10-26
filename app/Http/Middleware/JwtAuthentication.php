<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\JwtToken;
use App\Models\UserDevice;

class JwtAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Get token from Authorization header
            $authHeader = $request->header('Authorization');
            
            if (!$authHeader) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization header not provided'
                ], 401);
            }

            // Check if token starts with "Bearer "
            if (!str_starts_with($authHeader, 'Bearer ')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid authorization header format. Expected: Bearer <token>'
                ], 401);
            }

            // Extract token from "Bearer <token>"
            $token = substr($authHeader, 7); // Remove "Bearer " prefix
            
            if (empty($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not provided'
                ], 401);
            }

            // Set token for JWT Auth
            JWTAuth::setToken($token);

            // Authenticate user from token
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                $this->logSecurityEvent($request, 'user_not_found');
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check if user is still active
            if (!$user->isActive()) {
                $this->logSecurityEvent($request, 'user_inactive', $user->id);
                return response()->json([
                    'success' => false,
                    'message' => 'Account is suspended or banned'
                ], 403);
            }

            // Get token payload and validate token in database
            $payload = JWTAuth::getPayload($token);
            $tokenId = $payload->get('jti');

            // Single query to check token validity
            $jwtToken = JwtToken::where('token_id', $tokenId)
                ->where('user_id', $user->id)
                ->where('is_blacklisted', false)
                ->where('expires_at', '>', now())
                ->first();

            if (!$jwtToken) {
                $this->logSecurityEvent($request, 'token_invalid', $user->id);
                return response()->json([
                    'success' => false,
                    'message' => 'Token is invalid or expired'
                ], 401);
            }

            // Update last seen for device tracking
            if ($jwtToken->device_id) {
                UserDevice::where('device_id', $jwtToken->device_id)
                    ->update(['last_seen_at' => now()]);
            }

            // Add user to request
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            return $next($request);
        } catch (JWTException $e) {
            $this->logSecurityEvent($request, 'jwt_exception', null, $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid'
            ], 401);
        }
    }

    /**
     * Log security events for monitoring
     */
    private function logSecurityEvent(Request $request, string $event, ?int $userId = null, ?string $details = null): void
    {
        Log::warning('JWT Security Event', [
            'event' => $event,
            'user_id' => $userId,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => $details,
            'timestamp' => now()
        ]);
    }
}
