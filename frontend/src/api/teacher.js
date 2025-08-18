// src/api/teacher.js
import apiClient from './axiosConfig'

export default {
  async importStudents(file) {
    const formData = new FormData()
    formData.append('file', file)

    return apiClient.post('/teacher/import-students', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  async getImportHistory() {
    return apiClient.get('/teacher/import-history')
  }
}
