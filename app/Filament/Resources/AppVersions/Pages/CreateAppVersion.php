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
        // If using Google Drive link, file_path is optional
        if (isset($data['file']) && !empty($data['file'])) {
            $data['file_path'] = $data['file'];
        } else {
            // If using download_url, set file_path to null
            $data['file_path'] = null;
        }
        
        // Remove 'file' key as it's not a database column
        unset($data['file']);
        
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

