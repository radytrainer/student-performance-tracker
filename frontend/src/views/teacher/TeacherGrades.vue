<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4 text-gray-800">Manage Grades</h1>

    <!-- Role-Based Header -->
    <div v-if="user.role === 'student'" class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
      <p class="text-blue-800">You are viewing your grades in read-only mode.</p>
    </div>

    <!-- Filters & Actions Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <!-- Class Filter -->
      <div>
        <label class="block mb-1 font-medium text-gray-700">Class</label>
        <select 
          v-model="filters.class_id" 
          @change="fetchGrades" 
          class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">All</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
        </select>
      </div>

      <!-- Subject Filter -->
      <div>
        <label class="block mb-1 font-medium text-gray-700">Subject</label>
        <select 
          v-model="filters.subject_id" 
          @change="fetchGrades" 
          class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">All</option>
          <option v-for="sub in subjects" :key="sub.id" :value="sub.id">{{ sub.subject_name }}</option>
        </select>
      </div>

      <!-- Term Filter -->
      <div>
        <label class="block mb-1 font-medium text-gray-700">Term</label>
        <select 
          v-model="filters.term_id" 
          @change="fetchGrades" 
          class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">All</option>
          <option v-for="term in terms" :key="term.id" :value="term.id">{{ term.term_name }}</option>
        </select>
      </div>

      <!-- Assessment Type Filter -->
      <div>
        <label class="block mb-1 font-medium text-gray-700">Assessment</label>
        <select 
          v-model="filters.assessment_type" 
          @change="fetchGrades" 
          class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">All</option>
          <option v-for="type in assessmentTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
        </select>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-3 mb-6">
      <!-- Export Buttons -->
      <button 
        @click="exportToExcel" 
        class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
        Export to Excel
      </button>

      <button 
        @click="exportToPDF" 
        class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition-colors"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5 4v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1V4a4 4 0 00-8 0z" clip-rule="evenodd" />
        </svg>
        Export to PDF
      </button>

      <!-- Add Grade Button -->
      <button 
        v-if="user.role === 'teacher'"
        @click="openAddModal" 
        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors ml-auto"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Add New Grade
      </button>
    </div>

    <!-- Analytics Dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Grade Distribution -->
      <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Grade Distribution</h2>
        <div class="w-full h-64">
          <canvas ref="gradeChart" @click="handleGradeChartClick"></canvas>
        </div>
        <div v-if="selectedGradeGroup" class="mt-4 p-3 bg-gray-50 rounded border border-gray-200">
          <h3 class="font-medium text-gray-800">Details for {{ selectedGradeGroup.grade }} grades</h3>
          <p class="text-gray-600">Count: {{ selectedGradeGroup.count }}</p>
          <p class="text-gray-600">Average Score: {{ selectedGradeGroup.avgScore.toFixed(1) }}</p>
          <div class="mt-2">
            <h4 class="font-medium text-sm mb-1 text-gray-700">Students:</h4>
            <ul class="text-sm max-h-40 overflow-y-auto">
              <li v-for="grade in selectedGradeGroup.grades" :key="grade.id" class="py-1 border-b border-gray-100">
                {{ grade.student?.user?.first_name }} {{ grade.student?.user?.last_name }} - {{ grade.score_obtained }}
                <span v-if="grade.score_obtained < 50" class="text-red-500 ml-2">⚠️ Low</span>
              </li>
            </ul>
          </div>
          <button @click="selectedGradeGroup = null" class="text-blue-600 text-sm mt-2 hover:underline">Close</button>
        </div>
      </div>

      <!-- Student Scores -->
      <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Student Scores</h2>
        <div class="w-full h-64">
          <canvas ref="scoresChart" @click="handleScoresChartClick"></canvas>
        </div>
        <div v-if="selectedStudentGrade" class="mt-4 p-3 bg-gray-50 rounded border border-gray-200">
          <h3 class="font-medium text-gray-800">Details for {{ selectedStudentGrade.studentName }}</h3>
          <div class="grid grid-cols-2 gap-2 mt-2">
            <div>
              <p class="text-sm text-gray-600">Score:</p>
              <p :class="{'text-red-500': selectedStudentGrade.score < 50}">
                {{ selectedStudentGrade.score }}
                <span v-if="selectedStudentGrade.score < 50" class="ml-1">⚠️</span>
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Grade:</p>
              <p>{{ selectedStudentGrade.grade }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Assessment:</p>
              <p>{{ selectedStudentGrade.assessmentType }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Class:</p>
              <p>{{ selectedStudentGrade.className }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Subject:</p>
              <p>{{ selectedStudentGrade.subjectName }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Term:</p>
              <p>{{ selectedStudentGrade.termName }}</p>
            </div>
          </div>
          <div class="mt-3">
            <button 
              v-if="user.role === 'teacher'"
              @click="openEditModal(selectedStudentGrade.fullData)" 
              class="text-blue-600 text-sm mr-3 hover:underline"
            >
              Edit Grade
            </button>
            <button @click="selectedStudentGrade = null" class="text-blue-600 text-sm">Close</button>
          </div>
        </div>
      </div>

      <!-- Performance Over Time -->
      <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Performance Over Time</h2>
        <div class="w-full h-64">
          <canvas ref="performanceChart"></canvas>
        </div>
        <div class="mt-4 flex justify-between items-center">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">View By:</label>
            <select 
              v-model="performanceViewBy" 
              @change="updatePerformanceChart" 
              class="border border-gray-300 rounded-md p-1 text-sm"
            >
              <option value="term">Term</option>
              <option value="assessment">Assessment Type</option>
              <option value="subject">Subject</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Metric:</label>
            <select 
              v-model="performanceMetric" 
              @change="updatePerformanceChart" 
              class="border border-gray-300 rounded-md p-1 text-sm"
            >
              <option value="average">Average Score</option>
              <option value="median">Median Score</option>
              <option value="passRate">Pass Rate (%)</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Grade Table -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Grades</h2>
        <div class="flex items-center gap-2">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search students..." 
            class="border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
          >
          <button 
            @click="toggleSortOrder" 
            class="p-2 rounded-md hover:bg-gray-100"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
      </div>
      <div v-else-if="error" class="text-red-500 p-4 bg-red-50 rounded-md">{{ error }}</div>
      <div v-else-if="filteredGrades.length === 0" class="text-gray-500 p-4 text-center">No grades found.</div>

      <div v-else class="overflow-x-auto" id="grades-table">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assessment</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
              <th v-if="user.role === 'teacher'" scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="grade in filteredGrades" :key="grade.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-medium">
                      {{ getInitials(grade.student?.user?.first_name, grade.student?.user?.last_name) }}
                    </span>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ grade.student?.user?.first_name }} {{ grade.student?.user?.last_name }}
                    </div>
                    <div class="text-sm text-gray-500">ID: {{ grade.student_id }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ grade.class_subject?.class?.class_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ grade.class_subject?.subject?.subject_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ grade.term?.term_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                      :class="getAssessmentBadgeClass(grade.assessment_type)">
                  {{ grade.assessment_type }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" 
                  :class="getScoreTextClass(grade.score_obtained)">
                {{ grade.score_obtained }}
                <span v-if="grade.score_obtained < 50" class="ml-1">⚠️</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                      :class="getGradeBadgeClass(grade.grade_letter)">
                  {{ grade.grade_letter || 'N/A' }}
                </span>
              </td>
              <td v-if="user.role === 'teacher'" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="openEditModal(grade)" class="text-blue-500 hover:text-blue-700 mr-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                  </svg>
                </button>
                <button @click="deleteGrade(grade.id)" class="text-red-500 hover:text-red-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="filteredGrades.length > 0" class="flex items-center justify-between mt-4">
        <div class="text-sm text-gray-500">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries
        </div>
        <div class="flex gap-1">
          <button 
            @click="prevPage" 
            :disabled="pagination.currentPage === 1"
            class="px-3 py-1 rounded-md border border-gray-300 disabled:opacity-50"
          >
            Prev
          </button>
          <button 
            v-for="page in pagination.lastPage" 
            @click="goToPage(page)"
            :class="{'bg-blue-600 text-white': pagination.currentPage === page}"
            class="w-8 h-8 rounded-md border border-gray-300"
          >
            {{ page }}
          </button>
          <button 
            @click="nextPage" 
            :disabled="pagination.currentPage === pagination.lastPage"
            class="px-3 py-1 rounded-md border border-gray-300 disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Grade Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white w-full max-w-lg rounded-lg shadow-xl max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-xl font-bold text-gray-800">{{ isEditMode ? 'Edit Grade' : 'Add New Grade' }}</h3>
        </div>
        <div class="overflow-y-auto p-6 flex-1">
          <form @submit.prevent="submitNewGrade">
            <div class="mb-4">
              <label class="block font-medium mb-1 text-gray-700">Student</label>
              <select 
                v-model="newGrade.student_id" 
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" 
                required
              >
                <option disabled value="">Select student</option>
                <option v-for="student in students" :key="student.id" :value="student.user_id">
                  {{ student.user?.first_name }} {{ student.user?.last_name }}
                </option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block font-medium mb-1 text-gray-700">Class-Subject</label>
              <select 
                v-model="newGrade.class_subject_id" 
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" 
                required
              >
                <option v-for="cs in classSubjects" :key="cs.id" :value="cs.id">
                  {{ cs.class.class_name }} - {{ cs.subject.subject_name }}
                </option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block font-medium mb-1 text-gray-700">Term</label>
              <select 
                v-model="newGrade.term_id" 
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" 
                required
              >
                <option v-for="term in terms" :key="term.id" :value="term.id">{{ term.term_name }}</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block font-medium mb-1 text-gray-700">Assessment Type</label>
              <select 
                v-model="newGrade.assessment_type" 
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" 
                required
              >
                <option v-for="type in assessmentTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block font-medium mb-1 text-gray-700">Score</label>
              <input 
                v-model="newGrade.score_obtained" 
                type="number" 
                min="0" 
                max="100" 
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" 
                required
                @input="calculateGradeLetter"
              />
            </div>

            <div class="mb-6">
              <label class="block font-medium mb-1 text-gray-700">Grade Letter</label>
              <select 
                v-model="newGrade.grade_letter" 
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Auto-calculate</option>
                <option v-for="letter in gradeLetters" :key="letter" :value="letter">{{ letter }}</option>
              </select>
            </div>
          </form>
        </div>
        <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
          <div class="flex justify-end gap-3">
            <button 
              type="button" 
              @click="showAddModal = false" 
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              @click="submitNewGrade" 
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors"
            >
              {{ isEditMode ? 'Update' : 'Submit' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, computed } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'
import * as XLSX from 'xlsx'
import html2pdf from 'html2pdf.js'

// Initialize API client
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json'
  }
})

// Add auth token to requests
apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Reactive state
const loading = ref(false)
const error = ref(null)
const grades = ref([])
const classes = ref([])
const subjects = ref([])
const terms = ref([])
const students = ref([])
const classSubjects = ref([])
const user = ref({
  role: 'teacher', // Default to teacher (would normally come from auth)
  name: 'John Doe'
})

// Chart refs
const gradeChart = ref(null)
const scoresChart = ref(null)
const performanceChart = ref(null)
const gradeChartInstance = ref(null)
const scoresChartInstance = ref(null)
const performanceChartInstance = ref(null)

// UI state
const selectedGradeGroup = ref(null)
const selectedStudentGrade = ref(null)
const showAddModal = ref(false)
const isEditMode = ref(false)
const selectedGrade = ref(null)
const performanceViewBy = ref('term')
const performanceMetric = ref('average')

// Filters and pagination
const filters = ref({ 
  class_id: '', 
  subject_id: '', 
  term_id: '', 
  assessment_type: '' 
})

const searchQuery = ref('')
const sortOrder = ref('asc')
const pagination = ref({
  currentPage: 1,
  perPage: 10,
  total: 0,
  lastPage: 1,
  from: 1,
  to: 10
})

// Constants
const assessmentTypes = ref([
  { value: 'quiz', label: 'Quiz' },
  { value: 'exam', label: 'Exam' },
  { value: 'project', label: 'Project' },
  { value: 'assignment', label: 'Assignment' }
])

const gradeLetters = ['A', 'B', 'C', 'D', 'E', 'F']

// Form data
const newGrade = ref({
  student_id: '',
  class_subject_id: '',
  term_id: '',
  assessment_type: '',
  score_obtained: '',
  grade_letter: ''
})

// Computed properties
const filteredGrades = computed(() => {
  let result = grades.value

  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(grade => 
      `${grade.student?.user?.first_name} ${grade.student?.user?.last_name}`.toLowerCase().includes(query) ||
      grade.student_id.toString().includes(query)
    )
  }

  // Apply sorting
  result = [...result].sort((a, b) => {
    if (sortOrder.value === 'asc') {
      return a.score_obtained - b.score_obtained
    } else {
      return b.score_obtained - a.score_obtained
    }
  })

  // Update pagination totals
  pagination.value.total = result.length
  pagination.value.lastPage = Math.ceil(result.length / pagination.value.perPage)

  // Apply pagination
  const start = (pagination.value.currentPage - 1) * pagination.value.perPage
  const end = start + pagination.value.perPage
  result = result.slice(start, end)

  // Update pagination range
  pagination.value.from = start + 1
  pagination.value.to = Math.min(end, pagination.value.total)

  return result
})

// Methods
const fetchAll = async () => {
  loading.value = true
  try {
    const [cls, sub, term, stu, cs] = await Promise.all([
      apiClient.get('/classes'),
      apiClient.get('/subjects'),
      apiClient.get('/terms'),
      apiClient.get('/students'),
      apiClient.get('/my-class-subjects')
    ])
    classes.value = cls.data.data
    subjects.value = sub.data.data
    terms.value = term.data.data
    students.value = stu.data.data
    classSubjects.value = cs.data.data
    await fetchGrades()
  } catch (err) {
    error.value = 'Failed to load data: ' + (err.response?.data?.message || err.message)
  } finally {
    loading.value = false
  }
}

const fetchGrades = async () => {
  loading.value = true
  try {
    const res = await apiClient.get('/grades', { params: filters.value })
    grades.value = res.data.data
    updateCharts()
  } catch (err) {
    error.value = 'Failed to load grades: ' + (err.response?.data?.message || err.message)
  } finally {
    loading.value = false
  }
}

const calculateGradeLetter = () => {
  if (newGrade.value.score_obtained === '' || newGrade.value.score_obtained === null) {
    newGrade.value.grade_letter = ''
    return
  }

  const score = parseFloat(newGrade.value.score_obtained)
  
  if (score >= 90) newGrade.value.grade_letter = 'A'
  else if (score >= 80) newGrade.value.grade_letter = 'B'
  else if (score >= 70) newGrade.value.grade_letter = 'C'
  else if (score >= 60) newGrade.value.grade_letter = 'D'
  else if (score >= 50) newGrade.value.grade_letter = 'E'
  else newGrade.value.grade_letter = 'F'
}

const updateCharts = () => {
  updateGradeDistributionChart()
  updateStudentScoresChart()
  updatePerformanceChart()
}

const updateGradeDistributionChart = () => {
  if (!grades.value.length) {
    if (gradeChartInstance.value) {
      gradeChartInstance.value.destroy()
      gradeChartInstance.value = null
    }
    return
  }
  
  // Group grades by their grade letters with additional stats
  const gradeGroups = {}
  grades.value.forEach(grade => {
    const letter = grade.grade_letter || 'Ungraded'
    if (!gradeGroups[letter]) {
      gradeGroups[letter] = {
        count: 0,
        totalScore: 0,
        grades: []
      }
    }
    gradeGroups[letter].count++
    gradeGroups[letter].totalScore += grade.score_obtained
    gradeGroups[letter].grades.push(grade)
  })
  
  const labels = Object.keys(gradeGroups)
  const data = Object.values(gradeGroups).map(g => g.count)
  
  const backgroundColors = [
    'rgba(75, 192, 192, 0.7)',  // A - Green
    'rgba(54, 162, 235, 0.7)',  // B - Blue
    'rgba(255, 205, 86, 0.7)',   // C - Yellow
    'rgba(255, 159, 64, 0.7)',   // D - Orange
    'rgba(255, 99, 132, 0.7)',   // E - Red
    'rgba(201, 203, 207, 0.7)'   // F - Gray
  ]

  const chartData = {
    labels: labels,
    datasets: [{
      label: 'Grade Distribution',
      data: data,
      backgroundColor: labels.map((letter, i) => 
        backgroundColors[i] || 'rgba(201, 203, 207, 0.7)'
      ),
      borderColor: 'rgba(255, 255, 255, 0.8)',
      borderWidth: 1,
      hoverOffset: 10
    }]
  }

  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'right',
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            const label = context.label || ''
            const value = context.raw || 0
            const total = context.dataset.data.reduce((a, b) => a + b, 0)
            const percentage = Math.round((value / total) * 100)
            return `${label}: ${value} (${percentage}%)`
          }
        }
      }
    },
    cutout: '60%',
    animation: {
      animateScale: true,
      animateRotate: true
    }
  }

  nextTick(() => {
    if (gradeChartInstance.value) {
      gradeChartInstance.value.data = chartData
      gradeChartInstance.value.options = chartOptions
      gradeChartInstance.value.update()
    } else if (gradeChart.value) {
      gradeChartInstance.value = new Chart(gradeChart.value, {
        type: 'pie',
        data: chartData,
        options: chartOptions
      })
    }
  })
}

