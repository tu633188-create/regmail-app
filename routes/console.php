<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule periodic Telegram notifications at specific times
Schedule::command('telegram:send-periodic --hours=4')
    ->cron('0 0,4,8,12,16,20 * * *') // Every 4 hours: 0:00, 4:00, 8:00, 12:00, 16:00, 20:00
    ->withoutOverlapping()
    ->runInBackground();
