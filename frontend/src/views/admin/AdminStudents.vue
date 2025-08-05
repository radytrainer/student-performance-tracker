<template>
  <div class="p-6">
    <div v-if="!hasPermission('admin.manage_users')" class="text-center py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Access Denied</h2>
      <p class="text-gray-600">You don't have permission to manage students.</p>
    </div>
    <div v-else>
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Student Management</h1>
        <p class="text-gray-600 mt-1">Manage student records, enrollment, and academic data</p>
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
            <h3 class="text-sm font-medium text-red-800">Error loading students</h3>
            <p class="text-sm text-red-700 mt-1">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else class="space-y-6">
        <!-- Header Actions -->
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-4">
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search students..."
                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64"
              />
              <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
            <select
              v-model="classFilter"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Classes</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
            </select>
            <select
              v-model="statusFilter"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="flex items-center space-x-3">
            <!-- Bulk Actions -->
            <div v-if="selectedStudents.length > 0" class="flex items-center space-x-2">
              <span class="text-sm text-gray-600">{{ selectedStudents.length }} selected</span>
              <button
                @click="bulkActivate"
                class="bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center text-sm"
              >
                <i class="fas fa-check mr-1"></i>
                Activate
              </button>
              <button
                @click="bulkTransfer"
                class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center text-sm"
              >
                <i class="fas fa-exchange-alt mr-1"></i>
                Transfer
              </button>
              <button
                @click="bulkDeactivate"
                class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center text-sm"
              >
                <i class="fas fa-times mr-1"></i>
                Deactivate
              </button>
            </div>
            <button
              @click="openCreateModal"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
            >
              <i class="fas fa-plus mr-2"></i>
              Add Student
            </button>
          </div>
        </div>

        <!-- Students Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-3 text-left">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    @change="toggleSelectAll"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GPA</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="student in paginatedStudents" :key="student.user_id" class="hover:bg-gray-50">
                <td class="px-3 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :value="student.user_id"
                    v-model="selectedStudents"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div v-if="student.user?.profile_picture" class="h-10 w-10 rounded-full overflow-hidden">
                        <img :src="student.user.profile_picture" :alt="student.full_name" class="h-full w-full object-cover">
                      </div>
                      <div v-else class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-600 font-medium">{{ getStudentInitial(student) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ student.full_name }}</div>
                      <div class="text-sm text-gray-500">{{ student.user?.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ student.student_code }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ student.current_class?.class_name || 'Not assigned' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getGPAClass(student.current_gpa)">
                    {{ student.current_gpa || 'N/A' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getAttendanceClass(student.attendance_rate)">
                    {{ student.attendance_rate || 0 }}%
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <button
                    @click="toggleStudentStatus(student)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-colors"
                    :class="student.user?.is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'"
                  >
                    {{ student.user?.is_active ? 'Active' : 'Inactive' }}
                  </button>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="viewStudent(student)"
                      class="text-blue-600 hover:text-blue-900 transition-colors"
                      title="View Details"
                    >
                      <i class="fas fa-eye"></i>
                    </button>
                    <button
                      @click="editStudent(student)"
                      class="text-blue-600 hover:text-blue-900 transition-colors"
                      title="Edit Student"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <button
                      @click="confirmDelete(student)"
                      class="text-red-600 hover:text-red-900 transition-colors"
                      title="Delete Student"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="paginatedStudents.length === 0 && !loading" class="text-center py-12">
          <i class="fas fa-user-graduate text-gray-400 text-4xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No students found</h3>
          <p class="text-gray-500">
            {{ searchQuery || classFilter ? 'Try adjusting your search or filter criteria.' : 'Start by adding your first student.' }}
          </p>
        </div>

        <!-- Pagination -->
        <div v-if="paginationInfo.totalPages > 1" class="flex items-center justify-between bg-white px-4 py-3 border border-gray-200 rounded-lg">
          <div class="flex items-center">
            <p class="text-sm text-gray-700">
              Showing {{ paginationInfo.startItem }} to {{ paginationInfo.endItem }} of {{ paginationInfo.totalItems }} results
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="!paginationInfo.hasPrevPage"
              class="px-3 py-2 text-sm border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
            >
              Previous
            </button>
            
            <!-- Page Numbers -->
            <div class="flex items-center space-x-1">
              <template v-for="(page, index) in getVisiblePages()">
                <span
                  v-if="page === '...'"
                  :key="`ellipsis-${index}`"
                  class="px-3 py-2 text-sm text-gray-400"
                >
                  ...
                </span>
                <button
                  v-else
                  :key="`page-${page}`"
                  @click="goToPage(page)"
                  :class="[
                    'px-3 py-2 text-sm rounded-lg transition-colors',
                    page === currentPage 
                      ? 'bg-blue-600 text-white' 
                      : 'border border-gray-300 hover:bg-gray-50'
                  ]"
                >
                  {{ page }}
                </button>
              </template>
            </div>
            
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="!paginationInfo.hasNextPage"
              class="px-3 py-2 text-sm border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
            >
              Next
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
              <h3 class="text-lg font-medium text-gray-900">Delete Student</h3>
            </div>
          </div>
          <p class="text-gray-600 mb-6">
            Are you sure you want to delete <strong>{{ selectedStudent?.full_name }}</strong>? 
            This action cannot be undone and will remove all academic records.
          </p>
          <div class="flex justify-end space-x-3">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="deleteStudent"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              Delete Student
            </button>
          </div>
        </div>
      </div>

      <!-- Bulk Transfer Modal -->
      <div v-if="showBulkTransferModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <i class="fas fa-exchange-alt text-blue-400 text-2xl"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium text-gray-900">Transfer Students</h3>
            </div>
          </div>
          <p class="text-gray-600 mb-4">
            Transfer {{ selectedStudents.length }} selected students to a new class:
          </p>
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Target Class</label>
            <select
              v-model="transferClassId"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Select a class</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
            </select>
          </div>
          <div class="flex justify-end space-x-3">
            <button
              @click="showBulkTransferModal = false"
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="performBulkTransfer"
              :disabled="!transferClassId"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              Transfer Students
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
const students = ref([])
const classes = ref([])
const searchQuery = ref('')
const classFilter = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(15)
const successMessage = ref('')

// Modal states
const showDeleteModal = ref(false)
const showBulkTransferModal = ref(false)
const selectedStudent = ref(null)
const selectedStudents = ref([])
const transferClassId = ref('')

// Computed filtered students
const filteredStudents = computed(() => {
  let filtered = students.value

  // Apply search filter
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim()
    filtered = filtered.filter(student => {
      const fullName = student.full_name?.toLowerCase() || ''
      const email = student.user?.email?.toLowerCase() || ''
      const studentCode = student.student_code?.toLowerCase() || ''
      
      return fullName.includes(query) || 
             email.includes(query) || 
             studentCode.includes(query)
    })
  }

  // Apply class filter
  if (classFilter.value) {
    filtered = filtered.filter(student => student.current_class_id == classFilter.value)
  }

  // Apply status filter
  if (statusFilter.value === 'active') {
    filtered = filtered.filter(student => student.user?.is_active)
  } else if (statusFilter.value === 'inactive') {
    filtered = filtered.filter(student => !student.user?.is_active)
  }

  return filtered
})

// Computed paginated students
const paginatedStudents = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value
  const endIndex = startIndex + itemsPerPage.value
  return filteredStudents.value.slice(startIndex, endIndex)
})

