// src/api/auth.js

import apiClient, { getCsrfToken } from './axiosConfig'

export default {
  async login(credentials) {
    // Get CSRF token before login
    await getCsrfToken()
    const response = await apiClient.post('/auth/login', credentials)
    const token = response.data.token
    if (token) {
      localStorage.setItem('auth_token', token)
    }
    return response
  },

  async register(userData) {
    const registrationData = {
      ...userData,
      // Backend expects school_id
      school_id: userData.school,
      password_confirmation: userData.password
    }
    delete registrationData.school
    const response = await apiClient.post('/auth/register', registrationData)
    const token = response.data.token
    if (token) {
      localStorage.setItem('auth_token', token)
    }
    return response
  },

  async logout() {
    try {
      await apiClient.post('/auth/logout')
    } finally {
      localStorage.removeItem('auth_token')
    }
  },

  async getUser() {
    return apiClient.get('/auth/user') // Adjust if your endpoint is different
  }
}
