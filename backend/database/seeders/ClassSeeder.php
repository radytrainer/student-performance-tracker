<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Classes;
use App\Models\Teacher;
use App\Models\Student;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = Teacher::all();

        $classes = [
            [
                'class_name' => 'Web Design',
                'academic_year' => '2024-2025',
                'class_teacher_id' => optional($teachers->where('teacher_code', 'TCH001')->first())->user_id,
                'room_number' => 'Room 101',
                'schedule' => 'Monday-Friday 8:00 AM - 3:00 PM',
                'school_id' => 1,
            ],
            [
                'class_name' => 'Computer Science',
                'academic_year' => '2024-2025',
                'class_teacher_id' => optional($teachers->where('teacher_code', 'TCH002')->first())->user_id,
                'room_number' => 'Room 102',
                'schedule' => 'Monday-Friday 8:00 AM - 3:00 PM',
                'school_id' => 1,
            ],
            [
                'class_name' => 'Computer Networking',
                'academic_year' => '2024-2025',
                'class_teacher_id' => optional($teachers->where('teacher_code', 'TCH003')->first())->user_id,
                'room_number' => 'Room 201',
                'schedule' => 'Monday-Friday 8:00 AM - 3:00 PM',
                'school_id' => 1,
            ],
            // Class for requested test teacher
            [
                'class_name' => 'Graphic Design',
                'academic_year' => '2024-2025',
                'class_teacher_id' => optional($teachers->where('teacher_code', 'TCH010')->first())->user_id,
                'room_number' => 'Lab 5',
                'schedule' => 'Tue/Thu 10:00 AM - 11:30 AM',
                'school_id' => 1,
            ],
        ];

        foreach ($classes as $classData) {
            Classes::create($classData);
        }

        // Assign students to classes
        $students = Student::all();
        $createdClasses = Classes::all();

        // Distribute students among classes
        $students->take(2)->each(function ($student) use ($createdClasses) {
            $student->update(['current_class_id' => $createdClasses->first()->id]);
        });

        $students->skip(2)->take(2)->each(function ($student) use ($createdClasses) {
            $student->update(['current_class_id' => $createdClasses->skip(1)->first()->id]);
        });

        $students->skip(4)->each(function ($student) use ($createdClasses) {
            $student->update(['current_class_id' => $createdClasses->last()->id]);
        });
    }
}
