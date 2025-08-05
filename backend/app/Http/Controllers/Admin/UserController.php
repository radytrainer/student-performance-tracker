<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Constructor to ensure only admins can access these methods
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Reset a user's password (Admin only)
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        try {
            // Authorize the action
            $this->authorize('resetPassword', $user);

            $validator = Validator::make($request->all(), [
                'new_password' => 'required|string|min:8|confirmed',
                'notify_user' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate password if not provided
            $newPassword = $request->new_password ?? Str::random(12);

            // Update the user's password
            $user->update([
                'password_hash' => Hash::make($newPassword),
                'updated_at' => now()
            ]);

            // Log the action for audit trail
            Log::info('Admin reset user password', [
                'action' => 'password_reset',
                'admin_id' => auth()->id(),
                'admin_email' => auth()->user()->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'notify_user' => $request->boolean('notify_user', false),
                'timestamp' => now()
            ]);

            // TODO: Send email notification to user if notify_user is true
            // This would integrate with your notification system

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'temporary_password' => $request->new_password ? null : $newPassword
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a user's role (Admin only)
     */
    public function updateRole(Request $request, User $user): JsonResponse
    {
        try {
            // Authorize the action
            $this->authorize('update', $user);

            $validator = Validator::make($request->all(), [
                'role' => 'required|in:admin,teacher,student'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid role specified',
                    'errors' => $validator->errors()
                ], 422);
            }

            $oldRole = $user->role;
            $newRole = $request->role;

            // Prevent admin from changing their own role
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot change your own role'
                ], 403);
            }

            // Update the user's role
            $user->update([
                'role' => $newRole
            ]);

            // Log the action for audit trail
            Log::info('Admin changed user role', [
                'action' => 'role_change',
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'admin_id' => auth()->id(),
                'admin_email' => auth()->user()->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "User role updated from {$oldRole} to {$newRole}",
                'data' => [
                    'user_id' => $user->id,
                    'old_role' => $oldRole,
                    'new_role' => $newRole
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user status (activate/deactivate)
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        try {
            // Authorize the action
            $this->authorize('update', $user);

            // Prevent admin from deactivating themselves
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot deactivate your own account'
                ], 403);
            }

            $newStatus = !$user->is_active;

            $user->update([
                'is_active' => $newStatus
            ]);

            // Log the action for audit trail
            Log::info('Admin ' . ($newStatus ? 'activated' : 'deactivated') . ' user', [
                'action' => $newStatus ? 'user_activated' : 'user_deactivated',
                'admin_id' => auth()->id(),
                'admin_email' => auth()->user()->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'new_status' => $newStatus,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User ' . ($newStatus ? 'activated' : 'deactivated') . ' successfully',
                'data' => [
                    'user_id' => $user->id,
                    'is_active' => $newStatus
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update user status
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'exists:users,id',
                'status' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userIds = $request->user_ids;
            $status = $request->boolean('status');

            // Prevent admin from deactivating themselves
            if (!$status && in_array(auth()->id(), $userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot deactivate your own account'
                ], 403);
            }

            // Update users
            $updatedCount = User::whereIn('id', $userIds)
                ->where('id', '!=', auth()->id()) // Extra safety check
                ->update(['is_active' => $status]);

            // Log the action for audit trail
            Log::info('Admin performed bulk status update on users', [
                'action' => 'bulk_status_update',
                'user_ids' => $userIds,
                'status' => $status,
                'updated_count' => $updatedCount,
                'admin_id' => auth()->id(),
                'admin_email' => auth()->user()->email,
                'timestamp' => now()
            ]);

            $statusText = $status ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Successfully {$statusText} {$updatedCount} users",
                'data' => [
                    'updated_count' => $updatedCount,
                    'status' => $status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user access logs (login history)
     */
    public function getAccessLogs(User $user): JsonResponse
    {
        try {
            // Authorize the action
            $this->authorize('view', $user);

            // For now, return mock data since we don't have a full activity log system
            // In a real implementation, this would query your audit log table
            $logs = collect([
                [
                    'id' => 1,
                    'action' => 'User logged in',
                    'ip_address' => '192.168.1.100',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'timestamp' => now()->subHours(2),
                    'properties' => ['login_method' => 'email']
                ],
                [
                    'id' => 2,
                    'action' => 'Profile updated',
                    'ip_address' => '192.168.1.100',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'timestamp' => now()->subDays(1),
                    'properties' => ['fields_updated' => ['first_name', 'phone']]
                ],
                [
                    'id' => 3,
                    'action' => 'Password changed',
                    'ip_address' => '192.168.1.100',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'timestamp' => now()->subDays(7),
                    'properties' => ['changed_by' => 'user']
                ]
            ]);

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch access logs: ' . $e->getMessage()
            ], 500);
        }
    }
}
