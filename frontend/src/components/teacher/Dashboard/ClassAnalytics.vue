<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
      <h3 class="text-xl font-bold text-gray-900">Class Analytics</h3>
    </div>

    <div class="p-6">
      <div class="space-y-6">
        <!-- Grade Distribution -->
        <div>
          <h4 class="text-sm font-semibold text-gray-700 mb-3">Grade Distribution</h4>
          <div class="space-y-2">
            <div v-for="grade in gradeDistribution" :key="grade.grade" class="flex items-center justify-between">
              <span class="text-sm text-gray-600">{{ grade.grade }}</span>
              <div class="flex items-center space-x-2">
                <div class="w-20 bg-gray-200 rounded-full h-2">
                  <div
                    :class="getGradeBarColor(grade.grade)"
                    class="h-2 rounded-full transition-all duration-300"
                    :style="`width: ${grade.percentage}%`"
                  ></div>
                </div>
                <span class="text-xs text-gray-500 w-8">{{ grade.count }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Attendance Overview -->
        <div>
          <h4 class="text-sm font-semibold text-gray-700 mb-3">Attendance Overview</h4>
          <div class="grid grid-cols-2 gap-4">
            <div class="p-3 bg-green-50 rounded-xl border border-green-100">
              <p class="text-2xl font-bold text-green-600">{{ analytics.averageAttendance }}%</p>
              <p class="text-xs text-green-700">Average Attendance</p>
            </div>
            <div class="p-3 bg-red-50 rounded-xl border border-red-100">
              <p class="text-2xl font-bold text-red-600">{{ analytics.absentToday }}</p>
              <p class="text-xs text-red-700">Absent Today</p>
            </div>
          </div>
        </div>

        <!-- Assignment Completion -->
        <div>
          <h4 class="text-sm font-semibold text-gray-700 mb-3">Assignment Completion</h4>
          <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
            <p class="text-2xl font-bold text-blue-600">{{ analytics.completionRate }}%</p>
            <p class="text-xs text-blue-700">Average Completion Rate</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps(['gradeDistribution', 'analytics'])

const getGradeBarColor = (grade) => {
  switch (grade) {
    case 'A': return 'bg-green-500'
    case 'B': return 'bg-blue-500'
    case 'C': return 'bg-yellow-500'
    case 'D': return 'bg-orange-500'
    case 'F': return 'bg-red-500'
    default: return 'bg-gray-500'
  }
}
</script>