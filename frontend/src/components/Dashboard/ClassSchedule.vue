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
            :class="buttonClass('current')"
          >
            This Week
          </button>
          <button
            @click="selectedWeek = 'next'"
            :class="buttonClass('next')"
          >
            Next Week
          </button>
        </div>
      </div>
    </div>

    <div class="p-8">
      <div class="space-y-4">
        <div
          v-for="class_ in getScheduleWithStatus()"
          :key="class_.id"
          class="group flex items-center justify-between p-6 bg-gradient-to-r from-gray-50 to-white rounded-2xl border border-gray-100 hover:shadow-lg transition-all duration-300"
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
          <span :class="getStatusColor(class_.status)" class="px-3 py-1 text-xs font-semibold rounded-full">
            {{ class_.status }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps(['currentWeekSchedule', 'nextWeekSchedule'])

const selectedWeek = ref('current')
const now = ref(new Date())

// Real-time updater
let timer = null
onMounted(() => {
  timer = setInterval(() => {
    now.value = new Date()
  }, 30000) // update every 30 seconds
})
onUnmounted(() => {
  clearInterval(timer)
})

// Get day name to number (e.g., 'Monday' => 1)
const dayToNumber = {
  Sunday: 0, Monday: 1, Tuesday: 2, Wednesday: 3,
  Thursday: 4, Friday: 5, Saturday: 6
}

// Main function to return schedule with real-time status
const getScheduleWithStatus = () => {
  const today = new Date()
  const baseDate = new Date(today)

  // Adjust base date to start of the correct week
  const weekOffset = selectedWeek.value === 'next' ? 7 : 0
  baseDate.setDate(baseDate.getDate() - baseDate.getDay() + weekOffset) // Sunday start

  return (selectedWeek.value === 'current' ? props.currentWeekSchedule : props.nextWeekSchedule).map(class_ => {
    const classDay = dayToNumber[class_.day]
    const classDate = new Date(baseDate)
    classDate.setDate(baseDate.getDate() + (classDay - baseDate.getDay()))

    const [hour, minute] = class_.time.split(':').map(Number)
    classDate.setHours(hour, minute, 0, 0)

    const classStart = new Date(classDate)
    const classEnd = new Date(classStart.getTime() + 60 * 60 * 1000) // +1 hour

    let status = ''
    if (now.value < classStart) {
      status = 'Upcoming'
    } else if (now.value >= classStart && now.value < classEnd) {
      status = 'In Progress'
    } else {
      status = 'Completed'
    }

    return { ...class_, status }
  })
}

// Button style helper
const buttonClass = (week) => {
  return [
    'px-4 py-2 rounded-xl text-sm font-medium transition-colors',
    selectedWeek.value === week ? 'bg-blue-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100'
  ]
}

// Badge colors by status
const getStatusColor = (status) => {
  switch (status) {
    case 'Upcoming': return 'bg-blue-100 text-blue-700 border border-blue-200'
    case 'In Progress': return 'bg-green-100 text-green-700 border border-green-200'
    case 'Completed': return 'bg-red-100 text-red-700 border border-red-200'
    default: return 'bg-gray-100 text-gray-700 border border-gray-200'
  }
}

// Week labels
const getCurrentWeekLabel = () => {
  const base = new Date()
  const offset = selectedWeek.value === 'next' ? 7 : 0
  const start = new Date(base.setDate(base.getDate() - base.getDay() + offset))
  const end = new Date(start)
  end.setDate(start.getDate() + 6)
  return `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`
}
</script>
