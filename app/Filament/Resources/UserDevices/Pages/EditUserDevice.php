<?php

namespace App\Filament\Resources\UserDevices\Pages;

use App\Filament\Resources\UserDevices\UserDeviceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserDevice extends EditRecord
{
    protected static string $resource = UserDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
