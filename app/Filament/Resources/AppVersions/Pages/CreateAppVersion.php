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
        // Filament FileUpload with directory('versions') will automatically save file to storage/app/versions/
        // The $data['file'] will contain the path relative to storage/app/
        if (isset($data['file']) && !empty($data['file'])) {
            $filePath = $data['file'];

            // Filament saves to 'versions/filename', so file_path should be exactly that
            $data['file_path'] = $filePath;

            // Extract file info from the saved file
            $fullPath = storage_path('app/' . $filePath);

            if (file_exists($fullPath)) {
                // Set file_size if not already set
                if (empty($data['file_size']) || $data['file_size'] == 0) {
                    $data['file_size'] = filesize($fullPath);
                }

                // Set checksum if not already set
                if (empty($data['checksum'])) {
                    $data['checksum'] = hash_file('sha256', $fullPath);
                }

                // Set file_name from the actual saved file
                if (empty($data['file_name'])) {
                    $data['file_name'] = basename($filePath);
                }
            } else {
                // File doesn't exist yet, but set file_name from path
                if (empty($data['file_name'])) {
                    $data['file_name'] = basename($filePath);
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
