<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Manage Grades</h1>

    <!-- Filters -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div>
        <label class="block mb-1 font-medium">Class</label>
        <select v-model="filters.class_id" @change="fetchGrades" class="w-full border p-2 rounded">
          <option value="">All</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
        </select>
      </div>
      <div>
        <label class="block mb-1 font-medium">Subject</label>
        <select v-model="filters.subject_id" @change="fetchGrades" class="w-full border p-2 rounded">
          <option value="">All</option>
          <option v-for="sub in subjects" :key="sub.id" :value="sub.id">{{ sub.subject_name }}</option>
        </select>
      </div>
      <div>
        <label class="block mb-1 font-medium">Term</label>
        <select v-model="filters.term_id" @change="fetchGrades" class="w-full border p-2 rounded">
          <option value="">All</option>
          <option v-for="term in terms" :key="term.id" :value="term.id">{{ term.term_name }}</option>
        </select>
      </div>
      <div>
        <label class="block mb-1 font-medium">Assessment Type</label>
        <select v-model="filters.assessment_type" @change="fetchGrades" class="w-full border p-2 rounded">
          <option value="">All</option>
          <option v-for="type in assessmentTypes" :key="type">{{ type }}</option>
        </select>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <!-- Grade Distribution Chart -->
      <div class="bg-white p-4 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Grade Distribution</h2>
        <div class="w-full h-64">
          <canvas ref="gradeChart" @click="handleGradeChartClick"></canvas>
        </div>
        <div v-if="selectedGradeGroup" class="mt-4 p-3 bg-gray-50 rounded">
          <h3 class="font-medium">Details for {{ selectedGradeGroup.grade }} grades</h3>
          <p>Count: {{ selectedGradeGroup.count }}</p>
          <p>Average Score: {{ selectedGradeGroup.avgScore.toFixed(1) }}</p>
          <div class="mt-2">
            <h4 class="font-medium text-sm mb-1">Students:</h4>
            <ul class="text-sm max-h-40 overflow-y-auto">
              <li v-for="grade in selectedGradeGroup.grades" :key="grade.id" class="py-1 border-b">
                {{ grade.student?.user?.first_name }} {{ grade.student?.user?.last_name }} - {{ grade.score_obtained }}
              </li>
            </ul>
          </div>
          <button @click="selectedGradeGroup = null" class="text-blue-600 text-sm mt-2">Close</button>
        </div>
      </div>

      <!-- Student Scores Chart -->
      <div class="bg-white p-4 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Student Scores</h2>
        <div class="w-full h-64">
          <canvas ref="scoresChart" @click="handleScoresChartClick"></canvas>
        </div>
        <div v-if="selectedStudentGrade" class="mt-4 p-3 bg-gray-50 rounded">
          <h3 class="font-medium">Details for {{ selectedStudentGrade.studentName }}</h3>
          <div class="grid grid-cols-2 gap-2 mt-2">
            <div>
              <p class="text-sm text-gray-600">Score:</p>
              <p>{{ selectedStudentGrade.score }}</p>
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
            <button @click="openEditModal(selectedStudentGrade.fullData)" 
                    class="text-blue-600 text-sm mr-3 hover:underline">
              Edit Grade
            </button>
            <button @click="selectedStudentGrade = null" class="text-blue-600 text-sm">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Grade Table -->
    <div class="p-4">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Teacher Grades</h2>
        <button @click="openAddModal" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          + Add New Grade
        </button>
      </div>

      <div v-if="loading" class="text-blue-600">Loading grades...</div>
      <div v-else-if="error" class="text-red-500">{{ error }}</div>
      <div v-else-if="grades.length === 0" class="text-gray-500">No grades available.</div>

      <table v-else class="w-full table-auto border-collapse border border-gray-300">
        <thead>
          <tr class="bg-gray-200">
            <th class="p-2 border">Student</th>
            <th class="p-2 border">Class</th>
            <th class="p-2 border">Subject</th>
            <th class="p-2 border">Term</th>
            <th class="p-2 border">Assessment Type</th>
            <th class="p-2 border">Score</th>
            <th class="p-2 border">Grade</th>
            <th class="p-2 border">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="grade in grades" :key="grade.id">
            <td class="p-2 border">{{ grade.student?.user?.first_name }} {{ grade.student?.user?.last_name }}</td>
            <td class="p-2 border">{{ grade.class_subject?.class?.class_name }}</td>
            <td class="p-2 border">{{ grade.class_subject?.subject?.subject_name }}</td>
            <td class="p-2 border">{{ grade.term?.term_name }}</td>
            <td class="p-2 border">{{ grade.assessment_type }}</td>
            <td class="p-2 border">{{ grade.score_obtained }}</td>
            <td class="p-2 border">{{ grade.grade_letter }}</td>
            <td class="p-2 border flex gap-2">
              <button class="text-blue-600 hover:underline" @click="openEditModal(grade)">Edit</button>
              <button class="text-red-600 hover:underline" @click="deleteGrade(grade.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add/Edit Grade Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white w-full max-w-lg p-6 rounded shadow-lg">
        <h3 class="text-xl font-bold mb-4">{{ isEditMode ? 'Edit Grade' : 'Add New Grade' }}</h3>

        <form @submit.prevent="submitNewGrade">
          <div class="mb-3">
            <label class="block font-medium mb-1">Student</label>
            <select v-model="newGrade.student_id" class="w-full border p-2 rounded" required>
              <option disabled value="">Select student</option>
              <option v-for="student in students" :key="student.id" :value="student.user_id">
                {{ student.user?.first_name }} {{ student.user?.last_name }}
              </option>
            </select>
          </div>

          <div class="mb-3">
            <label class="block font-medium mb-1">Class-Subject</label>
            <select v-model="newGrade.class_subject_id" class="w-full border p-2 rounded" required>
              <option v-for="cs in classSubjects" :key="cs.id" :value="cs.id">
                {{ cs.class.class_name }} - {{ cs.subject.subject_name }}
              </option>
            </select>
          </div>

          <div class="mb-3">
            <label class="block font-medium mb-1">Term</label>
            <select v-model="newGrade.term_id" class="w-full border p-2 rounded" required>
              <option v-for="term in terms" :key="term.id" :value="term.id">{{ term.term_name }}</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="block font-medium mb-1">Assessment Type</label>
            <select v-model="newGrade.assessment_type" class="w-full border p-2 rounded" required>
              <option v-for="type in assessmentTypes" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="block font-medium mb-1">Score</label>
            <input 
              v-model="newGrade.score_obtained" 
              type="number" 
              min="0" 
              max="100" 
              class="w-full border p-2 rounded" 
              required
              @input="calculateGradeLetter"
            />
          </div>

          <div class="mb-3">
            <label class="block font-medium mb-1">Grade Letter</label>
            <select v-model="newGrade.grade_letter" class="w-full border p-2 rounded">
              <option value="">Auto-calculate</option>
              <option v-for="letter in gradeLetters" :key="letter" :value="letter">{{ letter }}</option>
            </select>
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" @click="showAddModal = false" class="px-4 py-2 border rounded">Cancel</button>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
              {{ isEditMode ? 'Update' : 'Submit' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json'
  }
})

apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

const loading = ref(false)
const error = ref(null)
const grades = ref([])
const classes = ref([])
const subjects = ref([])
const terms = ref([])
const students = ref([])
const classSubjects = ref([])
const gradeChart = ref(null)
const scoresChart = ref(null)
const gradeChartInstance = ref(null)
const scoresChartInstance = ref(null)
const selectedGradeGroup = ref(null)
const selectedStudentGrade = ref(null)

const filters = ref({ 
  class_id: '', 
  subject_id: '', 
  term_id: '', 
  assessment_type: '' 
})

const assessmentTypes = ref(['quiz', 'exam', 'project', 'assignment'])
const gradeLetters = ['A', 'B', 'C', 'D', 'E', 'F']

const newGrade = ref({
  student_id: '',
  class_subject_id: '',
  term_id: '',
  assessment_type: '',
  score_obtained: '',
  grade_letter: ''
})

const showAddModal = ref(false)
const isEditMode = ref(false)
const selectedGrade = ref(null)

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
      label: 'Number of Students',
      data: data,
      backgroundColor: labels.map((letter, i) => 
        backgroundColors[i] || 'rgba(201, 203, 207, 0.7)'
      ),
      borderColor: labels.map((letter, i) => 
        backgroundColors[i] ? backgroundColors[i].replace('0.7', '1') : 'rgba(201, 203, 207, 1)'
      ),
      borderWidth: 1
    }]
  }

  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top',
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            return `${context.label}: ${context.raw} students`
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          stepSize: 1
        }
      }
    }
  }

  nextTick(() => {
    if (gradeChartInstance.value) {
      gradeChartInstance.value.data = chartData
      gradeChartInstance.value.options = chartOptions
      gradeChartInstance.value.update()
    } else if (gradeChart.value) {
      gradeChartInstance.value = new Chart(gradeChart.value, {
        type: 'bar',
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
    weightage: 1 
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

watch(grades, updateCharts, { deep: true })

onMounted(() => {
  fetchAll()
})
</script>

<style scoped>
select:invalid {
  color: gray;
}

canvas {
  width: 100% !important;
  height: 100% !important;
}

.bg-gray-50 {
  background-color: #f9fafb;
}

.max-h-40 {
  max-height: 10rem;
}
</style>