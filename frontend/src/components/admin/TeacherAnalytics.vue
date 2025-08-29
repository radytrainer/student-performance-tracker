<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-bold text-gray-900">Teacher Analytics</h2>
      <div class="flex items-center space-x-3">
        <select
          v-model="selectedPeriod"
          @change="loadAnalytics"
          class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="month">This Month</option>
          <option value="term">Current Term</option>
          <option value="year">This Year</option>
        </select>
        <button
          @click="refreshAnalytics"
          :disabled="loading"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 flex items-center"
        >
          <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
          <i v-else class="fas fa-sync-alt mr-2"></i>
          Refresh
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex">
        <i class="fas fa-exclamation-circle text-red-400 mt-0.5"></i>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error loading analytics</h3>
          <p class="text-sm text-red-700 mt-1">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Analytics Content -->
    <div v-else class="space-y-8">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-blue-600"></i>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500">Total Teachers</p>
              <div class="flex items-baseline">
                <p class="text-2xl font-semibold text-gray-900">{{ analytics.total_teachers || 0 }}</p>
                <p class="ml-2 text-sm text-green-600" v-if="analytics.teacher_growth > 0">
                  +{{ analytics.teacher_growth }}%
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-check text-green-600"></i>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500">Active Teachers</p>
              <div class="flex items-baseline">
                <p class="text-2xl font-semibold text-gray-900">{{ analytics.active_teachers || 0 }}</p>
                <p class="ml-2 text-sm text-gray-500">
                  {{ calculatePercentage(analytics.active_teachers, analytics.total_teachers) }}%
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-book-open text-purple-600"></i>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500">Avg Classes/Teacher</p>
              <div class="flex items-baseline">
                <p class="text-2xl font-semibold text-gray-900">{{ analytics.avg_classes_per_teacher || '0' }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-graduation-cap text-orange-600"></i>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500">Avg Students/Teacher</p>
              <div class="flex items-baseline">
                <p class="text-2xl font-semibold text-gray-900">{{ analytics.avg_students_per_teacher || '0' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Department Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Teachers by Department</h3>
          <div class="space-y-3">
            <div
              v-for="dept in analytics.departments_distribution"
              :key="dept.department"
              class="flex items-center justify-between"
            >
              <div class="flex items-center">
                <div 
                  class="w-4 h-4 rounded-full mr-3"
                  :style="{ backgroundColor: getDepartmentColor(dept.department) }"
                ></div>
                <span class="text-sm font-medium text-gray-900">{{ dept.department || 'Unassigned' }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">{{ dept.count }}</span>
                <div class="w-24 bg-gray-200 rounded-full h-2">
                  <div
                    class="h-2 rounded-full"
                    :style="{
                      width: `${(dept.count / analytics.total_teachers) * 100}%`,
                      backgroundColor: getDepartmentColor(dept.department)
                    }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Class Load Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Class Load Distribution</h3>
          <div class="space-y-3">
            <div
              v-for="load in analytics.class_load_distribution"
              :key="load.range"
              class="flex items-center justify-between"
            >
              <span class="text-sm font-medium text-gray-900">{{ load.range }} classes</span>
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">{{ load.count }} teachers</span>
                <div class="w-24 bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-500 h-2 rounded-full"
                    :style="{ width: `${(load.count / analytics.total_teachers) * 100}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Subject Coverage -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Subject Coverage</h3>
          <div class="space-y-3">
            <div
              v-for="subject in analytics.subject_coverage"
              :key="subject.subject"
              class="flex items-center justify-between"
            >
              <span class="text-sm font-medium text-gray-900">{{ subject.subject }}</span>
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">{{ subject.teachers_count }} teachers</span>
                <span 
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getCoverageStatusClass(subject.coverage_status)"
                >
                  {{ subject.coverage_status }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Performance Metrics -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Performance Metrics</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between py-2 border-b">
              <span class="text-sm text-gray-600">Average Experience</span>
              <span class="text-sm font-semibold text-gray-900">{{ analytics.avg_experience || '0' }} years</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b">
              <span class="text-sm text-gray-600">Teacher Retention Rate</span>
              <span class="text-sm font-semibold text-green-600">{{ analytics.retention_rate || '0' }}%</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b">
              <span class="text-sm text-gray-600">New Teachers (This Term)</span>
              <span class="text-sm font-semibold text-gray-900">{{ analytics.new_teachers || '0' }}</span>
            </div>
            <div class="flex items-center justify-between py-2">
              <span class="text-sm text-gray-600">Avg Student Rating</span>
              <div class="flex items-center">
                <span class="text-sm font-semibold text-gray-900 mr-1">{{ analytics.avg_rating || 'N/A' }}</span>
                <div class="flex text-yellow-400">
                  <i v-for="i in 5" :key="i" :class="i <= Math.floor(analytics.avg_rating || 0) ? 'fas fa-star' : 'far fa-star'" class="text-xs"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Recent Teacher Activities</h3>
        </div>
        <div class="p-6">
          <div class="flow-root">
            <ul class="-mb-8">
              <li v-for="(activity, index) in analytics.recent_activities" :key="activity.id">
                <div class="relative pb-8" :class="{ 'pb-0': index === analytics.recent_activities.length - 1 }">
                  <span
                    v-if="index !== analytics.recent_activities.length - 1"
                    class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                    aria-hidden="true"
                  ></span>
                  <div class="relative flex space-x-3">
                    <div>
                      <span
                        class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                        :class="getActivityIconClass(activity.type)"
                      >
                        <i :class="getActivityIcon(activity.type)" class="text-xs"></i>
                      </span>
                    </div>
                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                      <div>
                        <p class="text-sm text-gray-500">
                          {{ activity.description }}
                        </p>
                      </div>
                      <div class="text-right text-sm whitespace-nowrap text-gray-500">
                        <time>{{ formatDate(activity.created_at) }}</time>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminAPI } from '@/api/admin'

// State
const loading = ref(false)
const error = ref(null)
const selectedPeriod = ref('month')
const analytics = ref({
  total_teachers: 0,
  active_teachers: 0,
  teacher_growth: 0,
  avg_classes_per_teacher: 0,
  avg_students_per_teacher: 0,
  avg_experience: 0,
  retention_rate: 0,
  new_teachers: 0,
  avg_rating: 0,
  departments_distribution: [],
  class_load_distribution: [],
  subject_coverage: [],
  recent_activities: []
})

// Department colors for consistent visualization
const departmentColors = [
  '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
  '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'
]

// Methods
const loadAnalytics = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await adminAPI.getTeacherStats()
    analytics.value = response.data || {}
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load analytics'
    console.error('Error loading teacher analytics:', err)
  } finally {
    loading.value = false
  }
}

const refreshAnalytics = () => {
  loadAnalytics()
}

const calculatePercentage = (value, total) => {
  if (!total || total === 0) return 0
  return Math.round((value / total) * 100)
}

const getDepartmentColor = (department) => {
  const index = analytics.value.departments_distribution?.findIndex(d => d.department === department) || 0
  return departmentColors[index % departmentColors.length]
}

const getCoverageStatusClass = (status) => {
  switch (status?.toLowerCase()) {
    case 'adequate':
      return 'bg-green-100 text-green-800'
    case 'understaffed':
      return 'bg-red-100 text-red-800'
    case 'overstaffed':
      return 'bg-yellow-100 text-yellow-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getActivityIconClass = (type) => {
  switch (type) {
    case 'teacher_created':
      return 'bg-green-500'
    case 'teacher_updated':
      return 'bg-blue-500'
    case 'teacher_deleted':
      return 'bg-red-500'
    case 'assignment_changed':
      return 'bg-purple-500'
    default:
      return 'bg-gray-500'
  }
}

const getActivityIcon = (type) => {
  switch (type) {
    case 'teacher_created':
      return 'fas fa-user-plus'
    case 'teacher_updated':
      return 'fas fa-user-edit'
    case 'teacher_deleted':
      return 'fas fa-user-minus'
    case 'assignment_changed':
      return 'fas fa-exchange-alt'
    default:
      return 'fas fa-info-circle'
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffInMinutes = Math.floor((now - date) / (1000 * 60))
  
  if (diffInMinutes < 60) {
    return `${diffInMinutes}m ago`
  } else if (diffInMinutes < 1440) {
    return `${Math.floor(diffInMinutes / 60)}h ago`
  } else {
    return date.toLocaleDateString()
  }
}

// Initialize
onMounted(() => {
  loadAnalytics()
})
</script>
