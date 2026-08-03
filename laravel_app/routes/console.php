<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Cron (server): * * * * * cd /path && php artisan schedule:run
| Periksa stok: email + simulasi WA (Laravel 12: routes/console.php).
*/
Schedule::command('check:stock')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/check-stock.log'));
