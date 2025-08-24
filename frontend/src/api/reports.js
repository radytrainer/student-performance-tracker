import axios from './axiosConfig'

export const reportsAPI = {
  // Get student reports dashboard data
  getReportsDashboard: async () => {
    const response = await axios.get('/student/reports')
    return response.data
  },

  // Generate a new report
  generateReport: async (reportConfig) => {
    const response = await axios.post('/student/reports/generate', reportConfig, {
      responseType: (reportConfig.format === 'pdf' || reportConfig.format === 'excel') ? 'blob' : 'json'
    })
    return response
  },

  // Get detailed report information
  getReportDetails: async (reportId) => {
    const response = await axios.get(`/student/reports/${reportId}`)
    return response.data
  },

  // Download an existing report
  downloadReport: async (reportId) => {
    const response = await axios.get(`/student/reports/${reportId}/download`, {
      responseType: 'blob'
    })
    return response
  },

  // Student vs class comparison (per subject)
  getComparison: async (params = {}) => {
    const response = await axios.get('/student/comparison', { params })
    return response.data
  }
}

export default reportsAPI
