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
      responseType: reportConfig.format === 'pdf' ? 'blob' : 'json'
    })
    return response
  },

  // Download an existing report
  downloadReport: async (reportId) => {
    const response = await axios.get(`/student/reports/${reportId}/download`, {
      responseType: 'blob'
    })
    return response
  }
}

export default reportsAPI
