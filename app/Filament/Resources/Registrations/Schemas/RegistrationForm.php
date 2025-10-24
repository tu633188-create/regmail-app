<?php

namespace App\Filament\Resources\Registrations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'success' => 'Success',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                Textarea::make('error_message')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('proxy_ip')
                    ->default(null),
                DateTimePicker::make('started_at')
                    ->required(),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
