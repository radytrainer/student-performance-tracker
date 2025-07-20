<template>
  <div v-if="hasPermission('teacher.manage_attendance')" class="p-6">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Attendance Management</h1>
      <p class="text-gray-600 mt-1">Track and manage student attendance</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-6">
      <!-- Controls -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
            <select
              v-model="selectedClass"
              @change="loadAttendance"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Select a class</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                {{ cls.subject }} - {{ cls.section }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
            <input
              v-model="selectedDate"
              @change="loadAttendance"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
            <select
              v-model="selectedPeriod"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="1">1st Period</option>
              <option value="2">2nd Period</option>
              <option value="3">3rd Period</option>
              <option value="4">4th Period</option>
              <option value="5">5th Period</option>
              <option value="6">6th Period</option>
            </select>
          </div>

          <div class="flex items-end space-x-2">
            <button
              @click="takeAttendance"
              :disabled="!selectedClass"
              class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
            >
              <i class="fas fa-calendar-check mr-2"></i>
              Take Attendance
            </button>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div v-if="selectedClass && attendanceData.length > 0" class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check text-green-600"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-green-600">{{ stats.present }}</div>
              <div class="text-sm text-gray-600">Present</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-times text-red-600"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-red-600">{{ stats.absent }}</div>
              <div class="text-sm text-gray-600">Absent</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-yellow-600">{{ stats.late }}</div>
              <div class="text-sm text-gray-600">Late</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-percentage text-blue-600"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-blue-600">{{ stats.attendanceRate }}%</div>
              <div class="text-sm text-gray-600">Attendance Rate</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Table -->
      <div v-if="selectedClass" class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">
              Attendance for {{ formatDate(selectedDate) }}
            </h2>
            <div class="flex items-center space-x-3">
              <button
                @click="markAllPresent"
                class="text-green-600 hover:text-green-800 text-sm font-medium"
              >
                Mark All Present
              </button>
              <button
                @click="saveAttendance"
                :disabled="!hasChanges"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
              >
                Save Changes
              </button>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Present</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Absent</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Late</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Excused</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="record in attendanceData" :key="record.studentId" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-600 font-medium">{{ record.student.name.charAt(0) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ record.student.name }}</div>
                      <div class="text-sm text-gray-500">{{ record.student.id }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <input
                    v-model="record.status"
                    @change="markChanged"
                    type="radio"
                    value="present"
                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <input
                    v-model="record.status"
                    @change="markChanged"
                    type="radio"
                    value="absent"
                    class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <input
                    v-model="record.status"
                    @change="markChanged"
                    type="radio"
                    value="late"
                    class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <input
                    v-model="record.excused"
                    @change="markChanged"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                </td>
                <td class="px-6 py-4">
                  <input
                    v-model="record.notes"
                    @change="markChanged"
                    type="text"
                    placeholder="Add notes..."
                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span class="text-sm font-medium" :class="getAttendanceRateClass(record.attendanceRate)">
                    {{ record.attendanceRate }}%
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="attendanceData.length === 0" class="text-center py-12">
          <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No students found</h3>
          <p class="text-gray-500">Select a class to view student attendance records.</p>
        </div>
      </div>

      <!-- No Class Selected -->
      <div v-else class="text-center py-12">
        <i class="fas fa-calendar-check text-gray-400 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Select a class to manage attendance</h3>
        <p class="text-gray-500">Choose a class from the dropdown above to take attendance.</p>
      </div>

      <!-- Attendance History -->
      <div v-if="selectedClass" class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Recent Attendance Records</h2>
        </div>
        <div class="p-6">
          <div class="space-y-3">
            <div v-for="record in recentAttendance" :key="record.date" 
                 class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
              <div>
                <div class="font-medium text-gray-900">{{ formatDate(record.date) }}</div>
                <div class="text-sm text-gray-500">Period {{ record.period }}</div>
              </div>
              <div class="flex items-center space-x-4">
                <div class="text-center">
                  <div class="text-lg font-semibold text-green-600">{{ record.present }}</div>
                  <div class="text-xs text-gray-500">Present</div>
                </div>
                <div class="text-center">
                  <div class="text-lg font-semibold text-red-600">{{ record.absent }}</div>
                  <div class="text-xs text-gray-500">Absent</div>
                </div>
                <div class="text-center">
                  <div class="text-lg font-semibold text-yellow-600">{{ record.late }}</div>
                  <div class="text-xs text-gray-500">Late</div>
                </div>
                <button
                  @click="viewAttendanceDetails(record)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  View Details
                </button>
              </div>
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
          <p class="text-sm text-red-700 mt-1">You don't have permission to manage attendance.</p>
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
const classes = ref([])
const attendanceData = ref([])
const recentAttendance = ref([])
const selectedClass = ref('')
const selectedDate = ref(new Date().toISOString().split('T')[0])
const selectedPeriod = ref('1')
const hasChanges = ref(false)

// Mock data
const mockClasses = [
  { id: 1, subject: 'Mathematics', section: 'Section A', grade: '11' },
  { id: 2, subject: 'Physics', section: 'Section B', grade: '12' },
  { id: 3, subject: 'Chemistry', section: 'Section A', grade: '12' }
]

const mockAttendanceData = [
  {
    studentId: 1,
    student: { id: 'ST001', name: 'Alice Johnson' },
    status: 'present',
    excused: false,
    notes: '',
    attendanceRate: 95
  },
  {
    studentId: 2,
    student: { id: 'ST002', name: 'Bob Smith' },
    status: 'absent',
    excused: true,
    notes: 'Doctor appointment',
    attendanceRate: 88
  },
  {
    studentId: 3,
    student: { id: 'ST003', name: 'Carol Davis' },
    status: 'late',
    excused: false,
    notes: 'Transportation delay',
    attendanceRate: 92
  }
]

const mockRecentAttendance = [
  { date: '2024-01-14', period: 1, present: 26, absent: 2, late: 0 },
  { date: '2024-01-13', period: 1, present: 25, absent: 1, late: 2 },
  { date: '2024-01-12', period: 1, present: 28, absent: 0, late: 0 }
]

// Computed
const stats = computed(() => {
  const present = attendanceData.value.filter(r => r.status === 'present').length
  const absent = attendanceData.value.filter(r => r.status === 'absent').length
  const late = attendanceData.value.filter(r => r.status === 'late').length
  const total = attendanceData.value.length
  const attendanceRate = total > 0 ? Math.round(((present + late) / total) * 100) : 0

  return {
    present,
    absent,
    late,
    attendanceRate
  }
})

// Methods
const loadAttendance = async () => {
  if (!selectedClass.value) return
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 500))
    attendanceData.value = mockAttendanceData
    recentAttendance.value = mockRecentAttendance
    hasChanges.value = false
  } catch (error) {
    console.error('Error loading attendance:', error)
  }
}

const takeAttendance = () => {
  if (!selectedClass.value) return
  loadAttendance()
}

const markAllPresent = () => {
  attendanceData.value.forEach(record => {
    record.status = 'present'
  })
  markChanged()
}

const markChanged = () => {
  hasChanges.value = true
}

const saveAttendance = async () => {
  try {
    // TODO: Save to API
    console.log('Saving attendance:', {
      classId: selectedClass.value,
      date: selectedDate.value,
      period: selectedPeriod.value,
      records: attendanceData.value
    })
    
    hasChanges.value = false
    alert('Attendance saved successfully!')
  } catch (error) {
    console.error('Error saving attendance:', error)
    alert('Failed to save attendance. Please try again.')
  }
}

const getAttendanceRateClass = (rate) => {
  if (rate >= 90) return 'text-green-600'
  if (rate >= 80) return 'text-yellow-600'
  return 'text-red-600'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const viewAttendanceDetails = (record) => {
  console.log('View attendance details:', record)
}

onMounted(async () => {
  try {
    loading.value = true
    // Load classes
    await new Promise(resolve => setTimeout(resolve, 1000))
    classes.value = mockClasses
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    loading.value = false
  }
})
</script>
