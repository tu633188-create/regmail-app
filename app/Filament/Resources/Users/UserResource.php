<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Models\JwtToken;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Forms\Components\TextInput::make('username')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn(string $context): bool => $context === 'create')
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->minLength(8),
                \Filament\Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'premium' => 'Premium',
                        'basic' => 'Basic',
                        'trial' => 'Trial',
                    ])
                    ->required(),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'banned' => 'Banned',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('role')
                    ->colors([
                        'danger' => 'admin',
                        'success' => 'premium',
                        'warning' => 'basic',
                        'secondary' => 'trial',
                    ]),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'suspended',
                        'danger' => 'banned',
                    ]),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('invalidate_tokens')
                    ->label('Invalidate Tokens')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(function (User $record) {
                        JwtToken::where('user_id', $record->id)
                            ->update(['is_blacklisted' => true]);

                        return redirect()->back();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Invalidate All Tokens')
                    ->modalDescription('This will log out all devices for this user. Are you sure?'),
            ])
            ->bulkActions([
                BulkAction::make('suspend')
                    ->label('Suspend Selected')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->action(function (Collection $records) {
                        $records->each->update(['status' => 'suspended']);
                    })
                    ->requiresConfirmation(),
                BulkAction::make('activate')
                    ->label('Activate Selected')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $records->each->update(['status' => 'active']);
                    }),
                BulkAction::make('invalidate_all_tokens')
                    ->label('Invalidate All Tokens')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(function (Collection $records) {
                        $userIds = $records->pluck('id');
                        JwtToken::whereIn('user_id', $userIds)
                            ->update(['is_blacklisted' => true]);
                    })
                    ->requiresConfirmation(),
            ]);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
