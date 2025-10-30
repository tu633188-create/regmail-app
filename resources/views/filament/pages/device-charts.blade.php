<x-filament::page>
    <x-filament::section heading="Filters" class="mb-6 device-filters">
        <form method="GET" class="space-y-3">
            <div class="grid grid-cols-1 gap-3 max-w-sm">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">User</label>
                    <select name="user_id" class="fi-input mt-1 block w-full rounded-lg shadow-sm text-sm h-9">
                        <option value="">All</option>
                        @foreach($users as $id => $name)
                        <option value="{{ $id }}" {{ (($filters['user_id'] ?? '') == (string) $id) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Device Fingerprint</label>
                    <input type="text" name="device_fingerprint" value="{{ $filters['device_fingerprint'] ?? '' }}" class="fi-input mt-1 block w-full rounded-lg shadow-sm text-sm h-9" placeholder="All">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">From</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="fi-input mt-1 block w-full rounded-lg shadow-sm text-sm h-9">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">To</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="fi-input mt-1 block w-full rounded-lg shadow-sm text-sm h-9">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Granularity</label>
                    <select name="granularity" class="fi-input mt-1 block w-full rounded-lg shadow-sm text-sm h-9">
                        <option value="day" {{ (($filters['granularity'] ?? 'day') === 'day') ? 'selected' : '' }}>Daily</option>
                        <option value="hour" {{ (($filters['granularity'] ?? 'day') === 'hour') ? 'selected' : '' }}>Hourly</option>
                    </select>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="submit" class="fi-btn fi-btn-primary rounded-lg h-9 px-3 text-sm">Apply</button>
                    <a href="{{ url()->current() }}" class="fi-btn rounded-lg h-9 px-3 text-sm">Reset</a>
                </div>
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
        .device-filters .fi-input {
            height: 36px;
        }

        .chart-wrapper {
            height: 440px;
        }
    </style>
</x-filament::page>