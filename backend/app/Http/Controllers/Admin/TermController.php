<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class TermController extends Controller
{
    /**
     * Constructor to ensure only admins can access these methods
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Display a listing of all terms
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $academicYear = $request->get('academic_year', '');

            $query = Term::query();

            if ($academicYear) {
                $query->where('academic_year', $academicYear);
            }

            $terms = $query->orderBy('start_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $terms
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching terms: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch terms'
            ], 500);
        }
    }

    /**
     * Store a newly created term
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'term_name' => 'required|string|max:255',
                'academic_year' => 'required|string|max:10',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'is_current' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // If setting as current term, update other terms to not current
            if ($request->boolean('is_current')) {
                Term::where('is_current', true)->update(['is_current' => false]);
            }

            $term = Term::create([
                'term_name' => $request->term_name,
                'academic_year' => $request->academic_year,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_current' => $request->boolean('is_current', false),
                'status' => 'active'
            ]);

            DB::commit();

            Log::info('Admin created new term', [
                'admin_id' => auth()->id(),
                'term_id' => $term->id,
                'term_name' => $term->term_name,
                'academic_year' => $term->academic_year,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Term created successfully',
                'data' => $term
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating term: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create term'
            ], 500);
        }
    }

    /**
     * Update the specified term
     */
    public function update(Request $request, Term $term): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'term_name' => 'required|string|max:255',
                'academic_year' => 'required|string|max:10',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'is_current' => 'boolean',
                'status' => 'in:active,inactive,completed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // If setting as current term, update other terms to not current
            if ($request->boolean('is_current') && !$term->is_current) {
                Term::where('is_current', true)->update(['is_current' => false]);
            }

            $term->update([
                'term_name' => $request->term_name,
                'academic_year' => $request->academic_year,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_current' => $request->boolean('is_current', $term->is_current),
                'status' => $request->status ?? $term->status
            ]);

            DB::commit();

            Log::info('Admin updated term', [
                'admin_id' => auth()->id(),
                'term_id' => $term->id,
                'term_name' => $term->term_name,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Term updated successfully',
                'data' => $term
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating term: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update term'
            ], 500);
        }
    }

    /**
     * Remove the specified term
     */
    public function destroy(Term $term): JsonResponse
    {
        try {
            // Check if term has grades
            if ($term->grades()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete term with existing grades'
                ], 422);
            }

            $termName = $term->term_name;
            $termId = $term->id;

            $term->delete();

            Log::info('Admin deleted term', [
                'admin_id' => auth()->id(),
                'deleted_term_id' => $termId,
                'deleted_term_name' => $termName,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Term deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting term: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete term'
            ], 500);
        }
    }

    /**
     * Set current term
     */
    public function setCurrent(Term $term): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Update all terms to not current
            Term::where('is_current', true)->update(['is_current' => false]);
            
            // Set this term as current
            $term->update(['is_current' => true]);

            DB::commit();

            Log::info('Admin set current term', [
                'admin_id' => auth()->id(),
                'term_id' => $term->id,
                'term_name' => $term->term_name,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Current term updated successfully',
                'data' => $term
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting current term: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to set current term'
            ], 500);
        }
    }

    /**
     * Get current term
     */
    public function getCurrent(): JsonResponse
    {
        try {
            $currentTerm = Term::where('is_current', true)->first();

            if (!$currentTerm) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current term set'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $currentTerm
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching current term: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch current term'
            ], 500);
        }
    }
}
