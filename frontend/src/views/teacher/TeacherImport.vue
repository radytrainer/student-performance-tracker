<template>
  <div class="p-6">
    <div>
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Teacher Data Import</h1>
        <p class="text-gray-600 mt-1">Import student data from CSV or Excel files</p>
      </div>

      <!-- SUCCESS -->
      <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-green-700">{{ successMessage }}</p>
      </div>

      <!-- ERROR -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-red-700">{{ error }}</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Upload -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Upload Student Data</h3>

          <!-- File Input -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
            <input
              type="file"
              @change="handleFileSelect"
              accept=".csv,.xlsx,.xls"
              class="block w-full text-sm border rounded p-2"
            />
          </div>

          <!-- Selected file -->
          <div v-if="selectedFile" class="bg-gray-50 rounded-lg p-4 flex justify-between items-center">
            <div>
              <p class="text-sm font-medium">{{ selectedFile.name }}</p>
              <p class="text-xs text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
            </div>
            <button @click="clearFile" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <!-- Default Class -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Default Class (optional)</label>
            <select v-model="defaultClassId" class="w-full border rounded p-2 text-sm">
              <option value="">Select class</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                {{ cls.class_name }}
              </option>
            </select>
            <p class="mt-1 text-xs text-gray-500">
              If your file has <code>current_class_id</code> column, that value will be used.
              Otherwise this default class will be applied to every row.
            </p>
          </div>

          <!-- Import Btn -->
          <button
            @click="importStudents"
            :disabled="!selectedFile || importing"
            class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
          >
            <span v-if="importing"><i class="fas fa-spinner fa-spin mr-2"></i> Importing...</span>
            <span v-else><i class="fas fa-upload mr-2"></i> Import Students</span>
          </button>

          <!-- Template hint -->
          <div class="text-xs text-gray-500">
            Required headers: <code>first_name,last_name,username,email,student_code,date_of_birth,gender,address,parent_name,parent_phone,enrollment_date,current_class_id</code>
          </div>
        </div>

        <!-- History -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900">Import History</h3>
          <div v-if="loadingHistory" class="py-6 text-center">Loading...</div>
          <div v-else-if="importHistory.length === 0" class="py-6 text-center text-gray-500">No history yet</div>

          <ul v-else class="space-y-3">
            <li v-for="item in importHistory" :key="item.id" class="border p-3 rounded">
              <div class="flex justify-between">
                <div>
                  <p class="font-medium">{{ item.file_name }}</p>
                  <p class="text-xs text-gray-500">Type: {{ item.import_type }} • Imported: {{ formatDate(item.imported_at) }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm">
                    <span class="font-semibold">{{ item.successful_records }}</span> / {{ item.total_records }} succeeded
                  </p>
                  <p class="text-xs" :class="item.failed_records ? 'text-red-600' : 'text-gray-500'">
                    {{ item.failed_records }} failed
                  </p>
                </div>
              </div>

              <details v-if="item.error_log" class="mt-2">
                <summary class="text-xs text-gray-600 cursor-pointer">View errors</summary>
                <pre class="text-xs bg-gray-50 p-2 rounded overflow-x-auto">{{ prettyJson(item.error_log) }}</pre>
              </details>
            </li>
          </ul>

          <button @click="loadImportHistory" class="mt-4 text-blue-600 hover:underline">Refresh</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { teacherAPI } from '@/api/teacher'

const selectedFile = ref(null)
const defaultClassId = ref('')
const classes = ref([])
const importing = ref(false)
const successMessage = ref('')
const error = ref('')
const importHistory = ref([])
const loadingHistory = ref(false)

// File select
const handleFileSelect = (e) => { selectedFile.value = e.target.files[0] }
const clearFile = () => { selectedFile.value = null }

// Helpers
const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  let i = 0
  while (bytes >= 1024 && i < units.length - 1) { bytes /= 1024; i++ }
  return `${bytes.toFixed(2)} ${units[i]}`
}

const prettyJson = (raw) => {
  try {
    if (typeof raw === 'string') return JSON.stringify(JSON.parse(raw), null, 2)
    return JSON.stringify(raw, null, 2)
  } catch { return raw }
}

const formatDate = (d) => (d ? new Date(d).toLocaleString() : '')

// Load classes
const loadClasses = async () => {
  try {
    const res = await teacherAPI.getClasses()
    classes.value = res.data?.data ?? res.data ?? []
  } catch (err) {
    console.error(err)
    error.value = 'Failed to load classes'
  }
}

// Load history
const loadImportHistory = async () => {
  try {
    loadingHistory.value = true
    const res = await teacherAPI.getImportHistory()
    importHistory.value = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
  } catch (err) {
    console.error(err)
    error.value = 'Failed to load import history'
  } finally {
    loadingHistory.value = false
  }
}

// Import
const importStudents = async () => {
  if (!selectedFile.value) {
    error.value = 'Please select a file'
    return
  }
  
  try {
    importing.value = true
    error.value = ''
    successMessage.value = ''

    const formData = new FormData()
    formData.append('file', selectedFile.value)
    if (defaultClassId.value) {
      formData.append('default_class_id', defaultClassId.value)
    }

    const res = await teacherAPI.importStudents(formData)

    successMessage.value = res.data.message || 'Import completed successfully'
    if (res.data.errors && res.data.errors.length > 0) {
      successMessage.value += `, but with ${res.data.errors.length} errors`
    }
    
    // Clear form
    selectedFile.value = null
    defaultClassId.value = ''
    
    // Refresh history
    await loadImportHistory()
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 
                 err.response?.data?.error || 
                 'Import failed. Please check your file and try again.'
  } finally {
    importing.value = false
  }
}

onMounted(() => {
  loadClasses()
  loadImportHistory()
})
</script>
