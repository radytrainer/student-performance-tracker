<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-8 border-b border-gray-100">
      <h2 class="text-2xl font-bold text-gray-900">Upcoming Assignments & Exams</h2>
    </div>

    <div class="p-8">
      <div class="space-y-4">
        <div
          v-for="item in items"
          :key="item.id"
          class="group flex items-center justify-between p-6 bg-gradient-to-r from-white to-gray-50 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-gray-200 transition-all duration-300"
        >
          <div class="flex items-center space-x-4">
            <div
              :class="item.type === 'exam' ? 'bg-gradient-to-br from-red-500 to-pink-500' : 'bg-gradient-to-br from-blue-500 to-indigo-500'"
              class="p-3 rounded-2xl text-white shadow-lg"
            >
              <component :is="item.type === 'exam' ? 'ClipboardCheckIcon' : 'DocumentTextIcon'" class="w-6 h-6" />
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ item.title }}</h3>
              <p class="text-gray-600 font-medium">{{ item.subject }}</p>
              <p class="text-sm text-gray-500">Due: {{ item.dueDate }}</p>
            </div>
          </div>
          <div class="text-right space-y-2">
            <span :class="getPriorityColor(item.priority)" class="inline-block px-3 py-1 text-xs font-semibold rounded-full">
              {{ item.priority }}
            </span>
            <p class="text-sm text-gray-500">{{ item.timeLeft }}</p>
            <span :class="getSubmissionColor(item.submissionStatus)" class="text-xs font-medium">
              {{ item.submissionStatus }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const ClipboardCheckIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>' }
const DocumentTextIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>' }

defineProps(['items'])

const getPriorityColor = (priority) => {
  switch (priority) {
    case 'High': return 'bg-red-100 text-red-700 border border-red-200'
    case 'Medium': return 'bg-yellow-100 text-yellow-700 border border-yellow-200'
    case 'Low': return 'bg-green-100 text-green-700 border border-green-200'
    default: return 'bg-gray-100 text-gray-700 border border-gray-200'
  }
}

const getSubmissionColor = (status) => {
  switch (status) {
    case 'Submitted': return 'text-green-600'
    case 'Draft Saved': return 'text-yellow-600'
    case 'Not Submitted': return 'text-red-600'
    case 'Scheduled': return 'text-blue-600'
    default: return 'text-gray-600'
  }
}
</script>