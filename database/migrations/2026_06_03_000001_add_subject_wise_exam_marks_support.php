<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('class_subjects')) {
            Schema::create('class_subjects', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('unit_id')->default(0)->index();
                $table->integer('class_id')->default(0)->index();
                $table->integer('subject_id')->default(0)->index();
                $table->tinyInteger('status')->default(1);
                $table->timestamp('deleted_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }

        if (Schema::hasTable('exam_full_marks') && !Schema::hasColumn('exam_full_marks', 'subject_id')) {
            Schema::table('exam_full_marks', function (Blueprint $table) {
                $table->integer('subject_id')->default(0)->after('class_id');
            });
        }

        if (Schema::hasTable('exam_student_marks') && !Schema::hasColumn('exam_student_marks', 'subject_id')) {
            Schema::table('exam_student_marks', function (Blueprint $table) {
                $table->integer('subject_id')->default(0)->after('student_id');
            });
        }

        if (Schema::hasTable('exam_full_marks')) {
            DB::statement('ALTER TABLE exam_full_marks MODIFY full_marks DECIMAL(8,2) NOT NULL DEFAULT 0');
        }

        if (Schema::hasTable('exam_student_marks')) {
            DB::statement('ALTER TABLE exam_student_marks MODIFY full_marks DECIMAL(8,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE exam_student_marks MODIFY obtain_marks DECIMAL(8,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE exam_student_marks MODIFY marks_percentage DECIMAL(8,2) NOT NULL DEFAULT 0');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exam_student_marks') && Schema::hasColumn('exam_student_marks', 'subject_id')) {
            Schema::table('exam_student_marks', function (Blueprint $table) {
                $table->dropColumn('subject_id');
            });
        }

        if (Schema::hasTable('exam_full_marks') && Schema::hasColumn('exam_full_marks', 'subject_id')) {
            Schema::table('exam_full_marks', function (Blueprint $table) {
                $table->dropColumn('subject_id');
            });
        }

        Schema::dropIfExists('class_subjects');
    }
};
