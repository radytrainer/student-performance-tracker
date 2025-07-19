<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StudentClass;
use App\Models\Student;
use App\Models\Classes;
use App\Models\Term;

class StudentClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $currentTerm = Term::where('is_current', true)->first();

        foreach ($students as $student) {
            if ($student->current_class_id) {
                StudentClass::create([
                    'student_id' => $student->user_id,
                    'class_id' => $student->current_class_id,
                    'enrollment_date' => $student->enrollment_date,
                    'status' => 'active',
                ]);
            }
        }
    }
}
