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

    <!-- Toast Notification -->
    <div 
      v-if="successMessage" 
      class="fixed top-24 right-6 z-50 bg-white border border-gray-200 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 ease-in-out"
      :class="successMessage ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'"
    >
      <div class="flex items-center p-4">
        <div class="flex-shrink-0">
          <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
            <i class="fas fa-check text-green-600 text-sm"></i>
          </div>
        </div>
        <div class="ml-3 flex-1">
          <p class="text-sm text-gray-800">{{ successMessage }}</p>
        </div>
        <div class="ml-3 flex-shrink-0">
          <button 
            @click="successMessage = ''" 
            class="text-gray-400 hover:text-gray-600 focus:outline-none transition ease-in-out duration-150"
          >
            <i class="fas fa-times text-xs"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Error Toast Notification -->
    <div 
      v-if="error" 
      class="fixed right-6 z-50 bg-white border border-gray-200 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 ease-in-out"
      :class="error ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'"
      :style="{ top: successMessage ? '100px' : '96px' }"
    >
      <div class="flex items-center p-4">
        <div class="flex-shrink-0">
          <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
            <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
          </div>
        </div>
        <div class="ml-3 flex-1">
          <p class="text-sm text-gray-800">{{ error }}</p>
        </div>
        <div class="ml-3 flex-shrink-0">
          <button 
            @click="error = ''" 
            class="text-gray-400 hover:text-gray-600 focus:outline-none transition ease-in-out duration-150"
          >
            <i class="fas fa-times text-xs"></i>
          </button>
        </div>
      </div>
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
              <option value="pdf">PDF (Coming Soon)</option>
              <option value="excel">Excel (.xlsx)</option>
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

        <!-- Report Details Modal -->
    <ReportDetailsModal 
      :is-open="showReportModal"
      :report-id="selectedReportId"
      @close="closeReportModal"
    />

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

    <!-- Student vs Class Average -->
    <div>
    <h3 class="text-sm font-medium text-gray-700 mb-3">My Performance vs Class Average</h3>
    <div class="bg-gray-50 rounded-lg p-3">
    <BarChart v-if="comparisonChartData" :chartData="comparisonChartData" :chartOptions="comparisonChartOptions" />
    <div v-else class="text-sm text-gray-500">No comparison data available</div>
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
import { reportsAPI } from '@/api/reports'
import ReportDetailsModal from '@/components/student/ReportDetailsModal.vue'
import BarChart from '@/components/charts/BarChart.vue'

const { hasPermission } = useAuth()

 // State
const loading = ref(true)
const generating = ref(false)
const error = ref(null)
const successMessage = ref('')

// Modal state
const showReportModal = ref(false)
const selectedReportId = ref(null)

const reportConfig = reactive({
  type: 'academic_summary',
  period: 'current_quarter',
  format: 'excel'
})

// Data
const studentStats = ref({
  gpa: '0.0',
  attendance: '0',
  rank: '0/0',
  credits: '0'
})

const availableReports = ref([])
const recentReports = ref([])
const gpaHistory = ref([])
const achievements = ref([])

// Comparison chart state
const comparisonChartData = ref(null)
const comparisonChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: true } },
  scales: { x: { grid: { display: false } }, y: { beginAtZero: true, max: 100 } }
}

const loadComparison = async () => {
  try {
    const resp = await reportsAPI.getComparison()
    const rows = resp.data || []
    if (!rows.length) { comparisonChartData.value = null; return }
    comparisonChartData.value = {
      labels: rows.map(r => r.subject_name),
      datasets: [
        { label: 'Me', data: rows.map(r => r.student_avg), backgroundColor: '#3B82F6' },
        { label: 'Class', data: rows.map(r => r.class_avg), backgroundColor: '#10B981' }
      ]
    }
  } catch (e) {
    // Leave chart empty if error
    comparisonChartData.value = null
  }
}
 
 // Load dashboard data
 const loadDashboardData = async () => {
  try {
    loading.value = true
    error.value = null
    
    const data = await reportsAPI.getReportsDashboard()
    
    studentStats.value = data.student_stats
    availableReports.value = data.available_reports
    recentReports.value = data.recent_reports
    gpaHistory.value = data.gpa_history
    achievements.value = data.achievements
  } catch (err) {
    console.error('Error loading dashboard data:', err)
    showErrorMessage('Failed to load reports data. Please try again.')
  } finally {
    loading.value = false
  }
}

