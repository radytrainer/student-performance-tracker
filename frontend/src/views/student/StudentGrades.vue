<template>
  <div class="max-w-7xl mx-auto p-2">
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">My Grades</h1>
          <p class="text-gray-600 mt-1">View your grade records</p>
        </div>
        <!-- Real-time Connection Status -->
        <div class="flex items-center space-x-2">
          <div class="flex items-center">
            <div 
              :class="[
                'w-3 h-3 rounded-full mr-2',
                isConnected ? 'bg-green-500 animate-pulse' : 'bg-red-500'
              ]"
            ></div>
            <span class="text-sm font-medium" :class="isConnected ? 'text-green-700' : 'text-red-700'">
              {{ isConnected ? 'Live Updates' : 'Offline' }}
            </span>
          </div>
          <div v-if="gradeUpdates.length > 0" class="text-xs text-gray-500">
            Last update: {{ new Date(gradeUpdates[0].timestamp).toLocaleTimeString() }}
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
          </path>
        </svg>
        <p class="text-gray-600">Loading your grades...</p>
      </div>
    </div>

    <!-- Real-time Update Notification -->
    <div v-if="lastUpdate" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 animate-fade-in">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="text-blue-800 font-medium">
            New grade {{ lastUpdate.type === 'grade_created' ? 'added' : lastUpdate.type === 'grade_updated' ? 'updated' : 'removed' }}!
          </span>
          <span class="text-blue-600 ml-2">
            {{ lastUpdate.metadata?.subject }} - {{ lastUpdate.metadata?.assessment_type }}
          </span>
        </div>
        <button @click="lastUpdate = null" class="text-blue-600 hover:text-blue-800">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
      <div class="flex items-center mb-4">
        <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-lg font-semibold text-red-800">Error Loading Grades</h3>
      </div>
      <p class="text-red-700 mb-4">{{ error }}</p>
      <button @click="fetchStudentData"
        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
        Try Again
      </button>
    </div>

    <!-- Main Content -->
    <div v-else>
      <!-- ✅ Wrap Exportable Content -->
      <div id="grade-export-content">
        <div class="bg-white shadow-md rounded-lg p-4 mb-4 border">
          <p><strong>Name:</strong> {{ student.name }}</p>
          <p><strong>Class:</strong> {{ student.class }}</p>
          <p><strong>Term:</strong> {{ currentTermName }}</p>
        </div>

        <h3 class="text-2xl font-bold mb-4 text-center">Performance Summary</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

          <!-- GPA Card -->
          <div class="bg-white rounded-xl shadow-lg p-5 border border-blue-0">
            <div class="flex justify-between items-start mb-4">
              <!-- Icon -->
              <div class="bg-blue-100 text-blue-600 p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <!-- Percentage -->
              <span class="text-green-500 font-semibold text-sm">+2.5%</span>
            </div>
            <p class="text-gray-500 font-semibold text-sm uppercase">GPA</p>
            <p class="text-3xl font-bold text-blue-800">{{ gpa.toFixed(2) }}</p>
          </div>

          <!-- Best Subject Card -->
          <div class="bg-white rounded-xl shadow-lg p-5 border border-green-0">
            <div class="flex justify-between items-start mb-4">
              <div class="bg-green-100 text-green-600 p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
              </div>
              <span class="text-green-500 font-semibold text-sm">{{ getBestSubjectTrend() }}</span>
            </div>
            <p class="text-gray-500 font-semibold text-sm uppercase">{{ bestSubject.name || gradeSummary?.best_subject || 'N/A' }}</p>
            <p class="text-2xl font-bold text-green-700">{{ Math.round(bestSubject.score || 0) }}%</p>
          </div>

          <!-- Weakest Subject Card -->
          <div class="bg-white rounded-xl shadow-lg p-5 border border-red-0">
            <div class="flex justify-between items-start mb-4">
              <div class="bg-red-100 text-red-600 p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                </svg>
              </div>
              <span class="text-red-500 font-semibold text-sm">{{ getWeakestSubjectTrend() }}</span>
            </div>
            <p class="text-gray-500 font-semibold text-sm uppercase">{{ weakestSubject.name || gradeSummary?.weakest_subject || 'N/A' }}</p>
            <p class="text-2xl font-bold text-red-700">{{ Math.round(weakestSubject.score || 0) }}%</p>
          </div>

          <!-- Overall Performance Card -->
          <div class="bg-white rounded-xl shadow-lg p-5 border border-purple-0">
            <div class="flex justify-between items-start mb-4">
              <div class="bg-purple-100 text-purple-600 p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <span class="text-purple-500 font-semibold text-sm">+3.0%</span>
            </div>
            <p class="text-gray-500 font-semibold text-sm uppercase">{{ overallPerformance.level }}</p>
            <p class="text-2xl font-bold text-purple-700">{{ overallPerformance.score }}%</p>
          </div>

        </div>


        <!-- Subject Performance Breakdown -->
        <div class="bg-white rounded-xl shadow-md p-5 mb-6 border">
          <h4 class="text-lg font-semibold text-gray-800 mb-4">Subject Performance Breakdown</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="(subject, index) in subjectPerformance" :key="index"
              class="border rounded-lg p-3 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-center mb-2">
                <span class="font-medium text-gray-700">{{ subject.name }}</span>
                <span class="text-sm font-semibold px-2 py-1 rounded-full" :class="gradeColor(subject.grade)">{{
                  subject.grade }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1">
                <div class="h-2.5 rounded-full" :class="getScoreColorClass(subject.score)"
                  :style="{ width: subject.score + '%' }"></div>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>{{ subject.score.toFixed(1) }}%</span>
                <span>{{ subject.count }} assessments</span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
          <div class="flex-1">
            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1 text-blue-500" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              Filter by Subject
            </label>
            <select id="subject" v-model="selectedSubject" class="w-full border rounded-lg p-2">
              <option value="">All Subjects</option>
              <option v-for="subject in subjectList" :key="subject" :value="subject">{{ subject }}</option>
            </select>
          </div>

          <div class="flex-1">
            <label for="term" class="block text-sm font-medium text-gray-700 mb-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1 text-blue-500" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Filter by Term
            </label>
            <select id="term" v-model="selectedTermId" class="w-full border rounded-lg p-2">
              <option value="">All Terms</option>
              <option v-for="term in termList" :key="term.id" :value="term.id">{{ term.name }}</option>
            </select>
          </div>

          <div class="md:self-end flex space-x-3">
            <button @click="fetchStudentData(true)"
              :disabled="loading"
              class="flex items-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200">
              <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              {{ loading ? 'Refreshing...' : 'Refresh' }}
            </button>
            <button v-if="isDev" @click="testRealTimeUpdate"
              class="flex items-center bg-purple-100 hover:bg-purple-200 text-purple-700 px-4 py-2 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              Test Update
            </button>
            <button @click="exportToPDF"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export as PDF
            </button>
          </div>
        </div>

        <div v-if="filteredGrades.length === 0" class="text-center text-red-500 mt-6">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          No grades found for selected filters.
        </div>

        <div v-else class="overflow-x-auto rounded-lg shadow">
          <table class="min-w-full bg-white border">
            <thead>
              <tr class="bg-gray-100 text-left text-sm font-semibold text-gray-700">
                <th class="px-4 py-2">Subject</th>
                <th class="px-4 py-2">Assessment</th>
                <th class="px-4 py-2">Score</th>
                <th class="px-4 py-2">Weightage (%)</th>
                <th class="px-4 py-2">Grade</th>
                <th class="px-4 py-2">Remarks</th>
                <th class="px-4 py-2">Recorded By</th>
                <th class="px-4 py-2">Recorded At</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="grade in filteredGrades" :key="grade.id" class="border-t hover:bg-gray-50 text-sm">
                <td class="px-4 py-2">{{ grade.subject }}</td>
                <td class="px-4 py-2">{{ grade.assessment_type }}</td>
                <td class="px-4 py-2">
                  {{ grade.score_obtained }} / {{ grade.max_score }}
                  <div class="w-full bg-gray-200 h-2 mt-1 rounded">
                    <div class="h-2 rounded" :class="getScoreColorClass((grade.score_obtained / grade.max_score) * 100)"
                      :style="{ width: ((grade.score_obtained / grade.max_score) * 100) + '%' }" />
                  </div>
                </td>
                <td class="px-4 py-2">{{ grade.weightage }}%</td>
                <td class="px-4 py-2">
                  <span class="text-xs font-medium px-2 py-1 rounded-full" :class="gradeColor(grade.grade_letter)">
                    {{ grade.grade_letter }}
                  </span>
                </td>
                <td class="px-4 py-2 italic text-gray-600">{{ grade.remarks || '—' }}</td>
                <td class="px-4 py-2">{{ grade.recorded_by || 'Unknown' }}</td>
                <td class="px-4 py-2 text-xs text-gray-500">{{ formatDate(grade.recorded_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div> <!-- End Main Content -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from "@/stores/auth"
import { useWebSocket, useGradeUpdates, useNotifications } from '@/composables/useWebSocket'
import gradesApi from '@/api/grades'
import apiClient from '@/api/axiosConfig'

const authStore = useAuthStore()
const { connect, isConnected } = useWebSocket()

// Loading and error states
const loading = ref(true)
const error = ref(null)

// Student info - get from auth store
const student = ref({
  name: '',
  class: '',
  student_id: '',
  class_id: '',
  current_class: null
})

// Term list and filters
const termList = ref([])
const selectedTermId = ref('')
const selectedSubject = ref('')

// Grade list - loaded from API
const grades = ref([])
const gradeSummary = ref(null)
const studentGPA = ref(0)

// Real-time updates
const studentId = computed(() => student.value.student_id || authStore.user?.id)
const { gradeUpdates, lastUpdate } = useGradeUpdates(studentId.value)
const { notifications } = useNotifications(authStore.user?.id)

// Environment check for development features
const isDev = import.meta.env.DEV

// Mock data for testing
const mockGradesData = {
  grades: [
    {
      id: 1, subject: 'Math', term_id: 2, assessment_type: 'Midterm',
      max_score: 100, score_obtained: 78, score: 78, total: 100, weightage: 30,
      grade_letter: 'B', letter_grade: 'B', grade: 'B',
      remarks: 'Good effort', recorded_by: 'Teacher A', recorded_at: '2025-07-10',
      date: '2025-07-10', assessment: 'Midterm'
    },
    {
      id: 2, subject: 'Math', term_id: 2, assessment_type: 'Quiz',
      max_score: 20, score_obtained: 18, score: 18, total: 20, weightage: 10,
      grade_letter: 'A', letter_grade: 'A', grade: 'A',
      remarks: 'Quick learner', recorded_by: 'Teacher A', recorded_at: '2025-07-15',
      date: '2025-07-15', assessment: 'Quiz'
    },
    {
      id: 3, subject: 'Science', term_id: 2, assessment_type: 'Project',
      max_score: 50, score_obtained: 40, score: 40, total: 50, weightage: 40,
      grade_letter: 'B', letter_grade: 'B', grade: 'B',
      remarks: '', recorded_by: 'Teacher B', recorded_at: '2025-07-12',
      date: '2025-07-12', assessment: 'Project'
    },
    {
      id: 4, subject: 'English', term_id: 2, assessment_type: 'Final',
      max_score: 100, score_obtained: 85, score: 85, total: 100, weightage: 20,
      grade_letter: 'A', letter_grade: 'A', grade: 'A',
      remarks: 'Great improvement', recorded_by: 'Teacher C', recorded_at: '2025-07-20',
      date: '2025-07-20', assessment: 'Final'
    },
    {
      id: 5, subject: 'Science', term_id: 2, assessment_type: 'Lab Test',
      max_score: 30, score_obtained: 25, score: 25, total: 30, weightage: 15,
      grade_letter: 'B', letter_grade: 'B', grade: 'B',
      remarks: 'Good practical skills', recorded_by: 'Teacher B', recorded_at: '2025-07-18',
      date: '2025-07-18', assessment: 'Lab Test'
    },
    {
      id: 6, subject: 'History', term_id: 2, assessment_type: 'Essay',
      max_score: 50, score_obtained: 32, score: 32, total: 50, weightage: 25,
      grade_letter: 'D', letter_grade: 'D', grade: 'D',
      remarks: 'Needs more detail', recorded_by: 'Teacher D', recorded_at: '2025-07-22',
      date: '2025-07-22', assessment: 'Essay'
    }
  ],
  summary: {
    best_subject: 'English',
    weakest_subject: 'Science',
    total_assessments: 6,
    average_score: 82.6
  },
  gpa: 3.4
};

// Computed values
const currentTermName = computed(() => {
  if (!selectedTermId.value) return 'All Terms'
  const term = termList.value.find(t => t.id === Number(selectedTermId.value))
  return term ? term.name : 'All Terms'
})

const subjectList = computed(() => {
  if (!grades.value || grades.value.length === 0) return []
  
  const filtered = selectedTermId.value
    ? grades.value.filter(g => g.term_id === Number(selectedTermId.value))
    : grades.value
  return [...new Set(filtered.map(g => g.subject).filter(Boolean))]
})

const filteredGrades = computed(() => {
  if (!grades.value || grades.value.length === 0) return []
  
  let result = [...grades.value]
  
  if (selectedTermId.value) {
    result = result.filter(g => g.term_id === Number(selectedTermId.value))
  }
  
  if (selectedSubject.value) {
    result = result.filter(g => g.subject === selectedSubject.value)
  }
  
  return result
})

// Calculate subject performance data
const subjectPerformance = computed(() => {
  const subjects = {}

  // Group grades by subject
  filteredGrades.value.forEach(grade => {
    if (!subjects[grade.subject]) {
      subjects[grade.subject] = {
        name: grade.subject,
        totalScore: 0,
        maxScore: 0,
        count: 0,
        grades: []
      }
    }

    const percentage = (grade.score_obtained / grade.max_score) * 100
    subjects[grade.subject].totalScore += percentage
    subjects[grade.subject].maxScore += 100
    subjects[grade.subject].count++
    subjects[grade.subject].grades.push(grade.grade_letter)
  })

  // Calculate average score and grade for each subject
  return Object.values(subjects).map(subject => {
    const avgScore = subject.totalScore / subject.count
    const gradeLetters = subject.grades

    // Calculate average grade (most frequent grade)
    const gradeCount = {}
    gradeLetters.forEach(grade => {
      gradeCount[grade] = (gradeCount[grade] || 0) + 1
    })

    let mostFrequentGrade = ''
    let maxCount = 0
    for (const grade in gradeCount) {
      if (gradeCount[grade] > maxCount) {
        mostFrequentGrade = grade
        maxCount = gradeCount[grade]
      }
    }

    return {
      name: subject.name,
      score: avgScore,
      grade: mostFrequentGrade,
      count: subject.count
    }
  }).sort((a, b) => b.score - a.score)
})

// Enhanced GPA calculation
const gpa = computed(() => {
  if (!filteredGrades.value.length) return 0

  // Calculate weighted GPA based on grade distribution
  const gradePoints = {
    'A': 4.0, 'B': 3.0, 'C': 2.0, 'D': 1.0, 'F': 0.0
  }

  let totalWeightedPoints = 0
  let totalWeight = 0

  filteredGrades.value.forEach(grade => {
    const weight = grade.weightage || 1
    const points = gradePoints[grade.grade_letter] || 0

    totalWeightedPoints += points * weight
    totalWeight += weight
  })

  return totalWeightedPoints / totalWeight
})

// Enhanced best subject calculation
const bestSubject = computed(() => {
  if (subjectPerformance.value.length === 0) {
    return { name: 'N/A', score: 0, grade: '' }
  }

  const best = subjectPerformance.value[0]
  return {
    name: best.name || gradeSummary.value?.best_subject || 'N/A',
    score: best.score || 0,
    grade: best.grade || ''
  }
})

// Enhanced weakest subject calculation
const weakestSubject = computed(() => {
  if (subjectPerformance.value.length === 0) {
    return { name: 'N/A', score: 0, grade: '' }
  }

  const weakest = subjectPerformance.value[subjectPerformance.value.length - 1]
  return {
    name: weakest.name || gradeSummary.value?.weakest_subject || 'N/A',
    score: weakest.score || 0,
    grade: weakest.grade || ''
  }
})

// Overall performance evaluation
const overallPerformance = computed(() => {
  const avgScore = subjectPerformance.value.reduce((sum, subj) => sum + subj.score, 0) /
    (subjectPerformance.value.length || 1)

  let level = ''
  if (avgScore >= 90) level = 'Excellent'
  else if (avgScore >= 80) level = 'Very Good'
  else if (avgScore >= 70) level = 'Good'
  else if (avgScore >= 60) level = 'Average'
  else level = 'Needs Improvement'

  return {
    score: avgScore.toFixed(1),
    level: level
  }
})

// Helper functions
const gradeColor = (letter) => {
  return {
    A: 'bg-green-100 text-green-800',
    B: 'bg-blue-100 text-blue-800',
    C: 'bg-yellow-100 text-yellow-800',
    D: 'bg-orange-100 text-orange-800',
    F: 'bg-red-100 text-red-800',
  }[letter] || 'bg-gray-100 text-gray-700'
}

const getScoreColorClass = (score) => {
  if (score >= 90) return 'bg-green-500'
  if (score >= 80) return 'bg-blue-500'
  if (score >= 70) return 'bg-yellow-500'
  if (score >= 60) return 'bg-orange-500'
  return 'bg-red-500'
}

const formatDate = (str) => new Date(str).toLocaleDateString()

// Calculate trend for best subject
const getBestSubjectTrend = () => {
  if (!bestSubject.value || bestSubject.value.score === 0) return '+0.0%'
  
  // Simple trend calculation - could be enhanced with historical data
  const score = bestSubject.value.score
  if (score >= 90) return '+2.5%'
  if (score >= 80) return '+1.8%'
  if (score >= 70) return '+1.0%'
  return '+0.5%'
}

// Calculate trend for weakest subject
const getWeakestSubjectTrend = () => {
  if (!weakestSubject.value || weakestSubject.value.name === 'N/A') return '±0.0%'
  
  // Simple trend calculation
  const score = weakestSubject.value.score
  if (score < 60) return '-1.2%'
  if (score < 70) return '-0.8%'
  if (score < 80) return '-0.5%'
  return '+0.2%'
}

const exportToPDF = () => {
  // Implementation for PDF export would go here
  console.log('Export to PDF functionality')
  alert('PDF export would be implemented here with a library like html2pdf.js')
}

// API Functions
const fetchStudentData = async (forceRefresh = false) => {
  try {
    loading.value = true
    error.value = null

    // Get student info from auth store
    const user = authStore.user
    if (!user) {
      throw new Error('User not authenticated')
    }

    console.log('📊 Fetching data for user:', user)

    // Fetch student profile information
    const studentProfile = await fetchStudentProfile(user)
    student.value = studentProfile

    // Fetch terms
    await fetchTerms()

    // Fetch grades for the student  
    await fetchGrades(true) // true = initial load

    // Connect to WebSocket for real-time updates
    if (!isConnected.value) {
      await connect()
    }

  } catch (err) {
    console.error('Failed to fetch student data:', err)
    error.value = err.message || 'Failed to load student data'
  } finally {
    loading.value = false
  }
}

const fetchStudentProfile = async (user) => {
  try {
    // Try to get detailed student information
    const response = await apiClient.get(`/students/${user.id}`)
    const studentData = response.data.data || response.data

    return {
      name: studentData.user?.username || `${studentData.user?.first_name || ''} ${studentData.user?.last_name || ''}`.trim() || user.username || 'Student',
      class: studentData.current_class?.class_name || studentData.class?.class_name || 'N/A',
      student_id: studentData.id || studentData.user_id || user.id,
      class_id: studentData.current_class_id || studentData.class_id,
      current_class: studentData.current_class || null
    }
  } catch (error) {
    console.warn('Failed to fetch detailed student profile, using basic user data:', error)
    
    // Fallback to basic user information
    return {
      name: user.username || `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'Student',
      class: user.class || 'N/A',
      student_id: user.id,
      class_id: user.class_id || null,
      current_class: null
    }
  }
}

const fetchTerms = async () => {
  try {
    const response = await apiClient.get('/terms')
    const termsData = response.data.data || response.data || []
    
    termList.value = termsData.map(term => ({
      id: term.id,
      name: term.term_name || term.name || `Term ${term.id}`
    }))
    
    console.log('📅 Terms loaded:', termList.value)
  } catch (error) {
    console.warn('Failed to fetch terms:', error)
    // Fallback to default terms
    termList.value = [
      { id: 1, name: 'Term 1' },
      { id: 2, name: 'Term 2' },
      { id: 3, name: 'Term 3' }
    ]
  }
}

const fetchGrades = async (isInitialLoad = true) => {
  try {
    if (!student.value.student_id) {
      throw new Error('No student ID available')
    }

    console.log('📊 Fetching grades for student ID:', student.value.student_id)

    // Try multiple API endpoints to get all grades for the student
    let response
    let allGrades = []

    // Method 1: Try the dedicated student grades endpoint
    try {
      console.log('🌐 API Method 1: /student-grades/${student.value.student_id}')
      response = await apiClient.get(`/student-grades/${student.value.student_id}`)
      console.log('📥 Method 1 raw response:', response.data)
      
      // Handle different possible response structures
      let responseGrades = null
      if (response.data?.data) {
        responseGrades = response.data.data
      } else if (Array.isArray(response.data)) {
        responseGrades = response.data
      } else if (response.data?.grades) {
        responseGrades = response.data.grades
      }
      
      if (responseGrades && Array.isArray(responseGrades)) {
        allGrades = responseGrades
        console.log('✅ Method 1 success:', allGrades.length, 'grades')
      } else {
        console.log('⚠️ Method 1: Unexpected response structure')
      }
    } catch (error1) {
      console.warn('❌ Method 1 failed:', error1.response?.status, error1.message)
    }

    // Method 2: Try the general grades endpoint with student filter
    if (allGrades.length === 0) {
      try {
        console.log('🌐 API Method 2: /grades with student filter')
        response = await apiClient.get('/grades', { 
          params: { 
            student_id: student.value.student_id,
            per_page: 1000  // Get all grades
          }
        })
        if (response.data && response.data.data) {
          allGrades = response.data.data
          console.log('✅ Method 2 success:', allGrades.length, 'grades')
        }
      } catch (error2) {
        console.warn('❌ Method 2 failed:', error2.response?.status)
      }
    }

    // Method 3: Try the students API to get related grades
    if (allGrades.length === 0) {
      try {
        console.log('🌐 API Method 3: /students/${student.value.student_id}/grades')
        response = await apiClient.get(`/students/${student.value.student_id}/grades`)
        if (response.data && response.data.data) {
          allGrades = response.data.data
          console.log('✅ Method 3 success:', allGrades.length, 'grades')
        }
      } catch (error3) {
        console.warn('❌ Method 3 failed:', error3.response?.status)
      }
    }

    console.log('📋 Final API Response:', {
      totalGrades: allGrades.length,
      isInitialLoad,
      studentId: student.value.student_id
    })
    
    if (allGrades && allGrades.length > 0) {
      const transformedGrades = allGrades.map(grade => ({
        id: grade.id,
        subject: grade.class_subject?.subject?.subject_name || grade.subject || 'Unknown Subject',
        term_id: grade.term_id,
        assessment_type: grade.assessment_type,
        max_score: grade.max_score || 100,
        score_obtained: grade.score_obtained,
        score: grade.score_obtained,
        total: grade.max_score || 100,
        weightage: grade.weightage || 0,
        grade_letter: grade.grade_letter,
        letter_grade: grade.grade_letter,
        grade: grade.grade_letter,
        remarks: grade.remarks,
        recorded_by: grade.recorded_by?.first_name && grade.recorded_by?.last_name 
          ? `${grade.recorded_by.first_name} ${grade.recorded_by.last_name}`
          : grade.teacher_name || 'Unknown Teacher',
        recorded_at: grade.created_at || grade.recorded_at,
        date: grade.created_at || grade.recorded_at,
        assessment: grade.assessment_type,
        class_subject: grade.class_subject
      }))

      if (isInitialLoad) {
        // Initial load: Replace all grades
        grades.value = transformedGrades
      } else {
        // Incremental update: Only add new grades that don't exist
        const existingIds = new Set(grades.value.map(g => g.id))
        const newGrades = transformedGrades.filter(grade => !existingIds.has(grade.id))
        
        if (newGrades.length > 0) {
          console.log('🆕 Adding', newGrades.length, 'new grades to existing list')
          grades.value = [...newGrades, ...grades.value] // New grades at top
        }
      }

      // Calculate summary statistics
      calculateGradeSummary()
      
      console.log('✅ Real grades loaded successfully:', grades.value.length, 'total grades')
      console.log('📊 Grade details:', grades.value.map(g => ({ id: g.id, subject: g.subject, assessment: g.assessment_type, score: g.score_obtained })))
    } else {
      console.warn('❌ No grades data received from any API endpoint')
      if (isInitialLoad) {
        grades.value = []
      }
    }

  } catch (error) {
    console.error('❌ All grade fetch methods failed:', error)
    
    // Check if it's a 404 (no grades found) or other error
    if (error.response?.status === 404 || error.response?.status === 400) {
      console.log('📝 No grades found for this student')
      if (isInitialLoad) {
        grades.value = []
      }
    } else {
      console.error('API error occurred:', error.response?.status, error.message)
      // Only use mock data in very specific development scenarios
      const shouldUseMockData = import.meta.env.DEV && 
                               (localStorage.getItem('use_mock_grades') === 'true' ||
                                import.meta.env.VITE_USE_MOCK_GRADES === 'true')
      
      if (shouldUseMockData && isInitialLoad) {
        console.log('🧪 Using mock data for development testing (use_mock_grades=true)')
        grades.value = mockGradesData.grades.map(grade => ({
          ...grade,
          subject: grade.subject || 'Mock Subject',
          recorded_by: grade.recorded_by || 'Mock Teacher'
        }))
      } else if (isInitialLoad) {
        console.log('📝 No grades available - showing empty state for new user')
        grades.value = []
      }
    }
    
    if (isInitialLoad) {
      calculateGradeSummary()
    }
  }
}

const calculateGradeSummary = () => {
  if (grades.value.length === 0) {
    gradeSummary.value = {
      best_subject: null,
      weakest_subject: null,
      total_assessments: 0,
      average_score: 0
    }
    studentGPA.value = 0
    return
  }

  // Group grades by subject
  const subjectStats = {}
  
  grades.value.forEach(grade => {
    const subject = grade.subject
    if (!subjectStats[subject]) {
      subjectStats[subject] = {
        scores: [],
        totalScore: 0,
        count: 0
      }
    }
    
    const percentage = (grade.score_obtained / grade.max_score) * 100
    subjectStats[subject].scores.push(percentage)
    subjectStats[subject].totalScore += percentage
    subjectStats[subject].count++
  })

  // Calculate averages
  const subjectAverages = Object.entries(subjectStats).map(([subject, stats]) => ({
    subject,
    average: stats.totalScore / stats.count,
    count: stats.count
  }))

  // Find best and weakest subjects (only if we have multiple subjects)
  let bestSubject, weakestSubject
  
  if (subjectAverages.length === 0) {
    bestSubject = { subject: 'N/A', average: 0, count: 0 }
    weakestSubject = { subject: 'N/A', average: 0, count: 0 }
  } else if (subjectAverages.length === 1) {
    // If only one subject, use it for best, set weakest to N/A
    bestSubject = subjectAverages[0]
    weakestSubject = { subject: 'N/A', average: 0, count: 0 }
  } else {
    // Multiple subjects - find actual best and weakest
    bestSubject = subjectAverages.reduce((max, current) => 
      current.average > max.average ? current : max
    )
    
    weakestSubject = subjectAverages.reduce((min, current) => 
      current.average < min.average ? current : min
    )
    
    // Don't show the same subject as both best and weakest
    if (bestSubject.subject === weakestSubject.subject && subjectAverages.length > 1) {
      const sortedSubjects = [...subjectAverages].sort((a, b) => b.average - a.average)
      bestSubject = sortedSubjects[0]
      weakestSubject = sortedSubjects[sortedSubjects.length - 1]
    }
  }

  // Calculate overall statistics
  const totalScore = grades.value.reduce((sum, grade) => 
    sum + ((grade.score_obtained / grade.max_score) * 100), 0
  )
  const averageScore = totalScore / grades.value.length

  gradeSummary.value = {
    best_subject: bestSubject.subject,
    weakest_subject: weakestSubject.subject,
    total_assessments: grades.value.length,
    average_score: averageScore
  }

  // Calculate GPA (4.0 scale)
  const gradePoints = {
    'A': 4.0, 'B': 3.0, 'C': 2.0, 'D': 1.0, 'E': 0.5, 'F': 0.0
  }
  
  const totalPoints = grades.value.reduce((sum, grade) => {
    const points = gradePoints[grade.grade_letter] || 0
    return sum + points
  }, 0)
  
  studentGPA.value = grades.value.length > 0 ? totalPoints / grades.value.length : 0
  
  console.log('📈 Grade summary calculated:', gradeSummary.value)
  console.log('🎯 GPA calculated:', studentGPA.value)
}

// Handle real-time grade updates
const handleGradeUpdate = (updateData) => {
  console.log('🔄 Handling grade update:', updateData)
  
  const { type, grade, student_id } = updateData
  
  // Only process updates for this student
  if (student_id !== studentId.value) {
    return
  }
  
  switch (type) {
    case 'grade_created':
      // Check if this grade already exists to avoid duplicates
      const existingGrade = grades.value.find(g => g.id === grade.id)
      if (existingGrade) {
        console.log('🔄 Grade already exists, skipping duplicate')
        return
      }
      
      // Transform the grade to match our expected structure
      const newGrade = {
        id: grade.id,
        subject: grade.class_subject?.subject?.subject_name || grade.subject || updateData.metadata?.subject || 'Unknown Subject',
        term_id: grade.term_id,
        assessment_type: grade.assessment_type || updateData.metadata?.assessment_type,
        max_score: grade.max_score || 100,
        score_obtained: grade.score_obtained,
        score: grade.score_obtained,
        total: grade.max_score || 100,
        weightage: grade.weightage || 0,
        grade_letter: grade.grade_letter || updateData.metadata?.grade_letter,
        letter_grade: grade.grade_letter || updateData.metadata?.grade_letter,
        grade: grade.grade_letter || updateData.metadata?.grade_letter,
        remarks: grade.remarks,
        recorded_by: grade.recorded_by?.first_name && grade.recorded_by?.last_name 
          ? `${grade.recorded_by.first_name} ${grade.recorded_by.last_name}`
          : grade.teacher_name || 'Unknown Teacher',
        recorded_at: grade.created_at || grade.recorded_at || new Date().toISOString(),
        date: grade.created_at || grade.recorded_at || new Date().toISOString(),
        assessment: grade.assessment_type || updateData.metadata?.assessment_type,
        class_subject: grade.class_subject
      }
      
      // Add new grade to the top of the list
      grades.value.unshift(newGrade)
      showNotification('New grade added!', `You received a new ${newGrade.assessment_type} grade in ${newGrade.subject}: ${newGrade.grade_letter}`)
      break
      
    case 'grade_updated':
      // Update existing grade
      const gradeIndex = grades.value.findIndex(g => g.id === grade.id)
      if (gradeIndex !== -1) {
        const updatedGrade = {
          ...grades.value[gradeIndex],
          subject: grade.class_subject?.subject?.subject_name || grade.subject || updateData.metadata?.subject || grades.value[gradeIndex].subject,
          term_id: grade.term_id,
          assessment_type: grade.assessment_type || updateData.metadata?.assessment_type,
          max_score: grade.max_score || 100,
          score_obtained: grade.score_obtained,
          score: grade.score_obtained,
          total: grade.max_score || 100,
          weightage: grade.weightage || 0,
          grade_letter: grade.grade_letter || updateData.metadata?.grade_letter,
          letter_grade: grade.grade_letter || updateData.metadata?.grade_letter,
          grade: grade.grade_letter || updateData.metadata?.grade_letter,
          remarks: grade.remarks,
          recorded_at: grade.updated_at || grade.created_at || grade.recorded_at
        }
        
        grades.value[gradeIndex] = updatedGrade
        showNotification('Grade updated!', `Your ${updatedGrade.assessment_type} grade in ${updatedGrade.subject} has been updated: ${updatedGrade.grade_letter}`)
      }
      break
      
    case 'grade_deleted':
      // Remove grade from list
      const deleteIndex = grades.value.findIndex(g => g.id === (grade.id || updateData.grade_id))
      if (deleteIndex !== -1) {
        const deletedGrade = grades.value[deleteIndex]
        grades.value.splice(deleteIndex, 1)
        showNotification('Grade removed', `A grade has been removed from ${deletedGrade.subject}`)
      }
      break
  }
  
  // Recalculate performance metrics
  calculateGradeSummary()
}

// Note: Performance metrics are recalculated automatically via calculateGradeSummary()
// and the computed properties will update reactively

// Show notification to user
const showNotification = (title, message) => {
  // You can integrate with a toast notification library here
  console.log(`🔔 ${title}: ${message}`)
  
  // For now, show a simple alert (replace with proper notification system)
  if (Notification.permission === 'granted') {
    new Notification(title, {
      body: message,
      icon: '/favicon.ico',
      badge: '/favicon.ico'
    })
  }
}

// Watch for real-time grade updates
watch(lastUpdate, (newUpdate) => {
  if (newUpdate) {
    console.log('🔄 Processing real-time update:', newUpdate)
    handleGradeUpdate(newUpdate)
    console.log('📊 Total grades after update:', grades.value.length)
  }
}, { deep: true })

// Auto-refresh data periodically (fallback for missed WebSocket messages)
const setupPeriodicRefresh = () => {
  setInterval(() => {
    if (document.visibilityState === 'visible' && isConnected.value) {
      // Silently check for new grades every 2 minutes when tab is visible
      // This only adds new grades, doesn't replace existing ones
      fetchGrades(false) // false = incremental update
    }
  }, 2 * 60 * 1000) // 2 minutes
}

// Test real-time update functionality (development only)
const testRealTimeUpdate = () => {
  console.log('🧪 Testing real-time update...')
  
  const mockUpdate = {
    type: 'grade_created',
    grade: {
      id: Date.now(),
      subject: 'Test Subject',
      term_id: 1,
      assessment_type: 'quiz',
      max_score: 100,
      score_obtained: 95,
      grade_letter: 'A',
      remarks: 'Test grade',
      created_at: new Date().toISOString()
    },
    student_id: studentId.value,
    teacher_id: 999,
    timestamp: new Date().toISOString(),
    metadata: {
      subject: 'Test Subject',
      assessment_type: 'quiz',
      grade_letter: 'A',
      score: '95/100'
    }
  }
  
  handleGradeUpdate(mockUpdate)
  showNotification('Test Update!', 'This is a test real-time grade update')
}

// Initialize data on component mount
onMounted(() => {
  fetchStudentData()
  setupPeriodicRefresh()
})
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.5s ease-out;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>