<template>
  <div class="p-6">
    <div v-if="!hasPermission('admin.manage_users')" class="text-center py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Access Denied</h2>
      <p class="text-gray-600">You don't have permission to manage teachers.</p>
    </div>
    <div v-else>
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-800">Teacher Management</h1>
            <p class="text-gray-600 mt-1">Manage teacher accounts, assignments, and professional data</p>
          </div>
          
          <div class="flex items-center space-x-3">
            <!-- Real-time indicator -->
            <div v-if="isRealTimeConnected" class="flex items-center text-green-600">
              <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-1"></div>
              <span class="text-xs">Live Updates</span>
            </div>
            
            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow p-3 text-center">
              <p class="text-sm text-gray-500">Total Teachers</p>
              <p class="text-2xl font-bold text-gray-900">{{ teachers.length }}</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-3 text-center">
              <p class="text-sm text-gray-500">Active</p>
              <p class="text-2xl font-bold text-green-600">{{ activeTeachersCount }}</p>
            </div>
            
            <!-- Analytics Toggle -->
            <button
              @click="showAnalyticsPanel = !showAnalyticsPanel"
              :class="[
                'px-4 py-2 rounded-lg transition-colors flex items-center',
                showAnalyticsPanel 
                  ? 'bg-blue-600 text-white' 
                  : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
              ]"
            >
              <i class="fas fa-chart-bar mr-2"></i>
              Analytics
            </button>
          </div>
        </div>
        
        <!-- Last refresh indicator -->
        <div class="mt-2 text-xs text-gray-500">
          Last updated: {{ formatLastRefresh() }}
          <button @click="refreshTeachers" class="ml-2 text-blue-600 hover:text-blue-800">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>

      <!-- Analytics Panel -->
      <div v-if="showAnalyticsPanel" class="mb-8">
        <TeacherAnalytics />
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
            <h3 class="text-sm font-medium text-red-800">Error loading teachers</h3>
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
                placeholder="Search teachers..."
                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64"
              />
              <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
            <select
              v-model="departmentFilter"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
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
            <!-- Sort controls -->
            <div class="flex items-center space-x-2">
              <select v-model="sortBy" class="px-3 py-2 border border-gray-300 rounded-lg">
                <option value="name">Sort: Name</option>
                <option value="email">Sort: Email</option>
                <option value="teacher_code">Sort: Teacher Code</option>
                <option value="department">Sort: Department</option>
                <option value="classes_count">Sort: Classes</option>
                <option value="created_at">Sort: Join Date</option>
              </select>
              <button @click="toggleSortDir" class="px-3 py-2 border border-gray-300 rounded-lg">
                <i :class="sortDir === 'asc' ? 'fas fa-sort-amount-up' : 'fas fa-sort-amount-down' "></i>
              </button>
            </div>
            
            <!-- Bulk Actions -->
            <div v-if="selectedTeachers.length > 0" class="flex items-center space-x-2">
              <span class="text-sm text-gray-600 bg-blue-50 px-2 py-1 rounded">{{ selectedTeachers.length }} selected</span>
              <button
                @click="openBulkOperations"
                class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center text-sm"
              >
                <i class="fas fa-cogs mr-1"></i>
                Bulk Actions
              </button>
              <button
                @click="clearSelection"
                class="bg-gray-500 text-white px-3 py-2 rounded-lg hover:bg-gray-600 transition-colors flex items-center text-sm"
              >
                <i class="fas fa-times mr-1"></i>
                Clear
              </button>
            </div>
            
            <!-- Export/Import Actions -->
            <div class="flex items-center space-x-2">
              <div class="relative">
                <button
                  @click="showExportMenu = !showExportMenu"
                  class="bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center text-sm"
                >
                  <i class="fas fa-download mr-1"></i>
                  Export
                  <i class="fas fa-chevron-down ml-1"></i>
                </button>
                
                <!-- Export Dropdown -->
                <div v-if="showExportMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                  <div class="py-1">
                    <button
                      @click="exportTeachers('csv')"
                      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                      <i class="fas fa-file-csv mr-2"></i>
                      Export as CSV
                    </button>
                    <button
                      @click="exportTeachers('excel')"
                      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                      <i class="fas fa-file-excel mr-2"></i>
                      Export as Excel
                    </button>
                    <button
                      @click="downloadTemplate"
                      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-t"
                    >
                      <i class="fas fa-file-download mr-2"></i>
                      Download Template
                    </button>
                  </div>
                </div>
              </div>
              
              <button
                @click="$refs.importFile?.click()"
                class="bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center text-sm"
              >
                <i class="fas fa-upload mr-1"></i>
                Import
              </button>
              <input
                ref="importFile"
                type="file"
                accept=".csv,.xlsx,.xls"
                class="hidden"
                @change="handleImport"
              />
            </div>
            <button
              @click="openCreateModal"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
            >
              <i class="fas fa-plus mr-2"></i>
              Add Teacher
            </button>
          </div>

          <!-- Teacher Modal -->
          <TeacherModal
            :show="showTeacherModal"
            :teacher="selectedTeacher"
            :subjects="subjects"
            :departments="departments"
            :available-classes="availableClasses"
            :loading="modalLoading"
            @close="closeTeacherModal"
            @submit="handleTeacherSubmit"
          />

          <!-- Bulk Operations Modal -->
          <BulkTeacherModal
            :show="showBulkModal"
            :selected-teachers="selectedTeachers"
            :selected-teachers-data="selectedTeachersData"
            :subjects="subjects"
            :departments="departments"
            :available-classes="availableClasses"
            @close="closeBulkModal"
            @completed="handleBulkOperationCompleted"
          />
        </div>

        <!-- Teachers Table -->
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
                <th @click="sortBy='name'; toggleSortDir()" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none">
                  Teacher
                  <i v-if="sortBy==='name'" :class="sortDir==='asc' ? 'fas fa-sort-up ml-1' : 'fas fa-sort-down ml-1'"></i>
                </th>
                <th @click="sortBy='teacher_code'; toggleSortDir()" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none">
                  Teacher Code
                  <i v-if="sortBy==='teacher_code'" :class="sortDir==='asc' ? 'fas fa-sort-up ml-1' : 'fas fa-sort-down ml-1'"></i>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subjects</th>
                <th @click="sortBy='classes_count'; toggleSortDir()" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none">
                  Classes
                  <i v-if="sortBy==='classes_count'" :class="sortDir==='asc' ? 'fas fa-sort-up ml-1' : 'fas fa-sort-down ml-1'"></i>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="teacher in paginatedTeachers" :key="teacher.user_id" class="hover:bg-gray-50">
                <td class="px-3 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :value="teacher.user_id"
                    v-model="selectedTeachers"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div v-if="resolveImage(teacher.user?.profile_picture)" class="h-10 w-10 rounded-full overflow-hidden">
                      <img :src="resolveImage(teacher.user.profile_picture)" :alt="teacher.full_name" class="h-full w-full object-cover" @error="$event.target.style.display='none'">
                      </div>
                      <div v-else class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-600 font-medium">{{ getTeacherInitial(teacher) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ teacher.full_name }}</div>
                      <div class="text-sm text-gray-500">{{ teacher.user?.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ teacher.teacher_code }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ teacher.department?.name || 'Not assigned' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-wrap gap-1">
                    <span 
                      v-for="subject in teacher.subjects?.slice(0, 2)" 
                      :key="subject.id"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                    >
                      {{ subject.subject_name }}
                    </span>
                    <span v-if="teacher.subjects?.length > 2" class="text-xs text-gray-500">
                      +{{ teacher.subjects.length - 2 }} more
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div class="flex items-center">
                    <span class="font-medium">{{ teacher.classes_count || 0 }}</span>
                    <span class="text-gray-500 ml-1">classes</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="teacher.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    {{ teacher.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-1">
                    <router-link
                      :to="`/admin/teachers/${teacher.user_id}`"
                      class="text-blue-600 hover:text-blue-900 p-2"
                      title="View Details"
                    >
                      <i class="fas fa-eye"></i>
                    </router-link>
                    <button
                      @click="editTeacher(teacher)"
                      class="text-green-600 hover:text-green-900 p-2"
                      title="Edit Teacher"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <div class="relative">
                      <button
                        @click="toggleTeacherActions(teacher.user_id)"
                        class="text-purple-600 hover:text-purple-900 p-2"
                        title="More Actions"
                      >
                        <i class="fas fa-ellipsis-v"></i>
                      </button>
                      
                      <!-- Actions dropdown -->
                      <div v-if="teacherActionsOpen === teacher.user_id" class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        <div class="py-1">
                          <button
                            @click="assignSubjects(teacher)"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                          >
                            <i class="fas fa-book mr-2"></i>
                            Assign Subjects
                          </button>
                          <button
                            @click="assignClasses(teacher)"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                          >
                            <i class="fas fa-chalkboard mr-2"></i>
                            Assign Classes
                          </button>
                          <button
                            @click="resetPassword(teacher)"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-t"
                          >
                            <i class="fas fa-key mr-2"></i>
                            Reset Password
                          </button>
                          <button
                            @click="toggleTeacherStatus(teacher)"
                            :class="[
                              'block w-full text-left px-4 py-2 text-sm hover:bg-gray-100',
                              teacher.status === 'active' ? 'text-red-700' : 'text-green-700'
                            ]"
                          >
                            <i :class="teacher.status === 'active' ? 'fas fa-user-slash' : 'fas fa-user-check'" class="mr-2"></i>
                            {{ teacher.status === 'active' ? 'Deactivate' : 'Activate' }}
                          </button>
                          <button
                            @click="deleteTeacher(teacher)"
                            class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 border-t"
                          >
                            <i class="fas fa-trash mr-2"></i>
                            Delete Teacher
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-lg">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="previousPage"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Previous
            </button>
            <button
              @click="nextPage"
              :disabled="currentPage === totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ startIndex + 1 }}</span> to 
                <span class="font-medium">{{ Math.min(endIndex, filteredTeachers.length) }}</span> of 
                <span class="font-medium">{{ filteredTeachers.length }}</span> results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  @click="previousPage"
                  :disabled="currentPage === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <i class="fas fa-chevron-left"></i>
                </button>
                
                <template v-for="page in visiblePages" :key="page">
                  <button
                    @click="goToPage(page)"
                    :class="[
                      page === currentPage
                        ? 'bg-blue-50 border-blue-500 text-blue-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                    ]"
                  >
                    {{ page }}
                  </button>
                </template>
                
                <button
                  @click="nextPage"
                  :disabled="currentPage === totalPages"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <i class="fas fa-chevron-right"></i>
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { adminAPI } from '@/api/admin'
import { usersAPI } from '@/api/users'
import { resolveImageUrl } from '@/utils/imageUrl'
import { initEcho } from '@/realtime/echo'
import TeacherModal from '@/components/admin/TeacherModal.vue'
import TeacherAnalytics from '@/components/admin/TeacherAnalytics.vue'
import BulkTeacherModal from '@/components/admin/BulkTeacherModal.vue'

