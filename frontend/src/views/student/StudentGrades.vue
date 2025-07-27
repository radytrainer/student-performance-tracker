<template>
  <div class="max-w-7xl mx-auto p-2">
    <h1 class="text-2xl font-bold mb-6">🎓 My Grade</h1>

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

      <h3 class="text-2xl font-bold mb-4 text-center">📊 Performance Summary</h3>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
        <div class="bg-blue-50 rounded-xl p-5 shadow text-center border border-blue-200">
          <div class="text-4xl mb-2">🎯</div>
          <p class="text-gray-500 font-semibold text-sm">GPA</p>
          <p class="text-2xl font-bold text-blue-800">{{ gpa.toFixed(2) }}</p>
        </div>

        <div class="bg-green-50 rounded-xl p-5 shadow text-center border border-green-200">
          <div class="text-4xl mb-2">📈</div>
          <p class="text-gray-500 font-semibold text-sm">Best Subject</p>
          <p class="text-xl font-bold text-green-700">{{ bestSubject || '—' }}</p>
        </div>

        <div class="bg-red-50 rounded-xl p-5 shadow text-center border border-red-200">
          <div class="text-4xl mb-2">📉</div>
          <p class="text-gray-500 font-semibold text-sm">Weakest Subject</p>
          <p class="text-xl font-bold text-red-600">{{ weakestSubject || '—' }}</p>
        </div>
      </div>

      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
        <div class="flex-1">
          <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">📚 Filter by Subject</label>
          <select id="subject" v-model="selectedSubject" class="w-full border rounded-lg p-2">
            <option value="">All Subjects</option>
            <option v-for="subject in subjectList" :key="subject" :value="subject">{{ subject }}</option>
          </select>
        </div>

        <div class="flex-1">
          <label for="term" class="block text-sm font-medium text-gray-700 mb-1">📅 Filter by Term</label>
          <select id="term" v-model="selectedTermId" class="w-full border rounded-lg p-2">
            <option value="">All Terms</option>
            <option v-for="term in termList" :key="term.id" :value="term.id">{{ term.name }}</option>
          </select>
        </div>

        <div class="md:self-end">
          <button @click="exportToPDF" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            📤 Export as PDF
          </button>
        </div>
      </div>

      <div v-if="filteredGrades.length === 0" class="text-center text-red-500 mt-6">
        ❌ No grades found for selected filters.
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
                  <div class="bg-green-500 h-2 rounded"
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
import gradesAPI from "@/api/grades"
// import html2pdf from 'html2pdf.js' // TODO: Install html2pdf.js for PDF export

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
// const selectedSubject = ref('')

// Simulated current student ID from auth or route
const currentStudentId = ref(null)

// Fetch grades
const fetchGrades = async () => {
  try {
    const { data } = await gradeService.getAllGrades()
    grades.value = data.map(g => ({
      id: g.id,
      subject: g.class_subject?.subject?.name || 'Unknown',
      term_id: g.term_id,
      assessment_type: g.assessment_type,
      max_score: g.max_score,
      score_obtained: g.score_obtained,
      weightage: g.weightage,
      grade_letter: g.grade_letter,
      remarks: g.remarks,
      recorded_by: g.recorded_by,
      recorded_at: g.recorded_at,
    }))
  } catch (err) {
    console.error('Failed to fetch grades:', err)
  }
}

// Fetch current student info (from token or static ID)
const fetchStudent = async () => {
  try {
    const tokenData = JSON.parse(localStorage.getItem('user')) // or decode JWT token
    const id = tokenData?.id
    if (!id) throw new Error('Student ID not found')

    currentStudentId.value = id

    const { data } = await studentService.getStudent(id)
    student.value = {
      name: `${data.first_name} ${data.last_name}`,
      class: data.class?.name || '—'
    }
  } catch (err) {
    console.error('Failed to fetch student info:', err)
  }
}

// Call both fetchers on mount
onMounted(() => {
  fetchStudent()
  fetchGrades()
})

// Computed values
const currentTermName = computed(() => {
  const term = termList.find(t => t.id === Number(selectedTermId.value))
  return term ? term.name : 'All Terms'
})

// Subject filter
const selectedSubject = ref('')