// Load only recent reports (for after generating a new report)
const loadRecentReports = async () => {
  try {
    const data = await reportsAPI.getReportsDashboard()
    recentReports.value = data.recent_reports
  } catch (err) {
    console.error('Error loading recent reports:', err)
  }
}

// Show success message
const showSuccessMessage = (message) => {
  successMessage.value = message
  setTimeout(() => {
    successMessage.value = ''
  }, 4000) // Clear after 4 seconds
}

// Show error message
const showErrorMessage = (message) => {
  error.value = message
  setTimeout(() => {
    error.value = ''
  }, 6000) // Clear after 6 seconds (errors might need more time to read)
}

// Methods
const generateReport = async () => {
  try {
    generating.value = true
    error.value = null
    
    const response = await reportsAPI.generateReport(reportConfig)
    
    if (reportConfig.format === 'pdf') {
      // Check if response is a blob (actual PDF) or JSON (temporary response)
      if (response.data instanceof Blob) {
        // Handle PDF download
        const blob = new Blob([response.data], { type: 'application/pdf' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = `${reportConfig.type}_${new Date().toISOString().split('T')[0]}.pdf`
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
        
        // Show success message without refreshing
        showSuccessMessage('PDF report downloaded successfully!')
      } else {
        // Handle JSON response (temporary until PDF generation is fully set up)
        showSuccessMessage('Report generated successfully!')
        console.log('Report data:', response.data.data)
      }
      
      // Only reload recent reports section, not the entire dashboard
      await loadRecentReports()
    } else if (reportConfig.format === 'excel') {
      // Handle Excel download
      if (response.data instanceof Blob) {
        const blob = new Blob([response.data], { 
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
        })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = `${reportConfig.type}_${new Date().toISOString().split('T')[0]}.xlsx`
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
        
        showSuccessMessage('Excel report downloaded successfully!')
      } else {
        showSuccessMessage('Excel report ready for download!')
        console.log('Report data:', response.data)
      }
      
      // Only reload recent reports section, not the entire dashboard
      await loadRecentReports()
    } else {
      // Handle other formats
      showSuccessMessage('Report generated successfully!')
    }
  } catch (err) {
    console.error('Error generating report:', err)
    showErrorMessage('Failed to generate report. Please try again.')
  } finally {
    generating.value = false
  }
}

const requestReport = (report) => {
  const typeMap = {
    1: 'academic_summary',
    2: 'grade_report', 
    3: 'attendance_report',
    4: 'progress_report',
    5: 'transcript'
  }
  
  reportConfig.type = typeMap[report.id] || 'academic_summary'
  generateReport()
}

const downloadReport = async (report) => {
  try {
    const response = await reportsAPI.downloadReport(report.id)
    
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `${report.name}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
    
    showSuccessMessage('Report downloaded successfully!')
  } catch (err) {
    console.error('Error downloading report:', err)
    showErrorMessage('Failed to download report. Please try again.')
  }
}

const viewReport = (report) => {
  if (report.status === 'completed') {
    selectedReportId.value = report.id
    showReportModal.value = true
  } else {
    showErrorMessage('Report is still being processed. Please try again later.')
  }
}

const closeReportModal = () => {
  showReportModal.value = false
  selectedReportId.value = null
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
  await loadDashboardData()
  await loadComparison()
})
</script>
