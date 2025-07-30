<template>
  <div class="p-6">
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
            <!-- Class Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
              <select
                v-model="selectedClass"
                @change="handleClassChange"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                :disabled="loading"
              >
                <option value="">Select a class</option>
                <option 
                  v-for="cls in classes" 
                  :key="cls.id" 
                  :value="cls.id"
                >
                  {{ cls.subject }} - {{ cls.section }} (Grade {{ cls.grade }})
                </option>
              </select>
            </div>
            
            <!-- Date Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
              <input
                v-model="selectedDate"
                @change="handleDateChange"
                type="date"
                :max="today"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :disabled="loading"
              />
            </div>

            <!-- Period Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
              <select
                v-model="selectedPeriod"
                @change="handlePeriodChange"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                :disabled="loading"
              >
                <option value="1">1st Period</option>
                <option value="2">2nd Period</option>
                <option value="3">3rd Period</option>
                <option value="4">4th Period</option>
                <option value="5">5th Period</option>
                <option value="6">6th Period</option>
                <option value="7">7th Period</option>
                <option value="8">8th Period</option>
              </select>
            </div>

            <!-- Action Button -->
            <div class="flex items-end space-x-2">
              <button
                @click="loadAttendance"
                :disabled="!selectedClass || loading"
                class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
              >
                <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                {{ loading ? 'Loading...' : 'Load Attendance' }}
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
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
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
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
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
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
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
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
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
                Attendance for {{ formatDate(selectedDate) }} - Period {{ selectedPeriod }}
              </h2>
              <div class="flex items-center space-x-3">
                <button
                  @click="markAllPresent"
                  :disabled="loading || attendanceData.length === 0"
                  class="text-green-600 hover:text-green-800 text-sm font-medium disabled:opacity-50"
                >
                  Mark All Present
                </button>
                <button
                  @click="exportAttendance"
                  :disabled="loading"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium disabled:opacity-50"
                >
                  Export CSV
                </button>
                <button
                  @click="saveAttendance"
                  :disabled="!hasChanges || loading || attendanceData.length === 0"
                  class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ loading ? 'Saving...' : hasChanges ? 'Save Changes' : 'No Changes' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Attendance Table Content -->
          <div v-if="attendanceData.length > 0" class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Present</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Absent</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Late</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Excused</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Overall %</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="record in attendanceData" :key="record.studentId" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                          <span class="text-blue-600 font-medium text-sm">{{ getInitials(record.student.name) }}</span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ record.student.name }}</div>
                        <div class="text-sm text-gray-500">ID: {{ record.student.id }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <input
                      v-model="record.status"
                      @change="markChanged"
                      type="radio"
                      value="present"
                      :name="`status-${record.studentId}`"
                      class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <input
                      v-model="record.status"
                      @change="markChanged"
                      type="radio"
                      value="absent"
                      :name="`status-${record.studentId}`"
                      class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <input
                      v-model="record.status"
                      @change="markChanged"
                      type="radio"
                      value="late"
                      :name="`status-${record.studentId}`"
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
                      @input="markChanged"
                      type="text"
                      placeholder="Add notes..."
                      class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      maxlength="500"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="text-sm font-medium" :class="getAttendanceRateClass(record.attendanceRate)">
                      {{ record.attendanceRate }}%
                    </span>
                    <div v-if="record.attendanceRate < 75" class="text-xs text-red-500 mt-1">
                      ⚠️ Low Attendance
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State for Selected Class -->
          <div v-else class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No students found</h3>
            <p class="text-gray-500">No students are enrolled in this class or attendance data is not available.</p>
          </div>
        </div>

        <!-- No Class Selected State -->
        <div v-else class="text-center py-12">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Select a class to manage attendance</h3>
          <p class="text-gray-500">Choose a class from the dropdown above to load student attendance records.</p>
        </div>

        <!-- Recent Attendance History -->
        <div v-if="selectedClass && recentAttendance.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-800">Recent Attendance Records</h2>
              <button
                @click="showCalendarView = !showCalendarView"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
              >
                {{ showCalendarView ? 'Hide Calendar' : 'Show Calendar' }}
              </button>
            </div>
          </div>

          <!-- Calendar View -->
          <div v-if="showCalendarView" class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-7 gap-2 text-center text-sm">
              <div class="font-medium text-gray-700 p-2">Mon</div>
              <div class="font-medium text-gray-700 p-2">Tue</div>
              <div class="font-medium text-gray-700 p-2">Wed</div>
              <div class="font-medium text-gray-700 p-2">Thu</div>
              <div class="font-medium text-gray-700 p-2">Fri</div>
              <div class="font-medium text-gray-700 p-2">Sat</div>
              <div class="font-medium text-gray-700 p-2">Sun</div>
              
              <div v-for="day in calendarDays" :key="day.date" class="p-2 rounded hover:bg-gray-50">
                <div class="text-sm font-medium">{{ day.day }}</div>
                <div v-if="day.attendance" class="mt-1">
                  <div class="w-3 h-3 rounded-full mx-auto" :class="getCalendarDotColor(day.attendance.rate)"></div>
                  <div class="text-xs mt-1">{{ day.attendance.rate }}%</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Records List -->
          <div class="p-6">
            <div class="space-y-3">
              <div v-for="record in recentAttendance" :key="`${record.date}-${record.period}`"
                   class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div>
                  <div class="font-medium text-gray-900">{{ formatDate(record.date) }}</div>
                  <div class="text-sm text-gray-500">Period {{ record.period }} • {{ record.total }} students</div>
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
                  <div class="text-center">
                    <div class="text-lg font-semibold text-blue-600">{{ record.rate }}%</div>
                    <div class="text-xs text-gray-500">Rate</div>
                  </div>
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
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Access Denied</h3>
            <p class="text-sm text-red-700 mt-1">You don't have permission to manage attendance.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="message" class="fixed bottom-4 right-4 z-50">
      <div :class="messageClass" class="rounded-lg p-4 shadow-lg">
        <div class="flex items-center">
          <svg v-if="message.type === 'success'" class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <svg v-else class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          <span class="text-sm font-medium">{{ message.text }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

// Mock composables for demonstration
const hasPermission = (permission) => true // Replace with actual auth logic

// Reactive state
const loading = ref(false)
const classes = ref([
  { id: 1, subject: 'Mathematics', section: 'A', grade: '10' },
  { id: 2, subject: 'Physics', section: 'A', grade: '10' },
  { id: 3, subject: 'Chemistry', section: 'B', grade: '10' }
])

const attendanceData = ref([])
const recentAttendance = ref([])
const selectedClass = ref('')
const selectedDate = ref(new Date().toISOString().split('T')[0])
const selectedPeriod = ref('1')
const hasChanges = ref(false)
const showCalendarView = ref(false)
const message = ref(null)

// Mock calendar data
const calendarDays = ref([
  { date: '2024-01-15', day: '15', attendance: { rate: 95 } },
  { date: '2024-01-16', day: '16', attendance: { rate: 88 } },
  { date: '2024-01-17', day: '17', attendance: { rate: 92 } },
  { date: '2024-01-18', day: '18', attendance: { rate: 85 } },
  { date: '2024-01-19', day: '19', attendance: { rate: 90 } }
])

// Computed properties
const today = computed(() => new Date().toISOString().split('T')[0])

const stats = computed(() => {
  const present = attendanceData.value.filter(r => r.status === 'present').length
  const absent = attendanceData.value.filter(r => r.status === 'absent').length
  const late = attendanceData.value.filter(r => r.status === 'late').length
  const total = attendanceData.value.length
  const attendanceRate = total > 0 ? Math.round(((present + late) / total) * 100) : 0

  return { present, absent, late, attendanceRate }
})

const messageClass = computed(() => {
  if (!message.value) return ''
  return message.value.type === 'success' 
    ? 'bg-green-50 border border-green-200 text-green-800'
    : 'bg-red-50 border border-red-200 text-red-800'
})

// Methods
const showMessage = (text, type = 'success') => {
  message.value = { text, type }
  setTimeout(() => {
    message.value = null
  }, 3000)
}

const handleClassChange = () => {
  if (selectedClass.value) {
    loadAttendance()
  } else {
    attendanceData.value = []
    recentAttendance.value = []
  }
}

const handleDateChange = () => {
  if (selectedClass.value) {
    loadAttendance()
  }
}

const handlePeriodChange = () => {
  if (selectedClass.value) {
    loadAttendance()
  }
}

const loadAttendance = async () => {
  if (!selectedClass.value) return

  loading.value = true
  hasChanges.value = false

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // Mock student data
    attendanceData.value = [
      {
        studentId: 1,
        student: { id: 'S001', name: 'Alice Johnson' },
        status: 'present',
        excused: false,
        notes: '',
        attendanceRate: 95
      },
      {
        studentId: 2,
        student: { id: 'S002', name: 'Bob Smith' },
        status: 'present',
        excused: false,
        notes: '',
        attendanceRate: 88
      },
      {
        studentId: 3,
        student: { id: 'S003', name: 'Carol Davis' },
        status: 'absent',
        excused: false,
        notes: '',
        attendanceRate: 72
      },
      {
        studentId: 4,
        student: { id: 'S004', name: 'David Wilson' },
        status: 'late',
        excused: false,
        notes: 'Traffic delay',
        attendanceRate: 91
      }
    ]

    // Mock recent attendance
    recentAttendance.value = [
      {
        date: '2024-01-15',
        period: 1,
        present: 18,
        absent: 2,
        late: 1,
        total: 21,
        rate: 90
      },
      {
        date: '2024-01-14',
        period: 1,
        present: 19,
        absent: 1,
        late: 1,
        total: 21,
        rate: 95
      }
    ]

    showMessage('Attendance data loaded successfully')
  } catch (error) {
    showMessage('Failed to load attendance data', 'error')
  } finally {
    loading.value = false
  }
}

const saveAttendance = async () => {
  if (!hasChanges.value) return

  loading.value = true

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    hasChanges.value = false
    showMessage('Attendance saved successfully')
  } catch (error) {
    showMessage('Failed to save attendance', 'error')
  } finally {
    loading.value = false
  }
}

