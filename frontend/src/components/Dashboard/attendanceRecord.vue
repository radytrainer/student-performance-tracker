<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
      <h3 class="text-xl font-bold text-gray-900">Attendance Record</h3>
    </div>

    <div class="p-6">
      <div class="space-y-4">
        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl border border-green-100">
          <span class="text-sm font-medium text-gray-700">This Month</span>
          <span class="text-xl font-bold text-green-600">{{ record.thisMonth }}%</span>
        </div>
        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-red-50 to-pink-50 rounded-2xl border border-red-100">
          <span class="text-sm font-medium text-gray-700">Absences</span>
          <span class="text-xl font-bold text-red-600">{{ record.totalAbsences }}</span>
        </div>
        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-2xl border border-yellow-100">
          <span class="text-sm font-medium text-gray-700">Late Check-ins</span>
          <span class="text-xl font-bold text-yellow-600">{{ record.lateCheckins }}</span>
        </div>
        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-100">
          <span class="text-sm font-medium text-gray-700">Weekly Average</span>
          <span class="text-xl font-bold text-blue-600">{{ record.weeklyAverage }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps(['record'])
</script>