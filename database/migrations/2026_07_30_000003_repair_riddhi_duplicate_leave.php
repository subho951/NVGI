<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('employees')
            || ! Schema::hasTable('leave_applications')
            || ! Schema::hasTable('employee_leave_taken_histories')
            || ! Schema::hasTable('employee_leave_allotments')
        ) {
            return;
        }

        DB::transaction(function () {
            $employee = DB::table('employees')
                ->where('employee_no', '=', 'NVGI-0007')
                ->first();

            if (! $employee) {
                return;
            }

            $applications = DB::table('leave_applications')
                ->where('employee_id', '=', (int) $employee->id)
                ->whereDate('leave_from_date', '=', '2026-08-05')
                ->whereDate('leave_to_date', '=', '2026-08-11')
                ->where('status', '!=', 3)
                ->orderByRaw('CASE WHEN application_status = 1 THEN 0 ELSE 1 END')
                ->orderBy('id')
                ->get();

            if ($applications->isEmpty()) {
                return;
            }

            $canonicalApplication = $applications->first();
            $duplicateApplicationIds = $applications
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->reject(fn ($id) => $id === (int) $canonicalApplication->id)
                ->values()
                ->all();
            $history = null;

            if (! empty($canonicalApplication->leave_taken_history_id)) {
                $history = DB::table('employee_leave_taken_histories')
                    ->where('id', '=', (int) $canonicalApplication->leave_taken_history_id)
                    ->where('employee_id', '=', (int) $employee->id)
                    ->first();
            }

            if (! $history) {
                $history = DB::table('employee_leave_taken_histories')
                    ->where('employee_id', '=', (int) $employee->id)
                    ->where('leave_type_id', '=', (int) $canonicalApplication->leave_type_id)
                    ->whereDate('leave_date', '=', '2026-08-05')
                    ->where('leave_count', '=', 7)
                    ->where('status', '!=', 3)
                    ->orderBy('id')
                    ->first();
            }

            if (! $history) {
                $historyId = DB::table('employee_leave_taken_histories')->insertGetId([
                    'leave_application_id' => (int) $canonicalApplication->id,
                    'employee_id' => (int) $employee->id,
                    'employee_no' => $canonicalApplication->employee_no,
                    'employee_name' => $canonicalApplication->employee_name,
                    'leave_type_id' => (int) $canonicalApplication->leave_type_id,
                    'leave_date' => '2026-08-05',
                    'leave_count' => 7,
                    'remarks' => 'Approved leave application #'.$canonicalApplication->id
                        .' (05-08-2026 to 11-08-2026)',
                    'status' => 1,
                    'created_by' => (int) ($canonicalApplication->approved_by ?: 0),
                    'updated_by' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $history = DB::table('employee_leave_taken_histories')
                    ->where('id', '=', $historyId)
                    ->first();
            }

            DB::table('employee_leave_taken_histories')
                ->where('leave_application_id', '=', (int) $canonicalApplication->id)
                ->where('id', '!=', (int) $history->id)
                ->update([
                    'leave_application_id' => null,
                    'status' => 3,
                    'updated_at' => now(),
                ]);

            DB::table('employee_leave_taken_histories')
                ->where('id', '=', (int) $history->id)
                ->update([
                    'leave_application_id' => (int) $canonicalApplication->id,
                    'leave_count' => 7,
                    'status' => 1,
                    'updated_at' => now(),
                ]);

            DB::table('leave_applications')
                ->where('id', '=', (int) $canonicalApplication->id)
                ->update([
                    'no_of_days' => 7,
                    'leave_taken_history_id' => (int) $history->id,
                    'status' => 1,
                    'updated_at' => now(),
                ]);

            if (! empty($duplicateApplicationIds)) {
                DB::table('leave_applications')
                    ->whereIn('id', $duplicateApplicationIds)
                    ->update([
                        'status' => 3,
                        'updated_at' => now(),
                    ]);

                DB::table('employee_leave_taken_histories')
                    ->whereIn('leave_application_id', $duplicateApplicationIds)
                    ->update([
                        'status' => 3,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('employee_leave_taken_histories')
                ->where('employee_id', '=', (int) $employee->id)
                ->where('leave_type_id', '=', (int) $canonicalApplication->leave_type_id)
                ->whereDate('leave_date', '=', '2026-08-05')
                ->where('leave_count', '=', 7)
                ->where('id', '!=', (int) $history->id)
                ->update([
                    'status' => 3,
                    'updated_at' => now(),
                ]);

            DB::table('employee_leave_allotments')
                ->where('employee_id', '=', (int) $employee->id)
                ->where('status', '!=', 3)
                ->orderBy('id')
                ->get()
                ->each(function ($allotment) use ($employee) {
                    $usedLeave = DB::table('employee_leave_taken_histories')
                        ->where('employee_id', '=', (int) $employee->id)
                        ->where('leave_type_id', '=', (int) $allotment->leave_type_id)
                        ->where('status', '!=', 3)
                        ->whereBetween('leave_date', [
                            $allotment->leave_tenure_from,
                            $allotment->leave_tenure_to,
                        ])
                        ->sum('leave_count');

                    DB::table('employee_leave_allotments')
                        ->where('id', '=', (int) $allotment->id)
                        ->update([
                            'used_leave' => $usedLeave,
                            'balance_leave' => max(
                                (float) $allotment->total_allotment - (float) $usedLeave,
                                0
                            ),
                            'updated_at' => now(),
                        ]);
                });
        });
    }

    public function down(): void
    {
        // This migration repairs duplicate operational data and is intentionally irreversible.
    }
};
