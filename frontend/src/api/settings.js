import axios from '@/api/axiosConfig'

export const settingsAPI = {
  get: () => axios.get('/admin/settings'),
  save: (data) => axios.put('/admin/settings', data),
  reset: () => axios.post('/admin/settings/reset'),
  initiateBackup: () => axios.post('/admin/settings/backup'),
  runMaintenance: () => axios.post('/admin/settings/maintenance'),
}
