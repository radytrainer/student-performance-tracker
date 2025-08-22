<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get current user's profile with all related data
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load(['student', 'teacher']); // Load relationships

            // Normalize profile picture to URL if stored as a relative path
            $profileUrl = null;
            if ($user->profile_picture) {
                if (str_starts_with($user->profile_picture, 'http://') || str_starts_with($user->profile_picture, 'https://')) {
                    $profileUrl = $user->profile_picture;
                } else {
                    $profileUrl = asset('storage/' . ltrim($user->profile_picture, '/'));
                }
            }

            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'profile_picture' => $user->profile_picture,
                'profile_picture_url' => $profileUrl,
                'phone' => Schema::hasColumn('users', 'phone') ? ($user->phone ?? null) : null,
                'bio' => Schema::hasColumn('users', 'bio') ? ($user->bio ?? null) : null,
                'created_at' => $user->created_at,
                'last_login' => $user->last_login,
            ];

            // Add role-specific data
            if ($user->role === 'student' && $user->student) {
                $userData['student_id'] = $user->student->student_code;
                $userData['student_code'] = $user->student->student_code;
                $userData['date_of_birth'] = $user->student->date_of_birth;
                $userData['gender'] = $user->student->gender;
                $userData['address'] = $user->student->address;
                $userData['parent_name'] = $user->student->parent_name;
                $userData['parent_phone'] = $user->student->parent_phone;
                $userData['enrollment_date'] = $user->student->enrollment_date;
            } elseif ($user->role === 'teacher' && $user->teacher) {
                $userData['teacher_code'] = $user->teacher->teacher_code;
                $userData['department'] = $user->teacher->department;
                $userData['qualification'] = $user->teacher->qualification;
                $userData['specialization'] = $user->teacher->specialization;
                $userData['hire_date'] = $user->teacher->hire_date;
            }

            return response()->json([
                'success' => true,
                'user' => $userData
            ], 200);
        } catch (\Exception $e) {
            Log::error('Profile show error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get profile data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update current user's profile
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load(['student', 'teacher']); // Load relationships

            // Validation rules
            $rules = [
                'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
                'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|nullable|string|max:255',
                'phone' => 'sometimes|nullable|string|max:20',
                'bio' => 'sometimes|nullable|string|max:1000',
                'profile_picture' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            ];

            // Add role-specific validation rules
            if ($user->role === 'student') {
                $rules = array_merge($rules, [
                    'date_of_birth' => 'sometimes|nullable|date',
                    'gender' => 'sometimes|nullable|in:male,female,other',
                    'address' => 'sometimes|nullable|string|max:500',
                    'parent_name' => 'sometimes|nullable|string|max:255',
                    'parent_phone' => 'sometimes|nullable|string|max:20',
                ]);
            } elseif ($user->role === 'teacher') {
                $rules = array_merge($rules, [
                    'department' => 'sometimes|nullable|string|max:255',
                    'qualification' => 'sometimes|nullable|string|max:500',
                    'specialization' => 'sometimes|nullable|string|max:500',
                ]);
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
            }

            // Filter validated data to only include fields that exist in users table
            $userFields = ['username', 'email', 'first_name', 'last_name', 'profile_picture'];

            // Check if phone and bio columns exist (they might not if migration hasn't run)
            try {
                // Use Schema to check if columns exist
                if (Schema::hasColumn('users', 'phone')) {
                    $userFields[] = 'phone';
                }
                if (Schema::hasColumn('users', 'bio')) {
                    $userFields[] = 'bio';
                }
            } catch (\Exception $e) {
                // Fallback: try to access the fields directly
                try {
                    $testPhone = $user->phone;
                    $userFields[] = 'phone';
                } catch (\Exception $e2) {
                    // Phone column doesn't exist
                }
                try {
                    $testBio = $user->bio;
                    $userFields[] = 'bio';
                } catch (\Exception $e2) {
                    // Bio column doesn't exist
                }
            }

            $userUpdateData = array_intersect_key($validated, array_flip($userFields));

            // Update user data only with existing fields
            if (!empty($userUpdateData)) {
                $user->update($userUpdateData);
            }

            // Update role-specific data
            if ($user->role === 'student' && $user->student) {
                $studentData = array_intersect_key($validated, array_flip([
                    'date_of_birth',
                    'gender',
                    'address',
                    'parent_name',
                    'parent_phone'
                ]));

                if (!empty($studentData)) {
                    $user->student->update($studentData);
                }
            } elseif ($user->role === 'teacher' && $user->teacher) {
                $teacherData = array_intersect_key($validated, array_flip([
                    'department',
                    'qualification',
                    'specialization'
                ]));

                if (!empty($teacherData)) {
                    $user->teacher->update($teacherData);
                }
            }

            // Refresh user data with relationships
            $user->refresh();
            $user->load(['student', 'teacher']);

            // Prepare response data
            // Normalize profile picture to URL if stored as a relative path
            $profileUrl = null;
            if ($user->profile_picture) {
                if (str_starts_with($user->profile_picture, 'http://') || str_starts_with($user->profile_picture, 'https://')) {
                    $profileUrl = $user->profile_picture;
                } else {
                    $profileUrl = asset('storage/' . ltrim($user->profile_picture, '/'));
                }
            }

            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'profile_picture' => $user->profile_picture,
                'profile_picture_url' => $profileUrl,
                'phone' => Schema::hasColumn('users', 'phone') ? ($user->phone ?? null) : null,
                'bio' => Schema::hasColumn('users', 'bio') ? ($user->bio ?? null) : null,
                'created_at' => $user->created_at,
                'last_login' => $user->last_login,
            ];

            // Add role-specific data to response
            if ($user->role === 'student' && $user->student) {
                $userData['student_id'] = $user->student->student_code;
                $userData['student_code'] = $user->student->student_code;
                $userData['date_of_birth'] = $user->student->date_of_birth;
                $userData['gender'] = $user->student->gender;
                $userData['address'] = $user->student->address;
                $userData['parent_name'] = $user->student->parent_name;
                $userData['parent_phone'] = $user->student->parent_phone;
                $userData['enrollment_date'] = $user->student->enrollment_date;
            } elseif ($user->role === 'teacher' && $user->teacher) {
                $userData['teacher_code'] = $user->teacher->teacher_code;
                $userData['department'] = $user->teacher->department;
                $userData['qualification'] = $user->teacher->qualification;
                $userData['specialization'] = $user->teacher->specialization;
                $userData['hire_date'] = $user->teacher->hire_date;
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $userData
            ], 200);
        } catch (\Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? null,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
