<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Survey Analytics Dashboard</h1>
            <p class="mt-2 text-gray-600">Comprehensive insights from your feedback surveys</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="refreshData"
              :disabled="isLoading"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
            >
              <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              {{ isLoading ? 'Refreshing...' : 'Refresh' }}
            </button>
            <button
              @click="exportData"
              :disabled="!analytics"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Export Data
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading && !analytics" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <span class="ml-4 text-gray-600">Loading analytics...</span>
      </div>

      <!-- Analytics Content -->
      <div v-else-if="analytics" class="space-y-8">
        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
          <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Total Forms</p>
                <p class="text-lg font-semibold text-gray-900">{{ analytics.overview.total_forms }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Active Forms</p>
                <p class="text-lg font-semibold text-gray-900">{{ analytics.overview.active_forms }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Total Responses</p>
                <p class="text-lg font-semibold text-gray-900">{{ analytics.overview.total_responses }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Average Score</p>
                <p class="text-lg font-semibold text-gray-900">{{ analytics.overview.average_score || 'N/A' }}/10</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Recent (30d)</p>
                <p class="text-lg font-semibold text-gray-900">{{ analytics.overview.recent_responses }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Key Insights -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Key Insights & Recommendations</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="p-4 bg-green-50 rounded-lg border border-green-200">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg class="w-5 h-5 text-green-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h4 class="text-sm font-medium text-green-800">Strong Performance</h4>
                  <p class="text-sm text-green-700 mt-1">
                    Your average score of {{ analytics.overview.average_score || 'N/A' }}/10 indicates excellent teaching quality.
                  </p>
                </div>
              </div>
            </div>

            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg class="w-5 h-5 text-blue-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h4 class="text-sm font-medium text-blue-800">Response Rate</h4>
                  <p class="text-sm text-blue-700 mt-1">
                    {{ analytics.overview.total_responses }} total responses across {{ analytics.overview.total_forms }} surveys.
                  </p>
                </div>
              </div>
            </div>

            <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg class="w-5 h-5 text-purple-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h4 class="text-sm font-medium text-purple-800">Recent Activity</h4>
                  <p class="text-sm text-purple-700 mt-1">
                    {{ analytics.overview.recent_responses }} responses in the last 30 days show active engagement.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Monthly Trend Chart -->
          <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Response Trend</h3>
            <div class="h-64" id="monthly-trend-chart">
              <canvas ref="monthlyTrendCanvas" width="400" height="200"></canvas>
            </div>
          </div>

          <!-- Score Distribution Chart -->
          <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Score Distribution</h3>
            <div class="h-64" id="score-distribution-chart">
              <canvas ref="scoreDistributionCanvas" width="400" height="200"></canvas>
            </div>
          </div>
        </div>

        <!-- Engagement Metrics -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200" v-if="analytics.engagement_metrics">
          <h3 class="text-lg font-medium text-gray-900 mb-6">Engagement Metrics</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
              <div class="text-3xl font-bold text-blue-600 mb-2">{{ analytics.engagement_metrics.average_completion_time || 0 }}s</div>
              <div class="text-sm text-gray-500 mb-4">Average Completion Time</div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" :style="`width: ${Math.min((analytics.engagement_metrics.average_completion_time || 0) / 300 * 100, 100)}%`"></div>
              </div>
            </div>
            
            <div class="text-center">
              <div class="text-3xl font-bold text-green-600 mb-2">{{ analytics.engagement_metrics.response_quality?.complete_responses || 0 }}</div>
              <div class="text-sm text-gray-500 mb-4">Complete Responses</div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full" style="width: 85%"></div>
              </div>
            </div>
            
            <div class="text-center">
              <div class="text-3xl font-bold text-orange-600 mb-2">{{ analytics.engagement_metrics.response_quality?.partial_responses || 0 }}</div>
              <div class="text-sm text-gray-500 mb-4">Partial Responses</div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-orange-600 h-2 rounded-full" style="width: 15%"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Performance Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Survey Performance Details</h3>
            <button
              @click="sortBy = sortBy === 'responses_count' ? 'average_score' : 'responses_count'"
              class="text-sm text-blue-600 hover:text-blue-800"
            >
              Sort by {{ sortBy === 'responses_count' ? 'Score' : 'Responses' }}
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Survey</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responses</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Score</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="form in sortedFormPerformance" :key="form.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ form.title }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                          :class="getSurveyTypeClass(form.survey_type)">
                      {{ form.survey_type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ form.responses_count }}
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <span class="text-sm text-gray-900 mr-2">{{ form.average_score || 'N/A' }}</span>
                      <div v-if="form.average_score" class="w-16 bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" :style="`width: ${(form.average_score / 10) * 100}%`"></div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500">
                    {{ formatDate(form.created_at) }}
                  </td>
                  <td class="px-6 py-4 text-sm font-medium">
                    <button 
                      @click="viewFormDetails(form.id)" 
                      class="text-blue-600 hover:text-blue-900 mr-3"
                    >
                      View Details
                    </button>
                    <button 
                      @click="exportFormData(form.id)" 
                      class="text-green-600 hover:text-green-900"
                    >
                      Export
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <div class="text-red-400 mb-4">
          <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to load analytics</h3>
        <p class="text-gray-500 mb-4">{{ error }}</p>
        <button @click="loadAnalytics" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          Retry
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import api from '@/api/axiosConfig'

// State
const analytics = ref(null)
const isLoading = ref(false)
const error = ref(null)
const sortBy = ref('responses_count')

// Computed
const sortedFormPerformance = computed(() => {
  if (!analytics.value?.form_performance) return []
  
  return [...analytics.value.form_performance].sort((a, b) => {
    if (sortBy.value === 'responses_count') {
      return (b.responses_count || 0) - (a.responses_count || 0)
    } else {
      return (b.average_score || 0) - (a.average_score || 0)
    }
  })
})

// Methods
const loadAnalytics = async () => {
  try {
    isLoading.value = true
    error.value = null
    
    const response = await api.get('/teacher/analytics')
    if (response.data.success) {
      analytics.value = response.data.data
      
      // Wait for DOM update then render charts
      await nextTick()
      renderCharts()
    }
  } catch (err) {
    console.error('Error loading analytics:', err)
    error.value = err.response?.data?.message || 'Failed to load analytics'
  } finally {
    isLoading.value = false
  }
}

const refreshData = () => {
  loadAnalytics()
}

const renderCharts = () => {
  if (!analytics.value) return
  
  // Simple chart implementations (you can replace with Chart.js or similar)
  renderMonthlyTrendChart()
  renderScoreDistributionChart()
}

const renderMonthlyTrendChart = () => {
  // Simple implementation - replace with actual charting library
  console.log('Rendering monthly trend chart with data:', analytics.value.monthly_trend)
}

const renderScoreDistributionChart = () => {
  // Simple implementation - replace with actual charting library
  console.log('Rendering score distribution chart')
}

const getSurveyTypeClass = (type) => {
  switch (type) {
    case 'weekly':
      return 'bg-purple-100 text-purple-800'
    case 'monthly':
      return 'bg-indigo-100 text-indigo-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}

const viewFormDetails = (formId) => {
  // Navigate to form-specific analytics
  console.log('View details for form:', formId)
}

const exportFormData = (formId) => {
  console.log('Export data for form:', formId)
}

const exportData = () => {
  if (!analytics.value) return
  
  // Create CSV data
  const csvData = [
    ['Survey Title', 'Type', 'Responses', 'Average Score', 'Created Date'],
    ...analytics.value.form_performance.map(form => [
      form.title,
      form.survey_type,
      form.responses_count,
      form.average_score || 'N/A',
      formatDate(form.created_at)
    ])
  ]
  
  // Convert to CSV
  const csvContent = csvData.map(row => row.join(',')).join('\n')
  
  // Download file
  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `survey-analytics-${new Date().toISOString().split('T')[0]}.csv`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  window.URL.revokeObjectURL(url)
}

// Lifecycle
onMounted(() => {
  loadAnalytics()
})
</script>
