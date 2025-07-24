import apiClient from '@/api/axiosConfig'

export default {
  // Get current profile image
  getProfileImage() {
    return apiClient.get('/profile/image')
  },

  // Upload new profile image
  uploadProfileImage(file) {
    const formData = new FormData()
    formData.append('image', file)
    
    return apiClient.post('/profile/image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  // Update profile image (same as upload)
  updateProfileImage(file) {
    const formData = new FormData()
    formData.append('image', file)
    
    return apiClient.put('/profile/image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  // Delete profile image
  deleteProfileImage() {
    return apiClient.delete('/profile/image')
  }
}
