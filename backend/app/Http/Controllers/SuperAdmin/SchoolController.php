<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class SchoolController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isSuperAdmin()) {
                return response()->json(['message' => 'Access denied. Super admin required.'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of all schools
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search', '');

        $query = School::with(['users', 'classes'])
            ->withCount(['users', 'classes', 'students', 'teachers', 'admins']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $schools = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $schools
        ]);
    }

    /**
     * Store a newly created school
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:schools,code|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $school = School::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'School created successfully',
            'data' => $school
        ], 201);
    }

    /**
     * Display the specified school
     */
    public function show(School $school): JsonResponse
    {
        $school->load(['users', 'classes'])
               ->loadCount(['users', 'classes', 'students', 'teachers', 'admins']);

        return response()->json([
            'success' => true,
            'data' => $school
        ]);
    }

    /**
     * Update the specified school
     */
    public function update(Request $request, School $school): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('schools')->ignore($school->id)],
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $school->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'School updated successfully',
            'data' => $school
        ]);
    }

    /**
     * Remove the specified school
     */
    public function destroy(School $school): JsonResponse
    {
        if ($school->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete school with existing users. Please transfer or remove users first.'
            ], 422);
        }

        $school->delete();

        return response()->json([
            'success' => true,
            'message' => 'School deleted successfully'
        ]);
    }

    /**
     * Create a sub-admin for a school
     */
    public function createSubAdmin(Request $request, School $school): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'nullable|string',
        ]);

        $validated['password_hash'] = Hash::make($validated['password']);
        $validated['role'] = 'admin';
        $validated['school_id'] = $school->id;
        $validated['is_super_admin'] = false;
        $validated['is_active'] = true;

        unset($validated['password']);

        $subAdmin = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sub-admin created successfully',
            'data' => $subAdmin->load('school')
        ], 201);
    }

    /**
     * Get all sub-admins for a school
     */
    public function getSubAdmins(School $school): JsonResponse
    {
        $subAdmins = $school->admins()->where('is_super_admin', false)->get();

        return response()->json([
            'success' => true,
            'data' => $subAdmins
        ]);
    }

    /**
     * Get school statistics
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_schools' => School::count(),
            'active_schools' => School::where('status', 'active')->count(),
            'total_users' => User::where('is_super_admin', false)->count(),
            'total_admins' => User::where('role', 'admin')->where('is_super_admin', false)->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_students' => User::where('role', 'student')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
