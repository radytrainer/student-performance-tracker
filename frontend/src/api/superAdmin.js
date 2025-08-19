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

export const superAdminAPI = {
  // School Management
  getSchools: async (params = {}) => {
    return await api.get('/super-admin/schools', { params })
  },

  createSchool: async (schoolData) => {
    return await api.post('/super-admin/schools', schoolData)
  },

  updateSchool: async (schoolId, schoolData) => {
    return await api.put(`/super-admin/schools/${schoolId}`, schoolData)
  },

  deleteSchool: async (schoolId) => {
    return await api.delete(`/super-admin/schools/${schoolId}`)
  },

  getSchool: async (schoolId) => {
    return await api.get(`/super-admin/schools/${schoolId}`)
  },

  // Sub-Admin Management
  createSubAdmin: async (schoolId, adminData) => {
    return await api.post(`/super-admin/schools/${schoolId}/sub-admins`, adminData)
  },

  getSubAdmins: async (schoolId) => {
    return await api.get(`/super-admin/schools/${schoolId}/sub-admins`)
  },

  // Statistics
  getStats: async () => {
    return await api.get('/super-admin/stats')
  }
}
