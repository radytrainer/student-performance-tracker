// Test utility to verify profile image updates and active users functionality
import { useAuthStore } from '@/stores/auth'
import { useActiveUsers } from '@/composables/useActiveUsers'

export function testProfileImageUpdate() {
  const authStore = useAuthStore()
  
  console.log('Testing profile image update...')
  console.log('Current user:', authStore.user)
  
  // Simulate profile image update
  const originalProfilePicture = authStore.user?.profile_picture
  console.log('Original profile picture:', originalProfilePicture)
  
  // Update with test image
  authStore.updateUser({
    ...authStore.user,
    profile_picture: 'test-image.jpg'
  })
  
  console.log('Updated user:', authStore.user)
  console.log('Updated profile picture:', authStore.user?.profile_picture)
  
  // Restore original
  setTimeout(() => {
    authStore.updateUser({
      ...authStore.user,
      profile_picture: originalProfilePicture
    })
    console.log('Restored to original:', authStore.user?.profile_picture)
  }, 2000)
}

export function testActiveUsers() {
  console.log('Testing active users functionality...')
  
  // Test the active users composable directly
  const {
    activeUsers,
    totalActiveUsers,
    isLoading,
    error,
    refreshActiveUsers,
    getUserImage,
    getUserInitials,
    getUserColor
  } = useActiveUsers()
  
  console.log('Active users state:', {
    activeUsers: activeUsers.value,
    totalActiveUsers: totalActiveUsers.value,
    isLoading: isLoading.value,
    error: error.value
  })
  
  // Test refresh functionality
  console.log('Refreshing active users...')
  refreshActiveUsers()
  
  // Test helper functions with mock user
  const mockUser = {
    id: 1,
    first_name: 'John',
    last_name: 'Doe',
    email: 'john@example.com',
    profile_picture: null
  }
  
  console.log('Testing helper functions:')
  console.log('getUserImage:', getUserImage(mockUser))
  console.log('getUserInitials:', getUserInitials(mockUser))
  console.log('getUserColor:', getUserColor(mockUser, 0))
  
  return {
    activeUsers,
    totalActiveUsers,
    isLoading,
    error
  }
}

// Export both functions for easy testing
export const profileImageTests = {
  testProfileImageUpdate,
  testActiveUsers
}
