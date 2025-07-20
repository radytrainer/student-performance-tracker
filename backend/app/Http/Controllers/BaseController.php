<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelController;

class BaseController extends LaravelController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Return success response with data
     */
    protected function successResponse($data = null, $message = 'Success', $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return error response
     */
    protected function errorResponse($message = 'Error', $statusCode = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return paginated response
     */
    protected function paginatedResponse($data, $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ]
        ]);
    }

    /**
     * Get current authenticated user
     */
    protected function getCurrentUser()
    {
        return auth()->user();
    }

    /**
     * Check if current user has role
     */
    protected function hasRole($role)
    {
        $user = $this->getCurrentUser();
        return $user && $user->role === $role;
    }

    /**
     * Check if current user has any of the roles
     */
    protected function hasAnyRole($roles)
    {
        $user = $this->getCurrentUser();
        return $user && in_array($user->role, $roles);
    }

    /**
     * Apply role-based filters to query
     */
    protected function applyRoleBasedFilters($query, $user = null)
    {
        $user = $user ?: $this->getCurrentUser();
        
        if (method_exists($query->getModel(), 'scopeAccessibleToUser')) {
            return $query->accessibleToUser($user);
        }
        
        return $query;
    }
}
