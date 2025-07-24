<template>
  <div class="max-w-7xl mx-auto p-2">
    <h1 class="text-2xl font-bold mb-6">🎓 My Grade</h1>

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
                  <div class="bg-green-500 h-2 rounded" :style="{ width: ((grade.score_obtained / grade.max_score) * 100) + '%' }" />
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import html2pdf from 'html2pdf.js'
import gradeService from '@/api/studentGrade'

const student = ref({ name: 'Aliya Developer', class: 'WD2025-A' })
const grades = ref([])

const termList = [
  { id: 1, name: 'Term 1' },
  { id: 2, name: 'Term 2' },
  { id: 3, name: 'Term 3' },
]

const selectedTermId = ref('')
const selectedSubject = ref('')

const fetchGrades = async () => {
  try {
    const { data } = await gradeService.getAllGrades()
    grades.value = data.map(g => ({
      id: g.id,
      subject: g.class_subject_id, // ideally map ID to subject name here
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

onMounted(fetchGrades)

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

const gpa = computed(() => {
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
