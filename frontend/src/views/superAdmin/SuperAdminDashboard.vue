<template>
  <div class="h-screen flex flex-col bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 p-6">
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Super Admin Dashboard</h1>
        <p class="text-gray-600 mt-1">System-wide management and oversight</p>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <i class="fas fa-school text-blue-600 text-2xl"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-blue-900">Total Schools</p>
              <p class="text-2xl font-bold text-blue-600">{{ stats.total_schools || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <i class="fas fa-users text-green-600 text-2xl"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-green-900">Total Users</p>
              <p class="text-2xl font-bold text-green-600">{{ stats.total_users || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <i class="fas fa-user-shield text-purple-600 text-2xl"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-purple-900">Sub Admins</p>
              <p class="text-2xl font-bold text-purple-600">{{ stats.total_admins || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <i class="fas fa-graduation-cap text-orange-600 text-2xl"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-orange-900">Students</p>
              <p class="text-2xl font-bold text-orange-600">{{ stats.total_students || 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex space-x-4">
        <button
          @click="openSchoolModal"
          class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
        >
          <i class="fas fa-plus mr-2"></i>
          Add School
        </button>
        <button
          @click="refreshData"
          class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors flex items-center"
          :disabled="loading"
        >
          <i :class="loading ? 'fas fa-spinner fa-spin' : 'fas fa-sync'" class="mr-2"></i>
          Refresh
        </button>
      </div>
    </div>

    <!-- Schools List -->
    <div class="flex-1 overflow-y-auto p-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-800">Schools Management</h2>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <!-- Schools Table -->
        <div v-else-if="schools.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  School
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Code
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Users
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="school in schools" :key="school.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ school.name }}</div>
                    <div class="text-sm text-gray-500">{{ school.email }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ school.code }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="space-y-1">
                    <div>Users: {{ school.users_count || 0 }}</div>
                    <div>Admins: {{ school.admins_count || 0 }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="school.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ school.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="editSchool(school)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Edit School"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <button
                      @click="openSubAdminModal(school)"
                      class="text-green-600 hover:text-green-900"
                      title="Add Sub Admin"
                    >
                      <i class="fas fa-user-plus"></i>
                    </button>
                    <button
                      @click="viewSchoolDetails(school)"
                      class="text-purple-600 hover:text-purple-900"
                      title="View Details"
                    >
                      <i class="fas fa-eye"></i>
                    </button>
                    <button
                      @click="deleteSchool(school)"
                      class="text-red-600 hover:text-red-900"
                      title="Delete School"
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
        <div v-else class="text-center py-12">
          <i class="fas fa-school text-gray-400 text-4xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No schools found</h3>
          <p class="text-gray-500">Start by adding your first school.</p>
        </div>
      </div>
    </div>

    <!-- School Modal -->
    <div v-if="showSchoolModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            {{ selectedSchool ? 'Edit School' : 'Add School' }}
          </h3>
          <button @click="closeSchoolModal" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <form @submit.prevent="saveSchool" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">School Name</label>
            <input
              v-model="schoolForm.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">School Code</label>
            <input
              v-model="schoolForm.code"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="schoolForm.email"
              type="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="schoolForm.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeSchoolModal"
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              :disabled="modalLoading"
            >
              {{ modalLoading ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Sub Admin Modal -->
    <div v-if="showSubAdminModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Add Sub Admin for {{ selectedSchool?.name }}</h3>
          <button @click="closeSubAdminModal" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <form @submit.prevent="createSubAdmin" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input
              v-model="subAdminForm.username"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="subAdminForm.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
            <input
              v-model="subAdminForm.first_name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
            <input
              v-model="subAdminForm.last_name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
              v-model="subAdminForm.password"
              type="password"
              required
              minlength="6"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeSubAdminModal"
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
              :disabled="modalLoading"
            >
              {{ modalLoading ? 'Creating...' : 'Create Sub Admin' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { superAdminAPI } from '@/api/superAdmin'

// State
const loading = ref(true)
const modalLoading = ref(false)
const schools = ref([])
const stats = ref({})
const showSchoolModal = ref(false)
const showSubAdminModal = ref(false)
const selectedSchool = ref(null)

// Form data
const schoolForm = ref({
  name: '',
  code: '',
  email: '',
  status: 'active'
})

const subAdminForm = ref({
  username: '',
  email: '',
  first_name: '',
  last_name: '',
  password: ''
})

// Methods
const loadSchools = async () => {
  try {
    loading.value = true
    const response = await superAdminAPI.getSchools()
    schools.value = response.data.data.data || response.data.data
  } catch (error) {
    console.error('Error loading schools:', error)
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  try {
    const response = await superAdminAPI.getStats()
    stats.value = response.data.data
  } catch (error) {
    console.error('Error loading stats:', error)
  }
}

const refreshData = async () => {
  await Promise.all([loadSchools(), loadStats()])
}

const openSchoolModal = () => {
  selectedSchool.value = null
  schoolForm.value = {
    name: '',
    code: '',
    email: '',
    status: 'active'
  }
  showSchoolModal.value = true
}

const editSchool = (school) => {
  selectedSchool.value = school
  schoolForm.value = { ...school }
  showSchoolModal.value = true
}

const closeSchoolModal = () => {
  showSchoolModal.value = false
  selectedSchool.value = null
}

const saveSchool = async () => {
  try {
    modalLoading.value = true
    
    if (selectedSchool.value) {
      await superAdminAPI.updateSchool(selectedSchool.value.id, schoolForm.value)
    } else {
      await superAdminAPI.createSchool(schoolForm.value)
    }
    
    closeSchoolModal()
    await loadSchools()
  } catch (error) {
    console.error('Error saving school:', error)
  } finally {
    modalLoading.value = false
  }
}

const openSubAdminModal = (school) => {
  selectedSchool.value = school
  subAdminForm.value = {
    username: '',
    email: '',
    first_name: '',
    last_name: '',
    password: ''
  }
  showSubAdminModal.value = true
}

const closeSubAdminModal = () => {
  showSubAdminModal.value = false
  selectedSchool.value = null
}

const createSubAdmin = async () => {
  try {
    modalLoading.value = true
    await superAdminAPI.createSubAdmin(selectedSchool.value.id, subAdminForm.value)
    closeSubAdminModal()
    await loadSchools()
  } catch (error) {
    console.error('Error creating sub admin:', error)
  } finally {
    modalLoading.value = false
  }
}

const deleteSchool = async (school) => {
  if (confirm(`Are you sure you want to delete ${school.name}?`)) {
    try {
      await superAdminAPI.deleteSchool(school.id)
      await loadSchools()
    } catch (error) {
      console.error('Error deleting school:', error)
    }
  }
}

const viewSchoolDetails = (school) => {
  window.location.href = `/super-admin/schools/${school.id}`
}

onMounted(() => {
  refreshData()
})
</script>