// Computed pagination info
const paginationInfo = computed(() => {
  const totalItems = filteredStudents.value.length
  const totalPages = Math.ceil(totalItems / itemsPerPage.value)
  const startItem = totalItems === 0 ? 0 : (currentPage.value - 1) * itemsPerPage.value + 1
  const endItem = Math.min(currentPage.value * itemsPerPage.value, totalItems)
  
  return {
    totalItems,
    totalPages,
    startItem,
    endItem,
    currentPage: currentPage.value,
    hasNextPage: currentPage.value < totalPages,
    hasPrevPage: currentPage.value > 1
  }
})

// Computed for bulk selection
const isAllSelected = computed(() => {
  return paginatedStudents.value.length > 0 && selectedStudents.value.length === paginatedStudents.value.length
})

// Methods
const getStudentInitial = (student) => {
  const name = student.full_name || 'N/A'
  return name.charAt(0).toUpperCase()
}

const getGPAClass = (gpa) => {
  if (!gpa) return 'bg-gray-100 text-gray-800'
  if (gpa >= 3.5) return 'bg-green-100 text-green-800'
  if (gpa >= 3.0) return 'bg-blue-100 text-blue-800'
  if (gpa >= 2.5) return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

const getAttendanceClass = (rate) => {
  if (!rate) return 'bg-gray-100 text-gray-800'
  if (rate >= 90) return 'bg-green-100 text-green-800'
  if (rate >= 80) return 'bg-blue-100 text-blue-800'
  if (rate >= 70) return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedStudents.value = []
  } else {
    selectedStudents.value = paginatedStudents.value.map(student => student.user_id)
  }
}