const updateStudentScoresChart = () => {
  if (!grades.value.length) {
    if (scoresChartInstance.value) {
      scoresChartInstance.value.destroy()
      scoresChartInstance.value = null
    }
    return
  }

  // Sort grades by student name for better readability
  const sortedGrades = [...grades.value].sort((a, b) => {
    const nameA = `${a.student?.user?.first_name} ${a.student?.user?.last_name}`.toLowerCase()
    const nameB = `${b.student?.user?.first_name} ${b.student?.user?.last_name}`.toLowerCase()
    return nameA.localeCompare(nameB)
  })

  const labels = sortedGrades.map(grade => 
    `${grade.student?.user?.first_name} ${grade.student?.user?.last_name}`
  )
  const scores = sortedGrades.map(grade => grade.score_obtained)
  const gradeLetters = sortedGrades.map(grade => grade.grade_letter || 'N/A')

  // Generate colors based on grade letters
  const gradeColors = {
    'A': 'rgba(75, 192, 192, 0.7)',  // Green
    'B': 'rgba(54, 162, 235, 0.7)',  // Blue
    'C': 'rgba(255, 205, 86, 0.7)',   // Yellow
    'D': 'rgba(255, 159, 64, 0.7)',   // Orange
    'E': 'rgba(255, 99, 132, 0.7)',   // Red
    'F': 'rgba(255, 99, 132, 0.7)',   // Red
    'N/A': 'rgba(201, 203, 207, 0.7)' // Gray
  }

  const backgroundColors = gradeLetters.map(letter => 
    gradeColors[letter] || gradeColors['N/A']
  )

  const chartData = {
    labels: labels,
    datasets: [{
      label: 'Scores',
      data: scores,
      backgroundColor: backgroundColors,
      borderColor: backgroundColors.map(c => c.replace('0.7', '1')),
      borderWidth: 1,
      barPercentage: 0.8
    }]
  }

  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            return `Score: ${context.raw} (Grade: ${gradeLetters[context.dataIndex]})`
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        max: 100,
        title: {
          display: true,
          text: 'Score'
        }
      },
      x: {
        title: {
          display: true,
          text: 'Students'
        },
        ticks: {
          autoSkip: false,
          maxRotation: 45,
          minRotation: 45
        }
      }
    }
  }

  nextTick(() => {
    if (scoresChartInstance.value) {
      scoresChartInstance.value.data = chartData
      scoresChartInstance.value.options = chartOptions
      scoresChartInstance.value.update()
    } else if (scoresChart.value) {
      scoresChartInstance.value = new Chart(scoresChart.value, {
        type: 'bar',
        data: chartData,
        options: chartOptions
      })
    }
  })
}

