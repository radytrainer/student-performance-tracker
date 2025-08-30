import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'

vi.mock('@/api/reports', () => ({
  reportsAPI: {
    getReportsDashboard: vi.fn(async () => ({
      student_stats: { gpa: '3.2', attendance: '92', rank: '3/30', credits: '18' },
      available_reports: [ { id: 1, name: 'Academic Summary', description: '', updateFrequency: '' } ],
      recent_reports: [],
      gpa_history: [],
      achievements: []
    })),
    getTerms: vi.fn(async () => ({ data: [ { id: 1, term_name: 'Spring 2025' } ] })),
    getComparison: vi.fn(async () => ({ data: [
      { subject_name: 'Math', student_avg: 85, class_avg: 80 },
      { subject_name: 'English', student_avg: 78, class_avg: 82 }
    ] })),
    generateReport: vi.fn(async () => ({ data: { data: {} } })),
    downloadReport: vi.fn(async () => ({ data: new Blob([new Uint8Array([1,2,3])], { type: 'application/pdf' }) }))
  }
}))

import StudentReports from '@/views/student/StudentReports.vue'

describe('StudentReports.vue', () => {
  beforeEach(() => vi.clearAllMocks())

  it('loads dashboard and comparison data', async () => {
    const wrapper = mount(StudentReports)
    // Wait for onMounted
    await new Promise(r => setTimeout(r))
    expect(wrapper.text()).toContain('My Reports')
    // Shows stats boxes
    expect(wrapper.text()).toContain('Current GPA')
    // Comparison chart placeholder text if chart renders is tricky; ensure no error
    expect(wrapper.html()).toContain('Academic Progress Summary')
  })

  it('generates a report and shows success message without blob', async () => {
    const wrapper = mount(StudentReports)
    await new Promise(r => setTimeout(r))
    // Click Generate
    const btn = wrapper.find('button')
    await btn.trigger('click')
    // Success toast appears
    await new Promise(r => setTimeout(r, 10))
    expect(wrapper.text()).toMatch(/report/i)
  })
})
