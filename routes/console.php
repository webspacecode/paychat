<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:generate-reports')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule::command('app:generate-reports --period=yesterday')
//     ->hourlyAt(10)
//     ->withoutOverlapping()
//     ->runInBackground();

// Schedule::command('app:generate-reports --period=week')
//     ->dailyAt('03:00')
//     ->withoutOverlapping()
//     ->runInBackground();
