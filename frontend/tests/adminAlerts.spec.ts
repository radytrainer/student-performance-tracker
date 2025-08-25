import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'

vi.mock('@/api/alerts', () => ({
  alertsAPI: {
    list: vi.fn(async () => ({ data: { data: { data: [] } } })),
    update: vi.fn(async () => ({ data: { success: true } })),
    generate: vi.fn(async () => ({ data: { success: true } }))
  }
}))
vi.mock('@/api/reports', () => ({
  reportsAPI: {
    getTerms: vi.fn(async () => ({ data: [] }))
  }
}))

import AdminAlerts from '@/views/admin/AdminAlerts.vue'

describe('AdminAlerts.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('mounts and shows empty state', async () => {
    const wrapper = mount(AdminAlerts)
    expect(wrapper.text()).toContain('Performance Alerts')
    expect(wrapper.text()).toContain('No alerts')
  })
})