const updatePerformanceChart = () => {
  if (!grades.value.length) {
    if (performanceChartInstance.value) {
      performanceChartInstance.value.destroy()
      performanceChartInstance.value = null
    }
    return
  }

  let labels = []
  let data = []
  let backgroundColor = 'rgba(79, 70, 229, 0.7)'

  // Group data based on selected view
  if (performanceViewBy.value === 'term') {
    const termGroups = {}
    grades.value.forEach(grade => {
      const termName = grade.term?.term_name || 'Unknown Term'
      if (!termGroups[termName]) {
        termGroups[termName] = []
      }
      termGroups[termName].push(grade.score_obtained)
    })

    labels = Object.keys(termGroups)
    data = Object.values(termGroups).map(scores => {
      if (performanceMetric.value === 'average') {
        return scores.reduce((a, b) => a + b, 0) / scores.length
      } else if (performanceMetric.value === 'median') {
        const sorted = [...scores].sort((a, b) => a - b)
        const mid = Math.floor(sorted.length / 2)
        return sorted.length % 2 !== 0 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2
      } else { // passRate
        const passing = scores.filter(score => score >= 50).length
        return (passing / scores.length) * 100
      }
    })
  } else if (performanceViewBy.value === 'assessment') {
    const assessmentGroups = {}
    grades.value.forEach(grade => {
      const assessmentType = grade.assessment_type || 'Unknown'
      if (!assessmentGroups[assessmentType]) {
        assessmentGroups[assessmentType] = []
      }
      assessmentGroups[assessmentType].push(grade.score_obtained)
    })

    labels = Object.keys(assessmentGroups)
    data = Object.values(assessmentGroups).map(scores => {
      if (performanceMetric.value === 'average') {
        return scores.reduce((a, b) => a + b, 0) / scores.length
      } else if (performanceMetric.value === 'median') {
        const sorted = [...scores].sort((a, b) => a - b)
        const mid = Math.floor(sorted.length / 2)
        return sorted.length % 2 !== 0 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2
      } else { // passRate
        const passing = scores.filter(score => score >= 50).length
        return (passing / scores.length) * 100
      }
    })
  } else { // subject
    const subjectGroups = {}
    grades.value.forEach(grade => {
      const subjectName = grade.class_subject?.subject?.subject_name || 'Unknown Subject'
      if (!subjectGroups[subjectName]) {
        subjectGroups[subjectName] = []
      }
      subjectGroups[subjectName].push(grade.score_obtained)
    })

    labels = Object.keys(subjectGroups)
    data = Object.values(subjectGroups).map(scores => {
      if (performanceMetric.value === 'average') {
        return scores.reduce((a, b) => a + b, 0) / scores.length
      } else if (performanceMetric.value === 'median') {
        const sorted = [...scores].sort((a, b) => a - b)
        const mid = Math.floor(sorted.length / 2)
        return sorted.length % 2 !== 0 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2
      } else { // passRate
        const passing = scores.filter(score => score >= 50).length
        return (passing / scores.length) * 100
      }
    })
  }

  const chartData = {
    labels: labels,
    datasets: [{
      label: performanceMetric.value === 'passRate' ? 'Pass Rate (%)' : 
             performanceMetric.value === 'average' ? 'Average Score' : 'Median Score',
      data: data,
      backgroundColor: backgroundColor,
      borderColor: backgroundColor.replace('0.7', '1'),
      borderWidth: 1,
      tension: 0.1
    }]
  }

  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            const label = performanceMetric.value === 'passRate' ? 'Pass Rate' : 
                         performanceMetric.value === 'average' ? 'Average' : 'Median'
            return `${label}: ${context.raw.toFixed(1)}`
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        max: performanceMetric.value === 'passRate' ? 100 : 100,
        title: {
          display: true,
          text: performanceMetric.value === 'passRate' ? 'Pass Rate (%)' : 'Score'
        }
      },
      x: {
        title: {
          display: true,
          text: performanceViewBy.value === 'term' ? 'Term' : 
                performanceViewBy.value === 'assessment' ? 'Assessment Type' : 'Subject'
        }
      }
    }
  }

  nextTick(() => {
    if (performanceChartInstance.value) {
      performanceChartInstance.value.data = chartData
      performanceChartInstance.value.options = chartOptions
      performanceChartInstance.value.update()
    } else if (performanceChart.value) {
      performanceChartInstance.value = new Chart(performanceChart.value, {
        type: 'line',
        data: chartData,
        options: chartOptions
      })
    }
  })
}

