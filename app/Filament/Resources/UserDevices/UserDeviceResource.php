<?php

namespace App\Filament\Resources\UserDevices;

use App\Filament\Resources\UserDevices\Pages\CreateUserDevice;
use App\Filament\Resources\UserDevices\Pages\EditUserDevice;
use App\Filament\Resources\UserDevices\Pages\ListUserDevices;
use App\Models\UserDevice;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Navigation\NavigationItem;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Collection;

class UserDeviceResource extends Resource
{
    protected static ?string $model = UserDevice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static ?string $navigationLabel = 'User Devices';

    protected static ?string $modelLabel = 'User Device';

    protected static ?string $pluralModelLabel = 'User Devices';

    protected static ?int $navigationSort = 3;

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

                Forms\Components\TextInput::make('device_name')
                    ->label('Device Name')
                    ->maxLength(255)
                    ->placeholder('e.g., My Laptop, Work Computer'),

                Forms\Components\TextInput::make('device_fingerprint')
                    ->label('Device Fingerprint')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\Select::make('device_type')
                    ->label('Device Type')
                    ->options([
                        'desktop' => 'Desktop',
                        'laptop' => 'Laptop',
                        'mobile' => 'Mobile',
                        'tablet' => 'Tablet',
                        'unknown' => 'Unknown',
                    ])
                    ->default('unknown'),

                Forms\Components\TextInput::make('os')
                    ->label('Operating System')
                    ->maxLength(255)
                    ->placeholder('e.g., Windows 11, macOS 14, Ubuntu 22.04'),

                Forms\Components\TextInput::make('browser')
                    ->label('Browser')
                    ->maxLength(255)
                    ->placeholder('e.g., Chrome 120, Firefox 121, Safari 17'),

                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->maxLength(255),

                Forms\Components\Textarea::make('user_agent')
                    ->label('User Agent')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
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

