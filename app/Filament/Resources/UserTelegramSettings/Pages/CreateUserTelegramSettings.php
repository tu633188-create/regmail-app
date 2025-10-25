<?php

namespace App\Filament\Resources\UserTelegramSettings\Pages;

use App\Filament\Resources\UserTelegramSettings\UserTelegramSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserTelegramSettings extends CreateRecord
{
    protected static string $resource = UserTelegramSettingsResource::class;
}
