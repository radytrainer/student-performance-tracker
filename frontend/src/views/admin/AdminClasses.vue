<template>
  <div class="p-6">
    <div v-if="!hasPermission('admin.manage_classes')" class="text-center py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Access Denied</h2>
      <p class="text-gray-600">You don't have permission to manage classes.</p>
    </div>
    <div v-else>
    <div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Class Management</h1>
    <p class="text-gray-600 mt-1">Manage classes, sections, and enrollment</p>
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
          <h3 class="text-sm font-medium text-red-800">Error loading classes</h3>
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
        placeholder="Search classes..."
        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
        <select
        v-model="gradeFilter"
        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
        <option value="">All Grades</option>
        <option value="9">Grade 9</option>
        <option value="10">Grade 10</option>
        <option value="11">Grade 11</option>
        <option value="12">Grade 12</option>
        </select>
          <select v-model="sortBy" class="px-3 py-2 border border-gray-300 rounded-lg">
             <option value="class_name">Sort: Name</option>
             <option value="academic_year">Sort: Academic Year</option>
             <option value="room_number">Sort: Room</option>
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
          Add Class
        </button>
      </div>

      <!-- Classes Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="cls in filteredClasses"
          :key="cls.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
        >
          <div class="p-6">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ cls.class_name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ cls.academic_year }}</p>
                <div class="mt-3 space-y-2">
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-door-open mr-2"></i>
                    Room {{ cls.room_number || 'Not assigned' }}
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                    {{ cls.class_teacher_name || 'No teacher assigned' }}
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-users mr-2"></i>
                    {{ cls.student_count || 0 }} students
                  </div>
                </div>
              </div>
              <div class="flex-shrink-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Active
                </span>
              </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <button
                  @click="viewClass(cls)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  View Details
                </button>
                <span class="text-gray-300">|</span>
                <button
                  @click="editClass(cls)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  Edit
                </button>
              </div>
              <button
                @click="confirmDelete(cls)"
                class="text-red-600 hover:text-red-800 text-sm font-medium"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredClasses.length === 0" class="text-center py-12">
        <i class="fas fa-school text-gray-400 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No classes found</h3>
        <p class="text-gray-500">Try adjusting your search or filter criteria, or create a new class.</p>
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
        <h3 class="text-lg font-medium mb-4">Add New Class</h3>
        <p class="text-gray-600 mb-4">Class creation form will be implemented here.</p>
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
            Create Class
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
            <h3 class="text-lg font-medium text-gray-900">Delete Class</h3>
          </div>
        </div>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete <strong>{{ selectedClass?.class_name }}</strong>? 
          This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="deleteClass"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Delete Class
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
const classes = ref([]) // current page items
const classesMeta = ref({ total: 0, last_page: 1, current_page: 1, per_page: 12, from: 0, to: 0 })
const searchQuery = ref('')
const gradeFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(12)
const sortBy = ref('class_name')
const sortDir = ref('asc')
const showCreateModal = ref(false)
const showDeleteModal = ref(false)
const selectedClass = ref(null)
const successMessage = ref('')

// Computed
// Server returns filtered page; no client-side filtering
const filteredClasses = computed(() => classes.value)

// Methods
const viewClass = (cls) => {
  try { 
    // @ts-ignore
    window.location.href = `/admin/classes/${cls.id}`
  } catch (e) {
    console.log('View class:', cls)
  }
}

const editClass = (cls) => {
  selectedClass.value = cls
  showCreateModal.value = true
}

const confirmDelete = (cls) => {
  selectedClass.value = cls
  showDeleteModal.value = true
}

const deleteClass = async () => {
  try {
    await adminAPI.deleteClass(selectedClass.value.id)
    showSuccessMessage('Class deleted successfully')
    showDeleteModal.value = false
    selectedClass.value = null
    await loadClasses()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete class'
  }
}

const showSuccessMessage = (message) => {
  successMessage.value = message
  setTimeout(() => {
    successMessage.value = ''
  }, 5000)
}

const loadClasses = async () => {
try {
loading.value = true
error.value = null

const params = { per_page: itemsPerPage.value, page: currentPage.value, sort_by: sortBy.value, sort_dir: sortDir.value }
if (searchQuery.value) params.search = searchQuery.value
if (gradeFilter.value) params.academic_year = gradeFilter.value

const response = await adminAPI.getClasses(params)
const payload = response.data.data || {}
const items = payload.data || []
classes.value = items
classesMeta.value = {
    total: payload.total || items.length,
  last_page: payload.last_page || 1,
  current_page: payload.current_page || params.page,
    per_page: payload.per_page || params.per_page,
  from: payload.from || (items.length ? (params.page - 1) * params.per_page + 1 : 0),
    to: payload.to || ((params.page - 1) * params.per_page + items.length)
    }
    
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to load classes'
    console.error('Error loading classes:', err)
  } finally {
    loading.value = false
  }
}

// Watch for search changes
watch([searchQuery, gradeFilter, sortBy, sortDir], () => {
  if (!loading.value) {
    currentPage.value = 1
    loadClasses()
  }
}, { debounce: 500 })

// Pagination info and helpers
const paginationInfo = computed(() => {
  const meta = classesMeta.value || {}
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
    await loadClasses()
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
  loadClasses()
})
</script>