                Tables\Columns\TextColumn::make('device_name')
                    ->label('Device Name')
                    ->searchable()
                    ->placeholder('Unnamed Device')
                    ->copyable()
                    ->copyMessage('Device name copied')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('last_email_submission')
                    ->label('Last Email Submission')
                    ->getStateUsing(function (UserDevice $record): string {
                        $lastRegistration = \App\Models\Registration::where('device_fingerprint', $record->device_fingerprint)
                            ->where('user_id', $record->user_id)
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if (!$lastRegistration) {
                            return 'Never';
                        }

                        return $lastRegistration->created_at->format('Y-m-d H:i:s');
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->addSelect([
                            'last_email_submission' => \App\Models\Registration::selectRaw('MAX(created_at)')
                                ->whereColumn('device_fingerprint', 'user_devices.device_fingerprint')
                                ->whereColumn('user_id', 'user_devices.user_id')
                        ])
                            ->orderBy('last_email_submission', $direction);
                    })
                    ->placeholder('Never')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('total_emails_2h')
                    ->label('Emails (2h)')
                    ->getStateUsing(function (UserDevice $record): int {
                        return \App\Models\Registration::where('device_fingerprint', $record->device_fingerprint)
                            ->where('user_id', $record->user_id)
                            ->where('created_at', '>=', now()->subHours(2))
                            ->count();
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->addSelect([
                            'total_emails_2h' => \App\Models\Registration::selectRaw('COUNT(*)')
                                ->whereColumn('device_fingerprint', 'user_devices.device_fingerprint')
                                ->whereColumn('user_id', 'user_devices.user_id')
                                ->where('created_at', '>=', now()->subHours(2))
                        ])
                            ->orderBy('total_emails_2h', $direction);
                    })
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 5 => 'success',
                        $state >= 3 => 'warning',
                        $state > 0 => 'info',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('total_emails_24h')
                    ->label('Emails (24h)')
                    ->getStateUsing(function (UserDevice $record): int {
                        return \App\Models\Registration::where('device_fingerprint', $record->device_fingerprint)
                            ->where('user_id', $record->user_id)
                            ->where('created_at', '>=', now()->subDay())
                            ->count();
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->addSelect([
                            'total_emails_24h' => \App\Models\Registration::selectRaw('COUNT(*)')
                                ->whereColumn('device_fingerprint', 'user_devices.device_fingerprint')
                                ->whereColumn('user_id', 'user_devices.user_id')
                                ->where('created_at', '>=', now()->subDay())
                        ])
                            ->orderBy('total_emails_24h', $direction);
                    })
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 10 => 'success',
                        $state >= 5 => 'warning',
                        $state > 0 => 'info',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('total_emails_all_time')
                    ->label('Total Emails')
                    ->getStateUsing(function (UserDevice $record): int {
                        return \App\Models\Registration::where('device_fingerprint', $record->device_fingerprint)
                            ->where('user_id', $record->user_id)
                            ->count();
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->addSelect([
                            'total_emails_all_time' => \App\Models\Registration::selectRaw('COUNT(*)')
                                ->whereColumn('device_fingerprint', 'user_devices.device_fingerprint')
                                ->whereColumn('user_id', 'user_devices.user_id')
                        ])
                            ->orderBy('total_emails_all_time', $direction);
                    })
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'warning',
                        $state >= 10 => 'info',
                        $state > 0 => 'gray',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: false),


                Tables\Columns\TextColumn::make('device_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'desktop' => 'success',
                        'laptop' => 'info',
                        'mobile' => 'warning',
                        'tablet' => 'gray',
                        'unknown' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('os')
                    ->label('OS')
                    ->searchable()
                    ->placeholder('Unknown')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('browser')
                    ->label('Browser')
                    ->searchable()
                    ->placeholder('Unknown')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('Last Used')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never')
                    ->toggleable(isToggledHiddenByDefault: true),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'username')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('device_type')
                    ->options([
                        'desktop' => 'Desktop',
                        'laptop' => 'Laptop',
                        'mobile' => 'Mobile',
                        'tablet' => 'Tablet',
                        'unknown' => 'Unknown',
                    ]),

                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),

                Filter::make('last_used_at')
                    ->form([
                        DatePicker::make('last_used_from')
                            ->label('Last Used From'),
                        DatePicker::make('last_used_until')
                            ->label('Last Used Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['last_used_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('last_used_at', '>=', $date),
                            )
                            ->when(
                                $data['last_used_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('last_used_at', '<=', $date),
                            );
                    }),

                Filter::make('last_email_submission')
                    ->form([
                        DatePicker::make('submission_from')
                            ->label('Last Submission From'),
                        DatePicker::make('submission_until')
                            ->label('Last Submission Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['submission_from'] || $data['submission_until'],
                            function (Builder $query) use ($data) {
                                $query->whereHas('registrations', function (Builder $registrationQuery) use ($data) {
                                    if ($data['submission_from']) {
                                        $registrationQuery->whereDate('created_at', '>=', $data['submission_from']);
                                    }
                                    if ($data['submission_until']) {
                                        $registrationQuery->whereDate('created_at', '<=', $data['submission_until']);
                                    }
                                });
                            }
                        );
                    }),

                Filter::make('email_count_2h')
                    ->form([
                        Forms\Components\TextInput::make('emails_2h_min')
                            ->label('Min Emails (2h)')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('emails_2h_max')
                            ->label('Max Emails (2h)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['emails_2h_min'] || $data['emails_2h_max'],
                            function (Builder $query) use ($data) {
                                $query->whereHas('registrations', function (Builder $registrationQuery) use ($data) {
                                    $registrationQuery->where('created_at', '>=', now()->subHours(2));

                                    if ($data['emails_2h_min']) {
                                        $registrationQuery->havingRaw('COUNT(*) >= ?', [$data['emails_2h_min']]);
                                    }
                                    if ($data['emails_2h_max']) {
                                        $registrationQuery->havingRaw('COUNT(*) <= ?', [$data['emails_2h_max']]);
                                    }
                                });
                            }
                        );
                    }),

                Filter::make('email_count_24h')
                    ->form([
                        Forms\Components\TextInput::make('emails_24h_min')
                            ->label('Min Emails (24h)')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('emails_24h_max')
                            ->label('Max Emails (24h)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['emails_24h_min'] || $data['emails_24h_max'],
                            function (Builder $query) use ($data) {
                                $query->whereHas('registrations', function (Builder $registrationQuery) use ($data) {
                                    $registrationQuery->where('created_at', '>=', now()->subDay());

                                    if ($data['emails_24h_min']) {
                                        $registrationQuery->havingRaw('COUNT(*) >= ?', [$data['emails_24h_min']]);
                                    }
                                    if ($data['emails_24h_max']) {
                                        $registrationQuery->havingRaw('COUNT(*) <= ?', [$data['emails_24h_max']]);
                                    }
                                });
                            }
                        );
                    }),

                Filter::make('total_email_count')
                    ->form([
                        Forms\Components\TextInput::make('total_emails_min')
                            ->label('Min Total Emails')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('total_emails_max')
                            ->label('Max Total Emails')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['total_emails_min'] || $data['total_emails_max'],
                            function (Builder $query) use ($data) {
                                $query->whereHas('registrations', function (Builder $registrationQuery) use ($data) {
                                    if ($data['total_emails_min']) {
                                        $registrationQuery->havingRaw('COUNT(*) >= ?', [$data['total_emails_min']]);
                                    }
                                    if ($data['total_emails_max']) {
                                        $registrationQuery->havingRaw('COUNT(*) <= ?', [$data['total_emails_max']]);
                                    }
                                });
                            }
                        );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('deactivate_selected')
                    ->label('Deactivate Selected')
                    ->icon('heroicon-o-x-mark')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each->update(['is_active' => false]);
                    }),

                BulkAction::make('activate_selected')
                    ->label('Activate Selected')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each->update(['is_active' => true]);
                    }),

                BulkAction::make('deactivate_inactive_devices')
                    ->label('Deactivate Inactive Devices (No emails in 7 days)')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            $hasRecentEmails = \App\Models\Registration::where('device_fingerprint', $record->device_fingerprint)
                                ->where('user_id', $record->user_id)
                                ->where('created_at', '>=', now()->subDays(7))
                                ->exists();

                            if (!$hasRecentEmails) {
                                $record->update(['is_active' => false]);
                            }
                        });
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

    public static function getSubNavigation(): array
    {
        return [
            NavigationItem::make('List')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->url(static::getUrl('index'))
                ->isActiveWhen(fn() => request()->routeIs('filament.*.resources.user-devices.index')),

            NavigationItem::make('Charts')
                ->icon(Heroicon::OutlinedChartBar)
                ->url(static::getUrl('charts'))
                ->isActiveWhen(fn() => request()->routeIs('filament.*.resources.user-devices.charts')),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserDevices::route('/'),
            'create' => CreateUserDevice::route('/create'),
            'edit' => EditUserDevice::route('/{record}/edit'),
            'charts' => Pages\ChartsUserDevices::route('/charts'),
        ];
    }
}
