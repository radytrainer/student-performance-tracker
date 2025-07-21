<template>
  <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-10">
    <div class="group bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 hover:-translate-y-1">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
          <BookOpenIcon class="w-6 h-6" />
        </div>
        <div class="text-right">
          <p class="text-3xl font-bold">{{ stats.totalCourses }}</p>
          <p class="text-blue-100 text-sm font-medium">Total Courses</p>
        </div>
      </div>
    </div>

    <div class="group bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-emerald-500/25 transition-all duration-300 hover:-translate-y-1">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
          <ClipboardCheckIcon class="w-6 h-6" />
        </div>
        <div class="text-right">
          <p class="text-3xl font-bold">
            {{ stats.assignmentsSubmitted }}/{{ stats.totalAssignments }}
          </p>
          <p class="text-emerald-100 text-sm font-medium">Assignments</p>
        </div>
      </div>
    </div>

    <div class="group bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-purple-500/25 transition-all duration-300 hover:-translate-y-1">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
          <UserCheckIcon class="w-6 h-6" />
        </div>
        <div class="text-right">
          <p class="text-3xl font-bold">{{ stats.attendanceRate }}%</p>
          <p class="text-purple-100 text-sm font-medium">Attendance</p>
        </div>
      </div>
    </div>

    <div class="group bg-gradient-to-br from-amber-500 to-orange-500 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-amber-500/25 transition-all duration-300 hover:-translate-y-1">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
          <TrendingUpIcon class="w-6 h-6" />
        </div>
        <div class="text-right">
          <p class="text-3xl font-bold">{{ stats.currentGPA }}</p>
          <p class="text-amber-100 text-sm font-medium">Current GPA</p>
        </div>
      </div>
    </div>

    <div class="group bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-indigo-500/25 transition-all duration-300 hover:-translate-y-1">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
          <ClockIcon class="w-6 h-6" />
        </div>
        <div class="text-right">
          <p class="text-xl font-bold">{{ stats.lastLogin }}</p>
          <p class="text-indigo-100 text-sm font-medium">Last Login</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Icon components
const BookOpenIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>' }
const ClipboardCheckIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>' }
const UserCheckIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' }
const TrendingUpIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>' }
const ClockIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' }

defineProps(['stats'])
</script>