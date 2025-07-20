<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = auth()->user();
        
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Access denied. Required roles: ' . implode(', ', $roles),
                'user_role' => $user->role,
                'required_roles' => $roles
            ], 403);
        }

        return $next($request);
    }
}
