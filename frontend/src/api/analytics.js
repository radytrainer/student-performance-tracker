import apiClient from './axiosConfig'

export default {
  getPerformanceTrends(studentId = null) {
    const endpoint = studentId 
      ? `/analytics/performance-trend/${studentId}`
      : '/analytics/performance-trend'
    return apiClient.get(endpoint)
  },
  getSubjectComparison() {
    return apiClient.get('/analytics/subject-comparison')
  },
  getClassPerformance() {
    return apiClient.get('/analytics/class-performance')
  },
  getStudentProgress(studentId) {
    return apiClient.get(`/analytics/student-progress/${studentId}`)
  }
}