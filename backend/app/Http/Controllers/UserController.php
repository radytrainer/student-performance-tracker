<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        // Temporarily disable auth middleware for testing
        // In production, this should be properly secured
    }

    public function index(Request $request)
    {
        $query = User::query();

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

        // Status filter
        if ($request->boolean('active_only', false)) {
            $query->where('is_active', true);
        }

        // Order by creation date (newest first)
        $query->orderBy('created_at', 'desc');

        $perPage = min($request->get('per_page', 10), 50); // Max 50 per page
        $users = $query->paginate($perPage);

        // Transform profile picture URLs for each user
        foreach ($users->items() as $user) {
            if ($user->profile_picture) {
                $user->profile_picture = asset('storage/' . $user->profile_picture);
            }
        }

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['admin', 'teacher', 'student'])],
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

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function show(string $id)
    {
        $user = User::findOrFail($id);
        $user->profile_picture = $user->profile_picture ? asset('storage/' . $user->profile_picture) : null;
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

        $user->update($validated);

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