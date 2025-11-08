<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class AppVersionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/app/version/check",
     *     summary="Check for app version updates",
     *     description="Check if a newer version of the app is available by comparing version codes",
     *     tags={"App Version Management"},
     *     @OA\Parameter(
     *         name="current_version_code",
     *         in="query",
     *         required=true,
     *         description="Current version code of the app (integer, e.g., 100 for version 1.0.0)",
     *         @OA\Schema(type="integer", example=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Version check result",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="needs_update", type="boolean", example=true, description="Whether an update is available"),
     *                 @OA\Property(property="force_update", type="boolean", example=false, description="Whether update is forced (users must update)"),
     *                 @OA\Property(property="latest_version", type="string", example="1.2.0", description="Latest version string"),
     *                 @OA\Property(property="latest_version_code", type="integer", example=120, description="Latest version code"),
     *                 @OA\Property(property="current_version_code", type="integer", example=100, description="Current version code from request"),
     *                 @OA\Property(property="download_url", type="string", example="/api/app/version/download/1", description="URL to download the update (null if no update needed)"),
     *                 @OA\Property(property="release_notes", type="string", example="Bug fixes and performance improvements", description="Release notes for the update"),
     *                 @OA\Property(property="file_size", type="integer", example=5242880, description="File size in bytes"),
     *                 @OA\Property(property="checksum", type="string", example="a1b2c3d4e5f6...", description="SHA256 checksum for file verification")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid version code",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid version code")
     *         )
     *     )
     * )
     * 
     * Check for updates
     * GET /api/app/version/check?current_version_code=100
     */
    public function check(Request $request)
    {
        $currentVersionCode = $request->query('current_version_code', 0);
        
        if (!is_numeric($currentVersionCode)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid version code',
            ], 400);
        }

        $result = AppVersion::needsUpdate((int) $currentVersionCode);
        
        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/app/version/download/{id}",
     *     summary="Download app version file",
     *     description="Download the executable file for a specific app version. Only active versions are available for download.",
     *     tags={"App Version Management"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Version ID to download",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File download",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Version not found or not available",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Version is not available for download")
     *         )
     *     )
     * )
     * 
     * Download version file
     * GET /api/app/version/download/{id}
     */
    public function download($id)
    {
        $version = AppVersion::findOrFail($id);
        
        if (!$version->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Version is not available for download',
            ], 404);
        }

        $filePath = storage_path('app/' . $version->file_path);
        
        if (!file_exists($filePath)) {
            Log::error("Version file not found: {$filePath}");
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        return response()->download($filePath, $version->file_name, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $version->file_name . '"',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/app/version/info",
     *     summary="Get latest version information",
     *     description="Get information about the latest active app version without checking for updates",
     *     tags={"App Version Management"},
     *     @OA\Response(
     *         response=200,
     *         description="Latest version information",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="version", type="string", example="1.2.0", description="Version string"),
     *                 @OA\Property(property="version_code", type="integer", example=120, description="Version code for comparison"),
     *                 @OA\Property(property="file_size", type="integer", example=5242880, description="File size in bytes"),
     *                 @OA\Property(property="checksum", type="string", example="a1b2c3d4e5f6...", description="SHA256 checksum"),
     *                 @OA\Property(property="release_notes", type="string", example="Bug fixes and performance improvements", description="Release notes"),
     *                 @OA\Property(property="is_force_update", type="boolean", example=false, description="Whether update is forced"),
     *                 @OA\Property(property="released_at", type="string", format="date-time", example="2025-11-09T00:00:00Z", description="Release date")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No version available",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No version available")
     *         )
     *     )
     * )
     * 
     * Get version info
     * GET /api/app/version/info
     */
    public function info()
    {
        $latest = AppVersion::getLatest();
        
        if (!$latest) {
            return response()->json([
                'success' => false,
                'message' => 'No version available',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'version' => $latest->version,
                'version_code' => $latest->version_code,
                'file_size' => $latest->file_size,
                'checksum' => $latest->checksum,
                'release_notes' => $latest->release_notes,
                'is_force_update' => $latest->is_force_update,
                'released_at' => $latest->created_at->toIso8601String(),
            ],
        ]);
    }
}
