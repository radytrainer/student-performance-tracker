<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Audit Logs</h1>
        <p class="text-gray-600">System actions and changes</p>
      </div>
      <div class="flex items-center space-x-3">
        <input v-model="filters.user_id" type="number" placeholder="User ID" class="px-3 py-2 border rounded w-28" />
        <input v-model="filters.action" placeholder="Action" class="px-3 py-2 border rounded" />
        <input v-model="filters.model_type" placeholder="Model" class="px-3 py-2 border rounded" />
        <input v-model="filters.date_from" type="date" class="px-3 py-2 border rounded" />
        <input v-model="filters.date_to" type="date" class="px-3 py-2 border rounded" />
        <button @click="load" class="px-4 py-2 bg-blue-600 text-white rounded">Apply</button>
      </div>
    </div>

    <div class="bg-white border rounded">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Time</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">User</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Action</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Model</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">IP</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Details</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="l in logs" :key="l.id" class="border-t">
            <td class="px-4 py-2 text-sm text-gray-500">{{ new Date(l.timestamp || l.created_at).toLocaleString() }}</td>
            <td class="px-4 py-2 text-sm">{{ l.user?.email || l.user_id }}</td>
            <td class="px-4 py-2 text-sm">{{ l.action }}</td>
            <td class="px-4 py-2 text-sm">{{ l.properties?.model_type }} #{{ l.properties?.model_id }}</td>
            <td class="px-4 py-2 text-sm">{{ l.ip_address || '—' }}</td>
            <td class="px-4 py-2 text-sm">{{ summarize(l) }}</td>
          </tr>
          <tr v-if="logs.length===0"><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No logs</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { auditLogsAPI } from '@/api/auditLogs'

const filters = ref({ user_id: '', action: '', model_type: '', date_from: '', date_to: '' })
const logs = ref([])

const load = async () => {
  const params = {}
  for (const [k,v] of Object.entries(filters.value)) { if (v) params[k]=v }
  const res = await auditLogsAPI.list(params)
  const payload = res.data?.data
  logs.value = payload?.data || payload || []
}

const summarize = (l) => {
  try { return JSON.stringify(l.properties?.new_values || l.new_values) } catch { return '' }
}

onMounted(load)
</script>
