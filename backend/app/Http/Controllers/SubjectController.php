<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::all();
        return response()->json([
            'success' => true,
            'message' => 'Subjects retrieved successfully',
            'data' => $subjects
        ]);
    }
}