const { hasPermission } = useAuth()

// State
const loading = ref(true)
const error = ref(null)
const successMessage = ref('')

// Data
const teachers = ref([])
const subjects = ref([])
const departments = ref([])
const availableClasses = ref([])
const teacherStats = ref({})

// Modal state  
const showTeacherModal = ref(false)
const showBulkModal = ref(false)
const showAnalyticsPanel = ref(false)
const selectedTeacher = ref(null)
const modalLoading = ref(false)

// Real-time state
const echo = ref(null)
const lastRefresh = ref(new Date())
const isRealTimeConnected = ref(false)

// Filters and search
const searchQuery = ref('')
const departmentFilter = ref('')
const statusFilter = ref('')
const sortBy = ref('name')
const sortDir = ref('asc')
const selectedTeachers = ref([])

// UI state
const showExportMenu = ref(false)
const teacherActionsOpen = ref(null)

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Computed properties
const filteredTeachers = computed(() => {
  let result = [...teachers.value]

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(teacher => 
      teacher.full_name?.toLowerCase().includes(query) ||
      teacher.user?.email?.toLowerCase().includes(query) ||
      teacher.teacher_code?.toLowerCase().includes(query)
    )
  }

  // Department filter
  if (departmentFilter.value) {
    result = result.filter(teacher => teacher.department_id == departmentFilter.value)
  }

  // Status filter
  if (statusFilter.value) {
    result = result.filter(teacher => teacher.status === statusFilter.value)
  }

  // Sorting
  result.sort((a, b) => {
    let aVal, bVal
    
    switch (sortBy.value) {
      case 'name':
        aVal = a.full_name || ''
        bVal = b.full_name || ''
        break
      case 'email':
        aVal = a.user?.email || ''
        bVal = b.user?.email || ''
        break
      case 'teacher_code':
        aVal = a.teacher_code || ''
        bVal = b.teacher_code || ''
        break
      case 'department':
        aVal = a.department?.name || ''
        bVal = b.department?.name || ''
        break
      case 'classes_count':
        aVal = a.classes_count || 0
        bVal = b.classes_count || 0
        break
      case 'created_at':
        aVal = new Date(a.created_at || 0)
        bVal = new Date(b.created_at || 0)
        break
      default:
        aVal = ''
        bVal = ''
    }

    if (typeof aVal === 'string') {
      return sortDir.value === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal)
    }
    return sortDir.value === 'asc' ? aVal - bVal : bVal - aVal
  })

  return result
})

