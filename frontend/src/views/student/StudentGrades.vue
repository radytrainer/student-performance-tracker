<template>
  <div v-if="hasPermission('student.view_own_grades')" class="p-6">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">My Grades</h1>
      <p class="text-gray-600 mt-1">View your academic performance and grades</p>
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
          <h3 class="text-sm font-medium text-red-800">Error loading grades</h3>
          <p class="text-sm text-red-700 mt-1">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-6">
      <!-- Overall Performance -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-blue-600">{{ overallGPA }}</div>
              <div class="text-sm text-gray-600">Overall GPA</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-graduation-cap text-green-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-green-600">{{ currentSemesterGPA }}</div>
              <div class="text-sm text-gray-600">Current Semester</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-trophy text-purple-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-purple-600">{{ classRank }}</div>
              <div class="text-sm text-gray-600">Class Rank</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-star text-yellow-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-yellow-600">{{ totalCredits }}</div>
              <div class="text-sm text-gray-600">Credits Earned</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Grading Period</label>
            <select
              v-model="selectedPeriod"
              @change="filterGrades"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="current">Current Quarter</option>
              <option value="q1">Quarter 1</option>
              <option value="q2">Quarter 2</option>
              <option value="q3">Quarter 3</option>
              <option value="q4">Quarter 4</option>
              <option value="semester1">Semester 1</option>
              <option value="semester2">Semester 2</option>
              <option value="year">Full Year</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
            <select
              v-model="selectedSubject"
              @change="filterGrades"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Subjects</option>
              <option v-for="subject in subjects" :key="subject" :value="subject">{{ subject }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">View Type</label>
            <select
              v-model="viewType"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="summary">Grade Summary</option>
              <option value="detailed">Detailed Breakdown</option>
              <option value="trends">Performance Trends</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Grades by Subject -->
      <div v-if="viewType === 'summary'" class="space-y-4">
        <div v-for="subject in filteredSubjects" :key="subject.id" 
             class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                     :style="{ backgroundColor: subject.color + '20' }">
                  <i :class="subject.icon" :style="{ color: subject.color }"></i>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ subject.name }}</h3>
                  <p class="text-sm text-gray-500">{{ subject.teacher }} • {{ subject.credits }} Credits</p>
                </div>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold" :class="getGradeClass(subject.currentGrade)">
                  {{ subject.currentGrade }}
                </div>
                <div class="text-sm text-gray-500">{{ subject.percentage }}%</div>
              </div>
            </div>
          </div>

          <div class="p-6">
            <!-- Grade Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
              <div v-for="category in subject.gradeBreakdown" :key="category.name" class="text-center">
                <div class="text-lg font-semibold" :class="getGradeClass(category.grade)">
                  {{ category.grade }}
                </div>
                <div class="text-sm text-gray-600">{{ category.name }}</div>
                <div class="text-xs text-gray-500">{{ category.weight }}% weight</div>
              </div>
            </div>

            <!-- Recent Assignments -->
            <div>
              <h4 class="text-sm font-medium text-gray-700 mb-3">Recent Assignments</h4>
              <div class="space-y-2">
                <div v-for="assignment in subject.recentAssignments" :key="assignment.id" 
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                          :class="getAssignmentTypeClass(assignment.type)">
                      {{ assignment.type }}
                    </span>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ assignment.title }}</div>
                      <div class="text-xs text-gray-500">{{ formatDate(assignment.date) }}</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-semibold" :class="getGradeClass(assignment.grade)">
                      {{ assignment.grade }}
                    </div>
                    <div class="text-xs text-gray-500">{{ assignment.points }}/{{ assignment.maxPoints }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Subject Actions -->
            <div class="mt-4 flex justify-between">
              <button
                @click="viewSubjectDetails(subject)"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
              >
                View All Assignments
              </button>
              <button
                @click="viewProgressChart(subject)"
                class="text-purple-600 hover:text-purple-800 text-sm font-medium"
              >
                View Progress Chart
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed View -->
      <div v-else-if="viewType === 'detailed'" class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Detailed Grade Breakdown</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignment</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="assignment in allAssignments" :key="assignment.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ assignment.title }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ assignment.subject }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getAssignmentTypeClass(assignment.type)">
                    {{ assignment.type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(assignment.date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-semibold" :class="getGradeClass(assignment.grade)">
                    {{ assignment.grade }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ assignment.points }}/{{ assignment.maxPoints }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ assignment.comments || 'No comments' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Performance Trends -->
      <div v-else-if="viewType === 'trends'" class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Performance Trends</h2>
        </div>
        <div class="p-6">
          <div class="text-center py-12">
            <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Performance Charts</h3>
            <p class="text-gray-500">Interactive performance charts will be implemented here.</p>
          </div>
        </div>
      </div>

      <!-- Grade History -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Grade History</h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-for="semester in gradeHistory" :key="semester.name" class="border border-gray-200 rounded-lg p-4">
              <h3 class="font-semibold text-gray-900 mb-3">{{ semester.name }}</h3>
              <div class="space-y-2">
                <div v-for="grade in semester.grades" :key="grade.subject" 
                     class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">{{ grade.subject }}</span>
                  <span class="text-sm font-medium" :class="getGradeClass(grade.grade)">
                    {{ grade.grade }}
                  </span>
                </div>
              </div>
              <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-700">Semester GPA</span>
                  <span class="text-sm font-bold text-blue-600">{{ semester.gpa }}</span>
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
          <i class="fas fa-exclamation-triangle text-red-400"></i>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Access Denied</h3>
          <p class="text-sm text-red-700 mt-1">You don't have permission to view grades.</p>
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
const selectedPeriod = ref('current')
const selectedSubject = ref('')
const viewType = ref('summary')

// Mock data
const overallGPA = ref('3.7')
const currentSemesterGPA = ref('3.8')
const classRank = ref('15/120')
const totalCredits = ref('24')

const subjectGrades = ref([
  {
    id: 1,
    name: 'Advanced Mathematics',
    teacher: 'Dr. Smith',
    credits: 4,
    currentGrade: 'A-',
    percentage: 88,
    color: '#3B82F6',
    icon: 'fas fa-square-root-alt',
    gradeBreakdown: [
      { name: 'Tests', grade: 'B+', weight: 40 },
      { name: 'Quizzes', grade: 'A', weight: 30 },
      { name: 'Homework', grade: 'A', weight: 20 },
      { name: 'Projects', grade: 'A-', weight: 10 }
    ],
    recentAssignments: [
      { id: 1, title: 'Chapter 5 Quiz', type: 'Quiz', date: '2024-01-15', grade: 'A', points: 18, maxPoints: 20 },
      { id: 2, title: 'Algebra Test', type: 'Test', date: '2024-01-12', grade: 'B+', points: 42, maxPoints: 50 }
    ]
  },
  {
    id: 2,
    name: 'Physics',
    teacher: 'Prof. Johnson',
    credits: 4,
    currentGrade: 'B+',
    percentage: 85,
    color: '#10B981',
    icon: 'fas fa-atom',
    gradeBreakdown: [
      { name: 'Tests', grade: 'B', weight: 50 },
      { name: 'Labs', grade: 'A', weight: 30 },
      { name: 'Homework', grade: 'A-', weight: 20 }
    ],
    recentAssignments: [
      { id: 3, title: 'Lab Report 3', type: 'Lab', date: '2024-01-14', grade: 'A', points: 95, maxPoints: 100 },
      { id: 4, title: 'Motion Test', type: 'Test', date: '2024-01-10', grade: 'B', points: 80, maxPoints: 100 }
    ]
  }
])

const gradeHistory = ref([
  {
    name: 'Semester 1',
    gpa: '3.6',
    grades: [
      { subject: 'Mathematics', grade: 'B+' },
      { subject: 'Physics', grade: 'A-' },
      { subject: 'Chemistry', grade: 'B' },
      { subject: 'English', grade: 'A' }
    ]
  },
  {
    name: 'Semester 2',
    gpa: '3.8',
    grades: [
      { subject: 'Mathematics', grade: 'A-' },
      { subject: 'Physics', grade: 'B+' },
      { subject: 'Chemistry', grade: 'B+' },
      { subject: 'English', grade: 'A' }
    ]
  }
])

// Computed
const subjects = computed(() => {
  return subjectGrades.value.map(s => s.name)
})

const filteredSubjects = computed(() => {
  let filtered = subjectGrades.value
  
  if (selectedSubject.value) {
    filtered = filtered.filter(s => s.name === selectedSubject.value)
  }
  
  return filtered
})

const allAssignments = computed(() => {
  const assignments = []
  
  subjectGrades.value.forEach(subject => {
    subject.recentAssignments.forEach(assignment => {
      assignments.push({
        ...assignment,
        subject: subject.name
      })
    })
  })
  
  return assignments.sort((a, b) => new Date(b.date) - new Date(a.date))
})

// Methods
const getGradeClass = (grade) => {
  if (grade.startsWith('A')) return 'text-green-600'
  if (grade.startsWith('B')) return 'text-blue-600'
  if (grade.startsWith('C')) return 'text-yellow-600'
  if (grade.startsWith('D')) return 'text-orange-600'
  return 'text-red-600'
}

const getAssignmentTypeClass = (type) => {
  const classes = {
    Test: 'bg-red-100 text-red-800',
    Quiz: 'bg-blue-100 text-blue-800',
    Homework: 'bg-green-100 text-green-800',
    Project: 'bg-purple-100 text-purple-800',
    Lab: 'bg-yellow-100 text-yellow-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const filterGrades = () => {
  // TODO: Implement filtering logic
  console.log('Filtering grades:', { selectedPeriod: selectedPeriod.value, selectedSubject: selectedSubject.value })
}

const viewSubjectDetails = (subject) => {
  console.log('View subject details:', subject)
}

const viewProgressChart = (subject) => {
  console.log('View progress chart for:', subject)
}

const loadGrades = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // Data is already loaded as mock data
  } catch (err) {
    error.value = err.message || 'Failed to load grades'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadGrades()
})
</script>
