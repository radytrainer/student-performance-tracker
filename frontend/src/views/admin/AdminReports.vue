<template>
  <div v-if="hasPermission('admin.view_reports')" class="p-6">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">System Reports</h1>
      <p class="text-gray-600 mt-1">Generate and view comprehensive system reports</p>
    </div>

    <!-- Report Categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div
        v-for="category in reportCategories"
        :key="category.id"
        @click="selectCategory(category)"
        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 cursor-pointer hover:shadow-md transition-shadow"
        :class="{ 'ring-2 ring-blue-500': selectedCategory?.id === category.id }"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                 :style="{ backgroundColor: category.color + '20' }">
              <i :class="category.icon" :style="{ color: category.color }" class="text-xl"></i>
            </div>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-900">{{ category.name }}</h3>
            <p class="text-sm text-gray-500">{{ category.description }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Generation Controls -->
    <div v-if="selectedCategory" class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">{{ selectedCategory.name }} Reports</h2>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
            <select
              v-model="reportConfig.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option v-for="report in selectedCategory.reports" :key="report.id" :value="report.id">
                {{ report.name }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <select
              v-model="reportConfig.dateRange"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="current_month">Current Month</option>
              <option value="last_month">Last Month</option>
              <option value="current_semester">Current Semester</option>
              <option value="academic_year">Academic Year</option>
              <option value="custom">Custom Range</option>
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
              <option value="csv">CSV</option>
            </select>
          </div>
        </div>

        <!-- Custom Date Range -->
        <div v-if="reportConfig.dateRange === 'custom'" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input
              v-model="reportConfig.startDate"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input
              v-model="reportConfig.endDate"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>

        <!-- Additional Filters -->
        <div v-if="selectedCategory.filters" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div v-for="filter in selectedCategory.filters" :key="filter.key">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ filter.label }}</label>
            <select
              v-model="reportConfig.filters[filter.key]"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All {{ filter.label }}</option>
              <option v-for="option in filter.options" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
        </div>

        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-600">
            <i class="fas fa-info-circle mr-1"></i>
            Reports may take a few minutes to generate for large datasets
          </div>
          <button
            @click="generateReport"
            :disabled="generating"
            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
          >
            <i v-if="generating" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-download mr-2"></i>
            {{ generating ? 'Generating...' : 'Generate Report' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Recent Reports -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Recent Reports</h2>
      </div>
      <div class="p-6">
        <div v-if="recentReports.length === 0" class="text-center py-8">
          <i class="fas fa-file-alt text-gray-400 text-4xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No reports generated yet</h3>
          <p class="text-gray-500">Select a report category above to get started.</p>
        </div>
        
        <div v-else class="space-y-4">
          <div
            v-for="report in recentReports"
            :key="report.id"
            class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                <i class="fas fa-file-alt text-blue-600"></i>
              </div>
              <div class="ml-4">
                <h4 class="text-sm font-medium text-gray-900">{{ report.name }}</h4>
                <p class="text-sm text-gray-500">
                  Generated {{ formatDate(report.createdAt) }} • {{ report.format.toUpperCase() }}
                </p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getStatusClass(report.status)">
                {{ report.status }}
              </span>
              <button
                v-if="report.status === 'completed'"
                @click="downloadReport(report)"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
              >
                Download
              </button>
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
const selectedCategory = ref(null)
const generating = ref(false)
const recentReports = ref([])

const reportConfig = reactive({
  type: '',
  dateRange: 'current_month',
  format: 'pdf',
  startDate: '',
  endDate: '',
  filters: {}
})

// Report Categories
const reportCategories = ref([
  {
    id: 'academic',
    name: 'Academic Reports',
    description: 'Student grades and performance',
    icon: 'fas fa-graduation-cap',
    color: '#3B82F6',
    reports: [
      { id: 'grade_summary', name: 'Grade Summary' },
      { id: 'performance_trends', name: 'Performance Trends' },
      { id: 'failing_students', name: 'At-Risk Students' }
    ],
    filters: [
      {
        key: 'grade',
        label: 'Grade Level',
        options: [
          { value: '9', label: 'Grade 9' },
          { value: '10', label: 'Grade 10' },
          { value: '11', label: 'Grade 11' },
          { value: '12', label: 'Grade 12' }
        ]
      }
    ]
  },
  {
    id: 'attendance',
    name: 'Attendance Reports',
    description: 'Student attendance tracking',
    icon: 'fas fa-calendar-check',
    color: '#10B981',
    reports: [
      { id: 'attendance_summary', name: 'Attendance Summary' },
      { id: 'chronic_absence', name: 'Chronic Absence Report' },
      { id: 'daily_attendance', name: 'Daily Attendance' }
    ]
  },
  {
    id: 'enrollment',
    name: 'Enrollment Reports',
    description: 'Student enrollment data',
    icon: 'fas fa-users',
    color: '#8B5CF6',
    reports: [
      { id: 'enrollment_summary', name: 'Enrollment Summary' },
      { id: 'class_rosters', name: 'Class Rosters' },
      { id: 'demographic_breakdown', name: 'Demographic Breakdown' }
    ]
  },
  {
    id: 'financial',
    name: 'Financial Reports',
    description: 'Budget and financial data',
    icon: 'fas fa-dollar-sign',
    color: '#F59E0B',
    reports: [
      { id: 'budget_summary', name: 'Budget Summary' },
      { id: 'fee_collection', name: 'Fee Collection' },
      { id: 'expense_analysis', name: 'Expense Analysis' }
    ]
  }
])

// Mock recent reports
const mockReports = [
  {
    id: 1,
    name: 'Grade Summary - December 2024',
    createdAt: new Date('2024-01-15T10:30:00'),
    format: 'pdf',
    status: 'completed'
  },
  {
    id: 2,
    name: 'Attendance Report - Current Semester',
    createdAt: new Date('2024-01-14T14:22:00'),
    format: 'excel',
    status: 'completed'
  },
  {
    id: 3,
    name: 'Enrollment Analysis',
    createdAt: new Date('2024-01-14T09:15:00'),
    format: 'pdf',
    status: 'processing'
  }
]

// Methods
const selectCategory = (category) => {
  selectedCategory.value = category
  reportConfig.type = category.reports[0]?.id || ''
  reportConfig.filters = {}
}

const generateReport = async () => {
  try {
    generating.value = true
    
    // TODO: Implement actual report generation
    console.log('Generating report with config:', reportConfig)
    
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

const downloadReport = (report) => {
  // TODO: Implement report download
  console.log('Downloading report:', report)
  alert(`Downloading ${report.name}...`)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const getStatusClass = (status) => {
  const classes = {
    completed: 'bg-green-100 text-green-800',
    processing: 'bg-yellow-100 text-yellow-800',
    failed: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  recentReports.value = mockReports
})
</script>
