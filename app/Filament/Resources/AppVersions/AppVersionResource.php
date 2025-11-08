<?php

namespace App\Filament\Resources\AppVersions;

use App\Filament\Resources\AppVersions\Pages\CreateAppVersion;
use App\Filament\Resources\AppVersions\Pages\EditAppVersion;
use App\Filament\Resources\AppVersions\Pages\ListAppVersions;
use App\Models\AppVersion;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use BackedEnum;

class AppVersionResource extends Resource
{
    protected static ?string $model = AppVersion::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;
    protected static ?string $navigationLabel = 'App Versions';
    protected static ?string $modelLabel = 'Version';
    protected static ?string $pluralModelLabel = 'Versions';
    protected static ?int $navigationSort = 99;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Version Information')
                    ->schema([
                        TextInput::make('version')
                            ->label('Version')
                            ->placeholder('1.0.0')
                            ->required()
                            ->maxLength(50)
                            ->helperText('Semantic version (e.g., 1.0.0, 1.2.3)'),

                        TextInput::make('version_code')
                            ->label('Version Code')
                            ->placeholder('100')
                            ->required()
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->helperText('Integer for comparison (e.g., 100 for 1.0.0, 123 for 1.2.3)'),

                        Textarea::make('release_notes')
                            ->label('Release Notes')
                            ->rows(4)
                            ->placeholder('What\'s new in this version...'),

                        Toggle::make('is_force_update')
                            ->label('Force Update')
                            ->helperText('Users must update to this version'),
                    ]),

                Section::make('File Upload')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Executable File (.exe)')
                            ->required(fn($operation) => $operation === 'create')
                            ->disk('local')
                            ->directory('versions')
                            ->visibility('private')
                            ->maxSize(512000) // 500MB in KB
                            ->rules([
                                'required',
                                'file',
                                'mimes:exe',
                                'mimetypes:application/x-msdownload,application/octet-stream,application/x-msdos-program,application/x-dosexec',
                                'max:512000', // 500MB in KB (override Livewire default 12MB)
                            ])
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $filePath = storage_path('app/' . $state);
                                    if (file_exists($filePath)) {
                                        $set('file_size', filesize($filePath));
                                        $fileName = basename($state);
                                        $set('file_name', $fileName);
                                        $set('checksum', hash_file('sha256', $filePath));
                                    } else {
                                        // Even if file doesn't exist yet, set file_name from path
                                        $set('file_name', basename($state));
                                    }
                                }
                            })
                            ->dehydrated(false)
                            ->default(fn($record) => $record?->file_path),

                        TextInput::make('file_name')
                            ->label('File Name')
                            ->required()
                            ->dehydrated()
                            ->default(fn($get) => basename($get('file_path'))),

                        TextInput::make('file_size')
                            ->label('File Size (bytes)')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('checksum')
                            ->label('SHA256 Checksum')
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version')
                    ->label('Version')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('version_code')
                    ->label('Code')
                    ->sortable(),

                TextColumn::make('file_name')
                    ->label('File')
                    ->searchable(),

                TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn($state) => $state ? number_format($state / 1024 / 1024, 2) . ' MB' : '-')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_force_update')
                    ->label('Force')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Released')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(AppVersion $record) => !$record->is_active)
                    ->action(function (AppVersion $record) {
                        // Deactivate all other versions
                        AppVersion::where('id', '<>', $record->id)
                            ->update(['is_active' => false]);

                        // Activate this version
                        $record->update(['is_active' => true]);
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('version_code', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppVersions::route('/'),
            'create' => CreateAppVersion::route('/create'),
            'edit' => EditAppVersion::route('/{record}/edit'),
        ];
    }
}
