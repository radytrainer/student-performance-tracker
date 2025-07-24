// src/stores/auth.js

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
      const token = localStorage.getItem('auth_token')
      if (!token) {
        isAuthenticated.value = false
        return
      }

      const response = await authAPI.getUser()
      user.value = response.data.user
      isAuthenticated.value = true
    } catch (err) {
      localStorage.removeItem('auth_token')
      isAuthenticated.value = false
      user.value = null
    }
  }

  const login = async (credentials) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await authAPI.login(credentials)
      user.value = response.data.user
      isAuthenticated.value = true

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

      const response = await authAPI.register(userData)
      user.value = response.data.user
      isAuthenticated.value = true

      const redirectPath = getRedirectPath(user.value?.role)
      router.push(redirectPath)
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const logout = async () => {
    try {
      await authAPI.logout()
    } catch (err) {
      console.error('Logout failed:', err)
    } finally {
      user.value = null
      isAuthenticated.value = false
      localStorage.removeItem('auth_token')
      router.push('/login')
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
