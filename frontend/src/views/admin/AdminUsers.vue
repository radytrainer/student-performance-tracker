<template>
  <div class="h-screen flex flex-col bg-gray-50">
    <div v-if="!hasPermission('admin.manage_users')" class="text-center py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Access Denied</h2>
      <p class="text-gray-600">You don't have permission to manage users.</p>
    </div>
    <div v-else class="h-full flex flex-col">
      <!-- Sticky Header Section -->
      <div class="bg-white shadow-sm border-b border-gray-200 p-6 sticky top-0 z-10">
        <!-- Title Section -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-800">User Management</h1>
          <p class="text-gray-600 mt-1">Manage system users and their roles</p>
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

        <!-- Error State -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Error loading users</h3>
              <p class="text-sm text-red-700 mt-1">{{ error }}</p>
            </div>
          </div>
        </div>

        <!-- Header Actions -->
        <div class="flex justify-between items-center mb-6">
          <div class="flex items-center space-x-4">
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search users..."
                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64"
                @input="debouncedApiSearch"
              />
              <i 
                :class="searchLoading ? 'fas fa-spinner fa-spin' : 'fas fa-search'" 
                class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
              ></i>
            </div>
            <select
              v-model="roleFilter"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @change="applyFilters"
            >
              <option value="">All Roles</option>
              <option value="admin">Admin</option>
              <option value="teacher">Teacher</option>
              <option value="student">Student</option>
            </select>
            <select
              v-model="classFilter"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @change="applyFilters"
            >
              <option value="">All Classes</option>
              <option 
                v-for="classItem in classes" 
                :key="classItem.id" 
                :value="classItem.id"
              >
                {{ classItem.class_name }}
              </option>
            </select>
          </div>
          <div class="flex items-center space-x-3">
            <!-- Bulk Actions -->
            <div v-if="selectedUsers.length > 0" class="flex items-center space-x-2">
              <span class="text-sm text-gray-600">{{ selectedUsers.length }} selected</span>
              <button
                @click="bulkActivate"
                class="bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center text-sm"
              >
                <i class="fas fa-check mr-1"></i>
                Activate
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
              Add User
            </button>
          </div>
        </div>

        <!-- Table Header -->
        <div class="bg-gray-50 rounded-lg border border-gray-200">
          <div class="grid grid-cols-12 gap-4 px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
            <div class="col-span-1 flex items-center">
              <input
                type="checkbox"
                :checked="isAllSelected"
                @change="toggleSelectAll"
                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
            </div>
            <div class="col-span-3">User</div>
            <div class="col-span-2">Role</div>
            <div class="col-span-2">Status</div>
            <div class="col-span-2">Last Login</div>
            <div class="col-span-2 text-right">Actions</div>
          </div>
        </div>
      </div>

      <!-- Scrollable Content Area -->
      <div class="flex-1 overflow-y-auto">
        <div class="p-6 pt-0">
          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>

          <!-- Users List -->
          <div v-else-if="!error" class="space-y-1">
            <!-- User Rows -->
            <div 
              v-for="user in paginatedUsers" 
              :key="user.id" 
              class="bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center">
                <div class="col-span-1">
                  <input
                    type="checkbox"
                    :value="user.id"
                    v-model="selectedUsers"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                </div>
                <div class="col-span-3">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div v-if="user.profile_picture" class="h-10 w-10 rounded-full overflow-hidden">
                        <img :src="user.profile_picture" :alt="getUserName(user)" class="h-full w-full object-cover">
                      </div>
                      <div v-else class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-600 font-medium">{{ getUserInitial(user) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ getUserName(user) }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </div>
                <div class="col-span-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getRoleClass(user.role)">
                    {{ capitalizeRole(user.role) }}
                  </span>
                </div>
                <div class="col-span-2">
                  <button
                    @click="toggleUserStatus(user)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-colors"
                    :class="user.is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'"
                  >
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                  </button>
                </div>
                <div class="col-span-2 text-sm text-gray-500">
                  {{ formatDate(user.last_login) }}
                </div>
                <div class="col-span-2">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="editUser(user)"
                      class="text-blue-600 hover:text-blue-900 transition-colors"
                      title="Edit User"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <button
                      @click="resetPassword(user)"
                      class="text-green-600 hover:text-green-900 transition-colors"
                      title="Reset Password"
                    >
                      <i class="fas fa-key"></i>
                    </button>
                    <button
                      @click="changeRole(user)"
                      class="text-purple-600 hover:text-purple-900 transition-colors"
                      title="Change Role"
                    >
                      <i class="fas fa-user-tag"></i>
                    </button>
                    <button
                      @click="viewAccessLogs(user)"
                      class="text-gray-600 hover:text-gray-900 transition-colors"
                      title="View Access Logs"
                    >
                      <i class="fas fa-history"></i>
                    </button>
                    <button
                      @click="confirmDelete(user)"
                      class="text-red-600 hover:text-red-900 transition-colors"
                      title="Delete User"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="paginatedUsers.length === 0 && !loading" class="text-center py-12">
              <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
              <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
              <p class="text-gray-500">
                {{ searchQuery || roleFilter ? 'Try adjusting your search or filter criteria.' : 'Start by adding your first user.' }}
              </p>
            </div>

            <!-- Pagination -->
            <div v-if="paginationInfo.totalPages > 1" class="flex items-center justify-between bg-white px-4 py-3 border border-gray-200 rounded-lg mt-6">
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
        </div>
      </div>

      <!-- User Modal -->
      <UserModal
        :show="showUserModal"
        :user="selectedUser"
        :loading="modalLoading"
        @close="closeModal"
        @submit="handleUserSubmit"
      />

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium text-gray-900">Delete User</h3>
            </div>
          </div>
          <p class="text-gray-600 mb-6">
            Are you sure you want to delete <strong>{{ getUserName(userToDelete) }}</strong>? 
            This action cannot be undone.
          </p>
          <div class="flex justify-end space-x-3">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              :disabled="deleteLoading"
            >
              Cancel
            </button>
            <button
              @click="deleteUser"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
              :disabled="deleteLoading"
            >
              <span v-if="deleteLoading" class="flex items-center">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Deleting...
              </span>
              <span v-else>Delete User</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Password Reset Modal -->
      <div v-if="showPasswordResetModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <i class="fas fa-key text-green-400 text-2xl"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium text-gray-900">Reset Password</h3>
              <p class="text-sm text-gray-500">Reset password for {{ getUserName(selectedUser) }}</p>
            </div>
          </div>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
              <input
                v-model="passwordResetData.newPassword"
                type="password"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Enter new password"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
              <input
                v-model="passwordResetData.confirmPassword"
                type="password"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Confirm new password"
              />
            </div>
            <div class="flex items-center">
              <input
                v-model="passwordResetData.notifyUser"
                type="checkbox"
                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <label class="ml-2 text-sm text-gray-700">Notify user via email</label>
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 mt-6">
            <button
              @click="showPasswordResetModal = false"
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              :disabled="modalLoading"
            >
              Cancel
            </button>
            <button
              @click="handlePasswordReset"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
              :disabled="modalLoading"
            >
              <span v-if="modalLoading" class="flex items-center">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Resetting...
              </span>
              <span v-else>Reset Password</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Role Change Modal -->
      <div v-if="showRoleChangeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <i class="fas fa-user-tag text-purple-400 text-2xl"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium text-gray-900">Change Role</h3>
              <p class="text-sm text-gray-500">Change role for {{ getUserName(selectedUser) }}</p>
            </div>
          </div>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Role</label>
              <select
                v-model="roleChangeData.newRole"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
              </select>
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 mt-6">
            <button
              @click="showRoleChangeModal = false"
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              :disabled="modalLoading"
            >
              Cancel
            </button>
            <button
              @click="handleRoleChange"
              class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50"
              :disabled="modalLoading"
            >
              <span v-if="modalLoading" class="flex items-center">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Changing...
              </span>
              <span v-else>Change Role</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Access Logs Modal -->
      <div v-if="showAccessLogsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-screen overflow-y-auto">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <i class="fas fa-history text-gray-400 text-2xl"></i>
              </div>
              <div class="ml-3">
                <h3 class="text-lg font-medium text-gray-900">Access Logs</h3>
                <p class="text-sm text-gray-500">Access history for {{ getUserName(selectedUser) }}</p>
              </div>
            </div>
            <button
              @click="showAccessLogsModal = false"
              class="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
          
          <div class="space-y-2">
            <div v-if="accessLogs.length === 0" class="text-center py-8 text-gray-500">
              No access logs found
            </div>
            <div
              v-for="log in accessLogs"
              :key="log.id"
              class="bg-gray-50 rounded-lg p-3 text-sm"
            >
              <div class="flex items-center justify-between">
                <div>
                  <span class="font-medium">{{ log.action }}</span>
                  <span class="text-gray-500 ml-2">{{ formatDate(log.timestamp) }}</span>
                </div>
                <div class="text-xs text-gray-400">
                  {{ log.ip_address }}
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex justify-end mt-6">
            <button
              @click="showAccessLogsModal = false"
              class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { useAuth } from '@/composables/useAuth'