const totalPages = computed(() => Math.ceil(filteredTeachers.value.length / itemsPerPage.value))
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value)
const endIndex = computed(() => startIndex.value + itemsPerPage.value)

const paginatedTeachers = computed(() => {
  return filteredTeachers.value.slice(startIndex.value, endIndex.value)
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, start + 4)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

const isAllSelected = computed(() => {
  return paginatedTeachers.value.length > 0 && 
         paginatedTeachers.value.every(teacher => selectedTeachers.value.includes(teacher.user_id))
})

const activeTeachersCount = computed(() => {
  return teachers.value.filter(teacher => teacher.status === 'active').length
})

const selectedTeachersData = computed(() => {
  return teachers.value.filter(teacher => selectedTeachers.value.includes(teacher.user_id))
})

// Methods
const loadTeachers = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Try new API first, fallback to old API
    let response
    try {
      response = await adminAPI.getTeachers()
    } catch (apiError) {
      console.warn('New teacher API not available, using fallback:', apiError)
      response = await usersAPI.getUsers({ role: 'teacher' })
    }
    
    teachers.value = response.data.data || response.data || []
    lastRefresh.value = new Date()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load teachers'
    console.error('Error loading teachers:', err)
  } finally {
    loading.value = false
  }
}

const loadSubjects = async () => {
  try {
    const response = await adminAPI.getSubjects()
    subjects.value = response.data.data || response.data || []
  } catch (err) {
    console.error('Error loading subjects:', err)
  }
}

