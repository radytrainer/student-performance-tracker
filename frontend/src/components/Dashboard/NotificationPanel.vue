<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-gray-900">Notifications</h3>
        <span class="bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs px-3 py-1 rounded-full font-semibold">
          {{ unreadCount }} new
        </span>
      </div>
    </div>

    <div class="p-6">
      <div class="space-y-4">
        <div
          v-for="notification in notifications.slice(0, 4)"
          :key="notification.id"
          class="flex items-start space-x-3 p-4 rounded-2xl hover:bg-gray-50 transition-colors cursor-pointer"
        >
          <div :class="getNotificationColor(notification.type)" class="p-2 rounded-xl">
            <component :is="getNotificationIcon(notification.type)" class="w-4 h-4" />
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-gray-900">{{ notification.title }}</p>
            <p class="text-xs text-gray-600 mt-1">{{ notification.message }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ notification.time }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const ClipboardCheckIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>' }
const CheckCircleIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' }
const InfoIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' }
const AlertCircleIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' }

defineProps(['notifications', 'unreadCount'])

const getNotificationColor = (type) => {
  switch (type) {
    case 'assignment': return 'bg-blue-100 text-blue-600'
    case 'grade': return 'bg-green-100 text-green-600'
    case 'announcement': return 'bg-yellow-100 text-yellow-600'
    case 'deadline': return 'bg-red-100 text-red-600'
    default: return 'bg-gray-100 text-gray-600'
  }
}

const getNotificationIcon = (type) => {
  switch (type) {
    case 'assignment': return 'ClipboardCheckIcon'
    case 'grade': return 'CheckCircleIcon'
    case 'announcement': return 'InfoIcon'
    case 'deadline': return 'AlertCircleIcon'
    default: return 'AlertCircleIcon'
  }
}
</script>