const markAllPresent = () => {
  attendanceData.value.forEach(record => {
    record.status = 'present'
  })
  markChanged()
  showMessage('All students marked as present')
}

const exportAttendance = async () => {
  try {
    // Simulate CSV export
    const csvContent = "data:text/csv;charset=utf-8," + 
      "Student ID,Student Name,Status,Excused,Notes\n" +
      attendanceData.value.map(record => 
        `${record.student.id},${record.student.name},${record.status},${record.excused ? 'Yes' : 'No'},"${record.notes}"`
      ).join("\n")

    const encodedUri = encodeURI(csvContent)
    const link = document.createElement("a")
    link.setAttribute("href", encodedUri)
    link.setAttribute("download", `attendance_${selectedDate.value}.csv`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    showMessage('Attendance exported successfully')
  } catch (error) {
    showMessage('Failed to export attendance', 'error')
  }
}

const markChanged = () => {
  hasChanges.value = true
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getAttendanceRateClass = (rate) => {
  if (rate >= 90) return 'text-green-600'
  if (rate >= 80) return 'text-yellow-600'
  return 'text-red-600'
}

const getCalendarDotColor = (rate) => {
  if (rate >= 90) return 'bg-green-500'
  if (rate >= 80) return 'bg-yellow-500'
  return 'bg-red-500'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Initialize component
onMounted(() => {
  // Component is ready
})
</script>