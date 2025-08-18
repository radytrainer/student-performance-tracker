<template>
  <div class="p-6 max-w-4xl mx-auto bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Student Data Import</h2>

    <!-- Stepper -->
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

    <!-- Step 1: File Upload -->
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

    <!-- Step 2: Data Preview -->
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

    <!-- Step 3: Import Settings -->
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
          </div>
        </div>
        
        <div>
          <label for="duplicate-handling" class="block text-sm font-medium text-gray-700">Duplicate Handling</label>
          <select id="duplicate-handling" v-model="importSettings.duplicateHandling" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
            <option value="skip">Skip duplicates</option>
            <option value="update">Update duplicates</option>
            <option value="create">Create new records</option>
          </select>
        </div>
        
        <div>
          <div class="flex items-center">
            <input id="send-notifications" v-model="importSettings.sendNotifications" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
            <label for="send-notifications" class="ml-2 block text-sm text-gray-700">Send welcome notifications to new students</label>
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
            <p class="text-sm font-medium text-gray-500">Successfully Imported</p>
            <p class="text-lg font-semibold text-green-600">{{ importResult.success }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Skipped</p>
            <p class="text-lg font-semibold text-yellow-600">{{ importResult.skipped }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Failed</p>
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
        </div>
      </div>
      
      <div class="mt-6">
        <button
          v-if="importStatus === 'success' || importStatus === 'error'"
          @click="startNewImport"
          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Start New Import
        </button>
      </div>
    </div>

    <!-- Import history -->
    <div class="mt-8">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-700">Recent Imports</h3>
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
</template>

<script>
import teacherApi from "@/api/teacher";

export default {
  name: "TeacherImport",
  data() {
    return {
      // Stepper
      currentStep: 0,
      steps: [
        { label: "Upload File", completed: false },
        { label: "Data Preview", completed: false },
        { label: "Settings", completed: false },
        { label: "Import", completed: false }
      ],
      
      // File handling
      file: null,
      previewLoading: false,
      previewHeaders: [],
      previewData: [],
      totalRows: 0,
      
      // Import settings
      importSettings: {
        type: "add",
        duplicateHandling: "skip",
        sendNotifications: true,
        name: ""
      },
      
      // Import progress
      importStatus: null, // null, 'processing', 'success', 'error'
      progress: 0,
      importResult: null,
      
      // History
      history: [],
      historyLoading: false,
      
      // Simulated progress interval
      progressInterval: null
    };
  },
  methods: {
    // File handling
    handleFileUpload(event) {
      this.file = event.target.files[0];
      this.loadPreview();
    },
    
    removeFile() {
      this.file = null;
      this.previewHeaders = [];
      this.previewData = [];
    },
    
    async loadPreview() {
      if (!this.file) return;
      
      this.previewLoading = true;
      try {
        // Simulate API call to get preview data
        // In a real app, you would send the file to your backend for parsing
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Mock data - replace with actual API response
        this.previewHeaders = ['ID', 'Name', 'Email', 'Class'];
        this.previewData = [
          ['001', 'John Doe', 'john@example.com', 'Grade 10'],
          ['002', 'Jane Smith', 'jane@example.com', 'Grade 11'],
          ['003', 'Bob Johnson', 'bob@example.com', 'Grade 9'],
          ['004', 'Alice Williams', 'alice@example.com', 'Grade 12'],
          ['005', 'Charlie Brown', 'charlie@example.com', 'Grade 10']
        ];
        this.totalRows = 125; // Mock total rows
        
      } catch (error) {
        console.error("Error loading preview:", error);
        this.error = "Failed to load file preview";
      } finally {
        this.previewLoading = false;
      }
    },
    
    // Stepper navigation
    nextStep() {
      if (this.currentStep < this.steps.length - 1) {
        this.currentStep++;
      }
    },
    
    prevStep() {
      if (this.currentStep > 0) {
        this.currentStep--;
      }
    },
    
    goToStep(index) {
      if (index < this.currentStep) {
        this.currentStep = index;
      }
    },
    
    // Import process
    async confirmImport() {
      this.importStatus = 'processing';
      this.progress = 0;
      this.nextStep();
      
      // Start progress simulation
      this.progressInterval = setInterval(() => {
        if (this.progress < 90) {
          this.progress += Math.floor(Math.random() * 10) + 1;
          if (this.progress > 90) this.progress = 90;
        }
      }, 500);
      
      try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 3000));
        
        // Clear interval and set to 100%
        clearInterval(this.progressInterval);
        this.progress = 100;
        
        // Mock result - replace with actual API response
        this.importResult = {
          total: 125,
          success: 120,
          skipped: 3,
          failed: 2,
          errors: [
            { row: 23, message: "Invalid email format" },
            { row: 56, message: "Missing required field: name" }
          ]
        };
        
        this.importStatus = 'success';
        await this.fetchHistory();
        
      } catch (error) {
        clearInterval(this.progressInterval);
        this.importStatus = 'error';
        this.importResult = {
          error: error.message || "Import failed"
        };
      }
    },
    
    startNewImport() {
      this.currentStep = 0;
      this.file = null;
      this.previewHeaders = [];
      this.previewData = [];
      this.importSettings = {
        type: "add",
        duplicateHandling: "skip",
        sendNotifications: true,
        name: ""
      };
      this.importStatus = null;
      this.progress = 0;
      this.importResult = null;
    },
    
    // History
    async fetchHistory() {
      this.historyLoading = true;
      try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 800));
        
        // Mock data - replace with actual API response
        this.history = [
          {
            id: 1,
            created_at: new Date(),
            name: "Fall 2023 Enrollment",
            import_type: "add",
            records_processed: 120,
            status: "completed"
          },
          {
            id: 2,
            created_at: new Date(Date.now() - 86400000),
            name: "Student Updates",
            import_type: "update",
            records_processed: 45,
            status: "completed"
          },
          {
            id: 3,
            created_at: new Date(Date.now() - 172800000),
            name: "",
            import_type: "overwrite",
            records_processed: 0,
            status: "failed"
          }
        ];
        
      } catch (error) {
        console.error("Error fetching history:", error);
        this.history = [];
      } finally {
        this.historyLoading = false;
      }
    },
    
    async refreshHistory() {
      await this.fetchHistory();
    },
    
    viewImportDetails(item) {
      // In a real app, you might navigate to a details view or show a modal
      alert(`Viewing details for import #${item.id}`);
    },
    
    downloadImportReport(item) {
      // In a real app, this would download a report file
      alert(`Downloading report for import #${item.id}`);
    },
    
    // Utility methods
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    },
    
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i])
    },
    
    formatImportType(type) {
      const types = {
        add: 'Add New',
        update: 'Update',
        overwrite: 'Overwrite'
      };
      return types[type] || type;
    },
    
    getStatusClass(status) {
      const classes = {
        completed: 'bg-green-100 text-green-800',
        processing: 'bg-blue-100 text-blue-800',
        failed: 'bg-red-100 text-red-800'
      };
      return classes[status.toLowerCase()] || 'bg-gray-100 text-gray-800';
    }
  },
  mounted() {
    this.fetchHistory();
  },
  beforeUnmount() {
    if (this.progressInterval) {
      clearInterval(this.progressInterval);
    }
  }
};
</script>