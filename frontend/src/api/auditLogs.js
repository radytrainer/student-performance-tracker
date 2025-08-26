import axios from '@/api/axiosConfig'

export const auditLogsAPI = {
  list: (params = {}) => axios.get('/admin/audit-logs', { params }),
}
