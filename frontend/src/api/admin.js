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
  },

  // Student Management
  getStudents: async (params = {}) => {
    return await api.get('/admin/students', { params })
  },

  createStudent: async (studentData) => {
    return await api.post('/admin/students', studentData)
  },

  updateStudent: async (studentId, studentData) => {
    return await api.put(`/admin/students/${studentId}`, studentData)
  },

  deleteStudent: async (studentId) => {
    return await api.delete(`/admin/students/${studentId}`)
  },

  getStudent: async (studentId) => {
    return await api.get(`/admin/students/${studentId}`)
  },

  bulkStudentAction: async (actionData) => {
    return await api.post('/admin/students/bulk-action', actionData)
  },

  getStudentsByClass: async (classId) => {
    return await api.get(`/admin/classes/${classId}/students`)
  },

  // Class Management
  getClasses: async (params = {}) => {
    return await api.get('/admin/classes', { params })
  },

  createClass: async (classData) => {
    return await api.post('/admin/classes', classData)
  },

  updateClass: async (classId, classData) => {
    return await api.put(`/admin/classes/${classId}`, classData)
  },

  deleteClass: async (classId) => {
    return await api.delete(`/admin/classes/${classId}`)
  },

  getClass: async (classId) => {
    return await api.get(`/admin/classes/${classId}`)
  },

  assignTeacher: async (classId, assignmentData) => {
    return await api.post(`/admin/classes/${classId}/assign-teacher`, assignmentData)
  },

  getAvailableTeachers: async () => {
    return await api.get('/admin/teachers/available')
  },

  getClassStats: async () => {
    return await api.get('/admin/classes-stats')
  },

  // Subject Management
  getSubjects: async (params = {}) => {
    return await api.get('/admin/subjects', { params })
  },

  createSubject: async (subjectData) => {
    return await api.post('/admin/subjects', subjectData)
  },

  updateSubject: async (subjectId, subjectData) => {
    return await api.put(`/admin/subjects/${subjectId}`, subjectData)
  },

  deleteSubject: async (subjectId) => {
    return await api.delete(`/admin/subjects/${subjectId}`)
  },

  getSubject: async (subjectId) => {
    return await api.get(`/admin/subjects/${subjectId}`)
  },

  bulkSubjectAction: async (actionData) => {
    return await api.post('/admin/subjects/bulk-action', actionData)
  },

  getDepartments: async () => {
    return await api.get('/admin/subjects/departments')
  },

  getSubjectStats: async () => {
    return await api.get('/admin/subjects-stats')
  },

  // Term Management
  getTerms: async (params = {}) => {
    return await api.get('/admin/terms', { params })
  },

  createTerm: async (termData) => {
    return await api.post('/admin/terms', termData)
  },

  updateTerm: async (termId, termData) => {
    return await api.put(`/admin/terms/${termId}`, termData)
  },

  deleteTerm: async (termId) => {
    return await api.delete(`/admin/terms/${termId}`)
  },

  setCurrentTerm: async (termId) => {
    return await api.post(`/admin/terms/${termId}/set-current`)
  },

  getCurrentTerm: async () => {
    return await api.get('/admin/terms/current')
  },

  // Data Import
  importStudents: async (formData) => {
    return await api.post('/admin/import/students', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  getImportTemplate: async (type = 'students') => {
    return await api.get('/admin/import/template', { params: { type } })
  },

  getImportHistory: async () => {
    return await api.get('/admin/import/history')
  }
}
