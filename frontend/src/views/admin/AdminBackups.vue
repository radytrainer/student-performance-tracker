<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Backups</h1>
        <p class="text-gray-600">Download system backup archives</p>
      </div>
      <div>
        <router-link to="/admin/settings" class="px-4 py-2 bg-gray-200 rounded">Settings</router-link>
      </div>
    </div>

    <div class="bg-white border rounded">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">File</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Size</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Modified</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="b in backups" :key="b.name" class="border-t">
            <td class="px-4 py-2 text-sm text-gray-800">{{ b.name }}</td>
            <td class="px-4 py-2 text-sm">{{ formatSize(b.size_bytes) }}</td>
            <td class="px-4 py-2 text-sm">{{ formatDate(b.modified_at) }}</td>
            <td class="px-4 py-2 text-sm text-right">
              <button @click="download(b.name)" class="text-blue-600 hover:text-blue-800">Download</button>
            </td>
          </tr>
          <tr v-if="backups.length===0"><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No backups</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { backupsAPI } from '@/api/backups'

const backups = ref([])

const load = async () => {
  const res = await backupsAPI.list()
  backups.value = res.data?.data || []
}

const download = async (name) => {
  const res = await backupsAPI.download(name)
  const blob = new Blob([res.data])
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = name
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  window.URL.revokeObjectURL(url)
}

const formatSize = (bytes) => {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  let i = 0
  let size = bytes
  while (size >= 1024 && i < units.length - 1) { size /= 1024; i++ }
  return `${size.toFixed(1)} ${units[i]}`
}
const formatDate = (d) => new Date(d).toLocaleString()

onMounted(load)
</script>
