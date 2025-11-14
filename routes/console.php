<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\RollupLoginDaily;
use Illuminate\Support\Facades\Schedule;



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Register command so artisan sees it (optional if auto discovered)
Schedule::command(RollupLoginDaily::class)
    ->dailyAt('00:10');
