<template>
  <div class="p-6 max-w-3xl mx-auto bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Import Students</h2>

    <!-- File upload section -->
    <div class="bg-gray-50 p-4 rounded-lg border border-dashed border-gray-300">
      <div class="flex items-center space-x-4">
        <label class="block">
          <span class="sr-only">Choose file</span>
          <input
            type="file"
            @change="handleFileUpload"
            accept=".csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            class="block w-full text-sm text-gray-500
                  file:mr-4 file:py-2 file:px-4
                  file:rounded-md file:border-0
                  file:text-sm file:font-semibold
                  file:bg-blue-50 file:text-blue-700
                  hover:file:bg-blue-100"
          />
        </label>
        
        <button
          @click="uploadFile"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
          :disabled="!file || loading"
          :class="{'opacity-50 cursor-not-allowed': !file || loading}"
        >
          <span v-if="loading">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Uploading...
          </span>
          <span v-else>Upload</span>
        </button>
      </div>

      <!-- File info -->
      <div v-if="file" class="mt-3 text-sm text-gray-600">
        Selected file: <span class="font-medium">{{ file.name }}</span> ({{ formatFileSize(file.size) }})
      </div>

      <!-- Status messages -->
      <div v-if="message" class="mt-3 p-3 text-sm text-green-800 bg-green-50 rounded-md">
        <span class="font-medium">Success:</span> {{ message }}
      </div>
      <div v-if="error" class="mt-3 p-3 text-sm text-red-800 bg-red-50 rounded-md">
        <span class="font-medium">Error:</span> {{ error }}
      </div>
    </div>

    <!-- Import history -->
    <div class="mt-8">
      <h3 class="text-lg font-semibold mb-4 text-gray-700">Import History</h3>
      <div class="overflow-hidden border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(item, index) in history" :key="index">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(item.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.import_type }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                  Completed
                </span>
              </td>
            </tr>
            <tr v-if="history.length === 0">
              <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                No import history found
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
      file: null,
      message: "",
      error: "",
      loading: false,
      history: []
    };
  },
  methods: {
    handleFileUpload(event) {
      this.file = event.target.files[0];
      this.message = "";
      this.error = "";
    },
    async uploadFile() {
      if (!this.file) return;
      this.loading = true;
      try {
        const response = await teacherApi.importStudents(this.file);
        this.message = response.data.message || "File uploaded successfully!";
        this.error = "";
        await this.fetchHistory();
      } catch (err) {
        this.error = err.response?.data?.message || "Upload failed. Please check the file format and try again.";
        this.message = "";
      } finally {
        this.loading = false;
      }
    },
    async fetchHistory() {
      try {
        const response = await teacherApi.getImportHistory();
        this.history = response.data || [];
      } catch {
        this.history = [];
      }
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
      return new Date(dateString).toLocaleDateString(undefined, options);
    },
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
  },
  mounted() {
    this.fetchHistory();
  }
};
</script>