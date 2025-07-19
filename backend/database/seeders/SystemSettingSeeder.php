<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;
use App\Models\User;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $settings = [
            [
                'setting_key' => 'school_name',
                'setting_value' => 'Springfield High School',
                'description' => 'Name of the educational institution',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'academic_year',
                'setting_value' => '2024-2025',
                'description' => 'Current academic year',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'grade_scale',
                'setting_value' => 'A:90-100,B:80-89,C:70-79,D:60-69,F:0-59',
                'description' => 'Grading scale used for performance evaluation',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'attendance_threshold',
                'setting_value' => '75',
                'description' => 'Minimum attendance percentage required',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'low_grade_threshold',
                'setting_value' => '70',
                'description' => 'Grade threshold below which alerts are triggered',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'high_absence_threshold',
                'setting_value' => '5',
                'description' => 'Number of consecutive absences before alert',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'email_notifications',
                'setting_value' => 'enabled',
                'description' => 'Enable or disable email notifications',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'max_students_per_class',
                'setting_value' => '30',
                'description' => 'Maximum number of students allowed per class',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'report_card_template',
                'setting_value' => 'standard',
                'description' => 'Template used for generating report cards',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'semester_system',
                'setting_value' => 'enabled',
                'description' => 'Whether to use semester-based academic calendar',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'default_timezone',
                'setting_value' => 'America/Chicago',
                'description' => 'Default timezone for the school',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'academic_calendar_start',
                'setting_value' => '08-20',
                'description' => 'Default start date for academic year (MM-DD)',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'academic_calendar_end',
                'setting_value' => '05-30',
                'description' => 'Default end date for academic year (MM-DD)',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'late_assignment_penalty',
                'setting_value' => '10',
                'description' => 'Percentage penalty for late assignments',
                'updated_by' => $admin->id,
            ],
            [
                'setting_key' => 'parent_portal_enabled',
                'setting_value' => 'enabled',
                'description' => 'Enable parent access to student information',
                'updated_by' => $admin->id,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::create($setting);
        }
    }
}
