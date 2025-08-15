<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Import Data</h1>
    <p>Upload your attendance or grade file here.</p>

    <input 
      type="file" 
      @change="handleFileUpload" 
      accept=".csv,.xlsx"
      class="border p-2 mt-4"
    />

    <button 
      @click="submitFile" 
      class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
    >
      Import
    </button>
  </div>
</template>

<script>
export default {
  name: "TeacherImport",
  data() {
    return {
      file: null
    };
  },
  methods: {
    handleFileUpload(event) {
      this.file = event.target.files[0];
    },
    async submitFile() {
      if (!this.file) {
        alert("Please select a file before importing.");
        return;
      }

      const formData = new FormData();
      formData.append("file", this.file);
      formData.append("import_type", "attendance"); // or 'grades'

      try {
        const response = await fetch("/api/teacher/import", {
          method: "POST",
          headers: {
            // Do not set Content-Type; browser sets it with FormData boundary
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
          },
          body: formData
        });

        const result = await response.json();
        alert(result.message || "File imported successfully.");
      } catch (error) {
        console.error("Upload failed", error);
        alert("There was an error uploading the file.");
      }
    }
  }
};
</script>
