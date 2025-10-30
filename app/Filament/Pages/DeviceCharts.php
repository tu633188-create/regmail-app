<?php

namespace App\Filament\Pages;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use Filament\Pages\Page;

class DeviceCharts extends Page
{
    protected static ?string $title = 'Device Charts';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 12;

    protected string $view = 'filament.pages.device-charts';

    protected function getViewData(): array
    {
        $defaultFrom = now()->subDays(30)->toDateString();
        $defaultTo = now()->toDateString();
        $filters = [
            'user_id' => request()->query('user_id'),
            'device_fingerprint' => request()->query('device_fingerprint'),
            'from' => request()->query('from', $defaultFrom),
            'to' => request()->query('to', $defaultTo),
            'granularity' => request()->query('granularity', 'day'),
        ];

        $query = Registration::query()
            ->when(!empty($filters['user_id']), fn($q) => $q->where('registrations.user_id', $filters['user_id']))
            ->when(!empty($filters['device_fingerprint']), fn($q) => $q->where('registrations.device_fingerprint', $filters['device_fingerprint']))
            ->when(!empty($filters['from']), fn($q) => $q->whereDate('registrations.created_at', '>=', $filters['from']))
            ->when(!empty($filters['to']), fn($q) => $q->whereDate('registrations.created_at', '<=', $filters['to']));

        // Aggregate by device (x-axis: device_fingerprint, y-axis: registrations count)
        $rows = $query
            ->leftJoin('user_devices', function ($join) {
                $join->on('user_devices.device_fingerprint', '=', 'registrations.device_fingerprint')
                    ->on('user_devices.user_id', '=', 'registrations.user_id');
            })
            ->select([
                DB::raw("COALESCE(NULLIF(user_devices.device_name, ''), registrations.device_fingerprint) as label"),
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(50)
            ->get();

        $labels = $rows->pluck('label')->all();
        $values = $rows->pluck('total')->all();

        $users = User::query()->orderBy('username')->pluck('username', 'id')->all();

        return [
            'labels' => $labels,
            'values' => $values,
            'filters' => $filters,
            'users' => $users,
        ];
    }
}
