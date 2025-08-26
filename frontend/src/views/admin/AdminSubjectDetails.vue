<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Subject Details</h1>
        <p class="text-gray-600">{{ subject?.subject_name }} • {{ subject?.subject_code }}</p>
      </div>
      <button @click="$router.back()" class="px-4 py-2 bg-gray-200 rounded">Back</button>
    </div>

    <div v-if="loading" class="py-12 text-center text-gray-500">Loading...</div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border rounded p-4">
          <h2 class="text-lg font-semibold mb-3">Overview</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div><span class="text-gray-500">Name:</span> {{ subject.subject_name }}</div>
            <div><span class="text-gray-500">Code:</span> {{ subject.subject_code }}</div>
            <div><span class="text-gray-500">Department:</span> {{ subject.department || '—' }}</div>
            <div><span class="text-gray-500">Credit Hours:</span> {{ subject.credit_hours || '—' }}</div>
            <div class="md:col-span-2"><span class="text-gray-500">Description:</span> {{ subject.description || '—' }}</div>
          </div>
        </div>
      </div>
      <div class="space-y-6">
        <div class="bg-white border rounded p-4">
          <h2 class="text-lg font-semibold mb-3">Actions</h2>
          <router-link to="/admin/subjects" class="w-full px-4 py-2 inline-block text-center bg-blue-600 text-white rounded">Manage Subjects</router-link>
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
const subject = ref(null)
const loading = ref(true)

const load = async () => {
  try {
    const s = await adminAPI.getSubject(id)
    subject.value = s.data?.data || s.data
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>
