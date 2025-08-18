<template>
  <div class="p-6">
    <div v-if="!hasPermission('admin.manage_users')" class="text-center py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Access Denied</h2>
      <p class="text-gray-600">You don't have permission to import data.</p>
    </div>
    <div v-else>
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Data Import</h1>
        <p class="text-gray-600 mt-1">Import student data from CSV or Excel files</p>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-green-400"></i>
          </div>
          <div class="ml-3">
            <p class="text-sm text-green-700">{{ successMessage }}</p>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-400"></i>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Import Error</h3>
            <p class="text-sm text-red-700 mt-1">{{ error }}</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Import Section -->
        <div class="space-y-6">
          <!-- File Upload -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Student Data</h3>
            
            <div class="space-y-4">
              <!-- File Input -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors"
                     :class="{ 'border-blue-400 bg-blue-50': isDragOver }"
                     @dragover.prevent="isDragOver = true"
                     @dragleave.prevent="isDragOver = false"
                     @drop.prevent="handleFileDrop">
                  <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                      <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                      <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                        <span>Upload a file</span>
                        <input id="file-upload" name="file-upload" type="file" class="sr-only" @change="handleFileSelect" accept=".csv,.xlsx,.xls">
                      </label>
                      <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">CSV, XLSX up to 2MB</p>
                  </div>
                </div>
              </div>

              <!-- Selected File Display -->
              <div v-if="selectedFile" class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center">
                  <i class="fas fa-file-excel text-green-600 text-xl mr-3"></i>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ selectedFile.name }}</p>
                    <p class="text-sm text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
                  </div>
                  <button @click="clearFile" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>

              <!-- Default Class Selection -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Default Class</label>
                <select
                  v-model="defaultClassId"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                >
                  <option value="">Select a default class</option>
                  <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Students without a specified class will be assigned to this class</p>
              </div>

              <!-- Import Button -->
              <button
                @click="importStudents"
                :disabled="!selectedFile || !defaultClassId || importing"
                class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
              >
                <span v-if="importing" class="flex items-center">
                  <i class="fas fa-spinner fa-spin mr-2"></i>
                  Importing...
                </span>
                <span v-else class="flex items-center">
                  <i class="fas fa-upload mr-2"></i>
                  Import Students
                </span>
              </button>
            </div>
          </div>

          <!-- Template Download -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Download Template</h3>
            <p class="text-sm text-gray-600 mb-4">
              Download a CSV template with the required columns and sample data to ensure your import is successful.
            </p>
            
            <div class="space-y-3">
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Required Columns:</h4>
                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                  <div>• first_name</div>
                  <div>• last_name</div>
                  <div>• email</div>
                  <div>• date_of_birth</div>
                  <div>• gender</div>
                  <div>• address (optional)</div>
                  <div>• parent_name (optional)</div>
                  <div>• parent_phone (optional)</div>
                  <div>• class_id (optional)</div>
                </div>
              </div>
              
              <button
                @click="downloadTemplate"
                class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center"
              >
                <i class="fas fa-download mr-2"></i>
                Download CSV Template
              </button>
            </div>
          </div>
        </div>

        <!-- Import History -->
        <div class="space-y-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Import History</h3>
            
            <div v-if="loadingHistory" class="flex justify-center py-8">
              <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            </div>
            
            <div v-else-if="importHistory.length === 0" class="text-center py-8">
              <i class="fas fa-history text-gray-400 text-3xl mb-3"></i>
              <p class="text-gray-500">No import history yet</p>
            </div>
            
            <div v-else class="space-y-3">
              <div
                v-for="import_record in importHistory"
                :key="import_record.id"
                class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center">
                      <i class="fas fa-file-import text-blue-600 mr-2"></i>
                      <h4 class="font-medium text-gray-900">{{ import_record.file_name }}</h4>
                    </div>
                    <div class="mt-1 text-sm text-gray-600">
                      <span class="text-green-600">{{ import_record.records_imported }} imported</span>
                      <span v-if="import_record.records_failed > 0" class="text-red-600 ml-2">
                        {{ import_record.records_failed }} failed
                      </span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                      {{ formatDate(import_record.imported_at) }} by {{ import_record.imported_by }}
                    </div>
                  </div>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getStatusClass(import_record.status)">
                    {{ capitalizeFirst(import_record.status) }}
                  </span>
                </div>
              </div>
            </div>

            <button
              @click="loadImportHistory"
              class="w-full mt-4 text-blue-600 hover:text-blue-700 text-sm transition-colors"
            >
              <i class="fas fa-refresh mr-1"></i>
              Refresh History
            </button>
          </div>

          <!-- Import Results -->
          <div v-if="importResults" class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Last Import Results</h3>
            
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-green-50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-green-600">{{ importResults.success_count }}</div>
                  <div class="text-sm text-green-700">Successfully Imported</div>
                </div>
                <div class="bg-red-50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-red-600">{{ importResults.error_count }}</div>
                  <div class="text-sm text-red-700">Import Errors</div>
                </div>
              </div>

              <div v-if="importResults.errors && importResults.errors.length > 0" class="space-y-2">
                <h4 class="font-medium text-gray-900">Import Errors:</h4>
                <div class="max-h-40 overflow-y-auto">
                  <div
                    v-for="(error, index) in importResults.errors"
                    :key="index"
                    class="text-sm text-red-600 bg-red-50 rounded p-2"
                  >
                    {{ error }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { adminAPI } from '@/api/admin'

const { hasPermission } = useAuth()

// State
const selectedFile = ref(null)
const defaultClassId = ref('')
const classes = ref([])
const importing = ref(false)
const isDragOver = ref(false)
const successMessage = ref('')
const error = ref('')
const importHistory = ref([])
const loadingHistory = ref(false)
const importResults = ref(null)

// Methods
const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
  }
}