const handleGradeChartClick = (event) => {
  if (gradeChartInstance.value) {
    const elements = gradeChartInstance.value.getElementsAtEventForMode(
      event,
      'nearest',
      { intersect: true },
      false
    )
    if (elements.length) {
      const index = elements[0].index
      const gradeLetter = gradeChartInstance.value.data.labels[index]
      const gradeData = grades.value.filter(g => (g.grade_letter || 'Ungraded') === gradeLetter)
      
      selectedGradeGroup.value = {
        grade: gradeLetter,
        count: gradeData.length,
        avgScore: gradeData.reduce((sum, g) => sum + g.score_obtained, 0) / gradeData.length,
        grades: gradeData
      }
    }
  }
}

const handleScoresChartClick = (event) => {
  if (scoresChartInstance.value) {
    const elements = scoresChartInstance.value.getElementsAtEventForMode(
      event,
      'nearest',
      { intersect: true },
      false
    )
    if (elements.length) {
      const index = elements[0].index
      const grade = grades.value[index]
      
      selectedStudentGrade.value = {
        studentName: `${grade.student?.user?.first_name} ${grade.student?.user?.last_name}`,
        score: grade.score_obtained,
        grade: grade.grade_letter || 'N/A',
        assessmentType: grade.assessment_type,
        className: grade.class_subject?.class?.class_name || 'N/A',
        subjectName: grade.class_subject?.subject?.subject_name || 'N/A',
        termName: grade.term?.term_name || 'N/A',
        fullData: grade
      }
    }
  }
}