const loadDepartments = async () => {
  try {
    // For now, use mock departments until backend implements this
    departments.value = [
      { id: 1, name: 'Mathematics' },
      { id: 2, name: 'Science' },
      { id: 3, name: 'English' },
      { id: 4, name: 'History' },
      { id: 5, name: 'Physical Education' },
      { id: 6, name: 'Arts' }
    ]
  } catch (err) {
    console.error('Error loading departments:', err)
  }
}

const openCreateModal = () => {
  selectedTeacher.value = null
  showTeacherModal.value = true
}

const closeTeacherModal = () => {
  showTeacherModal.value = false
  selectedTeacher.value = null
  modalLoading.value = false
}

const handleTeacherSubmit = async (teacherData) => {
  try {
    modalLoading.value = true
    
    if (selectedTeacher.value?.user_id) {
      // Update existing teacher
      await usersAPI.updateUser(selectedTeacher.value.user_id, { ...teacherData, role: 'teacher' })
      showSuccessMessage('Teacher updated successfully')
    } else {
      // Create new teacher
      await usersAPI.createUser({ ...teacherData, role: 'teacher' })
      showSuccessMessage('Teacher created successfully')
    }
    
    closeTeacherModal()
    await loadTeachers()
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to save teacher'
    console.error('Error saving teacher:', err)
  } finally {
    modalLoading.value = false
  }
}

