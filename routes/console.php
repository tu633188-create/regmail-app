<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule periodic Telegram notifications every 1 hour
Schedule::command('telegram:send-periodic --hours=1')
    ->cron('0 */1 * * *') // Every 1 hour at minute 0
    ->withoutOverlapping()
    ->runInBackground();
