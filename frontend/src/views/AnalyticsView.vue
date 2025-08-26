<template>
  <div class="p-4 md:p-6 space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Advanced Analytics</h1>

    <!-- Controls -->
    <div class="bg-white p-4 rounded-lg border grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
        <select v-model="selectedClassId" class="px-3 py-2 border rounded w-full">
          <option value="">Select a class</option>
          <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.class_name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
        <select v-model="selectedTermId" class="px-3 py-2 border rounded w-full">
          <option value="">Current term</option>
          <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.term_name }}</option>
        </select>
      </div>
      <div class="flex sm:justify-end">
        <button @click="loadAnalytics" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 w-full sm:w-auto">Run</button>
      </div>
    </div>

    <!-- Correlation -->
    <div class="bg-white p-4 rounded-lg border">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-gray-800">Attendance vs Grade Correlation</h2>
        <div v-if="corr !== null" class="text-sm text-gray-600">r = {{ corr }}</div>
      </div>
      <div class="h-64 md:h-72">
        <canvas ref="corrCanvas" height="280"></canvas>
      </div>
      <div v-if="points.length===0" class="text-sm text-gray-500 mt-2">No data for the selected class/term.</div>
    </div>

    <!-- Heatmap -->
    <div class="bg-white p-4 rounded-lg border">
      <h2 class="text-lg font-semibold text-gray-800 mb-3">Attendance vs Grade Heatmap</h2>
      <div class="grid grid-cols-10 gap-[2px] bg-gray-200 p-[2px] overflow-x-auto">
        <div v-for="(row, i) in buckets" :key="i" class="grid grid-cols-10 gap-[2px]">
          <div v-for="(cell, j) in row" :key="j"
               class="w-7 h-7 text-[10px] flex items-center justify-center"
               :style="cellStyle(cell)">{{ cell }}</div>
        </div>
      </div>
      <div class="text-xs text-gray-500 mt-2">Rows: attendance 0%→100%; Columns: grade 0%→100%</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import axios from '@/api/axiosConfig'
import { Chart, ScatterController, PointElement, LinearScale, Tooltip, Legend } from 'chart.js'
import { adminAPI } from '@/api/admin'
import { reportsAPI } from '@/api/reports'
import { useAuth } from '@/composables/useAuth'

Chart.register(ScatterController, PointElement, LinearScale, Tooltip, Legend)

const { isTeacher } = useAuth()

// Selections
const classes = ref([])
const terms = ref([])
const selectedClassId = ref('')
const selectedTermId = ref('')

// Data
const points = ref([])
const corr = ref(null)
const buckets = ref(Array.from({length:10}, ()=>Array.from({length:10}, ()=>0)))

// Chart
const corrCanvas = ref(null)
let corrChart = null

const cellStyle = (val) => {
  const max = Math.max(1, Math.max(...buckets.value.flat()))
  const t = Math.min(1, val / max)
  return { backgroundColor: `rgba(59,130,246,${0.2 + 0.6*t})`, color: '#111' }
}

const renderCorr = async () => {
  await nextTick()
  const ctx = corrCanvas.value?.getContext('2d')
  if (!ctx) return
  if (corrChart) corrChart.destroy()
  corrChart = new Chart(ctx, {
    type: 'scatter',
    data: {
      datasets: [{
        label: 'Students',
        data: points.value.map(p => ({ x: p.attendance, y: p.grade })),
        backgroundColor: '#3B82F6'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { min: 0, max: 100, title: { display: true, text: 'Attendance %' } },
        y: { min: 0, max: 100, title: { display: true, text: 'Grade %' } }
      }
    }
  })
}

const loadSelections = async () => {
  try {
    if (isTeacher.value) {
      const res = await adminAPI.getTeacherClasses()
      classes.value = res.data?.data || []
    } else {
      const res = await adminAPI.getClasses({ per_page: 100 })
      const payload = res.data?.data
      classes.value = payload?.data || payload || []
    }
  } catch {
    classes.value = []
  }
  try {
    const res = await reportsAPI.getTerms()
    terms.value = res.data || []
  } catch {
    terms.value = []
  }
}

const loadAnalytics = async () => {
  if (!selectedClassId.value) { points.value = []; corr.value = null; buckets.value = Array.from({length:10}, ()=>Array.from({length:10}, ()=>0)); await renderCorr(); return }
  const params = { class_id: Number(selectedClassId.value) }
  if (selectedTermId.value) params.term_id = Number(selectedTermId.value)
  const c = await axios.get('/analytics/correlation', { params })
  points.value = c.data?.data?.points || []
  corr.value = c.data?.data?.correlation ?? null
  await renderCorr()

  const h = await axios.get('/analytics/heatmap', { params })
  buckets.value = h.data?.data?.buckets || buckets.value
}

onMounted(async () => {
  await loadSelections()
  if (isTeacher.value && classes.value.length) selectedClassId.value = classes.value[0]?.id
  if (selectedClassId.value) await loadAnalytics()
})
</script>