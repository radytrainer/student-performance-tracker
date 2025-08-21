<template>
  <div class="max-w-7xl mx-auto p-2">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">My Grade</h1>
      <p class="text-gray-600 mt-1">View your grade records</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-600">Loading your grades...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
      <div class="flex items-center mb-4">
        <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="text-lg font-semibold text-red-800">Error Loading Grades</h3>
      </div>
      <p class="text-red-700 mb-4">{{ error }}</p>
      <button 
        @click="fetchStudentData" 
        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors"
      >
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

        <!-- Enhanced Performance Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <!-- GPA Card -->
          <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 shadow border border-blue-200 relative overflow-hidden">
            <div class="absolute top-4 right-4 text-blue-400 text-3xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <div class="mb-2">
              <p class="text-gray-500 font-semibold text-sm uppercase tracking-wide">GPA</p>
              <p class="text-3xl font-bold text-blue-800">{{ gpa.toFixed(2) }}</p>
            </div>
            <div class="w-full bg-blue-200 rounded-full h-2.5 mt-2">
              <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: (gpa / 4 * 100) + '%' }"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Out of 4.0 scale</p>
          </div>

          <!-- Best Subject Card -->
          <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 shadow border border-green-200 relative overflow-hidden">
            <div class="absolute top-4 right-4 text-green-400 text-3xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
            <div class="mb-2">
              <p class="text-gray-500 font-semibold text-sm uppercase tracking-wide">Best Subject</p>
              <p class="text-xl font-bold text-green-800 truncate">{{ bestSubject.name || '—' }}</p>
            </div>
            <p class="text-2xl font-bold text-green-700">{{ bestSubject.score || 0 }}%</p>
            <p class="text-xs text-gray-500 mt-1">{{ bestSubject.grade || '' }}</p>
          </div>

          <!-- Weakest Subject Card -->
          <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-5 shadow border border-red-200 relative overflow-hidden">
            <div class="absolute top-4 right-4 text-red-400 text-3xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
              </svg>
            </div>
            <div class="mb-2">
              <p class="text-gray-500 font-semibold text-sm uppercase tracking-wide">Improvement</p>
              <p class="text-xl font-bold text-red-800 truncate">{{ weakestSubject.name || '—' }}</p>
            </div>
            <p class="text-2xl font-bold text-red-700">{{ weakestSubject.score || 0 }}%</p>
            <p class="text-xs text-gray-500 mt-1">{{ weakestSubject.grade || '' }}</p>
          </div>

          <!-- Overall Performance Card -->
          <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 shadow border border-purple-200 relative overflow-hidden">
            <div class="absolute top-4 right-4 text-purple-400 text-3xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div class="mb-2">
              <p class="text-gray-500 font-semibold text-sm uppercase tracking-wide">Overall </p>
              <p class="text-xl font-bold text-purple-800">{{ overallPerformance.level }}</p>
            </div>
            <p class="text-2xl font-bold text-purple-700">{{ overallPerformance.score }}%</p>
            <p class="text-xs text-gray-500 mt-1">{{ filteredGrades.length }} assessments</p>
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
                <span class="text-sm font-semibold px-2 py-1 rounded-full" 
                      :class="gradeColor(subject.grade)">{{ subject.grade }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1">
                <div class="h-2.5 rounded-full" 
                     :class="getScoreColorClass(subject.score)" 
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
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
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
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Filter by Term
            </label>
            <select id="term" v-model="selectedTermId" class="w-full border rounded-lg p-2">
              <option value="">All Terms</option>
              <option v-for="term in termList" :key="term.id" :value="term.id">{{ term.name }}</option>
            </select>
          </div>

          <div class="md:self-end">
            <button @click="exportToPDF" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export as PDF
            </button>
          </div>
        </div>

        <div v-if="filteredGrades.length === 0" class="text-center text-red-500 mt-6">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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
                    <div class="h-2 rounded"
                         :class="getScoreColorClass((grade.score_obtained / grade.max_score) * 100)"
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
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from "@/stores/auth"

const authStore = useAuthStore()

// Loading and error states
const loading = ref(true)
const error = ref(null)

// Student info - get from auth store
const student = ref({
  name: '',
  class: '',
  student_id: ''
})

// Term list and filters
const termList = [
  { id: 1, name: 'Term 1' },
  { id: 2, name: 'Term 2' },
  { id: 3, name: 'Term 3' },
]
const selectedTermId = ref('')
const selectedSubject = ref('')

// Grade list - loaded from API
const grades = ref([])
const gradeSummary = ref(null)
const studentGPA = ref(0)

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
  const term = termList.find(t => t.id === Number(selectedTermId.value))
  return term ? term.name : 'All Terms'
})

const subjectList = computed(() => {
  const filtered = selectedTermId.value
    ? grades.value.filter(g => g.term_id === Number(selectedTermId.value))
    : grades.value
  return [...new Set(filtered.map(g => g.subject))]
})

const filteredGrades = computed(() => {
  let result = grades.value
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
  if (subjectPerformance.value.length === 0) return { name: '', score: 0, grade: '' }
  
  const best = subjectPerformance.value[0]
  return {
    name: best.name,
    score: best.score,
    grade: best.grade
  }
})

// Enhanced weakest subject calculation
const weakestSubject = computed(() => {
  if (subjectPerformance.value.length === 0) return { name: '', score: 0, grade: '' }
  
  const weakest = subjectPerformance.value[subjectPerformance.value.length - 1]
  return {
    name: weakest.name,
    score: weakest.score,
    grade: weakest.grade
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

const exportToPDF = () => {
  // Implementation for PDF export would go here
  console.log('Export to PDF functionality')
  alert('PDF export would be implemented here with a library like html2pdf.js')
}

// API Functions
const fetchStudentData = async () => {
  try {
    loading.value = true
    error.value = null

    // Get student info from auth store
    const user = authStore.user
    if (user) {
      student.value = {
        name: user.username || `${user.first_name} ${user.last_name}`.trim() || 'Student',
        class: user.class || 'N/A',
        student_id: user.student_id || user.id
      }
    }

    if (!student.value.student_id) {
      throw new Error('No student ID found')
    }

    console.log('📊 Fetching grades for student:', student.value.student_id)

    // Use mock data for demonstration
    console.log('🧪 Using mock data for testing...');
    
    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 800));
    
    // Load mock data
    grades.value = mockGradesData.grades;
    gradeSummary.value = mockGradesData.summary;
    studentGPA.value = mockGradesData.gpa;
    
    console.log('✅ Mock data loaded successfully');
    console.log('📊 Grades:', grades.value.length);
    console.log('📈 Summary:', gradeSummary.value);
    console.log('🎯 GPA:', studentGPA.value);

  } catch (err) {
    console.error('Failed to fetch student grades:', err)
    error.value = err.message || 'Failed to load grades data'
  } finally {
    loading.value = false
  }
}

// Initialize data on component mount
onMounted(() => {
  fetchStudentData()
})
</script>