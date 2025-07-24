<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-gray-900">Grading Queue</h3>
        <span class="bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs px-3 py-1 rounded-full font-semibold">
          {{ pendingCount }} pending
        </span>
      </div>
    </div>

    <div class="p-6">
      <div class="space-y-4">
        <div
          v-for="item in gradingQueue.slice(0, 5)"
          :key="item.id"
          class="flex items-center justify-between p-4 rounded-2xl hover:bg-gray-50 transition-colors cursor-pointer"
        >
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
              {{ item.studentName.charAt(0) }}
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ item.studentName }}</p>
              <p class="text-xs text-gray-600">{{ item.assignmentTitle }}</p>
              <p class="text-xs text-gray-500">Submitted {{ item.submittedDate }}</p>
            </div>
          </div>
          <div class="text-right">
            <button class="px-3 py-1 text-xs bg-blue-500 text-white rounded-full hover:bg-blue-600 transition-colors">
              Grade Now
            </button>
          </div>
        </div>
      </div>
      
      <button class="w-full mt-4 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-2xl font-medium hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
        View All Pending
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps(['gradingQueue', 'pendingCount'])
</script>