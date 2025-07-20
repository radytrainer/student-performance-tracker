<template>
  <div class="max-w-6xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">🎓 My Grade</h1>

    <!-- Student Info -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-6 border">
      <p class="text-gray-700"><span class="font-semibold">Name:</span> {{ student.name }}</p>
      <p class="text-gray-700"><span class="font-semibold">Class:</span> {{ student.class }}</p>
      <p class="text-gray-700"><span class="font-semibold">Term:</span> {{ currentTermName }}</p>
    </div>

    <!-- Subject Filter -->
    <div class="mb-6">
      <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">📚 Filter by Subject</label>
      <select
        id="subject"
        v-model="selectedSubject"
        class="w-full md:w-1/3 border border-gray-300 rounded-lg shadow-sm p-2 text-gray-700 focus:outline-none focus:ring focus:border-blue-300"
      >
        <option value="">All Subjects</option>
        <option v-for="subject in subjectList" :key="subject" :value="subject">
          {{ subject }}
        </option>
      </select>
    </div>

    <!-- Term Filter -->
    <div class="mb-6">
      <label for="term" class="block text-sm font-medium text-gray-700 mb-1">📅 Filter by Term</label>
      <select
        id="term"
        v-model="selectedTermId"
        class="w-full md:w-1/3 border border-gray-300 rounded-lg shadow-sm p-2 text-gray-700 focus:outline-none focus:ring focus:border-blue-300"
      >
        <option value="">All Terms</option>
        <option v-for="term in termList" :key="term.id" :value="term.id">{{ term.name }}</option>
      </select>
    </div>

    <!-- Subject Summary if filtered -->
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
          <tr
            v-for="grade in filteredGrades"
            :key="grade.id"
            class="border-t hover:bg-gray-50 text-sm"
          >
            <td class="px-4 py-2">{{ grade.subject }}</td>
            <td class="px-4 py-2">{{ grade.assessment_type }}</td>
            <td class="px-4 py-2">
              {{ grade.score_obtained }} / {{ grade.max_score }}
              <div class="w-full bg-gray-200 h-2 mt-1 rounded">
                <div
                  class="bg-green-500 h-2 rounded"
                  :style="{ width: ((grade.score_obtained / grade.max_score) * 100) + '%' }"
                />
              </div>
            </td>
            <td class="px-4 py-2">{{ grade.weightage }}%</td>
            <td class="px-4 py-2">
              <span
                class="text-xs font-medium px-2 py-1 rounded-full"
                :class="gradeColor(grade.grade_letter)"
              >
                {{ grade.grade_letter }}
              </span>
            </td>
            <td class="px-4 py-2 italic text-gray-600">
              {{ grade.remarks || '—' }}
            </td>
            <td class="px-4 py-2">{{ grade.recorded_by || 'Unknown' }}</td>
            <td class="px-4 py-2 text-xs text-gray-500">
              {{ formatDate(grade.recorded_at) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- No Data -->
    <div v-if="filteredGrades.length === 0" class="text-center text-gray-500 mt-6">
      😶 No grades found for selected filters.
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

// Dummy student info
const student = ref({
  name: 'Aliya Developer',
  class: 'WD2025-A',
})

// Dummy term list
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

// Subject filter state
const selectedSubject = ref('')

// Dummy grades data (simulate DB columns)
const grades = ref([
  {
    id: 1,
    subject: 'Math',
    class_subject_id: 101,
    term_id: 2,
    assessment_type: 'Midterm',
    max_score: 100,
    score_obtained: 78,
    weightage: 30, // percentage
    grade_letter: 'B',
    remarks: 'Good effort',
    recorded_by: 'Teacher A',
    recorded_at: '2025-07-10',
  },
  {
    id: 2,
    subject: 'Math',
    class_subject_id: 101,
    term_id: 2,
    assessment_type: 'Quiz',
    max_score: 20,
    score_obtained: 18,
    weightage: 10,
    grade_letter: 'A',
    remarks: 'Quick learner',
    recorded_by: 'Teacher A',
    recorded_at: '2025-07-15',
  },
  {
    id: 3,
    subject: 'Science',
    class_subject_id: 102,
    term_id: 2,
    assessment_type: 'Project',
    max_score: 50,
    score_obtained: 40,
    weightage: 40,
    grade_letter: 'B',
    remarks: '',
    recorded_by: 'Teacher B',
    recorded_at: '2025-07-12',
  },
  {
    id: 4,
    subject: 'English',
    class_subject_id: 103,
    term_id: 2,
    assessment_type: 'Final',
    max_score: 100,
    score_obtained: 85,
    weightage: 20,
    grade_letter: 'A',
    remarks: 'Great improvement',
    recorded_by: 'Teacher C',
    recorded_at: '2025-07-20',
  },
])

// Unique subject list for dropdown (from filtered terms)
const subjectList = computed(() => {
  const filteredByTerm = selectedTermId.value
    ? grades.value.filter(g => g.term_id === Number(selectedTermId.value))
    : grades.value

  return [...new Set(filteredByTerm.map(g => g.subject))]
})

// Filtered grades by subject and term
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

// Calculate weighted average and grade letter for filtered subject & term
const subjectStats = computed(() => {
  if (!selectedSubject.value) return null

  const subjectGrades = grades.value.filter(
    g => g.subject === selectedSubject.value && g.term_id === Number(selectedTermId.value)
  )
  if (subjectGrades.length === 0) return null

  // Weighted total score = sum of (score_obtained / max_score) * weightage
  const weightedTotal = subjectGrades.reduce((sum, g) => {
    const percentageScore = g.score_obtained / g.max_score
    return sum + (percentageScore * g.weightage)
  }, 0)

  // Total weightage for this subject (should ideally be 100 or close)
  const weightedMax = subjectGrades.reduce((sum, g) => sum + g.weightage, 0)

  // Weighted average percentage score
  const weightedAverage = (weightedTotal / weightedMax) * 100

  // Assign grade letter based on weighted average
  const grade_letter =
    weightedAverage >= 90 ? 'A' :
    weightedAverage >= 80 ? 'B' :
    weightedAverage >= 70 ? 'C' :
    weightedAverage >= 60 ? 'D' : 'F'

  return {
    count: subjectGrades.length,
    weightedTotal,
    weightedMax,
    weightedAverage,
    grade_letter,
  }
})

// Grade badge color mapping
const gradeColor = (letter) => {
  switch (letter) {
    case 'A': return 'bg-green-100 text-green-800'
    case 'B': return 'bg-blue-100 text-blue-800'
    case 'C': return 'bg-yellow-100 text-yellow-800'
    case 'D': return 'bg-orange-100 text-orange-800'
    case 'F': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-700'
  }
}

// Format date
const formatDate = (dateStr) => {
  const d = new Date(dateStr)
  return d.toLocaleDateString()
}
</script>
