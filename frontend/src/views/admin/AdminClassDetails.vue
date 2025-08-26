<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Class Details</h1>
        <p class="text-gray-600">{{ klass?.class_name }} • {{ klass?.academic_year }}</p>
      </div>
      <button @click="$router.back()" class="px-4 py-2 bg-gray-200 rounded">Back</button>
    </div>

    <div v-if="loading" class="py-12 text-center text-gray-500">Loading...</div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border rounded p-4">
          <h2 class="text-lg font-semibold mb-3">Overview</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div><span class="text-gray-500">Name:</span> {{ klass.class_name }}</div>
            <div><span class="text-gray-500">Academic Year:</span> {{ klass.academic_year }}</div>
            <div><span class="text-gray-500">Room:</span> {{ klass.room_number || '—' }}</div>
            <div><span class="text-gray-500">Class Teacher:</span> {{ klass.class_teacher_name || '—' }}</div>
          </div>
        </div>

        <div class="bg-white border rounded p-4">
          <h2 class="text-lg font-semibold mb-3">Students</h2>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-xs text-gray-500">
              <tr>
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2 text-left">Code</th>
                <th class="px-4 py-2 text-left">Email</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="s in students" :key="s.user_id" class="border-t">
                <td class="px-4 py-2 text-sm">{{ s.user?.first_name }} {{ s.user?.last_name }}</td>
                <td class="px-4 py-2 text-sm">{{ s.student_code }}</td>
                <td class="px-4 py-2 text-sm">{{ s.user?.email }}</td>
              </tr>
              <tr v-if="students.length===0"><td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">No students</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="space-y-6">
        <div class="bg-white border rounded p-4">
          <h2 class="text-lg font-semibold mb-3">Actions</h2>
          <router-link to="/admin/classes" class="w-full px-4 py-2 inline-block text-center bg-blue-600 text-white rounded">Manage Classes</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { adminAPI } from '@/api/admin'

const route = useRoute()
const id = route.params.id
const klass = ref(null)
const students = ref([])
const loading = ref(true)

const load = async () => {
  try {
    const k = await adminAPI.getClass(id)
    klass.value = k.data?.data || k.data
    const s = await adminAPI.getStudentsByClass(id)
    students.value = s.data?.data || s.data || []
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>
