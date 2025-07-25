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
                <h3 class="text-lg font-semibold text-gray-900">{{ cls.name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ cls.section }}</p>
                <div class="mt-3 space-y-2">
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-graduation-cap mr-2"></i>
                    Grade {{ cls.grade }}
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                    {{ cls.teacher }}
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-users mr-2"></i>
                    {{ cls.studentCount }} students
                  </div>
                </div>
              </div>
              <div class="flex-shrink-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="cls.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                  {{ cls.active ? 'Active' : 'Inactive' }}
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
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuth } from '@/composables/useAuth'

const { hasPermission } = useAuth()

// State
const loading = ref(true)
const error = ref(null)
const classes = ref([])
const searchQuery = ref('')
const gradeFilter = ref('')
const showCreateModal = ref(false)

// Mock data
const mockClasses = [
  {
    id: 1,
    name: 'Mathematics Advanced',
    section: 'Section A',
    grade: '11',
    teacher: 'Dr. Smith',
    studentCount: 28,
    active: true
  },
  {
    id: 2,
    name: 'Physics',
    section: 'Section B',
    grade: '12',
    teacher: 'Prof. Johnson',
    studentCount: 24,
    active: true
  },
  {
    id: 3,
    name: 'Chemistry',
    section: 'Section A',
    grade: '10',
    teacher: 'Ms. Williams',
    studentCount: 30,
    active: true
  }
]

// Computed
const filteredClasses = computed(() => {
  let filtered = classes.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(cls =>
      cls.name.toLowerCase().includes(query) ||
      cls.section.toLowerCase().includes(query) ||
      cls.teacher.toLowerCase().includes(query)
    )
  }

  if (gradeFilter.value) {
    filtered = filtered.filter(cls => cls.grade === gradeFilter.value)
  }

  return filtered
})

// Methods
const viewClass = (cls) => {
  // TODO: Navigate to class details view
  console.log('View class:', cls)
}

const editClass = (cls) => {
  // TODO: Implement edit class functionality
  console.log('Edit class:', cls)
}

const confirmDelete = (cls) => {
  // TODO: Implement delete confirmation and functionality
  if (confirm(`Are you sure you want to delete class ${cls.name}?`)) {
    console.log('Delete class:', cls)
  }
}

const loadClasses = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    classes.value = mockClasses
  } catch (err) {
    error.value = err.message || 'Failed to load classes'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadClasses()
})
</script>
