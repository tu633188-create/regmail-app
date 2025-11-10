<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class Settings extends Page
{
    protected static ?string $title = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'mode' => AppSetting::get('mode', 'dual'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Device Mode Setting')
                    ->description('Set the default mode for all devices. This mode will be used by all devices when they fetch the configuration.')
                    ->schema([
                        Select::make('mode')
                            ->label('Mode')
                            ->options([
                                'dual' => 'Dual',
                                'cloud' => 'Cloud',
                                'google' => 'Google',
                            ])
                            ->required()
                            ->default('dual')
                            ->helperText('This mode will be applied to all devices when they fetch the configuration via API.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AppSetting::set('mode', $data['mode']);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('save'),
        ];
    }
}
