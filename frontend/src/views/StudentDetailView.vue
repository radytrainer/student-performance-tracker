<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-4 sm:py-8">
    <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">
      <!-- Back Button -->
      <div class="mb-6">
        <button
          @click="goBack"
          class="inline-flex items-center px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors duration-200"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Back to Students
        </button>
      </div>

      <!-- Student Detail Card -->
      <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl overflow-hidden">
        <!-- Student Header -->
        <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 px-4 sm:px-8 py-8 sm:py-12">
          <div class="flex flex-col items-center space-y-4 sm:flex-row sm:space-y-0 sm:space-x-8">
            <!-- Profile Image -->
            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full overflow-hidden bg-white shadow-2xl border-4 border-white/20 backdrop-blur-sm">
              <img
                v-if="student.profileImage"
                :src="student.profileImage"
                :alt="student.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                <span class="text-2xl sm:text-3xl font-bold text-gray-600">
                  {{ getInitials(student.name) }}
                </span>
              </div>
            </div>
            
            <!-- Student Info -->
            <div class="text-center sm:text-left text-white flex-1">
              <h1 class="text-xl sm:text-3xl font-bold mb-2">{{ student.name }}</h1>
              <p class="text-blue-100 text-base sm:text-lg mb-3">{{ student.email }}</p>
              <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
                <span class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 rounded-full text-xs sm:text-sm font-medium bg-white/20 backdrop-blur-sm text-white border border-white/30">
                  <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  {{ student.role }}
                </span>
                <span :class="getStatusBadgeClass(student.status)" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 rounded-full text-xs sm:text-sm font-medium backdrop-blur-sm border">
                  <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full mr-1 sm:mr-2" :class="getStatusDotClass(student.status)"></span>
                  {{ student.status }}
                </span>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="absolute top-4 right-4 flex gap-2">
            <button
              @click="editStudent"
              class="p-2 bg-white/20 backdrop-blur-sm text-white rounded-lg hover:bg-white/30 transition-all duration-200"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Student Details Content -->
        <div class="p-4 sm:p-8">
          <!-- Comparison Chart -->
          <div class="mb-8 bg-white rounded-xl shadow border border-gray-200 p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Performance vs Class Average</h3>
            <div class="h-64">
              <BarChart v-if="comparisonChartData" :chartData="comparisonChartData" :chartOptions="comparisonChartOptions" />
              <div v-else class="text-sm text-gray-500">No comparison data available</div>
            </div>
          </div>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            <!-- Personal Information -->
            <div class="space-y-6">
              <div class="flex items-center space-x-3 pb-4 border-b border-gray-200">
                <div class="p-2 bg-blue-100 rounded-lg">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-900">Personal Information</h2>
              </div>

              <div class="space-y-4">
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                  </svg>
                  <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium text-gray-900">{{ student.email }}</p>
                  </div>
                </div>

                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                  <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-medium text-gray-900">{{ student.phone || 'Not provided' }}</p>
                  </div>
                </div>

                <div class="flex items-start p-4 bg-gray-50 rounded-lg">
                  <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  <div>
                    <p class="text-sm text-gray-500">Bio</p>
                    <p class="font-medium text-gray-900">{{ student.bio || 'No bio provided' }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Account Information -->
            <div class="space-y-6">
              <div class="flex items-center space-x-3 pb-4 border-b border-gray-200">
                <div class="p-2 bg-purple-100 rounded-lg">
                  <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                  </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-900">Account Information</h2>
              </div>

              <div class="space-y-4">
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <div>
                    <p class="text-sm text-gray-500">Role</p>
                    <p class="font-medium text-gray-900">{{ student.role }}</p>
                  </div>
                </div>

                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <div>
                    <p class="text-sm text-gray-500">Member Since</p>
                    <p class="font-medium text-gray-900">{{ formatDate(student.createdAt) }}</p>
                  </div>
                </div>

                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <div>
                    <p class="text-sm text-gray-500">Last Login</p>
                    <p class="font-medium text-gray-900">{{ formatDate(student.lastLogin) }}</p>
                  </div>
                </div>

                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                  <div class="w-5 h-5 mr-3 flex items-center justify-center">
                    <span :class="getStatusDotClass(student.status)" class="w-3 h-3 rounded-full"></span>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="font-medium text-gray-900">{{ student.status }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-3 mt-8 pt-6 border-t border-gray-200">
            <button
              @click="editStudent"
              class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Edit Student
            </button>
            <button
              @click="deleteStudent"
              class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
              Delete Student
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import BarChart from '@/components/charts/BarChart.vue'
import { teacherAPI } from '@/api/teacher'
import { useRoute } from 'vue-router'

// Sample student data
const student = ref({
  id: 1,
  name: 'Em Sophy',
  email: 'sophy.em@student.passerellesnumeriques.org',
  phone: '+855 12 345 678',
  bio: 'Computer Science student passionate about web development and technology. Currently learning Vue.js and modern web frameworks.',
  role: 'Student',
  status: 'Active',
  createdAt: '2023-01-15',
  lastLogin: '2024-01-20',
  profileImage: ''
})

// Comparison chart state
const comparisonChartData = ref(null)
const comparisonChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: true } },
  scales: { x: { grid: { display: false } }, y: { beginAtZero: true, max: 100 } }
}

// Methods
const getInitials = (name) => {
  if (!name) return ''
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'Active':
      return 'bg-green-500/20 text-green-100 border-green-400/30'
    case 'Inactive':
      return 'bg-red-500/20 text-red-100 border-red-400/30'
    default:
      return 'bg-gray-500/20 text-gray-100 border-gray-400/30'
  }
}

const getStatusDotClass = (status) => {
  switch (status) {
    case 'Active':
      return 'bg-green-400 animate-pulse'
    case 'Inactive':
      return 'bg-red-400'
    default:
      return 'bg-gray-400'
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  try {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  } catch (error) {
    return 'N/A'
  }
}

const goBack = () => {
  // Navigate back to students list
  console.log('Going back to students list')
  // In a real app: router.push('/students')
}

const editStudent = () => {
  // Navigate to edit mode
  console.log('Editing student:', student.value)
  // In a real app: router.push(`/students/${student.value.id}/edit`)
}

const deleteStudent = () => {
  // Show confirmation dialog and delete
  if (confirm('Are you sure you want to delete this student?')) {
    console.log('Deleting student:', student.value)
    // In a real app: call API to delete student
  }
}

onMounted(async () => {
  // Load student data from API based on route params (placeholder)
  const route = useRoute()
  const sid = Number(route.params.id || student.value.id)
  try {
    const resp = await teacherAPI.getStudentComparison(sid)
    const rows = resp?.data?.data || []
    if (rows.length) {
      comparisonChartData.value = {
        labels: rows.map(r => r.subject_name),
        datasets: [
          { label: 'Student', data: rows.map(r => r.student_avg), backgroundColor: '#3B82F6' },
          { label: 'Class', data: rows.map(r => r.class_avg), backgroundColor: '#10B981' }
        ]
      }
    } else {
      comparisonChartData.value = null
    }
  } catch (e) {
    comparisonChartData.value = null
  }
})
</script>
