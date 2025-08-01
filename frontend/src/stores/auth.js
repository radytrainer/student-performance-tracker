import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import authAPI from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter()
  const user = ref(null)
  const isAuthenticated = ref(false)
  const isLoading = ref(false)
  const error = ref(null)

  const initialize = async () => {
    try {
      isLoading.value = true
      // Check if we have a token in localStorage
      const token = localStorage.getItem('auth_token')
      if (!token) {
        isAuthenticated.value = false
        return
      }
      
      const response = await authAPI.getUser()
      user.value = response.data.user
      isAuthenticated.value = true
    } catch (err) {
      // Clear invalid token
      localStorage.removeItem('auth_token')
      isAuthenticated.value = false
      user.value = null
    } finally {
      isLoading.value = false
    }
  }

  const login = async (credentials) => {
    try {
      isLoading.value = true
      error.value = null
      const response = await authAPI.login(credentials)
      user.value = response.data.user
      isAuthenticated.value = true
      
      console.log('Login successful:', user.value)
      
      // Role-based redirect
      const redirectPath = getRedirectPath(user.value?.role)
      console.log('Redirecting to:', redirectPath)
      router.push(router.currentRoute.value.query.redirect || redirectPath)
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const register = async (userData) => {
    try {
      isLoading.value = true
      error.value = null
      const response = await authAPI.register(userData)
      user.value = response.data.user
      isAuthenticated.value = true
      
      // Role-based redirect
      const redirectPath = getRedirectPath(user.value?.role)
      router.push(redirectPath)
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const getRedirectPath = (role) => {
    switch (role) {
      case 'admin':
        return '/admin/dashboard'
      case 'teacher':
        return '/teacher/dashboard'
      case 'student':
        return '/student/dashboard'
      default:
        return '/'
    }
  }

  const logout = async () => {
    try {
      // Clear local state immediately for speed
      user.value = null
      isAuthenticated.value = false
      localStorage.removeItem('auth_token')
      
      // Redirect immediately, don't wait for API call
      router.push('/login')
      
      // Call API logout in background (for server-side cleanup)
      authAPI.logout().catch(err => {
        console.warn('Background logout API call failed:', err)
      })
    } catch (err) {
      console.error('Logout failed:', err)
      // Ensure redirect happens even on error
      router.push('/login')
    }
  }

  const updateUser = (userData) => {
    user.value = { ...user.value, ...userData }
  }

  const setAuthData = (token, userData) => {
    // Store token in localStorage
    localStorage.setItem('auth_token', token)
    
    // Set user data
    user.value = userData
    isAuthenticated.value = true
    
    console.log('Social login successful:', userData)
  }

  return {
    user,
    isAuthenticated,
    isLoading,
    error,
    initialize,
    login,
    register,
    logout,
    updateUser,
    setAuthData,
    getRedirectPath
  }
})