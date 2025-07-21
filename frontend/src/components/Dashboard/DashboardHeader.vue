<template>
  <header class="backdrop-blur-xl border-b border-gray-100 sticky top-0 z-10 bg-white/80">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex justify-between items-center py-6">
        <div class="flex items-center space-x-4">
          <div>
            <h1 class="text-2xl font-bold text-purple-600 tracking-tight">
              Welcome back, {{ student.name }}!
            </h1>
            <p class="text-sm text-gray-600 font-medium">
              {{ student.course }} • ID: {{ student.studentId }}
            </p>
            <p class="text-xs text-gray-500">Last login: {{ student.lastLogin }}</p>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
defineProps(['student'])
</script>