// Grade list - loaded from API
const grades = ref([])
const gradeSummary = ref(null)
const studentGPA = ref(0)

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

const gradeColor = (letter) => {
  return {
    A: 'bg-green-100 text-green-800',
    B: 'bg-blue-100 text-blue-800',
    C: 'bg-yellow-100 text-yellow-800',
    D: 'bg-orange-100 text-orange-800',
    F: 'bg-red-100 text-red-800',
  }[letter] || 'bg-gray-100 text-gray-700'
}

const formatDate = (str) => new Date(str).toLocaleDateString()

const exportToPDF = () => {
  const element = document.getElementById('grade-export-content')
  const options = {
    margin: 0.5,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
  }
  html2pdf().from(element).set(options).save()
}

// GPA + Performance
const gpa = computed(() => {
  // Use API GPA if available, otherwise calculate from filtered grades
  if (studentGPA.value && !selectedTermId.value && !selectedSubject.value) {
    return studentGPA.value
  }
  
  if (!filteredGrades.value.length) return 0
  const totalPoints = filteredGrades.value.reduce((sum, g) => {
    const gradePoint = {
      A: 4.0, B: 3.0, C: 2.0, D: 1.0, F: 0.0
    }[g.grade_letter] || 0
    return sum + gradePoint
  }, 0)
  return totalPoints / filteredGrades.value.length
})

const bestSubject = computed(() => {
  // Use API summary if available and no filters applied
  if (gradeSummary.value?.best_subject && !selectedTermId.value && !selectedSubject.value) {
    return gradeSummary.value.best_subject
  }

  const subjects = {}
  filteredGrades.value.forEach(g => {
    if (!subjects[g.subject]) subjects[g.subject] = []
    subjects[g.subject].push(g)
  })
  const averages = Object.entries(subjects).map(([subject, grades]) => {
    const total = grades.reduce((s, g) => s + ((g.score_obtained / g.max_score) * 100), 0)
    return { subject, avg: total / grades.length }
  })
  if (!averages.length) return null
  return averages.sort((a, b) => b.avg - a.avg)[0].subject
})

const weakestSubject = computed(() => {
  // Use API summary if available and no filters applied
  if (gradeSummary.value?.weakest_subject && !selectedTermId.value && !selectedSubject.value) {
    return gradeSummary.value.weakest_subject
  }

  const subjects = {}
  filteredGrades.value.forEach(g => {
    if (!subjects[g.subject]) subjects[g.subject] = []
    subjects[g.subject].push(g)
  })
  const averages = Object.entries(subjects).map(([subject, grades]) => {
    const total = grades.reduce((s, g) => s + ((g.score_obtained / g.max_score) * 100), 0)
    return { subject, avg: total / grades.length }
  })
  if (!averages.length) return null
  return averages.sort((a, b) => a.avg - b.avg)[0].subject
})

// Mock data for testing (same as ProfileView)
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
    }
  ],
  summary: {
    best_subject: 'English',
    weakest_subject: 'Science',
    total_assessments: 5,
    average_score: 82.6
  },
  gpa: 3.4
};

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

    // 🧪 TEMPORARY: Use mock data instead of API calls
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

    /* 
    // 🔄 REAL API CALLS (commented out for now)
    const gradesResponse = await gradesAPI.getStudentGrades(student.value.student_id)
    if (gradesResponse.data.success) {
      grades.value = gradesResponse.data.grades || []
      console.log('✅ Loaded', grades.value.length, 'grades')
    }

    // Fetch grade summary
    try {
      const summaryResponse = await gradesAPI.getStudentGradeSummary(student.value.student_id)
      if (summaryResponse.data.success) {
        gradeSummary.value = summaryResponse.data.summary
        console.log('✅ Loaded grade summary:', gradeSummary.value)
      }
    } catch (summaryError) {
      console.warn('Could not fetch grade summary:', summaryError)
    }

    // Fetch GPA
    try {
      const gpaResponse = await gradesAPI.getStudentGPA(student.value.student_id)
      if (gpaResponse.data.success) {
        studentGPA.value = gpaResponse.data.gpa
        console.log('✅ Loaded GPA:', studentGPA.value)
      }
    } catch (gpaError) {
      console.warn('Could not fetch GPA:', gpaError)
    }
    */

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