const handleFileDrop = (event) => {
  isDragOver.value = false
  const file = event.dataTransfer.files[0]
  if (file && (file.type === 'text/csv' || file.name.endsWith('.csv') || file.name.endsWith('.xlsx') || file.name.endsWith('.xls'))) {
    selectedFile.value = file
  }
}

const clearFile = () => {
  selectedFile.value = null
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const capitalizeFirst = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const getStatusClass = (status) => {
  const classes = {
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    processing: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const showSuccessMessage = (message) => {
  successMessage.value = message
  setTimeout(() => {
    successMessage.value = ''
  }, 5000)
}

const loadClasses = async () => {
  try {
    const response = await adminAPI.getClasses()
    classes.value = response.data.data.data || response.data.data || []
  } catch (err) {
    console.error('Error loading classes:', err)
  }
}

const loadImportHistory = async () => {
  try {
    loadingHistory.value = true
    const response = await adminAPI.getImportHistory()
    importHistory.value = response.data.data || []
  } catch (err) {
    console.error('Error loading import history:', err)
  } finally {
    loadingHistory.value = false
  }
}

const downloadTemplate = async () => {
  try {
    const response = await adminAPI.getImportTemplate('students')
    const template = response.data.data
    
    // Create CSV content
    const headers = template.headers.join(',')
    const sampleRow = template.sample_data[0]
    const sampleData = template.headers.map(header => sampleRow[header] || '').join(',')
    const csvContent = `${headers}\n${sampleData}`
    
    // Download file
    const blob = new Blob([csvContent], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'student_import_template.csv'
    a.click()
    window.URL.revokeObjectURL(url)
    
    showSuccessMessage('Template downloaded successfully')
  } catch (err) {
    error.value = 'Failed to download template'
  }
}

const importStudents = async () => {
  try {
    importing.value = true
    error.value = ''
    importResults.value = null
    
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    formData.append('default_class_id', defaultClassId.value)
    
    const response = await adminAPI.importStudents(formData)
    
    importResults.value = response.data.data
    showSuccessMessage(response.data.message)
    
    // Clear form
    selectedFile.value = null
    defaultClassId.value = ''
    
    // Refresh history
    await loadImportHistory()
    
  } catch (err) {
    if (err?.response?.status === 422 && err.response.data?.errors) {
      const details = Object.values(err.response.data.errors).flat().join('; ')
      error.value = `${err.response.data.message}${details ? ': ' + details : ''}`
    } else {
      error.value = err.response?.data?.message || 'Failed to import students'
    }
    console.error('Import error:', err)
  } finally {
    importing.value = false
  }
}

onMounted(() => {
  loadClasses()
  loadImportHistory()
})
</script>
