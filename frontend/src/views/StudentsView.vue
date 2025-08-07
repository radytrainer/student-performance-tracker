<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-4 sm:py-8">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
      <!-- Header Section -->
      <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-2">Students</h1>
            <p class="text-sm sm:text-base text-gray-600">Manage and view all student profiles</p>
          </div>
          <button
            @click="showAddModal = true"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg"
          >
            <Plus class="w-5 h-5 mr-2" />
            Add Student
          </button>
        </div>
      </div>

      <!-- Search and Filter Section -->
      <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search students by name or email..."
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              />
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            </div>
          </div>
          <div class="flex gap-2">
            <select
              v-model="selectedRole"
              class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            >
              <option value="">All Roles</option>
              <option value="Student">Student</option>
              <option value="Graduate">Graduate</option>
              <option value="Alumni">Alumni</option>
            </select>
            <select
              v-model="sortBy"
              class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            >
              <option value="name">Sort by Name</option>
              <option value="email">Sort by Email</option>
              <option value="createdAt">Sort by Date</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Students Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="student in filteredStudents"
          :key="student.id"
          class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer group"
          @click="viewStudent(student)"
        >
          <!-- Student Card Header -->
          <div class="relative bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white">
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full overflow-hidden bg-white/20 backdrop-blur-sm border-2 border-white/30 flex-shrink-0">
                <img
                  v-if="student.profileImage"
                  :src="student.profileImage"
                  :alt="student.name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <span class="text-xl font-bold text-white">
                    {{ getInitials(student.name) }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold truncate">{{ student.name }}</h3>
                <p class="text-blue-100 text-sm truncate">{{ student.email }}</p>
              </div>
            </div>
            <div class="absolute top-4 right-4">
              <span :class="getStatusBadgeClass(student.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                {{ student.status }}
              </span>
            </div>
          </div>

          <!-- Student Card Body -->
          <div class="p-6">
            <div class="space-y-3">
              <div class="flex items-center text-sm text-gray-600">
                <Briefcase class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" />
                <span class="truncate">{{ student.role }}</span>
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <Phone class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" />
                <span class="truncate">{{ student.phone || 'No phone' }}</span>
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <Calendar class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" />
                <span class="truncate">Joined {{ formatDate(student.createdAt) }}</span>
              </div>
            </div>
                        
            <!-- Action Buttons -->
            <div class="flex gap-2 mt-4">
              <button
                @click.stop="viewStudent(student)"
                class="flex-1 px-3 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-200 text-sm font-medium"
              >
                View Details
              </button>
              <button
                @click.stop="editStudent(student)"
                class="px-3 py-2 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors duration-200"
                :title="'Edit ' + student.name"
              >
                <Edit class="w-4 h-4" />
              </button>
              <button
                @click.stop="deleteStudent(student)"
                class="px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors duration-200"
                :title="'Delete ' + student.name"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredStudents.length === 0" class="text-center py-12">
        <Users class="w-16 h-16 mx-auto text-gray-400 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No students found</h3>
        <p class="text-gray-500 mb-4">Try adjusting your search or filter criteria.</p>
        <button
          @click="clearFilters"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
        >
          <RotateCcw class="w-4 h-4 mr-2" />
          Clear Filters
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Loading students...</p>
      </div>
    </div>

    <!-- Add Student Modal -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click.self="showAddModal = false"
    >
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Add New Student</h2>
            <button
              @click="showAddModal = false"
              class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
            >
              <X class="w-6 h-6" />
            </button>
          </div>
        </div>
        <form @submit.prevent="addStudent" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input
              v-model="newStudent.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter student name"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="newStudent.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter email address"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input
              v-model="newStudent.phone"
              type="tel"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter phone number"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select
              v-model="newStudent.role"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select role</option>
              <option value="Student">Student</option>
              <option value="Graduate">Graduate</option>
              <option value="Alumni">Alumni</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="newStudent.status"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select status</option>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="showAddModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
            >
              Add Student
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click.self="showDeleteModal = false"
    >
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
              <AlertTriangle class="w-6 h-6 text-red-600" />
            </div>
            <div>
              <h3 class="text-lg font-medium text-gray-900">Delete Student</h3>
              <p class="text-sm text-gray-500">This action cannot be undone.</p>
            </div>
          </div>
          <p class="text-gray-700 mb-6">
            Are you sure you want to delete <strong>{{ studentToDelete?.name }}</strong>?
          </p>
          <div class="flex gap-3">
            <button
              @click="showDeleteModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              @click="confirmDelete"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { 
  Plus, Search, Briefcase, Phone, Calendar, Edit, Trash2, 
  Users, RotateCcw, X, AlertTriangle 
} from 'lucide-vue-next'

