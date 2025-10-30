<?php

namespace App\Filament\Resources\UserDevices\Pages;

use App\Filament\Resources\UserDevices\UserDeviceResource;
use App\Filament\Resources\UserDevices\Widgets\DeviceRegistrationsChart;
use Filament\Resources\Pages\Page;
use BackedEnum;

class ChartsUserDevices extends Page
{
    protected static string $resource = UserDeviceResource::class;

    protected static ?string $title = 'User Device Charts';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected function getHeaderWidgets(): array
    {
        return [
            DeviceRegistrationsChart::class,
        ];
    }
}
