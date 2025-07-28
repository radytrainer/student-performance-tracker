import { ref, onMounted, onUnmounted } from 'vue'
import { usersAPI } from '@/api/users'
import axios from '@/api/axiosConfig'

export function useActiveUsers() {
  const activeUsers = ref([])
  const totalActiveUsers = ref(0)
  const isLoading = ref(false)
  const error = ref(null)
  
  // Refresh interval for active users (every 30 seconds)
  let refreshInterval = null

  const fetchActiveUsers = async () => {
    try {
      isLoading.value = true
      error.value = null
      
      // Fetch active users (limit to 8 for display) - using direct axios call for debugging
      const response = await axios.get('/active-users', {
        params: {
          active_only: true,
          per_page: 8
        }
      })
      
      // Handle paginated response structure
      console.log('API Response:', response.data)
      
      const responseData = response.data
      let users = []
      let total = 0
      
      // Check if it's a paginated response
      if (responseData.data && Array.isArray(responseData.data)) {
        users = responseData.data
        total = responseData.total || users.length
      } else if (Array.isArray(responseData)) {
        users = responseData
        total = users.length
      }
      
      console.log('Processed users:', users)
      
      if (users.length > 0) {
        // Shuffle users for random display
        const shuffled = users.sort(() => Math.random() - 0.5)
        activeUsers.value = shuffled.slice(0, 5) // Display first 5
        totalActiveUsers.value = total
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch active users'
      console.error('Error fetching active users:', err)
    } finally {
      isLoading.value = false
    }
  }

  const startAutoRefresh = (intervalMs = 30000) => {
    // Clear existing interval
    if (refreshInterval) {
      clearInterval(refreshInterval)
    }
    
    // Set up new interval
    refreshInterval = setInterval(fetchActiveUsers, intervalMs)
  }

  const stopAutoRefresh = () => {
    if (refreshInterval) {
      clearInterval(refreshInterval)
      refreshInterval = null
    }
  }

  const refreshActiveUsers = () => {
    fetchActiveUsers()
  }

  // Get user profile image or fallback
  const getUserImage = (user) => {
    if (user?.profile_picture) {
      // Backend already returns full URLs, so use as-is
      return user.profile_picture
    }
    return null
  }

  // Get user initials as fallback
  const getUserInitials = (user) => {
    if (!user) return '??'
    
    const firstName = user.first_name || user.name?.split(' ')[0] || ''
    const lastName = user.last_name || user.name?.split(' ')[1] || ''
    
    if (firstName && lastName) {
      return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
    }
    if (firstName) {
      return firstName.charAt(0).toUpperCase()
    }
    if (user.email) {
      return user.email.charAt(0).toUpperCase()
    }
    return '??'
  }

  // Get background color for user avatar
  const getUserColor = (user, index) => {
    const colors = [
      'bg-gradient-to-br from-pink-400 to-orange-400',
      'bg-gradient-to-br from-blue-400 to-purple-400',
      'bg-gradient-to-br from-green-400 to-blue-400',
      'bg-gradient-to-br from-yellow-400 to-red-400',
      'bg-gradient-to-br from-purple-400 to-indigo-400',
      'bg-gradient-to-br from-indigo-400 to-pink-400',
      'bg-gradient-to-br from-orange-400 to-red-400',
      'bg-gradient-to-br from-teal-400 to-blue-400'
    ]
    
    // Use user ID for consistency or fall back to index
    const colorIndex = user?.id ? user.id % colors.length : index % colors.length
    return colors[colorIndex]
  }

  // Auto-start fetching and refreshing
  onMounted(() => {
    fetchActiveUsers()
    startAutoRefresh()
  })

  onUnmounted(() => {
    stopAutoRefresh()
  })

  return {
    activeUsers,
    totalActiveUsers,
    isLoading,
    error,
    fetchActiveUsers,
    refreshActiveUsers,
    startAutoRefresh,
    stopAutoRefresh,
    getUserImage,
    getUserInitials,
    getUserColor
  }
}
