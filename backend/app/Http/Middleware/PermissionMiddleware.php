<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Permission matrix based on roles
     */
    private $permissions = [
        // Admin permissions - full access
        'admin' => [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'students.view', 'students.create', 'students.update', 'students.delete',
            'teachers.view', 'teachers.create', 'teachers.update', 'teachers.delete',
            'classes.view', 'classes.create', 'classes.update', 'classes.delete',
            'subjects.view', 'subjects.create', 'subjects.update', 'subjects.delete',
            'grades.view', 'grades.create', 'grades.update', 'grades.delete',
            'attendance.view', 'attendance.create', 'attendance.update', 'attendance.delete',
            'reports.view', 'reports.create', 'reports.update', 'reports.delete',
            'analytics.view', 'system.settings', 'audit.logs',
            'notifications.view', 'notifications.create',
            'feedback.view', 'feedback.manage',
            'imports.view', 'imports.create'
        ],
        
        // Teacher permissions - class and student management
        'teacher' => [
            'students.view', 'students.update', // View and edit assigned students
            'classes.view', 'classes.update', // View and manage assigned classes
            'subjects.view', // View subjects they teach
            'grades.view', 'grades.create', 'grades.update', 'grades.delete', // Full grade management
            'attendance.view', 'attendance.create', 'attendance.update', // Attendance management
            'reports.view', 'reports.create', // Create and view reports for their students
            'analytics.view', // View analytics for their classes
            'notifications.view', 'notifications.create', // View and create notifications
            'feedback.view', 'feedback.respond', // View feedback and respond
            'imports.view', 'imports.create', // Import student data
            'profile.view', 'profile.update' // Own profile management
        ],
        
        // Student permissions - view own data only
        'student' => [
            'grades.view.own', // View own grades only
            'attendance.view.own', // View own attendance only
            'reports.view.own', // View own reports only
            'notifications.view.own', // View own notifications only
            'feedback.create', // Submit feedback
            'profile.view', 'profile.update' // Own profile management
        ]
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = auth()->user();
        $userPermissions = $this->permissions[$user->role] ?? [];
        
        if (!in_array($permission, $userPermissions)) {
            return response()->json([
                'message' => 'Access denied. Missing permission: ' . $permission,
                'user_role' => $user->role,
                'required_permission' => $permission,
                'user_permissions' => $userPermissions
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user has permission
     */
    public static function hasPermission($user, $permission)
    {
        $middleware = new self();
        $userPermissions = $middleware->permissions[$user->role] ?? [];
        return in_array($permission, $userPermissions);
    }

    /**
     * Get all permissions for a role
     */
    public static function getRolePermissions($role)
    {
        $middleware = new self();
        return $middleware->permissions[$role] ?? [];
    }
}
