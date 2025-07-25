<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
      <h3 class="text-xl font-bold text-gray-900">Attendance Record</h3>
    </div>

    <div v-if="!loading && attendance.length > 0" class="p-6">
      <div class="space-y-4">
        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl border border-green-100">
          <span class="text-sm font-medium text-gray-700">This Month</span>
          <span class="text-xl font-bold text-green-600">{{ attendancePercentage }}%</span>
        </div>
        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-red-50 to-pink-50 rounded-2xl border border-red-100">
          <span class="text-sm font-medium text-gray-700">Absences</span>
          <span class="text-xl font-bold text-red-600">{{ summary.absent }}</span>
        </div>
        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-2xl border border-yellow-100">
          <span class="text-sm font-medium text-gray-700">Late Check-ins</span>
          <span class="text-xl font-bold text-yellow-600">{{ summary.late }}</span>
        </div>
        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-100">
          <span class="text-sm font-medium text-gray-700">Weekly Average</span>
          <span class="text-xl font-bold text-blue-600">{{ weeklyAverage }}%</span>
        </div>
      </div>
    </div>

    <div v-if="loading" class="p-6 text-gray-500">Loading...</div>
    <div v-if="error" class="p-6 text-red-500">{{ error }}</div>
    <div v-if="!loading && attendance.length === 0 && !error" class="p-6 text-gray-500">No attendance data available.</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

// Configure API client
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Add authorization header
apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Data
const attendance = ref([])
const loading = ref(true)
const error = ref(null)
const filters = ref({
  status: '',
  date: ''
})

// Fetch attendance data
const fetchAttendance = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await apiClient.get('/student/my-attendance')
    attendance.value = response.data.data || [] // Ensure it's an array, fallback to empty array
    console.log('Fetched attendance data:', attendance.value)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load attendance data'
    console.error('Error fetching attendance:', err)
  } finally {
    loading.value = false
  }
}

// Lifecycle hooks
onMounted(() => {
  fetchAttendance()
  // Auto-update every minute
  const interval = setInterval(() => {
    console.log('Auto-updating percentages...')
  }, 60000) // 60000 ms = 1 minute
  // Store interval ID to clear it later
  window.intervalId = interval
})

onUnmounted(() => {
  // Clean up interval on component unmount
  clearInterval(window.intervalId)
})

// Computed properties
const summary = computed(() => {
  const stats = { present: 0, absent: 0, late: 0, total: 0 }
  if (attendance.value && attendance.value.length > 0) {
    attendance.value.forEach(item => {
      if (item.status) {
        stats[item.status] = (stats[item.status] || 0) + 1
        stats.total++
      }
    })
  }
  console.log('Computed summary:', stats)
  return stats
})

const attendancePercentage = computed(() => {
  const now = new Date('2025-07-24T15:41:00+07:00') // Current date and time
  const currentMonth = now.getMonth() + 1 // 1-12, July is 7
  const currentYear = now.getFullYear() // 2025
  const monthlyRecords = attendance.value.filter(item => {
    const itemDate = new Date(item.date)
    return itemDate.getMonth() + 1 === currentMonth && itemDate.getFullYear() === currentYear
  })
  const monthlyTotal = monthlyRecords.length
  const monthlyPresent = monthlyRecords.filter(item => item.status === 'present').length
  return monthlyTotal ? Math.round((monthlyPresent / monthlyTotal) * 100) : 0
})

const weeklyAverage = computed(() => {
  const now = new Date('2025-07-24T15:41:00+07:00') // Current date and time
  const oneWeekAgo = new Date(now.setDate(now.getDate() - 7))
  const weeklyRecords = attendance.value.filter(item => new Date(item.date) >= oneWeekAgo)
  const weeklyTotal = weeklyRecords.length
  const weeklyPresent = weeklyRecords.filter(item => item.status === 'present').length
  return weeklyTotal ? Math.round((weeklyPresent / weeklyTotal) * 100) : 0
})
</script>