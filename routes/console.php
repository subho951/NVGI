<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('attendance:mark-absent', function () {
    $markedCount = app(\App\Services\EmployeeAttendanceAbsenceService::class)->sync();
    $this->info($markedCount.' employee roster attendance record(s) marked absent.');
})->purpose('Mark completed roster assignments without punch-in as absent');

Schedule::command('attendance:mark-absent')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
