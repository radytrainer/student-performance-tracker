// src/api/auth.js

import apiClient from './axiosConfig'

export default {
  async login(credentials) {
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
      password_confirmation: userData.password
    }
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
