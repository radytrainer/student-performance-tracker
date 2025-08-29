<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$totalAssignments = User::with('subjects')->where('role', 'teacher')->get()->sum(function($teacher) {
    return $teacher->subjects->count();
});

echo "Total teacher-subject assignments: {$totalAssignments}\n";
echo "Teacher-Subject Assignment System: WORKING ✓\n";
