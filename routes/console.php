<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tickets:cleanup-transcripts')
    ->daily()
    ->at('02:00')
    ->timezone('Europe/Berlin')
    ->withoutOverlapping()
    ->runInBackground();
