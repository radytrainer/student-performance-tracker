<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Performance Alerts</h1>
        <p class="text-gray-600">Alerts for your students</p>
      </div>
      <div class="flex items-center space-x-3">
        <select v-model="filters.term_id" class="px-3 py-2 border rounded">
          <option value="">Current term</option>
          <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.term_name }}</option>
        </select>
        <select v-model="filters.alert_type" class="px-3 py-2 border rounded">
          <option value="">All types</option>
          <option value="low_grade">Low grade</option>
          <option value="attendance">Attendance</option>
          <option value="behavior">Behavior</option>
          <option value="improvement">Improvement</option>
        </select>
        <select v-model="filters.severity" class="px-3 py-2 border rounded">
          <option value="">All severities</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
        <select v-model="filters.is_resolved" class="px-3 py-2 border rounded">
          <option value="">All</option>
          <option :value="false">Open</option>
          <option :value="true">Resolved</option>
        </select>
        <button @click="loadAlerts" class="px-4 py-2 bg-blue-600 text-white rounded">Apply</button>
      </div>
    </div>

    <div class="bg-white border rounded">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Student</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Type</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Severity</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Message</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Term</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in alerts" :key="a.id" class="border-t">
            <td class="px-4 py-2 text-sm text-gray-800">{{ a.student?.user?.first_name }} {{ a.student?.user?.last_name }}</td>
            <td class="px-4 py-2 text-sm">{{ a.alert_type }}</td>
            <td class="px-4 py-2 text-sm">
              <span :class="badgeClass(a.severity)" class="px-2 py-0.5 rounded text-xs font-medium">{{ a.severity }}</span>
            </td>
            <td class="px-4 py-2 text-sm text-gray-700">{{ a.message }}</td>
            <td class="px-4 py-2 text-sm">{{ a.term?.term_name || '—' }}</td>
            <td class="px-4 py-2 text-sm text-right">
              <button v-if="!a.is_resolved" @click="resolve(a)" class="text-green-600 hover:text-green-800">Mark Resolved</button>
              <span v-else class="text-gray-500">Resolved</span>
            </td>
          </tr>
          <tr v-if="alerts.length===0">
            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No alerts</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { alertsAPI } from '@/api/alerts'
import { reportsAPI } from '@/api/reports'

const alerts = ref([])
const terms = ref([])
const filters = ref({ term_id: '', alert_type: '', severity: '', is_resolved: '' })

const loadTerms = async () => {
  try { const res = await reportsAPI.getTerms(); terms.value = res.data || [] } catch { terms.value = [] }
}

const loadAlerts = async () => {
  const params = {}
  for (const [k,v] of Object.entries(filters.value)) { if (v!=='' && v!==null && v!==undefined) params[k]=v }
  const res = await alertsAPI.list(params, 'teacher')
  const payload = res.data?.data
  alerts.value = payload?.data || payload || []
}

const resolve = async (a) => {
  await alertsAPI.update(a.id, { is_resolved: true }, 'teacher')
  await loadAlerts()
}

const badgeClass = (sev) => ({ low: 'bg-yellow-100 text-yellow-800', medium: 'bg-orange-100 text-orange-800', high: 'bg-red-100 text-red-800' }[sev] || 'bg-gray-100 text-gray-800')

onMounted(async () => { await loadTerms(); await loadAlerts() })
</script>
