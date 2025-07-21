<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Bulk Assign Survey</h3>
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
        <form @submit.prevent="submitAssignment" class="space-y-6">
          <!-- Survey Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Select Survey
            </label>
            <select
              v-model="formData.survey_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option value="">Choose a survey...</option>
              <option v-for="survey in surveys" :key="survey.id" :value="survey.id">
                {{ survey.title }} ({{ survey.survey_type }})
              </option>
            </select>
          </div>

          <!-- Class Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Select Classes
            </label>
            <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-md p-3">
              <div
                v-for="classItem in classes"
                :key="classItem.id"
                class="flex items-center"
              >
                <input
                  :id="`class-${classItem.id}`"
                  v-model="formData.class_ids"
                  :value="classItem.id"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label
                  :for="`class-${classItem.id}`"
                  class="ml-3 text-sm text-gray-700 cursor-pointer"
                >
                  {{ classItem.class_name }}
                  <span class="text-gray-500">({{ classItem.student_count || 0 }} students)</span>
                </label>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              Selected: {{ formData.class_ids.length }} classes
            </p>
          </div>

          <!-- Due Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Due Date (Optional)
            </label>
            <input
              v-model="formData.due_date"
              type="date"
              :min="tomorrow"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Instructions -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Special Instructions (Optional)
            </label>
            <textarea
              v-model="formData.instructions"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Any special instructions for students..."
            ></textarea>
          </div>

          <!-- Assignment Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Assignment Type
            </label>
            <div class="space-y-2">
              <div class="flex items-center">
                <input
                  id="assign-now"
                  v-model="assignmentType"
                  value="now"
                  type="radio"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                />
                <label for="assign-now" class="ml-3 text-sm text-gray-700">
                  Assign Immediately
                </label>
              </div>
              <div class="flex items-center">
                <input
                  id="schedule-later"
                  v-model="assignmentType"
                  value="schedule"
                  type="radio"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                />
                <label for="schedule-later" class="ml-3 text-sm text-gray-700">
                  Schedule for Later
                </label>
              </div>
            </div>
          </div>

          <!-- Schedule Options (if scheduling) -->
          <div v-if="assignmentType === 'schedule'" class="space-y-4 p-4 bg-gray-50 rounded-lg">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Schedule Date
              </label>
              <input
                v-model="scheduleData.schedule_date"
                type="datetime-local"
                :min="tomorrowDateTime"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required
              />
            </div>

            <div class="flex items-center">
              <input
                id="auto-remind"
                v-model="scheduleData.auto_remind"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="auto-remind" class="ml-3 text-sm text-gray-700">
                Send automatic reminders
              </label>
            </div>

            <div v-if="scheduleData.auto_remind">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Remind Days Before Due Date
              </label>
              <select
                v-model="scheduleData.remind_days_before"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="1">1 day before</option>
                <option value="2">2 days before</option>
                <option value="3">3 days before</option>
                <option value="5">5 days before</option>
                <option value="7">1 week before</option>
              </select>
            </div>
          </div>

          <!-- Actions -->
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
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              {{ isProcessing ? 'Processing...' : (assignmentType === 'schedule' ? 'Schedule Assignment' : 'Assign Now') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/api/axiosConfig'

const props = defineProps({
  surveys: {
    type: Array,
    default: () => []
  },
  classes: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'assigned'])

// State
const isProcessing = ref(false)
const assignmentType = ref('now')

const formData = reactive({
  survey_id: '',
  class_ids: [],
  due_date: '',
  instructions: ''
})

const scheduleData = reactive({
  schedule_date: '',
  auto_remind: false,
  remind_days_before: 1
})

// Computed
const isFormValid = computed(() => {
  const hasBasicData = formData.survey_id && formData.class_ids.length > 0
  if (assignmentType.value === 'schedule') {
    return hasBasicData && scheduleData.schedule_date && formData.due_date
  }
  return hasBasicData
})

const tomorrow = computed(() => {
  const date = new Date()
  date.setDate(date.getDate() + 1)
  return date.toISOString().split('T')[0]
})

const tomorrowDateTime = computed(() => {
  const date = new Date()
  date.setDate(date.getDate() + 1)
  return date.toISOString().slice(0, 16)
})

// Methods
const submitAssignment = async () => {
  try {
    isProcessing.value = true

    let response
    if (assignmentType.value === 'schedule') {
      // Schedule the survey
      response = await api.post('/teacher/schedule-survey', {
        survey_id: formData.survey_id,
        class_ids: formData.class_ids,
        schedule_date: scheduleData.schedule_date,
        due_date: formData.due_date,
        instructions: formData.instructions,
        auto_remind: scheduleData.auto_remind,
        remind_days_before: scheduleData.remind_days_before
      })
    } else {
      // Assign immediately
      response = await api.post('/teacher/bulk-assign-surveys', {
        survey_id: formData.survey_id,
        class_ids: formData.class_ids,
        due_date: formData.due_date,
        instructions: formData.instructions
      })
    }

    if (response.data.success) {
      emit('assigned', response.data)
      showSuccessNotification(response.data.message)
      emit('close')
    }
  } catch (error) {
    console.error('Error assigning survey:', error)
    const errorMessage = error.response?.data?.message || 'Failed to assign survey'
    showErrorNotification(errorMessage)
  } finally {
    isProcessing.value = false
  }
}

const showSuccessNotification = (message) => {
  // Create success notification
  const notification = document.createElement('div')
  notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50'
  notification.textContent = message
  document.body.appendChild(notification)
  
  setTimeout(() => {
    document.body.removeChild(notification)
  }, 3000)
}

const showErrorNotification = (message) => {
  // Create error notification
  const notification = document.createElement('div')
  notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50'
  notification.textContent = message
  document.body.appendChild(notification)
  
  setTimeout(() => {
    document.body.removeChild(notification)
  }, 5000)
}

// Lifecycle
onMounted(() => {
  // Set default due date to 1 week from now
  const defaultDue = new Date()
  defaultDue.setDate(defaultDue.getDate() + 7)
  formData.due_date = defaultDue.toISOString().split('T')[0]
})
</script>
