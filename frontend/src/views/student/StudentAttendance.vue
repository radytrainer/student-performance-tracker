<template>
  <div class="p-6 font-sans">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">My Attendance</h1>
    
    <!-- Attendance Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
      <!-- Present Card -->
      <div class="bg-white rounded-md shadow-sm p-5 border border-gray-100">
        <div class="space-y-3">
          <div class="flex items-center space-x-3">
            <div class="p-2 rounded-md bg-green-50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-gray-500 font-medium">Present</h3>
          </div>
          <div class="border-t border-gray-100 pt-2">
            <p class="text-3xl font-semibold text-gray-800">{{ summary.present }}</p>
            <p class="text-sm text-gray-500">{{ presentPercentage }}% of total</p>
          </div>
        </div>
      </div>
      
      <!-- Absent Card -->
      <div class="bg-white rounded-md shadow-sm p-5 border border-gray-100">
        <div class="space-y-3">
          <div class="flex items-center space-x-3">
            <div class="p-2 rounded-md bg-red-50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" />
              </svg>
            </div>
            <h3 class="text-gray-500 font-medium">Absent</h3>
          </div>
          <div class="border-t border-gray-100 pt-2">
            <p class="text-3xl font-semibold text-gray-800">{{ summary.absent }}</p>
            <p class="text-sm text-gray-500">{{ absentPercentage }}% of total</p>
          </div>
        </div>
      </div>
      
      <!-- Late Card -->
      <div class="bg-white rounded-md shadow-sm p-5 border border-gray-100">
        <div class="space-y-3">
          <div class="flex items-center space-x-3">
            <div class="p-2 rounded-md bg-orange-50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-gray-500 font-medium">Late</h3>
          </div>
          <div class="border-t border-gray-100 pt-2">
            <p class="text-3xl font-semibold text-gray-800">{{ summary.late }}</p>
            <p class="text-sm text-gray-500">{{ latePercentage }}% of total</p>
          </div>
        </div>
      </div>
      
      <!-- Overall Attendance Card -->
      <div class="bg-white rounded-md shadow-sm p-5 border border-gray-100">
        <div class="space-y-3">
          <div class="flex items-center space-x-3">
            <div class="p-2 rounded-md bg-blue-50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h3 class="text-gray-500 font-medium">Attendance Rate</h3>
          </div>
          <div class="border-t border-gray-100 pt-2">
            <p class="text-3xl font-semibold text-gray-800">{{ attendancePercentage }}%</p>
            <p class="text-sm text-gray-500">Based on {{ summary.total }} records</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Attendance Records Table -->
    <div class="bg-white rounded-md shadow-sm border border-gray-100 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded At</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr 
            v-for="(record, index) in attendance" 
            :key="record.id" 
            :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
          >
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
              {{ formatDate(record.date) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <span 
                class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="{
                  'bg-green-100 text-green-800': record.status === 'present',
                  'bg-red-100 text-red-800': record.status === 'absent',
                  'bg-orange-100 text-orange-800': record.status === 'late'
                }"
              >
                {{ record.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
              {{ formatDateTime(record.recorded_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
              <button 
                class="text-blue-600 hover:text-blue-800 hover:underline transition-colors"
                @click="viewDetails(record)"
              >
                View
              </button>
            </td>
          </tr>
          <tr v-if="attendance.length === 0">
            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No attendance records found.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import axios from 'axios'

// Reactive reference to hold attendance data
const attendance = ref([])

// Fetch attendance data on component mount
const fetchAttendance = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/student/my-attendance', {
      headers: {
        Authorization: 'Bearer 4|m96F64f4Mvf4qPFVW1M1Q55MlvcfbSsfq8TYpBbB3ec52548',
        Accept: 'application/json',
      },
    })
    attendance.value = response.data.data
  } catch (error) {
    console.error('Error fetching attendance:', error)
  }
}

// Calculate summary statistics
const summary = computed(() => {
  const counts = {
    present: 0,
    absent: 0,
    late: 0,
    total: attendance.value.length
  }
  
  attendance.value.forEach(record => {
    if (record.status === 'present') counts.present++
    else if (record.status === 'absent') counts.absent++
    else if (record.status === 'late') counts.late++
  })
  
  return counts
})

// Calculate percentages
const presentPercentage = computed(() => {
  return summary.value.total > 0 
    ? Math.round((summary.value.present / summary.value.total) * 100) 
    : 0
})

const absentPercentage = computed(() => {
  return summary.value.total > 0 
    ? Math.round((summary.value.absent / summary.value.total) * 100) 
    : 0
})

const latePercentage = computed(() => {
  return summary.value.total > 0 
    ? Math.round((summary.value.late / summary.value.total) * 100) 
    : 0
})

const attendancePercentage = computed(() => {
  return summary.value.total > 0 
    ? Math.round(((summary.value.present + summary.value.late) / summary.value.total) * 100) 
    : 0
})

// Format date string (e.g., 2025-07-22 → July 22, 2025)
const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })
}

// Format datetime string (e.g., 2025-07-22T11:11:01Z → July 22, 2025, 11:11 AM)
const formatDateTime = (dateTimeStr) => {
  const date = new Date(dateTimeStr)
  return date.toLocaleString()
}

// View details action
const viewDetails = (record) => {
  // Implement your view details logic here
  console.log('Viewing details for:', record)
}

onMounted(() => {
  fetchAttendance()
})
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');

body {
  font-family: 'Inter', sans-serif;
}
</style>