const openAddModal = () => {
  isEditMode.value = false
  selectedGrade.value = null
  resetGradeForm()
  showAddModal.value = true
}

const openEditModal = (grade) => {
  isEditMode.value = true
  selectedGrade.value = grade
  newGrade.value = {
    student_id: grade.student_id,
    class_subject_id: grade.class_subject_id,
    term_id: grade.term_id,
    assessment_type: grade.assessment_type,
    score_obtained: grade.score_obtained,
    grade_letter: grade.grade_letter || ''
  }
  // Calculate grade if not set
  if (!grade.grade_letter && grade.score_obtained) {
    calculateGradeLetter()
  }
  showAddModal.value = true
  selectedStudentGrade.value = null
  selectedGradeGroup.value = null
}

const resetGradeForm = () => {
  newGrade.value = {
    student_id: '',
    class_subject_id: '',
    term_id: '',
    assessment_type: '',
    score_obtained: '',
    grade_letter: ''
  }
}

const submitNewGrade = async () => {
  // Calculate grade if not manually set
  if (!newGrade.value.grade_letter && newGrade.value.score_obtained) {
    calculateGradeLetter()
  }

  const payload = { 
    ...newGrade.value, 
    max_score: 100, 
    weightage: 1,
    recorded_by: user.value.id // Add the current user as the recorder
  }

  try {
    if (isEditMode.value && selectedGrade.value) {
      const res = await apiClient.put(`/grades/${selectedGrade.value.id}`, payload)
      const idx = grades.value.findIndex(g => g.id === selectedGrade.value.id)
      if (idx !== -1) grades.value[idx] = res.data.data
    } else {
      const res = await apiClient.post('/grades', payload)
      grades.value.unshift(res.data.data)
    }
    showAddModal.value = false
    resetGradeForm()
    updateCharts()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to save grade')
  }
}

