<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment('WhatsApp CRM Ready!');
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
| Run: php artisan schedule:work (local dev)
|      Add to crontab: * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
*/

// Auto-engage: send re-engagement nudges before 24-hr session window expires
Schedule::command('wacrm:auto-engage')->everyFifteenMinutes();

// Drip campaigns: process pending scheduled drip messages
Schedule::command('wacrm:process-drips')->everyMinute();
