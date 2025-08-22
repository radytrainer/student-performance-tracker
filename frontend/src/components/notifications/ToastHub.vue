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

const auth = useAuthStore()
const toasts = ref([])
let channel = null
let timer = null

const pushToast = (payload) => {
  const id = `${Date.now()}-${Math.random().toString(36).slice(2)}`
  toasts.value.push({ id, ...payload })
  // Auto dismiss after 5s
  setTimeout(() => dismiss(id), 5000)
}

const dismiss = (id) => {
  toasts.value = toasts.value.filter(t => t.id !== id)
}

onMounted(() => {
  // Subscribe to realtime notifications
  try {
    if (window.Echo && auth?.user?.id) {
      channel = window.Echo.private(`users.${auth.user.id}`)
        .listen('.notification.created', (payload) => {
          pushToast({
            title: payload?.title || 'Notification',
            message: payload?.message || '',
            type: payload?.type || 'info'
          })
        })
    }
  } catch {}
})

onUnmounted(() => {
  try { if (channel) channel.unsubscribe() } catch {}
})
</script>
