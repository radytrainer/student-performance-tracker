<template>
  <div v-if="hasPermission('student.view_own_reports')" class="p-6">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">My Reports</h1>
      <p class="text-gray-600 mt-1">Access your academic reports and progress summaries</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-6">
      <!-- Report Generation -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Generate New Report</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
            <select
              v-model="reportConfig.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="academic_summary">Academic Summary</option>
              <option value="grade_report">Grade Report</option>
              <option value="attendance_report">Attendance Report</option>
              <option value="progress_report">Progress Report</option>
              <option value="transcript">Transcript</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Time Period</label>
            <select
              v-model="reportConfig.period"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="current_quarter">Current Quarter</option>
              <option value="current_semester">Current Semester</option>
              <option value="academic_year">Academic Year</option>
              <option value="all_time">All Time</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
            <select
              v-model="reportConfig.format"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="pdf">PDF</option>
              <option value="excel">Excel</option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="generateReport"
              :disabled="generating"
              class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              <i v-if="generating" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-download mr-2"></i>
              {{ generating ? 'Generating...' : 'Generate' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-blue-600">{{ studentStats.gpa }}</div>
              <div class="text-sm text-gray-600">Current GPA</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-check text-green-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-green-600">{{ studentStats.attendance }}%</div>
              <div class="text-sm text-gray-600">Attendance Rate</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-trophy text-purple-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-purple-600">{{ studentStats.rank }}</div>
              <div class="text-sm text-gray-600">Class Rank</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-star text-yellow-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-yellow-600">{{ studentStats.credits }}</div>
              <div class="text-sm text-gray-600">Credits Earned</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Available Reports -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Available Reports</h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="report in availableReports" :key="report.id" 
                 class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                 @click="requestReport(report)">
              <div class="flex items-center space-x-3 mb-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                     :style="{ backgroundColor: report.color + '20' }">
                  <i :class="report.icon" :style="{ color: report.color }"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900">{{ report.name }}</h3>
                  <p class="text-sm text-gray-500">{{ report.description }}</p>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">{{ report.updateFrequency }}</span>
                <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                  Generate
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Reports -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Recent Reports</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Generated</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="report in recentReports" :key="report.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                      <i class="fas fa-file-alt text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900">{{ report.name }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getReportTypeClass(report.type)">
                    {{ formatReportType(report.type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatPeriod(report.period) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(report.generatedAt) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getStatusClass(report.status)">
                    {{ formatStatus(report.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    v-if="report.status === 'completed'"
                    @click="downloadReport(report)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    Download
                  </button>
                  <button
                    @click="viewReport(report)"
                    class="text-green-600 hover:text-green-900"
                  >
                    View
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="recentReports.length === 0" class="text-center py-12">
          <i class="fas fa-file-alt text-gray-400 text-4xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No reports generated yet</h3>
          <p class="text-gray-500">Generate your first report using the options above.</p>
        </div>
      </div>

      <!-- Academic Progress Summary -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Academic Progress Summary</h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- GPA Trend -->
            <div>
              <h3 class="text-sm font-medium text-gray-700 mb-3">GPA Trend</h3>
              <div class="space-y-3">
                <div v-for="semester in gpaHistory" :key="semester.name" 
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <span class="text-sm text-gray-600">{{ semester.name }}</span>
                  <span class="text-sm font-semibold" :class="getGPAClass(semester.gpa)">
                    {{ semester.gpa }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Achievement Highlights -->
            <div>
              <h3 class="text-sm font-medium text-gray-700 mb-3">Achievement Highlights</h3>
              <div class="space-y-3">
                <div v-for="achievement in achievements" :key="achievement.id" 
                     class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                  <div class="flex-shrink-0 w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-trophy text-yellow-600"></i>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ achievement.title }}</div>
                    <div class="text-xs text-gray-500">{{ achievement.description }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Unauthorized Access -->
  <div v-else class="p-6">
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <i class="fas fa-exclamation-triangle text-red-400"></i>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Access Denied</h3>
          <p class="text-sm text-red-700 mt-1">You don't have permission to view reports.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuth } from '@/composables/useAuth'

const { hasPermission } = useAuth()

// State
const loading = ref(true)
const generating = ref(false)

const reportConfig = reactive({
  type: 'academic_summary',
  period: 'current_quarter',
  format: 'pdf'
})

// Mock data
const studentStats = ref({
  gpa: '3.8',
  attendance: '92',
  rank: '15/120',
  credits: '24'
})

const availableReports = ref([
  {
    id: 1,
    name: 'Academic Summary',
    description: 'Overall academic performance overview',
    icon: 'fas fa-chart-bar',
    color: '#3B82F6',
    updateFrequency: 'Updated weekly'
  },
  {
    id: 2,
    name: 'Grade Report',
    description: 'Detailed grades by subject and assignment',
    icon: 'fas fa-clipboard-list',
    color: '#10B981',
    updateFrequency: 'Updated daily'
  },
  {
    id: 3,
    name: 'Attendance Report',
    description: 'Attendance records and patterns',
    icon: 'fas fa-calendar-check',
    color: '#F59E0B',
    updateFrequency: 'Updated daily'
  },
  {
    id: 4,
    name: 'Progress Report',
    description: 'Academic progress and trends',
    icon: 'fas fa-chart-line',
    color: '#8B5CF6',
    updateFrequency: 'Updated monthly'
  },
  {
    id: 5,
    name: 'Transcript',
    description: 'Official academic transcript',
    icon: 'fas fa-scroll',
    color: '#EF4444',
    updateFrequency: 'Updated at term end'
  },
  {
    id: 6,
    name: 'Parent Report',
    description: 'Summary for parent/guardian review',
    icon: 'fas fa-users',
    color: '#06B6D4',
    updateFrequency: 'Updated weekly'
  }
])

const recentReports = ref([
  {
    id: 1,
    name: 'Academic Summary - Q1 2024',
    type: 'academic_summary',
    period: 'q1_2024',
    generatedAt: new Date('2024-01-15T10:30:00'),
    status: 'completed'
  },
  {
    id: 2,
    name: 'Grade Report - Current Quarter',
    type: 'grade_report',
    period: 'current_quarter',
    generatedAt: new Date('2024-01-14T14:22:00'),
    status: 'completed'
  },
  {
    id: 3,
    name: 'Attendance Report - December',
    type: 'attendance_report',
    period: 'december_2023',
    generatedAt: new Date('2024-01-10T09:15:00'),
    status: 'processing'
  }
])

const gpaHistory = ref([
  { name: 'Current Quarter', gpa: '3.8' },
  { name: 'Quarter 1', gpa: '3.7' },
  { name: 'Quarter 2', gpa: '3.6' },
  { name: 'Quarter 3', gpa: '3.9' }
])

const achievements = ref([
  {
    id: 1,
    title: 'Honor Roll',
    description: 'Made honor roll for Q1 2024'
  },
  {
    id: 2,
    title: 'Perfect Attendance',
    description: 'Perfect attendance in December'
  },
  {
    id: 3,
    title: 'Math Achievement',
    description: 'Top 10% in Mathematics'
  }
])

// Methods
const generateReport = async () => {
  try {
    generating.value = true
    
    console.log('Generating report:', reportConfig)
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    alert('Report generation started! You will be notified when it\'s ready.')
  } catch (error) {
    console.error('Error generating report:', error)
    alert('Failed to generate report. Please try again.')
  } finally {
    generating.value = false
  }
}

const requestReport = (report) => {
  reportConfig.type = report.id === 1 ? 'academic_summary' : 
                     report.id === 2 ? 'grade_report' :
                     report.id === 3 ? 'attendance_report' :
                     report.id === 4 ? 'progress_report' :
                     'transcript'
  generateReport()
}

const downloadReport = (report) => {
  console.log('Downloading report:', report)
  alert(`Downloading ${report.name}...`)
}

const viewReport = (report) => {
  console.log('Viewing report:', report)
}

const getReportTypeClass = (type) => {
  const classes = {
    academic_summary: 'bg-blue-100 text-blue-800',
    grade_report: 'bg-green-100 text-green-800',
    attendance_report: 'bg-yellow-100 text-yellow-800',
    progress_report: 'bg-purple-100 text-purple-800',
    transcript: 'bg-red-100 text-red-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getStatusClass = (status) => {
  const classes = {
    completed: 'bg-green-100 text-green-800',
    processing: 'bg-yellow-100 text-yellow-800',
    failed: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getGPAClass = (gpa) => {
  const numGpa = parseFloat(gpa)
  if (numGpa >= 3.7) return 'text-green-600'
  if (numGpa >= 3.0) return 'text-blue-600'
  if (numGpa >= 2.5) return 'text-yellow-600'
  return 'text-red-600'
}

const formatReportType = (type) => {
  return type.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

const formatPeriod = (period) => {
  return period.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

onMounted(async () => {
  try {
    loading.value = true
    // Simulate loading delay
    await new Promise(resolve => setTimeout(resolve, 1000))
  } catch (error) {
    console.error('Error loading reports:', error)
  } finally {
    loading.value = false
  }
})
</script>
