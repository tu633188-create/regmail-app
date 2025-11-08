<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'version',
        'version_code',
        'file_path',
        'file_name',
        'file_size',
        'release_notes',
        'is_active',
        'is_force_update',
        'checksum',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_force_update' => 'boolean',
        'version_code' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Get the latest active version
     */
    public static function getLatest(): ?self
    {
        return self::where('is_active', true)
            ->orderBy('version_code', 'desc')
            ->first();
    }

    /**
     * Check if version needs update
     */
    public static function needsUpdate(int $currentVersionCode): array
    {
        $latest = self::getLatest();
        
        if (!$latest) {
            return [
                'needs_update' => false,
                'message' => 'No version available',
            ];
        }

        $needsUpdate = $latest->version_code > $currentVersionCode;
        
        return [
            'needs_update' => $needsUpdate,
            'force_update' => $needsUpdate && $latest->is_force_update,
            'latest_version' => $latest->version,
            'latest_version_code' => $latest->version_code,
            'current_version_code' => $currentVersionCode,
            'download_url' => $needsUpdate ? route('api.app.version.download', $latest->id) : null,
            'release_notes' => $latest->release_notes,
            'file_size' => $latest->file_size,
            'checksum' => $latest->checksum,
        ];
    }
}
