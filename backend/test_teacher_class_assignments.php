<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Classes;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Teacher-Class Assignment System Test ===\n";

try {
    // Test 1: Check if classes table has school_id column
    echo "\n1. Checking database structure...\n";
    $hasSchoolId = Schema::hasColumn('classes', 'school_id');
    $hasClassTeacherId = Schema::hasColumn('classes', 'class_teacher_id');
    
    echo "   - Classes table has school_id: " . ($hasSchoolId ? "✅ YES" : "❌ NO") . "\n";
    echo "   - Classes table has class_teacher_id: " . ($hasClassTeacherId ? "✅ YES" : "❌ NO") . "\n";
    
    // Test 2: Check current class assignments
    echo "\n2. Current class assignments:\n";
    $classes = Classes::with(['classTeacher.user'])->get();
    
    foreach ($classes as $class) {
        $teacherName = $class->classTeacher ? 
            $class->classTeacher->user->first_name . ' ' . $class->classTeacher->user->last_name : 
            'Not Assigned';
        echo "   - {$class->class_name}: {$teacherName}\n";
    }
    
    // Test 3: Teacher workload distribution
    echo "\n3. Teacher workload distribution:\n";
    $teachers = Teacher::with(['user', 'classes'])->whereHas('user', function($q) {
        $q->where('is_active', true)->where('role', 'teacher');
    })->get();
    
    foreach ($teachers as $teacher) {
        $classCount = $teacher->classes->count();
        $maxClasses = config('app.max_classes_per_teacher', 5);
        $workloadPercentage = round(($classCount / $maxClasses) * 100);
        
        echo "   - {$teacher->user->first_name} {$teacher->user->last_name}: ";
        echo "{$classCount}/{$maxClasses} classes ({$workloadPercentage}%)\n";
    }
    
    // Test 4: User model computed properties
    echo "\n4. Testing User model computed properties:\n";
    $teacherUsers = User::where('role', 'teacher')->where('is_active', true)->get();
    
    foreach ($teacherUsers as $user) {
        echo "   - {$user->first_name} {$user->last_name}: ";
        echo "classes_count = {$user->classes_count}, ";
        echo "total_students_count = {$user->total_students_count}\n";
    }
    
    // Test 5: Assignment statistics
    echo "\n5. Assignment statistics:\n";
    $totalClasses = Classes::count();
    $assignedClasses = Classes::whereNotNull('class_teacher_id')->count();
    $unassignedClasses = $totalClasses - $assignedClasses;
    $teachersWithClasses = Teacher::whereHas('classes')->count();
    $teachersWithoutClasses = Teacher::whereDoesntHave('classes')->count();
    
    echo "   - Total Classes: {$totalClasses}\n";
    echo "   - Assigned Classes: {$assignedClasses}\n";
    echo "   - Unassigned Classes: {$unassignedClasses}\n";
    echo "   - Teachers with Classes: {$teachersWithClasses}\n";
    echo "   - Teachers without Classes: {$teachersWithoutClasses}\n";
    
    if ($teachers->count() > 0) {
        $averageClassesPerTeacher = round($assignedClasses / $teachers->count(), 2);
        echo "   - Average Classes per Teacher: {$averageClassesPerTeacher}\n";
    }
    
    echo "\n✅ Teacher-Class Assignment System test completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
