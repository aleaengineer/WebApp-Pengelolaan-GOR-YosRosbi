<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Bersihkan booking pending yang sudah terlewat jadwalnya (kupon & kuota member dikembalikan)
Schedule::command('booking:expire-stale')->dailyAt('01:00');
