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
        // FileUpload đã tự động lưu vào file_path, chỉ cần đảm bảo file_name và file_size được set
        if (!empty($data['file_path'])) {
            $fullPath = storage_path('app/' . $data['file_path']);

            // Ensure file_name is set
            if (empty($data['file_name'])) {
                $data['file_name'] = basename($data['file_path']);
            }

            // Ensure file_size is set
            if ((empty($data['file_size']) || $data['file_size'] == 0) && file_exists($fullPath)) {
                $data['file_size'] = filesize($fullPath);
            }

            // Ensure checksum is set
            if (empty($data['checksum']) && file_exists($fullPath)) {
                $data['checksum'] = hash_file('sha256', $fullPath);
            }
        }

        // Fallback values
        if (empty($data['file_size'])) {
            $data['file_size'] = 0;
        }

        if (empty($data['file_name'])) {
            $data['file_name'] = basename($data['file_path'] ?? 'app.exe');
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
