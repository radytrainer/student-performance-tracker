<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    /**
     * Constructor to ensure only admins can access these methods
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Display a listing of all students
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $classFilter = $request->get('class_id', '');
            $statusFilter = $request->get('status', '');

            $query = Student::with(['user', 'currentClass', 'grades'])
                ->join('users', 'students.user_id', '=', 'users.id');

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('users.first_name', 'like', "%{$search}%")
                      ->orWhere('users.last_name', 'like', "%{$search}%")
                      ->orWhere('users.email', 'like', "%{$search}%")
                      ->orWhere('students.student_code', 'like', "%{$search}%");
                });
            }

            // Apply class filter
            if ($classFilter) {
                $query->where('students.current_class_id', $classFilter);
            }

            // Apply status filter
            if ($statusFilter === 'active') {
                $query->where('users.is_active', true);
            } elseif ($statusFilter === 'inactive') {
                $query->where('users.is_active', false);
            }

            // Sorting (with optional GPA/Attendance derived columns)
            $sortBy = strtolower($request->get('sort_by', 'name'));
            $sortDir = strtolower($request->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

            // Subqueries for GPA and Attendance (last 30 days) to support sorting
            $gpaSub = DB::table('grades')
                ->selectRaw("student_id, AVG(CASE grade_letter \
                    WHEN 'A+' THEN 4.0 WHEN 'A' THEN 4.0 WHEN 'A-' THEN 3.7 \
                    WHEN 'B+' THEN 3.3 WHEN 'B' THEN 3.0 WHEN 'B-' THEN 2.7 \
                    WHEN 'C+' THEN 2.3 WHEN 'C' THEN 2.0 WHEN 'C-' THEN 1.7 \
                    WHEN 'D+' THEN 1.3 WHEN 'D' THEN 1.0 WHEN 'F' THEN 0.0 ELSE 0 END) as gpa_calc")
                ->groupBy('student_id');

            $attendanceSub = DB::table('attendances')
                ->selectRaw("student_id, (SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0)) * 100 as att_pct")
                ->where('date', '>=', now()->subDays(30))
                ->groupBy('student_id');

            $query->leftJoinSub($gpaSub, 'gpa', function($join){
                $join->on('gpa.student_id', '=', 'students.user_id');
            });
            $query->leftJoinSub($attendanceSub, 'att', function($join){
                $join->on('att.student_id', '=', 'students.user_id');
            });

            $sortMap = [
                'name' => ['users.first_name', 'users.last_name'],
                'email' => ['users.email'],
                'student_code' => ['students.student_code'],
                'created_at' => ['users.created_at'],
                'gpa' => ['gpa_calc'],
                'attendance' => ['att_pct'],
            ];
            $orderColumns = $sortMap[$sortBy] ?? $sortMap['name'];

            $students = $query->select('students.*', DB::raw('gpa_calc'), DB::raw('att_pct'));
            foreach ($orderColumns as $col) {
                $students = $students->orderBy($col, $sortDir);
            }
            $students = $students->paginate($perPage);

            // Add computed fields
            $students->getCollection()->transform(function ($student) {
                $student->full_name = trim($student->user->first_name . ' ' . $student->user->last_name);
                $student->current_gpa = $this->calculateGPA($student);
                $student->attendance_rate = $this->calculateAttendanceRate($student);
                return $student;
            });

            return response()->json([
                'success' => true,
                'data' => $students
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students'
            ], 500);
        }
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'student_code' => 'required|string|unique:students,student_code',
                'date_of_birth' => 'required|date',
                'gender' => 'required|in:male,female,other',
                'address' => 'nullable|string',
                'parent_name' => 'required|string|max:255',
                'parent_phone' => 'required|string|max:20',
                'current_class_id' => 'required|exists:classes,id',
                'enrollment_date' => 'required|date',
                'password' => 'required|string|min:8|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create user account
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'username' => $request->email,
                'password_hash' => Hash::make($request->password),
                'role' => 'student',
                'is_active' => true
            ]);

            // Create student profile
            $student = Student::create([
                'user_id' => $user->id,
                'student_code' => $request->student_code,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'parent_name' => $request->parent_name,
                'parent_phone' => $request->parent_phone,
                'current_class_id' => $request->current_class_id,
                'enrollment_date' => $request->enrollment_date
            ]);

            // Load relationships for response
            $student->load(['user', 'currentClass']);

            DB::commit();

            // Log the action
            Log::info('Admin created new student', [
                'admin_id' => auth()->id(),
                'student_id' => $student->user_id,
                'student_code' => $student->student_code,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'data' => $student
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating student: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student'
            ], 500);
        }
    }

    /**
     * Display the specified student
     */
    public function show(Student $student): JsonResponse
    {
        try {
            $student->load([
                'user', 
                'currentClass.classTeacher.user', 
                'grades.classSubject.subject',
                'attendances' => function ($query) {
                    $query->where('date', '>=', now()->subDays(30));
                },
                'performanceAlerts' => function ($query) {
                    $query->where('is_resolved', false);
                }
            ]);

            // Add computed fields
            $student->full_name = trim($student->user->first_name . ' ' . $student->user->last_name);
            $student->current_gpa = $this->calculateGPA($student);
            $student->attendance_rate = $this->calculateAttendanceRate($student);

            return response()->json([
                'success' => true,
                'data' => $student
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching student details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student details'
            ], 500);
        }
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $student->user_id,
                'student_code' => 'required|string|unique:students,student_code,' . $student->user_id . ',user_id',
                'date_of_birth' => 'required|date',
                'gender' => 'required|in:male,female,other',
                'address' => 'nullable|string',
                'parent_name' => 'required|string|max:255',
                'parent_phone' => 'required|string|max:20',
                'current_class_id' => 'required|exists:classes,id',
                'enrollment_date' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update user account
            $student->user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'username' => $request->email
            ]);

            // Update student profile
            $student->update([
                'student_code' => $request->student_code,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'parent_name' => $request->parent_name,
                'parent_phone' => $request->parent_phone,
                'current_class_id' => $request->current_class_id,
                'enrollment_date' => $request->enrollment_date
            ]);

            $student->load(['user', 'currentClass']);

            DB::commit();

            // Log the action
            Log::info('Admin updated student', [
                'admin_id' => auth()->id(),
                'student_id' => $student->user_id,
                'student_code' => $student->student_code,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'data' => $student
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating student: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student'
            ], 500);
        }
    }

    /**
     * Remove the specified student
     */
    public function destroy(Student $student): JsonResponse
    {
        try {
            DB::beginTransaction();

            $studentCode = $student->student_code;
            $userId = $student->user_id;

            // Delete student record (this will cascade to related records)
            $student->delete();
            
            // Delete user account
            $student->user->delete();

            DB::commit();

            // Log the action
            Log::info('Admin deleted student', [
                'admin_id' => auth()->id(),
                'deleted_student_id' => $userId,
                'deleted_student_code' => $studentCode,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting student: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student'
            ], 500);
        }
    }

    /**
     * Bulk operations for students
     */
    public function bulkAction(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:activate,deactivate,transfer,delete',
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'exists:students,user_id',
                'class_id' => 'required_if:action,transfer|exists:classes,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $action = $request->action;
            $studentIds = $request->student_ids;
            $affectedCount = 0;

            DB::beginTransaction();

            switch ($action) {
                case 'activate':
                    $affectedCount = User::whereIn('id', $studentIds)->update(['is_active' => true]);
                    break;
                    
                case 'deactivate':
                    $affectedCount = User::whereIn('id', $studentIds)->update(['is_active' => false]);
                    break;
                    
                case 'transfer':
                    $affectedCount = Student::whereIn('user_id', $studentIds)
                        ->update(['current_class_id' => $request->class_id]);
                    break;
                    
                case 'delete':
                    $students = Student::whereIn('user_id', $studentIds)->get();
                    foreach ($students as $student) {
                        $student->delete();
                        $student->user->delete();
                    }
                    $affectedCount = count($students);
                    break;
            }

            DB::commit();

            // Log the action
            Log::info('Admin performed bulk action on students', [
                'admin_id' => auth()->id(),
                'action' => $action,
                'student_ids' => $studentIds,
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
     * Get students by class
     */
    public function getByClass(Request $request, $classId): JsonResponse
    {
        try {
            $students = Student::with(['user', 'currentClass'])
                ->where('current_class_id', $classId)
                ->whereHas('user', function ($query) {
                    $query->where('is_active', true);
                })
                ->get();

            // Add computed fields
            $students->transform(function ($student) {
                $student->full_name = trim($student->user->first_name . ' ' . $student->user->last_name);
                $student->current_gpa = $this->calculateGPA($student);
                return $student;
            });

            return response()->json([
                'success' => true,
                'data' => $students
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching students by class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students'
            ], 500);
        }
    }

    /**
     * Calculate GPA for a student
     */
    private function calculateGPA(Student $student): float
    {
        $grades = $student->grades()->with('classSubject.subject')->get();
        
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($grades as $grade) {
            $points = $this->gradeToPoints($grade->grade_letter);
            $credits = $grade->classSubject->subject->credit_hours ?? 1;
            
            $totalPoints += $points * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
    }

    /**
     * Calculate attendance rate for a student
     */
    private function calculateAttendanceRate(Student $student): float
    {
        $attendances = $student->attendances()
            ->where('date', '>=', now()->subDays(30))
            ->get();

        if ($attendances->isEmpty()) {
            return 0.0;
        }

        $presentCount = $attendances->where('status', 'present')->count();
        $totalCount = $attendances->count();

        return round(($presentCount / $totalCount) * 100, 1);
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
