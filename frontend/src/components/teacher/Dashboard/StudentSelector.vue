<template>
  <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-2 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg">
        <Users class="w-5 h-5 text-white" />
      </div>
      <h3 class="text-xl font-bold text-gray-900">Students</h3>
      <div class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
        {{ students.length }}
      </div>
    </div>
                
    <div class="space-y-3 max-h-96 overflow-y-auto">
      <button
        v-for="student in students"
        :key="student.id"
        @click="$emit('select-student', student)"
        :class="`w-full p-4 rounded-xl text-left transition-all duration-300 ${
          selectedStudent.id === student.id
            ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg'
            : 'bg-gray-50 hover:bg-gray-100 text-gray-900'
        }`"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="font-semibold">{{ student.name }}</span>
          <div :class="`px-2 py-1 rounded-lg text-xs font-medium ${getStatusColor(student)}`">
            {{ getStatusText(student) }}
          </div>
        </div>
        <div class="text-sm opacity-80">
          <div>{{ student.course }}</div>
          <div class="flex justify-between mt-1">
            <span>Grade: {{ student.averageGrade }}%</span>
            <span>Attendance: {{ student.attendanceRate }}%</span>
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
import { Users } from 'lucide-vue-next'

defineProps({
  students: Array,
  selectedStudent: Object
})

defineEmits(['select-student'])

const getPerformanceLevel = (student) => {
  if (student.averageGrade < 75 || student.attendanceRate < 80) return 'at-risk'
  if (student.averageGrade >= 85 && student.attendanceRate >= 90) return 'excellent'
  return 'good'
}

const getStatusColor = (student) => {
  const level = getPerformanceLevel(student)
  switch (level) {
    case 'at-risk': return 'bg-red-100 text-red-700'
    case 'excellent': return 'bg-green-100 text-green-700'
    default: return 'bg-blue-100 text-blue-700'
  }
}

const getStatusText = (student) => {
  const level = getPerformanceLevel(student)
  switch (level) {
    case 'at-risk': return 'At Risk'
    case 'excellent': return 'Excellent'
    default: return 'Good'
  }
}
</script>