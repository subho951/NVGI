<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addPassword = !Schema::hasColumn('branches', 'password');
        $addOriginalPassword = !Schema::hasColumn('branches', 'original_password');

        if ($addPassword || $addOriginalPassword) {
            Schema::table('branches', function (Blueprint $table) use ($addPassword, $addOriginalPassword) {
                if ($addPassword) {
                    $table->string('password')->nullable()->after('name');
                }

                if ($addOriginalPassword) {
                    $table->text('original_password')->nullable()->after('password');
                }
            });
        }

        $usedSerialIds = [];
        $branches = DB::table('branches')
            ->select('id', 'serial_id', 'name')
            ->where('status', '!=', 3)
            ->orderBy('id', 'ASC')
            ->get();

        foreach ($branches as $branch) {
            $baseSerialId = trim((string) $branch->serial_id);

            if ($baseSerialId === '') {
                $baseSerialId = $this->makeSerialBase($branch->name);
            }

            $serialId = $baseSerialId;
            $suffix = 2;

            while (array_key_exists(strtolower($serialId), $usedSerialIds)) {
                $serialId = $baseSerialId . '-' . $suffix;
                $suffix++;
            }

            $usedSerialIds[strtolower($serialId)] = true;

            if ($serialId !== (string) $branch->serial_id) {
                DB::table('branches')
                    ->where('id', '=', $branch->id)
                    ->update(['serial_id' => $serialId]);
            }
        }
    }

    public function down(): void
    {
        $dropOriginalPassword = Schema::hasColumn('branches', 'original_password');
        $dropPassword = Schema::hasColumn('branches', 'password');

        if ($dropOriginalPassword || $dropPassword) {
            Schema::table('branches', function (Blueprint $table) use ($dropOriginalPassword, $dropPassword) {
                if ($dropOriginalPassword) {
                    $table->dropColumn('original_password');
                }

                if ($dropPassword) {
                    $table->dropColumn('password');
                }
            });
        }
    }

    private function makeSerialBase($name): string
    {
        $serialId = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', (string) $name));

        return substr($serialId !== '' ? $serialId : 'BRANCH', 0, 3);
    }
};
