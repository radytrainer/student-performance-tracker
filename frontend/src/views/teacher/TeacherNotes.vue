<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Student Notes</h1>
        <p class="text-gray-600">Create and manage notes for your students</p>
      </div>
      <div class="flex items-center space-x-3">
        <select v-model="filters.student_id" class="px-3 py-2 border rounded">
          <option value="">All students</option>
          <option v-for="s in students" :key="s.user_id" :value="s.user_id">{{ s.user?.first_name }} {{ s.user?.last_name }}</option>
        </select>
        <button @click="loadNotes" class="px-4 py-2 bg-blue-600 text-white rounded">Apply</button>
        <button @click="openCreate = true" class="px-4 py-2 bg-green-600 text-white rounded">New Note</button>
      </div>
    </div>

    <div v-if="openCreate" class="bg-white border rounded p-4 space-y-3">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <select v-model="form.student_id" class="px-3 py-2 border rounded">
          <option v-for="s in students" :key="s.user_id" :value="s.user_id">{{ s.user?.first_name }} {{ s.user?.last_name }}</option>
        </select>
        <input v-model="form.title" placeholder="Title" class="px-3 py-2 border rounded" />
        <label class="inline-flex items-center space-x-2"><input type="checkbox" v-model="form.is_private" /> <span class="text-sm">Private</span></label>
      </div>
      <textarea v-model="form.note" rows="4" class="w-full px-3 py-2 border rounded" placeholder="Write your note..."></textarea>
      <div class="flex justify-end space-x-2">
        <button @click="saveNote" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
        <button @click="resetForm" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
      </div>
    </div>

    <div class="bg-white border rounded">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Student</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Title</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Note</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Private</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="n in notes" :key="n.id" class="border-t">
            <td class="px-4 py-2 text-sm">{{ n.student?.user?.first_name }} {{ n.student?.user?.last_name }}</td>
            <td class="px-4 py-2 text-sm">{{ n.title }}</td>
            <td class="px-4 py-2 text-sm text-gray-700">{{ n.note }}</td>
            <td class="px-4 py-2 text-sm">{{ n.is_private ? 'Yes' : 'No' }}</td>
            <td class="px-4 py-2 text-sm text-right">
              <button @click="edit(n)" class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
              <button @click="remove(n)" class="text-red-600 hover:text-red-800">Delete</button>
            </td>
          </tr>
          <tr v-if="notes.length===0">
            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No notes</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { notesAPI } from '@/api/notes'
import { adminAPI } from '@/api/admin'

const students = ref([])
const notes = ref([])
const filters = ref({ student_id: '' })
const openCreate = ref(false)
const form = ref({ student_id: '', title: '', note: '', is_private: false })

const loadStudents = async () => {
  try { const res = await adminAPI.getClasses({ per_page: 100 }); /* reuse */ } catch {}
  // Fallback: fetch students list
  try { const res = await fetch(import.meta.env.VITE_API_BASE_URL + '/students', { headers: { Authorization: 'Bearer ' + localStorage.getItem('auth_token') } }); const data = await res.json(); const payload = data.data; students.value = payload?.data || payload || [] } catch { students.value = [] }
}

const loadNotes = async () => {
  const params = {}
  if (filters.value.student_id) params.student_id = filters.value.student_id
  const res = await notesAPI.list(params, 'teacher')
  const payload = res.data?.data
  notes.value = payload?.data || payload || []
}

const resetForm = () => { openCreate.value = false; form.value = { student_id: '', title: '', note: '', is_private: false } }

const saveNote = async () => {
  await notesAPI.create(form.value, 'teacher')
  resetForm()
  await loadNotes()
}

const edit = async (n) => {
  form.value = { student_id: n.student_id, title: n.title, note: n.note, is_private: n.is_private }
  openCreate.value = true
}

const remove = async (n) => {
  await notesAPI.remove(n.id, 'teacher')
  await loadNotes()
}

onMounted(async () => { await loadStudents(); await loadNotes() })
</script>
