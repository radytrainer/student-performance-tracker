<template>
  <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 mb-8">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-2 bg-gradient-to-br from-red-500 to-orange-500 rounded-lg">
        <AlertTriangle class="w-5 h-5 text-white" />
      </div>
      <h3 class="text-xl font-bold text-red-600">Performance Alerts</h3>
      <div class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
        {{ alertStudents.length }} Active
      </div>
    </div>
            
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="student in alertStudents"
        :key="student.id"
        @click="$emit('select-student', student)"
        class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-xl p-4 hover:shadow-md transition-all cursor-pointer"
      >
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-semibold text-gray-900">{{ student.name }}</h4>
          <div class="flex gap-2">
            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-medium">
              Grade: {{ student.averageGrade }}%
            </span>
            <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-lg text-xs font-medium">
              Attendance: {{ student.attendanceRate }}%
            </span>
          </div>
        </div>
        <p class="text-sm text-gray-600">{{ student.course }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { AlertTriangle } from 'lucide-vue-next'

defineProps({
  alertStudents: Array
})

defineEmits(['select-student'])
</script>