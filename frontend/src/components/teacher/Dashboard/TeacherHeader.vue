<template>
  <header >
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex justify-between items-center py-6">
        <div class="flex items-center space-x-4">
          <div>
            <h1 class="text-2xl font-bold text-purple-600 tracking-tight">
              Welcome back, {{ teacher.name }}!
            </h1>
            <p class="text-sm text-gray-600 font-medium">
              {{ teacher.department }} • {{ teacher.employeeId }}
            </p>
            <p class="text-xs text-gray-500">Last login: {{ teacher.lastLogin }}</p>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <button class="px-4 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-colors">
            Quick Grade
          </button>
          <button class="px-4 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition-colors">
            New Assignment
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
defineProps(['teacher'])
</script>