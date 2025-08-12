<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicSchoolController extends Controller
{
    /**
     * Get list of active schools for public registration
     */
    public function index(): JsonResponse
    {
        $schools = School::where('status', 'active')
            ->select('id', 'name', 'code', 'description')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schools
        ]);
    }
}
