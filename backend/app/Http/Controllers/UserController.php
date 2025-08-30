<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\SchoolIsolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use SchoolIsolation;
    public function __construct()
    {
        // Temporarily disable auth middleware for testing
        // In production, this should be properly secured
    }

    public function index(Request $request)
    {
        $query = User::query()->with(['school', 'student', 'teacher', 'subjects' => function($query) {
            $query->withPivot('primary_subject', 'assigned_at');
        }]);

        // Apply school isolation for admins (but not super admins)
        if (auth()->user() && auth()->user()->role === 'admin' && !auth()->user()->is_super_admin) {
            $query = $this->applyUserSchoolIsolation($query);
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
            });
        }

        // Role filter
        if ($request->has('role') && !empty($request->role)) {
            $query->where('role', $request->role);
        }

        // Subject filter (for teachers only)
        if ($request->filled('subject_id') && ($request->input('role') === 'teacher' || !$request->has('role'))) {
            $subjectId = (int) $request->get('subject_id');
            $query->whereHas('subjects', function($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        // Primary subject filter (for teachers only)
        if ($request->boolean('primary_subjects_only', false) && ($request->input('role') === 'teacher' || !$request->has('role'))) {
            $query->whereHas('subjects', function($q) {
                $q->wherePivot('primary_subject', true);
            });
        }

        // Class filter (students only)
        if ($request->filled('class_id')) {
            $classId = (int) $request->get('class_id');
            $query->where(function($q) use ($classId) {
                $q->where('role', 'student')->whereHas('student', function($qs) use ($classId) {
                    $qs->where('current_class_id', $classId);
                });
            });
        }

        // Status filter
        if ($request->boolean('active_only', false)) {
            $query->where('is_active', true);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at'); // name|email|created_at
               $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        if ($sortBy === 'name') {
            $query->orderBy('first_name', $sortDir)->orderBy('last_name', $sortDir);
        } elseif ($sortBy === 'email') {
            $query->orderBy('email', $sortDir);
        } else {
            $query->orderBy('created_at', $sortDir);
        }
 
        $perPage = min($request->get('per_page', 10), 50); // Max 50 per page
        $users = $query->paginate($perPage);



        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['admin', 'teacher', 'student'])],
            'school_id' => 'nullable|exists:schools,id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            try {
                $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to upload profile picture'], 500);
            }
        }

        $validated['password_hash'] = Hash::make($validated['password']);
        unset($validated['password']);

        // Handle school assignment based on user permissions
        $currentUser = auth()->user();
        
        if ($currentUser) {
            if ($currentUser->is_super_admin) {
                // Super admin can assign any school or leave it null
                if (!isset($validated['school_id']) || empty($validated['school_id'])) {
                    $validated['school_id'] = null;
                }
            } else {
                // Regular admin can only assign users to their own school
                $validated['school_id'] = $currentUser->school_id;
            }
        }

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function show(string $id)
    {
        $user = User::with(['school', 'student', 'teacher', 'subjects' => function($query) {
            $query->withPivot('primary_subject', 'assigned_at');
        }])->findOrFail($id);
        
        // Check school isolation
        if (auth()->user() && !$this->canAccessBySchool($user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        return response()->json($user);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'sometimes|required|string|unique:users,username,' . $id,
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'first_name' => 'sometimes|required|string',
            'last_name' => 'sometimes|required|string',
            'role' => ['sometimes', 'required', Rule::in(['admin', 'teacher', 'student'])],
            'school_id' => 'nullable|exists:schools,id',
            'password' => 'nullable|string|min:6',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        if (!empty($validated['password'])) {
            $validated['password_hash'] = Hash::make($validated['password']);
            unset($validated['password']);
        }

        // Handle school assignment for updates (similar to create)
        $currentUser = auth()->user();
        
        if ($currentUser && isset($validated['school_id'])) {
            if (!$currentUser->is_super_admin) {
                // Regular admin can only assign users to their own school
                $validated['school_id'] = $currentUser->school_id;
            }
            // Super admin can change to any school (validation already handled above)
        }

        $user->update($validated);

        // Attach profile picture URL for frontend convenience
        if ($user->profile_picture) {
            if (str_starts_with($user->profile_picture, 'http://') || str_starts_with($user->profile_picture, 'https://')) {
                $user->profile_picture_url = $user->profile_picture;
            } else {
                $user->profile_picture_url = asset('storage/' . ltrim($user->profile_picture, '/'));
            }
        } else {
            $user->profile_picture_url = null;
        }
 
        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function toggleStatus(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $user->update(['is_active' => $validated['is_active']]);

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $user
        ]);
    }
}