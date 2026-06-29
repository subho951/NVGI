<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MODULE_ID = 39;
    private const MODULE_NAME = 'Payroll & Leave - Salary Statement';
    private const SALARY_MODULE_IDS = ['38'];

    public function up(): void
    {
        $now = now();

        if (Schema::hasTable('modules')) {
            $existing = DB::table('modules')->where('id', self::MODULE_ID)->first();

            if ($existing) {
                DB::table('modules')
                    ->where('id', self::MODULE_ID)
                    ->update([
                        'name' => self::MODULE_NAME,
                        'status' => 1,
                        'deleted_at' => null,
                        'updated_by' => 1,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('modules')->insert([
                    'id' => self::MODULE_ID,
                    'name' => self::MODULE_NAME,
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                    'deleted_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('roles')) {
            $this->grantToSalaryRoles($now);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            $roles = DB::table('roles')->select('id', 'module_id')->get();

            foreach ($roles as $role) {
                $moduleIds = json_decode($role->module_id, true);

                if (! is_array($moduleIds)) {
                    continue;
                }

                $filteredModuleIds = array_values(array_filter(
                    array_map('strval', $moduleIds),
                    fn ($moduleId) => $moduleId !== (string) self::MODULE_ID
                ));

                if ($filteredModuleIds !== array_values(array_map('strval', $moduleIds))) {
                    DB::table('roles')
                        ->where('id', $role->id)
                        ->update([
                            'module_id' => json_encode($filteredModuleIds),
                            'updated_at' => now(),
                        ]);
                }
            }
        }

        if (Schema::hasTable('modules')) {
            DB::table('modules')
                ->where('id', self::MODULE_ID)
                ->where('name', self::MODULE_NAME)
                ->delete();
        }
    }

    private function grantToSalaryRoles($now): void
    {
        $roles = DB::table('roles')->select('id', 'module_id')->where('status', '!=', 3)->get();

        foreach ($roles as $role) {
            $moduleIds = json_decode($role->module_id, true);

            if (! is_array($moduleIds)) {
                continue;
            }

            $moduleIds = array_values(array_map('strval', $moduleIds));

            if (in_array((string) self::MODULE_ID, $moduleIds, true)) {
                continue;
            }

            if (count(array_intersect(self::SALARY_MODULE_IDS, $moduleIds)) === 0) {
                continue;
            }

            $moduleIds[] = (string) self::MODULE_ID;

            DB::table('roles')
                ->where('id', $role->id)
                ->update([
                    'module_id' => json_encode($moduleIds),
                    'updated_at' => $now,
                ]);
        }
    }
};
