<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reminder:spp')->dailyAt('08:00');

// Backup database & file storage tiap hari + bersihkan backup lama tiap minggu.
Schedule::command('backup:clean')->weeklyOn(1, '01:30');
Schedule::command('backup:run')->dailyAt('02:00');

