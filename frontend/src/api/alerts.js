import axios from '@/api/axiosConfig'

export const alertsAPI = {
  // Admin or teacher (teacher routes available for GET/PUT)
  list: (params = {}, role = 'admin') => {
    const base = role === 'teacher' ? '/teacher/alerts' : '/admin/alerts'
    return axios.get(base, { params })
  },
  create: (data) => axios.post('/admin/alerts', data),
  update: (id, data, role = 'admin') => {
    const base = role === 'teacher' ? '/teacher/alerts' : '/admin/alerts'
    return axios.put(`${base}/${id}`, data)
  },
  generate: (termId) => axios.post('/admin/alerts/generate', { term_id: termId }),
}
