<template>
  <div class="p-6">
    <div v-if="!hasPermission('admin.manage_system')" class="text-center py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Access Denied</h2>
      <p class="text-gray-600">You don't have permission to manage system settings.</p>
    </div>
    <div v-else>
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">System Settings</h1>
      <p class="text-gray-600 mt-1">Configure system-wide settings and preferences</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Main Content -->
    <div v-else class="max-w-4xl mx-auto space-y-8">
      
      <!-- General Settings -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">General Settings</h2>
        </div>
        <div class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">School Name</label>
            <input
              v-model="settings.schoolName"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
            <select
              v-model="settings.academicYear"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="2023-2024">2023-2024</option>
              <option value="2024-2025">2024-2025</option>
              <option value="2025-2026">2025-2026</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Time Zone</label>
            <select
              v-model="settings.timeZone"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="UTC">UTC</option>
              <option value="America/New_York">Eastern Time</option>
              <option value="America/Chicago">Central Time</option>
              <option value="America/Denver">Mountain Time</option>
              <option value="America/Los_Angeles">Pacific Time</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Academic Settings -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Academic Settings</h2>
        </div>
        <div class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Grading Scale</label>
            <select
              v-model="settings.gradingScale"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="letter">Letter Grades (A-F)</option>
              <option value="percentage">Percentage (0-100%)</option>
              <option value="points">Points Based</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Passing Grade</label>
            <input
              v-model="settings.passingGrade"
              type="number"
              min="0"
              max="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Attendance Required (%)</label>
            <input
              v-model="settings.requiredAttendance"
              type="number"
              min="0"
              max="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <div class="flex items-center">
            <input
              v-model="settings.allowLateSubmissions"
              type="checkbox"
              id="lateSubmissions"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="lateSubmissions" class="ml-2 block text-sm text-gray-700">
              Allow late assignment submissions
            </label>
          </div>
        </div>
      </div>

      <!-- Notification Settings -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Notification Settings</h2>
        </div>
        <div class="p-6 space-y-6">
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <label class="text-sm font-medium text-gray-700">Email Notifications</label>
                <p class="text-sm text-gray-500">Send system notifications via email</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input v-model="settings.emailNotifications" type="checkbox" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>

            <div class="flex items-center justify-between">
              <div>
                <label class="text-sm font-medium text-gray-700">SMS Notifications</label>
                <p class="text-sm text-gray-500">Send urgent notifications via SMS</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input v-model="settings.smsNotifications" type="checkbox" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>

            <div class="flex items-center justify-between">
              <div>
                <label class="text-sm font-medium text-gray-700">Parent Notifications</label>
                <p class="text-sm text-gray-500">Automatically notify parents of student updates</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input v-model="settings.parentNotifications" type="checkbox" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Security Settings -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Security Settings</h2>
        </div>
        <div class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (minutes)</label>
            <input
              v-model="settings.sessionTimeout"
              type="number"
              min="5"
              max="480"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <div class="flex items-center">
            <input
              v-model="settings.requireTwoFactor"
              type="checkbox"
              id="twoFactor"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="twoFactor" class="ml-2 block text-sm text-gray-700">
              Require two-factor authentication for admin accounts
            </label>
          </div>

          <div class="flex items-center">
            <input
              v-model="settings.enforcePasswordPolicy"
              type="checkbox"
              id="passwordPolicy"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="passwordPolicy" class="ml-2 block text-sm text-gray-700">
              Enforce strong password policy
            </label>
          </div>
        </div>
      </div>

      <!-- Backup Settings -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Backup & Maintenance</h2>
        </div>
        <div class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Automatic Backup Frequency</label>
            <select
              v-model="settings.backupFrequency"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>

          <div class="space-y-3">
            <button
              @click="initiateBackup"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
              <i class="fas fa-download mr-2"></i>
              Create Manual Backup
            </button>
            
            <button
              @click="runMaintenance"
              class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors ml-3"
            >
              <i class="fas fa-tools mr-2"></i>
              Run System Maintenance
            </button>
          </div>
        </div>
      </div>

      <!-- Save Changes -->
      <div class="flex justify-end space-x-3 pt-6">
        <button
          @click="resetSettings"
          class="px-6 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Reset to Defaults
        </button>
        <button
          @click="saveSettings"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Save Changes
        </button>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { settingsAPI } from '@/api/settings'

const { hasPermission } = useAuth()

// State
const loading = ref(true)
const settings = ref({
  // Defaults (will be replaced by API on mount)
  schoolName: 'Riverside High School',
  academicYear: '2024-2025',
  timeZone: 'America/New_York',
  gradingScale: 'letter',
  passingGrade: 60,
  requiredAttendance: 75,
  allowLateSubmissions: true,
  emailNotifications: true,
  smsNotifications: false,
  parentNotifications: true,
  sessionTimeout: 120,
  requireTwoFactor: false,
  enforcePasswordPolicy: true,
  backupFrequency: 'daily'
})

// Methods
const saveSettings = async () => {
  try {
    const payload = { ...settings.value }
    await settingsAPI.save(payload)
    alert('Settings saved successfully!')
  } catch (error) {
    console.error('Error saving settings:', error)
    alert('Failed to save settings. Please try again.')
  }
}

const resetSettings = async () => {
  if (!confirm('Are you sure you want to reset all settings to defaults?')) return
  try {
    const res = await settingsAPI.reset()
    const data = res.data?.data || {}
    settings.value = { ...settings.value, ...data }
  } catch (error) {
    console.error('Error resetting settings:', error)
    alert('Failed to reset settings. Please try again.')
  }
}

const initiateBackup = async () => {
  try {
    await settingsAPI.initiateBackup()
    alert('Backup initiated. You will receive a notification when it completes.')
  } catch (error) {
    console.error('Error initiating backup:', error)
    alert('Failed to initiate backup. Please try again.')
  }
}

const runMaintenance = async () => {
  if (!confirm('Running system maintenance may temporarily affect system performance. Continue?')) return
  try {
    await settingsAPI.runMaintenance()
    alert('System maintenance completed successfully!')
  } catch (error) {
    console.error('Error running maintenance:', error)
    alert('Failed to run maintenance. Please try again.')
  }
}

const loadSettings = async () => {
  try {
    loading.value = true
    const res = await settingsAPI.get()
    const data = res.data?.data || {}
    settings.value = { ...settings.value, ...data }
  } catch (error) {
    console.error('Error loading settings:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadSettings()
})
</script>
