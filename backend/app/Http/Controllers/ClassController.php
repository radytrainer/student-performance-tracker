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
        $query = Classes::with(['classTeacher', 'students', 'classSubjects']);

        // Apply school isolation for authenticated users (non-super-admin)
        if (auth()->check()) {
            $query = $this->applyClassSchoolIsolation($query);
        }

        $classes = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'All classes retrieved successfully',
            'data' => $classes
        ]);
    }
}
