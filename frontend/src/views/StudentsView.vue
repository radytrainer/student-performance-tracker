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
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
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
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>
          </div>
          <div class="flex gap-2">
            <select
              v-model="selectedRole"
              class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Roles</option>
              <option value="Student">Student</option>
              <option value="Graduate">Graduate</option>
              <option value="Alumni">Alumni</option>
            </select>
            <select
              v-model="sortBy"
              class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
          class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer"
          @click="viewStudent(student)"
        >
          <!-- Student Card Header -->
          <div class="relative bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white">
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full overflow-hidden bg-white/20 backdrop-blur-sm border-2 border-white/30">
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
              <div class="flex-1">
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
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2h8z"/>
                </svg>
                {{ student.role }}
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                {{ student.phone || 'No phone' }}
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Joined {{ formatDate(student.createdAt) }}
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
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredStudents.length === 0" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No students found</h3>
        <p class="text-gray-500">Try adjusting your search or filter criteria.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Reactive state
const searchQuery = ref('')
const selectedRole = ref('')
const sortBy = ref('name')
const showAddModal = ref(false)

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
  }
])

// Computed properties
const filteredStudents = computed(() => {
  let filtered = students.value

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
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
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
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
      month: 'short'
    })
  } catch (error) {
    return 'N/A'
  }
}

const viewStudent = (student) => {
  // Navigate to student detail view
  console.log('Viewing student:', student)
  // In a real app: router.push(`/students/${student.id}`)
}

const editStudent = (student) => {
  // Navigate to edit student
  console.log('Editing student:', student)
  // In a real app: router.push(`/students/${student.id}/edit`)
}

onMounted(() => {
  // Load students data from API
  console.log('Students view mounted')
})
</script>
