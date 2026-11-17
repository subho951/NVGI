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

Artisan::command('attendance:reset-photo-free-punch-outs {date : Attendance date in YYYY-MM-DD format} {--apply : Back up and clear the matching punch-outs} {--ids= : Optional comma-separated attendance record IDs}', function () {
    $date = (string) $this->argument('date');
    try {
        $parsed = \Carbon\Carbon::createFromFormat('!Y-m-d', $date, 'Asia/Kolkata');
        if ($parsed->format('Y-m-d') !== $date) {
            throw new \InvalidArgumentException('Invalid date');
        }
    } catch (\Throwable $e) {
        $this->error('Specify a valid attendance date in YYYY-MM-DD format.');
        return 1;
    }

    $ids = trim((string) $this->option('ids'));
    if ($ids !== '' && ! preg_match('/^[1-9][0-9]*(,[1-9][0-9]*)*$/', $ids)) {
        $this->error('IDs must be positive integers separated by commas.');
        return 1;
    }

    return \Illuminate\Support\Facades\DB::transaction(function () use ($date, $ids) {
        $query = \Illuminate\Support\Facades\DB::table('employee_attendances')
            ->whereDate('attendance_date', $date)
            ->whereNotNull('punch_out_at')
            ->where(function ($query) {
                $query->whereNull('punch_out_image')->orWhereRaw("TRIM(punch_out_image) = ''");
            });
        if ($ids !== '') {
            $query->whereIn('id', explode(',', $ids));
        }
        if ($this->option('apply')) {
            $query->lockForUpdate();
        }
        $rows = $query->orderBy('id')->get();
        $this->table(['ID', 'Employee No', 'Name', 'Branch', 'Punch In', 'Punch Out'], $rows->map(fn ($row) => [
            $row->id, $row->employee_no, $row->employee_name, $row->branch_name, $row->punch_in_at, $row->punch_out_at,
        ])->all());
        $this->info($rows->count().' matching photo-free punch-out(s).');
        if (! $this->option('apply') || $rows->isEmpty()) {
            $this->info('No records changed. Use --apply to back up and clear matching punch-outs.');
            return 0;
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $backup = 'attendance-repairs/'.$date.'-'.\Illuminate\Support\Str::uuid().'.json';
        $saved = $disk->put($backup, json_encode([
            'attendance_date' => $date,
            'backed_up_at' => now()->toIso8601String(),
            'records' => $rows->all(),
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        if (! $saved) {
            throw new \RuntimeException('Backup failed. No attendance records were changed.');
        }
        $this->info('Backup: '.$disk->path($backup));

        $updated = \Illuminate\Support\Facades\DB::table('employee_attendances')
            ->whereIn('id', $rows->pluck('id'))
            ->update([
                'punch_out_at' => null,
                'punch_out_image' => null,
                'punch_out_ip' => null,
                'punch_out_portal_branch_id' => null,
                'updated_at' => now(),
            ]);
        $this->info($updated.' punch-out(s) cleared. Punch-in fields preserved.');
        return 0;
    });
})->purpose('Preview or reset photo-free punch-outs for an explicitly selected date, with a private backup');
