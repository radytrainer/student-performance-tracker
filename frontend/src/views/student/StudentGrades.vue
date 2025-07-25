<template>
  <div class="max-w-7xl mx-auto p-2">
    <h1 class="text-2xl font-bold mb-6">🎓 My Grade</h1>


    <!-- ✅ Wrap Exportable Content -->
    <div id="grade-export-content">
      <!-- Student Info -->
      <div class="bg-white shadow-md rounded-lg p-4 mb-4 border">
        <p class="text-gray-700"><span class="font-semibold">Name:</span> {{ student.name }}</p>
        <p class="text-gray-700"><span class="font-semibold">Class:</span> {{ student.class }}</p>
        <p class="text-gray-700"><span class="font-semibold">Term:</span> {{ currentTermName }}</p>
      </div>


      <!-- Summary Card -->
      <h3 class="text-2xl font-bold text-gray-800 mb-4 text-center">📊 Performance Summary</h3>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
        <!-- GPA Card -->
        <div class="bg-blue-50 rounded-xl p-5 shadow text-center border border-blue-200">
          <div class="text-4xl mb-2">🎯</div>
          <p class="text-gray-500 font-semibold text-sm">GPA</p>
          <p class="text-2xl font-bold text-blue-800">{{ gpa.toFixed(2) }}</p>
        </div>

        <!-- Best Subject Card -->
        <div class="bg-green-50 rounded-xl p-5 shadow text-center border border-green-200">
          <div class="text-4xl mb-2">📈</div>
          <p class="text-gray-500 font-semibold text-sm">Best Subject</p>
          <p class="text-xl font-bold text-green-700">{{ bestSubject || '—' }}</p>
        </div>

        <!-- Weakest Subject Card -->
        <div class="bg-red-50 rounded-xl p-5 shadow text-center border border-red-200">
          <div class="text-4xl mb-2">📉</div>
          <p class="text-gray-500 font-semibold text-sm">Weakest Subject</p>
          <p class="text-xl font-bold text-red-600">{{ weakestSubject || '—' }}</p>
        </div>
      </div>


      <!-- Filters + Export -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
        <!-- Subject Filter -->
        <div class="flex-1">
          <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">📚 Filter by Subject</label>
          <select id="subject" v-model="selectedSubject"
            class="w-full border border-gray-300 rounded-lg shadow-sm p-2 text-gray-700 focus:outline-none focus:ring focus:border-blue-300">
            <option value="">All Subjects</option>
            <option v-for="subject in subjectList" :key="subject" :value="subject">
              {{ subject }}
            </option>
          </select>
        </div>

        <!-- Term Filter -->
        <div class="flex-1">
          <label for="term" class="block text-sm font-medium text-gray-700 mb-1">📅 Filter by Term</label>
          <select id="term" v-model="selectedTermId"
            class="w-full border border-gray-300 rounded-lg shadow-sm p-2 text-gray-700 focus:outline-none focus:ring focus:border-blue-300">
            <option value="">All Terms</option>
            <option v-for="term in termList" :key="term.id" :value="term.id">{{ term.name }}</option>
          </select>
        </div>

        <!-- Export Button -->
        <div class="md:self-end">
          <button @click="exportToPDF"
            class="w-full md:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            📤 Export as PDF
          </button>
        </div>
      </div>

      <!-- Subject Summary -->
      <div v-if="selectedSubject && subjectStats" class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-lg">
        <h2 class="text-lg font-semibold text-blue-800 mb-2">{{ selectedSubject }} Summary</h2>
        <ul class="text-sm text-blue-900 space-y-1">
          <li><strong>Assessments:</strong> {{ subjectStats.count }}</li>
          <li><strong>Weighted Score:</strong> {{ subjectStats.weightedTotal.toFixed(2) }} / {{
            subjectStats.weightedMax.toFixed(2) }}</li>
          <li><strong>Weighted Average:</strong> {{ subjectStats.weightedAverage.toFixed(1) }}%</li>
          <li><strong>Overall Grade:</strong>
            <span :class="gradeColor(subjectStats.grade_letter)" class="text-xs font-semibold px-2 py-1 rounded-full">
              {{ subjectStats.grade_letter }}
            </span>
          </li>
        </ul>
      </div>

      <!-- Grades Table -->
      <div class="overflow-x-auto rounded-lg shadow">
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

      <!-- No Data -->
      <div v-if="filteredGrades.length === 0" class="text-center text-gray-500 mt-6">
        😶 No grades found for selected filters.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
