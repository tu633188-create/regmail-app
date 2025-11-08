<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppVersionController extends Controller
{
    /**
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
