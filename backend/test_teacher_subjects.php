<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Subject;

echo "=== Teacher-Subject Assignment System Test ===\n\n";

// Test 1: Get all teachers with their subjects
echo "1. Testing Teachers with Subjects:\n";
$teachers = User::with(['subjects' => function($query) {
    $query->withPivot('primary_subject', 'assigned_at');
}])->where('role', 'teacher')->get();

foreach ($teachers as $teacher) {
    echo "Teacher: {$teacher->first_name} {$teacher->last_name}\n";
    echo "  Subjects: " . $teacher->subjects->count() . "\n";
    
    foreach ($teacher->subjects as $subject) {
        $primary = $subject->pivot->primary_subject ? ' (PRIMARY)' : '';
        echo "  - {$subject->subject_name}{$primary}\n";
    }
    echo "\n";
}

// Test 2: Get subjects with their teachers
echo "\n2. Testing Subjects with Teachers:\n";
$subjects = Subject::with(['teachers' => function($query) {
    $query->withPivot('primary_subject', 'assigned_at');
}])->get();

foreach ($subjects as $subject) {
    if ($subject->teachers->count() > 0) {
        echo "Subject: {$subject->subject_name}\n";
        echo "  Teachers: " . $subject->teachers->count() . "\n";
        
        foreach ($subject->teachers as $teacher) {
            $primary = $teacher->pivot->primary_subject ? ' (PRIMARY)' : '';
            echo "  - {$teacher->first_name} {$teacher->last_name}{$primary}\n";
        }
        echo "\n";
    }
}

echo "\n=== Test Complete ===\n";
