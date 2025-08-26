import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'

vi.mock('@/api/notes', () => ({
  notesAPI: {
    list: vi.fn(async () => ({ data: { data: { data: [] } } })),
    create: vi.fn(async () => ({ data: { success: true } })),
    update: vi.fn(async () => ({ data: { success: true } })),
    remove: vi.fn(async () => ({ data: { success: true } }))
  }
}))
vi.mock('@/api/admin', () => ({
  adminAPI: {
    getStudents: vi.fn(async () => ({ data: { data: { data: [] } } })),
    getAvailableTeachers: vi.fn(async () => ({ data: { data: [] } }))
  }
}))

import AdminNotes from '@/views/admin/AdminNotes.vue'

describe('AdminNotes.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('mounts and shows empty notes', async () => {
    const wrapper = mount(AdminNotes)
    expect(wrapper.text()).toContain('Student Notes (Admin)')
    expect(wrapper.text()).toContain('No notes')
  })
})
