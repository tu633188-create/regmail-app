<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailSubmissionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('validate', [AuthController::class, 'validate']);
    Route::get('devices', [AuthController::class, 'devices']);
    Route::delete('devices/{deviceId}', [AuthController::class, 'logoutDevice']);
});

// Protected routes (require JWT token)
Route::middleware('jwt.auth')->group(function () {
    // User routes
    Route::prefix('users')->group(function () {
        Route::get('profile', function (Request $request) {
            return response()->json([
                'success' => true,
                'data' => $request->user()
            ]);
        });

        Route::get('quota', function (Request $request) {
            $user = $request->user();
            return response()->json([
                'success' => true,
                'data' => [
                    'monthly_quota' => $user->monthly_quota,
                    'used_quota' => $user->used_quota,
                    'remaining_quota' => $user->monthly_quota - $user->used_quota,
                    'quota_reset_at' => $user->quota_reset_at,
                ]
            ]);
        });
    });

    // Registration routes
    Route::prefix('register')->group(function () {
        Route::post('start', function (Request $request) {
            // TODO: Implement registration logic
            return response()->json([
                'success' => true,
                'message' => 'Registration started',
                'data' => [
                    'id' => 'reg_' . uniqid(),
                    'status' => 'pending'
                ]
            ]);
        });

        Route::get('status/{id}', function (Request $request, $id) {
            // TODO: Implement status check
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $id,
                    'status' => 'pending',
                    'progress' => 50
                ]
            ]);
        });

        Route::get('history', function (Request $request) {
            $user = $request->user();
            $registrations = $user->registrations()
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get(['id', 'email', 'status', 'started_at', 'completed_at']);

            return response()->json([
                'success' => true,
                'data' => $registrations
            ]);
        });

        Route::get('stats', function (Request $request) {
            $user = $request->user();
            $total = $user->registrations()->count();
            $success = $user->registrations()->where('status', 'success')->count();
            $failed = $user->registrations()->where('status', 'failed')->count();
            $pending = $user->registrations()->where('status', 'pending')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'success' => $success,
                    'failed' => $failed,
                    'pending' => $pending,
                    'success_rate' => $total > 0 ? round(($success / $total) * 100, 2) : 0
                ]
            ]);
        });
    });

    // Email submission routes
    Route::prefix('email')->group(function () {
        Route::post('submit', [EmailSubmissionController::class, 'submit']);
    });
});
