import apiClient from './axiosConfig'

export default {
  async login(credentials) {
    const response = await apiClient.post('/auth/login', credentials)
    if (response.data.token) {
      localStorage.setItem('auth_token', response.data.token)
    }
    return response
  },
  
  async register(userData) {
    // Add password_confirmation field for Laravel validation
    const registrationData = {
      ...userData,
      password_confirmation: userData.password
    }
    const response = await apiClient.post('/auth/register', registrationData)
    if (response.data.token) {
      localStorage.setItem('auth_token', response.data.token)
    }
    return response
  },
  
  async logout() {
    try {
      await apiClient.post('/auth/logout')
    } finally {
      localStorage.removeItem('auth_token')
    }
  },
  
  async getUser() {
    return apiClient.get('/auth/user')
  }
}