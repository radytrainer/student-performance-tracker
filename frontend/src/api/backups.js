import axios from '@/api/axiosConfig'

export const backupsAPI = {
  list: () => axios.get('/admin/backups'),
  download: (file) => axios.get('/admin/backups/download', { params: { file }, responseType: 'blob' }),
}