import UserModal from '@/components/modals/UserModal.vue'
import { usersAPI } from '@/api/users'
import { adminAPI } from '@/api/admin'

const { hasPermission } = useAuth()

// State
const loading = ref(true)
const searchLoading = ref(false)
const error = ref(null)
const successMessage = ref('')
const users = ref([])
const allUsers = ref([]) // Store all users for local filtering
const pagination = ref(null)
const searchQuery = ref('')
const roleFilter = ref('')
const classFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Classes data
const classes = ref([])

// Modal states
const showUserModal = ref(false)
const showDeleteModal = ref(false)
const showPasswordResetModal = ref(false)
const showRoleChangeModal = ref(false)
const showAccessLogsModal = ref(false)
const selectedUser = ref(null)
const userToDelete = ref(null)
const modalLoading = ref(false)
const deleteLoading = ref(false)

// Bulk selection
const selectedUsers = ref([])

// New modal data
const passwordResetData = ref({
  newPassword: '',
  confirmPassword: '',
  notifyUser: true
})
const roleChangeData = ref({
  newRole: ''
})
const accessLogs = ref([])

// Helper functions (defined early for use in computed)
const getUserName = (user) => {
  return `${user.first_name || ''} ${user.last_name || ''}`.trim() || user.username || user.email
}