const deleteGrade = async (id) => {
  if (!confirm('Are you sure you want to delete this grade?')) return
  try {
    await apiClient.delete(`/grades/${id}`)
    grades.value = grades.value.filter(g => g.id !== id)
    updateCharts()
    selectedStudentGrade.value = null
    selectedGradeGroup.value = null
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete grade')
  }
}

const exportToExcel = () => {
  if (grades.value.length === 0) {
    alert('No data to export')
    return
  }

  // Prepare the data for export
  const data = grades.value.map(grade => ({
    'Student Name': `${grade.student?.user?.first_name} ${grade.student?.user?.last_name}`,
    'Student ID': grade.student_id,
    'Class': grade.class_subject?.class?.class_name || 'N/A',
    'Subject': grade.class_subject?.subject?.subject_name || 'N/A',
    'Term': grade.term?.term_name || 'N/A',
    'Assessment Type': grade.assessment_type,
    'Score': grade.score_obtained,
    'Grade': grade.grade_letter || 'N/A',
    'Max Score': 100,
    'Date Recorded': grade.created_at ? new Date(grade.created_at).toLocaleDateString() : 'N/A'
  }))

  // Create a worksheet
  const ws = XLSX.utils.json_to_sheet(data)
  
  // Create a workbook
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Grades')
  
  // Generate current date for filename
  const today = new Date()
  const dateStr = today.toISOString().split('T')[0]
  
  // Export the file
  XLSX.writeFile(wb, `Grades_${dateStr}.xlsx`)
}

