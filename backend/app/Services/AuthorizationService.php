<?php

namespace App\Services;

use App\Models\User;
use App\Http\Middleware\PermissionMiddleware;

class AuthorizationService
{
    /**
     * Check if user has specific role
     */
    public static function hasRole(User $user, string $role): bool
    {
        return $user->role === $role;
    }

    /**
     * Check if user has any of the specified roles
     */
    public static function hasAnyRole(User $user, array $roles): bool
    {
        return in_array($user->role, $roles);
    }

    /**
     * Check if user has specific permission
     */
    public static function hasPermission(User $user, string $permission): bool
    {
        return PermissionMiddleware::hasPermission($user, $permission);
    }

    /**
     * Get all permissions for user's role
     */
    public static function getUserPermissions(User $user): array
    {
        return PermissionMiddleware::getRolePermissions($user->role);
    }

    /**
     * Check if teacher can access student data
     */
    public static function teacherCanAccessStudent(User $teacher, User $student): bool
    {
        if ($teacher->role !== 'teacher') {
            return false;
        }

        // Check if teacher has any classes with this student
        return $teacher->teacher->classSubjects()
            ->whereHas('class.studentClasses', function ($query) use ($student) {
                $query->where('student_id', $student->student->user_id)
                      ->where('status', 'active');
            })->exists();
    }

    /**
     * Check if teacher can access class data
     */
    public static function teacherCanAccessClass(User $teacher, $classId): bool
    {
        if ($teacher->role !== 'teacher') {
            return false;
        }

        return $teacher->teacher->classSubjects()
            ->where('class_id', $classId)
            ->exists();
    }

    /**
     * Check if student can access their own data
     */
    public static function studentCanAccessOwnData(User $student, $resourceUserId): bool
    {
        if ($student->role !== 'student') {
            return false;
        }

        return $student->id === $resourceUserId;
    }

    /**
     * Check if user can manage grades for specific class subject
     */
    public static function canManageGrades(User $user, $classSubjectId): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'teacher') {
            return $user->teacher->classSubjects()
                ->where('id', $classSubjectId)
                ->exists();
        }

        return false;
    }

    /**
     * Check if user can mark attendance for specific class
     */
    public static function canMarkAttendance(User $user, $classId): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'teacher') {
            return $user->teacher->classSubjects()
                ->where('class_id', $classId)
                ->exists();
        }

        return false;
    }

    /**
     * Get accessible students for a teacher
     */
    public static function getAccessibleStudents(User $teacher)
    {
        if ($teacher->role !== 'teacher') {
            return collect();
        }

        return User::where('role', 'student')
            ->whereHas('student.studentClasses', function ($query) use ($teacher) {
                $query->whereIn('class_id', function ($subQuery) use ($teacher) {
                    $subQuery->select('class_id')
                        ->from('class_subjects')
                        ->where('teacher_id', $teacher->teacher->user_id);
                })
                ->where('status', 'active');
            });
    }

    /**
     * Get accessible classes for a teacher
     */
    public static function getAccessibleClasses(User $teacher)
    {
        if ($teacher->role !== 'teacher') {
            return collect();
        }

        return $teacher->teacher->classSubjects()
            ->with('class')
            ->get()
            ->pluck('class')
            ->unique('id');
    }

    /**
     * Check if user can view analytics
     */
    public static function canViewAnalytics(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }

    /**
     * Check if user can export data
     */
    public static function canExportData(User $user, string $dataType): bool
    {
        $exportPermissions = [
            'students' => ['admin', 'teacher'],
            'grades' => ['admin', 'teacher'],
            'attendance' => ['admin', 'teacher'],
            'reports' => ['admin', 'teacher'],
            'system' => ['admin']
        ];

        $allowedRoles = $exportPermissions[$dataType] ?? [];
        return in_array($user->role, $allowedRoles);
    }

    /**
     * Check if user can import data
     */
    public static function canImportData(User $user, string $dataType): bool
    {
        $importPermissions = [
            'students' => ['admin', 'teacher'],
            'grades' => ['admin', 'teacher'],
            'attendance' => ['admin', 'teacher'],
            'subjects' => ['admin']
        ];

        $allowedRoles = $importPermissions[$dataType] ?? [];
        return in_array($user->role, $allowedRoles);
    }

    /**
     * Get role-based dashboard data access rules
     */
    public static function getDashboardAccessRules(User $user): array
    {
        switch ($user->role) {
            case 'admin':
                return [
                    'can_view_all_students' => true,
                    'can_view_all_teachers' => true,
                    'can_view_all_classes' => true,
                    'can_view_system_stats' => true,
                    'can_manage_users' => true,
                    'can_view_audit_logs' => true,
                ];

            case 'teacher':
                return [
                    'can_view_assigned_students' => true,
                    'can_view_assigned_classes' => true,
                    'can_manage_grades' => true,
                    'can_manage_attendance' => true,
                    'can_view_analytics' => true,
                    'can_create_reports' => true,
                ];

            case 'student':
                return [
                    'can_view_own_grades' => true,
                    'can_view_own_attendance' => true,
                    'can_view_own_reports' => true,
                    'can_submit_feedback' => true,
                    'can_view_own_performance' => true,
                ];

            default:
                return [];
        }
    }

    /**
     * Validate role transition (for user management)
     */
    public static function canChangeUserRole(User $admin, User $targetUser, string $newRole): bool
    {
        // Only admins can change roles
        if ($admin->role !== 'admin') {
            return false;
        }

        // Admins can't change their own role
        if ($admin->id === $targetUser->id) {
            return false;
        }

        // Valid roles
        $validRoles = ['admin', 'teacher', 'student'];
        if (!in_array($newRole, $validRoles)) {
            return false;
        }

        return true;
    }

    /**
     * Check rate limiting for role-based actions
     */
    public static function checkRateLimit(User $user, string $action): array
    {
        $limits = [
            'admin' => [
                'grade_creation' => 1000, // per hour
                'student_creation' => 500,
                'bulk_operations' => 100,
            ],
            'teacher' => [
                'grade_creation' => 200,
                'student_creation' => 50,
                'bulk_operations' => 20,
            ],
            'student' => [
                'feedback_submission' => 10,
                'profile_updates' => 5,
            ]
        ];

        $userLimits = $limits[$user->role] ?? [];
        $actionLimit = $userLimits[$action] ?? 0;

        return [
            'allowed' => $actionLimit > 0,
            'limit' => $actionLimit,
            'window' => 3600, // 1 hour in seconds
        ];
    }
}
