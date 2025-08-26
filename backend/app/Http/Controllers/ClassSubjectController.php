<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSubject;

class ClassSubjectController extends Controller
{
    public function myClassSubjects(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['data' => []]);
        }

        // Teachers should see their assignments; fall back to user id if relation missing
        if ($user->isTeacher()) {
            $teacherId = optional($user->teacher)->user_id ?? $user->id;
            $classSubjects = ClassSubject::with(['class', 'subject'])
                ->where('teacher_id', $teacherId)
                ->orderBy('class_id')
                ->orderBy('subject_id')
                ->get();
            return response()->json(['data' => $classSubjects]);
        }

        // Admins can see all
        if ($user->isAdmin()) {
            $classSubjects = ClassSubject::with(['class', 'subject'])->get();
            return response()->json(['data' => $classSubjects]);
        }

        // Other roles: none
        return response()->json(['data' => []]);
    }
}
