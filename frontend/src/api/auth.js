import apiClient from './axiosConfig'

export default {
  login(credentials) {
    return apiClient.post('/auth/login', credentials)
  },
  logout() {
    return apiClient.post('/auth/logout')
  },
  getUser() {
    return apiClient.get('/auth/user')
  }
}