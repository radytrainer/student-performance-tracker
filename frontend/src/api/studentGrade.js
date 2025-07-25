// src/api/studentGrade.js
import apiClient from './axiosConfig'

export default {
  // GET /api/student/grades
  getAllGrades(params = {}) {
    return apiClient.get('/student/grades', { params })
  },

  // These next endpoints will only work if you define them in the backend!
  getGrade(id) {
    return apiClient.get(`/student/grades/${id}`)
  },

  createGrade(data) {
    return apiClient.post('/student/grades', data)
  },

  updateGrade(id, data) {
    return apiClient.put(`/student/grades/${id}`, data)
  },

  deleteGrade(id) {
    return apiClient.delete(`/student/grades/${id}`)
  },
}
