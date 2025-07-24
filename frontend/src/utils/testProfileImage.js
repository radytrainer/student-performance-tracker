// Test utility to verify profile image updates work
import { useAuthStore } from '@/stores/auth'

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
