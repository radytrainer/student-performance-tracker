<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-8 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Weekly Class Schedule</h2>
          <p class="text-gray-500">{{ getCurrentWeekLabel() }}</p>
        </div>
        <div class="flex space-x-2">
          <button
            @click="selectedWeek = 'current'"
            :class="[
              'px-4 py-2 rounded-xl text-sm font-medium transition-colors',
              selectedWeek === 'current' ? 'bg-blue-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100'
            ]"
          >
            This Week
          </button>
          <button
            @click="selectedWeek = 'next'"
            :class="[
              'px-4 py-2 rounded-xl text-sm font-medium transition-colors',
              selectedWeek === 'next' ? 'bg-blue-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100'
            ]"
          >
            Next Week
          </button>
        </div>
      </div>
    </div>

    <div class="p-8">
      <div class="space-y-4">
        <div
          v-for="class_ in getCurrentSchedule()"
          :key="class_.id"
          class="group flex items-center justify-between p-6 bg-gradient-to-r from-gray-50 to-white rounded-2xl border border-gray-100 hover:shadow-lg hover:border-gray-200 transition-all duration-300"
        >
          <div class="flex items-center space-x-6">
            <div class="text-center min-w-[100px]">
              <p class="text-lg font-bold text-gray-900">{{ class_.time }}</p>
              <p class="text-sm text-gray-500">{{ class_.day }}</p>
            </div>
            <div class="w-px h-12 bg-gray-200"></div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ class_.subject }}</h3>
              <p class="text-gray-600 font-medium">{{ class_.teacher }}</p>
              <p class="text-sm text-gray-500">{{ class_.room }}</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <span :class="getStatusColor(class_.status)" class="px-3 py-1 text-xs font-semibold rounded-full">
              {{ class_.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps(['currentWeekSchedule', 'nextWeekSchedule'])

const selectedWeek = ref('current')

const getCurrentSchedule = () => {
  return selectedWeek.value === 'current' ? props.currentWeekSchedule : props.nextWeekSchedule
}

const getCurrentWeek = () => {
  const now = new Date()
  const startOfWeek = new Date(now.setDate(now.getDate() - now.getDay()))
  const endOfWeek = new Date(now.setDate(now.getDate() - now.getDay() + 6))
  return `${startOfWeek.toLocaleDateString()} - ${endOfWeek.toLocaleDateString()}`
}

const getNextWeek = () => {
  const now = new Date()
  const startOfNextWeek = new Date(now.setDate(now.getDate() - now.getDay() + 7))
  const endOfNextWeek = new Date(now.setDate(now.getDate() - now.getDay() + 13))
  return `${startOfNextWeek.toLocaleDateString()} - ${endOfNextWeek.toLocaleDateString()}`
}

const getCurrentWeekLabel = () => {
  return selectedWeek.value === 'current' ? getCurrentWeek() : getNextWeek()
}

const getStatusColor = (status) => {
  switch (status) {
    case 'Upcoming': return 'bg-blue-100 text-blue-700 border border-blue-200'
    case 'In Progress': return 'bg-green-100 text-green-700 border border-green-200'
    case 'Completed': return 'bg-gray-100 text-gray-700 border border-gray-200'
    default: return 'bg-gray-100 text-gray-700 border border-gray-200'
  }
}
</script>