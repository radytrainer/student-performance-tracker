<template>
  <div v-if="hasPermission('student.view_own_attendance')" class="p-6">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">My Attendance</h1>
      <p class="text-gray-600 mt-1">Track your attendance records and patterns</p>
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
          <h3 class="text-sm font-medium text-red-800">Error loading attendance</h3>
          <p class="text-sm text-red-700 mt-1">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-6">
      <!-- Overall Attendance Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check text-green-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-green-600">{{ attendanceStats.overall }}%</div>
              <div class="text-sm text-gray-600">Overall Attendance</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-blue-600">{{ attendanceStats.present }}</div>
              <div class="text-sm text-gray-600">Days Present</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-times text-red-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-red-600">{{ attendanceStats.absent }}</div>
              <div class="text-sm text-gray-600">Days Absent</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-yellow-600">{{ attendanceStats.late }}</div>
              <div class="text-sm text-gray-600">Times Late</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Time Period</label>
            <select
              v-model="selectedPeriod"
              @change="filterAttendance"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="current_month">Current Month</option>
              <option value="last_month">Last Month</option>
              <option value="current_semester">Current Semester</option>
              <option value="last_semester">Last Semester</option>
              <option value="academic_year">Academic Year</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
            <select
              v-model="selectedSubject"
              @change="filterAttendance"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Subjects</option>
              <option v-for="subject in subjects" :key="subject" :value="subject">{{ subject }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status Filter</label>
            <select
              v-model="statusFilter"
              @change="filterAttendance"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Status</option>
              <option value="present">Present</option>
              <option value="absent">Absent</option>
              <option value="late">Late</option>
              <option value="excused">Excused</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Attendance by Subject -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="subject in subjectAttendance" :key="subject.id" 
             class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                   :style="{ backgroundColor: subject.color + '20' }">
                <i :class="subject.icon" :style="{ color: subject.color }"></i>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ subject.name }}</h3>
                <p class="text-sm text-gray-500">{{ subject.teacher }}</p>
              </div>
            </div>
          </div>

          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Attendance Rate</span>
              <span class="text-lg font-bold" :class="getAttendanceRateClass(subject.attendanceRate)">
                {{ subject.attendanceRate }}%
              </span>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="h-2 rounded-full transition-all duration-300"
                :class="getAttendanceBarClass(subject.attendanceRate)"
                :style="{ width: subject.attendanceRate + '%' }"
              ></div>
            </div>

            <div class="grid grid-cols-3 gap-2 text-center">
              <div>
                <div class="text-lg font-semibold text-green-600">{{ subject.present }}</div>
                <div class="text-xs text-gray-500">Present</div>
              </div>
              <div>
                <div class="text-lg font-semibold text-red-600">{{ subject.absent }}</div>
                <div class="text-xs text-gray-500">Absent</div>
              </div>
              <div>
                <div class="text-lg font-semibold text-yellow-600">{{ subject.late }}</div>
                <div class="text-xs text-gray-500">Late</div>
              </div>
            </div>
          </div>

          <button
            @click="viewSubjectDetails(subject)"
            class="w-full mt-4 text-blue-600 hover:text-blue-800 text-sm font-medium"
          >
            View Details
          </button>
        </div>
      </div>

      <!-- Attendance Calendar/Timeline -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Attendance History</h2>
            <div class="flex items-center space-x-2">
              <button
                @click="viewType = 'calendar'"
                :class="viewType === 'calendar' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:text-gray-800'"
                class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
              >
                Calendar View
              </button>
              <button
                @click="viewType = 'list'"
                :class="viewType === 'list' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:text-gray-800'"
                class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
              >
                List View
              </button>
            </div>
          </div>
        </div>

        <!-- Calendar View -->
        <div v-if="viewType === 'calendar'" class="p-6">
          <div class="text-center py-12">
            <i class="fas fa-calendar-alt text-gray-400 text-4xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Attendance Calendar</h3>
            <p class="text-gray-500">Interactive attendance calendar will be implemented here.</p>
          </div>
        </div>

        <!-- List View -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="record in filteredAttendanceRecords" :key="record.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(record.date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.subject }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  Period {{ record.period }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getStatusClass(record.status)">
                    <i :class="getStatusIcon(record.status)" class="mr-1"></i>
                    {{ capitalizeFirst(record.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ record.time || '-' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ record.notes || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Attendance Alerts -->
      <div v-if="attendanceAlerts.length > 0" class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-800">Attendance Alerts</h2>
        <div v-for="alert in attendanceAlerts" :key="alert.id" 
             class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-yellow-800">{{ alert.title }}</h3>
              <p class="text-sm text-yellow-700 mt-1">{{ alert.message }}</p>
            </div>
          </div>
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
          <p class="text-sm text-red-700 mt-1">You don't have permission to view attendance records.</p>
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
const selectedPeriod = ref('current_month')
const selectedSubject = ref('')
const statusFilter = ref('')
const viewType = ref('list')

// Mock data
const attendanceStats = ref({
  overall: 92,
  present: 85,
  absent: 7,
  late: 3
})

const subjectAttendance = ref([
  {
    id: 1,
    name: 'Mathematics',
    teacher: 'Dr. Smith',
    color: '#3B82F6',
    icon: 'fas fa-square-root-alt',
    attendanceRate: 95,
    present: 38,
    absent: 2,
    late: 0
  },
  {
    id: 2,
    name: 'Physics',
    teacher: 'Prof. Johnson',
    color: '#10B981',
    icon: 'fas fa-atom',
    attendanceRate: 88,
    present: 35,
    absent: 4,
    late: 1
  },
  {
    id: 3,
    name: 'Chemistry',
    teacher: 'Ms. Williams',
    color: '#F59E0B',
    icon: 'fas fa-flask',
    attendanceRate: 93,
    present: 37,
    absent: 2,
    late: 1
  }
])

const attendanceRecords = ref([
  {
    id: 1,
    date: '2024-01-15',
    subject: 'Mathematics',
    period: 1,
    status: 'present',
    time: '08:00 AM',
    notes: ''
  },
  {
    id: 2,
    date: '2024-01-15',
    subject: 'Physics',
    period: 2,
    status: 'late',
    time: '09:15 AM',
    notes: 'Transportation delay'
  },
  {
    id: 3,
    date: '2024-01-14',
    subject: 'Mathematics',
    period: 1,
    status: 'present',
    time: '08:00 AM',
    notes: ''
  },
  {
    id: 4,
    date: '2024-01-14',
    subject: 'Chemistry',
    period: 3,
    status: 'absent',
    time: '',
    notes: 'Sick leave'
  }
])

const attendanceAlerts = ref([
  {
    id: 1,
    title: 'Attendance Warning',
    message: 'Your attendance in Physics is below the required 90%. Current rate: 88%'
  }
])

// Computed
const subjects = computed(() => {
  return [...new Set(attendanceRecords.value.map(r => r.subject))]
})

const filteredAttendanceRecords = computed(() => {
  let filtered = attendanceRecords.value

  if (selectedSubject.value) {
    filtered = filtered.filter(r => r.subject === selectedSubject.value)
  }

  if (statusFilter.value) {
    filtered = filtered.filter(r => r.status === statusFilter.value)
  }

  return filtered.sort((a, b) => new Date(b.date) - new Date(a.date))
})

// Methods
const getAttendanceRateClass = (rate) => {
  if (rate >= 95) return 'text-green-600'
  if (rate >= 90) return 'text-blue-600'
  if (rate >= 80) return 'text-yellow-600'
  return 'text-red-600'
}

const getAttendanceBarClass = (rate) => {
  if (rate >= 95) return 'bg-green-500'
  if (rate >= 90) return 'bg-blue-500'
  if (rate >= 80) return 'bg-yellow-500'
  return 'bg-red-500'
}

const getStatusClass = (status) => {
  const classes = {
    present: 'bg-green-100 text-green-800',
    absent: 'bg-red-100 text-red-800',
    late: 'bg-yellow-100 text-yellow-800',
    excused: 'bg-blue-100 text-blue-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusIcon = (status) => {
  const icons = {
    present: 'fas fa-check',
    absent: 'fas fa-times',
    late: 'fas fa-clock',
    excused: 'fas fa-file-medical'
  }
  return icons[status] || 'fas fa-question'
}

const capitalizeFirst = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const filterAttendance = () => {
  // TODO: Implement filtering logic based on selected filters
  console.log('Filtering attendance:', {
    period: selectedPeriod.value,
    subject: selectedSubject.value,
    status: statusFilter.value
  })
}

const viewSubjectDetails = (subject) => {
  console.log('View subject attendance details:', subject)
}

const loadAttendance = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // Data is already loaded as mock data
  } catch (err) {
    error.value = err.message || 'Failed to load attendance records'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadAttendance()
})
</script>
