<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
      <h3 class="text-xl font-bold text-gray-900">Quick Actions</h3>
    </div>

    <div class="p-6">
      <div class="space-y-3">
        <button class="w-full flex items-center justify-center space-x-2 px-4 py-4 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-2xl font-medium hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
          <PlusIcon class="w-5 h-5" />
          <span>Create Assignment</span>
        </button>

        <button class="w-full flex items-center justify-center space-x-2 px-4 py-4 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-2xl font-medium hover:shadow-lg hover:shadow-green-500/25 transition-all duration-200">
          <ClipboardCheckIcon class="w-5 h-5" />
          <span>Take Attendance</span>
        </button>

        <div class="grid grid-cols-2 gap-3">
          <button class="flex items-center justify-center space-x-2 px-3 py-3 border border-gray-200 text-gray-700 rounded-2xl hover:bg-gray-50 transition-colors">
            <MailIcon class="w-4 h-4" />
            <span class="text-sm">Message Students</span>
          </button>
          <button class="flex items-center justify-center space-x-2 px-3 py-3 border border-gray-200 text-gray-700 rounded-2xl hover:bg-gray-50 transition-colors">
            <BarChartIcon class="w-4 h-4" />
            <span class="text-sm">View Reports</span>
          </button>
        </div>

        <button class="w-full flex items-center justify-center space-x-2 px-4 py-3 bg-purple-50 text-purple-700 rounded-2xl hover:bg-purple-100 transition-colors">
          <CalendarIcon class="w-5 h-5" />
          <span>Schedule Office Hours</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const PlusIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>' }
const ClipboardCheckIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>' }
const MailIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>' }
const BarChartIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>' }
const CalendarIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>' }
</script>