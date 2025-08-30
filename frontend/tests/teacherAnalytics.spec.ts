import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'

vi.mock('@/api/axiosConfig', () => ({
  default: {
    get: vi.fn(async (url: string) => {
      if (url.includes('/teacher/analytics')) {
        return { data: { success: true, data: {
          overview: { total_forms: 3, active_forms: 2, total_responses: 45, average_score: 8.1, recent_responses: 12 },
          monthly_trend: [
            { month: 'Jan', responses_count: 10, average_score: 7.5 },
            { month: 'Feb', responses_count: 12, average_score: 8.0 }
          ],
          form_performance: [
            { id: 1, title: 'Weekly Survey', survey_type: 'weekly', responses_count: 20, average_score: 8.4, created_at: '2025-01-10' },
            { id: 2, title: 'Midterm', survey_type: 'monthly', responses_count: 25, average_score: 7.8, created_at: '2025-02-05' }
          ],
          engagement_metrics: { average_completion_time: 120, response_quality: { complete_responses: 40, partial_responses: 5 } }
        } } }
      }
      return { data: { success: true } }
    })
  }
}))

// Stub Chart.js to avoid canvas context errors
vi.mock('chart.js', () => ({
  Chart: class { constructor() {} destroy() {} static register() {} },
  BarController: class {}, BarElement: class {}, CategoryScale: class {}, LinearScale: class {}, ArcElement: class {}, Tooltip: class {}, Legend: class {}, LineController: class {}, LineElement: class {}, PointElement: class {}
}))

import TeacherAnalytics from '@/views/teacher/TeacherAnalytics.vue'

describe('TeacherAnalytics.vue', () => {
  beforeEach(() => vi.clearAllMocks())

  it('renders overview and loads charts data', async () => {
    const wrapper = mount(TeacherAnalytics)
    await new Promise(r => setTimeout(r))
    expect(wrapper.text()).toContain('Survey Analytics Dashboard')
    expect(wrapper.text()).toContain('Total Forms')
    expect(wrapper.text()).toContain('Average Score')
  })
})
