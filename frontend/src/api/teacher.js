import apiClient from './axiosConfig'

export const teacherAPI = {
// Classes for the current teacher
getClasses(params = {}) {
return apiClient.get('/attendance/my-classes', { params })
},

// Import history
getImportHistory(params = {}) {
return apiClient.get('/teacher/import-history', { params })
},

importStudents(formData) {
return apiClient.post('/teacher/import/students', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
},

// File upload/manage
uploadFileOnly(formData) {
return apiClient.post('/teacher/import/upload-file', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
},
getUploadedFiles(params = {}) {
return apiClient.get('/teacher/import/uploads', { params })
},
deleteUploadedFile(id) {
return apiClient.delete(`/teacher/import/uploads/${id}`)
},

// Google Sheets helpers
getGoogleAuthUrl() {
return apiClient.get('/google/auth-url')
},
getGoogleStatus() {
  return apiClient.get('/google/status')
},
previewGoogleSheet(payload) {
  // { sheet_id, sheet_name?, range?, limit? }
 return apiClient.post('/google/sheets/preview', payload)
},
importFromGoogle(payload) {
// { sheet_id, sheet_name?, range?, default_class_id }
return apiClient.post('/teacher/import/google', payload)
},

// Student analytics
getStudentComparison(studentId, params = {}) {
return apiClient.get(`/teacher/students/${studentId}/comparison`, { params })
},
  
 // Lists
 getSubjectsForImport(params = {}) {
   return apiClient.get('/teacher/import/subjects-list', { params })
 },
 
importStudentsFromUpload(formData) {
  // Supports uploaded_file_id, default_class_id, sheet_name, subject_ids[]
return apiClient.post('/teacher/import/students', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
},
 
getImportTemplate(type = 'students') {
return apiClient.get('/teacher/import/template', { params: { type } })
},
}
