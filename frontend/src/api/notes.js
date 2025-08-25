import axios from '@/api/axiosConfig'

export const notesAPI = {
  list: (params = {}, role = 'admin') => {
    const base = role === 'teacher' ? '/teacher/notes' : '/admin/notes'
    return axios.get(base, { params })
  },
  create: (data, role = 'teacher') => {
    const base = role === 'teacher' ? '/teacher/notes' : '/admin/notes'
    return axios.post(base, data)
  },
  update: (id, data, role = 'teacher') => {
    const base = role === 'teacher' ? '/teacher/notes' : '/admin/notes'
    return axios.put(`${base}/${id}`, data)
  },
  remove: (id, role = 'teacher') => {
    const base = role === 'teacher' ? '/teacher/notes' : '/admin/notes'
    return axios.delete(`${base}/${id}`)
  },
}
