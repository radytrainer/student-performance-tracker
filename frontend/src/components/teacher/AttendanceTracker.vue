<template>
  <div class="space-y-6">
    <!-- Attendance Header -->
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-2xl font-bold text-gray-900">Take Attendance</h3>
        <p class="text-gray-600 mt-1">{{ selectedClass?.class_name || selectedClass?.name }} - {{ formatDate(selectedDate) }}</p>
      </div>
      <div class="flex items-center space-x-4">
        <input
          v-model="selectedDate"
          type="date"
          class="border border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-md"
        />
        <div class="flex space-x-2">
          <button
            @click="markAllPresent"
            class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-200"
          >
            Mark All Present
          </button>
          <button
            @click="markAllAbsent"
            class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-200"
          >
            Mark All Absent
          </button>
        </div>
      </div>
    </div>

    <!-- Attendance Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 p-4 rounded-xl shadow-md">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-blue-700 font-bold text-xs uppercase tracking-wide">Total</p>
            <p class="text-2xl font-bold text-blue-900">{{ attendanceStats.total }}</p>
          </div>
          <Users class="w-6 h-6 text-blue-600" />
        </div>
      </div>
      
      <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 p-4 rounded-xl shadow-md">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-700 font-bold text-xs uppercase tracking-wide">Present</p>
            <p class="text-2xl font-bold text-green-900">{{ attendanceStats.present }}</p>
          </div>
          <CheckCircle class="w-6 h-6 text-green-600" />
        </div>
      </div>
      
      <div class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 p-4 rounded-xl shadow-md">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-red-700 font-bold text-xs uppercase tracking-wide">Absent</p>
            <p class="text-2xl font-bold text-red-900">{{ attendanceStats.absent }}</p>
          </div>
          <XCircle class="w-6 h-6 text-red-600" />
        </div>
      </div>
      
      <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 p-4 rounded-xl shadow-md">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-yellow-700 font-bold text-xs uppercase tracking-wide">Late</p>
            <p class="text-2xl font-bold text-yellow-900">{{ attendanceStats.late }}</p>
          </div>
          <Clock class="w-6 h-6 text-yellow-600" />
        </div>
      </div>
      
      <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 p-4 rounded-xl shadow-md">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-purple-700 font-bold text-xs uppercase tracking-wide">Present %</p>
            <p class="text-2xl font-bold text-purple-900">{{ attendanceStats.presentPercentage }}%</p>
          </div>
          <TrendingUp class="w-6 h-6 text-purple-600" />
        </div>
      </div>
    </div>

    <!-- Students List -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
      <div v-if="loading" class="flex justify-center py-12">
        <Loader class="w-8 h-8 animate-spin text-blue-600" />
      </div>
      
      <div v-else-if="students.length === 0" class="text-center py-12">
        <Users class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <p class="text-gray-500 text-lg">No students found</p>
      </div>
      
      <div v-else class="divide-y divide-gray-200">
        <div
          v-for="student in students"
          :key="student.id || student.user_id"
          class="p-6 hover:bg-gray-50/50 transition-colors duration-200"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                <User class="w-6 h-6 text-white" />
              </div>
              <div>
                <p class="text-lg font-bold text-gray-900">{{ formatStudentName(student) }}</p>
                <p class="text-sm text-gray-500">{{ formatStudentCode(student) }}</p>
              </div>
            </div>
            
            <div class="flex items-center space-x-4">
              <!-- Status Buttons -->
              <div class="flex space-x-2">
                <button
                  v-for="status in attendanceStatuses"
                  :key="status.value"
                  @click="updateAttendanceStatus(student.id || student.user_id, status.value)"
                  :class="[
                    'px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 shadow-md hover:shadow-lg',
                    getAttendanceStatus(student.id || student.user_id) === status.value
                      ? status.activeClass
                      : status.inactiveClass
                  ]"
                >
                  <component :is="status.icon" class="w-4 h-4 mr-1 inline" />
                  {{ status.label }}
                </button>
              </div>
              
              <!-- Notes -->
              <div class="relative">
                <button
                  @click="toggleNotesInput(student.id || student.user_id)"
                  class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                  title="Add notes"
                >
                  <MessageSquare class="w-4 h-4" />
                </button>
                <div
                  v-if="showNotesFor === (student.id || student.user_id)"
                  class="absolute right-0 top-full mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-lg z-10 p-3"
                >
                  <textarea
                    :value="getAttendanceNotes(student.id || student.user_id)"
                    @input="updateAttendanceNotes(student.id || student.user_id, $event.target.value)"
                    placeholder="Add notes..."
                    rows="3"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                  ></textarea>
                  <div class="flex justify-end mt-2">
                    <button
                      @click="showNotesFor = null"
                      class="text-sm text-gray-500 hover:text-gray-700"
                    >
                      Done
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Notes Display -->
          <div
            v-if="getAttendanceNotes(student.id || student.user_id)"
            class="mt-3 ml-16 p-3 bg-gray-50 rounded-lg"
          >
            <p class="text-sm text-gray-600">{{ getAttendanceNotes(student.id || student.user_id) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-center">
      <button
        @click="saveAttendance"
        :disabled="saving || loading"
        class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-8 py-3 rounded-xl text-lg font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <Loader v-if="saving" class="w-5 h-5 mr-2 inline animate-spin" />
        <Save v-else class="w-5 h-5 mr-2 inline" />
        {{ saving ? 'Saving...' : 'Save Attendance' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import {
  Users, User, CheckCircle, XCircle, Clock, TrendingUp,
  Loader, Save, MessageSquare
} from 'lucide-vue-next'
import { useAttendanceTracking } from '@/composables/useAttendanceTracking'

const props = defineProps({
  selectedClass: {
    type: Object,
    required: true
  }
})

const {
  selectedDate,
  attendanceRecords,
  students,
  loading,
  saving,
  attendanceStats,
  updateAttendanceStatus,
  updateAttendanceNotes,
  saveAttendance,
  markAllPresent,
  markAllAbsent,
  initializeAttendanceForClass
} = useAttendanceTracking()

// Local state for UI
const showNotesFor = ref(null)

// Attendance status options
const attendanceStatuses = [
  {
    value: 'present',
    label: 'Present',
    icon: CheckCircle,
    activeClass: 'bg-gradient-to-r from-green-600 to-green-700 text-white',
    inactiveClass: 'bg-gray-100 text-gray-600 hover:bg-green-50 hover:text-green-600'
  },
  {
    value: 'absent',
    label: 'Absent',
    icon: XCircle,
    activeClass: 'bg-gradient-to-r from-red-600 to-red-700 text-white',
    inactiveClass: 'bg-gray-100 text-gray-600 hover:bg-red-50 hover:text-red-600'
  },
  {
    value: 'late',
    label: 'Late',
    icon: Clock,
    activeClass: 'bg-gradient-to-r from-yellow-600 to-yellow-700 text-white',
    inactiveClass: 'bg-gray-100 text-gray-600 hover:bg-yellow-50 hover:text-yellow-600'
  },
  {
    value: 'excused',
    label: 'Excused',
    icon: CheckCircle,
    activeClass: 'bg-gradient-to-r from-purple-600 to-purple-700 text-white',
    inactiveClass: 'bg-gray-100 text-gray-600 hover:bg-purple-50 hover:text-purple-600'
  }
]

// Helper methods
const formatStudentName = (student) => {
  return student.name || `${student.first_name || ''} ${student.last_name || ''}`.trim()
}

const formatStudentCode = (student) => {
  return student.code || student.student_code || 'N/A'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getAttendanceStatus = (studentId) => {
  const record = attendanceRecords.value.find(r => r.student_id === studentId)
  return record?.status || 'present'
}

const getAttendanceNotes = (studentId) => {
  const record = attendanceRecords.value.find(r => r.student_id === studentId)
  return record?.notes || ''
}

const toggleNotesInput = (studentId) => {
  showNotesFor.value = showNotesFor.value === studentId ? null : studentId
}

// Initialize when component is mounted or class changes
import { watch, onMounted } from 'vue'

onMounted(() => {
  if (props.selectedClass) {
    initializeAttendanceForClass(props.selectedClass)
  }
})

watch(() => props.selectedClass, (newClass) => {
  if (newClass) {
    initializeAttendanceForClass(newClass)
  }
}, { immediate: true })
</script>
