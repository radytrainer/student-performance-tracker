<template>
  <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 mb-8">
    <div class="flex items-center gap-3 mb-4">
      <div class="p-2 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg">
        <Filter class="w-5 h-5 text-white" />
      </div>
      <h3 class="text-xl font-bold text-gray-900">Filters</h3>
      <div class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
        {{ filteredCount }} results
      </div>
    </div>
            
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="relative">
        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
        <input
          v-model="filters.name"
          placeholder="Search students..."
          class="w-full pl-12 pr-4 py-3 bg-white/70 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
        />
      </div>
                
      <select
        v-model="filters.subject"
        class="w-full px-4 py-3 bg-white/70 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
      >
        <option value="">All Subjects</option>
        <option v-for="subject in availableSubjects" :key="subject" :value="subject">{{ subject }}</option>
      </select>
                
      <select
        v-model="filters.performance"
        class="w-full px-4 py-3 bg-white/70 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
      >
        <option value="">All Performance</option>
        <option value="excellent">Excellent</option>
        <option value="good">Good</option>
        <option value="at-risk">At Risk</option>
      </select>
                
      <button
        @click="$emit('clear-filters')"
        :disabled="!hasActiveFilters"
        class="px-6 py-3 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-xl hover:from-red-600 hover:to-pink-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 font-medium"
      >
        <X class="w-4 h-4 inline mr-2" />
        Clear
      </button>
    </div>
  </div>
</template>

<script setup>
import { Filter, Search, X } from 'lucide-vue-next'

const filters = defineModel('filters', { required: true })

defineProps({
  filteredCount: Number,
  availableSubjects: Array,
  hasActiveFilters: Boolean
})

defineEmits(['clear-filters'])
</script>