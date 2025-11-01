<?php

namespace App\Filament\Resources\UserTelegramSettings;

use App\Filament\Resources\UserTelegramSettings\Pages\ManageUserTelegramSettings;
use App\Filament\Resources\UserTelegramSettings\Pages\CreateUserTelegramSettings;
use App\Filament\Resources\UserTelegramSettings\Pages\EditUserTelegramSettings;
use App\Models\UserTelegramSettings;
use App\Services\UserTelegramService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Schemas\Components\Section;
use Illuminate\Validation\Rules\Unique;

class UserTelegramSettingsResource extends Resource
{
    protected static ?string $model = UserTelegramSettings::class;

    protected static ?string $navigationLabel = 'Telegram Settings';
    protected static ?string $modelLabel = 'Telegram Settings';
    protected static ?string $pluralModelLabel = 'Telegram Settings';
    protected static ?int $navigationSort = 98;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Telegram Bot Configuration')
                    ->description('Configure Telegram bot for notifications. You can create multiple bot configurations per user.')
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'username')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Each user can have multiple bot configurations'),

                        Toggle::make('telegram_enabled')
                            ->label('Enable Telegram Bot')
                            ->live(),

                        TextInput::make('telegram_bot_token')
                            ->label('Bot Token')
                            ->placeholder('1234567890:ABCdefGHIjklMNOpqrsTUVwxyz')
                            ->required(fn($get) => $get('telegram_enabled'))
                            ->password()
                            ->unique(
                                table: UserTelegramSettings::class,
                                column: 'telegram_bot_token',
                                modifyRuleUsing: function (Unique $rule, $get) {
                                    $userId = $get('user_id');
                                    if ($userId) {
                                        $rule->where('user_id', $userId);
                                    }
                                    return $rule;
                                },
                                ignoreRecord: true
                            )
                            ->helperText('Get this from @BotFather on Telegram. Must be unique per user.'),

                        TextInput::make('telegram_chat_id')
                            ->label('Chat ID')
                            ->placeholder('123456789')
                            ->required(fn($get) => $get('telegram_enabled'))
                            ->helperText('Your personal Telegram chat ID. Get it by messaging @userinfobot'),
                    ])
                    ->columns(2),

                Section::make('Notification Preferences')
                    ->schema([
                        Toggle::make('registration_notifications')
                            ->label('Registration Notifications')
                            ->helperText('Get notified when email registration completes')
                            ->visible(fn($get) => $get('telegram_enabled')),

                        Toggle::make('error_notifications')
                            ->label('Error Notifications')
                            ->helperText('Get notified when errors occur')
                            ->visible(fn($get) => $get('telegram_enabled')),

                        Toggle::make('daily_summary')
                            ->label('Daily Summary')
                            ->helperText('Receive daily summary of activities')
                            ->visible(fn($get) => $get('telegram_enabled')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.username')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => "Chat ID: {$record->telegram_chat_id}", position: 'below'),

                TextColumn::make('telegram_bot_token')
                    ->label('Bot Token')
                    ->limit(30)
                    ->copyable()
                    ->searchable()
                    ->description(fn($record) => substr($record->telegram_bot_token, -10) ?? 'N/A'),

                IconColumn::make('telegram_enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('registration_notifications')
                    ->label('Registration')
                    ->boolean(),

                IconColumn::make('error_notifications')
                    ->label('Errors')
                    ->boolean(),

                IconColumn::make('daily_summary')
                    ->label('Daily Summary')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('telegram_enabled')
                    ->label('Telegram Enabled'),

                TernaryFilter::make('registration_notifications')
                    ->label('Registration Notifications'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('setWebhook')
                    ->label('Set Webhook')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Set Telegram Webhook')
                    ->modalDescription('This will set the webhook URL for this bot. Make sure the bot token and chat ID are configured.')
                    ->modalSubmitActionLabel('Set Webhook')
                    ->action(function (UserTelegramSettings $record) {
                        if (!$record->telegram_enabled || !$record->telegram_bot_token) {
                            Notification::make()
                                ->title('Configuration Required')
                                ->body('Please enable Telegram and set Bot Token first.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $webhookUrl = url("/api/telegram/webhook/" . urlencode($record->telegram_bot_token));
                        $result = UserTelegramService::setWebhook($record->telegram_bot_token, $webhookUrl);

                        if ($result['success']) {
                            Notification::make()
                                ->title('Webhook Set Successfully')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failed to Set Webhook')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('checkWebhook')
                    ->label('Check Webhook')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->action(function (UserTelegramSettings $record) {
                        if (!$record->telegram_bot_token) {
                            Notification::make()
                                ->title('Bot Token Required')
                                ->body('Please set Bot Token first.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $result = UserTelegramService::getWebhookInfo($record->telegram_bot_token);

                        if ($result['success']) {
                            $info = $result['data'];
                            $url = $info['url'] ?? 'Not set';
                            $pendingUpdates = $info['pending_update_count'] ?? 0;
                            $lastErrorDate = $info['last_error_date'] ?? null;
                            $lastErrorMessage = $info['last_error_message'] ?? null;

                            $message = "URL: <code>{$url}</code>\n";
                            $message .= "Pending Updates: {$pendingUpdates}\n";
                            if ($lastErrorDate) {
                                $message .= "Last Error: " . date('Y-m-d H:i:s', $lastErrorDate) . "\n";
                                $message .= "Error: {$lastErrorMessage}";
                            }

                            Notification::make()
                                ->title('Webhook Info')
                                ->body($message)
                                ->info()
                                ->persistent()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failed to Get Webhook Info')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('deleteWebhook')
                    ->label('Delete Webhook')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Telegram Webhook')
                    ->modalDescription('This will delete the webhook for this bot. The bot will stop receiving updates via webhook.')
                    ->modalSubmitActionLabel('Delete')
                    ->action(function (UserTelegramSettings $record) {
                        if (!$record->telegram_bot_token) {
                            Notification::make()
                                ->title('Bot Token Required')
                                ->body('Please set Bot Token first.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $result = UserTelegramService::deleteWebhook($record->telegram_bot_token);

                        if ($result['success']) {
                            Notification::make()
                                ->title('Webhook Deleted')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failed to Delete Webhook')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUserTelegramSettings::route('/'),
            'create' => CreateUserTelegramSettings::route('/create'),
            'edit' => EditUserTelegramSettings::route('/{record}/edit'),
        ];
    }
}
