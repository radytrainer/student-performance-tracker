<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Classes;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TeacherClassAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Assigning teachers to classes...');

        // Get all active teachers
        $teachers = Teacher::with('user')
            ->whereHas('user', function ($query) {
                $query->where('is_active', true)->where('role', 'teacher');
            })
            ->get();

        // Get all classes that don't have assigned teachers
        $unassignedClasses = Classes::whereNull('class_teacher_id')->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No active teachers found. Skipping class assignments.');
            return;
        }

        if ($unassignedClasses->isEmpty()) {
            $this->command->info('All classes already have assigned teachers.');
            return;
        }

        $maxClassesPerTeacher = config('app.max_classes_per_teacher', 5);
        $assignmentCount = 0;
        $teacherIndex = 0;

        foreach ($unassignedClasses as $class) {
            // Find a teacher with available capacity
            $assigned = false;
            $attempts = 0;
            $maxAttempts = $teachers->count();

            while (!$assigned && $attempts < $maxAttempts) {
                $teacher = $teachers->get($teacherIndex % $teachers->count());
                
                // Check current class count for this teacher
                $currentClassCount = Classes::where('class_teacher_id', $teacher->user_id)->count();

                if ($currentClassCount < $maxClassesPerTeacher) {
                    // Assign teacher to class
                    $class->update([
                        'class_teacher_id' => $teacher->user_id
                    ]);

                    $this->command->info("Assigned {$teacher->user->first_name} {$teacher->user->last_name} to class: {$class->class_name}");
                    
                    $assignmentCount++;
                    $assigned = true;
                }

                $teacherIndex++;
                $attempts++;
            }

            if (!$assigned) {
                $this->command->warn("Could not assign teacher to class: {$class->class_name} (all teachers at capacity)");
            }
        }

        $this->command->info("Successfully assigned $assignmentCount classes to teachers.");
        
        // Display workload distribution
        $this->displayWorkloadDistribution();
    }

    /**
     * Display teacher workload distribution
     */
    private function displayWorkloadDistribution(): void
    {
        $this->command->info("\n--- Teacher Workload Distribution ---");
        
        $teachers = Teacher::with(['user', 'classes'])
            ->whereHas('user', function ($query) {
                $query->where('is_active', true)->where('role', 'teacher');
            })
            ->get();

        foreach ($teachers as $teacher) {
            $classCount = $teacher->classes->count();
            $classNames = $teacher->classes->pluck('class_name')->join(', ');
            
            $this->command->info(sprintf(
                "%s %s: %d classes [%s]",
                $teacher->user->first_name,
                $teacher->user->last_name,
                $classCount,
                $classNames ?: 'None'
            ));
        }

        // Summary statistics
        $totalClasses = Classes::count();
        $assignedClasses = Classes::whereNotNull('class_teacher_id')->count();
        $unassignedClasses = $totalClasses - $assignedClasses;

        $this->command->info("\n--- Summary ---");
        $this->command->info("Total Classes: $totalClasses");
        $this->command->info("Assigned Classes: $assignedClasses");
        $this->command->info("Unassigned Classes: $unassignedClasses");
        
        if ($teachers->count() > 0) {
            $averageClassesPerTeacher = round($assignedClasses / $teachers->count(), 2);
            $this->command->info("Average Classes per Teacher: $averageClassesPerTeacher");
        }
    }
}
