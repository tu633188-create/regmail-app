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

            // Ensure file_name is set from file path if not already set
            if (empty($data['file_name']) && !empty($data['file_path'])) {
                $data['file_name'] = basename($data['file_path']);
            }

            unset($data['file']);
        }

        // Ensure file_name is not null
        if (empty($data['file_name'])) {
            $data['file_name'] = 'app-version-' . ($data['version'] ?? 'unknown') . '.exe';
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
