<?php

namespace App\Filament\Resources\UserTelegramSettings\Pages;

use App\Filament\Resources\UserTelegramSettings\UserTelegramSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserTelegramSettings extends EditRecord
{
    protected static string $resource = UserTelegramSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
