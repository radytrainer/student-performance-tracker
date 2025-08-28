<template>
  <div v-if="hasPermission('teacher.create_reports')" class="p-6">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Teaching Reports</h1>
      <p class="text-gray-600 mt-1">Generate and manage reports for your classes and students</p>
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
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Generate Class Report</h2>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
            <select
              v-model="reportConfig.classId"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Select Class</option>
              <option v-for="classItem in teacherClasses" :key="classItem.id" :value="classItem.id">
                {{ classItem.subject_name }} - {{ classItem.class_name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
            <select
              v-model="reportConfig.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="class_performance">Class Performance</option>
              <option value="student_grades">Student Grades</option>
              <option value="attendance_summary">Attendance Summary</option>
              <option value="progress_tracking">Progress Tracking</option>
              <option value="grade_distribution">Grade Distribution</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Time Period</label>
            <select
              v-model="reportConfig.period"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="current_term">Current Term</option>
              <option value="current_quarter">Current Quarter</option>
              <option value="current_semester">Current Semester</option>
              <option value="academic_year">Academic Year</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
            <select
              v-model="reportConfig.format"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="pdf">PDF (.pdf)</option>
              <option value="excel">Excel (.xlsx)</option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="generateReport"
              :disabled="generating || !reportConfig.classId"
              class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i v-if="generating" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-download mr-2"></i>
              {{ generating ? 'Generating...' : 'Generate' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Teaching Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-blue-600">{{ teachingStats.totalStudents }}</div>
              <div class="text-sm text-gray-600">Total Students</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-green-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-green-600">{{ teachingStats.averageGPA }}</div>
              <div class="text-sm text-gray-600">Class Average GPA</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-check text-purple-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-purple-600">{{ teachingStats.attendanceRate }}%</div>
              <div class="text-sm text-gray-600">Attendance Rate</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-door-open text-orange-600 text-xl"></i>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-orange-600">{{ teachingStats.activeClasses }}</div>
              <div class="text-sm text-gray-600">Active Classes</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Available Report Templates -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Available Report Templates</h2>
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
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Recent Reports</h2>
            <button
              @click="loadRecentReports"
              :disabled="loadingReports"
              class="text-blue-600 hover:text-blue-800 text-sm font-medium disabled:opacity-50"
            >
              <i v-if="loadingReports" class="fas fa-spinner fa-spin mr-1"></i>
              <i v-else class="fas fa-refresh mr-1"></i>
              Refresh
            </button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
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
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ report.class_name }}
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
                  {{ formatDate(report.generated_at) }}
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
                    <i class="fas fa-download mr-1"></i>
                    Download
                  </button>
                  <button
                    @click="viewReport(report)"
                    class="text-green-600 hover:text-green-900"
                  >
                    <i class="fas fa-eye mr-1"></i>
                    View
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="recentReports.length === 0 && !loading" class="text-center py-12">
          <i class="fas fa-file-alt text-gray-400 text-4xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No reports generated yet</h3>
          <p class="text-gray-500">Generate your first class report using the options above.</p>
        </div>
      </div>

      <!-- Class Performance Overview -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Class Performance Overview</h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Grade Distribution -->
            <div>
              <h3 class="text-sm font-medium text-gray-700 mb-3">Grade Distribution Across Classes</h3>
              <div class="space-y-3">
                <div v-for="grade in gradeDistribution" :key="grade.grade" 
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex items-center">
                    <span class="text-sm font-medium text-gray-900 w-8">{{ grade.grade }}</span>
                    <div class="ml-3 flex-1 bg-gray-200 rounded-full h-2">
                      <div 
                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        :style="{ width: grade.percentage + '%' }"
                      ></div>
                    </div>
                  </div>
                  <span class="text-sm text-gray-600 ml-3">{{ grade.count }} ({{ grade.percentage }}%)</span>
                </div>
              </div>
            </div>

            <!-- Top Performing Classes -->
            <div>
              <h3 class="text-sm font-medium text-gray-700 mb-3">Top Performing Classes</h3>
              <div class="space-y-3">
                <div v-for="classItem in topClasses" :key="classItem.id" 
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                      <i class="fas fa-trophy text-green-600 text-sm"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ classItem.class_name }}</div>
                      <div class="text-xs text-gray-500">{{ classItem.subject_name }}</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-semibold text-green-600">{{ classItem.average_grade }}</div>
                    <div class="text-xs text-gray-500">Avg Grade</div>
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

  <!-- Report Details Modal -->
  <ReportDetailsModal 
    :is-open="showReportModal"
    :report-id="selectedReportId"
    @close="closeReportModal"
  />
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { teacherAPI } from '@/api/teacher'
import ReportDetailsModal from '@/components/student/ReportDetailsModal.vue'

