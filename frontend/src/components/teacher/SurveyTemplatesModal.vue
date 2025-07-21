<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Survey Templates</h3>
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
        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="ml-3 text-gray-600">Loading templates...</span>
        </div>

        <!-- Templates Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="template in templates"
            :key="template.id"
            class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200 cursor-pointer"
            :class="{ 'border-blue-500 ring-2 ring-blue-200': selectedTemplate?.id === template.id }"
            @click="selectTemplate(template)"
          >
            <div class="flex items-start justify-between mb-3">
              <h4 class="text-lg font-medium text-gray-900">{{ template.name }}</h4>
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                :class="getTypeClass(template.type)"
              >
                {{ template.type }}
              </span>
            </div>
            
            <p class="text-sm text-gray-600 mb-4">{{ template.description }}</p>
            
            <div class="space-y-2">
              <h5 class="text-sm font-medium text-gray-700">Sample Questions:</h5>
              <ul class="text-xs text-gray-600 space-y-1">
                <li v-for="(question, index) in template.questions.slice(0, 3)" :key="index">
                  {{ index + 1 }}. {{ question }}
                </li>
                <li v-if="template.questions.length > 3" class="text-gray-400">
                  ... and {{ template.questions.length - 3 }} more
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Create Form (if template selected) -->
        <div v-if="selectedTemplate" class="mt-8 pt-6 border-t border-gray-200">
          <h4 class="text-lg font-medium text-gray-900 mb-4">
            Create Survey from "{{ selectedTemplate.name }}"
          </h4>
          
          <form @submit.prevent="createFromTemplate" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Survey Title *
                </label>
                <input
                  v-model="formData.title"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Enter survey title..."
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Google Form URL *
                </label>
                <input
                  v-model="formData.google_form_url"
                  type="url"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="https://forms.gle/..."
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                v-model="formData.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Survey description..."
              ></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Survey Type
                </label>
                <select
                  v-model="formData.survey_type"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                >
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Start Date (Optional)
                </label>
                <input
                  v-model="formData.start_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  End Date (Optional)
                </label>
                <input
                  v-model="formData.end_date"
                  type="date"
                  :min="formData.start_date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="clearSelection"
                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="isCreating || !isFormValid"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
              >
                {{ isCreating ? 'Creating...' : 'Create Survey' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/api/axiosConfig'

const emit = defineEmits(['close', 'created'])

// State
const isLoading = ref(false)
const isCreating = ref(false)
const templates = ref([])
const selectedTemplate = ref(null)

const formData = reactive({
  title: '',
  description: '',
  google_form_url: '',
  survey_type: 'weekly',
  start_date: '',
  end_date: ''
})

// Computed
const isFormValid = computed(() => {
  return formData.title && formData.google_form_url && formData.survey_type
})

// Methods
const loadTemplates = async () => {
  try {
    isLoading.value = true
    const response = await api.get('/teacher/survey-templates')
    
    if (response.data.success) {
      templates.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading templates:', error)
    showErrorNotification('Failed to load survey templates')
  } finally {
    isLoading.value = false
  }
}

const selectTemplate = (template) => {
  selectedTemplate.value = template
  
  // Pre-fill form with template data
  formData.survey_type = template.type
  formData.description = template.description
  
  // Generate a suggested title
  const now = new Date()
  const monthYear = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
  formData.title = `${template.name} - ${monthYear}`
}

const clearSelection = () => {
  selectedTemplate.value = null
  
  // Clear form
  Object.assign(formData, {
    title: '',
    description: '',
    google_form_url: '',
    survey_type: 'weekly',
    start_date: '',
    end_date: ''
  })
}

const createFromTemplate = async () => {
  try {
    isCreating.value = true
    
    const response = await api.post('/teacher/create-from-template', {
      template_id: selectedTemplate.value.id,
      ...formData
    })
    
    if (response.data.success) {
      emit('created', response.data.data)
      showSuccessNotification('Survey created successfully from template!')
      emit('close')
    }
  } catch (error) {
    console.error('Error creating survey from template:', error)
    const errorMessage = error.response?.data?.message || 'Failed to create survey'
    showErrorNotification(errorMessage)
  } finally {
    isCreating.value = false
  }
}

const getTypeClass = (type) => {
  switch (type) {
    case 'weekly':
      return 'bg-purple-100 text-purple-800'
    case 'monthly':
      return 'bg-indigo-100 text-indigo-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const showSuccessNotification = (message) => {
  const notification = document.createElement('div')
  notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50'
  notification.textContent = message
  document.body.appendChild(notification)
  
  setTimeout(() => {
    document.body.removeChild(notification)
  }, 3000)
}

const showErrorNotification = (message) => {
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
  loadTemplates()
})
</script>
