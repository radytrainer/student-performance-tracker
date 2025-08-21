<template>
  <div class="p-6 max-w-4xl mx-auto bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Student Data Import</h2>

    <div class="mb-8">
      <div class="flex items-center">
        <div v-for="(step, index) in steps" :key="index" class="flex items-center">
          <div 
            @click="currentStep > index && goToStep(index)"
            :class="{
              'bg-blue-600 text-white': currentStep >= index,
              'bg-gray-200 text-gray-600': currentStep < index,
              'cursor-pointer': currentStep > index
            }"
            class="w-8 h-8 rounded-full flex items-center justify-center font-medium"
          >
            {{ index + 1 }}
          </div>
          <div 
            v-if="index < steps.length - 1"
            :class="{
              'bg-blue-600': currentStep > index,
              'bg-gray-200': currentStep <= index
            }"
            class="h-1 w-12 mx-1"
          ></div>
        </div>
      </div>
      <div class="flex justify-between mt-2 text-sm text-gray-600">
        <span v-for="(step, index) in steps" :key="'label'+index">{{ step.label }}</span>
      </div>
    </div>

    <div v-show="currentStep === 0" class="bg-gray-50 p-6 rounded-lg border border-dashed border-gray-300 transition-all duration-300">
      <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-900">Upload student data file</h3>
        <p class="mt-1 text-sm text-gray-500">CSV or Excel files only (max 5MB)</p>
        
        <div class="mt-4">
          <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            Select file
            <input type="file" @change="handleFileUpload" class="sr-only" accept=".csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
          </label>
        </div>
        
        <div v-if="file" class="mt-4 p-3 bg-blue-50 rounded-md inline-flex items-start max-w-md">
          <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-blue-800">File selected</p>
            <p class="text-xs text-blue-700">{{ file.name }} ({{ formatFileSize(file.size) }})</p>
            <button @click="removeFile" class="mt-1 text-xs text-blue-600 hover:text-blue-800 focus:outline-none">
              Remove file
            </button>
          </div>
        </div>
      </div>
      
      <div class="mt-6 flex justify-end">
        <button
          @click="nextStep"
          :disabled="!file"
          :class="{'opacity-50 cursor-not-allowed': !file}"
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Next
        </button>
      </div>
    </div>


    <div v-show="currentStep === 1" class="bg-gray-50 p-6 rounded-lg border border-gray-200">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Data Preview</h3>
      
      <div v-if="previewLoading" class="flex justify-center py-8">
        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
      
      <div v-else>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
              <tr>
                <th v-for="(header, index) in previewHeaders" :key="index" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(row, rowIndex) in previewData" :key="rowIndex">
                <td v-for="(cell, cellIndex) in row" :key="cellIndex" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ cell }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="mt-4 text-sm text-gray-500">
          Showing first {{ previewData.length }} rows of {{ totalRows }} total rows
        </div>
      </div>
      
      <div class="mt-6 flex justify-between">
        <button
          @click="prevStep"
          class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Back
        </button>
        <button
          @click="nextStep"
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Continue
        </button>
      </div>
    </div>

    <div v-show="currentStep === 2" class="bg-gray-50 p-6 rounded-lg border border-gray-200">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Import Settings</h3>
      
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Import Type</label>
          <div class="mt-1 space-y-2">
            <div class="flex items-center">
              <input id="import-type-add" v-model="importSettings.type" type="radio" value="add" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
              <label for="import-type-add" class="ml-3 block text-sm font-medium text-gray-700">Add new records only</label>
            </div>
            <div class="flex items-center">
              <input id="import-type-update" v-model="importSettings.type" type="radio" value="update" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
              <label for="import-type-update" class="ml-3 block text-sm font-medium text-gray-700">Update existing records</label>
            </div>
            <div class="flex items-center">
              <input id="import-type-overwrite" v-model="importSettings.type" type="radio" value="overwrite" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
              <label for="import-type-overwrite" class="ml-3 block text-sm font-medium text-gray-700">Overwrite all records</label>
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
        
        <div>
          <label for="import-name" class="block text-sm font-medium text-gray-700">Import Name (optional)</label>
          <input type="text" id="import-name" v-model="importSettings.name" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
          <p class="mt-1 text-sm text-gray-500">Give this import a name for future reference</p>
        </div>
      </div>
      
      <div class="mt-6 flex justify-between">
        <button
          @click="prevStep"
          class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Back
        </button>
        <button
          @click="confirmImport"
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Confirm Import
        </button>
      </div>
    </div>

    <!-- Step 4: Import Progress -->
    <div v-show="currentStep === 3" class="bg-gray-50 p-6 rounded-lg border border-gray-200 text-center">
      <svg v-if="importStatus === 'processing'" class="mx-auto h-12 w-12 text-blue-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      
      <svg v-else-if="importStatus === 'success'" class="mx-auto h-12 w-12 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>
      
      <svg v-else-if="importStatus === 'error'" class="mx-auto h-12 w-12 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
      
      <h3 class="mt-4 text-lg font-medium text-gray-900">
        <span v-if="importStatus === 'processing'">Importing student data...</span>
        <span v-else-if="importStatus === 'success'">Import completed successfully!</span>
        <span v-else-if="importStatus === 'error'">Import failed</span>
      </h3>
      
      <div v-if="importStatus === 'processing'" class="mt-4">
        <div class="w-full bg-gray-200 rounded-full h-2.5">
          <div class="bg-blue-600 h-2.5 rounded-full" :style="{width: progress + '%'}"></div>
        </div>
        <p class="mt-2 text-sm text-gray-600">{{ progress }}% complete</p>
      </div>
      
      <div v-if="importResult" class="mt-4 text-left bg-white p-4 rounded-md shadow-sm">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-sm font-medium text-gray-500">Total Records</p>
            <p class="text-lg font-semibold">{{ importResult.total }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Successfully Imported file</p>
            <p class="text-lg font-semibold text-green-600">{{ importResult.success }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Skipped the import</p>
            <p class="text-lg font-semibold text-yellow-600">{{ importResult.skipped }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Failed import</p>
            <p class="text-lg font-semibold text-red-600">{{ importResult.failed }}</p>
          </div>
        </div>
        
        <div v-if="importResult.errors && importResult.errors.length > 0" class="mt-4">
          <p class="text-sm font-medium text-gray-700 mb-2">Error Details:</p>
          <ul class="text-sm text-red-600 space-y-1">
            <li v-for="(error, index) in importResult.errors" :key="index">
              Row {{ error.row }}: {{ error.message }}
            </li>
          </ul>

          <button @click="loadImportHistory" class="mt-4 text-blue-600 hover:underline">Refresh</button>
        </div>
      </div>
      
      <div class="mt-6">
        <button
          v-if="importStatus === 'success' || importStatus === 'error'"
          @click="startNewImport"
          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Start New Import file
        </button>
      </div>
    </div>
    <div class="mt-8">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-700">Recent Imports file</h3>
        <button 
          @click="refreshHistory"
          class="text-sm text-blue-600 hover:text-blue-800 flex items-center focus:outline-none"
          :disabled="historyLoading"
        >
          <svg v-if="historyLoading" class="animate-spin -ml-1 mr-1 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span v-else>
            <svg class="-ml-1 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </span>
          Refresh
        </button>
      </div>
      
      <div class="overflow-hidden border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Records</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(item, index) in history" :key="index">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(item.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.name || 'Untitled import' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatImportType(item.import_type) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.records_processed }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(item.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ item.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <button @click="viewImportDetails(item)" class="text-blue-600 hover:text-blue-900 mr-3">Details</button>
                <button v-if="item.status === 'completed'" @click="downloadImportReport(item)" class="text-green-600 hover:text-green-900">Report</button>
              </td>
            </tr>
            <tr v-if="history.length === 0 && !historyLoading">
              <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                No import history found
              </td>
            </tr>
            <tr v-if="historyLoading">
              <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                <svg class="animate-spin mx-auto h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Errors modal -->
  <div v-if="showErrors" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl p-4">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold">Import errors</h3>
        <button @click="showErrors=false" class="text-gray-600 hover:text-gray-800">✕</button>
      </div>
      <div class="max-h-80 overflow-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left border-b">
              <th class="py-2 pr-3">Row</th>
              <th class="py-2">Message</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(e,i) in errorsList" :key="i" class="border-b">
              <td class="py-2 pr-3">{{ e.row ?? '-' }}</td>
              <td class="py-2">{{ e.message ?? e }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="mt-3 text-right">
        <button @click="showErrors=false" class="bg-blue-600 text-white px-4 py-2 rounded">Close</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
// import dayjs from 'dayjs' // Not installed, using native Date
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

const formatDate = (d) => {
  if (!d) return ''
  const date = new Date(d)
  return date.toLocaleString('en-CA', { 
    year: 'numeric', 
    month: '2-digit', 
    day: '2-digit', 
    hour: '2-digit', 
    minute: '2-digit' 
  }).replace(',', '')
}

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
