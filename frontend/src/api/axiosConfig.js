import axios from 'axios'

// Create axios instance with base configuration
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  withCredentials: true, // Required if using Laravel Sanctum for authentication
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Request interceptor
apiClient.interceptors.request.use(
  (config) => {
    // Add authorization token if available
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
apiClient.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // Handle common error responses
    if (error.response) {
      switch (error.response.status) {
        case 401:
          // Handle unauthorized access (redirect to login)
          break
        case 403:
          // Handle forbidden access
          break
        case 404:
          // Handle not found errors
          break
        case 422:
          // Handle validation errors (return for form handling)
          return Promise.reject(error.response.data.errors)
        case 500:
          // Handle server errors
          break
        default:
          // Handle other errors
          break
      }
    }
    return Promise.reject(error)
  }
)

export default apiClient