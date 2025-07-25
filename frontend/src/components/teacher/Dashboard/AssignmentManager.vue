<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-8 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Assignment Management</h2>
        <div class="flex space-x-2">
          <select 
            v-model="filterStatus" 
            @change="filterAssignments"
            class="px-3 py-2 border border-gray-200 rounded-xl text-sm"
          >
            <option value="">All Assignments</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="overdue">Overdue</option>
          </select>
          <button 
            @click="showCreateModal = true"
            class="px-4 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition-colors"
          >
            Create Assignment
          </button>
        </div>
      </div>
    </div>

    <div class="p-8">
      <div class="space-y-4">
        <div
          v-for="assignment in filteredAssignments"
          :key="assignment.id"
          class="group flex items-center justify-between p-6 bg-gradient-to-r from-white to-gray-50 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-gray-200 transition-all duration-300"
        >
          <div class="flex items-center space-x-4">
            <div
              :class="assignment.type === 'exam' ? 'bg-gradient-to-br from-red-500 to-pink-500' : 'bg-gradient-to-br from-blue-500 to-indigo-500'"
              class="p-3 rounded-2xl text-white shadow-lg"
            >
              <component :is="assignment.type === 'exam' ? 'ClipboardCheckIcon' : 'DocumentTextIcon'" class="w-6 h-6" />
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ assignment.title }}</h3>
              <p class="text-gray-600 font-medium">{{ assignment.subject }}</p>
              <p class="text-sm text-gray-500">Due: {{ assignment.dueDate }}</p>
            </div>
          </div>
          <div class="text-right space-y-2">
            <div class="flex items-center space-x-2">
              <span class="text-sm text-gray-500">Submitted:</span>
              <span class="text-lg font-bold text-gray-900">{{ assignment.submitted }}/{{ assignment.total }}</span>
            </div>
            <div class="w-32 bg-gray-200 rounded-full h-2">
              <div
                class="bg-gradient-to-r from-blue-500 to-green-500 h-2 rounded-full transition-all duration-300"
                :style="`width: ${(assignment.submitted / assignment.total) * 100}%`"
              ></div>
            </div>
            <div class="flex space-x-2">
              <button 
                @click="gradeAssignment(assignment)"
                class="px-3 py-1 text-xs bg-blue-100 text-blue-600 rounded-full hover:bg-blue-200 transition-colors"
              >
                Grade ({{ assignment.submitted }})
              </button>
              <button 
                @click="editAssignment(assignment)"
                class="px-3 py-1 text-xs bg-gray-100 text-gray-600 rounded-full hover:bg-gray-200 transition-colors"
              >
                Edit
              </button>
              <button 
                @click="viewSubmissions(assignment)"
                class="px-3 py-1 text-xs bg-green-100 text-green-600 rounded-full hover:bg-green-200 transition-colors"
              >
                View All
              </button>
              <button 
                @click="deleteAssignment(assignment)"
                class="px-3 py-1 text-xs bg-red-100 text-red-600 rounded-full hover:bg-red-200 transition-colors"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Assignment Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="showCreateModal = false">
      <div class="bg-white rounded-2xl p-6 w-96 max-w-md" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-4">Create New Assignment</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input v-model="newAssignment.title" type="text" placeholder="Assignment title" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
            <select v-model="newAssignment.subject" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option value="">Select Subject</option>
              <option value="Mathematics">Mathematics</option>
              <option value="Physics">Physics</option>
              <option value="Chemistry">Chemistry</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
            <input v-model="newAssignment.dueDate" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select v-model="newAssignment.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option value="assignment">Assignment</option>
              <option value="quiz">Quiz</option>
              <option value="exam">Exam</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="newAssignment.description" rows="3" placeholder="Assignment description..." class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
          </div>
          <div class="flex space-x-3">
            <button @click="createAssignment" class="flex-1 bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition-colors">
              Create Assignment
            </button>
            <button @click="showCreateModal = false" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition-colors">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Assignment Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="showEditModal = false">
      <div class="bg-white rounded-2xl p-6 w-96 max-w-md" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Assignment</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input v-model="editingAssignment.title" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
            <input v-model="editingAssignment.dueDate" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="editingAssignment.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
          </div>
          <div class="flex space-x-3">
            <button @click="saveAssignmentChanges" class="flex-1 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-colors">
              Save Changes
            </button>
            <button @click="showEditModal = false" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition-colors">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Submissions Modal -->
    <div v-if="showSubmissionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="showSubmissionsModal = false">
      <div class="bg-white rounded-2xl p-6 w-4/5 max-w-4xl max-h-96 overflow-y-auto" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ selectedAssignment?.title }} - Submissions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="submission in submissions" :key="submission.id" class="p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-semibold text-gray-900">{{ submission.studentName }}</h4>
              <span :class="getSubmissionStatusColor(submission.status)" class="px-2 py-1 text-xs rounded-full">
                {{ submission.status }}
              </span>
            </div>
            <p class="text-sm text-gray-600 mb-2">Submitted: {{ submission.submittedDate }}</p>
            <div class="flex space-x-2">
              <button 
                @click="gradeSubmission(submission)"
                class="px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
              >
                Grade
              </button>
              <button 
                @click="viewSubmission(submission)"
                class="px-3 py-1 text-xs bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors"
              >
                View
              </button>
            </div>
          </div>
        </div>
        <button @click="showSubmissionsModal = false" class="w-full mt-4 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-colors">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const ClipboardCheckIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>' }
const DocumentTextIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>' }

const props = defineProps(['assignments'])
const emit = defineEmits(['assignment-created', 'assignment-updated', 'assignment-deleted'])

// State
const filterStatus = ref('')
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showSubmissionsModal = ref(false)
const selectedAssignment = ref(null)
const editingAssignment = ref({})

const newAssignment = ref({
  title: '',
  subject: '',
  dueDate: '',
  type: 'assignment',
  description: ''
})

const submissions = ref([
  { id: 1, studentName: 'Sarah Johnson', status: 'Submitted', submittedDate: '2 days ago', grade: null },
  { id: 2, studentName: 'Michael Chen', status: 'Submitted', submittedDate: '1 day ago', grade: 'A-' },
  { id: 3, studentName: 'Emma Wilson', status: 'Late', submittedDate: '3 hours ago', grade: null },
  { id: 4, studentName: 'David Rodriguez', status: 'Missing', submittedDate: null, grade: null }
])

// Computed
const filteredAssignments = computed(() => {
  if (!filterStatus.value) return props.assignments || []
  
  return (props.assignments || []).filter(assignment => {
    const now = new Date()
    const dueDate = new Date(assignment.dueDate)
    
    switch (filterStatus.value) {
      case 'active':
        return dueDate >= now
      case 'completed':
        return assignment.submitted === assignment.total
      case 'overdue':
        return dueDate < now && assignment.submitted < assignment.total
      default:
        return true
    }
  })
})

// Methods
const filterAssignments = () => {
  // Filtering handled by computed property
}

const createAssignment = () => {
  if (newAssignment.value.title && newAssignment.value.subject && newAssignment.value.dueDate) {
    const assignment = {
      ...newAssignment.value,
      id: Date.now(),
      submitted: 0,
      total: 30 // Default class size
    }
    
    emit('assignment-created', assignment)
    alert(`Assignment "${newAssignment.value.title}" created successfully!`)
    
    // Reset form
    newAssignment.value = {
      title: '',
      subject: '',
      dueDate: '',
      type: 'assignment',
      description: ''
    }
    showCreateModal.value = false
  }
}

const editAssignment = (assignment) => {
  editingAssignment.value = { ...assignment }
  showEditModal.value = true
}

const saveAssignmentChanges = () => {
  emit('assignment-updated', editingAssignment.value)
  alert(`Assignment "${editingAssignment.value.title}" updated successfully!`)
  showEditModal.value = false
}

const deleteAssignment = (assignment) => {
  if (confirm(`Are you sure you want to delete "${assignment.title}"?`)) {
    emit('assignment-deleted', assignment.id)
    alert(`Assignment "${assignment.title}" deleted successfully!`)
  }
}

const gradeAssignment = (assignment) => {
  selectedAssignment.value = assignment
  showSubmissionsModal.value = true
}

const viewSubmissions = (assignment) => {
  selectedAssignment.value = assignment
  showSubmissionsModal.value = true
}

const getSubmissionStatusColor = (status) => {
  switch (status) {
    case 'Submitted': return 'bg-green-100 text-green-700'
    case 'Late': return 'bg-yellow-100 text-yellow-700'
    case 'Missing': return 'bg-red-100 text-red-700'
    default: return 'bg-gray-100 text-gray-700'
  }
}

const gradeSubmission = (submission) => {
  const grade = prompt(`Enter grade for ${submission.studentName}:`)
  if (grade) {
    submission.grade = grade
    alert(`Grade ${grade} assigned to ${submission.studentName}`)
  }
}

const viewSubmission = (submission) => {
  alert(`Viewing submission from ${submission.studentName}:\n\nFile: assignment_${submission.id}.pdf\nSubmitted: ${submission.submittedDate}\nStatus: ${submission.status}`)
}
</script>