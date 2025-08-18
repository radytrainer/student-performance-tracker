<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Import Students</h1>
      <p class="text-gray-600 mt-1">Upload CSV or Excel and assign to one of your classes</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
          <input type="file" @change="onFile" accept=".csv,.xlsx,.xls" class="w-full" />
          <p v-if="file" class="text-sm text-gray-600 mt-1">{{ file.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Default Class</label>
          <select v-model="classId" class="w-full px-3 py-2 border rounded">
            <option value="">Select a class</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.class_name }}</option>
          </select>
        </div>

        <div v-if="file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls'))">
          <label class="block text-sm font-medium text-gray-700 mb-2">Sheet Name (optional)</label>
          <input v-model="sheet" type="text" class="w-full px-3 py-2 border rounded" placeholder="e.g., Students" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Assign Subjects (optional)</label>
          <select multiple v-model="subjectIds" class="w-full px-3 py-2 border rounded min-h-[110px]">
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.subject_name }}</option>
          </select>
        </div>

        <div class="flex gap-3">
          <button @click="importNew" :disabled="!file || !classId || busy" class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50">Import Students</button>
          <button @click="uploadOnly" :disabled="!file || busy" class="bg-gray-700 text-white px-4 py-2 rounded disabled:opacity-50">Upload file only</button>
        </div>
        <p v-if="err" class="text-red-600 text-sm">{{ err }}</p>
        <p v-if="ok" class="text-green-600 text-sm">{{ ok }}</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Uploaded Files</h3>
        <div v-if="loading" class="py-8 text-center">Loading…</div>
        <div v-else-if="files.length === 0" class="text-gray-500 text-sm">No uploads yet</div>
        <div v-else class="space-y-3">
          <div v-for="f in files" :key="f.id" class="border rounded p-3 flex items-center justify-between">
            <div>
              <a :href="f.url" target="_blank" class="text-blue-600 hover:underline">{{ f.original_name }}</a>
              <div class="text-xs text-gray-500">{{ (f.size_bytes/1024).toFixed(1) }} KB • {{ formatDate(f.uploaded_at) }}</div>
            </div>
            <div class="flex gap-3">
              <button @click="importFromUpload(f)" :disabled="!classId || busy" class="text-blue-600 hover:text-blue-800 text-sm">Import with selected class</button>
              <button @click="remove(f)" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminAPI } from '@/api/admin'

const file = ref(null)
const classId = ref('')
const sheet = ref('')
const subjectIds = ref([])
const subjects = ref([])
const classes = ref([])
const files = ref([])
const loading = ref(false)
const busy = ref(false)
const err = ref('')
const ok = ref('')

const onFile = (e) => { file.value = e.target.files[0] }
const formatDate = (d) => new Date(d).toLocaleString()

const loadClasses = async () => {
  try {
    const res = await adminAPI.getTeacherClasses()
    classes.value = res.data.data || res.data.classes || []
  } catch {}
}

const loadUploads = async () => {
  try {
    loading.value = true
    const res = await adminAPI.getUploadedFiles({ page: 1, per_page: 10 })
    const payload = res.data.data
    files.value = payload.data || payload
  } finally { loading.value = false }
}

const loadSubjects = async () => {
  try {
    const res = await adminAPI.getSubjectsForImport()
    subjects.value = res.data.data || []
  } catch {}
}

const importNew = async () => {
  try {
    busy.value = true; err.value = ''; ok.value = ''
    const fd = new FormData()
    fd.append('file', file.value)
    fd.append('default_class_id', classId.value)
    if (sheet.value) fd.append('sheet_name', sheet.value)
    if (subjectIds.value?.length) subjectIds.value.forEach(id => fd.append('subject_ids[]', id))
    const r = await adminAPI.importStudents(fd)
    ok.value = r.data.message
    await Promise.all([loadUploads()])
  } catch (e) {
    err.value = e?.response?.data?.message || 'Failed to import'
  } finally { busy.value = false }
}

const importFromUpload = async (f) => {
  try {
    busy.value = true; err.value = ''; ok.value = ''
    const fd = new FormData()
    fd.append('uploaded_file_id', f.id)
    fd.append('default_class_id', classId.value)
    if (sheet.value) fd.append('sheet_name', sheet.value)
    if (subjectIds.value?.length) subjectIds.value.forEach(id => fd.append('subject_ids[]', id))
    const r = await adminAPI.importStudents(fd)
    ok.value = r.data.message
    await Promise.all([loadUploads()])
  } catch (e) {
    err.value = e?.response?.data?.message || 'Failed to import'
  } finally { busy.value = false }
}

const uploadOnly = async () => {
  try {
    busy.value = true; err.value = ''; ok.value = ''
    const fd = new FormData(); fd.append('file', file.value); fd.append('label', file.value?.name || '')
    const r = await adminAPI.uploadFileOnly(fd)
    ok.value = r.data.message
    file.value = null; sheet.value=''; subjectIds.value=[]
    await loadUploads()
  } catch (e) { err.value = e?.response?.data?.message || 'Failed to upload' } finally { busy.value = false }
}

onMounted(async () => {
  await loadClasses()
  await Promise.all([loadSubjects(), loadUploads()])
})
</script>
