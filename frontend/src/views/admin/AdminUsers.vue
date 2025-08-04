<template>
  <div class="p-6">
    <div v-if="!hasPermission('admin.manage_users')" class="text-center py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Access Denied</h2>
      <p class="text-gray-600">You don't have permission to manage users.</p>
    </div>
    <div v-else>
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
          <h3 class="text-sm font-medium text-red-800">Error loading users</h3>
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
        </div>
        <button
          @click="openCreateModal"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
        >
          <i class="fas fa-plus mr-2"></i>
          Add User
        </button>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in paginatedUsers" :key="user.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
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
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getRoleClass(user.role)">
                  {{ capitalizeRole(user.role) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <button
                  @click="toggleUserStatus(user)"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-colors"
                  :class="user.is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'"
                >
                  {{ user.is_active ? 'Active' : 'Inactive' }}
                </button>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(user.last_login) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="editUser(user)"
                  class="text-blue-600 hover:text-blue-900 mr-3 transition-colors"
                >
                  Edit
                </button>
                <button
                  @click="confirmDelete(user)"
                  class="text-red-600 hover:text-red-900 transition-colors"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
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
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { useAuth } from '@/composables/useAuth'
import UserModal from '@/components/modals/UserModal.vue'
import { usersAPI } from '@/api/users'

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
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Modal states
const showUserModal = ref(false)
const showDeleteModal = ref(false)
const selectedUser = ref(null)
const userToDelete = ref(null)
const modalLoading = ref(false)
const deleteLoading = ref(false)

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

// Watch for search/filter changes to reset pagination
watch([searchQuery, roleFilter], () => {
  currentPage.value = 1
})

onMounted(() => {
  loadUsers()
})
</script>