const showSuccessMessage = (message) => {
  successMessage.value = message
  setTimeout(() => {
    successMessage.value = ''
  }, 5000)
}

const loadStudents = async () => {
  try {
    loading.value = true
    error.value = null
    
    const params = {
      per_page: 50 // Load more for client-side filtering
    }
    if (searchQuery.value) params.search = searchQuery.value
    if (classFilter.value) params.class_id = classFilter.value
    if (statusFilter.value) params.status = statusFilter.value

    const response = await adminAPI.getStudents(params)
    students.value = response.data.data.data || response.data.data || []
    
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to load students'
    console.error('Error loading students:', err)
  } finally {
    loading.value = false
  }
}

const loadClasses = async () => {
  try {
    const response = await adminAPI.getClasses()
    classes.value = response.data.data.data || response.data.data || []
  } catch (err) {
    console.error('Error loading classes:', err)
  }
}

const openCreateModal = () => {
  // TODO: Implement create student modal
  console.log('Create student modal')
}

const viewStudent = (student) => {
  // TODO: Navigate to student details view
  console.log('View student:', student)
}

const editStudent = (student) => {
  // TODO: Implement edit student modal
  console.log('Edit student:', student)
}

const confirmDelete = (student) => {
  selectedStudent.value = student
  showDeleteModal.value = true
}

const deleteStudent = async () => {
  try {
    await adminAPI.deleteStudent(selectedStudent.value.user_id)
    showSuccessMessage('Student deleted successfully')
    showDeleteModal.value = false
    selectedStudent.value = null
    await loadStudents()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete student'
  }
}

const toggleStudentStatus = async (student) => {
  try {
    const newStatus = !student.user.is_active
    await adminAPI.bulkStudentAction({
      action: newStatus ? 'activate' : 'deactivate',
      student_ids: [student.user_id]
    })
    student.user.is_active = newStatus
    showSuccessMessage(`Student ${newStatus ? 'activated' : 'deactivated'} successfully`)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update student status'
  }
}

const bulkActivate = async () => {
  try {
    await adminAPI.bulkStudentAction({
      action: 'activate',
      student_ids: selectedStudents.value
    })
    // Update local data
    selectedStudents.value.forEach(studentId => {
      const student = students.value.find(s => s.user_id === studentId)
      if (student && student.user) {
        student.user.is_active = true
      }
    })
    showSuccessMessage(`${selectedStudents.value.length} students activated successfully`)
    selectedStudents.value = []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to activate students'
  }
}

const bulkDeactivate = async () => {
  try {
    await adminAPI.bulkStudentAction({
      action: 'deactivate',
      student_ids: selectedStudents.value
    })
    // Update local data
    selectedStudents.value.forEach(studentId => {
      const student = students.value.find(s => s.user_id === studentId)
      if (student && student.user) {
        student.user.is_active = false
      }
    })
    showSuccessMessage(`${selectedStudents.value.length} students deactivated successfully`)
    selectedStudents.value = []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to deactivate students'
  }
}

const bulkTransfer = () => {
  transferClassId.value = ''
  showBulkTransferModal.value = true
}

const performBulkTransfer = async () => {
  try {
    await adminAPI.bulkStudentAction({
      action: 'transfer',
      student_ids: selectedStudents.value,
      class_id: transferClassId.value
    })
    showSuccessMessage(`${selectedStudents.value.length} students transferred successfully`)
    showBulkTransferModal.value = false
    selectedStudents.value = []
    await loadStudents()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to transfer students'
  }
}

// Pagination functions
const goToPage = (page) => {
  if (page >= 1 && page <= paginationInfo.value.totalPages) {
    currentPage.value = page
  }
}

const getVisiblePages = () => {
  const totalPages = paginationInfo.value.totalPages
  const current = currentPage.value
  const pages = []
  
  if (totalPages <= 7) {
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      if (totalPages > 6) {
        pages.push('...')
        pages.push(totalPages)
      }
    } else if (current >= totalPages - 3) {
      pages.push(1)
      if (totalPages > 6) {
        pages.push('...')
      }
      for (let i = totalPages - 4; i <= totalPages; i++) {
        pages.push(i)
      }
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(totalPages)
    }
  }
  
  return pages
}

// Watch for search/filter changes to reset pagination
watch([searchQuery, classFilter, statusFilter], () => {
  currentPage.value = 1
  selectedStudents.value = []
})

// Debounced API calls
watch([searchQuery, classFilter, statusFilter], () => {
  if (!loading.value) {
    loadStudents()
  }
}, { debounce: 500 })

onMounted(() => {
  loadStudents()
  loadClasses()
})
</script>
