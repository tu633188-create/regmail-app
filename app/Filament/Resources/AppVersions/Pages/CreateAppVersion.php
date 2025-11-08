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
            $data['file_path'] = $data['file'];
            unset($data['file']);
        }

        // CRITICAL: Ensure file_name is always set (cannot be null in database)
        if (empty($data['file_name']) || is_null($data['file_name'])) {
            if (!empty($data['file_path'])) {
                // Use filename from file path
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
