<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ATTENDANCE_TIMEZONE = 'Asia/Kolkata';

    public function up(): void
    {
        if (! Schema::hasColumn('employee_attendances', 'is_late')) {
            Schema::table('employee_attendances', function (Blueprint $table) {
                $table->boolean('is_late')->default(false)->index()->after('scheduled_out_time');
            });
        }

        if (! Schema::hasColumn('employee_attendances', 'late_minutes')) {
            Schema::table('employee_attendances', function (Blueprint $table) {
                $table->unsignedSmallInteger('late_minutes')->default(0)->after('is_late');
            });
        }

        DB::table('employee_attendances')
            ->whereNotNull('punch_in_at')
            ->whereNotNull('scheduled_in_time')
            ->orderBy('id')
            ->chunkById(200, function ($attendances) {
                foreach ($attendances as $attendance) {
                    $lateMinutes = $this->lateMinutes(
                        (string) $attendance->attendance_date,
                        (string) $attendance->scheduled_in_time,
                        (string) $attendance->punch_in_at
                    );

                    DB::table('employee_attendances')
                        ->where('id', '=', $attendance->id)
                        ->update([
                            'is_late' => $lateMinutes > 0,
                            'late_minutes' => $lateMinutes,
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('employee_attendances', 'late_minutes')) {
            Schema::table('employee_attendances', function (Blueprint $table) {
                $table->dropColumn('late_minutes');
            });
        }

        if (Schema::hasColumn('employee_attendances', 'is_late')) {
            Schema::table('employee_attendances', function (Blueprint $table) {
                $table->dropColumn('is_late');
            });
        }
    }

    private function lateMinutes(string $attendanceDate, string $scheduledInTime, string $punchInAt): int
    {
        try {
            $scheduledAt = Carbon::createFromFormat(
                'Y-m-d H:i',
                trim($attendanceDate).' '.substr(trim($scheduledInTime), 0, 5),
                self::ATTENDANCE_TIMEZONE
            );
            $punchedAt = Carbon::parse($punchInAt, self::ATTENDANCE_TIMEZONE);

            if (! $punchedAt->greaterThan($scheduledAt)) {
                return 0;
            }

            return (int) max(1, ceil($scheduledAt->diffInSeconds($punchedAt) / 60));
        } catch (\Throwable $e) {
            return 0;
        }
    }
};
