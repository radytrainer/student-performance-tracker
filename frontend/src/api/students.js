import apiClient from './axiosConfig'

export default {
  getAllStudents(params = {}) {
    return apiClient.get('/students', { params })
  },
  getStudent(id) {
    return apiClient.get(`/students/${id}`)
  },
  createStudent(studentData) {
    return apiClient.post('/students', studentData)
  },
  updateStudent(id, studentData) {
    return apiClient.put(`/students/${id}`, studentData)
  },
  deleteStudent(id) {
    return apiClient.delete(`/students/${id}`)
  }
}