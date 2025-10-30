<x-filament::page>
    <x-filament::section heading="Filters" class="mb-6 device-filters">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div style="display:flex;flex-direction:column;gap:4px;width:260px;">
                <p style="font-size:12px;font-weight:500;color:#374151;">User</p>
                <select name="user_id" style="width:100%;height:36px;border:1px solid #d1d5db;border-radius:8px;padding:2px 28px 2px 10px;background:#fff;color:#111827;box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                    <option value="">All</option>
                    @foreach($users as $id => $name)
                    <option value="{{ $id }}" {{ (($filters['user_id'] ?? '') == (string) $id) ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;min-width:260px;">
                <p style="font-size:12px;font-weight:500;color:#374151;">Device Name</p>
                <select name="device_name" style="width:260px;height:36px;border:1px solid #d1d5db;border-radius:8px;padding:0 28px 0 10px;background:#fff;color:#111827;box-shadow:0 1px 2px rgba(0,0,0,0.05);" {{ empty($filters['user_id']) ? 'disabled' : '' }}>
                    <option value="">All</option>
                    @foreach(($deviceNames ?? []) as $dn)
                    <option value="{{ $dn }}" {{ (($filters['device_name'] ?? '') === $dn) ? 'selected' : '' }}>{{ $dn }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;width:200px;">
                <p style="font-size:12px;font-weight:500;color:#374151;">From</p>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" style="width:100%;height:36px;border:1px solid #d1d5db;border-radius:8px;padding:0 10px;background:#fff;color:#111827;box-shadow:0 1px 2px rgba(0,0,0,0.05);">
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;width:200px;">
                <p style="font-size:12px;font-weight:500;color:#374151;">To</p>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" style="width:100%;height:36px;border:1px solid #d1d5db;border-radius:8px;padding:0 10px;background:#fff;color:#111827;box-shadow:0 1px 2px rgba(0,0,0,0.05);">
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;width:220px;">
                <p style="font-size:12px;font-weight:500;color:#374151;">Granularity</p>
                <select name="granularity" style="width:100%;height:36px;border:1px solid #d1d5db;border-radius:8px;padding:0 28px 0 10px;background:#fff;color:#111827;box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                    <option value="day" {{ (($filters['granularity'] ?? 'day') === 'day') ? 'selected' : '' }}>Daily</option>
                    <option value="hour" {{ (($filters['granularity'] ?? 'day') === 'hour') ? 'selected' : '' }}>Hourly</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;padding-top:4px;">
                <button type="submit" style="display:inline-flex;align-items:center;border-radius:8px;background:#2563eb;color:#fff;height:36px;padding:0 12px;font-size:14px;border:0;cursor:pointer;">Apply</button>
                <a href="{{ url()->current() }}" style="display:inline-flex;align-items:center;border-radius:8px;height:36px;padding:0 12px;font-size:14px;border:1px solid #d1d5db;color:#111827;text-decoration:none;">Reset</a>
            </div>
        </form>
    </x-filament::section>

    <x-filament::section heading="Registrations by Device">
        <div class="chart-card bg-white dark:bg-gray-900 rounded-2xl p-5 shadow-sm">
            <div class="chart-wrapper">
                <canvas id="registrationsChart"></canvas>
            </div>
        </div>
    </x-filament::section>

    <script type="application/json" id="chart-data">
        @json(['labels' => $labels, 'values' => $values])
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/device-charts.js') }}"></script>
    <style>
        .chart-wrapper {
            height: 440px;
        }
    </style>

</x-filament::page>