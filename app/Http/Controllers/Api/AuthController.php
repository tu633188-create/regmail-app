<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Annotations as OA;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * @OA\Info(
 *     title="RegMail API",
 *     version="1.0.0",
 *     description="API for RegMail - Email Registration Service"
 * )
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Development Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="User Login",
     *     description="Authenticate user and return JWT token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="admin"),
     *             @OA\Property(property="password", type="string", example="admin123"),
     *             @OA\Property(property="device_name", type="string", example="Chrome Browser")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="role", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="device_limit", type="integer"),
     *                     @OA\Property(property="monthly_quota", type="integer"),
     *                     @OA\Property(property="used_quota", type="integer")
     *                 ),
     *                 @OA\Property(property="device", type="object",
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="name", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Account suspended",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Account is suspended or banned")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('username', 'password');

        // Check user exists và status
        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is suspended or banned'
            ], 403);
        }

        // Device fingerprinting
        $deviceId = $this->generateDeviceId($request);
        $deviceFingerprint = $this->generateDeviceFingerprint($request);

        // Check device limit
        if (!$user->canAddDevice()) {
            // Force logout oldest device
            $oldestDevice = $user->devices()
                ->where('is_active', true)
                ->orderBy('last_seen_at', 'asc')
                ->first();

            if ($oldestDevice) {
                $oldestDevice->deactivate();
            }
        }

        try {
            // Create or update device
            $device = UserDevice::updateOrCreate(
                ['device_id' => $deviceId],
                [
                    'user_id' => $user->id,
                    'device_name' => $request->input('device_name', 'Unknown Device'),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_fingerprint' => $deviceFingerprint,
                    'is_active' => true,
                    'last_seen_at' => now(),
                ]
            );

            // Generate JWT token
            $token = JWTAuth::fromUser($user);
            $payload = JWTAuth::getPayload($token);
            $tokenId = $payload->get('jti');

            // Store token in database
            JwtToken::create([
                'user_id' => $user->id,
                'token_id' => $tokenId,
                'token_hash' => hash('sha256', $token),
                'expires_at' => $payload->get('exp'),
                'device_id' => $deviceId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status,
                        'device_limit' => $user->device_limit,
                        'monthly_quota' => $user->monthly_quota,
                        'used_quota' => $user->used_quota,
                    ],
                    'device' => [
                        'id' => $deviceId,
                        'name' => $device->device_name,
                    ]
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/auth/validate",
     *     summary="Validate Token",
     *     description="Validate JWT token and return user info",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token is valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token is valid"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="role", type="string"),
     *                     @OA\Property(property="status", type="string")
     *                 ),
     *                 @OA\Property(property="expires_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalid or expired",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token is invalid or expired")
     *         )
     *     )
     * )
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $token = JWTAuth::getToken();
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check if user is still active
            if (!$user->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is suspended or banned'
                ], 403);
            }

            // Check if token is blacklisted
            $payload = JWTAuth::getPayload($token);
            $tokenId = $payload->get('jti');

            $jwtToken = JwtToken::where('token_id', $tokenId)
                ->where('user_id', $user->id)
                ->first();

            if (!$jwtToken || $jwtToken->is_blacklisted || $jwtToken->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token is invalid or expired'
                ], 401);
            }

            // Update device last seen
            if ($jwtToken->device_id) {
                UserDevice::where('device_id', $jwtToken->device_id)
                    ->update(['last_seen_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Token is valid',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'role' => $user->role,
                        'status' => $user->status,
                    ],
                    'expires_at' => $jwtToken->expires_at->toISOString(),
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid'
            ], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Refresh Token",
     *     description="Refresh JWT token and get new token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Could not refresh token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Could not refresh token")
     *         )
     *     )
     * )
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh();
            $user = JWTAuth::setToken($token)->toUser();

            $payload = JWTAuth::getPayload($token);
            $tokenId = $payload->get('jti');

            // Update token in database
            JwtToken::where('user_id', $user->id)
                ->where('is_blacklisted', false)
                ->update([
                    'token_id' => $tokenId,
                    'token_hash' => hash('sha256', $token),
                    'expires_at' => $payload->get('exp'),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not refresh token'
            ], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="User Logout",
     *     description="Logout user and blacklist JWT token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Could not logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Could not logout")
     *         )
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        try {
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token);
            $tokenId = $payload->get('jti');

            // Blacklist token
            JwtToken::where('token_id', $tokenId)
                ->update(['is_blacklisted' => true]);

            JWTAuth::invalidate($token);

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not logout'
            ], 500);
        }
    }

    /**
     * Get user devices
     */
    public function devices(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $devices = $user->devices()
                ->where('is_active', true)
                ->orderBy('last_seen_at', 'desc')
                ->get(['device_id', 'device_name', 'ip_address', 'last_seen_at']);

            return response()->json([
                'success' => true,
                'data' => $devices
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
    }

    /**
     * Force logout specific device
     */
    public function logoutDevice(Request $request, string $deviceId): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $device = $user->devices()
                ->where('device_id', $deviceId)
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            $device->deactivate();

            return response()->json([
                'success' => true,
                'message' => 'Device logged out successfully'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
    }

    /**
     * Generate unique device ID
     */
    private function generateDeviceId(Request $request): string
    {
        $fingerprint = $request->ip() . $request->userAgent();
        return 'device_' . hash('sha256', $fingerprint);
    }

    /**
     * Generate device fingerprint
     */
    private function generateDeviceFingerprint(Request $request): string
    {
        $data = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accept_language' => $request->header('Accept-Language'),
            'accept_encoding' => $request->header('Accept-Encoding'),
        ];

        return hash('sha256', json_encode($data));
    }
}
