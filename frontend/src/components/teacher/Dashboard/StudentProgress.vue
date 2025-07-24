<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-8 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Student Progress Overview</h2>
        <div class="flex space-x-2">
          <select 
            v-model="selectedClass" 
            @change="filterStudents"
            class="px-3 py-2 border border-gray-200 rounded-xl text-sm"
          >
            <option value="">All Classes</option>
            <option value="Mathematics">Mathematics</option>
            <option value="Physics">Physics</option>
            <option value="Chemistry">Chemistry</option>
          </select>
          <select 
            v-model="sortBy" 
            @change="sortStudents"
            class="px-3 py-2 border border-gray-200 rounded-xl text-sm"
          >
            <option value="name">Sort by Name</option>
            <option value="grade">Sort by Grade</option>
            <option value="attendance">Sort by Attendance</option>
          </select>
        </div>
      </div>
    </div>

    <div class="p-8">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div
          v-for="student in filteredStudents"
          :key="student.id"
          @click="showStudentDetails(student)"
          class="group p-6 bg-gradient-to-br from-white to-gray-50 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-gray-200 transition-all duration-300 cursor-pointer"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                {{ student.name.charAt(0) }}
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ student.name }}</h3>
                <p class="text-sm text-gray-500">ID: {{ student.studentId }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span :class="getGradeColor(student.currentGrade)" class="text-xl font-bold">
                {{ student.currentGrade }}
              </span>
              <button 
                @click.stop="sendMessage(student)"
                class="p-1 text-gray-400 hover:text-blue-500 transition-colors"
              >
                <MessageIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
          <div class="space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Attendance</span>
              <div class="flex items-center space-x-2">
                <div class="w-16 bg-gray-200 rounded-full h-2">
                  <div
                    :class="getAttendanceBarColor(student.attendance)"
                    class="h-2 rounded-full transition-all duration-300"
                    :style="`width: ${student.attendance}%`"
                  ></div>
                </div>
                <span :class="getAttendanceColor(student.attendance)">{{ student.attendance }}%</span>
              </div>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Assignments</span>
              <span class="text-gray-900">{{ student.assignmentsCompleted }}/{{ student.totalAssignments }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Last Activity</span>
              <span class="text-gray-500">{{ student.lastActivity }}</span>
            </div>
            <div class="flex space-x-2 mt-3">
              <button 
                @click.stop="gradeStudent(student)"
                class="px-3 py-1 text-xs bg-blue-100 text-blue-600 rounded-full hover:bg-blue-200 transition-colors"
              >
                Grade
              </button>
              <button 
                @click.stop="viewAssignments(student)"
                class="px-3 py-1 text-xs bg-green-100 text-green-600 rounded-full hover:bg-green-200 transition-colors"
              >
                Assignments
              </button>
              <button 
                @click.stop="contactParent(student)"
                class="px-3 py-1 text-xs bg-purple-100 text-purple-600 rounded-full hover:bg-purple-200 transition-colors"
              >
                Contact Parent
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Details Modal -->
    <div v-if="selectedStudent" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click="selectedStudent = null">
      <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl" @click.stop>
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
          <h3 class="text-xl font-bold text-gray-900">{{ selectedStudent.name }}</h3>
          <button @click="selectedStudent = null" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <XIcon class="w-5 h-5 text-gray-400" />
          </button>
        </div>
        
        <div class="p-6 space-y-4">
          <div class="p-4 bg-gray-50 rounded-lg">
            <h4 class="font-semibold text-gray-900 mb-2">Academic Performance</h4>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Current Grade:</span>
                <span :class="getGradeColor(selectedStudent.currentGrade)" class="font-bold">
                  {{ selectedStudent.currentGrade }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Attendance:</span>
                <span :class="getAttendanceColor(selectedStudent.attendance)">
                  {{ selectedStudent.attendance }}%
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Assignments:</span>
                <span class="text-gray-900">{{ selectedStudent.assignmentsCompleted }}/{{ selectedStudent.totalAssignments }}</span>
              </div>
            </div>
          </div>

          <div class="p-4 bg-blue-50 rounded-lg">
            <h4 class="font-semibold text-gray-900 mb-2">Recent Activity</h4>
            <div class="space-y-1">
              <p class="text-sm text-gray-600">• Submitted Math Assignment 5</p>
              <p class="text-sm text-gray-600">• Attended Physics Lab</p>
              <p class="text-sm text-gray-600">• Missed Chemistry Quiz</p>
            </div>
          </div>

          <div class="flex space-x-2">
            <button 
              @click="sendQuickMessage(selectedStudent)"
              class="flex-1 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-colors"
            >
              Send Message
            </button>
            <button 
              @click="scheduleParentMeeting(selectedStudent)"
              class="flex-1 bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition-colors"
            >
              Parent Meeting
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Message Modal -->
    <div v-if="showMessageModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click="showMessageModal = false">
      <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl" @click.stop>
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
          <h3 class="text-xl font-bold text-gray-900">Send Message</h3>
          <button @click="showMessageModal = false" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <XIcon class="w-5 h-5 text-gray-400" />
          </button>
        </div>
        
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">To: {{ messageRecipient?.name }}</label>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
            <input v-model="messageData.subject" type="text" placeholder="Message subject" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
            <textarea v-model="messageData.content" rows="4" placeholder="Type your message..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
          </div>
        </div>

        <div class="flex space-x-3 p-6 bg-gray-50 rounded-b-2xl">
          <button @click="sendMessageToStudent" class="flex-1 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-colors">
            Send Message
          </button>
          <button @click="showMessageModal = false" class="flex-1 bg-white border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-50 transition-colors">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const MessageIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>' }
const XIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>' }

const props = defineProps(['students'])
const emit = defineEmits(['student-graded', 'message-sent', 'parent-contacted'])

// State
const selectedClass = ref('')
const sortBy = ref('name')
const selectedStudent = ref(null)
const showMessageModal = ref(false)
const messageRecipient = ref(null)
const messageData = ref({ subject: '', content: '' })

// Computed
const filteredStudents = computed(() => {
  let filtered = props.students || []
  
  if (selectedClass.value) {
    filtered = filtered.filter(student => student.class === selectedClass.value)
  }
  
  // Sort students
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'grade':
        return a.currentGrade.localeCompare(b.currentGrade)
      case 'attendance':
        return b.attendance - a.attendance
      default:
        return a.name.localeCompare(b.name)
    }
  })
  
  return filtered
})

