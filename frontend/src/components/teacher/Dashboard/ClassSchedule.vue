<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-8 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Today's Schedule</h2>
          <p class="text-gray-500">{{ getCurrentDate() }}</p>
        </div>
        <div class="flex space-x-3">
          <button 
            @click="showFullSchedule = true"
            class="px-4 py-2 bg-blue-100 text-blue-600 rounded-xl hover:bg-blue-200 transition-colors"
          >
            View Full Schedule
          </button>
          <button 
            @click="showAttendanceModal = true"
            class="px-4 py-2 bg-green-100 text-green-600 rounded-xl hover:bg-green-200 transition-colors"
          >
            Take Attendance
          </button>
        </div>
      </div>
    </div>

    <div class="p-8">
      <div class="space-y-4">
        <div
          v-for="class_ in schedule"
          :key="class_.id"
          class="group flex items-center justify-between p-6 bg-gradient-to-r from-gray-50 to-white rounded-2xl border border-gray-100 hover:shadow-lg hover:border-gray-200 transition-all duration-300"
        >
          <div class="flex items-center space-x-6">
            <div class="text-center min-w-[100px]">
              <p class="text-lg font-bold text-gray-900">{{ class_.time }}</p>
              <p class="text-sm text-gray-500">{{ class_.duration }}</p>
            </div>
            <div class="w-px h-12 bg-gray-200"></div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ class_.subject }}</h3>
              <p class="text-gray-600 font-medium">{{ class_.room }} • {{ class_.students }} students</p>
              <p class="text-sm text-gray-500">{{ class_.type }}</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <span :class="getStatusColor(class_.status)" class="px-3 py-1 text-xs font-semibold rounded-full">
              {{ class_.status }}
            </span>
            <div class="relative">
              <button 
                @click="toggleClassMenu(class_.id)"
                class="p-2 text-gray-400 hover:text-blue-500 transition-colors"
              >
                <MoreHorizontalIcon class="w-5 h-5" />
              </button>
              
              <!-- Class Action Menu -->
              <div v-if="activeClassMenu === class_.id" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 z-10">
                <button 
                  @click="startClass(class_)"
                  v-if="class_.status === 'Upcoming'"
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg"
                >
                  Start Class
                </button>
                <button 
                  @click="endClass(class_)"
                  v-if="class_.status === 'In Progress'"
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                  End Class
                </button>
                <button 
                  @click="takeAttendance(class_)"
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                  Take Attendance
                </button>
                <button 
                  @click="viewStudents(class_)"
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                  View Students
                </button>
                <button 
                  @click="sendAnnouncement(class_)"
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-b-lg"
                >
                  Send Announcement
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Attendance Modal -->
    <div v-if="showAttendanceModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click="showAttendanceModal = false">
      <div class="bg-white rounded-2xl w-full max-w-4xl shadow-2xl max-h-[90vh] overflow-y-auto" @click.stop>
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl">
          <div>
            <h3 class="text-xl font-bold text-gray-900">Take Attendance</h3>
            <p class="text-sm text-gray-500 mt-1">Mark student attendance for today's class</p>
          </div>
          <button 
            @click="showAttendanceModal = false"
            class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <XIcon class="w-5 h-5 text-gray-400" />
          </button>
        </div>

        <!-- Class Selection -->
        <div class="p-6 border-b border-gray-100">
          <label class="block text-sm font-medium text-gray-700 mb-2">Select Class</label>
          <select v-model="selectedClassForAttendance" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Choose a class...</option>
            <option v-for="class_ in schedule" :key="class_.id" :value="class_.id">
              {{ class_.subject }} - {{ class_.time }} ({{ class_.students }} students)
            </option>
          </select>
        </div>

        <!-- Student List -->
        <div v-if="selectedClassForAttendance" class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-semibold text-gray-900">Student List</h4>
            <div class="flex space-x-2">
              <button 
                @click="markAllPresent"
                class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-sm hover:bg-green-200 transition-colors"
              >
                All Present
              </button>
              <button 
                @click="markAllAbsent"
                class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200 transition-colors"
              >
                All Absent
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto">
            <div v-for="student in attendanceList" :key="student.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                  {{ student.name.charAt(0) }}
                </div>
                <div>
                  <p class="font-medium text-gray-900 text-sm">{{ student.name }}</p>
                  <p class="text-xs text-gray-500">{{ student.id }}</p>
                </div>
              </div>
              <div class="flex space-x-1">
                <button 
                  @click="markAttendance(student.id, 'present')"
                  :class="student.status === 'present' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                  class="px-3 py-1 text-xs font-medium rounded transition-colors"
                >
                  Present
                </button>
                <button 
                  @click="markAttendance(student.id, 'absent')"
                  :class="student.status === 'absent' ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                  class="px-3 py-1 text-xs font-medium rounded transition-colors"
                >
                  Absent
                </button>
              </div>
            </div>
          </div>

          <!-- Summary -->
          <div class="mt-4 p-4 bg-blue-50 rounded-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-blue-900">Attendance Summary</p>
                <p class="text-xs text-blue-700">Present: {{ getPresentCount() }} | Absent: {{ getAbsentCount() }} | Not Marked: {{ getNotMarkedCount() }}</p>
              </div>
              <div class="text-xl font-bold text-blue-600">
                {{ Math.round((getPresentCount() / attendanceList.length) * 100) }}%
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex space-x-3 p-6 bg-gray-50 rounded-b-2xl">
          <button 
            @click="saveAttendance" 
            :disabled="!selectedClassForAttendance"
            class="flex-1 bg-green-500 text-white py-2.5 rounded-lg font-medium hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Save Attendance
          </button>
          <button 
            @click="showAttendanceModal = false" 
            class="flex-1 bg-white border border-gray-300 text-gray-700 py-2.5 rounded-lg font-medium hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const MoreHorizontalIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"></path></svg>' }
const XIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>' }

const props = defineProps(['schedule'])
const emit = defineEmits(['class-started', 'class-ended', 'attendance-taken'])

// State
const showFullSchedule = ref(false)
const showAttendanceModal = ref(false)
const activeClassMenu = ref(null)
const selectedClassForAttendance = ref('')

const attendanceList = ref([
  { id: 'STU001', name: 'Sarah Johnson', status: null },
  { id: 'STU002', name: 'Michael Chen', status: null },
  { id: 'STU003', name: 'Emma Wilson', status: null },
  { id: 'STU004', name: 'David Rodriguez', status: null },
  { id: 'STU005', name: 'Lisa Park', status: null },
  { id: 'STU006', name: 'James Wilson', status: null },
  { id: 'STU007', name: 'Maria Garcia', status: null },
  { id: 'STU008', name: 'Alex Thompson', status: null }
])

// Methods
const getCurrentDate = () => {
  return new Date().toLocaleDateString('en-US', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

const getStatusColor = (status) => {
  switch (status) {
    case 'Upcoming': return 'bg-blue-100 text-blue-700 border border-blue-200'
    case 'In Progress': return 'bg-green-100 text-green-700 border border-green-200'
    case 'Completed': return 'bg-gray-100 text-gray-700 border border-gray-200'
    default: return 'bg-gray-100 text-gray-700 border border-gray-200'
  }
}

const toggleClassMenu = (classId) => {
  activeClassMenu.value = activeClassMenu.value === classId ? null : classId
}

const startClass = (class_) => {
  class_.status = 'In Progress'
  emit('class-started', class_)
  alert(`Started ${class_.subject} class`)
  activeClassMenu.value = null
}

const endClass = (class_) => {
  class_.status = 'Completed'
  emit('class-ended', class_)
  alert(`Ended ${class_.subject} class`)
  activeClassMenu.value = null
}

const takeAttendance = (class_) => {
  selectedClassForAttendance.value = class_.id
  showAttendanceModal.value = true
  activeClassMenu.value = null
}

const viewStudents = (class_) => {
  alert(`Viewing students for ${class_.subject}`)
  activeClassMenu.value = null
}

const sendAnnouncement = (class_) => {
  const message = prompt(`Send announcement to ${class_.subject} students:`)
  if (message) {
    alert(`Announcement sent: "${message}"`)
  }
  activeClassMenu.value = null
}

const markAttendance = (studentId, status) => {
  const student = attendanceList.value.find(s => s.id === studentId)
  if (student) {
    student.status = status
  }
}

const markAllPresent = () => {
  attendanceList.value.forEach(student => student.status = 'present')
}

const markAllAbsent = () => {
  attendanceList.value.forEach(student => student.status = 'absent')
}

const getPresentCount = () => {
  return attendanceList.value.filter(s => s.status === 'present').length
}

const getAbsentCount = () => {
  return attendanceList.value.filter(s => s.status === 'absent').length
}

const getNotMarkedCount = () => {
  return attendanceList.value.filter(s => s.status === null).length
}

const saveAttendance = () => {
  const presentCount = getPresentCount()
  const absentCount = getAbsentCount()
  
  emit('attendance-taken', {
    classId: selectedClassForAttendance.value,
    present: presentCount,
    absent: absentCount
  })
  
  alert(`Attendance saved: ${presentCount} present, ${absentCount} absent`)
  showAttendanceModal.value = false
  
  // Reset attendance list
  attendanceList.value.forEach(student => student.status = null)
  selectedClassForAttendance.value = ''
}
</script>
