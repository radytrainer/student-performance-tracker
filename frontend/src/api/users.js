import axios from './axiosConfig'

export const usersAPI = {
  // Get all users with optional filters
  getUsers(params = {}) {
    return axios.get('/users', { params })
  },

  // Get a specific user by ID
  getUser(id) {
    return axios.get(`/users/${id}`)
  },

  // Create a new user
  createUser(userData) {
    const formData = new FormData()
    
    // Add all user data to FormData
    Object.keys(userData).forEach(key => {
      if (userData[key] !== null && userData[key] !== undefined) {
        formData.append(key, userData[key])
      }
    })

    return axios.post('/users', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  // Update an existing user
  updateUser(id, userData) {
    const formData = new FormData()
    
    // Add all user data to FormData
    Object.keys(userData).forEach(key => {
      if (userData[key] !== null && userData[key] !== undefined) {
        formData.append(key, userData[key])
      }
    })

    // Laravel doesn't support PUT with FormData, so we use POST with _method
    formData.append('_method', 'PUT')

    return axios.post(`/users/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  // Delete a user
  deleteUser(id) {
    return axios.delete(`/users/${id}`)
  },

  // Toggle user status
  toggleUserStatus(id, isActive) {
    return axios.patch(`/users/${id}/status`, { is_active: isActive })
  },

  // Get users by role
  getUsersByRole(role) {
    return axios.get('/users', { params: { role } })
  },

  // Search users
  searchUsers(query, filters = {}) {
    return axios.get('/users', { 
      params: { 
        search: query,
        ...filters 
      } 
    })
  }
}

export default usersAPI