// import html2pdf from 'html2pdf.js'

// Dummy student info
const student = ref({
  name: 'Aliya Developer',
  class: 'WD2025-A',
})

// Term info
const termList = [
  { id: 1, name: 'Term 1' },
  { id: 2, name: 'Term 2' },
  { id: 3, name: 'Term 3' },
]
const selectedTermId = ref('2') // Default to Term 2
const currentTermName = computed(() => {
  const term = termList.find(t => t.id === Number(selectedTermId.value))
  return term ? term.name : 'All Terms'
})

// Subject filter
const selectedSubject = ref('')

// Grade list (dummy data)
const grades = ref([
  {
    id: 1, subject: 'Math', term_id: 2, assessment_type: 'Midterm',
    max_score: 100, score_obtained: 78, weightage: 30, grade_letter: 'B',
    remarks: 'Good effort', recorded_by: 'Teacher A', recorded_at: '2025-07-10',
  },
  {
    id: 2, subject: 'Math', term_id: 2, assessment_type: 'Quiz',
    max_score: 20, score_obtained: 18, weightage: 10, grade_letter: 'A',
    remarks: 'Quick learner', recorded_by: 'Teacher A', recorded_at: '2025-07-15',
  },
  {
    id: 3, subject: 'Science', term_id: 2, assessment_type: 'Project',
    max_score: 50, score_obtained: 40, weightage: 40, grade_letter: 'B',
    remarks: '', recorded_by: 'Teacher B', recorded_at: '2025-07-12',
  },
  {
    id: 4, subject: 'English', term_id: 2, assessment_type: 'Final',
    max_score: 100, score_obtained: 85, weightage: 20, grade_letter: 'A',
    remarks: 'Great improvement', recorded_by: 'Teacher C', recorded_at: '2025-07-20',
  },
])

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

const subjectStats = computed(() => {
  if (!selectedSubject.value) return null
  const filtered = grades.value.filter(
    g => g.subject === selectedSubject.value && g.term_id === Number(selectedTermId.value)
  )
  if (!filtered.length) return null

  const weightedTotal = filtered.reduce((sum, g) => {
    return sum + ((g.score_obtained / g.max_score) * g.weightage)
  }, 0)

  const weightedMax = filtered.reduce((sum, g) => sum + g.weightage, 0)
  const weightedAverage = (weightedTotal / weightedMax) * 100
  const grade_letter =
    weightedAverage >= 90 ? 'A' :
      weightedAverage >= 80 ? 'B' :
        weightedAverage >= 70 ? 'C' :
          weightedAverage >= 60 ? 'D' : 'F'

  return { count: filtered.length, weightedTotal, weightedMax, weightedAverage, grade_letter }
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
    // filename: `${student.value.name}-Grades-${currentTermName.value}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
  }
  html2pdf().from(element).set(options).save()
}










//Cards
const gpa = computed(() => {
  if (!filteredGrades.value.length) return 0

  const totalPoints = filteredGrades.value.reduce((sum, g) => {
    const gradePoint = {
      A: 4.0,
      B: 3.0,
      C: 2.0,
      D: 1.0,
      F: 0.0,
    }[g.grade_letter] || 0
    return sum + gradePoint
  }, 0)

  return totalPoints / filteredGrades.value.length
})

const bestSubject = computed(() => {
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

</script>
