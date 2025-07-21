<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">
            Assign Survey to Individual Users
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

      <div class="p-6 overflow-y-auto max-h-96">
        <form @submit.prevent="assignForm">
          <!-- Survey Info -->
          <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h4 class="font-medium text-blue-900">{{ form?.title }}</h4>
            <p class="text-blue-700 text-sm">{{ form?.description }}</p>
          </div>

          <!-- User Selection Tabs -->
          <div class="mb-6">
            <div class="border-b border-gray-200">
              <nav class="-mb-px flex space-x-8">
                <button
                  v-for="tab in userTabs"
                  :key="tab.id"
                  @click="activeTab = tab.id"
                  type="button"
                  :class="[
                    activeTab === tab.id
                      ? 'border-blue-500 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
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

          <!-- User Selection -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">
              Select {{ activeTab === 'students' ? 'Students' : 'Teachers' }}
            </label>
            <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
              <div
                v-for="user in currentUsers"
                :key="user.id"
                class="flex items-center"
              >
                <input
                  :id="`user-${user.id}`"
                  v-model="selectedUsers"
                  :value="user.id"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label
                  :for="`user-${user.id}`"
                  class="ml-3 block text-sm text-gray-700 cursor-pointer flex-1"
                >
                  <div class="flex items-center justify-between">
                    <div>
                      <span class="font-medium">{{ user.first_name }} {{ user.last_name }}</span>
                      <span class="text-gray-500 ml-2">({{ user.username }})</span>
                    </div>
                    <span 
                      :class="user.role === 'teacher' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                    >
                      {{ user.role }}
                    </span>
                  </div>
                  <div class="text-xs text-gray-500">{{ user.email }}</div>
                </label>
              </div>
              
              <!-- Empty State -->
              <div v-if="currentUsers.length === 0" class="text-center py-4 text-gray-500">
                No {{ activeTab }} available
              </div>
            </div>
          </div>

          <!-- Quick Selection Buttons -->
          <div class="mb-6 flex space-x-2">
            <button
              type="button"
              @click="selectAll"
              class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors duration-200"
            >
              Select All {{ activeTab === 'students' ? 'Students' : 'Teachers' }}
            </button>
            <button
              type="button"
              @click="clearSelection"
              class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors duration-200"
            >
              Clear Selection
            </button>
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
              Instructions for Users (Optional)
            </label>
            <textarea
              id="instructions"
              v-model="assignment.instructions"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Special instructions for completing this survey..."
            ></textarea>
          </div>

          <!-- Selected Users Summary -->
          <div v-if="selectedUsers.length > 0" class="mb-6 p-4 bg-green-50 rounded-lg">
            <h5 class="font-medium text-green-900 mb-2">
              Selected Users ({{ selectedUsers.length }})
            </h5>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="userId in selectedUsers"
                :key="userId"
                class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded"
              >
                {{ getUserName(userId) }}
                <button
                  type="button"
                  @click="removeUser(userId)"
                  class="ml-1 text-green-600 hover:text-green-800"
                >
                  ×
                </button>
              </span>
            </div>
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
              :disabled="isLoading || selectedUsers.length === 0"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isLoading ? 'Assigning...' : `Assign to ${selectedUsers.length} User${selectedUsers.length === 1 ? '' : 's'}` }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
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
const availableUsers = ref({ teachers: [], students: [] })
const selectedUsers = ref([])
const activeTab = ref('students')

const assignment = reactive({
  due_date: '',
  instructions: ''
})

// Computed
const userTabs = computed(() => [
  { id: 'students', name: 'Students', count: availableUsers.value.students.length },
  { id: 'teachers', name: 'Teachers', count: availableUsers.value.teachers.length }
])

const currentUsers = computed(() => {
  return activeTab.value === 'students' 
    ? availableUsers.value.students 
    : availableUsers.value.teachers
})

const allUsers = computed(() => [
  ...availableUsers.value.students,
  ...availableUsers.value.teachers
])

// Methods
const loadUsers = async () => {
  try {
    console.log('Loading available users...')
    const response = await fetch('http://localhost:8000/api/teacher/available-users', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      console.log('Users data:', data)
      availableUsers.value = data.data || { teachers: [], students: [] }
    } else {
      console.error('Failed to load users:', response.status)
    }
  } catch (error) {
    console.error('Error loading users:', error)
  }
}

const assignForm = async () => {
  try {
    isLoading.value = true

    const assignments = selectedUsers.value.map(userId => ({
      feedback_form_id: props.form.id,
      user_id: userId,
      due_date: assignment.due_date || null,
      instructions: assignment.instructions || null
    }))

    const response = await fetch('http://localhost:8000/api/teacher/user-assignments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
      body: JSON.stringify({ assignments })
    })

    if (response.ok) {
      emit('assigned')
      alert(`Survey assigned to ${selectedUsers.value.length} user(s) successfully!`)
    } else {
      const errorData = await response.json()
      alert(errorData.message || 'Failed to assign survey')
    }
  } catch (error) {
    console.error('Error assigning form:', error)
    alert('Error assigning survey')
  } finally {
    isLoading.value = false
  }
}

const selectAll = () => {
  const currentUserIds = currentUsers.value.map(u => u.id)
  // Add current tab users to selection (avoiding duplicates)
  selectedUsers.value = [...new Set([...selectedUsers.value, ...currentUserIds])]
}

const clearSelection = () => {
  const currentUserIds = currentUsers.value.map(u => u.id)
  // Remove current tab users from selection
  selectedUsers.value = selectedUsers.value.filter(id => !currentUserIds.includes(id))
}

const removeUser = (userId) => {
  selectedUsers.value = selectedUsers.value.filter(id => id !== userId)
}

const getUserName = (userId) => {
  const user = allUsers.value.find(u => u.id === userId)
  return user ? `${user.first_name} ${user.last_name}` : 'Unknown User'
}

// Lifecycle
onMounted(() => {
  loadUsers()
})
</script>
