import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'

vi.mock('@/api/admin', () => ({
  adminAPI: {
    getStudents: vi.fn(async () => ({ data: { data: {
      data: [
        {
          user_id: 10,
          full_name: 'Student One',
          student_code: 'STU20250001',
          current_class: { class_name: '9th Grade A' },
          current_gpa: 3.4,
          attendance_rate: 92,
          user: { is_active: true, email: 'student1@school.com' }
        }
      ],
      total: 1, last_page: 1, current_page: 1, per_page: 15, from: 1, to: 1
    } } })),
    deleteStudent: vi.fn(async () => ({ data: { success: true } })),
    bulkStudentAction: vi.fn(async () => ({ data: { success: true } })),
    getClasses: vi.fn(async () => ({ data: { data: [] } }))
  }
}))

import AdminStudents from '@/views/admin/AdminStudents.vue'

describe('AdminStudents.vue', () => {
  beforeEach(() => vi.clearAllMocks())

  it('renders students page and shows one student', async () => {
    const wrapper = mount(AdminStudents)
    await new Promise(r => setTimeout(r))
    expect(wrapper.text()).toContain('Student Management')
    expect(wrapper.text()).toContain('Student One')
    expect(wrapper.text()).toContain('Showing 1 to 1 of 1 results')
  })
})
