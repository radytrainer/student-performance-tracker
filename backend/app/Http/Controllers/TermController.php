<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;

class TermController extends Controller
{
    // Get all terms
    public function index()
    {
        $terms = Term::all();

        return response()->json([
            'success' => true,
            'message' => 'All terms retrieved successfully',
            'data' => $terms,
        ]);
    }

    // Get a specific term by ID
    public function show($id)
    {
        $term = Term::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Term retrieved successfully',
            'data' => $term,
        ]);
    }

    // Optional: Get the current term
    public function current()
    {
        $term = Term::where('is_current', true)->first();

        return response()->json([
            'success' => true,
            'message' => 'Current term retrieved successfully',
            'data' => $term,
        ]);
    }
}