const { hasPermission } = useAuth()

// State
const loading = ref(true)
const loadingReports = ref(false)
const generating = ref(false)
const error = ref(null)
const successMessage = ref('')

// Modal state
const showReportModal = ref(false)
const selectedReportId = ref(null)

const reportConfig = reactive({
  classId: '',
  type: 'class_performance',
  period: 'current_term',
  format: 'pdf'
})

// Data
const teachingStats = ref({
  totalStudents: 0,
  averageGPA: '0.0',
  attendanceRate: 0,
  activeClasses: 0
})

const teacherClasses = ref([])
const availableReports = ref([
  {
    id: 1,
    name: 'Class Performance Report',
    description: 'Comprehensive class performance analysis',
    icon: 'fas fa-chart-bar',
    color: '#3B82F6',
    updateFrequency: 'Weekly'
  },
  {
    id: 2,
    name: 'Student Grades Report',
    description: 'Individual student grades breakdown',
    icon: 'fas fa-list-check',
    color: '#10B981',
    updateFrequency: 'Daily'
  },
  {
    id: 3,
    name: 'Attendance Summary',
    description: 'Class attendance patterns and trends',
    icon: 'fas fa-calendar-check',
    color: '#8B5CF6',
    updateFrequency: 'Daily'
  },
  {
    id: 4,
    name: 'Progress Tracking',
    description: 'Student progress over time',
    icon: 'fas fa-line-chart',
    color: '#F59E0B',
    updateFrequency: 'Monthly'
  },
  {
    id: 5,
    name: 'Grade Distribution',
    description: 'Grade distribution analysis',
    icon: 'fas fa-chart-pie',
    color: '#EF4444',
    updateFrequency: 'Weekly'
  }
])

const recentReports = ref([])
const gradeDistribution = ref([])
const topClasses = ref([])

// Load teacher dashboard data
const loadDashboardData = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Load teacher classes using existing working endpoint
    const classesResponse = await teacherAPI.getClasses()
    teacherClasses.value = classesResponse.data.data || []

    // Calculate basic statistics from available data
    const totalStudents = teacherClasses.value.reduce((sum, cls) => sum + (cls.student_count || 0), 0)
    teachingStats.value = {
      totalStudents: totalStudents,
      averageGPA: '0.0', // Will be calculated when we have grades
      attendanceRate: 0, // Will be calculated when we have attendance data
      activeClasses: teacherClasses.value.length
    }

    // Load analytics data if available
    try {
      const analyticsResponse = await teacherAPI.getAnalytics()
      if (analyticsResponse.data) {
        // Update stats with analytics data if available
        teachingStats.value.averageGPA = analyticsResponse.data.average_gpa || '0.0'
        teachingStats.value.attendanceRate = Math.round(analyticsResponse.data.attendance_rate || 0)
      }
    } catch (analyticsErr) {
      console.log('Analytics data not available:', analyticsErr)
      // Continue without analytics - not critical
    }

    // Load recent reports - use mock data for now
    recentReports.value = []

    // Initialize grade distribution with mock data
    gradeDistribution.value = [
      { grade: 'A', count: 0, percentage: 0 },
      { grade: 'B', count: 0, percentage: 0 },
      { grade: 'C', count: 0, percentage: 0 },
      { grade: 'D', count: 0, percentage: 0 },
      { grade: 'F', count: 0, percentage: 0 }
    ]

    // Initialize top classes with actual classes data
    topClasses.value = teacherClasses.value.slice(0, 3).map(cls => ({
      id: cls.id,
      class_name: cls.class_name,
      subject_name: cls.subject_name,
      average_grade: 'N/A'
    }))

  } catch (err) {
    console.error('Error loading dashboard data:', err)
    showErrorMessage('Some data may be limited. Core functionality is available.')
    
    // Set basic defaults to prevent UI breaks
    teachingStats.value = {
      totalStudents: 0,
      averageGPA: 'N/A',
      attendanceRate: 0,
      activeClasses: 0
    }
    teacherClasses.value = []
  } finally {
    loading.value = false
  }
}

