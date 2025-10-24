<?php

namespace App\Filament\Exports;

use App\Models\Registration;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\Log;

class RegistrationExporter extends Exporter
{
    protected static ?string $model = Registration::class;

    public static function getColumns(): array
    {
        Log::debug('RegistrationExporter: getColumns called');
        try {
            return [
                ExportColumn::make('id')
                    ->label('ID'),
                ExportColumn::make('user.username')
                    ->label('User'),
                ExportColumn::make('device_fingerprint')
                    ->label('Device Fingerprint'),
                ExportColumn::make('device_name')
                    ->label('Device Name')
                    ->state(function (Registration $record): string {
                        $device = \App\Models\UserDevice::where('device_fingerprint', $record->device_fingerprint)
                            ->where('user_id', $record->user_id)
                            ->first();
                        return $device ? $device->device_name : 'Unknown Device';
                    }),
                ExportColumn::make('email')
                    ->label('Email'),
                ExportColumn::make('password')
                    ->label('Password'),
                ExportColumn::make('recovery_email')
                    ->label('Recovery Email'),
                ExportColumn::make('status')
                    ->label('Status'),
                ExportColumn::make('registration_time')
                    ->label('Registration Time')
                    ->state(function (Registration $record): string {
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
                    }),
                ExportColumn::make('proxy_ip')
                    ->label('Proxy IP'),
                ExportColumn::make('started_at')
                    ->label('Started At'),
                ExportColumn::make('completed_at')
                    ->label('Completed At'),
                ExportColumn::make('created_at')
                    ->label('Submitted At'),
            ];
        } catch (\Exception $e) {
            Log::error('RegistrationExporter error: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your registration export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
