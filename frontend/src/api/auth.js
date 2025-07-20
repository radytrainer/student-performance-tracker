// Temporarily using mock API for testing
// Switch back to real API when backend is ready
import mockAuth from './mockAuth'

export default mockAuth

// Original API (uncomment when backend is ready):
/*
import apiClient from './axiosConfig'

export default {
  login(credentials) {
    return apiClient.post('/auth/login', credentials)
  },
  register(userData) {
    return apiClient.post('/auth/register', userData)
  },
  logout() {
    return apiClient.post('/auth/logout')
  },
  getUser() {
    return apiClient.get('/auth/user')
  }
}
*/