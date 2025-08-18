<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Import Students</h2>

    <!-- File input -->
    <input
      type="file"
      @change="handleFileUpload"
      accept=".csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    />

    <!-- Upload button -->
    <button
      @click="uploadFile"
      class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      :disabled="!file || loading"
    >
      {{ loading ? "Uploading..." : "Upload" }}
    </button>

    <!-- Success & Error messages -->
    <div v-if="message" class="mt-4 text-green-600">
      {{ message }}
    </div>
    <div v-if="error" class="mt-4 text-red-600">
      {{ error }}
    </div>

    <!-- Import history -->
    <div class="mt-6">
      <h3 class="text-lg font-semibold mb-2">Import History</h3>
      <ul>
        <li v-for="(item, index) in history" :key="index" class="border-b py-2">
          Imported file on {{ item.created_at }} → {{ item.import_type }}
        </li>
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
