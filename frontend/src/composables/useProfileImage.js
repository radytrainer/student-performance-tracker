import { ref, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import profileImageAPI from '@/api/profileImage'

// Global reactive profile image state
const globalProfileImage = ref(null)
const isLoading = ref(false)

export function useProfileImage() {
  const authStore = useAuthStore()

  // Watch for auth store user changes and update profile image
  watch(
    () => authStore.user?.profile_picture,
    (newProfilePicture) => {
      globalProfileImage.value = newProfilePicture
    },
    { immediate: true }
  )

  const getImageUrl = (imagePath) => {
    if (!imagePath) return null
    
    // If it's already a full URL (starts with http), return as is
    if (imagePath.startsWith('http')) return imagePath
    
    // If it's a data URL (base64), return as is
    if (imagePath.startsWith('data:')) return imagePath
    
    // If it's a relative path, prepend the backend storage URL
    const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
    return `${baseUrl.replace('/api', '')}/storage/${imagePath}`
  }

  const updateProfileImage = async (file) => {
    try {
      isLoading.value = true
      
      const response = await profileImageAPI.uploadProfileImage(file)
      
      // Update global state
      globalProfileImage.value = response.data.image_path
      
      // Update auth store
      authStore.updateUser({
        ...authStore.user,
        profile_picture: response.data.image_path
      })
      
      return {
        success: true,
        data: response.data
      }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to upload image'
      }
    } finally {
      isLoading.value = false
    }
  }

  const deleteProfileImage = async () => {
    try {
      isLoading.value = true
      
      const response = await profileImageAPI.deleteProfileImage()
      
      // Update global state
      globalProfileImage.value = null
      
      // Update auth store
      authStore.updateUser({
        ...authStore.user,
        profile_picture: null
      })
      
      return {
        success: true,
        data: response.data
      }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to delete image'
      }
    } finally {
      isLoading.value = false
    }
  }

  return {
    profileImage: globalProfileImage,
    isLoading,
    getImageUrl,
    updateProfileImage,
    deleteProfileImage
  }
}
