<template>
  <div class="p-6 max-w-3xl mx-auto bg-white rounded-xl shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">Import Students</h2>

    <!-- File input card -->
    <div class="p-4 border border-gray-200 rounded-lg mb-4 shadow-sm bg-gray-50">
      <label class="block mb-2 font-medium text-gray-700">Select Excel or CSV file</label>
      <input
        type="file"
        @change="handleFileUpload"
        accept=".csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        class="block w-full text-gray-700 border border-gray-300 rounded-lg p-2 cursor-pointer"
      />
      <button
        @click="uploadFile"
        class="mt-4 w-full px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-200 disabled:opacity-50"
        :disabled="!file || loading"
      >
        {{ loading ? "Uploading..." : "Upload" }}
      </button>
    </div>

    <!-- Success & Error messages -->
    <div v-if="message" class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg shadow-sm">
      {{ message }}
    </div>
    <div v-if="error" class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg shadow-sm">
      {{ error }}
    </div>

    <!-- Import history card -->
    <div class="mt-6 p-4 border border-gray-200 rounded-lg shadow-sm bg-gray-50">
      <h3 class="text-xl font-semibold mb-3 text-gray-700">Import History</h3>
      <ul>
        <li
          v-for="(item, index) in history"
          :key="index"
          class="flex justify-between items-center py-2 border-b last:border-b-0"
        >
          <span class="text-gray-600">📄 {{ item.import_type }}</span>
          <span class="text-gray-500 text-sm">{{ new Date(item.created_at).toLocaleString() }}</span>
        </li>
        <li v-if="history.length === 0" class="text-gray-400 py-2 text-center">No import history yet.</li>
      </ul>
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
        this.error = err.response?.data?.message || "Upload failed!";
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
    }
  },
  mounted() {
    this.fetchHistory();
  }
};
</script>
