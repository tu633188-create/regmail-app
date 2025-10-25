<?php

namespace App\Filament\Resources\UserTelegramSettings\Pages;

use App\Filament\Resources\UserTelegramSettings\UserTelegramSettingsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUserTelegramSettings extends ManageRecords
{
    protected static string $resource = UserTelegramSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
