<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    /**
     * Constructor to ensure only admins can access these methods
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Display a listing of all subjects
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $department = $request->get('department', '');
            $isCore = $request->get('is_core', '');

            $query = Subject::with(['classSubjects.class']);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('subject_name', 'like', "%{$search}%")
                      ->orWhere('subject_code', 'like', "%{$search}%")
                      ->orWhere('department', 'like', "%{$search}%");
                });
            }

            // Apply department filter
            if ($department) {
                $query->where('department', $department);
            }

            // Apply core/elective filter
            if ($isCore !== '') {
                $query->where('is_core', $isCore === '1');
            }

            // Sorting
            $sortBy = strtolower($request->get('sort_by', 'subject_name'));
            $sortDir = strtolower($request->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
            $allowed = ['subject_name', 'subject_code', 'department', 'credit_hours', 'created_at'];
            if (!in_array($sortBy, $allowed, true)) { $sortBy = 'subject_name'; }
            $subjects = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

            // Add computed fields
            $subjects->getCollection()->transform(function ($subject) {
                $subject->class_count = $subject->classSubjects->count();
                $subject->unique_classes = $subject->classSubjects->pluck('class.class_name')->unique()->values();
                return $subject;
            });

            return response()->json([
                'success' => true,
                'data' => $subjects
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching subjects: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subjects'
            ], 500);
        }
    }

    /**
     * Store a newly created subject
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject_name' => 'required|string|max:255|unique:subjects,subject_name',
                'subject_code' => 'required|string|max:20|unique:subjects,subject_code',
                'description' => 'nullable|string',
                'credit_hours' => 'required|integer|min:1|max:10',
                'department' => 'required|string|max:100',
                'is_core' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $subject = Subject::create([
                'subject_name' => $request->subject_name,
                'subject_code' => $request->subject_code,
                'description' => $request->description,
                'credit_hours' => $request->credit_hours,
                'department' => $request->department,
                'is_core' => $request->is_core
            ]);

            // Log the action
            Log::info('Admin created new subject', [
                'admin_id' => auth()->id(),
                'subject_id' => $subject->id,
                'subject_code' => $subject->subject_code,
                'subject_name' => $subject->subject_name,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subject created successfully',
                'data' => $subject
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subject'
            ], 500);
        }
    }

    /**
     * Display the specified subject
     */
    public function show(Subject $subject): JsonResponse
    {
        try {
            $subject->load([
                'classSubjects.class',
                'classSubjects.class.students',
                'classSubjects.teacher.user',
                'grades' => function ($query) {
                    $query->with('student.user');
                }
            ]);

            // Add computed fields
            $subject->class_count = $subject->classSubjects->count();
            $subject->total_students = $subject->classSubjects->sum(function ($classSubject) {
                return $classSubject->class->students->count();
            });
            $subject->average_grade = $this->calculateAverageGrade($subject);

            return response()->json([
                'success' => true,
                'data' => $subject
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching subject details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subject details'
            ], 500);
        }
    }

    /**
     * Update the specified subject
     */
    public function update(Request $request, Subject $subject): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject_name' => 'required|string|max:255|unique:subjects,subject_name,' . $subject->id,
                'subject_code' => 'required|string|max:20|unique:subjects,subject_code,' . $subject->id,
                'description' => 'nullable|string',
                'credit_hours' => 'required|integer|min:1|max:10',
                'department' => 'required|string|max:100',
                'is_core' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $subject->update([
                'subject_name' => $request->subject_name,
                'subject_code' => $request->subject_code,
                'description' => $request->description,
                'credit_hours' => $request->credit_hours,
                'department' => $request->department,
                'is_core' => $request->is_core
            ]);

            // Log the action
            Log::info('Admin updated subject', [
                'admin_id' => auth()->id(),
                'subject_id' => $subject->id,
                'subject_code' => $subject->subject_code,
                'subject_name' => $subject->subject_name,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subject updated successfully',
                'data' => $subject
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subject'
            ], 500);
        }
    }

    /**
     * Remove the specified subject
     */
    public function destroy(Subject $subject): JsonResponse
    {
        try {
            // Check if subject has class assignments
            if ($subject->classSubjects()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete subject that is assigned to classes. Please remove class assignments first.'
                ], 422);
            }

            $subjectName = $subject->subject_name;
            $subjectCode = $subject->subject_code;
            $subjectId = $subject->id;

            $subject->delete();

            // Log the action
            Log::info('Admin deleted subject', [
                'admin_id' => auth()->id(),
                'deleted_subject_id' => $subjectId,
                'deleted_subject_code' => $subjectCode,
                'deleted_subject_name' => $subjectName,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subject deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subject'
            ], 500);
        }
    }

    /**
     * Get available departments
     */
    public function getDepartments(): JsonResponse
    {
        try {
            $departments = Subject::select('department')
                ->distinct()
                ->orderBy('department')
                ->pluck('department')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $departments
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching departments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch departments'
            ], 500);
        }
    }

    /**
     * Get subject statistics
     */
    public function getSubjectStats(): JsonResponse
    {
        try {
            $stats = [
                'total_subjects' => Subject::count(),
                'core_subjects' => Subject::where('is_core', true)->count(),
                'elective_subjects' => Subject::where('is_core', false)->count(),
                'subjects_by_department' => Subject::select('department', DB::raw('count(*) as count'))
                    ->groupBy('department')
                    ->orderBy('count', 'desc')
                    ->get(),
                'average_credit_hours' => round(Subject::avg('credit_hours'), 1),
                'total_credit_hours' => Subject::sum('credit_hours'),
                'subjects_in_use' => Subject::whereHas('classSubjects')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching subject statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics'
            ], 500);
        }
    }

    /**
     * Bulk operations for subjects
     */
    public function bulkAction(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:update_department,toggle_core_status,delete',
                'subject_ids' => 'required|array|min:1',
                'subject_ids.*' => 'exists:subjects,id',
                'department' => 'required_if:action,update_department|string|max:100',
                'is_core' => 'required_if:action,toggle_core_status|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $action = $request->action;
            $subjectIds = $request->subject_ids;
            $affectedCount = 0;

            DB::beginTransaction();

            switch ($action) {
                case 'update_department':
                    $affectedCount = Subject::whereIn('id', $subjectIds)
                        ->update(['department' => $request->department]);
                    break;
                    
                case 'toggle_core_status':
                    $affectedCount = Subject::whereIn('id', $subjectIds)
                        ->update(['is_core' => $request->is_core]);
                    break;
                    
                case 'delete':
                    // Check if any subjects have class assignments
                    $subjectsWithClasses = Subject::whereIn('id', $subjectIds)
                        ->whereHas('classSubjects')
                        ->count();
                        
                    if ($subjectsWithClasses > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot delete subjects that are assigned to classes'
                        ], 422);
                    }
                    
                    $affectedCount = Subject::whereIn('id', $subjectIds)->count();
                    Subject::whereIn('id', $subjectIds)->delete();
                    break;
            }

            DB::commit();

            // Log the action
            Log::info('Admin performed bulk action on subjects', [
                'admin_id' => auth()->id(),
                'action' => $action,
                'subject_ids' => $subjectIds,
                'affected_count' => $affectedCount,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Bulk {$action} completed successfully",
                'data' => [
                    'affected_count' => $affectedCount,
                    'action' => $action
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error performing bulk action: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action'
            ], 500);
        }
    }

    /**
     * Calculate average grade for a subject
     */
    private function calculateAverageGrade(Subject $subject): float
    {
        $grades = $subject->grades()->get();
        
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalPoints = 0;
        $gradeCount = 0;

        foreach ($grades as $grade) {
            $points = $this->gradeToPoints($grade->grade_letter);
            $totalPoints += $points;
            $gradeCount++;
        }

        return $gradeCount > 0 ? round($totalPoints / $gradeCount, 2) : 0.0;
    }

    /**
     * Convert grade letter to GPA points
     */
    private function gradeToPoints($gradeLetter): float
    {
        $gradePoints = [
            'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
            'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
            'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D' => 1.0, 'F' => 0.0
        ];

        return $gradePoints[$gradeLetter] ?? 0.0;
    }
}
