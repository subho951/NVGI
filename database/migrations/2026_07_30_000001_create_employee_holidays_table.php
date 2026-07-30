<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_holidays')) {
            Schema::create('employee_holidays', function (Blueprint $table) {
                $table->id();
                $table->date('holiday_date')->index();
                $table->string('name', 160);
                $table->string('branch_name', 120)->nullable()->index();
                $table->string('category', 100)->nullable()->index();
                $table->tinyInteger('status')->default(1)->index();
                $table->integer('created_by')->default(0);
                $table->integer('updated_by')->default(0);
                $table->timestamps();

                $table->index(
                    ['holiday_date', 'branch_name', 'category', 'status'],
                    'employee_holidays_scope_lookup'
                );
            });
        }

        foreach ([
            '2026-07-06',
            '2026-07-16',
        ] as $holidayDate) {
            $exists = DB::table('employee_holidays')
                ->whereDate('holiday_date', '=', $holidayDate)
                ->whereNull('branch_name')
                ->whereNull('category')
                ->exists();

            if (! $exists) {
                DB::table('employee_holidays')->insert([
                    'holiday_date' => $holidayDate,
                    'name' => 'Admin declared holiday',
                    'branch_name' => null,
                    'category' => null,
                    'status' => 1,
                    'created_by' => 0,
                    'updated_by' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (
            Schema::hasTable('employee_attendances')
            && Schema::hasTable('employee_schedule_rosters')
            && Schema::hasColumn('employee_attendances', 'is_absent')
            && Schema::hasColumn('employee_attendances', 'absent_marked_at')
        ) {
            DB::table('employee_attendances')
                ->where('is_absent', '=', 1)
                ->whereNull('punch_in_at')
                ->whereIn('roster_id', function ($query) {
                    $query
                        ->select('id')
                        ->from('employee_schedule_rosters')
                        ->whereIn('roster_date', [
                            '2026-07-06',
                            '2026-07-16',
                        ]);
                })
                ->update([
                    'is_absent' => 0,
                    'absent_marked_at' => null,
                    'updated_at' => now(),
                ]);

            if (
                Schema::hasTable('employees')
                && Schema::hasColumn('employees', 'doj')
            ) {
                DB::table('employee_attendances')
                    ->where('is_absent', '=', 1)
                    ->whereNull('punch_in_at')
                    ->whereIn('roster_id', function ($query) {
                        $query
                            ->select('roster.id')
                            ->from('employee_schedule_rosters as roster')
                            ->join('employees as employee', 'employee.id', '=', 'roster.employee_id')
                            ->whereNotNull('employee.doj')
                            ->whereColumn('roster.roster_date', '<', 'employee.doj');
                    })
                    ->update([
                        'is_absent' => 0,
                        'absent_marked_at' => null,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_holidays');
    }
};
