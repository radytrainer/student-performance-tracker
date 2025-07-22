<template>
  <div class="p-6 max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-4xl font-bold text-gray-900">Survey Assignments</h1>
      <div class="text-sm text-gray-600">
        Completion Rate: {{ stats.completion_rate || 0 }}% 
        ({{ stats.completed_assignments || 0 }}/{{ stats.total_assignments || 0 }})
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100 text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Surveys</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_assignments || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100 text-green-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Completed</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.completed_assignments || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Pending</p>
            <p class="text-2xl font-semibold text-gray-900">{{ (stats.total_assignments || 0) - (stats.completed_assignments || 0) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-purple-100 text-purple-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Avg Score</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.average_score ? stats.average_score.toFixed(1) : 'N/A' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Survey List -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div 
        v-for="survey in surveys" 
        :key="survey.id"
        class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-200"
      >
        <div class="p-6">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-xl font-semibold text-gray-900">{{ survey.title }}</h3>
              <p class="text-sm text-gray-600 mt-1">{{ survey.class_name }}</p>
            </div>
            <span 
              :class="survey.is_completed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
              class="px-3 py-1 rounded-full text-xs font-medium"
            >
              {{ survey.is_completed ? 'Completed' : 'Pending' }}
            </span>
          </div>

          <p class="text-gray-700 text-sm mb-4">{{ survey.description }}</p>

          <!-- Survey Details -->
          <div class="space-y-2 mb-4 text-sm text-gray-600">
            <div class="flex justify-between">
              <span>Type:</span>
              <span class="capitalize">{{ survey.survey_type }}</span>
            </div>
            <div v-if="survey.due_date" class="flex justify-between">
              <span>Due Date:</span>
              <span>{{ formatDate(survey.due_date) }}</span>
            </div>
            <div v-if="survey.is_completed" class="flex justify-between">
              <span>Completed:</span>
              <span>{{ formatDate(survey.completed_at) }}</span>
            </div>
            <div v-if="survey.average_score" class="flex justify-between">
              <span>Score:</span>
              <span class="font-medium">{{ survey.average_score }}/10</span>
            </div>
          </div>

          <!-- Instructions -->
          <div v-if="survey.instructions" class="bg-gray-50 p-3 rounded-md mb-4">
            <p class="text-sm text-gray-700">{{ survey.instructions }}</p>
          </div>

          <!-- Action Button -->
          <div class="flex justify-end">
            <button
              v-if="!survey.is_completed"
              @click="openSurvey(survey)"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
            >
              Take Survey
            </button>
            <button
              v-else
              @click="viewSurveyDetails(survey)"
              class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-200"
            >
              View Details
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && surveys.length === 0" class="text-center py-12">
      <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Surveys Available</h3>
      <p class="text-gray-600">You don't have any survey assignments at the moment.</p>
    </div>

    <!-- Survey Modal -->
    <SurveyModal
      v-if="selectedSurvey"
      :survey="selectedSurvey"
      @close="closeSurveyModal"
      @completed="handleSurveyCompleted"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axiosConfig'
import SurveyModal from '@/components/student/SurveyModal.vue'

const surveys = ref([])
const stats = ref({})
const loading = ref(false)
const selectedSurvey = ref(null)

const loadSurveys = async () => {
  try {
    loading.value = true
    const response = await api.get('/student/surveys')
    surveys.value = response.data.data
  } catch (error) {
    console.error('Error loading surveys:', error)
    alert('Failed to load surveys. Please try again.')
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  try {
    const response = await api.get('/student/survey-stats')
    stats.value = response.data.data
  } catch (error) {
    console.error('Error loading stats:', error)
  }
}

const openSurvey = async (survey) => {
  try {
    // Get detailed survey information
    const response = await api.get(`/student/surveys/${survey.id}`)
    selectedSurvey.value = response.data.data
  } catch (error) {
    console.error('Error loading survey details:', error)
    alert('Failed to load survey details. Please try again.')
  }
}

const viewSurveyDetails = async (survey) => {
  try {
    const response = await api.get(`/student/surveys/${survey.id}`)
    selectedSurvey.value = response.data.data
  } catch (error) {
    console.error('Error loading survey details:', error)
    alert('Failed to load survey details. Please try again.')
  }
}

const closeSurveyModal = () => {
  selectedSurvey.value = null
}

const handleSurveyCompleted = () => {
  // Refresh surveys and stats after completion
  loadSurveys()
  loadStats()
  closeSurveyModal()
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

onMounted(() => {
  loadSurveys()
  loadStats()
})
</script>
