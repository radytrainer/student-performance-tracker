<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Teacher;
use App\Models\User;
use App\Traits\SchoolIsolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class TeacherClassController extends Controller
{
    use SchoolIsolation;

    /**
     * Constructor to ensure only admins can access these methods
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Assign teacher to class as primary teacher
     */
    public function assignTeacherToClass(Request $request, Classes $class): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,user_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if teacher already has too many classes (configurable limit)
            $maxClassesPerTeacher = config('app.max_classes_per_teacher', 5);
            $currentClassCount = Classes::where('class_teacher_id', $request->teacher_id)->count();
            
            if ($currentClassCount >= $maxClassesPerTeacher) {
                return response()->json([
                    'success' => false,
                    'message' => "Teacher already assigned to maximum allowed classes ($maxClassesPerTeacher)"
                ], 422);
            }

            // Check if class already has a teacher assigned
            if ($class->class_teacher_id) {
                $currentTeacher = Teacher::with('user')->find($class->class_teacher_id);
                return response()->json([
                    'success' => false,
                    'message' => 'Class already has a teacher assigned: ' . 
                                ($currentTeacher ? $currentTeacher->user->first_name . ' ' . $currentTeacher->user->last_name : 'Unknown')
                ], 422);
            }

            $class->update([
                'class_teacher_id' => $request->teacher_id
            ]);

            $class->load(['classTeacher.user']);

            // Log the action
            Log::info('Admin assigned teacher to class', [
                'admin_id' => auth()->id(),
                'class_id' => $class->id,
                'teacher_id' => $request->teacher_id,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Teacher assigned successfully',
                'data' => [
                    'class' => $class,
                    'teacher' => $class->classTeacher
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning teacher to class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign teacher'
            ], 500);
        }
    }

    /**
     * Remove teacher from class
     */
    public function removeTeacherFromClass(Classes $class): JsonResponse
    {
        try {
            if (!$class->class_teacher_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No teacher assigned to this class'
                ], 422);
            }

            $previousTeacherId = $class->class_teacher_id;
            
            $class->update([
                'class_teacher_id' => null
            ]);

            // Log the action
            Log::info('Admin removed teacher from class', [
                'admin_id' => auth()->id(),
                'class_id' => $class->id,
                'previous_teacher_id' => $previousTeacherId,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Teacher removed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing teacher from class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove teacher'
            ], 500);
        }
    }

    /**
     * Get teacher's assigned classes
     */
    public function getTeacherClasses(Teacher $teacher, Request $request): JsonResponse
    {
        try {
            $includeSubjects = $request->get('include_subjects', false);
            $academicYear = $request->get('academic_year', '');

            $query = $teacher->classes()->with(['students']);

            if ($includeSubjects) {
                $query->with(['classSubjects.subject']);
            }

            if ($academicYear) {
                $query->where('academic_year', $academicYear);
            }

            $classes = $query->get();

            // Add computed fields
            $classes->transform(function ($class) {
                $class->student_count = $class->students->count();
                $class->subject_count = $class->classSubjects->count();
                return $class;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'teacher' => $teacher->load('user'),
                    'classes' => $classes,
                    'total_classes' => $classes->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching teacher classes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch teacher classes'
            ], 500);
        }
    }

    /**
     * Get class teachers (primary and subject teachers)
     */
    public function getClassTeachers(Classes $class): JsonResponse
    {
        try {
            $class->load([
                'classTeacher.user',
                'classSubjects.teacher.user',
                'classSubjects.subject'
            ]);

            $primaryTeacher = $class->classTeacher ? [
                'id' => $class->classTeacher->user_id,
                'name' => trim($class->classTeacher->user->first_name . ' ' . $class->classTeacher->user->last_name),
                'email' => $class->classTeacher->user->email,
                'type' => 'primary',
                'department' => $class->classTeacher->department
            ] : null;

            $subjectTeachers = $class->classSubjects->map(function ($classSubject) {
                if ($classSubject->teacher) {
                    return [
                        'id' => $classSubject->teacher->user_id,
                        'name' => trim($classSubject->teacher->user->first_name . ' ' . $classSubject->teacher->user->last_name),
                        'email' => $classSubject->teacher->user->email,
                        'type' => 'subject',
                        'subject' => $classSubject->subject->subject_name,
                        'department' => $classSubject->teacher->department
                    ];
                }
                return null;
            })->filter()->unique('id')->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'class' => $class->only(['id', 'class_name', 'academic_year', 'room_number']),
                    'primary_teacher' => $primaryTeacher,
                    'subject_teachers' => $subjectTeachers,
                    'total_teachers' => ($primaryTeacher ? 1 : 0) + $subjectTeachers->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching class teachers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch class teachers'
            ], 500);
        }
    }

    /**
     * Bulk assign classes to teacher
     */
    public function bulkAssignClassesToTeacher(Request $request, Teacher $teacher): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'class_ids' => 'required|array|min:1',
                'class_ids.*' => 'exists:classes,id',
                'replace_existing' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $classIds = $request->class_ids;
            $replaceExisting = $request->get('replace_existing', false);
            
            // Check class load limit
            $maxClassesPerTeacher = config('app.max_classes_per_teacher', 5);
            $currentClassCount = $teacher->classes()->count();
            $newClassCount = $replaceExisting ? count($classIds) : $currentClassCount + count($classIds);
            
            if ($newClassCount > $maxClassesPerTeacher) {
                return response()->json([
                    'success' => false,
                    'message' => "Assignment would exceed maximum classes per teacher ($maxClassesPerTeacher)"
                ], 422);
            }

            DB::beginTransaction();

            $assignedClasses = [];
            $errors = [];

            // If replacing, remove current assignments
            if ($replaceExisting) {
                $teacher->classes()->update(['class_teacher_id' => null]);
            }

            foreach ($classIds as $classId) {
                $class = Classes::find($classId);
                
                if (!$class) {
                    $errors[] = "Class with ID $classId not found";
                    continue;
                }

                // Check if class already has a teacher (unless we're replacing)
                if (!$replaceExisting && $class->class_teacher_id && $class->class_teacher_id != $teacher->user_id) {
                    $currentTeacher = Teacher::with('user')->find($class->class_teacher_id);
                    $errors[] = "Class '{$class->class_name}' already assigned to " . 
                               ($currentTeacher ? $currentTeacher->user->first_name . ' ' . $currentTeacher->user->last_name : 'another teacher');
                    continue;
                }

                $class->update(['class_teacher_id' => $teacher->user_id]);
                $assignedClasses[] = $class->only(['id', 'class_name', 'academic_year']);
            }

            DB::commit();

            // Log the action
            Log::info('Admin bulk assigned classes to teacher', [
                'admin_id' => auth()->id(),
                'teacher_id' => $teacher->user_id,
                'assigned_classes' => count($assignedClasses),
                'errors' => count($errors),
                'replace_existing' => $replaceExisting,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => count($assignedClasses) . ' classes assigned successfully',
                'data' => [
                    'teacher' => $teacher->load('user'),
                    'assigned_classes' => $assignedClasses,
                    'errors' => $errors,
                    'total_assigned' => count($assignedClasses)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk assigning classes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign classes'
            ], 500);
        }
    }

    /**
     * Get all class-teacher assignments with statistics
     */
    public function getAllClassAssignments(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $academicYear = $request->get('academic_year', '');
            $assignmentStatus = $request->get('assignment_status', ''); // 'assigned', 'unassigned', 'all'

            $query = Classes::with(['classTeacher.user', 'students']);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('class_name', 'like', "%{$search}%")
                      ->orWhereHas('classTeacher.user', function ($userQ) use ($search) {
                          $userQ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                      });
                });
            }

            // Apply academic year filter
            if ($academicYear) {
                $query->where('academic_year', $academicYear);
            }

            // Apply assignment status filter
            if ($assignmentStatus === 'assigned') {
                $query->whereNotNull('class_teacher_id');
            } elseif ($assignmentStatus === 'unassigned') {
                $query->whereNull('class_teacher_id');
            }

            $classes = $query->orderBy('class_name')->paginate($perPage);

            // Transform data
            $classes->getCollection()->transform(function ($class) {
                return [
                    'id' => $class->id,
                    'class_name' => $class->class_name,
                    'academic_year' => $class->academic_year,
                    'room_number' => $class->room_number,
                    'student_count' => $class->students->count(),
                    'teacher_assigned' => $class->class_teacher_id ? true : false,
                    'teacher' => $class->classTeacher ? [
                        'id' => $class->classTeacher->user_id,
                        'name' => trim($class->classTeacher->user->first_name . ' ' . $class->classTeacher->user->last_name),
                        'email' => $class->classTeacher->user->email,
                        'department' => $class->classTeacher->department
                    ] : null,
                    'created_at' => $class->created_at,
                    'updated_at' => $class->updated_at
                ];
            });

            // Get assignment statistics
            $stats = [
                'total_classes' => Classes::count(),
                'assigned_classes' => Classes::whereNotNull('class_teacher_id')->count(),
                'unassigned_classes' => Classes::whereNull('class_teacher_id')->count(),
                'teachers_with_classes' => Teacher::whereHas('classes')->count(),
                'teachers_without_classes' => Teacher::whereDoesntHave('classes')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'classes' => $classes,
                    'statistics' => $stats
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching class assignments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch class assignments'
            ], 500);
        }
    }

    /**
     * Get teacher workload statistics
     */
    public function getTeacherWorkloadStats(): JsonResponse
    {
        try {
            $teachers = Teacher::with(['user', 'classes.students'])
                ->whereHas('user', function ($query) {
                    $query->where('is_active', true);
                })
                ->get();

            $workloadStats = $teachers->map(function ($teacher) {
                $classCount = $teacher->classes->count();
                $totalStudents = $teacher->classes->sum(function ($class) {
                    return $class->students->count();
                });

                return [
                    'teacher_id' => $teacher->user_id,
                    'teacher_name' => trim($teacher->user->first_name . ' ' . $teacher->user->last_name),
                    'department' => $teacher->department,
                    'class_count' => $classCount,
                    'total_students' => $totalStudents,
                    'workload_level' => $this->calculateWorkloadLevel($classCount),
                    'classes' => $teacher->classes->map(function ($class) {
                        return [
                            'id' => $class->id,
                            'name' => $class->class_name,
                            'student_count' => $class->students->count()
                        ];
                    })
                ];
            });

            // Sort by workload (highest first)
            $workloadStats = $workloadStats->sortByDesc('class_count')->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'teacher_workloads' => $workloadStats,
                    'summary' => [
                        'total_teachers' => $teachers->count(),
                        'overloaded_teachers' => $workloadStats->where('workload_level', 'overloaded')->count(),
                        'balanced_teachers' => $workloadStats->where('workload_level', 'balanced')->count(),
                        'underloaded_teachers' => $workloadStats->where('workload_level', 'underloaded')->count(),
                        'average_classes_per_teacher' => round($workloadStats->avg('class_count'), 2)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching teacher workload stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch workload statistics'
            ], 500);
        }
    }

    /**
     * Calculate workload level based on class count
     */
    private function calculateWorkloadLevel(int $classCount): string
    {
        $maxClasses = config('app.max_classes_per_teacher', 5);
        $balancedThreshold = ceil($maxClasses * 0.7); // 70% of max is considered balanced

        if ($classCount >= $maxClasses) {
            return 'overloaded';
        } elseif ($classCount >= $balancedThreshold) {
            return 'balanced';
        } else {
            return 'underloaded';
        }
    }
}
