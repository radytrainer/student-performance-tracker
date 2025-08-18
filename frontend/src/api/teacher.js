import apiClient from './axiosConfig'

export const teacherAPI = {
  // Classes for the current teacher
  getClasses() {
    return apiClient.get('/attendance/my-classes')
  },

  // Basic teacher import endpoints
  getImportHistory() {
    return apiClient.get('/teacher/import-history')
  },
  importStudents(formData) {
    // Minimal teacher import route
    return apiClient.post('/teacher/import-students', formData)
  },

  // Advanced import workflow using admin endpoints (teachers are allowed)
  uploadFileOnly(formData) {
    return apiClient.post('/teacher/import/upload-file', formData)
  },
  getUploadedFiles(params = {}) {
    return apiClient.get('/teacher/import/uploads', { params })
  },
  deleteUploadedFile(id) {
    return apiClient.delete(`/teacher/import/uploads/${id}`)
  },
  getSubjectsForImport() {
    return apiClient.get('/teacher/import/subjects-list')
  },
  importStudentsFromUpload(formData) {
    // Supports uploaded_file_id, default_class_id, sheet_name, subject_ids[]
    return apiClient.post('/teacher/import/students', formData)
  },
  getImportTemplate(type = 'students') {
    return apiClient.get('/teacher/import/template', { params: { type } })
  },
}