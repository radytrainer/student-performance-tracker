<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileImageController extends Controller
{
    /**
     * Upload or update profile image
     */
    public function upload(Request $request)
    {
        try {
            $validated = $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            ]);

            $user = Auth::user();
            
            // Delete old profile picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new image
            $imagePath = $request->file('image')->store('profile_pictures', 'public');
            
            // Update user profile_picture field
            $user->update(['profile_picture' => $imagePath]);

            // Return full URL for the image
            $imageUrl = Storage::disk('public')->url($imagePath);

            return response()->json([
                'message' => 'Profile image uploaded successfully',
                'image_path' => $imagePath,
                'image_url' => $imageUrl,
                'user' => $user->fresh()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Profile image upload failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to upload profile image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current profile image
     */
    public function show()
    {
        try {
            $user = Auth::user();
            
            if (!$user->profile_picture) {
                return response()->json([
                    'message' => 'No profile image found',
                    'image_url' => null
                ], 404);
            }

            $imageUrl = Storage::disk('public')->url($user->profile_picture);

            return response()->json([
                'image_path' => $user->profile_picture,
                'image_url' => $imageUrl
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve profile image: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve profile image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete profile image
     */
    public function delete()
    {
        try {
            $user = Auth::user();
            
            if (!$user->profile_picture) {
                return response()->json([
                    'message' => 'No profile image to delete'
                ], 404);
            }

            // Delete file from storage
            if (Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Update user record
            $user->update(['profile_picture' => null]);

            return response()->json([
                'message' => 'Profile image deleted successfully',
                'user' => $user->fresh()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Profile image deletion failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete profile image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update profile image (same as upload but different endpoint name)
     */
    public function update(Request $request)
    {
        return $this->upload($request);
    }
}
