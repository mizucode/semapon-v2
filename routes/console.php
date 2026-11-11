<?php

use App\Services\ReminderService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('semapon:scan-reminders', function (ReminderService $reminderService) {
    $this->info('Scanning all stores for overdue orders, unpicked units, and low stock products...');
    $created = $reminderService->scanAllStores();
    $this->info("Scan completed successfully. {$created} new reminder(s) generated.");
})->purpose('Scan operational data across all active stores and generate reminders');

Schedule::command('semapon:scan-reminders')->hourly();
