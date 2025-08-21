<template>
  <div class="teacher-import-root">
    <div class="p-6">
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
                    <p v-if="missingHeaders.length" class="text-xs text-red-600 mt-1">Missing columns: {{ missingHeaders.join(', ') }}</p>
                  </div>
                  <button @click="clearFile" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="mt-3 border rounded overflow-auto" v-if="localPreviewHeaders.length">
                  <table class="min-w-full text-xs">
                    <thead class="bg-gray-100">
                      <tr>
                        <th v-for="(h,i) in localPreviewHeaders" :key="i" class="px-2 py-1 text-left">{{ h }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(row, idx) in localPreviewRows" :key="idx" class="border-b">
                        <td v-for="(h,i) in localPreviewHeaders" :key="i" class="px-2 py-1">{{ row[h] }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-else-if="localPreviewLoading" class="mt-3 text-sm text-gray-500">Parsing preview...</div>
              </div>

              <!-- Sheet selection (Excel only) -->
              <div v-if="selectedFile && (selectedFile.name.endsWith('.xlsx') || selectedFile.name.endsWith('.xls'))">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sheet</label>
                <select v-model="selectedLocalSheetName" @change="onChangeLocalSheet" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                  <option v-for="n in localSheetNames" :key="n" :value="n">{{ n }}</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Selected sheet will be used for preview and import.</p>
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

              <!-- Optional Subjects Selection -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assign Subjects to Class (optional)</label>
                <select multiple v-model="subjectIds" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent min-h-[110px]">
                  <option v-for="sub in subjects" :key="sub.id" :value="sub.id">{{ sub.subject_name }}</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">If selected, these subjects will be added to the chosen class</p>
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

              <!-- Or upload file only -->
              <div class="pt-2">
                <label class="inline-flex items-center">
                  <input type="checkbox" v-model="fileOnly" class="mr-2">
                  <span class="text-sm text-gray-700">Upload file only (store without processing)</span>
                </label>
              </div>
              <button
                @click="uploadFileOnly"
                :disabled="!selectedFile || importing"
                class="w-full mt-2 bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
              >
                <span v-if="importing && fileOnly" class="flex items-center">
                  <i class="fas fa-spinner fa-spin mr-2"></i>
                  Uploading...
                </span>
                <span v-else class="flex items-center">
                  <i class="fas fa-file-upload mr-2"></i>
                  Upload File Only
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

        <!-- Uploaded Files -->
        <div class="space-y-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Uploaded Files</h3>

            <div class="flex items-center justify-between mb-3">
              <input v-model="searchUploaded" type="text" placeholder="Search files..." class="border rounded px-3 py-2 w-full max-w-sm" />
            </div>
            
            <div v-if="loadingUploads" class="flex justify-center py-8">
              <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            </div>
            
            <div v-else-if="uploadedFiles.length === 0" class="text-center py-8">
              <i class="fas fa-history text-gray-400 text-3xl mb-3"></i>
              <p class="text-gray-500">No uploaded files yet</p>
            </div>
            
            <div v-else class="space-y-3">
              <div
                v-for="file in filteredUploadedFiles"
                :key="file.id"
                class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center">
                      <i class="fas fa-file-import text-blue-600 mr-2"></i>
                      <h4 class="font-medium text-gray-900">
                        <a v-if="file.url" :href="file.url" target="_blank" class="text-blue-600 hover:underline">{{ file.original_name }}</a>
                        <span v-else>{{ file.original_name }}</span>
                      </h4>
                    </div>
                    <div class="mt-1 text-sm text-gray-600">
                      <span v-if="file.size_bytes" class="ml-0">{{ (file.size_bytes/1024).toFixed(1) }} KB</span>
                      <span v-if="file.mime_type" class="ml-2">• {{ file.mime_type }}</span>
                      <span v-if="file.label" class="ml-2">• {{ file.label }}</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                      {{ formatDate(file.uploaded_at) }}
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <button @click="openFileEditor(file)" class="text-gray-700 hover:text-gray-900 text-sm">
                      <i class="fas fa-eye mr-1"></i> Preview/Edit
                    </button>
                    <button @click="importFromUpload(file)" class="text-blue-600 hover:text-blue-800 text-sm">
                      <i class="fas fa-upload mr-1"></i> Import with selected class
                    </button>
                    <button @click="confirmDelete(file)" class="text-red-600 hover:text-red-800 text-sm">
                      <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-between items-center mt-4" v-if="uploadsMeta">
              <button
                @click="prevUploadsPage"
                :disabled="uploadsMeta.current_page <= 1"
                class="px-3 py-1 border rounded disabled:opacity-50"
              >Prev</button>
              <div class="text-sm text-gray-600">Page {{ uploadsMeta.current_page }} of {{ uploadsMeta.last_page }}</div>
              <button
                @click="nextUploadsPage"
                :disabled="uploadsMeta.current_page >= uploadsMeta.last_page"
                class="px-3 py-1 border rounded disabled:opacity-50"
              >Next</button>
            </div>

            <button
              @click="reloadUploads"
              class="w-full mt-4 text-blue-600 hover:text-blue-700 text-sm transition-colors"
            >
              <i class="fas fa-refresh mr-1"></i>
              Refresh Uploaded Files
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
                    v-for="(err, index) in importResults.errors"
                    :key="index"
                    class="text-sm text-red-600 bg-red-50 rounded p-2"
                  >
                    {{ err }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Import History -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Import History</h3>
            <div class="flex items-center justify-between mb-3">
              <input v-model="searchHistory" type="text" placeholder="Search history..." class="border rounded px-3 py-2 w-full max-w-sm" />
              <button @click="loadImportHistory" class="ml-3 px-3 py-2 border rounded">Refresh</button>
            </div>
            <div v-if="loadingHistory" class="flex justify-center py-8">
              <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            </div>
            <div v-else>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-2 text-left">Date</th>
                      <th class="px-4 py-2 text-left">Name</th>
                      <th class="px-4 py-2 text-left">Type</th>
                      <th class="px-4 py-2 text-left">Records</th>
                      <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="(item, i) in filteredHistory" :key="i">
                      <td class="px-4 py-2">{{ formatDate(item.created_at) }}</td>
                      <td class="px-4 py-2">{{ item.name || 'Untitled' }}</td>
                      <td class="px-4 py-2">{{ item.import_type || 'students' }}</td>
                      <td class="px-4 py-2">{{ item.records_processed ?? '-' }}</td>
                      <td class="px-4 py-2">
                        <span :class="getStatusClass(item.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">{{ item.status }}</span>
                      </td>
                    </tr>
                    <tr v-if="filteredHistory.length === 0">
                      <td colspan="5" class="px-4 py-6 text-center text-gray-500">No history found</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  <!-- File Editor Modal -->
  <div v-if="showEditor" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl p-4">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold">Preview/Edit: {{ editorFileName }}</h3>
        <button @click="closeEditor" class="text-gray-600 hover:text-gray-800">✕</button>
      </div>
      <div class="overflow-auto max-h-[60vh] border rounded">
        <div class="p-2">
          <div v-if="editorMissingHeaders.length" class="text-xs text-red-600 mb-2">Missing columns: {{ editorMissingHeaders.join(', ') }}</div>
          <div v-if="editorExtraHeaders.length" class="text-xs text-yellow-700 mb-2">Extra columns: {{ editorExtraHeaders.join(', ') }}</div>
        </div>
        <div class="px-2 pb-2" v-if="editorSheetNames.length">
          <label class="text-xs text-gray-600 mr-2">Sheet:</label>
          <select v-model="selectedEditorSheetName" @change="reparseEditorSheet" class="border rounded px-2 py-1 text-xs">
            <option v-for="n in editorSheetNames" :key="n" :value="n">{{ n }}</option>
          </select>
        </div>
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th v-for="(h,i) in editorHeaders" :key="i" class="px-2 py-1 text-left">{{ h }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row,r) in editorRows" :key="r" class="border-b">
              <td v-for="(h,c) in editorHeaders" :key="c" class="px-2 py-1">
                <input v-model="editorRows[r][h]" :class="['border rounded px-1 py-0.5 w-full', editorCellInvalid(r,h) ? 'border-red-500 bg-red-50' : 'border-gray-300']" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="mt-3 flex justify-between">
        <div class="text-xs text-gray-500">Rows: {{ editorRows.length }}</div>
        <div class="space-x-2">
          <button @click="downloadEditedCsv" class="px-3 py-2 border rounded">Download edited CSV</button>
          <button @click="uploadEditedCsv" class="px-3 py-2 bg-gray-700 text-white rounded">Upload edited CSV</button>
          <button @click="importEditedCsv" class="px-3 py-2 bg-blue-600 text-white rounded" :disabled="!defaultClassId">Import edited (uses selected class)</button>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import dayjs from 'dayjs'
import { teacherAPI } from '@/api/teacher'
import { useAuth } from '@/composables/useAuth'
import * as XLSX from 'xlsx'

// State
const selectedFile = ref(null)
const defaultClassId = ref('')
const sheetName = ref('')
const classes = ref([])
const importing = ref(false)
const isDragOver = ref(false)
const successMessage = ref('')
const error = ref('')
const importHistory = ref([])
const loadingHistory = ref(false)
const historyPage = ref(1)
const historyPerPage = 10
const importResults = ref(null)
const fileOnly = ref(false)

// Expected template headers
const expectedHeaders = ref([])

// Subjects
const subjects = ref([])
const subjectIds = ref([])

// Uploaded files state
const uploadedFiles = ref([])
const uploadsMeta = ref(null)
const loadingUploads = ref(false)
let uploadsPage = 1
const uploadsPerPage = 10
const searchUploaded = ref('')
const filteredUploadedFiles = computed(() => {
  const q = searchUploaded.value.toLowerCase().trim()
  if (!q) return uploadedFiles.value
  return uploadedFiles.value.filter(f => (f.original_name || f.label || '').toLowerCase().includes(q))
})

// Editor modal state
const showEditor = ref(false)
const editorHeaders = ref([])
const editorRows = ref([])
const editorFileName = ref('')
const editorOriginalFile = ref(null)
const editorSheetNames = ref([])
const selectedEditorSheetName = ref('')
const editorMissingHeaders = ref([])
const editorExtraHeaders = ref([])

const requiredHeaders = computed(() => expectedHeaders.value.length ? expectedHeaders.value : minimalHeaders)

const editorCellInvalid = (rowIndex, header) => {
  const val = editorRows.value[rowIndex]?.[header]
  if (requiredHeaders.value.includes(header)) {
    if (header === 'email') return !!val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)
    return val === undefined || val === null || String(val).trim() === ''
  }
  if (header === 'date_of_birth') {
    return val && isNaN(Date.parse(val))
  }
  if (header === 'gender') {
    return val && !['male','female','other','m','f'].includes(String(val).toLowerCase())
  }
  return false
}

// School context
const { user } = useAuth()
const schoolId = computed(() => user.value?.school_id || user.value?.schoolId || null)

// Local preview state (before upload)
const localPreviewHeaders = ref([])
const localPreviewRows = ref([])
const localPreviewLoading = ref(false)
const missingHeaders = ref([])
const minimalHeaders = ['first_name','last_name','email']

// Local sheet support
const localSheetNames = ref([])
const selectedLocalSheetName = ref('')

const parseLocalFile = async (file) => {
  try {
    localPreviewLoading.value = true
    localPreviewHeaders.value = []
    localPreviewRows.value = []
    localSheetNames.value = []
    const buffer = await file.arrayBuffer()
    if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
      const wb = XLSX.read(buffer, { type: 'array' })
      localSheetNames.value = wb.SheetNames
      const name = selectedLocalSheetName.value || wb.SheetNames[0]
      selectedLocalSheetName.value = name
      sheetName.value = name
      const sheet = wb.Sheets[name]
      const json = XLSX.utils.sheet_to_json(sheet, { defval: '' })
      if (json.length) {
        localPreviewHeaders.value = Object.keys(json[0])
        localPreviewRows.value = json.slice(0, 10)
      }
    } else {
      const text = new TextDecoder().decode(buffer)
      const lines = text.split(/\r?\n/).filter(Boolean)
      if (lines.length) {
        const headers = lines[0].split(',')
        localPreviewHeaders.value = headers
        localPreviewRows.value = lines.slice(1, 11).map(l => {
          const cells = l.split(',')
          return Object.fromEntries(headers.map((h,i)=>[h, cells[i] ?? '']))
        })
      }
      localSheetNames.value = ['CSV']
      selectedLocalSheetName.value = 'CSV'
    }
    // Soft validation: show missing headers using template if available
    const baseline = expectedHeaders.value.length ? expectedHeaders.value : minimalHeaders
    missingHeaders.value = baseline.filter(h => !localPreviewHeaders.value.includes(h))
  } catch (e) {
    console.error('Failed to parse local file', e)
  } finally {
    localPreviewLoading.value = false
  }
}

const onChangeLocalSheet = async () => {
  if (!selectedFile.value) return
  await parseLocalFile(selectedFile.value)
}

// Methods
const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    parseLocalFile(file)
  }
}