// Methods
const getGradeColor = (grade) => {
  if (grade.startsWith('A')) return 'text-green-600'
  if (grade.startsWith('B')) return 'text-blue-600'
  if (grade.startsWith('C')) return 'text-yellow-600'
  return 'text-red-600'
}

const getAttendanceColor = (attendance) => {
  if (attendance >= 90) return 'text-green-600'
  if (attendance >= 80) return 'text-yellow-600'
  return 'text-red-600'
}

const getAttendanceBarColor = (attendance) => {
  if (attendance >= 90) return 'bg-green-500'
  if (attendance >= 80) return 'bg-yellow-500'
  return 'bg-red-500'
}

const filterStudents = () => {
  // Filtering is handled by computed property
}

const sortStudents = () => {
  // Sorting is handled by computed property
}

const showStudentDetails = (student) => {
  selectedStudent.value = student
}

const sendMessage = (student) => {
  messageRecipient.value = student
  showMessageModal.value = true
}

const gradeStudent = (student) => {
  const grade = prompt(`Enter grade for ${student.name}:`)
  if (grade) {
    student.currentGrade = grade
    emit('student-graded', { student: student.name, grade })
    alert(`Grade ${grade} assigned to ${student.name}`)
  }
}

const viewAssignments = (student) => {
  alert(`Viewing assignments for ${student.name}:\n• Assignment 1: A-\n• Assignment 2: B+\n• Assignment 3: Pending`)
}

const contactParent = (student) => {
  const message = prompt(`Message to ${student.name}'s parent:`)
  if (message) {
    emit('parent-contacted', { student: student.name, message })
    alert(`Message sent to ${student.name}'s parent`)
  }
}

const sendQuickMessage = (student) => {
  messageRecipient.value = student
  showMessageModal.value = true
  selectedStudent.value = null
}

const scheduleParentMeeting = (student) => {
  const date = prompt(`Schedule parent meeting for ${student.name} (enter date):`)
  if (date) {
    alert(`Parent meeting scheduled for ${student.name} on ${date}`)
    selectedStudent.value = null
  }
}

const sendMessageToStudent = () => {
  if (messageData.value.subject && messageData.value.content) {
    emit('message-sent', {
      recipient: messageRecipient.value.name,
      subject: messageData.value.subject,
      content: messageData.value.content
    })
    alert(`Message sent to ${messageRecipient.value.name}`)
    messageData.value = { subject: '', content: '' }
    showMessageModal.value = false
    messageRecipient.value = null
  }
}
</script>
