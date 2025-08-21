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
return apiClient.post('/teacher/import/students', formData)
},

// File upload/manage
uploadFileOnly(formData) {
return apiClient.post('/teacher/import/upload-file', formData)
},
getUploadedFiles(params = {}) {
return apiClient.get('/teacher/import/uploads', { params })
},
deleteUploadedFile(id) {
return apiClient.delete(`/teacher/import/uploads/${id}`)
},

// Lists
getSubjectsForImport(params = {}) {
  return apiClient.get('/teacher/import/subjects-list', { params })
},

importStudentsFromUpload(formData) {
  // Supports uploaded_file_id, default_class_id, sheet_name, subject_ids[]
return apiClient.post('/teacher/import/students', formData)
},

getImportTemplate(type = 'students') {
return apiClient.get('/teacher/import/template', { params: { type } })
},
}
