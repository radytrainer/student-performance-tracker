import apiClient from '@/api/axiosConfig' // or wherever your axios config lives

export async function getMyClasses() {
  try {
    const response = await apiClient.get('/teacher/feedback-classes')
    return response.data.data
  } catch (error) {
    console.error('Error fetching classes:', error)
    throw error
  }
}
