<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username|max:255',
            'email' => 'required|string|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'role' => 'required|in:admin,teacher,student',
            // Additional fields for students
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create user first
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'role' => $request->role,
                'is_active' => true,
            ]);

            // Create role-specific record
            if ($user->role === 'student') {
                $this->createStudentRecord($user, $request);
            } elseif ($user->role === 'teacher') {
                $this->createTeacherRecord($user, $request);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password_hash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is inactive'
                ], 403);
            }

            // Update last login
            $user->update(['last_login' => now()]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load(['student', 'teacher']); // Load relationships

            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'profile_picture' => $user->profile_picture,
                'phone' => $user->phone,
                'bio' => $user->bio,
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $request->user()->currentAccessToken()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'token' => $token,
                'token_type' => 'Bearer'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create student record
     */
    private function createStudentRecord(User $user, Request $request): void
    {
        $user->student()->create([
            'user_id' => $user->id,
            'student_code' => $this->generateStudentCode(),
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'address' => trim(($request->city ?? '') . ', ' . ($request->country ?? ''), ', '),
            'enrollment_date' => now()->toDateString(),
        ]);
    }

    /**
     * Create teacher record
     */
    private function createTeacherRecord(User $user, Request $request): void
    {
        $user->teacher()->create([
            'user_id' => $user->id,
            'teacher_code' => $this->generateTeacherCode(),
            'department' => null, // Can be set later by admin
            'qualification' => null, // Can be set later
            'specialization' => null, // Can be set later
            'hire_date' => now()->toDateString(),
        ]);
    }

    /**
     * Generate unique student code
     */
    private function generateStudentCode(): string
    {
        do {
            $code = 'STU' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Student::where('student_code', $code)->exists());
        
        return $code;
    }

    /**
     * Generate unique teacher code
     */
    private function generateTeacherCode(): string
    {
        do {
            $code = 'TCH' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Teacher::where('teacher_code', $code)->exists());
        
        return $code;
    }
<<<<<<< HEAD
=======

    /**
     * Redirect to social provider
     */
    public function redirectToProvider(string $provider)
    {
        try {
            if (!in_array($provider, ['google', 'facebook'])) {
                return redirect('http://localhost:5173/login?error=unsupported_provider');
            }

            return Socialite::driver($provider)->redirect();

        } catch (\Exception $e) {
            return redirect('http://localhost:5173/login?error=oauth_error&message=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Handle social provider callback
     */
    public function handleProviderCallback(string $provider)
    {
        try {
            if (!in_array($provider, ['google', 'facebook'])) {
                return redirect('http://localhost:5173/login?error=unsupported_provider');
            }

            $socialUser = Socialite::driver($provider)->user();

            // Check if user exists by email
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Update user info if necessary
                $user->update([
                    'last_login' => now(),
                    'profile_picture' => $socialUser->getAvatar()
                ]);
            } else {
                // Generate unique username if needed
                $baseUsername = $socialUser->getNickname() ?? explode('@', $socialUser->getEmail())[0];
                $username = $baseUsername;
                $counter = 1;
                
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . '_' . $counter;
                    $counter++;
                }

                // Create new user
                $user = User::create([
                    'username' => $username,
                    'email' => $socialUser->getEmail(),
                    'password_hash' => Hash::make(bin2hex(random_bytes(16))), // Random password
                    'first_name' => $socialUser->getName() ?? '',
                    'last_name' => '',
                    'role' => 'student', // Default role
                    'is_active' => true,
                    'profile_picture' => $socialUser->getAvatar(),
                    'email_verified_at' => now()
                ]);

                // Create student record for new social users
                $this->createStudentRecord($user, new Request());
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirect to frontend with token
            return redirect('http://localhost:3000/login?token=' . $token . '&user=' . urlencode(json_encode([
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
            ])));

        } catch (\Exception $e) {
            return redirect('http://localhost:3000/login?error=social_login_failed&message=' . urlencode($e->getMessage()));
        }
    }
>>>>>>> main
}
