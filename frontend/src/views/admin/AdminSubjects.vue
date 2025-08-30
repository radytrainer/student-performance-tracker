<template>
  <div class="p-6">
    <div v-if="!hasPermission('admin.manage_subjects')" class="text-center py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Access Denied</h2>
      <p class="text-gray-600">You don't have permission to manage subjects.</p>
    </div>
    <div v-else>
    <div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Subject Management</h1>
    <p class="text-gray-600 mt-1">Manage subjects, curriculum, and academic structure</p>
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
          <h3 class="text-sm font-medium text-red-800">Error loading subjects</h3>
          <p class="text-sm text-red-700 mt-1">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-6">
      <!-- Header Actions -->
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
        <input
        v-model="searchQuery"
        type="text"
        placeholder="Search subjects..."
        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
        <select
        v-model="categoryFilter"
        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
        <option value="">All Categories</option>
        <option value="Science">Science</option>
        <option value="Mathematics">Mathematics</option>
        <option value="Language Arts">Language Arts</option>
        <option value="Social Studies">Social Studies</option>
        <option value="Arts">Arts</option>
        </select>
          <select v-model="sortBy" class="px-3 py-2 border border-gray-300 rounded-lg">
             <option value="subject_name">Sort: Name</option>
             <option value="subject_code">Sort: Code</option>
             <option value="department">Sort: Department</option>
             <option value="credit_hours">Sort: Credit Hours</option>
             <option value="created_at">Sort: Created</option>
           </select>
           <button @click="toggleSortDir" class="px-3 py-2 border border-gray-300 rounded-lg">
             <i :class="sortDir === 'asc' ? 'fas fa-sort-amount-up' : 'fas fa-sort-amount-down' "></i>
           </button>
         </div>
        <button
          @click="showCreateModal = true"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
        >
          <i class="fas fa-plus mr-2"></i>
          Add Subject
        </button>
      </div>

      <!-- Subjects Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="subject in filteredSubjects"
          :key="subject.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
        >
          <div class="p-6">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center">
                  <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                       :style="{ backgroundColor: subject.color + '20' }">
                    <i :class="subject.icon" :style="{ color: subject.color }"></i>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-lg font-semibold text-gray-900">{{ subject.subject_name }}</h3>
                    <p class="text-sm text-gray-500">{{ subject.subject_code }}</p>
                  </div>
                </div>
                
                <div class="mt-4 space-y-2">
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-tag mr-2"></i>
                    {{ subject.category }}
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-clock mr-2"></i>
                    {{ subject.credit_hours }} credits
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-graduation-cap mr-2"></i>
                    Grades {{ subject.gradeRange }}
                  </div>
                </div>

                <p class="text-sm text-gray-600 mt-3 line-clamp-2">{{ subject.description }}</p>
              </div>
              <div class="flex-shrink-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="subject.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                  {{ subject.active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <button
                  @click="viewSubject(subject)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  View Details
                </button>
                <span class="text-gray-300">|</span>
                <button
                  @click="editSubject(subject)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  Edit
                </button>
              </div>
              <button
                @click="confirmDelete(subject)"
                class="text-red-600 hover:text-red-800 text-sm font-medium"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredSubjects.length === 0" class="text-center py-12">
        <i class="fas fa-book text-gray-400 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No subjects found</h3>
        <p class="text-gray-500">Try adjusting your search or filter criteria, or create a new subject.</p>
      </div>

      <!-- Pagination -->
      <div v-if="paginationInfo.totalPages > 1" class="flex items-center justify-between bg-white px-4 py-3 border border-gray-200 rounded-lg">
        <div class="flex items-center">
          <p class="text-sm text-gray-700">
            Showing {{ paginationInfo.startItem }} to {{ paginationInfo.endItem }} of {{ paginationInfo.totalItems }} results
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <button @click="goToPage(currentPage - 1)" :disabled="!paginationInfo.hasPrevPage" class="px-3 py-2 text-sm border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors">
            Previous
          </button>
          <div class="flex items-center space-x-1">
            <template v-for="(page, index) in getVisiblePages()">
              <span v-if="page === '...'" :key="`ellipsis-${index}`" class="px-3 py-2 text-sm text-gray-400">...</span>
              <button v-else :key="`page-${page}`" @click="goToPage(page)" :class="['px-3 py-2 text-sm rounded-lg transition-colors', page === currentPage ? 'bg-blue-600 text-white' : 'border border-gray-300 hover:bg-gray-50']">
                {{ page }}
              </button>
            </template>
          </div>
          <button @click="goToPage(currentPage + 1)" :disabled="!paginationInfo.hasNextPage" class="px-3 py-2 text-sm border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal Placeholder -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium mb-4">Add New Subject</h3>
        <p class="text-gray-600 mb-4">Subject creation form will be implemented here.</p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showCreateModal = false"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="showCreateModal = false"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Create Subject
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
            <h3 class="text-lg font-medium text-gray-900">Delete Subject</h3>
          </div>
        </div>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete <strong>{{ selectedSubject?.subject_name }}</strong>? 
          This action cannot be undone and will affect all related class assignments.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="deleteSubject"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Delete Subject
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
const subjects = ref([]) // current page items
const subjectsMeta = ref({ total: 0, last_page: 1, current_page: 1, per_page: 15, from: 0, to: 0 })
const searchQuery = ref('')
const categoryFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(15)
const sortBy = ref('subject_name')
const sortDir = ref('asc')
const showCreateModal = ref(false)
const showDeleteModal = ref(false)
const selectedSubject = ref(null)
const successMessage = ref('')

// Subject categories with colors and icons
const subjectCategories = {
  'Mathematics': { color: '#3B82F6', icon: 'fas fa-square-root-alt' },
  'Science': { color: '#10B981', icon: 'fas fa-atom' },
  'Language Arts': { color: '#8B5CF6', icon: 'fas fa-book-open' },
  'Social Studies': { color: '#EF4444', icon: 'fas fa-globe' },
  'Arts': { color: '#F59E0B', icon: 'fas fa-palette' },
  'Physical Education': { color: '#06B6D4', icon: 'fas fa-running' },
  'Technology': { color: '#8B5CF6', icon: 'fas fa-laptop-code' },
  'Other': { color: '#6B7280', icon: 'fas fa-book' }
}

// Computed
const filteredSubjects = computed(() => {
  // Server-side filtering, only map current page items for UI props
  return subjects.value.map(subject => ({
    ...subject,
    color: subjectCategories[subject.department]?.color || subjectCategories['Other'].color,
    icon: subjectCategories[subject.department]?.icon || subjectCategories['Other'].icon,
    gradeRange: '9-12',
    category: subject.department
  }))
})

// Methods
const viewSubject = (subject) => {
  try {
    // @ts-ignore
    window.location.href = `/admin/subjects/${subject.id}`
  } catch (e) {
    console.log('View subject:', subject)
  }
}

const editSubject = (subject) => {
  selectedSubject.value = subject
  showCreateModal.value = true
}

const confirmDelete = (subject) => {
  selectedSubject.value = subject
  showDeleteModal.value = true
}

const deleteSubject = async () => {
  try {
    await adminAPI.deleteSubject(selectedSubject.value.id)
    showSuccessMessage('Subject deleted successfully')
    showDeleteModal.value = false
    selectedSubject.value = null
    await loadSubjects()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete subject'
  }
}

const showSuccessMessage = (message) => {
  successMessage.value = message
  setTimeout(() => {
    successMessage.value = ''
  }, 5000)
}

const loadSubjects = async () => {
try {
loading.value = true
error.value = null

const params = { per_page: itemsPerPage.value, page: currentPage.value, sort_by: sortBy.value, sort_dir: sortDir.value }
if (searchQuery.value) params.search = searchQuery.value
if (categoryFilter.value) params.department = categoryFilter.value

const response = await adminAPI.getSubjects(params)
const payload = response.data.data || {}
const items = payload.data || []
subjects.value = items
subjectsMeta.value = {
total: payload.total || items.length,
last_page: payload.last_page || 1,
current_page: payload.current_page || params.page,
per_page: payload.per_page || params.per_page,
from: payload.from || (items.length ? (params.page - 1) * params.per_page + 1 : 0),
to: payload.to || ((params.page - 1) * params.per_page + items.length)
}

} catch (err) {
error.value = err.response?.data?.message || err.message || 'Failed to load subjects'
console.error('Error loading subjects:', err)
} finally {
loading.value = false
}
}

// Watch for search/filter changes
watch([searchQuery, categoryFilter, sortBy, sortDir], () => {
  if (!loading.value) {
    currentPage.value = 1
    loadSubjects()
  }
}, { debounce: 500 })

// Pagination info and helpers
const paginationInfo = computed(() => {
  const meta = subjectsMeta.value || {}
  const totalItems = meta.total || 0
  const totalPages = meta.last_page || 1
  const startItem = meta.from || 0
  const endItem = meta.to || 0
  return {
    totalItems,
    totalPages,
    startItem,
    endItem,
    currentPage: meta.current_page || currentPage.value,
    hasNextPage: (meta.current_page || 1) < totalPages,
    hasPrevPage: (meta.current_page || 1) > 1
  }
})

const goToPage = async (page) => {
  if (page >= 1 && page <= paginationInfo.value.totalPages) {
    currentPage.value = page
    await loadSubjects()
  }
}

const getVisiblePages = () => {
  const totalPages = paginationInfo.value.totalPages
  const current = currentPage.value
  const pages = []
  if (totalPages <= 7) {
    for (let i = 1; i <= totalPages; i++) pages.push(i)
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      if (totalPages > 6) { pages.push('...'); pages.push(totalPages) }
    } else if (current >= totalPages - 3) {
      pages.push(1)
      if (totalPages > 6) pages.push('...')
      for (let i = totalPages - 4; i <= totalPages; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(totalPages)
    }
  }
  return pages
}

const toggleSortDir = () => {
  sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
}

onMounted(() => {
  loadSubjects()
})
</script>
