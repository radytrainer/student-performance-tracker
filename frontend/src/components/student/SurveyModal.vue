<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ survey?.title }}</h3>
            <p class="text-sm text-gray-500">{{ survey?.class_name }} • {{ survey?.survey_type }} Survey</p>
          </div>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <div class="p-6 overflow-y-auto max-h-96">
        <!-- Survey Description -->
        <div v-if="survey?.description" class="mb-6 p-4 bg-blue-50 rounded-lg">
          <p class="text-blue-800">{{ survey.description }}</p>
        </div>

        <!-- Instructions -->
        <div v-if="survey?.instructions" class="mb-6 p-4 bg-yellow-50 rounded-lg">
          <h4 class="font-medium text-yellow-900 mb-2">Special Instructions:</h4>
          <p class="text-yellow-800">{{ survey.instructions }}</p>
        </div>

        <!-- Due Date Warning -->
        <div v-if="survey?.due_date && !survey?.is_completed" class="mb-6 p-4 bg-orange-50 rounded-lg">
          <div class="flex items-center">
            <svg class="w-5 h-5 text-orange-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L5.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <p class="text-orange-800">
              <strong>Due Date:</strong> {{ formatDate(survey.due_date) }}
            </p>
          </div>
        </div>

        <!-- Completed Survey Info -->
        <div v-if="survey?.is_completed" class="mb-6">
          <div class="bg-green-50 rounded-lg p-4 mb-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <p class="text-green-800">
                <strong>Survey Completed</strong> on {{ formatDate(survey.completed_at) }}
              </p>
            </div>
            <div v-if="survey.average_score" class="mt-2">
              <p class="text-green-800">Your average score: {{ survey.average_score.toFixed(1) }}/10</p>
            </div>
          </div>
        </div>

        <!-- Google Form Integration -->
        <div v-if="!survey?.is_completed" class="space-y-4">
          <div class="text-center">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Complete Your Feedback Survey</h4>
            <p class="text-gray-600 mb-6">
              Click the button below to open the Google Form and complete your feedback survey.
              Once you submit the form, return here to mark it as completed.
            </p>
            
            <button
              @click="openGoogleForm"
              :disabled="isProcessing"
              class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 mb-4"
            >
              <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
              </svg>
              Open Survey Form
            </button>
          </div>

          <!-- Manual Score Entry (Fallback) -->
          <div class="border-t pt-6">
            <h5 class="font-medium text-gray-900 mb-4">Manual Completion Entry</h5>
            <p class="text-sm text-gray-600 mb-4">
              If you've completed the survey, you can manually enter your responses here:
            </p>
            
            <form @submit.prevent="submitManualCompletion" class="space-y-4">
              <!-- Sample Questions (5 questions with 1-10 scale) -->
              <div v-for="(question, index) in sampleQuestions" :key="index" class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">
                  {{ question.text }}
                </label>
                <select
                  v-model="manualResponses[`question_${index + 1}`]"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                >
                  <option value="">Select a score (1-10)</option>
                  <option v-for="score in 10" :key="score" :value="score">
                    {{ score }} - {{ getScoreLabel(score) }}
                  </option>
                </select>
              </div>

              <!-- Google Response ID (Optional) -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Google Form Response ID (Optional)
                </label>
                <input
                  v-model="manualResponses.google_response_id"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Enter response ID if available"
                />
                <p class="text-xs text-gray-500 mt-1">
                  You can find this in the Google Form confirmation page
                </p>
              </div>

              <div class="flex justify-end space-x-3 pt-4">
                <button
                  type="button"
                  @click="$emit('close')"
                  class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="isProcessing || !isFormValid"
                  class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                >
                  {{ isProcessing ? 'Submitting...' : 'Mark as Completed' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- View Completed Response -->
        <div v-else class="space-y-4">
          <h4 class="text-lg font-medium text-gray-900">Your Survey Response</h4>
          <div v-if="survey?.response_data" class="space-y-3">
            <div
              v-for="(value, key) in survey.response_data"
              :key="key"
              class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
            >
              <span class="font-medium text-gray-700">{{ formatQuestionKey(key) }}</span>
              <span class="text-gray-900">{{ value }}/10</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import api from '@/api/axiosConfig'

const props = defineProps({
  survey: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'completed'])

// State
const isProcessing = ref(false)
const manualResponses = reactive({
  question_1: '',
  question_2: '',
  question_3: '',
  question_4: '',
  question_5: '',
  google_response_id: ''
})

// Sample questions for manual entry
const sampleQuestions = [
  { text: "How would you rate the overall teaching quality?" },
  { text: "How clear and understandable are the lesson explanations?" },
  { text: "How effectively does the teacher engage with students?" },
  { text: "How well does the teacher provide feedback on your work?" },
  { text: "How would you rate your overall learning experience?" }
]

// Computed
const isFormValid = computed(() => {
  return Object.keys(manualResponses).filter(key => key !== 'google_response_id')
    .every(key => manualResponses[key] !== '')
})

// Methods
const openGoogleForm = () => {
  if (props.survey?.google_form_url) {
    window.open(props.survey.google_form_url, '_blank')
  }
}

const submitManualCompletion = async () => {
  try {
    isProcessing.value = true
    const startTime = Date.now()

    // Validate all responses before submission
    const responses = [
      manualResponses.question_1,
      manualResponses.question_2,
      manualResponses.question_3,
      manualResponses.question_4,
      manualResponses.question_5
    ]

    if (responses.some(response => !response || response < 1 || response > 10)) {
      alert('Please ensure all questions are answered with values between 1 and 10')
      return
    }

    const responseData = {
      question_1: parseInt(manualResponses.question_1),
      question_2: parseInt(manualResponses.question_2),
      question_3: parseInt(manualResponses.question_3),
      question_4: parseInt(manualResponses.question_4),
      question_5: parseInt(manualResponses.question_5),
    }

    const timeSpent = Math.floor((Date.now() - startTime) / 1000)

    const response = await api.post(`/student/surveys/${props.survey.id}/complete`, {
      google_response_id: manualResponses.google_response_id || null,
      response_data: responseData,
      submitted_at: new Date().toISOString(),
      time_spent: timeSpent,
      completion_method: 'manual'
    })

    if (response.data.success) {
      // Show success message with score
      const avgScore = response.data.data.average_score
      emit('completed', {
        average_score: avgScore,
        completion_status: 'completed',
        submitted_at: response.data.data.submitted_at
      })
      
      // Show success notification
      showSuccessNotification(
        `Survey completed successfully! Your average score: ${avgScore}/10`
      )
      
      // Close modal after brief delay
      setTimeout(() => {
        emit('close')
      }, 2000)
    }
  } catch (error) {
    console.error('Error submitting completion:', error)
    
    let errorMessage = 'Error submitting survey completion'
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      errorMessage = errors.join(', ')
    }
    
    showErrorNotification(errorMessage)
  } finally {
    isProcessing.value = false
  }
}

// Notification functions
const showSuccessNotification = (message) => {
  // Create a temporary success notification
  const notification = document.createElement('div')
  notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-transform duration-300'
  notification.textContent = message
  document.body.appendChild(notification)
  
  setTimeout(() => {
    notification.style.transform = 'translateX(100%)'
    setTimeout(() => document.body.removeChild(notification), 300)
  }, 3000)
}

const showErrorNotification = (message) => {
  // Create a temporary error notification
  const notification = document.createElement('div')
  notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-transform duration-300'
  notification.textContent = message
  document.body.appendChild(notification)
  
  setTimeout(() => {
    notification.style.transform = 'translateX(100%)'
    setTimeout(() => document.body.removeChild(notification), 300)
  }, 5000)
}

const getScoreLabel = (score) => {
  const labels = {
    1: 'Poor', 2: 'Poor', 3: 'Fair', 4: 'Fair', 5: 'Average',
    6: 'Average', 7: 'Good', 8: 'Good', 9: 'Excellent', 10: 'Excellent'
  }
  return labels[score] || ''
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}

const formatQuestionKey = (key) => {
  const questionMap = {
    question_1: "Teaching Quality",
    question_2: "Lesson Clarity", 
    question_3: "Student Engagement",
    question_4: "Feedback Quality",
    question_5: "Overall Experience"
  }
  return questionMap[key] || key
}
</script>
