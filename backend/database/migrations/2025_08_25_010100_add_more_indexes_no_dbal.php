<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            if (Schema::hasTable('users')) {
                Schema::table('users', function (Blueprint $table) {
                    try { $table->index('role', 'users_role_idx'); } catch (\Throwable $e) {}
                    try { $table->index('is_active', 'users_is_active_idx'); } catch (\Throwable $e) {}
                    try { $table->index('school_id', 'users_school_id_idx'); } catch (\Throwable $e) {}
                    try { $table->index('created_at', 'users_created_at_idx'); } catch (\Throwable $e) {}
                });
            }
            if (Schema::hasTable('students')) {
                Schema::table('students', function (Blueprint $table) {
                    try { $table->index('current_class_id', 'students_current_class_id_idx'); } catch (\Throwable $e) {}
                    try { $table->index('gender', 'students_gender_idx'); } catch (\Throwable $e) {}
                });
            }
            if (Schema::hasTable('teachers')) {
                Schema::table('teachers', function (Blueprint $table) {
                    try { $table->index('department', 'teachers_department_idx'); } catch (\Throwable $e) {}
                });
            }
            if (Schema::hasTable('subjects')) {
                Schema::table('subjects', function (Blueprint $table) {
                    try { $table->index('department', 'subjects_department_idx'); } catch (\Throwable $e) {}
                    try { $table->index('is_core', 'subjects_is_core_idx'); } catch (\Throwable $e) {}
                });
            }
            if (Schema::hasTable('classes')) {
                Schema::table('classes', function (Blueprint $table) {
                    try { $table->index('academic_year', 'classes_academic_year_idx'); } catch (\Throwable $e) {}
                    try { $table->index('class_teacher_id', 'classes_class_teacher_id_idx'); } catch (\Throwable $e) {}
                });
            }
        } catch (\Throwable $e) {
            // no-op, best-effort indexing
        }
    }

    public function down(): void
    {
        // no down: indexes are safe to keep
    }
};
