<?php

namespace App\Filament\Resources\UserDevices\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserDeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('device_id')
                    ->required(),
                TextInput::make('device_name')
                    ->default(null),
                TextInput::make('ip_address')
                    ->required(),
                Textarea::make('user_agent')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('device_fingerprint')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('last_seen_at'),
            ]);
    }
}
