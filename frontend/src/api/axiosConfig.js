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
    await axios.create({
      baseURL: 'http://localhost:8000',
      withCredentials: true
    }).get('/sanctum/csrf-cookie')
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
// Remove duplicate token interceptor (already handled above)

export { getCsrfToken }
export default apiClient