// Load only recent reports
const loadRecentReports = async () => {
  try {
    loadingReports.value = true
    // For now, use mock data until backend implements reports endpoint
    recentReports.value = []
  } catch (err) {
    console.error('Error loading recent reports:', err)
  } finally {
    loadingReports.value = false
  }
}

// Show success message
const showSuccessMessage = (message) => {
  successMessage.value = message
  setTimeout(() => {
    successMessage.value = ''
  }, 4000)
}

// Show error message
const showErrorMessage = (message) => {
  error.value = message
  setTimeout(() => {
    error.value = ''
  }, 6000)
}

// Methods
const generateReport = async () => {
  if (!reportConfig.classId) {
    showErrorMessage('Please select a class first.')
    return
  }

  try {
    generating.value = true
    error.value = null
    
    // For demo purposes - simulate report generation
    const className = teacherClasses.value.find(c => c.id == reportConfig.classId)?.class_name || 'class'
    
    // Simulate processing time
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // Create mock report content
    const reportContent = `${reportConfig.type.toUpperCase()} REPORT
Class: ${className}
Period: ${reportConfig.period}
Generated: ${new Date().toLocaleDateString()}

This is a sample report. Backend integration pending.`

    if (reportConfig.format === 'pdf') {
      // Create a simple text file for now (until PDF generation is implemented)
      const blob = new Blob([reportContent], { type: 'text/plain' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `${reportConfig.type}_${className}_${new Date().toISOString().split('T')[0]}.txt`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
      
      showSuccessMessage('Sample report downloaded! (Backend integration pending)')
    } else {
      showSuccessMessage('Report template ready! (Backend integration pending)')
    }
  } catch (err) {
    console.error('Error generating report:', err)
    const errorMessage = err.response?.data?.message || 'Failed to generate report. Please try again.'
    showErrorMessage(errorMessage)
  } finally {
    generating.value = false
  }
}

const requestReport = (report) => {
  if (!reportConfig.classId) {
    showErrorMessage('Please select a class first from the generate section above.')
    return
  }

  const typeMap = {
    1: 'class_performance',
    2: 'student_grades', 
    3: 'attendance_summary',
    4: 'progress_tracking',
    5: 'grade_distribution'
  }
  
  reportConfig.type = typeMap[report.id] || 'class_performance'
  generateReport()
}

const downloadReport = async (report) => {
  try {
    // Mock download for demo
    showSuccessMessage('Download feature will be available when backend is connected.')
  } catch (err) {
    console.error('Error downloading report:', err)
    showErrorMessage('Download feature is not yet implemented.')
  }
}

const viewReport = (report) => {
  showErrorMessage('View feature will be available when backend is connected.')
}

const closeReportModal = () => {
  showReportModal.value = false
  selectedReportId.value = null
}

// Utility functions
const getReportTypeClass = (type) => {
  const classes = {
    class_performance: 'bg-blue-100 text-blue-800',
    student_grades: 'bg-green-100 text-green-800',
    attendance_summary: 'bg-yellow-100 text-yellow-800',
    progress_tracking: 'bg-purple-100 text-purple-800',
    grade_distribution: 'bg-red-100 text-red-800'
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
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
