<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentController extends BaseController
{
    /**
     * Display a listing of students (role-based filtering applied)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $user = $this->getCurrentUser();
        $query = Student::with(['user', 'currentClass']);
        
        // Apply role-based filtering automatically
        $query = $this->applyRoleBasedFilters($query, $user);
        
        // Additional filters
        if ($request->has('class_id')) {
            $query->inClass($request->class_id);
        }
        
        if ($request->has('gender')) {
            $query->byGender($request->gender);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            })->orWhere('student_code', 'like', "%{$search}%");
        }

        $perPage = $request->get('per_page', 15);
        $students = $query->active()->paginate($perPage);

        return $this->paginatedResponse($students, 'Students retrieved successfully');
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        $this->authorize('create', Student::class);

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'student_code' => 'required|string|unique:students,student_code',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string|max:100',
            'parent_phone' => 'nullable|string|max:20',
            'enrollment_date' => 'required|date',
            'current_class_id' => 'nullable|exists:classes,id',
        ]);

        // Create user first
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password_hash' => bcrypt('student123'), // Default password
            'role' => 'student',
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'is_active' => true,
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
            'enrollment_date' => $request->enrollment_date,
            'current_class_id' => $request->current_class_id,
        ]);

        $student->load(['user', 'currentClass']);

        return $this->successResponse($student, 'Student created successfully', 201);
    }

    /**
     * Display the specified student
     */
    public function show($id)
    {
        $user = $this->getCurrentUser();
        $query = Student::with(['user', 'currentClass', 'studentClasses.class']);
        
        // Apply role-based filtering
        $query = $this->applyRoleBasedFilters($query, $user);
        
        try {
            $student = $query->findOrFail($id);
            $this->authorize('view', $student);
            
            return $this->successResponse($student, 'Student retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Student not found or access denied', 404);
        }
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $query = Student::query();
        
        // Apply role-based filtering
        $query = $this->applyRoleBasedFilters($query, $user);
        
        try {
            $student = $query->findOrFail($id);
            $this->authorize('update', $student);

            // Validation rules differ based on role
            $rules = [];
            
            if ($user->isAdmin() || $user->isTeacher()) {
                $rules = [
                    'student_code' => 'sometimes|string|unique:students,student_code,' . $student->user_id . ',user_id',
                    'date_of_birth' => 'sometimes|date|before:today',
                    'gender' => 'sometimes|in:male,female,other',
                    'address' => 'sometimes|string',
                    'parent_name' => 'sometimes|string|max:100',
                    'parent_phone' => 'sometimes|string|max:20',
                    'current_class_id' => 'sometimes|nullable|exists:classes,id',
                ];
            } elseif ($user->isStudent()) {
                // Students can only update limited fields
                $rules = [
                    'address' => 'sometimes|string',
                    'parent_phone' => 'sometimes|string|max:20',
                ];
            }

            $request->validate($rules);
            
            $student->update($request->only(array_keys($rules)));
            $student->load(['user', 'currentClass']);

            return $this->successResponse($student, 'Student updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Student not found or access denied', 404);
        }
    }

    /**
     * Remove the specified student
     */
    public function destroy($id)
    {
        $user = $this->getCurrentUser();
        $query = Student::query();
        
        // Apply role-based filtering
        $query = $this->applyRoleBasedFilters($query, $user);
        
        try {
            $student = $query->findOrFail($id);
            $this->authorize('delete', $student);

            // Soft delete by deactivating user
            $student->user->update(['is_active' => false]);

            return $this->successResponse(null, 'Student deactivated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Student not found or access denied', 404);
        }
    }

    /**
     * Get student performance data
     */
    public function performance($id)
    {
        $user = $this->getCurrentUser();
        $query = Student::query();
        
        // Apply role-based filtering
        $query = $this->applyRoleBasedFilters($query, $user);
        
        try {
            $student = $query->findOrFail($id);
            $this->authorize('view', $student);

            $performance = [
                'grades' => $student->grades()->with(['classSubject.subject', 'term'])->accessibleToUser($user)->get(),
                'attendance' => $student->attendances()->accessibleToUser($user)->get(),
                'alerts' => $student->performanceAlerts()->latest()->get(),
                'average_grade' => $student->grades()->accessibleToUser($user)->avg('score_obtained'),
                'attendance_percentage' => $this->calculateAttendancePercentage($student, $user),
            ];

            return $this->successResponse($performance, 'Student performance retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Student not found or access denied', 404);
        }
    }

    /**
     * Calculate attendance percentage
     */
    private function calculateAttendancePercentage($student, $user)
    {
        $attendanceRecords = $student->attendances()->accessibleToUser($user);
        $totalRecords = $attendanceRecords->count();
        
        if ($totalRecords === 0) {
            return 0;
        }
        
        $presentRecords = $attendanceRecords->where('status', 'present')->count();
        return round(($presentRecords / $totalRecords) * 100, 2);
    }

    
}
