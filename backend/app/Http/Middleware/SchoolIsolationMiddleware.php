<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SchoolIsolationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$user->school_id) {
            return response()->json(['message' => 'User not assigned to any school'], 403);
        }

        // Add school_id to the request for controllers to use
        $request->merge(['current_school_id' => $user->school_id]);
        
        return $next($request);
    }
}
