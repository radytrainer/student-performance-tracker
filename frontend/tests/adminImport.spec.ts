import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'

vi.mock('@/api/admin', () => ({
  adminAPI: {
    getClasses: vi.fn(async () => ({ data: { data: { data: [] } } })),
    getSubjectsForImport: vi.fn(async () => ({ data: { data: [] } })),
    getImportHistory: vi.fn(async () => ({ data: { data: [] } })),
    getUploadedFiles: vi.fn(async () => ({ data: { data: { data: [] } } })),
    getGoogleStatus: vi.fn(async () => ({ data: { connected: false } })),
    getGoogleAuthUrl: vi.fn(async () => ({ data: { auth_url: 'http://example.com/auth' } })),
    previewGoogleSheet: vi.fn(async () => ({ data: { headers: ['first_name','last_name','email'], rows: [ { first_name:'John', last_name:'Doe', email:'john@example.com' } ] } })),
    importFromGoogle: vi.fn(async () => ({ data: { message: 'Import completed', data: { success_count: 1, error_count: 0, errors: [] } } }))
  }
}))

import AdminImport from '@/views/admin/AdminImport.vue'

describe('AdminImport.vue', () => {
  beforeEach(() => vi.clearAllMocks())

  it('mounts and shows Data Import header', async () => {
    const wrapper = mount(AdminImport)
    await new Promise(r => setTimeout(r))
    expect(wrapper.text()).toContain('Data Import')
    expect(wrapper.text()).toContain('Upload Student Data')
  })
})
