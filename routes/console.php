<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule periodic Telegram notifications every 2 hours
Schedule::command('telegram:send-periodic --hours=2')
    ->cron('0 */2 * * *') // Every 2 hours at minute 0
    ->withoutOverlapping()
    ->runInBackground();
