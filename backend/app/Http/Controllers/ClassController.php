<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes;

class ClassController extends Controller
{
    public function index()
    {
        $classes = Classes::with(['classTeacher', 'students', 'classSubjects'])->get();
        return response()->json([
            'success' => true,
            'message' => 'All classes retrieved successfully',
            'data' => $classes
        ]);
    }
}
