// src/api/studentGrade.js
import apiClient from './axiosConfig'

export default {
  getAllGrades(params = {}) {
    return apiClient.get('/student/grades', { params }) // must match backend route exactly
  },
  getGrade(id) {
    return apiClient.get(`/student/grades/${id}`)
  },
  createGrade(GradeData) {
    return apiClient.post('/student/grades', GradeData)
  },
  updateGrade(id, GradeData) {
    return apiClient.put(`/student/grades/${id}`, GradeData)
  },
  deleteGrade(id) {
    return apiClient.delete(`/student/grades/${id}`)
  },
}



