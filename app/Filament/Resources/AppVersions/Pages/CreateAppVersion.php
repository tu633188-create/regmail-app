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
        // Move file path from 'file' to 'file_path'
        if (isset($data['file']) && !empty($data['file'])) {
            $tempPath = $data['file'];

            // If file is in livewire-tmp, move it to versions directory
            if (str_contains($tempPath, 'livewire-tmp')) {
                $tempFilePath = storage_path('app/livewire-tmp/' . basename($tempPath));
                $fileName = basename($tempPath);

                // Remove Livewire temporary file prefix/suffix if present
                $fileName = preg_replace('/^.*-meta/', '', $fileName);
                $fileName = preg_replace('/==-\.exe$/', '.exe', $fileName);

                // Create versions directory if not exists
                $versionsDir = storage_path('app/versions');
                if (!is_dir($versionsDir)) {
                    mkdir($versionsDir, 0755, true);
                }

                // Move file to permanent location
                $permanentPath = 'versions/' . $fileName;
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
                } else {
                    // Fallback: just use the path as is
                    $data['file_path'] = $tempPath;
                }
            } else {
                // Already in permanent location
                $data['file_path'] = $tempPath;
            }

            unset($data['file']);
        }

        // CRITICAL: Ensure file_name is always set (cannot be null in database)
        if (empty($data['file_name']) || is_null($data['file_name'])) {
            if (!empty($data['file_path'])) {
                // Extract clean filename from path
                $fileName = basename($data['file_path']);
                // Remove Livewire temp prefixes if any
                $fileName = preg_replace('/^.*-meta/', '', $fileName);
                $fileName = preg_replace('/==-\.exe$/', '.exe', $fileName);
                $data['file_name'] = $fileName;
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