const exportToPDF = () => {
  if (grades.value.length === 0) {
    alert('No data to export')
    return
  }

  const element = document.getElementById('grades-table')
  const opt = {
    margin: 10,
    filename: `grades_${new Date().toISOString().split('T')[0]}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
  }

  html2pdf().from(element).set(opt).save()
}

const toggleSortOrder = () => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
}

const prevPage = () => {
  if (pagination.value.currentPage > 1) {
    pagination.value.currentPage--
  }
}

const nextPage = () => {
  if (pagination.value.currentPage < pagination.value.lastPage) {
    pagination.value.currentPage++
  }
}

const goToPage = (page) => {
  pagination.value.currentPage = page
}

const getInitials = (firstName, lastName) => {
  if (!firstName || !lastName) return '??'
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
}

const getAssessmentBadgeClass = (type) => {
  const classes = {
    'quiz': 'bg-purple-100 text-purple-800',
    'exam': 'bg-red-100 text-red-800',
    'project': 'bg-green-100 text-green-800',
    'assignment': 'bg-yellow-100 text-yellow-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getGradeBadgeClass = (grade) => {
  const classes = {
    'A': 'bg-green-100 text-green-800',
    'B': 'bg-blue-100 text-blue-800',
    'C': 'bg-yellow-100 text-yellow-800',
    'D': 'bg-orange-100 text-orange-800',
    'E': 'bg-red-100 text-red-800',
    'F': 'bg-red-100 text-red-800'
  }
  return classes[grade] || 'bg-gray-100 text-gray-800'
}

const getScoreTextClass = (score) => {
  if (score >= 90) return 'text-green-600'
  if (score >= 80) return 'text-blue-600'
  if (score >= 70) return 'text-yellow-600'
  if (score >= 60) return 'text-orange-600'
  if (score >= 50) return 'text-red-500'
  return 'text-red-600'
}

// Lifecycle hooks
onMounted(() => {
  fetchAll()
})

watch(grades, updateCharts, { deep: true })
watch([performanceViewBy, performanceMetric], updatePerformanceChart)
</script>

<style scoped>
/* Custom scrollbar for the table container */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Modal scrollable content */
.overflow-y-auto {
  overflow-y: auto;
}

/* Custom scrollbar for modal */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Smooth transitions for hover effects */
tr {
  transition: background-color 0.2s ease;
}

/* Focus styles for form inputs */
select:focus, input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}

/* Animation for loading spinner */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
.animate-spin {
  animation: spin 1s linear infinite;
}

/* Modal transition */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>