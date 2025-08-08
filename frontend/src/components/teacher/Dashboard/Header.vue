<template>
  <div class="text-center md:text-left mb-8">
    <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-3">
      Analytics Dashboard
    </h1>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
      <p class="text-gray-600 text-lg">Professional Student Performance Analytics</p>
      <div class="flex items-center gap-4 mt-2 md:mt-0">
        <div class="text-sm text-gray-500">{{ currentTime }}</div>
        <button 
          @click="$emit('refresh')"
          :disabled="isRefreshing"
          class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-50"
        >
          <RefreshCw :class="`w-4 h-4 ${isRefreshing ? 'animate-spin' : ''}`" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RefreshCw } from 'lucide-vue-next'

defineProps({
  currentTime: String,
  isRefreshing: Boolean
})

defineEmits(['refresh'])
</script>

<style scoped>
.bg-clip-text {
  -webkit-background-clip: text;
  background-clip: text;
}
</style>