// Reactive state
const searchQuery = ref('')
const selectedRole = ref('')
const sortBy = ref('name')
const showAddModal = ref(false)
const showDeleteModal = ref(false)
const loading = ref(false)
const studentToDelete = ref(null)

// New student form data
const newStudent = reactive({
  name: '',
  email: '',
  phone: '',
  role: '',
  status: ''
})

// Sample students data
const students = ref([
  {
    id: 1,
    name: 'Em Sophy',
    email: 'sophy.em@student.passerellesnumeriques.org',
    phone: '+855 12 345 678',
    role: 'Student',
    status: 'Active',
    createdAt: '2023-01-15',
    profileImage: ''
  },
  {
    id: 2,
    name: 'Sok Dara',
    email: 'dara.sok@student.passerellesnumeriques.org',
    phone: '+855 98 765 432',
    role: 'Student',
    status: 'Active',
    createdAt: '2023-02-20',
    profileImage: ''
  },
  {
    id: 3,
    name: 'Chea Pisach',
    email: 'pisach.chea@student.passerellesnumeriques.org',
    phone: '+855 77 888 999',
    role: 'Graduate',
    status: 'Inactive',
    createdAt: '2022-09-10',
    profileImage: ''
  },
  {
    id: 4,
    name: 'Lim Sophea',
    email: 'sophea.lim@student.passerellesnumeriques.org',
    phone: '+855 11 222 333',
    role: 'Alumni',
    status: 'Active',
    createdAt: '2021-03-05',
    profileImage: ''
  },
  {
    id: 5,
    name: 'Chan Mony',
    email: 'mony.chan@student.passerellesnumeriques.org',
    phone: '+855 66 777 888',
    role: 'Student',
    status: 'Active',
    createdAt: '2023-03-12',
    profileImage: ''
  }
])

// Computed properties
const filteredStudents = computed(() => {
  let filtered = [...students.value]
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase().trim()
    filtered = filtered.filter(student => 
      student.name.toLowerCase().includes(query) ||
      student.email.toLowerCase().includes(query)
    )
  }
  
  // Filter by role
  if (selectedRole.value) {
    filtered = filtered.filter(student => student.role === selectedRole.value)
  }
  
  // Sort
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'name':
        return a.name.localeCompare(b.name)
      case 'email':
        return a.email.localeCompare(b.email)
      case 'createdAt':
        return new Date(b.createdAt) - new Date(a.createdAt)
      default:
        return 0
    }
  })
  
  return filtered
})

// Methods
const getInitials = (name) => {
  if (!name) return ''
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'Active':
      return 'bg-green-500/20 text-green-100 border border-green-400/30'
    case 'Inactive':
      return 'bg-red-500/20 text-red-100 border border-red-400/30'
    default:
      return 'bg-gray-500/20 text-gray-100 border border-gray-400/30'
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  try {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch (error) {
    return 'N/A'
  }
}

const viewStudent = (student) => {
  console.log('Viewing student:', student)
  // In a real app: router.push(`/students/${student.id}`)
}

const editStudent = (student) => {
  console.log('Editing student:', student)
  // In a real app: router.push(`/students/${student.id}/edit`)
}

const deleteStudent = (student) => {
  studentToDelete.value = student
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (studentToDelete.value) {
    const index = students.value.findIndex(s => s.id === studentToDelete.value.id)
    if (index > -1) {
      students.value.splice(index, 1)
    }
  }
  showDeleteModal.value = false
  studentToDelete.value = null
}

const addStudent = () => {
  const student = {
    id: Date.now(),
    name: newStudent.name,
    email: newStudent.email,
    phone: newStudent.phone,
    role: newStudent.role,
    status: newStudent.status,
    createdAt: new Date().toISOString().split('T')[0],
    profileImage: ''
  }
  
  students.value.unshift(student)
  
  // Reset form
  Object.keys(newStudent).forEach(key => {
    newStudent[key] = ''
  })
  
  showAddModal.value = false
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedRole.value = ''
  sortBy.value = 'name'
}

const loadStudents = async () => {
  loading.value = true
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    console.log('Students loaded')
  } catch (error) {
    console.error('Error loading students:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadStudents()
})
</script>