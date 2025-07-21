<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Feedback Surveys</h1>
            <p class="mt-2 text-gray-600">Complete your assigned feedback surveys</p>
          </div>
          <div class="flex items-center space-x-4">
            <!-- Auto-refresh indicator -->
            <div class="flex items-center text-sm text-gray-500">
              <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse mr-2"></div>
              <span>Auto-refresh in {{ refreshCountdown }}s</span>
            </div>
            <!-- Manual refresh button -->
            <button
              @click="refreshData"
              :disabled="isLoading"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              {{ isLoading ? 'Refreshing...' : 'Refresh' }}
            </button>
            <!-- Last updated -->
            <div class="text-xs text-gray-400">
              Last updated: {{ formatLastRefresh() }}
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" v-if="stats">
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Surveys</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ stats.total_assignments }}</dd>
              </dl>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ stats.completed_assignments }}</dd>
              </dl>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Completion Rate</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ stats.completion_rate }}%</dd>
              </dl>
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
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Average Score</dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ stats.average_score ? stats.average_score.toFixed(1) : 'N/A' }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
              ]"
            >
              {{ tab.name }}
              <span
                v-if="tab.count !== undefined"
                :class="[
                  activeTab === tab.id
                    ? 'bg-blue-100 text-blue-600'
                    : 'bg-gray-100 text-gray-900',
                  'ml-2 inline-block py-0.5 px-2 text-xs rounded-full'
                ]"
              >
                {{ tab.count }}
              </span>
            </button>
          </nav>
        </div>
      </div>

      <!-- Surveys List -->
      <div class="space-y-4">
        <div
          v-for="survey in filteredSurveys"
          :key="survey.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200"
          :class="getCardBorderClass(survey)"
        >
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                  <h3 class="text-lg font-semibold text-gray-900">{{ survey.title }}</h3>
                  <span
                    :class="getStatusBadgeClass(survey)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  >
                    <span class="w-2 h-2 rounded-full mr-1.5" :class="getStatusDotClass(survey)"></span>
                    {{ getStatusText(survey) }}
                  </span>
                  <span
                    :class="getSurveyTypeClass(survey.survey_type)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                  >
                    {{ survey.survey_type }}
                  </span>
                  <span
                    v-if="isOverdue(survey)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                  >
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Overdue
                  </span>
                </div>
                
                <p class="text-gray-600 mb-3">{{ survey.description }}</p>
                
                <div class="flex items-center space-x-6 text-sm text-gray-500">
                  <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H7m2 0v-4a2 2 0 012-2h2a2 2 0 012 2v4"></path>
                    </svg>
                    Class: {{ survey.class_name }}
                  </span>
                  
                  <span v-if="survey.due_date" class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Due: {{ formatDate(survey.due_date) }} 
                    <span v-if="!survey.is_completed" :class="isOverdue(survey) ? 'text-red-600 font-medium' : isDueSoon(survey) ? 'text-orange-600 font-medium' : 'text-gray-600'"
                          class="ml-1">
                      ({{ getTimeRemaining(survey) }})
                    </span>
                  </span>
                  
                  <span v-if="survey.is_completed" class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Completed: {{ formatDate(survey.completed_at) }}
                  </span>
                  
                  <span v-if="survey.average_score" class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                    Score: {{ survey.average_score.toFixed(1) }}/10
                  </span>
                </div>

                <!-- Instructions -->
                <div v-if="survey.instructions" class="mt-3 p-3 bg-blue-50 rounded-lg">
                  <p class="text-sm text-blue-800">
                    <strong>Instructions:</strong> {{ survey.instructions }}
                  </p>
                </div>
              </div>
              
              <div class="ml-6 flex-shrink-0">
                <button
                  v-if="!survey.is_completed"
                  @click="takeSurvey(survey)"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
                >
                  Take Survey
                </button>
                <button
                  v-else
                  @click="viewResponse(survey)"
                  class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200"
                >
                  View Response
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredSurveys.length === 0" class="text-center py-12">
          <div class="text-gray-400 mb-4">
            <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No surveys found</h3>
          <p class="text-gray-500">
            {{ activeTab === 'pending' ? 'You have no pending surveys.' : 'You have no completed surveys.' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Survey Modal -->
    <SurveyModal
      v-if="showSurveyModal"
      :survey="selectedSurvey"
      @close="showSurveyModal = false"
      @completed="onSurveyCompleted"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import SurveyModal from '@/components/student/SurveyModal.vue'

const authStore = useAuthStore()

// State
const surveys = ref([])
const stats = ref(null)
const activeTab = ref('all')
const showSurveyModal = ref(false)
const selectedSurvey = ref(null)
const isLoading = ref(false)
const lastRefresh = ref(new Date())
const autoRefreshInterval = ref(null)
const refreshCountdown = ref(30)

// Computed
const tabs = computed(() => [
  { id: 'all', name: 'All Surveys', count: surveys.value.length },
  { id: 'pending', name: 'Pending', count: surveys.value.filter(s => !s.is_completed).length },
  { id: 'completed', name: 'Completed', count: surveys.value.filter(s => s.is_completed).length }
])

const filteredSurveys = computed(() => {
  switch (activeTab.value) {
    case 'pending':
      return surveys.value.filter(survey => !survey.is_completed)
    case 'completed':
      return surveys.value.filter(survey => survey.is_completed)
    default:
      return surveys.value
  }
})

// Methods
const loadSurveys = async () => {
  try {
    isLoading.value = true
    const response = await fetch('http://localhost:8000/api/student/surveys', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      surveys.value = data.data || []
    }
  } catch (error) {
    console.error('Error loading surveys:', error)
  } finally {
    isLoading.value = false
  }
}

const loadStats = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/student/survey-stats', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      stats.value = data.data
    }
  } catch (error) {
    console.error('Error loading stats:', error)
  }
}

