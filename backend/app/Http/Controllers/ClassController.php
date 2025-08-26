<?php

namespace App\Http\Controllers;

use App\Traits\SchoolIsolation;

use Illuminate\Http\Request;
use App\Models\Classes;

class ClassController extends Controller
{
    use SchoolIsolation;

    public function index()
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $query = Classes::with(['classTeacher', 'students', 'classSubjects.subject', 'classSubjects.teacher']);

        // Apply school isolation for authenticated users (non-super-admin)
        $query = $this->applyClassSchoolIsolation($query);

        // Filter classes based on user role
        if ($user->role === 'teacher') {
            // Teachers should only see classes they're assigned to
            $teacherId = $user->id;
            $query->where(function ($q) use ($teacherId) {
                // Classes where the user is the class teacher
                $q->where('class_teacher_id', $teacherId)
                  // OR classes where they teach a subject
                  ->orWhereHas('classSubjects', function ($sq) use ($teacherId) {
                      $sq->where('teacher_id', $teacherId);
                  });
            });
        } elseif ($user->role === 'admin') {
            // Admins see all classes in their school
            // Already handled by school isolation
        }
        // Students and other roles will get empty results

        $classes = $query->orderBy('class_name')->get();

        // Add computed fields for frontend
        foreach ($classes as $class) {
            $class->student_count = $class->students->count();
            $class->subjects = $class->classSubjects->map(function ($cs) {
                return $cs->subject?->subject_name ?? 'Unknown Subject';
            })->filter()->values();
        }

        return response()->json([
            'success' => true,
            'message' => 'Classes retrieved successfully',
            'data' => $classes
        ]);
    }
}
