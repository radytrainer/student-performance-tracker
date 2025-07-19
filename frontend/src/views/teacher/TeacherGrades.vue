<template>
  <div v-if="hasPermission('teacher.manage_grades')" class="p-6">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Grade Management</h1>
      <p class="text-gray-600 mt-1">Manage student grades and assessments</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-6">
      <!-- Filters and Controls -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
            <select
              v-model="selectedClass"
              @change="loadGrades"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Select a class</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                {{ cls.subject }} - {{ cls.section }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Assessment Type</label>
            <select
              v-model="assessmentFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Assessments</option>
              <option value="exam">Exams</option>
              <option value="quiz">Quizzes</option>
              <option value="assignment">Assignments</option>
              <option value="project">Projects</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Grading Period</label>
            <select
              v-model="gradingPeriod"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="current">Current Quarter</option>
              <option value="q1">Quarter 1</option>
              <option value="q2">Quarter 2</option>
              <option value="q3">Quarter 3</option>
              <option value="q4">Quarter 4</option>
              <option value="semester1">Semester 1</option>
              <option value="semester2">Semester 2</option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="showAddAssessmentModal = true"
              :disabled="!selectedClass"
              class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              <i class="fas fa-plus mr-2"></i>
              Add Assessment
            </button>
          </div>
        </div>
      </div>

      <!-- Assessments List -->
      <div v-if="selectedClass" class="space-y-4">
        <div v-for="assessment in filteredAssessments" :key="assessment.id" 
             class="bg-white rounded-lg shadow-sm border border-gray-200">
          
          <!-- Assessment Header -->
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ assessment.title }}</h3>
                  <div class="flex items-center space-x-4 mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="getAssessmentTypeClass(assessment.type)">
                      {{ capitalizeFirst(assessment.type) }}
                    </span>
                    <span class="text-sm text-gray-500">
                      <i class="fas fa-calendar mr-1"></i>
                      {{ formatDate(assessment.date) }}
                    </span>
                    <span class="text-sm text-gray-500">
                      <i class="fas fa-star mr-1"></i>
                      {{ assessment.maxPoints }} points
                    </span>
                  </div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="editAssessment(assessment)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  Edit
                </button>
                <button
                  @click="exportGrades(assessment)"
                  class="text-green-600 hover:text-green-800 text-sm font-medium"
                >
                  Export
                </button>
                <button
                  @click="deleteAssessment(assessment)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>

          <!-- Grades Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Letter Grade</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="grade in assessment.grades" :key="grade.studentId" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-8 w-8">
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                          <span class="text-blue-600 text-sm font-medium">{{ grade.student.name.charAt(0) }}</span>
                        </div>
                      </div>
                      <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900">{{ grade.student.name }}</div>
                        <div class="text-sm text-gray-500">{{ grade.student.id }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      v-model="grade.points"
                      @change="updateGrade(assessment.id, grade)"
                      type="number"
                      :max="assessment.maxPoints"
                      min="0"
                      step="0.5"
                      class="w-20 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <span class="text-sm text-gray-500 ml-1">/ {{ assessment.maxPoints }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium" :class="getPercentageClass(grade.percentage)">
                      {{ grade.percentage }}%
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="getLetterGradeClass(grade.letterGrade)">
                      {{ grade.letterGrade }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <input
                      v-model="grade.comments"
                      @change="updateGrade(assessment.id, grade)"
                      type="text"
                      placeholder="Add comments..."
                      class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      @click="viewStudentDetails(grade.student)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      View
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Assessment Statistics -->
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 text-center">
              <div>
                <div class="text-lg font-semibold text-gray-900">{{ assessment.stats.average }}%</div>
                <div class="text-sm text-gray-600">Class Average</div>
              </div>
              <div>
                <div class="text-lg font-semibold text-gray-900">{{ assessment.stats.highest }}%</div>
                <div class="text-sm text-gray-600">Highest</div>
              </div>
              <div>
                <div class="text-lg font-semibold text-gray-900">{{ assessment.stats.lowest }}%</div>
                <div class="text-sm text-gray-600">Lowest</div>
              </div>
              <div>
                <div class="text-lg font-semibold text-gray-900">{{ assessment.stats.submitted }}</div>
                <div class="text-sm text-gray-600">Submitted</div>
              </div>
              <div>
                <div class="text-lg font-semibold text-gray-900">{{ assessment.stats.pending }}</div>
                <div class="text-sm text-gray-600">Pending</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredAssessments.length === 0" class="text-center py-12">
          <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No assessments found</h3>
          <p class="text-gray-500">Create your first assessment to start grading students.</p>
        </div>
      </div>

      <!-- No Class Selected -->
      <div v-else class="text-center py-12">
        <i class="fas fa-chalkboard text-gray-400 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Select a class to view grades</h3>
        <p class="text-gray-500">Choose a class from the dropdown above to manage grades.</p>
      </div>
    </div>

    <!-- Add Assessment Modal -->
    <div v-if="showAddAssessmentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium mb-4">Add New Assessment</h3>
        <p class="text-gray-600 mb-4">Assessment creation form will be implemented here.</p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showAddAssessmentModal = false"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="showAddAssessmentModal = false"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Create Assessment
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
          <p class="text-sm text-red-700 mt-1">You don't have permission to manage grades.</p>
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
const assessments = ref([])
const selectedClass = ref('')
const assessmentFilter = ref('')
const gradingPeriod = ref('current')
const showAddAssessmentModal = ref(false)

// Mock data
const mockClasses = [
  { id: 1, subject: 'Mathematics', section: 'Section A', grade: '11' },
  { id: 2, subject: 'Physics', section: 'Section B', grade: '12' },
  { id: 3, subject: 'Chemistry', section: 'Section A', grade: '12' }
]

const mockAssessments = [
  {
    id: 1,
    classId: 1,
    title: 'Chapter 5 Quiz',
    type: 'quiz',
    date: new Date('2024-01-15'),
    maxPoints: 20,
    stats: { average: 85, highest: 98, lowest: 65, submitted: 26, pending: 2 },
    grades: [
      {
        studentId: 1,
        student: { id: 'ST001', name: 'Alice Johnson' },
        points: 18,
        percentage: 90,
        letterGrade: 'A',
        comments: 'Excellent work'
      },
      {
        studentId: 2,
        student: { id: 'ST002', name: 'Bob Smith' },
        points: 16,
        percentage: 80,
        letterGrade: 'B',
        comments: ''
      }
    ]
  }
]

// Computed
const filteredAssessments = computed(() => {
  let filtered = assessments.value

  if (assessmentFilter.value) {
    filtered = filtered.filter(assessment => assessment.type === assessmentFilter.value)
  }

  return filtered
})

// Methods
const loadGrades = async () => {
  if (!selectedClass.value) return
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 500))
    assessments.value = mockAssessments.filter(a => a.classId == selectedClass.value)
  } catch (error) {
    console.error('Error loading grades:', error)
  }
}