const handleFileDrop = (event) => {
  isDragOver.value = false
  const file = event.dataTransfer.files[0]
  if (file && (file.type === 'text/csv' || file.name.endsWith('.csv') || file.name.endsWith('.xlsx') || file.name.endsWith('.xls'))) {
    selectedFile.value = file
    parseLocalFile(file)
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
  return dayjs(dateString).format('YYYY-MM-DD HH:mm')
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
    const params = schoolId.value ? { school_id: schoolId.value } : {}
    const response = await teacherAPI.getClasses(params)
    classes.value = response.data.data?.data || response.data.data || response.data || []
  } catch (err) {
    console.error('Error loading classes:', err)
  }
}

const loadTemplateHeaders = async () => {
  try {
    const resp = await teacherAPI.getImportTemplate('students')
    expectedHeaders.value = resp?.data?.data?.headers || []
  } catch (e) {
    console.warn('Could not load template headers', e)
  }
}

const loadSubjects = async () => {
  try {
    const params = schoolId.value ? { school_id: schoolId.value } : {}
    const response = await teacherAPI.getSubjectsForImport(params)
    subjects.value = response.data.data || []
  } catch (err) {
    console.error('Error loading subjects:', err)
  }
}

const loadImportHistory = async () => {
  try {
    loadingHistory.value = true
    const params = schoolId.value ? { school_id: schoolId.value } : {}
    const response = await teacherAPI.getImportHistory(params)
    importHistory.value = response.data.data || response.data || []
  } catch (err) {
    console.error('Error loading import history:', err)
  } finally {
    loadingHistory.value = false
  }
}

