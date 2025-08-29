<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;
use App\Models\Teacher;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            [
                'name' => 'Mathematics',
                'description' => 'Department of Mathematics and Applied Sciences',
            ],
            [
                'name' => 'English',
                'description' => 'Department of English Language and Literature',
            ],
            [
                'name' => 'Science',
                'description' => 'Department of Natural Sciences including Physics, Chemistry, Biology',
            ],
            [
                'name' => 'History',
                'description' => 'Department of History and Social Studies',
            ],
            [
                'name' => 'Physical Education',
                'description' => 'Department of Physical Education and Sports',
            ],
        ];

        foreach ($departments as $deptData) {
            $department = Department::create($deptData);

            // Try to find a teacher to assign as head
            $availableTeacher = User::where('role', 'teacher')
                ->whereNull('department_id')
                ->inRandomOrder()
                ->first();

            if ($availableTeacher) {
                $department->update(['head_teacher_id' => $availableTeacher->id]);
                $availableTeacher->update(['department_id' => $department->id]);

                // Generate teacher code for the head teacher
                if ($availableTeacher->teacher) {
                    $teacherCode = $department->generateTeacherCode();
                    $availableTeacher->teacher->update(['teacher_code' => $teacherCode]);
                }
            }
        }

        // Assign remaining teachers to random departments
        $remainingTeachers = User::where('role', 'teacher')
            ->whereNull('department_id')
            ->get();

        $allDepartments = Department::all();

        foreach ($remainingTeachers as $teacher) {
            if ($allDepartments->count() > 0) {
                $randomDepartment = $allDepartments->random();
                $teacher->update(['department_id' => $randomDepartment->id]);

                // Generate teacher code
                if ($teacher->teacher) {
                    $teacherCode = $randomDepartment->generateTeacherCode();
                    $teacher->teacher->update(['teacher_code' => $teacherCode]);
                }
            }
        }
    }
}
