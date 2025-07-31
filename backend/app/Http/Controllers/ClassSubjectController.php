<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSubject;

class ClassSubjectController extends Controller
{
    public function myClassSubjects(Request $request)
    {
        $user = $request->user(); // get authenticated user

        if (!$user || !$user->isTeacher()) {
            // Return empty data if user not teacher or not authenticated
            return response()->json(['data' => []]);
        }

        $classSubjects = ClassSubject::with(['class', 'subject'])
            ->where('teacher_id', $user->teacher->user_id)
            ->get();

        return response()->json(['data' => $classSubjects]);
    }
}