const takeSurvey = (survey) => {
  selectedSurvey.value = survey
  showSurveyModal.value = true
}

const viewResponse = (survey) => {
  selectedSurvey.value = survey
  showSurveyModal.value = true
}

const onSurveyCompleted = (completionData) => {
  // Update the local survey data immediately for real-time feedback
  if (completionData && selectedSurvey.value) {
    const surveyIndex = surveys.value.findIndex(s => s.id === selectedSurvey.value.id)
    if (surveyIndex !== -1) {
      surveys.value[surveyIndex] = {
        ...surveys.value[surveyIndex],
        is_completed: true,
        completed_at: completionData.submitted_at,
        average_score: completionData.average_score
      }
    }
  }
  
  showSurveyModal.value = false
  
  // Refresh data from server to ensure consistency
  setTimeout(() => {
    loadSurveys()
    loadStats()
  }, 500)
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

const getStatusBadgeClass = (survey) => {
  if (survey.is_completed) {
    return 'bg-green-100 text-green-800'
  }
  if (isOverdue(survey)) {
    return 'bg-red-100 text-red-800'
  }
  if (isDueSoon(survey)) {
    return 'bg-orange-100 text-orange-800'
  }
  return 'bg-yellow-100 text-yellow-800'
}

const getStatusDotClass = (survey) => {
  if (survey.is_completed) {
    return 'bg-green-500'
  }
  if (isOverdue(survey)) {
    return 'bg-red-500'
  }
  if (isDueSoon(survey)) {
    return 'bg-orange-500'
  }
  return 'bg-yellow-500'
}

const getStatusText = (survey) => {
  if (survey.is_completed) {
    return 'Completed'
  }
  if (isOverdue(survey)) {
    return 'Overdue'
  }
  if (isDueSoon(survey)) {
    return 'Due Soon'
  }
  return 'Pending'
}

const getCardBorderClass = (survey) => {
  if (isOverdue(survey)) {
    return 'border-l-4 border-l-red-500'
  }
  if (isDueSoon(survey)) {
    return 'border-l-4 border-l-orange-500'
  }
  if (survey.is_completed) {
    return 'border-l-4 border-l-green-500'
  }
  return ''
}

const isOverdue = (survey) => {
  if (!survey.due_date || survey.is_completed) return false
  return new Date(survey.due_date) < new Date()
}

const isDueSoon = (survey) => {
  if (!survey.due_date || survey.is_completed) return false
  const dueDate = new Date(survey.due_date)
  const now = new Date()
  const diffTime = dueDate - now
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays <= 3 && diffDays > 0
}

const getTimeRemaining = (survey) => {
  if (!survey.due_date || survey.is_completed) return ''
  const dueDate = new Date(survey.due_date)
  const now = new Date()
  const diffTime = dueDate - now
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays < 0) return 'Overdue'
  if (diffDays === 0) return 'Due Today'
  if (diffDays === 1) return 'Due Tomorrow'
  return `${diffDays} days remaining`
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}

const formatLastRefresh = () => {
  return lastRefresh.value.toLocaleTimeString()
}

const refreshData = async () => {
  lastRefresh.value = new Date()
  await Promise.all([loadSurveys(), loadStats()])
  resetRefreshCountdown()
}

const resetRefreshCountdown = () => {
  refreshCountdown.value = 30
}

const startAutoRefresh = () => {
  // Clear any existing interval
  if (autoRefreshInterval.value) {
    clearInterval(autoRefreshInterval.value)
  }
  
  // Start countdown timer
  autoRefreshInterval.value = setInterval(() => {
    refreshCountdown.value--
    
    if (refreshCountdown.value <= 0) {
      refreshData()
    }
  }, 1000)
}

const stopAutoRefresh = () => {
  if (autoRefreshInterval.value) {
    clearInterval(autoRefreshInterval.value)
    autoRefreshInterval.value = null
  }
}

// Lifecycle
onMounted(() => {
  loadSurveys()
  loadStats()
  startAutoRefresh()
})

// Cleanup on unmount
onUnmounted(() => {
  stopAutoRefresh()
})
</script>
