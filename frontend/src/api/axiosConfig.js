// src/api/axiosConfig.js
import axios from 'axios'

// Create axios instance with base configuration
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  withCredentials: true, // Important for CSRF cookies
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Function to get CSRF token
const getCsrfToken = async () => {
  try {
    await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
      withCredentials: true
    })
  } catch (error) {
    console.warn('Failed to get CSRF token:', error)
  }
}

// Request interceptor
apiClient.interceptors.request.use(
  async (config) => {
    // Get CSRF token for state-changing operations
    if (['post', 'put', 'patch', 'delete'].includes(config.method?.toLowerCase())) {
      await getCsrfToken()
    }
    
    // Add authorization token if available
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// ✅ Response interceptor - handle common API errors
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      switch (error.response.status) {
        case 401:
          console.warn('Unauthorized – maybe token expired?')
          // Redirect to login or show toast
          break
        case 403:
          console.warn('Forbidden – no permission')
          break
        case 404:
          console.warn('Not found')
          break
        case 422:
          return Promise.reject(error.response.data.errors)
        case 500:
          console.error('Server error')
          break
        default:
          break
      }
    }
    return Promise.reject(error)
  }
)

export { getCsrfToken }
export default apiClient