const searchHistory = ref('')
const filteredHistory = computed(() => {
  const q = searchHistory.value.toLowerCase().trim()
  if (!q) return importHistory.value
  return importHistory.value.filter(i =>
    (i.name || '').toLowerCase().includes(q) ||
    (i.status || '').toLowerCase().includes(q) ||
    (i.import_type || '').toLowerCase().includes(q)
  )
})

const loadUploadedFiles = async (page = 1) => {
  try {
    loadingUploads.value = true
    const params = { page, per_page: uploadsPerPage }
    if (schoolId.value) params.school_id = schoolId.value
    const response = await teacherAPI.getUploadedFiles(params)
    const payload = response.data.data
    uploadedFiles.value = payload.data || payload || []
    uploadsMeta.value = payload && payload.data ? payload : null
    uploadsPage = uploadsMeta.value ? uploadsMeta.value.current_page : page
  } catch (err) {
    console.error('Error loading uploaded files:', err)
  } finally {
    loadingUploads.value = false
  }
}

const prevUploadsPage = async () => {
  if (uploadsMeta.value && uploadsMeta.value.current_page > 1) {
    await loadUploadedFiles(uploadsMeta.value.current_page - 1)
  }
}

const nextUploadsPage = async () => {
  if (uploadsMeta.value && uploadsMeta.value.current_page < uploadsMeta.value.last_page) {
    await loadUploadedFiles(uploadsMeta.value.current_page + 1)
  }
}

