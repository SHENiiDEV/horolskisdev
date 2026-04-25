<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('check:project-health')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::call(function () {
    \App\Models\ProjectUptimeCheck::where('created_at', '<', now()->subDay())->delete();
})->daily()->name('cleanup-monitoring-data');

Schedule::command('backup:run')->dailyAt('01:00');
Schedule::command('backup:clean')->dailyAt('01:30');
