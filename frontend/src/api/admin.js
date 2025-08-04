import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

// Create axios instance with default config
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Add auth token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export const adminAPI = {
  // User Management
  resetUserPassword: async (userId, passwordData) => {
    return await api.post(`/admin/users/${userId}/reset-password`, passwordData)
  },

  updateUserRole: async (userId, roleData) => {
    return await api.put(`/admin/users/${userId}/role`, roleData)
  },

  toggleUserStatus: async (userId) => {
    return await api.patch(`/admin/users/${userId}/toggle-status`)
  },

  bulkUpdateUserStatus: async (userIds, status) => {
    return await api.post('/admin/users/bulk-status', {
      user_ids: userIds,
      status: status
    })
  },

  getUserAccessLogs: async (userId) => {
    return await api.get(`/admin/users/${userId}/access-logs`)
  }
}
