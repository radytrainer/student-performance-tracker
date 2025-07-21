<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-md w-full mx-4 max-h-[90vh] overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">
            Assign Survey to Classes
          </h3>
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

      <div class="p-6 overflow-y-auto">
        <form @submit.prevent="assignForm">
          <!-- Survey Info -->
          <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h4 class="font-medium text-blue-900">{{ form?.title }}</h4>
            <p class="text-blue-700 text-sm">{{ form?.description }}</p>
          </div>

          <!-- Class Selection -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">
              Select Classes
            </label>
            <div class="space-y-2 max-h-48 overflow-y-auto">
              <div
                v-for="classItem in availableClasses"
                :key="classItem.id"
                class="flex items-center"
              >
                <input
                  :id="`class-${classItem.id}`"
                  v-model="selectedClasses"
                  :value="classItem.id"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label
                  :for="`class-${classItem.id}`"
                  class="ml-3 block text-sm text-gray-700 cursor-pointer"
                >
                  {{ classItem.class_name }} ({{ classItem.academic_year }})
                  <span class="text-gray-500">- {{ classItem.students_count }} students</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Due Date -->
          <div class="mb-6">
            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
              Due Date (Optional)
            </label>
            <input
              id="due_date"
              v-model="assignment.due_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Instructions -->
          <div class="mb-6">
            <label for="instructions" class="block text-sm font-medium text-gray-700 mb-2">
              Instructions for Students (Optional)
            </label>
            <textarea
              id="instructions"
              v-model="assignment.instructions"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Special instructions for completing this survey..."
            ></textarea>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isLoading || selectedClasses.length === 0"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isLoading ? 'Assigning...' : 'Assign Survey' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  form: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'assigned'])

const authStore = useAuthStore()

// State
const isLoading = ref(false)
const availableClasses = ref([])
const selectedClasses = ref([])

const assignment = reactive({
  due_date: '',
  instructions: ''
})

// Methods
const loadClasses = async () => {
  try {
    console.log('Loading classes...')
    const response = await fetch('http://localhost:8000/api/teacher/feedback-classes', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    console.log('Response status:', response.status)
    
    if (response.ok) {
      const data = await response.json()
      console.log('Classes data:', data)
      availableClasses.value = data.data || []
      console.log('Available classes:', availableClasses.value)
    } else {
      console.error('Failed to load classes:', response.status, response.statusText)
    }
  } catch (error) {
    console.error('Error loading classes:', error)
  }
}

const assignForm = async () => {
  try {
    isLoading.value = true

    const assignments = selectedClasses.value.map(classId => ({
      feedback_form_id: props.form.id,
      class_id: classId,
      due_date: assignment.due_date || null,
      instructions: assignment.instructions || null
    }))

    const response = await fetch('http://localhost:8000/api/teacher/form-assignments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
      body: JSON.stringify({ assignments })
    })

    if (response.ok) {
      emit('assigned')
      alert('Survey assigned successfully!')
    } else {
      alert('Failed to assign survey')
    }
  } catch (error) {
    console.error('Error assigning form:', error)
    alert('Error assigning survey')
  } finally {
    isLoading.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadClasses()
})
</script>