// Computed filtered users for instant search
const filteredUsers = computed(() => {
  let filtered = allUsers.value

  // Apply search filter
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim()
    filtered = filtered.filter(user => {
      const fullName = getUserName(user).toLowerCase()
      const email = (user.email || '').toLowerCase()
      const username = (user.username || '').toLowerCase()
      
      return fullName.includes(query) || 
             email.includes(query) || 
             username.includes(query)
    })
  }

  // Apply role filter
  if (roleFilter.value) {
    filtered = filtered.filter(user => user.role === roleFilter.value)
  }

  // Apply class filter
  if (classFilter.value) {
    filtered = filtered.filter(user => {
      if (user.role === 'student' && user.student) {
        return user.student.current_class_id === parseInt(classFilter.value)
      } else if (user.role === 'teacher' && user.teacher) {
        // Check if teacher is assigned to the selected class
        return user.teacher.classes && user.teacher.classes.some(cls => cls.id === parseInt(classFilter.value))
      }
      return false
    })
  }

  return filtered
})

// Computed paginated users
const paginatedUsers = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value
  const endIndex = startIndex + itemsPerPage.value
  return filteredUsers.value.slice(startIndex, endIndex)
})

// Computed pagination info
const paginationInfo = computed(() => {
  const totalItems = filteredUsers.value.length
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
  return paginatedUsers.value.length > 0 && selectedUsers.value.length === paginatedUsers.value.length
})

