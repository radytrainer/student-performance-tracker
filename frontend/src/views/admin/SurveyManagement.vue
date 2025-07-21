<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Survey System Administration</h1>
            <p class="mt-2 text-gray-600">Manage surveys, assignments, and system-wide settings</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="refreshData"
              :disabled="isLoading"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
            >
              <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              {{ isLoading ? 'Refreshing...' : 'Refresh' }}
            </button>
            <button
              @click="showBulkActionsModal = true"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
              </svg>
              Bulk Actions
            </button>
          </div>
        </div>
      </div>

      <!-- System Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5">
              <p class="text-sm font-medium text-gray-500">Total Surveys</p>
              <p class="text-lg font-semibold text-gray-900">{{ systemStats.total_surveys || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5">
              <p class="text-sm font-medium text-gray-500">Active Students</p>
              <p class="text-lg font-semibold text-gray-900">{{ systemStats.active_students || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5">
              <p class="text-sm font-medium text-gray-500">Total Responses</p>
              <p class="text-lg font-semibold text-gray-900">{{ systemStats.total_responses || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5">
              <p class="text-sm font-medium text-gray-500">Response Rate</p>
              <p class="text-lg font-semibold text-gray-900">{{ systemStats.response_rate || 0 }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Student Management</h3>
          <div class="space-y-3">
            <button
              @click="showStudentEnrollmentModal = true"
              class="w-full text-left px-4 py-2 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors"
            >
              📚 Manage Student Enrollments
            </button>
            <button
              @click="showStudentImportModal = true"
              class="w-full text-left px-4 py-2 bg-green-50 text-green-700 rounded-md hover:bg-green-100 transition-colors"
            >
              📥 Import Students from CSV
            </button>
            <button
              @click="exportStudentData"
              class="w-full text-left px-4 py-2 bg-purple-50 text-purple-700 rounded-md hover:bg-purple-100 transition-colors"
            >
              📤 Export Student Data
            </button>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Survey Operations</h3>
          <div class="space-y-3">
            <button
              @click="showSurveyCleanupModal = true"
              class="w-full text-left px-4 py-2 bg-orange-50 text-orange-700 rounded-md hover:bg-orange-100 transition-colors"
            >
              🧹 Cleanup Old Surveys
            </button>
            <button
              @click="showDataExportModal = true"
              class="w-full text-left px-4 py-2 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors"
            >
              📊 Export Survey Data
            </button>
            <button
              @click="runSystemMaintenance"
              :disabled="isMaintenanceRunning"
              class="w-full text-left px-4 py-2 bg-red-50 text-red-700 rounded-md hover:bg-red-100 transition-colors disabled:opacity-50"
            >
              ⚙️ {{ isMaintenanceRunning ? 'Running...' : 'Run Maintenance' }}
            </button>
          </div>
        </div>

        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">System Settings</h3>
          <div class="space-y-3">
            <button
              @click="showNotificationSettingsModal = true"
              class="w-full text-left px-4 py-2 bg-indigo-50 text-indigo-700 rounded-md hover:bg-indigo-100 transition-colors"
            >
              🔔 Notification Settings
            </button>
            <button
              @click="showSystemConfigModal = true"
              class="w-full text-left px-4 py-2 bg-gray-50 text-gray-700 rounded-md hover:bg-gray-100 transition-colors"
            >
              ⚙️ System Configuration
            </button>
            <button
              @click="viewAuditLogs"
              class="w-full text-left px-4 py-2 bg-yellow-50 text-yellow-700 rounded-md hover:bg-yellow-100 transition-colors"
            >
              📋 View Audit Logs
            </button>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
        </div>
        <div class="p-6">
          <div v-if="recentActivity.length === 0" class="text-center py-8 text-gray-500">
            No recent activity to display
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="activity in recentActivity"
              :key="activity.id"
              class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-sm font-medium text-blue-600">{{ activity.type.charAt(0).toUpperCase() }}</span>
                </div>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">{{ activity.description }}</p>
                <p class="text-xs text-gray-500">{{ formatDateTime(activity.created_at) }}</p>
              </div>
              <div class="flex-shrink-0">
                <span class="text-xs text-gray-400">{{ activity.user_name }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- System Health Status -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">System Health</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="flex items-center">
              <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
              <span class="text-sm text-gray-700">Database: Healthy</span>
            </div>
            <div class="flex items-center">
              <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
              <span class="text-sm text-gray-700">Email Service: Active</span>
            </div>
            <div class="flex items-center">
              <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
              <span class="text-sm text-gray-700">Queue: {{ queueStatus }}</span>
            </div>
            <div class="flex items-center">
              <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
              <span class="text-sm text-gray-700">Storage: {{ storageStatus }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals would go here -->
    <!-- Add modals for each action as needed -->
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axiosConfig'

// State
const isLoading = ref(false)
const isMaintenanceRunning = ref(false)
const systemStats = ref({})
const recentActivity = ref([])
const queueStatus = ref('Processing')
const storageStatus = ref('Available')

// Modal states
const showBulkActionsModal = ref(false)
const showStudentEnrollmentModal = ref(false)
const showStudentImportModal = ref(false)
const showSurveyCleanupModal = ref(false)
const showDataExportModal = ref(false)
const showNotificationSettingsModal = ref(false)
const showSystemConfigModal = ref(false)

// Methods
const refreshData = async () => {
  isLoading.value = true
  await Promise.all([
    loadSystemStats(),
    loadRecentActivity(),
    checkSystemHealth()
  ])
  isLoading.value = false
}

const loadSystemStats = async () => {
  try {
    // Mock data - replace with actual API call
    systemStats.value = {
      total_surveys: 15,
      active_students: 127,
      total_responses: 342,
      response_rate: 78
    }
  } catch (error) {
    console.error('Error loading system stats:', error)
  }
}

const loadRecentActivity = async () => {
  try {
    // Mock data - replace with actual API call
    recentActivity.value = [
      {
        id: 1,
        type: 'survey',
        description: 'New survey "Monthly Feedback" created',
        user_name: 'John Teacher',
        created_at: new Date()
      },
      {
        id: 2,
        type: 'response',
        description: 'Student completed "Weekly Assessment"',
        user_name: 'System',
        created_at: new Date(Date.now() - 30 * 60 * 1000)
      }
    ]
  } catch (error) {
    console.error('Error loading recent activity:', error)
  }
}

const checkSystemHealth = async () => {
  try {
    // Mock health checks - replace with actual API calls
    queueStatus.value = 'Processing'
    storageStatus.value = 'Available'
  } catch (error) {
    console.error('Error checking system health:', error)
  }
}

const runSystemMaintenance = async () => {
  isMaintenanceRunning.value = true
  try {
    // Simulate maintenance tasks
    await new Promise(resolve => setTimeout(resolve, 3000))
    alert('System maintenance completed successfully!')
  } catch (error) {
    console.error('Error running maintenance:', error)
    alert('Maintenance failed. Please check the logs.')
  } finally {
    isMaintenanceRunning.value = false
  }
}

const exportStudentData = () => {
  // Implement student data export
  console.log('Exporting student data...')
}

const viewAuditLogs = () => {
  // Navigate to audit logs view
  console.log('Viewing audit logs...')
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString()
}

// Lifecycle
onMounted(() => {
  refreshData()
})
</script>