const updateGrade = (assessmentId, grade) => {
  // Calculate percentage and letter grade
  const assessment = assessments.value.find(a => a.id === assessmentId)
  if (assessment) {
    grade.percentage = Math.round((grade.points / assessment.maxPoints) * 100)
    grade.letterGrade = getLetterGradeFromPercentage(grade.percentage)
  }
  
  // TODO: Save to API
  console.log('Update grade:', { assessmentId, grade })
}

const getLetterGradeFromPercentage = (percentage) => {
  if (percentage >= 90) return 'A'
  if (percentage >= 80) return 'B'
  if (percentage >= 70) return 'C'
  if (percentage >= 60) return 'D'
  return 'F'
}

const getAssessmentTypeClass = (type) => {
  const classes = {
    exam: 'bg-red-100 text-red-800',
    quiz: 'bg-blue-100 text-blue-800',
    assignment: 'bg-green-100 text-green-800',
    project: 'bg-purple-100 text-purple-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getPercentageClass = (percentage) => {
  if (percentage >= 90) return 'text-green-600'
  if (percentage >= 80) return 'text-blue-600'
  if (percentage >= 70) return 'text-yellow-600'
  if (percentage >= 60) return 'text-orange-600'
  return 'text-red-600'
}

const getLetterGradeClass = (grade) => {
  const classes = {
    A: 'bg-green-100 text-green-800',
    B: 'bg-blue-100 text-blue-800',
    C: 'bg-yellow-100 text-yellow-800',
    D: 'bg-orange-100 text-orange-800',
    F: 'bg-red-100 text-red-800'
  }
  return classes[grade] || 'bg-gray-100 text-gray-800'
}

const capitalizeFirst = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const editAssessment = (assessment) => {
  console.log('Edit assessment:', assessment)
}

const exportGrades = (assessment) => {
  console.log('Export grades for:', assessment)
}

const deleteAssessment = (assessment) => {
  if (confirm('Are you sure you want to delete this assessment?')) {
    console.log('Delete assessment:', assessment)
  }
}

const viewStudentDetails = (student) => {
  console.log('View student details:', student)
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
