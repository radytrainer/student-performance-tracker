<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!self::hasIndex('users', 'users_role_idx')) $table->index('role', 'users_role_idx');
                if (!self::hasIndex('users', 'users_is_active_idx')) $table->index('is_active', 'users_is_active_idx');
                if (!self::hasIndex('users', 'users_school_id_idx')) $table->index('school_id', 'users_school_id_idx');
                if (!self::hasIndex('users', 'users_created_at_idx')) $table->index('created_at', 'users_created_at_idx');
            });
        }
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (!self::hasIndex('students', 'students_current_class_id_idx')) $table->index('current_class_id', 'students_current_class_id_idx');
                if (!self::hasIndex('students', 'students_gender_idx')) $table->index('gender', 'students_gender_idx');
            });
        }
        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                if (!self::hasIndex('teachers', 'teachers_department_idx')) $table->index('department', 'teachers_department_idx');
            });
        }
        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                if (!self::hasIndex('subjects', 'subjects_department_idx')) $table->index('department', 'subjects_department_idx');
                if (!self::hasIndex('subjects', 'subjects_is_core_idx')) $table->index('is_core', 'subjects_is_core_idx');
            });
        }
        if (Schema::hasTable('classes')) {
            Schema::table('classes', function (Blueprint $table) {
                if (!self::hasIndex('classes', 'classes_academic_year_idx')) $table->index('academic_year', 'classes_academic_year_idx');
                if (!self::hasIndex('classes', 'classes_class_teacher_id_idx')) $table->index('class_teacher_id', 'classes_class_teacher_id_idx');
            });
        }
    }

    public function down(): void
    {
        // Optional: dropping indexes intentionally omitted to avoid accidental data loss.
    }

    private static function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $connection->listTableIndexes($table);
        return array_key_exists($index, $indexes);
    }
};
