import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'

vi.mock('@/api/users', () => ({
  usersAPI: {
    getUsers: vi.fn(async () => ({ data: {
      data: [
        { id: 1, first_name: 'Alice', last_name: 'Admin', email: 'alice@school.com', role: 'admin', is_active: true },
        { id: 2, first_name: 'Tom', last_name: 'Teacher', email: 'tom@school.com', role: 'teacher', is_active: true }
      ],
      total: 2, last_page: 1, current_page: 1, per_page: 15, from: 1, to: 2
    } }))
  }
}))

vi.mock('@/api/admin', () => ({
  adminAPI: {
    resetUserPassword: vi.fn(),
    updateUserRole: vi.fn(),
    getUserAccessLogs: vi.fn(async () => ({ data: { data: [] } })),
    bulkUpdateUserStatus: vi.fn(async () => ({ data: { success: true } }))
  }
}))

import AdminUsers from '@/views/admin/AdminUsers.vue'

describe('AdminUsers.vue', () => {
  beforeEach(() => vi.clearAllMocks())

  it('renders users and shows pagination', async () => {
    const wrapper = mount(AdminUsers)
    await new Promise(r => setTimeout(r))
    expect(wrapper.text()).toContain('User Management')
    expect(wrapper.text()).toContain('Alice Admin')
    expect(wrapper.text()).toContain('Showing 1 to 2 of 2 results')
  })
})
