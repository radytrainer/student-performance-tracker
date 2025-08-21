import api from './axiosConfig'

export const notificationsAPI = {
  list: async (params = {}) => {
    return await api.get('/notifications', { params })
  },
  markRead: async (ids = []) => {
    return await api.post('/notifications/mark-read', { ids })
  }
}

export default notificationsAPI
