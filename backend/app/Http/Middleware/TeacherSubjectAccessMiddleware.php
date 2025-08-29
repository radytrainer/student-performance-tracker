<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class TeacherSubjectAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only allow admins to manage teacher-subject assignments
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ], 403);
        }

        // For school-specific routes, ensure school isolation
        if ($request->route('teacher')) {
            $teacher = $request->route('teacher');
            
            // Ensure the target is actually a teacher
            if (!$teacher instanceof User || $teacher->role !== 'teacher') {
                return response()->json([
                    'success' => false,
                    'message' => 'The specified user is not a teacher.'
                ], 400);
            }

            // School isolation for non-super admins
            if ($user->isSchoolAdmin() && $teacher->school_id !== $user->school_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only manage teachers from your school.'
                ], 403);
            }
        }

        return $next($request);
    }
}
