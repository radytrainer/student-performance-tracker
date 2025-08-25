<template>
  <div class="fixed top-20 right-6 z-[1000] space-y-3">
    <div v-for="t in toasts" :key="t.id" class="w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-4 flex items-start">
      <div :class="['mt-1 w-2 h-2 rounded-full', t.type === 'success' ? 'bg-green-500' : t.type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500']"></div>
      <div class="ml-3 flex-1">
        <div class="text-sm font-semibold text-gray-900">{{ t.title || 'Notification' }}</div>
        <div class="text-sm text-gray-700">{{ t.message }}</div>
      </div>
      <button @click="dismiss(t.id)" class="text-gray-400 hover:text-gray-600 ml-2">×</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { notificationsAPI } from '@/api/notifications'

const auth = useAuthStore()
const toasts = ref([])
let channel = null
let pollTimer = null
const seenIds = new Set()

const pushToast = (payload) => {
  const id = `${Date.now()}-${Math.random().toString(36).slice(2)}`
  toasts.value.push({ id, ...payload })
  setTimeout(() => dismiss(id), 5000)
}

const dismiss = (id) => {
  toasts.value = toasts.value.filter(t => t.id !== id)
}

const startPollingFallback = async () => {
  if (!auth?.user?.id) return
  const fetchAndToast = async () => {
    try {
      const res = await notificationsAPI.list({ per_page: 10 })
      const items = res?.data?.data?.data || res?.data?.data || []
      for (const n of items) {
        if (!n?.id) continue
        if (seenIds.has(n.id)) continue
        seenIds.add(n.id)
        pushToast({ title: n.title || 'Notification', message: n.message || '', type: n.type || 'info' })
      }
    } catch {}
  }
  await fetchAndToast()
  pollTimer = setInterval(fetchAndToast, 30000)
}

onMounted(() => {
  try {
    if (window.Echo && auth?.user?.id) {
      channel = window.Echo.private(`users.${auth.user.id}`)
        .listen('.notification.created', (payload) => {
          pushToast({ title: payload?.title || 'Notification', message: payload?.message || '', type: payload?.type || 'info' })
        })
        .listen('.survey.assigned', (payload) => {
          pushToast({ title: 'New Survey Assigned', message: payload?.title || 'A new survey is available', type: 'info' })
        })
        .listen('.survey.completed', (payload) => {
          pushToast({ title: 'Survey Completed', message: `${payload?.form_title || 'Survey'} completed${payload?.average_score != null ? ` with score ${payload.average_score}` : ''}` , type: 'success' })
        })
    } else {
      // Fallback: poll notifications endpoint
      startPollingFallback()
    }
  } catch {
    startPollingFallback()
  }
})

onUnmounted(() => {
  try { if (channel) channel.unsubscribe() } catch {}
  if (pollTimer) clearInterval(pollTimer)
})
</script>
