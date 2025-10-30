<?php

namespace App\Filament\Resources\UserDevices\Widgets;

use App\Models\Registration;
use App\Models\User;
use Filament\Forms;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DeviceRegistrationsChart extends ChartWidget
{
    protected ?string $heading = 'Device Registrations';

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('user_id')
                ->label('User')
                ->options(User::query()->orderBy('username')->pluck('username', 'id'))
                ->searchable()
                ->placeholder('All'),
            Forms\Components\TextInput::make('device_fingerprint')
                ->label('Device Fingerprint')
                ->placeholder('All'),
            Forms\Components\DatePicker::make('from')->label('From'),
            Forms\Components\DatePicker::make('to')->label('To'),
            Forms\Components\Select::make('granularity')
                ->label('Granularity')
                ->options([
                    'hour' => 'Hourly',
                    'day' => 'Daily',
                ])
                ->default('day'),
        ];
    }

    protected function getData(): array
    {
        $f = $this->filterFormData ?? [];

        $q = Registration::query()
            ->when(!empty($f['user_id']), fn($q) => $q->where('user_id', $f['user_id']))
            ->when(!empty($f['device_fingerprint']), fn($q) => $q->where('device_fingerprint', $f['device_fingerprint']))
            ->when(!empty($f['from']), fn($q) => $q->where('created_at', '>=', $f['from']))
            ->when(!empty($f['to']), fn($q) => $q->where('created_at', '<=', $f['to']));

        $granularity = $f['granularity'] ?? 'day';
        $format = $granularity === 'hour' ? '%Y-%m-%d %H:00' : '%Y-%m-%d';

        $rows = $q->select([
            DB::raw("DATE_FORMAT(created_at, '{$format}') as bucket"),
            DB::raw('COUNT(*) as total'),
        ])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        return [
            'labels' => $rows->pluck('bucket')->all(),
            'datasets' => [[
                'label' => 'Registrations',
                'data' => $rows->pluck('total')->all(),
                'borderColor' => '#3b82f6',
                'backgroundColor' => 'rgba(59,130,246,0.2)',
                'fill' => true,
                'tension' => 0.3,
            ]],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