// Debounced search for API calls (only when needed)
let searchTimeout = null
const debouncedApiSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    // Only make API call if search query is very specific or for pagination
    if (searchQuery.value.length > 2) {
      performApiSearch()
    }
  }, 800) // Longer delay for API calls
}

const performApiSearch = async () => {
  try {
    searchLoading.value = true
    const params = {
      page: 1,
      per_page: 50, // Get more results for better local filtering
      search: searchQuery.value,
      role: roleFilter.value
    }

    const response = await usersAPI.getUsers(params)
    allUsers.value = response.data.data
    pagination.value = response.data
    currentPage.value = 1
  } catch (err) {
    console.error('Search error:', err)
  } finally {
    searchLoading.value = false
  }
}

// Methods
const getRoleClass = (role) => {
  const classes = {
    admin: 'bg-purple-100 text-purple-800',
    teacher: 'bg-blue-100 text-blue-800',
    student: 'bg-green-100 text-green-800'
  }
  return classes[role] || 'bg-gray-100 text-gray-800'
}

const capitalizeRole = (role) => {
  return role.charAt(0).toUpperCase() + role.slice(1)
}

const formatDate = (date) => {
  if (!date) return 'Never'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getUserInitial = (user) => {
  const name = getUserName(user)
  return name.charAt(0).toUpperCase()
}

const showSuccessMessage = (message) => {
  successMessage.value = message
  setTimeout(() => {
    successMessage.value = ''
  }, 5000)
}

const loadUsers = async () => {
  try {
    loading.value = true
    error.value = null
    
    const params = {
      page: currentPage.value,
      per_page: 50 // Load more users for better local filtering
    }

    // Only add filters for initial load or role changes
    if (roleFilter.value) {
      params.role = roleFilter.value
    }

    const response = await usersAPI.getUsers(params)
    allUsers.value = response.data.data
    pagination.value = response.data
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to load users'
    console.error('Error loading users:', err)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  searchQuery.value = '' // Clear search when changing filters
  loadUsers()
}

const loadClasses = async () => {
  try {
    const response = await adminAPI.getClasses()
    classes.value = response.data.data || response.data
  } catch (err) {
    console.error('Error loading classes:', err)
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    currentPage.value = page
    loadUsers()
  }
}

// New pagination functions for client-side pagination
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
    // Show all pages if 7 or fewer
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i)
    }
  } else {
    // Show smart pagination
    if (current <= 4) {
      // Show first 5 pages + ... + last page
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      if (totalPages > 6) {
        pages.push('...')
        pages.push(totalPages)
      }
    } else if (current >= totalPages - 3) {
      // Show first page + ... + last 5 pages
      pages.push(1)
      if (totalPages > 6) {
        pages.push('...')
      }
      for (let i = totalPages - 4; i <= totalPages; i++) {
        pages.push(i)
      }
    } else {
      // Show first + ... + current-1, current, current+1 + ... + last
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

// Modal functions
const openCreateModal = () => {
  selectedUser.value = null
  showUserModal.value = true
}

const editUser = (user) => {
  selectedUser.value = { ...user }
  showUserModal.value = true
}

const closeModal = () => {
  showUserModal.value = false
  selectedUser.value = null
  modalLoading.value = false
}

const handleUserSubmit = async (userData) => {
  try {
    modalLoading.value = true
    
    if (selectedUser.value?.id) {
      // Update existing user
      await usersAPI.updateUser(selectedUser.value.id, userData)
      showSuccessMessage('User updated successfully')
    } else {
      // Create new user
      await usersAPI.createUser(userData)
      showSuccessMessage('User created successfully')
    }
    
    closeModal()
    await loadUsers()
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) {
      // Pass validation errors to modal
      const modalRef = document.querySelector('[data-modal-ref]')
      if (modalRef) {
        modalRef.setErrors(errors)
      }
    } else {
      error.value = err.response?.data?.message || err.message || 'Failed to save user'
    }
    console.error('Error saving user:', err)
  } finally {
    modalLoading.value = false
  }
}

const confirmDelete = (user) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const deleteUser = async () => {
  try {
    deleteLoading.value = true
    await usersAPI.deleteUser(userToDelete.value.id)
    showSuccessMessage('User deleted successfully')
    showDeleteModal.value = false
    userToDelete.value = null
    await loadUsers()
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to delete user'
    console.error('Error deleting user:', err)
  } finally {
    deleteLoading.value = false
  }
}

const toggleUserStatus = async (user) => {
  try {
    const newStatus = !user.is_active
    await usersAPI.toggleUserStatus(user.id, newStatus)
    user.is_active = newStatus
    showSuccessMessage(`User ${newStatus ? 'activated' : 'deactivated'} successfully`)
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to update user status'
    console.error('Error updating user status:', err)
  }
}

// New admin methods
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedUsers.value = []
  } else {
    selectedUsers.value = paginatedUsers.value.map(user => user.id)
  }
}

