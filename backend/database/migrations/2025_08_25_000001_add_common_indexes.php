<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add indexes if tables exist
        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->index('student_id', 'grades_student_id_idx');
                $table->index('class_subject_id', 'grades_class_subject_id_idx');
                $table->index('term_id', 'grades_term_id_idx');
                $table->index('recorded_by', 'grades_recorded_by_idx');
                $table->index('created_at', 'grades_created_at_idx');
            });
        }
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->index('student_id', 'att_student_id_idx');
                $table->index('class_id', 'att_class_id_idx');
                $table->index('date', 'att_date_idx');
                $table->index('recorded_by', 'att_recorded_by_idx');
            });
        }
        if (Schema::hasTable('performance_alerts')) {
            Schema::table('performance_alerts', function (Blueprint $table) {
                $table->index('student_id', 'pa_student_id_idx');
                $table->index('term_id', 'pa_term_id_idx');
                $table->index('alert_type', 'pa_alert_type_idx');
                $table->index('severity', 'pa_severity_idx');
                $table->index('is_resolved', 'pa_is_resolved_idx');
                $table->index('created_by', 'pa_created_by_idx');
            });
        }
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index('user_id', 'notif_user_id_idx');
                $table->index('is_read', 'notif_is_read_idx');
                $table->index('sent_at', 'notif_sent_at_idx');
            });
        }
        if (Schema::hasTable('report_cards')) {
            Schema::table('report_cards', function (Blueprint $table) {
                $table->index('student_id', 'rc_student_id_idx');
                $table->index('term_id', 'rc_term_id_idx');
                $table->index('generated_by', 'rc_generated_by_idx');
                $table->index('generated_at', 'rc_generated_at_idx');
            });
        }
        if (Schema::hasTable('student_notes')) {
            Schema::table('student_notes', function (Blueprint $table) {
                $table->index('student_id', 'sn_student_id_idx');
                $table->index('teacher_id', 'sn_teacher_id_idx');
                $table->index('is_private', 'sn_is_private_idx');
                $table->index('created_at', 'sn_created_at_idx');
            });
        }
        if (Schema::hasTable('class_subjects')) {
            Schema::table('class_subjects', function (Blueprint $table) {
                $table->index('class_id', 'cs_class_id_idx');
                $table->index('subject_id', 'cs_subject_id_idx');
                $table->index('teacher_id', 'cs_teacher_id_idx');
            });
        }
    }

    public function down(): void
    {
        // Drop indexes if needed (optional)
    }
};
