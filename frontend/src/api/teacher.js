import axios from 'axios'

export const teacherAPI = {
  getClasses() {
    return axios.get('/api/classes')
  },
  getImportHistory() {
    return axios.get('/api/teacher/import-history')
  },
  importStudents(formData) {
    return axios.post('/api/teacher/import-students', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },
}