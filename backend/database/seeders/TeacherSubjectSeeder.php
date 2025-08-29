<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all teachers and subjects
        $teachers = User::where('role', 'teacher')->get();
        $subjects = Subject::all();

        if ($teachers->isEmpty() || $subjects->isEmpty()) {
            $this->command->info('No teachers or subjects found. Please run UserSeeder and SubjectSeeder first.');
            return;
        }

        $assignments = [];
        $timestamp = now();

        // Assign subjects to teachers based on their likely expertise
        $subjectExpertise = [
            'Mathematics' => ['Mathematics', 'Physics', 'Statistics'],
            'English' => ['English Language', 'Literature', 'Creative Writing'],
            'Science' => ['Biology', 'Chemistry', 'Physics', 'Environmental Science'],
            'History' => ['History', 'Geography', 'Social Studies', 'Civics'],
            'Physical Education' => ['Physical Education', 'Health Education'],
            'Arts' => ['Art', 'Music', 'Drama'],
        ];

        foreach ($teachers as $teacher) {
            $teacherName = $teacher->first_name . ' ' . $teacher->last_name;
            $assignedSubjects = [];
            $primaryAssigned = false;

            // Determine teacher's expertise area based on their name or department
            $expertiseArea = $this->determineExpertiseArea($teacher);
            
            if (isset($subjectExpertise[$expertiseArea])) {
                $relevantSubjects = $subjects->whereIn('subject_name', $subjectExpertise[$expertiseArea]);
                
                foreach ($relevantSubjects as $subject) {
                    $assignments[] = [
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject->id,
                        'primary_subject' => !$primaryAssigned, // First subject is primary
                        'assigned_at' => $timestamp->copy()->subDays(rand(1, 30)),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                    
                    $assignedSubjects[] = $subject->subject_name;
                    $primaryAssigned = true;
                }
            }

            // If no specific expertise match, assign some random subjects
            if (empty($assignedSubjects)) {
                $randomSubjects = $subjects->random(min(2, $subjects->count()));
                
                foreach ($randomSubjects as $index => $subject) {
                    $assignments[] = [
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject->id,
                        'primary_subject' => $index === 0, // First subject is primary
                        'assigned_at' => $timestamp->copy()->subDays(rand(1, 30)),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                    
                    $assignedSubjects[] = $subject->subject_name;
                }
            }

            $this->command->info("Assigned subjects to {$teacherName}: " . implode(', ', $assignedSubjects));
        }

        // Insert all assignments
        if (!empty($assignments)) {
            DB::table('teacher_subjects')->insert($assignments);
            $this->command->info('Teacher-Subject assignments created successfully!');
            $this->command->info('Total assignments: ' . count($assignments));
        } else {
            $this->command->warn('No assignments were created.');
        }
    }

    /**
     * Determine expertise area based on teacher's profile
     */
    private function determineExpertiseArea(User $teacher): string
    {
        $name = strtolower($teacher->first_name . ' ' . $teacher->last_name);
        
        // Simple heuristic based on common teacher names/backgrounds
        if (str_contains($name, 'math') || str_contains($name, 'calc') || str_contains($name, 'algebra')) {
            return 'Mathematics';
        } elseif (str_contains($name, 'eng') || str_contains($name, 'lit') || str_contains($name, 'write')) {
            return 'English';
        } elseif (str_contains($name, 'sci') || str_contains($name, 'bio') || str_contains($name, 'chem') || str_contains($name, 'phys')) {
            return 'Science';
        } elseif (str_contains($name, 'hist') || str_contains($name, 'geo') || str_contains($name, 'social')) {
            return 'History';
        } elseif (str_contains($name, 'sport') || str_contains($name, 'gym') || str_contains($name, 'fit')) {
            return 'Physical Education';
        } elseif (str_contains($name, 'art') || str_contains($name, 'music') || str_contains($name, 'draw')) {
            return 'Arts';
        }

        // Default fallback - assign based on teacher index
        $teacherIndex = $teacher->id % 6;
        $areas = ['Mathematics', 'English', 'Science', 'History', 'Physical Education', 'Arts'];
        
        return $areas[$teacherIndex];
    }
}
