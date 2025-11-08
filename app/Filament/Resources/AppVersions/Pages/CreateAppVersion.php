<?php

namespace App\Filament\Resources\AppVersions\Pages;

use App\Filament\Resources\AppVersions\AppVersionResource;
use App\Models\AppVersion;
use Filament\Resources\Pages\CreateRecord;

class CreateAppVersion extends CreateRecord
{
    protected static string $resource = AppVersionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // CRITICAL: file_path is required in database, must be set
        if (isset($data['file']) && !empty($data['file'])) {
            $tempPath = $data['file'];

            // If file is in livewire-tmp, move it to versions directory
            if (str_contains($tempPath, 'livewire-tmp')) {
                $tempFilePath = storage_path('app/livewire-tmp/' . basename($tempPath));

                // Generate clean filename
                $version = $data['version'] ?? 'unknown';
                $versionCode = $data['version_code'] ?? time();
                $cleanFileName = 'app-v' . str_replace('.', '_', $version) . '-' . $versionCode . '.exe';

                // Create versions directory if not exists
                $versionsDir = storage_path('app/versions');
                if (!is_dir($versionsDir)) {
                    mkdir($versionsDir, 0755, true);
                }

                // Move file to permanent location
                $permanentPath = 'versions/' . $cleanFileName;
                $permanentFilePath = storage_path('app/' . $permanentPath);

                if (file_exists($tempFilePath)) {
                    // Get file info before moving
                    if (empty($data['file_size']) || $data['file_size'] == 0) {
                        $data['file_size'] = filesize($tempFilePath);
                    }
                    if (empty($data['checksum'])) {
                        $data['checksum'] = hash_file('sha256', $tempFilePath);
                    }

                    // Move file
                    rename($tempFilePath, $permanentFilePath);
                    $data['file_path'] = $permanentPath;
                    $data['file_name'] = $cleanFileName;
                } else {
                    // File doesn't exist, but we still need file_path
                    $data['file_path'] = $permanentPath;
                    $data['file_name'] = $cleanFileName;
                }
            } else {
                // Already in permanent location
                $data['file_path'] = $tempPath;
                if (empty($data['file_name'])) {
                    $data['file_name'] = basename($tempPath);
                }
            }

            unset($data['file']);
        } else {
            // No file uploaded - this should not happen as file is required
            // But set a placeholder to prevent SQL error
            $version = $data['version'] ?? 'unknown';
            $versionCode = $data['version_code'] ?? time();
            $data['file_path'] = 'versions/app-v' . str_replace('.', '_', $version) . '-' . $versionCode . '.exe';
        }

        // CRITICAL: Ensure file_name is always set (cannot be null in database)
        if (empty($data['file_name']) || is_null($data['file_name'])) {
            if (!empty($data['file_path'])) {
                $data['file_name'] = basename($data['file_path']);
            } else {
                // Fallback: generate filename from version
                $version = $data['version'] ?? 'unknown';
                $data['file_name'] = 'app-version-' . $version . '.exe';
            }
        }

        // Ensure file_size has a default value if not set
        if (empty($data['file_size']) || is_null($data['file_size'])) {
            $data['file_size'] = 0;
        }

        // Final check: file_path must never be empty
        if (empty($data['file_path'])) {
            $version = $data['version'] ?? 'unknown';
            $versionCode = $data['version_code'] ?? time();
            $data['file_path'] = 'versions/app-v' . str_replace('.', '_', $version) . '-' . $versionCode . '.exe';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // If this version is set as active, deactivate all others
        if ($this->record->is_active) {
            AppVersion::where('id', '!=', $this->record->id)
                ->update(['is_active' => false]);
        }
    }
}
