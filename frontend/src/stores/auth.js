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
      const response = await authAPI.getUser()
      user.value = response.data
      isAuthenticated.value = true
    } catch (err) {
      isAuthenticated.value = false
    }
  }

  const login = async (credentials) => {
    try {
      isLoading.value = true
      error.value = null
      await authAPI.login(credentials)
      await initialize()
      
      // Role-based redirect
      const redirectPath = getRedirectPath(user.value?.role)
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
      await authAPI.register(userData)
      await initialize()
      
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
      await authAPI.logout()
      user.value = null
      isAuthenticated.value = false
      router.push('/login')
    } catch (err) {
      console.error('Logout failed:', err)
    }
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
    getRedirectPath
  }
})