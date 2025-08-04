<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ClassController extends Controller
{
    /**
     * Constructor to ensure only admins can access these methods
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Display a listing of all classes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $academicYear = $request->get('academic_year', '');

            $query = Classes::with(['classTeacher.user', 'students', 'classSubjects.subject']);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('class_name', 'like', "%{$search}%")
                      ->orWhere('room_number', 'like', "%{$search}%");
                });
            }

            // Apply academic year filter
            if ($academicYear) {
                $query->where('academic_year', $academicYear);
            }

            $classes = $query->orderBy('class_name')->paginate($perPage);

            // Add computed fields
            $classes->getCollection()->transform(function ($class) {
                $class->student_count = $class->students->count();
                $class->subject_count = $class->classSubjects->count();
                $class->class_teacher_name = $class->classTeacher ? 
                    trim($class->classTeacher->user->first_name . ' ' . $class->classTeacher->user->last_name) : 
                    'Not Assigned';
                return $class;
            });

            return response()->json([
                'success' => true,
                'data' => $classes
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching classes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch classes'
            ], 500);
        }
    }

    /**
     * Store a newly created class
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'class_name' => 'required|string|max:255|unique:classes,class_name',
                'academic_year' => 'required|string|max:10',
                'class_teacher_id' => 'nullable|exists:teachers,user_id',
                'room_number' => 'nullable|string|max:50',
                'schedule' => 'nullable|string',
                'subjects' => 'array',
                'subjects.*' => 'exists:subjects,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create class
            $class = Classes::create([
                'class_name' => $request->class_name,
                'academic_year' => $request->academic_year,
                'class_teacher_id' => $request->class_teacher_id,
                'room_number' => $request->room_number,
                'schedule' => $request->schedule
            ]);

            // Assign subjects to class if provided
            if ($request->has('subjects') && is_array($request->subjects)) {
                foreach ($request->subjects as $subjectId) {
                    ClassSubject::create([
                        'class_id' => $class->id,
                        'subject_id' => $subjectId,
                        'teacher_id' => $request->class_teacher_id // Default to class teacher
                    ]);
                }
            }

            $class->load(['classTeacher.user', 'classSubjects.subject']);

            DB::commit();

            // Log the action
            Log::info('Admin created new class', [
                'admin_id' => auth()->id(),
                'class_id' => $class->id,
                'class_name' => $class->class_name,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Class created successfully',
                'data' => $class
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create class'
            ], 500);
        }
    }

    /**
     * Display the specified class
     */
    public function show(Classes $class): JsonResponse
    {
        try {
            $class->load([
                'classTeacher.user',
                'students.user',
                'classSubjects.subject',
                'classSubjects.teacher.user'
            ]);

            // Add computed fields
            $class->student_count = $class->students->count();
            $class->subject_count = $class->classSubjects->count();
            $class->class_teacher_name = $class->classTeacher ? 
                trim($class->classTeacher->user->first_name . ' ' . $class->classTeacher->user->last_name) : 
                'Not Assigned';

            return response()->json([
                'success' => true,
                'data' => $class
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching class details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch class details'
            ], 500);
        }
    }

    /**
     * Update the specified class
     */
    public function update(Request $request, Classes $class): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'class_name' => 'required|string|max:255|unique:classes,class_name,' . $class->id,
                'academic_year' => 'required|string|max:10',
                'class_teacher_id' => 'nullable|exists:teachers,user_id',
                'room_number' => 'nullable|string|max:50',
                'schedule' => 'nullable|string',
                'subjects' => 'array',
                'subjects.*' => 'exists:subjects,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update class
            $class->update([
                'class_name' => $request->class_name,
                'academic_year' => $request->academic_year,
                'class_teacher_id' => $request->class_teacher_id,
                'room_number' => $request->room_number,
                'schedule' => $request->schedule
            ]);

            // Update subject assignments if provided
            if ($request->has('subjects')) {
                // Remove existing subject assignments
                $class->classSubjects()->delete();
                
                // Add new subject assignments
                if (is_array($request->subjects)) {
                    foreach ($request->subjects as $subjectId) {
                        ClassSubject::create([
                            'class_id' => $class->id,
                            'subject_id' => $subjectId,
                            'teacher_id' => $request->class_teacher_id
                        ]);
                    }
                }
            }

            $class->load(['classTeacher.user', 'classSubjects.subject']);

            DB::commit();

            // Log the action
            Log::info('Admin updated class', [
                'admin_id' => auth()->id(),
                'class_id' => $class->id,
                'class_name' => $class->class_name,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Class updated successfully',
                'data' => $class
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update class'
            ], 500);
        }
    }

    /**
     * Remove the specified class
     */
    public function destroy(Classes $class): JsonResponse
    {
        try {
            // Check if class has students
            if ($class->students()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete class with enrolled students. Please transfer students first.'
                ], 422);
            }

            DB::beginTransaction();

            $className = $class->class_name;
            $classId = $class->id;

            // Delete class subjects first
            $class->classSubjects()->delete();
            
            // Delete class
            $class->delete();

            DB::commit();

            // Log the action
            Log::info('Admin deleted class', [
                'admin_id' => auth()->id(),
                'deleted_class_id' => $classId,
                'deleted_class_name' => $className,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Class deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete class'
            ], 500);
        }
    }

    /**
     * Assign teacher to class subject
     */
    public function assignTeacher(Request $request, Classes $class): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject_id' => 'required|exists:subjects,id',
                'teacher_id' => 'required|exists:teachers,user_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $classSubject = ClassSubject::where('class_id', $class->id)
                ->where('subject_id', $request->subject_id)
                ->first();

            if (!$classSubject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not assigned to this class'
                ], 404);
            }

            $classSubject->update([
                'teacher_id' => $request->teacher_id
            ]);

            $classSubject->load(['subject', 'teacher.user']);

            // Log the action
            Log::info('Admin assigned teacher to class subject', [
                'admin_id' => auth()->id(),
                'class_id' => $class->id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Teacher assigned successfully',
                'data' => $classSubject
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning teacher: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign teacher'
            ], 500);
        }
    }

    /**
     * Get available teachers for assignment
     */
    public function getAvailableTeachers(): JsonResponse
    {
        try {
            $teachers = Teacher::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('is_active', true);
                })
                ->get()
                ->map(function ($teacher) {
                    return [
                        'id' => $teacher->user_id,
                        'name' => trim($teacher->user->first_name . ' ' . $teacher->user->last_name),
                        'email' => $teacher->user->email,
                        'department' => $teacher->department,
                        'specialization' => $teacher->specialization
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $teachers
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching teachers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch teachers'
            ], 500);
        }
    }

    /**
     * Get class statistics
     */
    public function getClassStats(): JsonResponse
    {
        try {
            $stats = [
                'total_classes' => Classes::count(),
                'classes_by_year' => Classes::select('academic_year', DB::raw('count(*) as count'))
                    ->groupBy('academic_year')
                    ->orderBy('academic_year', 'desc')
                    ->get(),
                'classes_with_teachers' => Classes::whereNotNull('class_teacher_id')->count(),
                'total_students_enrolled' => DB::table('students')->count(),
                'average_class_size' => round(DB::table('students')->count() / max(Classes::count(), 1), 1)
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching class statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics'
            ], 500);
        }
    }
}
