<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MODULE_ID = 40;

    private const MODULE_NAME = 'Masters - Holiday Management';

    private const MASTER_MODULE_IDS = [
        '6',
        '7',
        '8',
        '9',
        '10',
        '11',
        '12',
        '13',
        '14',
        '27',
    ];

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
            $this->grantToMasterRoles($now);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            DB::table('roles')
                ->select(['id', 'module_id'])
                ->get()
                ->each(function ($role) {
                    $moduleIds = json_decode($role->module_id, true);

                    if (! is_array($moduleIds)) {
                        return;
                    }

                    $filteredModuleIds = collect($moduleIds)
                        ->map(fn ($moduleId) => (string) $moduleId)
                        ->reject(fn ($moduleId) => $moduleId === (string) self::MODULE_ID)
                        ->values()
                        ->all();

                    DB::table('roles')
                        ->where('id', '=', (int) $role->id)
                        ->update([
                            'module_id' => json_encode($filteredModuleIds),
                            'updated_at' => now(),
                        ]);
                });
        }

        if (Schema::hasTable('modules')) {
            DB::table('modules')
                ->where('id', '=', self::MODULE_ID)
                ->where('name', '=', self::MODULE_NAME)
                ->delete();
        }
    }

    private function grantToMasterRoles($now): void
    {
        DB::table('roles')
            ->select(['id', 'module_id'])
            ->where('status', '!=', 3)
            ->get()
            ->each(function ($role) use ($now) {
                $moduleIds = json_decode($role->module_id, true);

                if (! is_array($moduleIds)) {
                    return;
                }

                $moduleIds = collect($moduleIds)
                    ->map(fn ($moduleId) => (string) $moduleId)
                    ->unique()
                    ->values()
                    ->all();

                if (in_array((string) self::MODULE_ID, $moduleIds, true)) {
                    return;
                }

                if (
                    (int) $role->id !== 1
                    && count(array_intersect(self::MASTER_MODULE_IDS, $moduleIds)) === 0
                ) {
                    return;
                }

                $moduleIds[] = (string) self::MODULE_ID;

                DB::table('roles')
                    ->where('id', '=', (int) $role->id)
                    ->update([
                        'module_id' => json_encode($moduleIds),
                        'updated_at' => $now,
                    ]);
            });
    }
};