const resetPassword = (user) => {
  selectedUser.value = user
  passwordResetData.value = {
    newPassword: '',
    confirmPassword: '',
    notifyUser: true
  }
  showPasswordResetModal.value = true
}

const handlePasswordReset = async () => {
  try {
    modalLoading.value = true
    
    if (passwordResetData.value.newPassword !== passwordResetData.value.confirmPassword) {
      error.value = 'Passwords do not match'
      return
    }

    await adminAPI.resetUserPassword(selectedUser.value.id, {
      new_password: passwordResetData.value.newPassword,
      new_password_confirmation: passwordResetData.value.confirmPassword,
      notify_user: passwordResetData.value.notifyUser
    })

    showSuccessMessage('Password reset successfully')
    showPasswordResetModal.value = false
    selectedUser.value = null
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to reset password'
  } finally {
    modalLoading.value = false
  }
}

const changeRole = (user) => {
  selectedUser.value = user
  roleChangeData.value = {
    newRole: user.role
  }
  showRoleChangeModal.value = true
}

const handleRoleChange = async () => {
  try {
    modalLoading.value = true
    
    await adminAPI.updateUserRole(selectedUser.value.id, {
      role: roleChangeData.value.newRole
    })

    // Update user in local data
    const userIndex = allUsers.value.findIndex(u => u.id === selectedUser.value.id)
    if (userIndex !== -1) {
      allUsers.value[userIndex].role = roleChangeData.value.newRole
    }

    showSuccessMessage('User role updated successfully')
    showRoleChangeModal.value = false
    selectedUser.value = null
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update role'
  } finally {
    modalLoading.value = false
  }
}

const viewAccessLogs = async (user) => {
  try {
    selectedUser.value = user
    modalLoading.value = true
    
    const response = await adminAPI.getUserAccessLogs(user.id)
    accessLogs.value = response.data.data
    showAccessLogsModal.value = true
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load access logs'
  } finally {
    modalLoading.value = false
  }
}

const bulkActivate = async () => {
  try {
    await adminAPI.bulkUpdateUserStatus(selectedUsers.value, true)
    
    // Update local data
    selectedUsers.value.forEach(userId => {
      const user = allUsers.value.find(u => u.id === userId)
      if (user) user.is_active = true
    })
    
    showSuccessMessage(`${selectedUsers.value.length} users activated successfully`)
    selectedUsers.value = []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to activate users'
  }
}

const bulkDeactivate = async () => {
  try {
    await adminAPI.bulkUpdateUserStatus(selectedUsers.value, false)
    
    // Update local data
    selectedUsers.value.forEach(userId => {
      const user = allUsers.value.find(u => u.id === userId)
      if (user) user.is_active = false
    })
    
    showSuccessMessage(`${selectedUsers.value.length} users deactivated successfully`)
    selectedUsers.value = []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to deactivate users'
  }
}

// Watch for search/filter changes to reset pagination
watch([searchQuery, roleFilter, classFilter], () => {
  currentPage.value = 1
  selectedUsers.value = [] // Clear selection when filtering
})

onMounted(() => {
  loadUsers()
  loadClasses()
})
</script>
