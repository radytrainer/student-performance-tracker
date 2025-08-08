<template>
  <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
    <div class="flex items-center gap-4 mb-6">
      <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
        <User class="w-8 h-8 text-white" />
      </div>
      <div class="flex-1">
        <h2 class="text-3xl font-bold text-gray-900">{{ student.name }}</h2>
        <p class="text-gray-600 text-lg">{{ student.course }} • {{ student.term }}</p>
      </div>
      <button 
        @click="$emit('export')"
        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
      >
        <Download class="w-4 h-4" />
      </button>
    </div>
                
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
        <div class="text-2xl font-bold text-blue-700">{{ student.averageGrade }}%</div>
        <div class="text-sm text-blue-600 font-medium">Average Grade</div>
      </div>
      <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl">
        <div class="text-2xl font-bold text-green-700">{{ student.attendanceRate }}%</div>
        <div class="text-sm text-green-600 font-medium">Attendance</div>
      </div>
      <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl">
        <div class="text-2xl font-bold text-purple-700">{{ student.subjects.length }}</div>
        <div class="text-sm text-purple-600 font-medium">Subjects</div>
      </div>
      <div class="text-center p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl">
        <div class="text-2xl font-bold text-orange-700">{{ Math.max(...student.subjects.map(s => s.grade)) }}%</div>
        <div class="text-sm text-orange-600 font-medium">Best Score</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { User, Download } from 'lucide-vue-next'

defineProps({
  student: Object
})

defineEmits(['export'])
</script>