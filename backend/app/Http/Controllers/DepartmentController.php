<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $query = Department::with(['headTeacher:id,first_name,last_name,email']);
            
            // School-based filtering for non-super-admins
            if (!$user->isSuperAdmin()) {
                $query->forSchool($user->school_id);
            }
            
            // Add teacher counts
            $departments = $query->withCount('teachers')->get();
            
            $departments->each(function ($department) {
                $department->teacher_count = $department->teachers_count;
                unset($department->teachers_count);
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $departments
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch departments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Only admins can create departments
            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:departments',
                'description' => 'nullable|string|max:1000',
                'head_teacher_id' => 'nullable|exists:users,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Validate head teacher is actually a teacher
            if ($request->head_teacher_id) {
                $headTeacher = User::find($request->head_teacher_id);
                if (!$headTeacher || !$headTeacher->isTeacher()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Head teacher must be a valid teacher'
                    ], 422);
                }
                
                // School isolation check
                if (!$user->isSuperAdmin() && $headTeacher->school_id !== $user->school_id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Head teacher must be from your school'
                    ], 403);
                }
            }
            
            $department = Department::create([
                'name' => $request->name,
                'description' => $request->description,
                'head_teacher_id' => $request->head_teacher_id,
            ]);
            
            $department->load(['headTeacher:id,first_name,last_name,email']);
            $department->teacher_count = 0;
            
            return response()->json([
                'status' => 'success',
                'message' => 'Department created successfully',
                'data' => $department
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create department: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $department = Department::with(['headTeacher:id,first_name,last_name,email'])
                ->withCount('teachers')
                ->find($id);
                
            if (!$department) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Department not found'
                ], 404);
            }
            
            // School access validation
            if (!$user->isSuperAdmin()) {
                $departmentUsers = $department->users()->where('school_id', $user->school_id)->exists();
                if (!$departmentUsers) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthorized access'
                    ], 403);
                }
            }
            
            $department->teacher_count = $department->teachers_count;
            unset($department->teachers_count);
            
            return response()->json([
                'status' => 'success',
                'data' => $department
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch department: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $department = Department::find($id);
            
            if (!$department) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Department not found'
                ], 404);
            }
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:departments,name,' . $id,
                'description' => 'nullable|string|max:1000',
                'head_teacher_id' => 'nullable|exists:users,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Validate head teacher
            if ($request->head_teacher_id) {
                $headTeacher = User::find($request->head_teacher_id);
                if (!$headTeacher || !$headTeacher->isTeacher()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Head teacher must be a valid teacher'
                    ], 422);
                }
                
                if (!$user->isSuperAdmin() && $headTeacher->school_id !== $user->school_id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Head teacher must be from your school'
                    ], 403);
                }
            }
            
            $department->update([
                'name' => $request->name,
                'description' => $request->description,
                'head_teacher_id' => $request->head_teacher_id,
            ]);
            
            $department->load(['headTeacher:id,first_name,last_name,email']);
            $department->loadCount('teachers');
            $department->teacher_count = $department->teachers_count;
            unset($department->teachers_count);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Department updated successfully',
                'data' => $department
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update department: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $department = Department::find($id);
            
            if (!$department) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Department not found'
                ], 404);
            }
            
            // Check if department has users
            $userCount = $department->users()->count();
            if ($userCount > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Cannot delete department with {$userCount} assigned users"
                ], 422);
            }
            
            $department->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Department deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete department: ' . $e->getMessage()
            ], 500);
        }
    }

    public function teachers($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $department = Department::find($id);
            
            if (!$department) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Department not found'
                ], 404);
            }
            
            $query = $department->teachers()->with(['teacher:user_id,teacher_code,department,specialization']);
            
            // School-based filtering
            if (!$user->isSuperAdmin()) {
                $query->where('school_id', $user->school_id);
            }
            
            $teachers = $query->select('id', 'first_name', 'last_name', 'email', 'school_id')
                            ->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $teachers
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch department teachers: ' . $e->getMessage()
            ], 500);
        }
    }
}
