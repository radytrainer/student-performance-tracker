<template>
  <div class="p-6">
    <div v-if="!hasPermission('admin.manage_system')" class="text-center py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Access Denied</h2>
      <p class="text-gray-600">You don't have permission to manage academic terms.</p>
    </div>
    <div v-else>
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Academic Terms Management</h1>
        <p class="text-gray-600 mt-1">Manage academic terms, semesters, and calendar</p>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-green-400"></i>
          </div>
          <div class="ml-3">
            <p class="text-sm text-green-700">{{ successMessage }}</p>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-400"></i>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error loading terms</h3>
            <p class="text-sm text-red-700 mt-1">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else class="space-y-6">
        <!-- Header Actions -->
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-4">
            <select
              v-model="academicYearFilter"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Academic Years</option>
              <option v-for="year in academicYears" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>
          <button
            @click="openCreateModal"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
          >
            <i class="fas fa-plus mr-2"></i>
            Add Term
          </button>
        </div>

        <!-- Terms Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="term in filteredTerms"
            :key="term.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
            :class="{ 'ring-2 ring-blue-500': term.is_current }"
          >
            <div class="p-6">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center">
                    <h3 class="text-lg font-semibold text-gray-900">{{ term.term_name }}</h3>
                    <span v-if="term.is_current" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      Current
                    </span>
                  </div>
                  <p class="text-sm text-gray-500 mt-1">{{ term.academic_year }}</p>
                  
                  <div class="mt-4 space-y-2">
                    <div class="flex items-center text-sm text-gray-600">
                      <i class="fas fa-calendar-day mr-2"></i>
                      {{ formatDate(term.start_date) }}
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                      <i class="fas fa-calendar-times mr-2"></i>
                      {{ formatDate(term.end_date) }}
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                      <i class="fas fa-clock mr-2"></i>
                      {{ getTermDuration(term) }} days
                    </div>
                  </div>
                </div>
                <div class="flex-shrink-0">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getStatusClass(term.status)">
                    {{ capitalizeFirst(term.status) }}
                  </span>
                </div>
              </div>

              <div class="mt-6 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <button
                    v-if="!term.is_current"
                    @click="setCurrent(term)"
                    class="text-blue-600 hover:text-blue-900 text-sm transition-colors"
                  >
                    Set Current
                  </button>
                </div>
                <div class="flex items-center space-x-2">
                  <button
                    @click="editTerm(term)"
                    class="text-blue-600 hover:text-blue-900 transition-colors"
                    title="Edit Term"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    @click="confirmDelete(term)"
                    class="text-red-600 hover:text-red-900 transition-colors"
                    title="Delete Term"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredTerms.length === 0" class="text-center py-12">
          <i class="fas fa-calendar-alt text-gray-400 text-4xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No academic terms found</h3>
          <p class="text-gray-500">
            {{ academicYearFilter ? 'Try adjusting your filter or create a new term.' : 'Start by creating your first academic term.' }}
          </p>
        </div>
      </div>

      <!-- Create/Edit Term Modal -->
      <div v-if="showTermModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <i class="fas fa-calendar-alt text-blue-400 text-2xl"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium text-gray-900">
                {{ selectedTerm ? 'Edit Term' : 'Create New Term' }}
              </h3>
            </div>
          </div>
          
          <form @submit.prevent="saveTerm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Term Name</label>
              <input
                v-model="termForm.term_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g., Fall Semester, Spring Term"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
              <input
                v-model="termForm.academic_year"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g., 2024-2025"
              />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input
                  v-model="termForm.start_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input
                  v-model="termForm.end_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
            <div class="flex items-center">
              <input
                v-model="termForm.is_current"
                type="checkbox"
                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <label class="ml-2 text-sm text-gray-700">Set as current term</label>
            </div>
          </form>
          
          <div class="flex justify-end space-x-3 mt-6">
            <button
              @click="closeTermModal"
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              :disabled="modalLoading"
            >
              Cancel
            </button>
            <button
              @click="saveTerm"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
              :disabled="modalLoading"
            >
              <span v-if="modalLoading" class="flex items-center">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Saving...
              </span>
              <span v-else>{{ selectedTerm ? 'Update Term' : 'Create Term' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium text-gray-900">Delete Term</h3>
            </div>
          </div>
          <p class="text-gray-600 mb-6">
            Are you sure you want to delete <strong>{{ selectedTerm?.term_name }}</strong>? 
            This action cannot be undone and may affect related academic records.
          </p>
          <div class="flex justify-end space-x-3">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="deleteTerm"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              Delete Term
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { adminAPI } from '@/api/admin'

const { hasPermission } = useAuth()

// State
const loading = ref(true)
const error = ref(null)
const terms = ref([])
const academicYearFilter = ref('')
const successMessage = ref('')

// Modal states
const showTermModal = ref(false)
const showDeleteModal = ref(false)
const selectedTerm = ref(null)
const modalLoading = ref(false)

// Form data
const termForm = ref({
  term_name: '',
  academic_year: '',
  start_date: '',
  end_date: '',
  is_current: false
})

// Computed
const filteredTerms = computed(() => {
  let filtered = terms.value

  if (academicYearFilter.value) {
    filtered = filtered.filter(term => term.academic_year === academicYearFilter.value)
  }

  return filtered.sort((a, b) => {
    // Current term first, then by start date descending
    if (a.is_current && !b.is_current) return -1
    if (!a.is_current && b.is_current) return 1
    return new Date(b.start_date) - new Date(a.start_date)
  })
})

const academicYears = computed(() => {
  return [...new Set(terms.value.map(term => term.academic_year))].sort().reverse()
})

// Methods
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getTermDuration = (term) => {
  const start = new Date(term.start_date)
  const end = new Date(term.end_date)
  const diffTime = Math.abs(end - start)
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    completed: 'bg-blue-100 text-blue-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const capitalizeFirst = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const showSuccessMessage = (message) => {
  successMessage.value = message
  setTimeout(() => {
    successMessage.value = ''
  }, 5000)
}

const loadTerms = async () => {
  try {
    loading.value = true
    error.value = null
    
    const params = {}
    if (academicYearFilter.value) params.academic_year = academicYearFilter.value

    const response = await adminAPI.getTerms(params)
    terms.value = response.data.data || []
    
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to load terms'
    console.error('Error loading terms:', err)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  selectedTerm.value = null
  termForm.value = {
    term_name: '',
    academic_year: '',
    start_date: '',
    end_date: '',
    is_current: false
  }
  showTermModal.value = true
}

const editTerm = (term) => {
  selectedTerm.value = term
  termForm.value = {
    term_name: term.term_name,
    academic_year: term.academic_year,
    start_date: term.start_date,
    end_date: term.end_date,
    is_current: term.is_current
  }
  showTermModal.value = true
}

const closeTermModal = () => {
  showTermModal.value = false
  selectedTerm.value = null
  modalLoading.value = false
}

const saveTerm = async () => {
  try {
    modalLoading.value = true
    
    if (selectedTerm.value) {
      await adminAPI.updateTerm(selectedTerm.value.id, termForm.value)
      showSuccessMessage('Term updated successfully')
    } else {
      await adminAPI.createTerm(termForm.value)
      showSuccessMessage('Term created successfully')
    }
    
    closeTermModal()
    await loadTerms()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save term'
  } finally {
    modalLoading.value = false
  }
}

const confirmDelete = (term) => {
  selectedTerm.value = term
  showDeleteModal.value = true
}

const deleteTerm = async () => {
  try {
    await adminAPI.deleteTerm(selectedTerm.value.id)
    showSuccessMessage('Term deleted successfully')
    showDeleteModal.value = false
    selectedTerm.value = null
    await loadTerms()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete term'
  }
}

const setCurrent = async (term) => {
  try {
    await adminAPI.setCurrentTerm(term.id)
    showSuccessMessage(`${term.term_name} set as current term`)
    await loadTerms()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to set current term'
  }
}

// Watch for filter changes
watch(academicYearFilter, () => {
  if (!loading.value) {
    loadTerms()
  }
})

onMounted(() => {
  loadTerms()
})
</script>
