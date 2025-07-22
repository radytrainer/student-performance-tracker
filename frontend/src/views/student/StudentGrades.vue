<template>
  <div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">🎓 My Grade</h1>

    <!-- Exportable Content -->
    <div id="grade-export-content">
      <!-- Student Info -->
      <div class="bg-white shadow-md rounded-lg p-4 mb-4 border">
        <p class="text-gray-700"><span class="font-semibold">Name:</span> {{ student.name }}</p>
        <p class="text-gray-700"><span class="font-semibold">Class:</span> {{ student.class }}</p>
        <p class="text-gray-700"><span class="font-semibold">Term:</span> {{ currentTermName }}</p>
      </div>

      <!-- Summary Cards -->
      <h3 class="text-2xl font-bold text-gray-800 mb-4 text-center">📊 Performance Summary</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
        <SummaryCard emoji="🎯" title="GPA" :value="gpa.toFixed(2)" color="blue" />
        <SummaryCard emoji="📈" title="Best Subject" :value="bestSubject || '—'" color="green" />
        <SummaryCard emoji="📉" title="Weakest Subject" :value="weakestSubject || '—'" color="red" />
      </div>

      <!-- Filters -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
        <!-- Subject Filter -->
        <div class="flex-1">
          <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">📚 Filter by Subject</label>
          <select v-model="selectedSubject"
                  class="w-full border border-gray-300 rounded-lg shadow-sm p-2 text-gray-700 focus:outline-none">
            <option value="">All Subjects</option>
            <option v-for="subject in subjectList" :key="subject" :value="subject">
              {{ subject }}
            </option>
          </select>
        </div>

        <!-- Term Filter -->
        <div class="flex-1">
          <label for="term" class="block text-sm font-medium text-gray-700 mb-1">📅 Filter by Term</label>
          <select v-model="selectedTermId"
                  class="w-full border border-gray-300 rounded-lg shadow-sm p-2 text-gray-700 focus:outline-none">
            <option value="">All Terms</option>
            <option v-for="term in termList" :key="term.id" :value="term.id">
              {{ term.name }}
            </option>
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
          <li><strong>Weighted Score:</strong> {{ subjectStats.weightedTotal.toFixed(2) }} / {{ subjectStats.weightedMax.toFixed(2) }}</li>
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
          <thead class="bg-gray-100 text-left text-sm font-semibold text-gray-700">
            <tr>
              <th class="px-4 py-2">Subject</th>
              <th class="px-4 py-2">Assessment</th>
              <th class="px-4 py-2">Score</th>
              <th class="px-4 py-2">Weightage</th>
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
              <td class="px-4 py-2 italic text-gray-600">{{ grade.remarks }}</td>
              <td class="px-4 py-2">{{ grade.recorded_by }}</td>
              <td class="px-4 py-2 text-xs text-gray-500">{{ formatDate(grade.recorded_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="filteredGrades.length === 0" class="text-center text-gray-500 mt-6">
        😶 No grades found for selected filters.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from 'axios'
import html2pdf from 'html2pdf.js'

// Sample student info (replace with dynamic data later)
const student = ref({
  name: 'Aliya Developer',
  class: 'WD2025-A'
})

// Filter controls
const selectedTermId = ref(2)
const selectedSubject = ref('')
const termList = [
  { id: 1, name: 'Term 1' },
  { id: 2, name: 'Term 2' },
  { id: 3, name: 'Term 3' }
]
const currentTermName = computed(() => {
  const term = termList.find(t => t.id === Number(selectedTermId.value))
  return term ? term.name : 'All Terms'
})

// Grades state
const grades = ref([])
const fetchGrades = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await api.get('http://127.0.0.1:8000/api/grades', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    })
    grades.value = res.data.data
  } catch (err) {
    console.error('❌ Error fetching grades:', err)
  }
}

onMounted(fetchGrades)

