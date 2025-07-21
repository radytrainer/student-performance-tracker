<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
      <h3 class="text-xl font-bold text-gray-900">Support & Contact</h3>
    </div>

    <div class="p-6">
      <div class="space-y-3">
        <button class="w-full flex items-center justify-center space-x-2 px-4 py-4 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-2xl font-medium hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
          <MessageCircleIcon class="w-5 h-5" />
          <span>Submit Help Ticket</span>
        </button>

        <div class="grid grid-cols-2 gap-3">
          <button class="flex items-center justify-center space-x-2 px-3 py-3 border border-gray-200 text-gray-700 rounded-2xl hover:bg-gray-50 transition-colors">
            <PhoneIcon class="w-4 h-4" />
            <span class="text-sm">Call</span>
          </button>
          <button class="flex items-center justify-center space-x-2 px-3 py-3 border border-gray-200 text-gray-700 rounded-2xl hover:bg-gray-50 transition-colors">
            <MailIcon class="w-4 h-4" />
            <span class="text-sm">Email</span>
          </button>
        </div>

        <div class="text-center pt-4 border-t border-gray-100">
          <p class="text-xs text-gray-500">Academic Advisor</p>
          <p class="text-sm font-semibold text-gray-900">Dr. Sarah Wilson</p>
          <p class="text-xs text-blue-600">s.wilson@university.edu</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const MessageCircleIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>' }
const PhoneIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>' }
const MailIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>' }
</script>