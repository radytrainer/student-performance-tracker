<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Term;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\Classes;
use Carbon\Carbon;

class ReportsTestDataSeeder extends Seeder
{
    public function run()
    {
        // Get current term
        $currentTerm = Term::where('is_current', true)->first();
        if (!$currentTerm) {
            $this->command->info('No current term found. Please run the main seeder first.');
            return;
        }

        // Get all students
        $students = Student::with('user')->get();
        
        // Get all subjects and classes
        $subjects = Subject::all();
        $classes = Classes::all();
        
        if ($subjects->isEmpty() || $classes->isEmpty()) {
            $this->command->info('No subjects or classes found. Creating some...');
            $this->createSubjectsAndClasses();
            $subjects = Subject::all();
            $classes = Classes::all();
        }

        // Create class-subject relationships if they don't exist
        $this->createClassSubjects($classes, $subjects);
        
        foreach ($students as $student) {
            $this->command->info("Creating test data for student: {$student->user->username}");
            
            // Assign student to a class if not already assigned
            if (!$student->current_class_id) {
                $student->current_class_id = $classes->random()->id;
                $student->save();
            }
            
            // Create grades for this student
            $this->createGradesForStudent($student, $currentTerm, $subjects);
            
            // Create attendance records for this student
            $this->createAttendanceForStudent($student, $currentTerm);
        }
        
        $this->command->info('Reports test data created successfully!');
    }

    private function createSubjectsAndClasses()
    {
        // Create some basic subjects if none exist
        $subjectData = [
            ['subject_code' => 'MATH101', 'subject_name' => 'Mathematics', 'credit_hours' => 3],
            ['subject_code' => 'ENG101', 'subject_name' => 'English', 'credit_hours' => 3],
            ['subject_code' => 'SCI101', 'subject_name' => 'Science', 'credit_hours' => 4],
            ['subject_code' => 'HIST101', 'subject_name' => 'History', 'credit_hours' => 2],
            ['subject_code' => 'ART101', 'subject_name' => 'Art', 'credit_hours' => 2],
        ];

        foreach ($subjectData as $subject) {
            Subject::firstOrCreate(['subject_code' => $subject['subject_code']], $subject);
        }

        // Create some basic classes if none exist
        $classData = [
            ['class_name' => '10th Grade A', 'academic_year' => 2024],
            ['class_name' => '10th Grade B', 'academic_year' => 2024],
            ['class_name' => '11th Grade A', 'academic_year' => 2024],
        ];

        foreach ($classData as $class) {
            Classes::firstOrCreate(['class_name' => $class['class_name']], $class);
        }
    }

    private function createClassSubjects($classes, $subjects)
    {
        // Get a teacher to assign to class subjects
        $teacher = \App\Models\Teacher::first();
        if (!$teacher) {
            // Create a dummy teacher if none exists
            $teacherUser = \App\Models\User::create([
                'username' => 'teacher_demo',
                'email' => 'teacher@demo.com',
                'password_hash' => bcrypt('password'),
                'role' => 'teacher',
                'first_name' => 'Demo',
                'last_name' => 'Teacher',
                'is_active' => true,
            ]);
            
            $teacher = \App\Models\Teacher::create([
                'user_id' => $teacherUser->id,
                'teacher_code' => 'TCH001',
                'department' => 'General',
                'qualification' => 'B.Ed',
                'hire_date' => now(),
            ]);
        }

        foreach ($classes as $class) {
            foreach ($subjects as $subject) {
                ClassSubject::firstOrCreate([
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                ], [
                    'teacher_id' => $teacher->user_id,
                ]);
            }
        }
    }

    private function createGradesForStudent($student, $term, $subjects)
    {
        $classSubjects = ClassSubject::where('class_id', $student->current_class_id)->get();
        
        foreach ($classSubjects as $classSubject) {
            // Create multiple assessments per subject
            $assessmentTypes = ['exam', 'quiz', 'assignment', 'project'];
            
            foreach ($assessmentTypes as $type) {
                // Random number of assessments (1-3) per type
                $numAssessments = rand(1, 3);
                
                for ($i = 0; $i < $numAssessments; $i++) {
                    $maxScore = $type === 'exam' ? 100 : ($type === 'project' ? 50 : 25);
                    $scoreObtained = rand(60, $maxScore); // Random score between 60% and 100%
                    $percentage = ($scoreObtained / $maxScore) * 100;
                    
                    // Convert percentage to letter grade
                    $gradeLetter = $this->getLetterGrade($percentage);
                    
                    Grade::create([
                        'student_id' => $student->user_id,
                        'class_subject_id' => $classSubject->id,
                        'term_id' => $term->id,
                        'assessment_type' => $type,
                        'max_score' => $maxScore,
                        'score_obtained' => $scoreObtained,
                        'weightage' => $type === 'exam' ? 40 : ($type === 'project' ? 30 : 15),
                        'grade_letter' => $gradeLetter,
                        'remarks' => $this->getRemarks($percentage),
                        'recorded_by' => 1, // Admin user
                        'recorded_at' => now()->subDays(rand(1, 30)),
                    ]);
                }
            }
        }
    }

    private function createAttendanceForStudent($student, $term)
    {
        $startDate = Carbon::parse($term->start_date);
        $endDate = Carbon::parse($term->end_date)->min(now()); // Don't go beyond today
        
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            // Skip weekends (basic school days only)
            if ($currentDate->isWeekday()) {
                // 90% chance of being present, 5% late, 3% absent, 2% excused
                $rand = rand(1, 100);
                
                if ($rand <= 90) {
                    $status = 'present';
                } elseif ($rand <= 95) {
                    $status = 'late';
                } elseif ($rand <= 98) {
                    $status = 'absent';
                } else {
                    $status = 'excused';
                }
                
                Attendance::create([
                    'student_id' => $student->user_id,
                    'class_id' => $student->current_class_id,
                    'date' => $currentDate->format('Y-m-d'),
                    'status' => $status,
                    'recorded_by' => 1,
                    'notes' => $status === 'late' ? 'Arrived 10 minutes late' : 
                              ($status === 'absent' ? 'Unexcused absence' : 
                              ($status === 'excused' ? 'Medical appointment' : null)),
                    'recorded_at' => $currentDate->copy()->addHours(8), // 8 AM attendance
                ]);
            }
            
            $currentDate->addDay();
        }
    }

    private function getLetterGrade($percentage)
    {
        if ($percentage >= 97) return 'A+';
        if ($percentage >= 93) return 'A';
        if ($percentage >= 90) return 'A-';
        if ($percentage >= 87) return 'B+';
        if ($percentage >= 83) return 'B';
        if ($percentage >= 80) return 'B-';
        if ($percentage >= 77) return 'C+';
        if ($percentage >= 73) return 'C';
        if ($percentage >= 70) return 'C-';
        if ($percentage >= 67) return 'D+';
        if ($percentage >= 65) return 'D';
        if ($percentage >= 60) return 'D-';
        return 'F';
    }

    private function getRemarks($percentage)
    {
        if ($percentage >= 90) return 'Excellent work!';
        if ($percentage >= 80) return 'Good performance';
        if ($percentage >= 70) return 'Satisfactory';
        if ($percentage >= 60) return 'Needs improvement';
        return 'Below expectations';
    }
}
