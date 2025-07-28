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

    <!-- Grades Table (REPLACED WITH CLEAN VERSION) -->
    <div class="p-4">
      <h2 class="text-xl font-semibold mb-3">Teacher Grades</h2>

      <!-- Loading / Error States -->
      <div v-if="loading" class="text-blue-600">Loading grades...</div>
      <div v-else-if="error" class="text-red-500">{{ error }}</div>
      <div v-else-if="grades.length === 0" class="text-gray-500">No grades available.</div>

      <!-- Grades Table -->
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
          </tr>
        </thead>
        <tbody>
          <tr v-for="grade in grades" :key="grade.id">
            <td class="p-2 border">
              {{ grade.student?.user?.first_name }} {{ grade.student?.user?.last_name }}
            </td>
            <td class="p-2 border">
              {{ grade.class_subject?.class?.class_name || '-' }}
            </td>
            <td class="p-2 border">
              {{ grade.class_subject?.subject?.subject_name || '-' }}
            </td>
            <td class="p-2 border">{{ grade.term?.term_name }}</td>
            <td class="p-2 border">{{ grade.assessment_type }}</td>
            <td class="p-2 border">{{ grade.score_obtained }}</td>
            <td class="p-2 border">{{ grade.grade_letter }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Axios client setup
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

// Data refs
const classes = ref([])
const subjects = ref([])
const terms = ref([])
const students = ref([])
const grades = ref([])
const loading = ref(false)
const error = ref(null)

// Filters
const filters = ref({
  class_id: '',
  subject_id: '',
  term_id: '',
  assessment_type: ''
})

const assessmentTypes = ref(['Quiz', 'Midterm', 'Final', 'Project'])

// Fetch all required data
const fetchAll = async () => {
  loading.value = true
  try {
    const [classRes, subRes, termRes, stuRes] = await Promise.all([
      apiClient.get('/classes'),
      apiClient.get('/subjects'),
      apiClient.get('/terms'),
      apiClient.get('/students')
    ])
    classes.value = classRes.data.data
    subjects.value = subRes.data.data
    terms.value = termRes.data.data
    students.value = stuRes.data.data

    await fetchGrades()
  } catch (err) {
    error.value = 'Failed to load data'
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Fetch grades
const fetchGrades = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await apiClient.get('/grades', {
      params: {
        class_id: filters.value.class_id,
        subject_id: filters.value.subject_id,
        term_id: filters.value.term_id,
        assessment_type: filters.value.assessment_type
      }
    })
    grades.value = res.data.data?.data || res.data.data // handle paginated or flat
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load grades'
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchAll)
</script>
