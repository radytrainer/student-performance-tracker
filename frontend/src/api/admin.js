import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

// Create axios instance with default config
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    // Let Axios set the appropriate Content-Type per request (JSON or multipart)
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
  // Lightweight subjects list for import (admin or teacher)
  getSubjectsForImport: async () => {
    return await api.get('/admin/import/subjects-list')
  },

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

  // Teacher helpers
  getTeacherClasses: async () => {
    return await api.get('/teacher/feedback-classes')
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
    // Explicitly set multipart to avoid any global defaults interfering
    return await api.post('/admin/import/students', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  },

  getImportTemplate: async (type = 'students') => {
  return await api.get('/admin/import/template', { params: { type } })
  },

  uploadFileOnly: async (formData) => {
  return await api.post('/admin/import/upload-file', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  },
 
  getImportHistory: async () => {
    return await api.get('/admin/import/history')
  },

  getUploadedFiles: async (params = {}) => {
    return await api.get('/admin/import/uploads', { params })
  },

  deleteUploadedFile: async (id) => {
    return await api.delete(`/admin/import/uploads/${id}`)
  },

  // Google Sheets helpers (Admin)
  getGoogleAuthUrl: async () => {
    return await api.get('/admin/google/auth-url')
  },
  getGoogleStatus: async () => {
    return await api.get('/admin/google/status')
  },
  previewGoogleSheet: async (payload) => {
    // { sheet_id, sheet_name?, range?, limit? }
    return await api.post('/admin/google/sheets/preview', payload)
  },
  importFromGoogle: async (payload) => {
    // { sheet_id, sheet_name?, range?, default_class_id }
    return await api.post('/admin/import/google', payload)
  },

  // Department Management
  getDepartments: async (params = {}) => {
    return await api.get('/admin/departments', { params })
  },

  createDepartment: async (departmentData) => {
    return await api.post('/admin/departments', departmentData)
  },

  updateDepartment: async (departmentId, departmentData) => {
    return await api.put(`/admin/departments/${departmentId}`, departmentData)
  },

  deleteDepartment: async (departmentId) => {
    return await api.delete(`/admin/departments/${departmentId}`)
  },

  getDepartment: async (departmentId) => {
    return await api.get(`/admin/departments/${departmentId}`)
  },

  // Teacher Management
  getTeachers: async (params = {}) => {
    return await api.get('/admin/teachers', { params })
  },

  createTeacher: async (teacherData) => {
    return await api.post('/admin/teachers', teacherData)
  },

  updateTeacher: async (teacherId, teacherData) => {
    return await api.put(`/admin/teachers/${teacherId}`, teacherData)
  },

  deleteTeacher: async (teacherId) => {
    return await api.delete(`/admin/teachers/${teacherId}`)
  },

  getTeacher: async (teacherId) => {
    return await api.get(`/admin/teachers/${teacherId}`)
  },

  getTeacherStats: async () => {
    return await api.get('/admin/teachers/stats')
  },

  getTeacherAnalytics: async (teacherId) => {
    return await api.get(`/admin/teachers/${teacherId}/analytics`)
  },

  // Teacher-Subject Assignment
  assignSubjectsToTeacher: async (teacherId, subjectIds) => {
    return await api.post(`/admin/teachers/${teacherId}/subjects`, {
      subject_ids: subjectIds
    })
  },

  removeSubjectFromTeacher: async (teacherId, subjectId) => {
    return await api.delete(`/admin/teachers/${teacherId}/subjects/${subjectId}`)
  },

  bulkAssignSubjects: async (teacherIds, subjectIds) => {
    return await api.post('/admin/teachers/bulk-assign-subjects', {
      teacher_ids: teacherIds,
      subject_ids: subjectIds
    })
  },

  // Teacher-Class Assignment
  assignClassesToTeacher: async (teacherId, classIds) => {
    return await api.post(`/admin/teachers/${teacherId}/classes`, {
      class_ids: classIds
    })
  },

  removeClassFromTeacher: async (teacherId, classId) => {
    return await api.delete(`/admin/teachers/${teacherId}/classes/${classId}`)
  },

  bulkAssignClasses: async (teacherIds, classIds) => {
    return await api.post('/admin/teachers/bulk-assign-classes', {
      teacher_ids: teacherIds,
      class_ids: classIds
    })
  },

  // Bulk Teacher Operations
  bulkUpdateTeacherStatus: async (teacherIds, status) => {
    return await api.post('/admin/teachers/bulk-status', {
      teacher_ids: teacherIds,
      status: status
    })
  },

  bulkUpdateTeacherDepartment: async (teacherIds, departmentId) => {
    return await api.post('/admin/teachers/bulk-department', {
      teacher_ids: teacherIds,
      department_id: departmentId
    })
  },

  bulkDeleteTeachers: async (teacherIds) => {
    return await api.post('/admin/teachers/bulk-delete', {
      teacher_ids: teacherIds
    })
  },

  // Teacher Import/Export
  exportTeachers: async (format = 'csv', filters = {}) => {
    return await api.get('/admin/teachers/export', {
      params: { format, ...filters },
      responseType: 'blob'
    })
  },

  importTeachers: async (formData) => {
    return await api.post('/admin/teachers/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  },

  getTeacherImportTemplate: async () => {
    return await api.get('/admin/teachers/import/template', {
      responseType: 'blob'
    })
  },

  // Real-time Data Refresh
  refreshTeacherData: async () => {
    return await api.post('/admin/teachers/refresh')
  },

  subscribeToTeacherUpdates: async (callback) => {
    // This will be implemented with WebSocket integration
    // For now, return a polling mechanism
    const pollInterval = 30000 // 30 seconds
    const poll = async () => {
      try {
        const response = await api.get('/admin/teachers/updates')
        if (response.data.hasUpdates) {
          callback(response.data.updates)
        }
      } catch (error) {
        console.warn('Failed to poll teacher updates:', error)
      }
    }

    const intervalId = setInterval(poll, pollInterval)
    return () => clearInterval(intervalId)
  }
}
