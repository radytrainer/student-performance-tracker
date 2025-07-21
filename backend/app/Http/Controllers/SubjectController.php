<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    public function index(): JsonResponse
    {
        $subjects = Subject::select('id', 'subject_name')->get();

        return response()->json([
            'success' => true,
            'data' => $subjects
        ]);
    }
}
