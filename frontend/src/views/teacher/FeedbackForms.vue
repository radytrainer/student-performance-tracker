<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Student Feedback Surveys</h1>
        <p class="mt-2 text-gray-600">Create and manage feedback surveys for your students</p>
      </div>

      <!-- Create Form Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900">Create New Survey</h2>
        </div>
        <div class="p-6">
          <form @submit.prevent="createForm" class="space-y-6">
            <!-- Survey Title -->
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                Survey Title
              </label>
              <input
                id="title"
                v-model="newForm.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g., Monthly Student Feedback Survey"
              />
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                id="description"
                v-model="newForm.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Brief description of the survey purpose..."
              ></textarea>
            </div>

            <!-- Survey Type -->
            <div>
              <label for="survey_type" class="block text-sm font-medium text-gray-700 mb-2">
                Survey Type
              </label>
              <select
                id="survey_type"
                v-model="newForm.survey_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
              </select>
            </div>

            <!-- Google Form URL -->
            <div>
              <label for="google_form_url" class="block text-sm font-medium text-gray-700 mb-2">
                Google Form URL
              </label>
              <input
                id="google_form_url"
                v-model="newForm.google_form_url"
                type="url"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="https://docs.google.com/forms/d/..."
              />
              <p class="mt-1 text-sm text-gray-500">
                Create your survey in Google Forms and paste the URL here
              </p>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                  Start Date
                </label>
                <input
                  id="start_date"
                  v-model="newForm.start_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
              <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                  End Date
                </label>
                <input
                  id="end_date"
                  v-model="newForm.end_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="isLoading"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ isLoading ? 'Creating...' : 'Create Survey' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Existing Forms List -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900">My Surveys</h2>
        </div>
        <div class="divide-y divide-gray-200">
          <div
            v-for="form in forms"
            :key="form.id"
            class="p-6 hover:bg-gray-50 transition-colors duration-200"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-medium text-gray-900">{{ form.title }}</h3>
                <p class="text-gray-600 mt-1">{{ form.description }}</p>
                <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                  <span class="capitalize">{{ form.survey_type }} survey</span>
                  <span>•</span>
                  <span>{{ form.total_responses || 0 }} responses</span>
                  <span>•</span>
                  <span>Created {{ formatDate(form.created_at) }}</span>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <!-- Status Badge -->
                <span
                  :class="form.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                >
                  {{ form.is_active ? 'Active' : 'Inactive' }}
                </span>
                
                <!-- Actions -->
                <button
                  @click="assignToClasses(form)"
                  class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200"
                >
                  Assign to Classes
                </button>
                <button
                  @click="assignToUsers(form)"
                  class="px-3 py-1 text-sm bg-purple-100 text-purple-700 rounded-md hover:bg-purple-200 transition-colors duration-200"
                >
                  Assign to Users
                </button>
                <button
                  @click="viewResponses(form)"
                  class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors duration-200"
                >
                  View Responses
                </button>
                <button
                  @click="toggleFormStatus(form)"
                  class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-200"
                >
                  {{ form.is_active ? 'Deactivate' : 'Activate' }}
                </button>
              </div>
            </div>
          </div>
          
          <!-- Empty State -->
          <div v-if="forms.length === 0" class="p-12 text-center">
            <div class="text-gray-400 mb-4">
              <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No surveys yet</h3>
            <p class="text-gray-500">Create your first feedback survey to get started.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Class Assignment Modal -->
    <ClassAssignmentModal
      v-if="showAssignmentModal"
      :form="selectedForm"
      @close="showAssignmentModal = false"
      @assigned="onFormAssigned"
    />

    <!-- User Assignment Modal -->
    <UserAssignmentModal
      v-if="showUserAssignmentModal"
      :form="selectedForm"
      @close="showUserAssignmentModal = false"
      @assigned="onFormAssigned"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import ClassAssignmentModal from '@/components/teacher/ClassAssignmentModal.vue'
import UserAssignmentModal from '@/components/teacher/UserAssignmentModal.vue'

const authStore = useAuthStore()

// State
const isLoading = ref(false)
const forms = ref([])
const showAssignmentModal = ref(false)
const showUserAssignmentModal = ref(false)
const selectedForm = ref(null)

// New form data
const newForm = reactive({
  title: '',
  description: '',
  survey_type: 'monthly',
  google_form_url: '',
  start_date: '',
  end_date: '',
})

// Methods
const createForm = async () => {
  try {
    isLoading.value = true
    
    // Basic validation
    if (!newForm.title || !newForm.google_form_url) {
      alert('Please fill in required fields')
      return
    }

    // Here we'll call the API to create the form
    const response = await fetch('http://localhost:8000/api/teacher/feedback-forms', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
      body: JSON.stringify({
        ...newForm,
        created_by_teacher_id: authStore.user.id
      })
    })

    if (response.ok) {
      const createdForm = await response.json()
      forms.value.unshift(createdForm.data)
      resetForm()
      alert('Survey created successfully!')
    } else {
      alert('Failed to create survey')
    }
  } catch (error) {
    console.error('Error creating form:', error)
    alert('Error creating survey')
  } finally {
    isLoading.value = false
  }
}

const resetForm = () => {
  Object.assign(newForm, {
    title: '',
    description: '',
    survey_type: 'monthly',
    google_form_url: '',
    start_date: '',
    end_date: '',
  })
}

const assignToClasses = (form) => {
  selectedForm.value = form
  showAssignmentModal.value = true
}

const assignToUsers = (form) => {
  selectedForm.value = form
  showUserAssignmentModal.value = true
}

const viewResponses = (form) => {
  // Navigate to responses view
  console.log('View responses for form:', form.id)
}

const toggleFormStatus = async (form) => {
  try {
    const response = await fetch(`http://localhost:8000/api/teacher/feedback-forms/${form.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
      body: JSON.stringify({
        is_active: !form.is_active
      })
    })

    if (response.ok) {
      form.is_active = !form.is_active
    }
  } catch (error) {
    console.error('Error updating form status:', error)
  }
}

const onFormAssigned = () => {
  showAssignmentModal.value = false
  showUserAssignmentModal.value = false
  // Refresh forms list or show success message
}

const loadForms = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/teacher/feedback-forms', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      forms.value = data.data || []
    }
  } catch (error) {
    console.error('Error loading forms:', error)
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

// Lifecycle
onMounted(() => {
  loadForms()
})
</script>
