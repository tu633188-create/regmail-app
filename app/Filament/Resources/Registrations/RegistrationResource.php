<?php

namespace App\Filament\Resources\Registrations;

use App\Filament\Resources\Registrations\Pages\ListRegistrations;
use App\Models\Registration;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Exports\RegistrationExporter;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Email Registrations';

    protected static ?string $modelLabel = 'Email Registration';

    protected static ?string $pluralModelLabel = 'Email Registrations';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'username')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('device_fingerprint')
                    ->label('Device Fingerprint')
                    ->maxLength(255)
                    ->placeholder('device_abc123xyz')
                    ->searchable(),

                Forms\Components\TextInput::make('device_name')
                    ->label('Device Name')
                    ->maxLength(255)
                    ->placeholder('My Laptop')
                    ->searchable(),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('recovery_email')
                    ->label('Recovery Email')
                    ->email()
                    ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('proxy_ip')
                    ->label('Proxy IP')
                    ->maxLength(255),

                Forms\Components\DateTimePicker::make('started_at')
                    ->label('Started At'),

                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Completed At'),

                Forms\Components\Textarea::make('error_message')
                    ->label('Error Message')
                    ->columnSpanFull(),

                Forms\Components\KeyValue::make('metadata')
                    ->label('Metadata')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.username')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('device_fingerprint')
                    ->label('Device Fingerprint')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Device fingerprint copied')
                    ->copyMessageDuration(1500)
                    ->placeholder('No device info'),

                Tables\Columns\TextColumn::make('device_name')
                    ->label('Device Name')
                    ->getStateUsing(function (Registration $record): string {
                        // Get device name from user_devices table
                        $device = \App\Models\UserDevice::where('device_fingerprint', $record->device_fingerprint)
                            ->where('user_id', $record->user_id)
                            ->first();
                        return $device ? $device->device_name : 'Unknown Device';
                    })
                    ->searchable()
                    ->placeholder('Unknown Device'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('recovery_email')
                    ->label('Recovery Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Recovery email copied')
                    ->copyMessageDuration(1500)
                    ->placeholder('No recovery email'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('registration_time')
                    ->label('Registration Time')
                    ->getStateUsing(function (Registration $record): string {
                        $metadata = $record->metadata ?? [];
                        $seconds = $metadata['registration_time_seconds'] ?? 0;

                        if ($seconds == 0) return 'N/A';

                        $hours = floor($seconds / 3600);
                        $minutes = floor(($seconds % 3600) / 60);
                        $remainingSeconds = $seconds % 60;

                        if ($hours > 0) {
                            return sprintf('%dh %dm %ds', $hours, $minutes, $remainingSeconds);
                        } elseif ($minutes > 0) {
                            return sprintf('%dm %ds', $minutes, $remainingSeconds);
                        } else {
                            return sprintf('%ds', $remainingSeconds);
                        }
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('proxy_ip')
                    ->label('Proxy IP')
                    ->searchable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'username')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('device_fingerprint')
                    ->form([
                        Forms\Components\TextInput::make('device_fingerprint')
                            ->label('Device Fingerprint')
                            ->placeholder('Enter device fingerprint'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['device_fingerprint'],
                            fn(Builder $query, $fingerprint): Builder => $query->where('device_fingerprint', 'like', "%{$fingerprint}%")
                        );
                    }),

                Tables\Filters\Filter::make('device_name')
                    ->form([
                        Forms\Components\TextInput::make('device_name')
                            ->label('Device Name')
                            ->placeholder('Enter device name'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['device_name'],
                            function (Builder $query, $deviceName) {
                                $query->whereHas('user.devices', function (Builder $deviceQuery) use ($deviceName) {
                                    $deviceQuery->where('device_name', 'like', "%{$deviceName}%");
                                });
                            }
                        );
                    }),

                Filter::make('started_at')
                    ->form([
                        DatePicker::make('started_from')
                            ->label('Started From'),
                        DatePicker::make('started_until')
                            ->label('Started Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['started_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('started_at', '>=', $date),
                            )
                            ->when(
                                $data['started_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('started_at', '<=', $date),
                            );
                    }),

                Filter::make('registration_time')
                    ->form([
                        Forms\Components\TextInput::make('min_seconds')
                            ->label('Min Registration Time (seconds)')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_seconds')
                            ->label('Max Registration Time (seconds)')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['min_seconds'] || $data['max_seconds'],
                            function (Builder $query) use ($data) {
                                $query->where(function (Builder $query) use ($data) {
                                    if ($data['min_seconds']) {
                                        $query->whereRaw("JSON_EXTRACT(metadata, '$.registration_time_seconds') >= ?", [$data['min_seconds']]);
                                    }
                                    if ($data['max_seconds']) {
                                        $query->whereRaw("JSON_EXTRACT(metadata, '$.registration_time_seconds') <= ?", [$data['max_seconds']]);
                                    }
                                });
                            }
                        );
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export to Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->exporter(RegistrationExporter::class),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('export_selected')
                    ->label('Export Selected')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Collection $records) {
                        // Export logic here
                        return redirect()->back();
                    }),
                BulkAction::make('delete_selected')
                    ->label('Delete Selected')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each->delete();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegistrations::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'success')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