const reloadUploads = async () => {
  await loadUploadedFiles(uploadsPage)
}

const confirmDelete = async (file) => {
  if (!confirm(`Delete ${file.original_name}?`)) return
  try {
    await teacherAPI.deleteUploadedFile(file.id)
    showSuccessMessage('File deleted')
    await loadUploadedFiles(uploadsMeta.value?.current_page || 1)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete file'
  }
}

// File editor helpers
const closeEditor = () => { showEditor.value = false }

const getFetchableUrl = (url) => {
  try {
    const u = new URL(url, window.location.origin)
    if ((u.host === 'localhost:8000' || u.port === '8000') && u.pathname.startsWith('/storage')) {
      return u.pathname // use Vite proxy /storage -> backend
    }
    return u.toString()
  } catch {
    return url
  }
}

const openFileEditor = async (file) => {
  try {
    editorOriginalFile.value = file
    editorFileName.value = file.original_name || file.label || 'uploaded.csv'
    editorHeaders.value = []
    editorRows.value = []
    editorSheetNames.value = []
    selectedEditorSheetName.value = ''
    editorMissingHeaders.value = []
    editorExtraHeaders.value = []
    showEditor.value = true
    if (!file.url) {
      error.value = 'This file has no accessible URL to preview.'
      return
    }
    const res = await fetch(getFetchableUrl(file.url), { credentials: 'include' })
    const arrayBuffer = await res.arrayBuffer()
    const wb = XLSX.read(arrayBuffer, { type: 'array' })
    editorSheetNames.value = wb.SheetNames
    const name = wb.SheetNames[0]
    selectedEditorSheetName.value = name
    const sheet = wb.Sheets[name]
    const json = XLSX.utils.sheet_to_json(sheet, { defval: '' })
    if (json.length) {
      editorHeaders.value = Object.keys(json[0])
      editorRows.value = json
    } else {
      // Try CSV fallback
      const text = new TextDecoder().decode(arrayBuffer)
      const rows = text.split(/\r?\n/).filter(Boolean).map(l => l.split(','))
      if (rows.length) {
        editorHeaders.value = rows[0]
        editorRows.value = rows.slice(1).map(r => Object.fromEntries(editorHeaders.value.map((h,i)=>[h, r[i] ?? ''])))
      }
    }
    // header diff
    if (expectedHeaders.value.length) {
      const set = new Set(editorHeaders.value)
      editorMissingHeaders.value = expectedHeaders.value.filter(h => !set.has(h))
      editorExtraHeaders.value = editorHeaders.value.filter(h => !expectedHeaders.value.includes(h))
    }
  } catch (e) {
    console.error('Preview failed', e)
    error.value = 'Failed to preview file'
  }
}

