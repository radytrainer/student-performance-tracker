<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClassSubject;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Teacher;

class ClassSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = Classes::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();

        // Get teachers by their specialization
        $mathTeacher = $teachers->where('department', 'Mathematics')->first();
        $scienceTeacher = $teachers->where('department', 'Science')->first();
        $englishTeacher = $teachers->where('department', 'English')->first();

        // Core subjects for all classes
        $coreSubjects = [
            'MATH101' => $mathTeacher->user_id,
            'ENG101' => $englishTeacher->user_id,
            'SCI101' => $scienceTeacher->user_id,
            'HIST101' => $englishTeacher->user_id, // English teacher can teach History
            'PE101' => $scienceTeacher->user_id, // Science teacher can teach PE
        ];

        foreach ($classes as $class) {
            foreach ($coreSubjects as $subjectCode => $teacherId) {
                $subject = $subjects->where('subject_code', $subjectCode)->first();
                
                if ($subject) {
                    ClassSubject::create([
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacherId,
                        'schedule' => $this->generateSchedule($subject->subject_name),
                    ]);
                }
            }

            // Add some elective subjects for higher grades
            if (str_contains($class->class_name, '10th Grade')) {
                $electiveSubjects = [
                    'PHY201' => $scienceTeacher->user_id,
                    'CHEM201' => $scienceTeacher->user_id,
                    'CS101' => $mathTeacher->user_id,
                ];

                foreach ($electiveSubjects as $subjectCode => $teacherId) {
                    $subject = $subjects->where('subject_code', $subjectCode)->first();
                    
                    if ($subject) {
                        ClassSubject::create([
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacherId,
                            'schedule' => $this->generateSchedule($subject->subject_name),
                        ]);
                    }
                }
            }
        }
    }

    private function generateSchedule($subjectName): string
    {
        $schedules = [
            'Mathematics' => 'Mon, Wed, Fri - 9:00 AM',
            'English Language Arts' => 'Tue, Thu - 10:00 AM',
            'General Science' => 'Mon, Wed - 11:00 AM',
            'World History' => 'Tue, Fri - 1:00 PM',
            'Physical Education' => 'Daily - 2:00 PM',
            'Physics' => 'Mon, Wed - 1:00 PM',
            'Chemistry' => 'Tue, Thu - 1:00 PM',
            'Computer Science' => 'Fri - 11:00 AM',
        ];

        return $schedules[$subjectName] ?? 'Schedule TBD';
    }
}
