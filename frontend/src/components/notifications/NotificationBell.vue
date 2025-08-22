<template>
  <div class="relative" @keydown.escape="open = false">
    <button
      class="relative inline-flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 focus:outline-none"
      @click="toggle"
      :aria-expanded="open ? 'true' : 'false'"
      aria-haspopup="true"
      title="Notifications"
    >
      <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 bg-red-600 text-white text-[10px] leading-none rounded-full px-1.5 py-0.5"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <div v-if="open" class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-xl z-50">
      <div class="flex items-center justify-between px-4 py-2 border-b">
        <div class="text-sm font-medium text-gray-700">Notifications</div>
        <button @click="markAllRead" class="text-xs text-blue-600 hover:text-blue-800">Mark all as read</button>
      </div>
      <div class="max-h-80 overflow-y-auto">
        <div
          v-for="n in notifications"
          :key="n.id"
          class="px-4 py-3 border-b last:border-b-0 flex items-start gap-3 hover:bg-gray-50"
          :class="n.is_read ? 'opacity-75' : ''"
        >
          <div class="w-2 h-2 mt-2 rounded-full" :class="n.is_read ? 'bg-gray-300' : 'bg-blue-600'"></div>
          <div class="flex-1">
            <div class="text-sm font-medium text-gray-900">{{ n.title || n.type || 'Notification' }}</div>
            <div class="text-sm text-gray-700" v-if="n.message">{{ n.message }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ formatDate(n.sent_at) }}</div>
          </div>
          <button v-if="!n.is_read" @click="markRead([n.id])" class="text-xs text-blue-600 hover:text-blue-800">Read</button>
        </div>
        <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">No notifications</div>
      </div>
      <div class="px-4 py-2 text-xs text-gray-500 border-t">Updated {{ lastUpdated ? formatTime(lastUpdated) : '—' }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { notificationsAPI } from '@/api/notifications'

const open = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const lastUpdated = ref(null)
let timer = null

const fetchNotifications = async () => {
  try {
    const res = await notificationsAPI.list({ per_page: 10 })
    const payload = res.data?.data
    const items = payload?.data || payload || []
    notifications.value = items
    unreadCount.value = items.filter(n => !n.is_read).length
    lastUpdated.value = new Date()
  } catch (e) {
    // silently ignore
  }
}

const markRead = async (ids = []) => {
  try {
    await notificationsAPI.markRead(ids)
    await fetchNotifications()
  } catch {}
}

const markAllRead = () => markRead([])

const toggle = async () => {
  open.value = !open.value
  if (open.value) await fetchNotifications()
}

const formatDate = (d) => {
  if (!d) return ''
  return new Date(d).toLocaleString()
}
const formatTime = (d) => new Date(d).toLocaleTimeString()

onMounted(async () => {
  await fetchNotifications()
  timer = setInterval(fetchNotifications, 30000) // poll every 30s
})

onUnmounted(() => { if (timer) clearInterval(timer) })
</script>

<style scoped>
/***** minimal *****/
</style>
