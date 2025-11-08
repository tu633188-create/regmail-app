<?php

namespace App\Filament\Resources\AppVersions\Pages;

use App\Filament\Resources\AppVersions\AppVersionResource;
use App\Models\AppVersion;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAppVersion extends EditRecord
{
    protected static string $resource = AppVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // FileUpload đã tự động update file_path nếu file mới được upload
        if (!empty($data['file_path']) && $data['file_path'] !== $this->record->file_path) {
            // Delete old file if exists
            if ($this->record->file_path && file_exists(storage_path('app/' . $this->record->file_path))) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($this->record->file_path);
            }

            // Update file info from new file
            $fullPath = storage_path('app/' . $data['file_path']);
            if (file_exists($fullPath)) {
                $data['file_size'] = filesize($fullPath);
                $data['file_name'] = basename($data['file_path']);
                $data['checksum'] = hash_file('sha256', $fullPath);
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // If this version is set as active, deactivate all others
        if ($this->record->is_active) {
            AppVersion::where('id', '!=', $this->record->id)
                ->update(['is_active' => false]);
        }
    }
}