const viewTeacher = (teacher) => {
  selectedTeacher.value = teacher
  showTeacherModal.value = true
}

const editTeacher = (teacher) => {
  selectedTeacher.value = teacher
  showTeacherModal.value = true
}

const assignSubjects = (teacher) => {
  // TODO: Implement subject assignment modal
  console.log('Assign subjects to teacher:', teacher)
  showSuccessMessage('Subject assignment will be implemented soon')
}

const deleteTeacher = async (teacher) => {
  if (confirm(`Are you sure you want to delete teacher ${teacher.full_name}?`)) {
    try {
      await usersAPI.deleteUser(teacher.user_id)
      showSuccessMessage('Teacher deleted successfully')
      await loadTeachers()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete teacher'
      console.error('Error deleting teacher:', err)
    }
  }
}

// Bulk actions
const bulkActivate = async () => {
  try {
    for (const teacherId of selectedTeachers.value) {
      await usersAPI.updateUser(teacherId, { status: 'active' })
    }
    showSuccessMessage(`${selectedTeachers.value.length} teachers activated`)
    selectedTeachers.value = []
    await loadTeachers()
  } catch (err) {
    error.value = 'Failed to activate teachers'
  }
}

const bulkDeactivate = async () => {
  if (confirm(`Are you sure you want to deactivate ${selectedTeachers.value.length} teachers?`)) {
    try {
      for (const teacherId of selectedTeachers.value) {
        await usersAPI.updateUser(teacherId, { status: 'inactive' })
      }
      showSuccessMessage(`${selectedTeachers.value.length} teachers deactivated`)
      selectedTeachers.value = []
      await loadTeachers()
    } catch (err) {
      error.value = 'Failed to deactivate teachers'
    }
  }
}

const bulkAssignSubjects = () => {
  // TODO: Implement bulk subject assignment
  console.log('Bulk assign subjects to:', selectedTeachers.value)
  showSuccessMessage('Bulk subject assignment will be implemented soon')
}

// Pagination
const goToPage = (page) => {
  currentPage.value = page
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

// Sorting
const toggleSortDir = () => {
  sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
}

// Selection
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedTeachers.value = []
  } else {
    selectedTeachers.value = paginatedTeachers.value.map(teacher => teacher.user_id)
  }
}

// Utilities
const getTeacherInitial = (teacher) => {
  return teacher.full_name?.charAt(0)?.toUpperCase() || 'T'
}

const resolveImage = (imagePath) => {
  return resolveImageUrl(imagePath)
}

const showSuccessMessage = (message) => {
  successMessage.value = message
  setTimeout(() => {
    successMessage.value = ''
  }, 3000)
}

// Format last refresh timestamp
const formatLastRefresh = () => {
  if (!lastRefresh.value) return 'Never'
  
  const now = new Date()
  const diff = now - lastRefresh.value
  const seconds = Math.floor(diff / 1000)
  
  if (seconds < 60) return `${seconds} seconds ago`
  
  const minutes = Math.floor(seconds / 60)
  if (minutes < 60) return `${minutes} minutes ago`
  
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours} hours ago`
  
  return lastRefresh.value.toLocaleDateString()
}

// Refresh teachers data
const refreshTeachers = async () => {
  await loadTeachers()
}

// Reset pagination when filters change
watch([searchQuery, departmentFilter, statusFilter], () => {
  currentPage.value = 1
})

onMounted(async () => {
  await Promise.all([
    loadTeachers(),
    loadSubjects(),
    loadDepartments()
  ])
})
</script>
