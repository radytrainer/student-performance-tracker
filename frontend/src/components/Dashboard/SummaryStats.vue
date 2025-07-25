<template>
  <div>
    <!-- Error Message -->
    <div v-if="error" class="text-red-500 font-semibold mb-4">
      {{ error }}
    </div>

    <!-- Dashboard Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-10">
      <!-- Total Courses -->
      <div class="group bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl p-6 text-white">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-2xl">
            <BookOpenIcon />
          </div>
          <div class="text-right">
            <p class="text-3xl font-bold">{{ stats.totalCourses }}</p>
            <p class="text-sm font-medium">Total Courses</p>
          </div>
        </div>
      </div>

      <!-- Assignments -->
      <div class="group bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-3xl p-6 text-white">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-2xl">
            <ClipboardCheckIcon />
          </div>
          <div class="text-right">
            <p class="text-3xl font-bold">
              {{ stats.assignmentsSubmitted }}/{{ stats.totalAssignments }}
            </p>
            <p class="text-sm font-medium">Assignments</p>
          </div>
        </div>
      </div>

      <!-- Attendance -->
      <div class="group bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl p-6 text-white">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-2xl">
            <UsersIcon />
          </div>
          <div class="text-right">
            <p class="text-3xl font-bold">
              <span v-if="loading">...</span><span v-else>{{ attendancePercentage }}%</span>
            </p>
            <p class="text-sm font-medium">Attendance</p>
          </div>
        </div>
      </div>

      <!-- GPA -->
      <div class="group bg-gradient-to-br from-amber-500 to-orange-500 rounded-3xl p-6 text-white">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-2xl">
            <TrendingUpIcon />
          </div>
          <div class="text-right">
            <p class="text-3xl font-bold">{{ stats.currentGPA }}</p>
            <p class="text-sm font-medium">Current GPA</p>
          </div>
        </div>
      </div>

      <!-- Last Login -->
      <div class="group bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-3xl p-6 text-white">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-2xl">
            <CalendarIcon />
          </div>
          <div class="text-right">
            <p class="text-xl font-bold">{{ formattedLastLogin }}</p>
            <p class="text-sm font-medium">Last Login</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import moment from 'moment'
import axios from 'axios'
import { useAuth } from '@/composables/useAuth'

// Props
const props = defineProps({
  stats: {
    type: Object,
    required: true
  }
})
const stats = props.stats

// Auth
const { user } = useAuth()

const formattedLastLogin = computed(() =>
  user.value?.last_login ? moment(user.value.last_login).fromNow() : 'N/A'
)

const attendance = ref([])
const loading = ref(false)
const error = ref(null)

const fetchAttendance = async () => {
  loading.value = true
  error.value = null
  try {
    const resp = await axios.get('/api/student/my-attendance')
    const data = resp?.data?.data ?? []
    attendance.value = Array.isArray(data) ? data : []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load attendance'
  } finally {
    loading.value = false
  }
}

onMounted(fetchAttendance)

const attendancePercentage = computed(() => {
  const now = new Date()
  const m = now.getMonth(), y = now.getFullYear()
  const recs = attendance.value.filter(i => {
    const d = new Date(i.date)
    return d.getMonth() === m && d.getFullYear() === y
  })
  const presentDays = recs.filter(i => i.status === 'present').length
  return recs.length ? Math.round((presentDays / recs.length) * 100) : 0
})
</script>

<script>
// Inline SVG Icon Components
export default {
  components: {
    UsersIcon: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path></svg>`
    },
    BookOpenIcon: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>`
    },
    ClipboardCheckIcon: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>`
    },
    CalendarIcon: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>`
    },
    TrendingUpIcon: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>`
    }
  }
}
</script>
