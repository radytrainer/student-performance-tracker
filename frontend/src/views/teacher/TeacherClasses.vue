<template>
  <div v-if="hasPermission('teacher.manage_classes')" class="p-6">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">My Classes</h1>
      <p class="text-gray-600 mt-1">Manage your assigned classes and students</p>
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
            v-model="subjectFilter"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">All Subjects</option>
            <option v-for="subject in subjects" :key="subject" :value="subject">{{ subject }}</option>
          </select>
        </div>
        <div class="text-sm text-gray-600">
          {{ filteredClasses.length }} class{{ filteredClasses.length !== 1 ? 'es' : '' }} assigned
        </div>
      </div>

      <!-- Classes Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="cls in filteredClasses"
          :key="cls.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ cls.subject }}</h3>
                <p class="text-sm text-gray-500">{{ cls.className }} • {{ cls.section }}</p>
                <div class="mt-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Grade {{ cls.grade }}
                  </span>
                </div>
              </div>
              <div class="flex-shrink-0">
                <i class="fas fa-chalkboard-teacher text-blue-500 text-xl"></i>
              </div>
            </div>

            <!-- Class Stats -->
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ cls.studentCount }}</div>
                <div class="text-sm text-gray-600">Students</div>
              </div>
              <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ cls.avgAttendance }}%</div>
                <div class="text-sm text-gray-600">Attendance</div>
              </div>
            </div>

            <!-- Recent Activity -->
            <div class="mb-4">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Recent Activity</h4>
              <div class="space-y-1">
                <div v-for="activity in cls.recentActivity" :key="activity.id" class="text-xs text-gray-600">
                  • {{ activity.description }}
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-2">
              <button
                @click="viewClass(cls)"
                class="flex-1 bg-blue-600 text-white text-sm py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors"
              >
                <i class="fas fa-eye mr-1"></i>
                View Class
              </button>
              <button
                @click="takeAttendance(cls)"
                class="flex-1 bg-green-600 text-white text-sm py-2 px-3 rounded-lg hover:bg-green-700 transition-colors"
              >
                <i class="fas fa-calendar-check mr-1"></i>
                Attendance
              </button>
            </div>

            <!-- Quick Actions -->
            <div class="mt-3 flex justify-between text-sm">
              <button
                @click="addGrade(cls)"
                class="text-blue-600 hover:text-blue-800 font-medium"
              >
                Add Grade
              </button>
              <button
                @click="viewReports(cls)"
                class="text-purple-600 hover:text-purple-800 font-medium"
              >
                View Reports
              </button>
              <button
                @click="manageClass(cls)"
                class="text-gray-600 hover:text-gray-800 font-medium"
              >
                Manage
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredClasses.length === 0" class="text-center py-12">
        <i class="fas fa-chalkboard text-gray-400 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No classes found</h3>
        <p class="text-gray-500">
          {{ classes.length === 0 ? 'You have no classes assigned yet.' : 'Try adjusting your search or filter criteria.' }}
        </p>
      </div>
    </div>

    <!-- Quick Action Modals -->
    <div v-if="showAttendanceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium mb-4">Take Attendance</h3>
        <p class="text-gray-600 mb-4">{{ selectedClass?.subject }} - {{ selectedClass?.section }}</p>
        <p class="text-gray-600 mb-4">Attendance taking interface will be implemented here.</p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showAttendanceModal = false"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="saveAttendance"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
          >
            Save Attendance
          </button>
        </div>
      </div>
    </div>

    <div v-if="showGradeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium mb-4">Add Grade</h3>
        <p class="text-gray-600 mb-4">{{ selectedClass?.subject }} - {{ selectedClass?.section }}</p>
        <p class="text-gray-600 mb-4">Grade entry form will be implemented here.</p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showGradeModal = false"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="saveGrade"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Add Grade
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Unauthorized Access -->
  <div v-else class="p-6">
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <i class="fas fa-exclamation-triangle text-red-400"></i>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Access Denied</h3>
          <p class="text-sm text-red-700 mt-1">You don't have permission to manage classes.</p>
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
const subjectFilter = ref('')
const showAttendanceModal = ref(false)
const showGradeModal = ref(false)
const selectedClass = ref(null)

// Mock data
const mockClasses = [
  {
    id: 1,
    subject: 'Mathematics',
    className: 'Advanced Algebra',
    section: 'Section A',
    grade: '11',
    studentCount: 28,
    avgAttendance: 92,
    recentActivity: [
      { id: 1, description: 'Quiz added for Chapter 5' },
      { id: 2, description: '3 students submitted assignment' },
      { id: 3, description: 'Attendance taken for today' }
    ]
  },
  {
    id: 2,
    subject: 'Physics',
    className: 'General Physics',
    section: 'Section B',
    grade: '12',
    studentCount: 24,
    avgAttendance: 88,
    recentActivity: [
      { id: 1, description: 'Lab report grades entered' },
      { id: 2, description: 'New assignment posted' }
    ]
  },
  {
    id: 3,
    subject: 'Chemistry',
    className: 'Organic Chemistry',
    section: 'Section A',
    grade: '12',
    studentCount: 22,
    avgAttendance: 95,
    recentActivity: [
      { id: 1, description: 'Exam scheduled for next week' },
      { id: 2, description: 'Study materials uploaded' }
    ]
  }
]

// Computed
const subjects = computed(() => {
  return [...new Set(classes.value.map(cls => cls.subject))]
})

const filteredClasses = computed(() => {
  let filtered = classes.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(cls =>
      cls.subject.toLowerCase().includes(query) ||
      cls.className.toLowerCase().includes(query) ||
      cls.section.toLowerCase().includes(query)
    )
  }

  if (subjectFilter.value) {
    filtered = filtered.filter(cls => cls.subject === subjectFilter.value)
  }

  return filtered
})

// Methods
const viewClass = (cls) => {
  // TODO: Navigate to class details view
  console.log('View class:', cls)
}

const takeAttendance = (cls) => {
  selectedClass.value = cls
  showAttendanceModal.value = true
}

const addGrade = (cls) => {
  selectedClass.value = cls
  showGradeModal.value = true
}

const viewReports = (cls) => {
  // TODO: Navigate to class reports
  console.log('View reports for class:', cls)
}

const manageClass = (cls) => {
  // TODO: Navigate to class management
  console.log('Manage class:', cls)
}

const saveAttendance = () => {
  // TODO: Implement attendance saving
  console.log('Save attendance for:', selectedClass.value)
  showAttendanceModal.value = false
}

const saveGrade = () => {
  // TODO: Implement grade saving
  console.log('Save grade for:', selectedClass.value)
  showGradeModal.value = false
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
