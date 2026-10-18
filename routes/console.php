<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('bms:check-low-stock')->dailyAt('07:00');
Schedule::command('bms:check-expired-batches')->dailyAt('07:05');
Schedule::command('backup:clean')->dailyAt('01:00');
Schedule::command('backup:run')->dailyAt('01:30')->onSuccess(function () {
    \Illuminate\Support\Facades\Notification::send(
        \App\Models\User::all(),
        new \App\Notifications\BackupCompletedNotification(true)
    );
})->onFailure(function () {
    \Illuminate\Support\Facades\Notification::send(
        \App\Models\User::all(),
        new \App\Notifications\BackupCompletedNotification(false)
    );
});