// Computed filters
const subjectList = computed(() => {
  const filtered = selectedTermId.value
    ? grades.value.filter(g => g.term_id === Number(selectedTermId.value))
    : grades.value
  return [...new Set(filtered.map(g => g.subject))]
})

const filteredGrades = computed(() => {
  let list = grades.value
  if (selectedTermId.value) {
    list = list.filter(g => g.term_id === Number(selectedTermId.value))
  }
  if (selectedSubject.value) {
    list = list.filter(g => g.subject === selectedSubject.value)
  }
  return list
})

// Stats
const subjectStats = computed(() => {
  if (!selectedSubject.value) return null
  const subjectGrades = grades.value.filter(g =>
    g.subject === selectedSubject.value &&
    g.term_id === Number(selectedTermId.value)
  )
  if (!subjectGrades.length) return null

  const weightedTotal = subjectGrades.reduce(
    (sum, g) => sum + ((g.score_obtained / g.max_score) * g.weightage), 0
  )
  const weightedMax = subjectGrades.reduce((sum, g) => sum + g.weightage, 0)
  const weightedAverage = (weightedTotal / weightedMax) * 100

  const grade_letter = weightedAverage >= 90 ? 'A' :
                       weightedAverage >= 80 ? 'B' :
                       weightedAverage >= 70 ? 'C' :
                       weightedAverage >= 60 ? 'D' : 'F'

  return { count: subjectGrades.length, weightedTotal, weightedMax, weightedAverage, grade_letter }
})

const gpa = computed(() => {
  if (!filteredGrades.value.length) return 0
  const totalPoints = filteredGrades.value.reduce((sum, g) => {
    const map = { A: 4, B: 3, C: 2, D: 1, F: 0 }
    return sum + (map[g.grade_letter] || 0)
  }, 0)
  return totalPoints / filteredGrades.value.length
})

const bestSubject = computed(() => {
  const map = {}
  filteredGrades.value.forEach(g => {
    if (!map[g.subject]) map[g.subject] = []
    map[g.subject].push(g)
  })

  const sorted = Object.entries(map)
    .map(([subject, entries]) => {
      const avg = entries.reduce((sum, g) => sum + ((g.score_obtained / g.max_score) * 100), 0) / entries.length
      return { subject, avg }
    })
    .sort((a, b) => b.avg - a.avg)

  return sorted.length ? sorted[0].subject : null
})

const weakestSubject = computed(() => {
  const map = {}
  filteredGrades.value.forEach(g => {
    if (!map[g.subject]) map[g.subject] = []
    map[g.subject].push(g)
  })

  const sorted = Object.entries(map)
    .map(([subject, entries]) => {
      const avg = entries.reduce((sum, g) => sum + ((g.score_obtained / g.max_score) * 100), 0) / entries.length
      return { subject, avg }
    })
    .sort((a, b) => a.avg - b.avg)

  return sorted.length ? sorted[0].subject : null
})

// Utils
const formatDate = str => new Date(str).toLocaleDateString()
const gradeColor = letter => ({
  A: 'bg-green-100 text-green-800',
  B: 'bg-blue-100 text-blue-800',
  C: 'bg-yellow-100 text-yellow-800',
  D: 'bg-orange-100 text-orange-800',
  F: 'bg-red-100 text-red-800',
})[letter] || 'bg-gray-100 text-gray-700'

const exportToPDF = () => {
  html2pdf().from(document.getElementById('grade-export-content')).set({
    margin: 0.5,
    filename: 'grades.pdf',
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
  }).save()
}
</script>

<!-- Optional: SummaryCard.vue component -->
<script>
export default {
  props: ['emoji', 'title', 'value', 'color'],
  template: `
    <div :class="'bg-' + color + '-50 rounded-xl p-5 shadow text-center border border-' + color + '-200'">
      <div class="text-4xl mb-2">{{ emoji }}</div>
      <p class="text-gray-500 font-semibold text-sm">{{ title }}</p>
      <p class="text-2xl font-bold text-gray-800">{{ value }}</p>
    </div>
  `
}
</script>