const reparseEditorSheet = async () => {
  try {
    if (!editorOriginalFile.value || !selectedEditorSheetName.value) return
    const res = await fetch(getFetchableUrl(editorOriginalFile.value.url), { credentials: 'include' })
    const arrayBuffer = await res.arrayBuffer()
    const wb = XLSX.read(arrayBuffer, { type: 'array' })
    const sheet = wb.Sheets[selectedEditorSheetName.value]
    const json = XLSX.utils.sheet_to_json(sheet, { defval: '' })
    editorHeaders.value = json.length ? Object.keys(json[0]) : []
    editorRows.value = json
    if (expectedHeaders.value.length) {
      const set = new Set(editorHeaders.value)
      editorMissingHeaders.value = expectedHeaders.value.filter(h => !set.has(h))
      editorExtraHeaders.value = editorHeaders.value.filter(h => !expectedHeaders.value.includes(h))
    }
  } catch (e) {
    console.error('Reparse failed', e)
  }
}

const toCsv = (headers, rows) => {
  const head = headers.join(',')
  const body = rows.map(r => headers.map(h => (r[h] ?? '').toString().replace(/"/g,'""')).map(v=>`"${v}"`).join(',')).join('\n')
  return head + '\n' + body
}

const downloadEditedCsv = () => {
  const csv = toCsv(editorHeaders.value, editorRows.value)
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'edited_' + editorFileName.value.replace(/\s+/g,'_')
  a.click()
  URL.revokeObjectURL(url)
}

const uploadEditedCsv = async () => {
  try {
    const csv = toCsv(editorHeaders.value, editorRows.value)
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' })
    const filename = 'edited_' + editorFileName.value.replace(/\s+/g,'_')
    const form = new FormData()
    form.append('file', blob, filename)
    form.append('label', filename)
    if (schoolId.value) form.append('school_id', schoolId.value)
    await teacherAPI.uploadFileOnly(form)
    showSuccessMessage('Edited file uploaded')
    await loadUploadedFiles(1)
  } catch (e) {
    console.error('Upload edited CSV failed', e)
    error.value = 'Failed to upload edited CSV'
  }
}

const importEditedCsv = async () => {
  try {
    // Upload edited first, then import by id
    const csv = toCsv(editorHeaders.value, editorRows.value)
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' })
    const filename = 'edited_' + editorFileName.value.replace(/\s+/g,'_')
    const form = new FormData()
    form.append('file', blob, filename)
    form.append('label', filename)
    if (schoolId.value) form.append('school_id', schoolId.value)
    const resp = await teacherAPI.uploadFileOnly(form)
    // Try to detect id from response; fallback by reloading and taking most recent
    let uploadedId = resp?.data?.data?.id || resp?.data?.id
    if (!uploadedId) {
      await loadUploadedFiles(1)
      uploadedId = uploadedFiles.value?.[0]?.id
    }
    if (!uploadedId) throw new Error('Cannot determine uploaded file id')

    const importForm = new FormData()
    importForm.append('uploaded_file_id', uploadedId)
    importForm.append('default_class_id', defaultClassId.value)
    if (sheetName.value) importForm.append('sheet_name', sheetName.value)
    if (subjectIds.value && subjectIds.value.length) {
      subjectIds.value.forEach(id => importForm.append('subject_ids[]', id))
    }
    if (schoolId.value) importForm.append('school_id', schoolId.value)
    const importResp = await teacherAPI.importStudents(importForm)
    importResults.value = importResp.data.data
    showSuccessMessage(importResp.data.message || 'Import completed')
    closeEditor()
  } catch (e) {
    console.error('Import edited CSV failed', e)
    error.value = 'Failed to import edited CSV'
  }
}

const downloadTemplate = async () => {
  try {
    const response = await teacherAPI.getImportTemplate('students')
    const template = response.data.data
    
    const headers = template.headers.join(',')
    const sampleRow = template.sample_data[0]
    const sampleData = template.headers.map(header => sampleRow[header] || '').join(',')
    const csvContent = `${headers}\n${sampleData}`
    
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
    if (sheetName.value) {
      formData.append('sheet_name', sheetName.value)
    }
    if (subjectIds.value && subjectIds.value.length) {
      subjectIds.value.forEach(id => formData.append('subject_ids[]', id))
    }
    if (schoolId.value) {
      formData.append('school_id', schoolId.value)
    }

    const response = await teacherAPI.importStudents(formData)

    importResults.value = response.data.data
    showSuccessMessage(response.data.message || 'Import completed')
    
    // Clear form
    selectedFile.value = null
    defaultClassId.value = ''
    sheetName.value = ''
    
    // Refresh lists
    await Promise.all([loadImportHistory(), loadClasses(), loadUploadedFiles(1)])
    
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

const uploadFileOnly = async () => {
  try {
    importing.value = true
    error.value = ''
    successMessage.value = ''

    const formData = new FormData()
    formData.append('file', selectedFile.value)
    formData.append('label', selectedFile.value?.name || '')
    if (schoolId.value) formData.append('school_id', schoolId.value)

    const response = await teacherAPI.uploadFileOnly(formData)
    showSuccessMessage(response.data.message || 'File uploaded')

    selectedFile.value = null
    defaultClassId.value = ''
    sheetName.value = ''
    fileOnly.value = false

    await loadUploadedFiles(1)

  } catch (err) {
    if (err?.response?.status === 422 && err.response.data?.errors) {
      const details = Object.values(err.response.data.errors).flat().join('; ')
      error.value = `${err.response.data.message}${details ? ': ' + details : ''}`
    } else {
      error.value = err.response?.data?.message || 'Failed to upload file'
    }
    console.error('Upload file error:', err)
  } finally {
    importing.value = false
  }
}

const importFromUpload = async (file) => {
  if (!defaultClassId.value) {
    error.value = 'Please select a Default Class before importing'
    return
  }
  try {
    importing.value = true
    error.value = ''
    importResults.value = null

    const formData = new FormData()
    formData.append('uploaded_file_id', file.id)
    formData.append('default_class_id', defaultClassId.value)
    if (sheetName.value) formData.append('sheet_name', sheetName.value)
    if (subjectIds.value && subjectIds.value.length) {
      subjectIds.value.forEach(id => formData.append('subject_ids[]', id))
    }
    if (schoolId.value) formData.append('school_id', schoolId.value)

    const response = await teacherAPI.importStudents(formData)
    importResults.value = response.data.data
    showSuccessMessage(response.data.message || 'Import completed')

    await Promise.all([loadImportHistory(), loadClasses()])
  } catch (err) {
    if (err?.response?.status === 422 && err.response.data?.errors) {
      const details = Object.values(err.response.data.errors).flat().join('; ')
      error.value = `${err.response.data.message}${details ? ': ' + details : ''}`
    } else {
      error.value = err.response?.data?.message || 'Failed to import students'
    }
    console.error('Import from uploaded file error:', err)
  } finally {
    importing.value = false
  }
}

onMounted(() => {
  loadClasses()
  loadSubjects()
  loadImportHistory()
  loadUploadedFiles(1)
  loadTemplateHeaders()
})
</script>
