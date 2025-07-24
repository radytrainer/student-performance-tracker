<template>
  <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-10">
    <!-- Total Students - Clickable -->
    <div 
      @click="showStudentList = true"
      class="group bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 hover:-translate-y-1 cursor-pointer"
    >
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
          <UsersIcon class="w-6 h-6" />
        </div>
        <div class="text-right">
          <p class="text-3xl font-bold">{{ stats.totalStudents }}</p>
          <p class="text-blue-100 text-sm font-medium">Total Students</p>
        </div>
      </div>
      <div class="text-xs text-blue-100 opacity-75">Click to view list</div>
    </div>

    <!-- Active Classes - Clickable -->
    <div 
      @click="showClassList = true"
      class="group bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-emerald-500/25 transition-all duration-300 hover:-translate-y-1 cursor-pointer"
    >
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
          <BookOpenIcon class="w-6 h-6" />
        </div>
        <div class="text-right">
          <p class="text-3xl font-bold">{{ stats.activeClasses }}</p>
          <p class="text-emerald-100 text-sm font-medium">Active Classes</p>
        </div>
      </div>
      <div class="text-xs text-emerald-100 opacity-75">Click to manage</div>
    </div>

    <!-- Pending Grades - Clickable -->
    <div 
      @click="showPendingGrades = true"
      class="group bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-purple-500/25 transition-all duration-300 hover:-translate-y-1 cursor-pointer"
    >
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
          <ClipboardCheckIcon class="w-6 h-6" />
        </div>
        <div class="text-right">
          <p class="text-3xl font-bold">{{ stats.pendingGrades }}</p>
          <p class="text-purple-100 text-sm font-medium">Pending Grades</p>
        </div>
      </div>
      <div class="text-xs text-purple-100 opacity-75">Click to grade</div>
    </div>

    <!-- Today's Classes -->
    <div class="group bg-gradient-to-br from-amber-500 to-orange-500 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-amber-500/25 transition-all duration-300 hover:-translate-y-1">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
          <CalendarIcon class="w-6 h-6" />
        </div>
        <div class="text-right">
          <p class="text-3xl font-bold">{{ stats.upcomingClasses }}</p>
          <p class="text-amber-100 text-sm font-medium">Today's Classes</p>
        </div>
      </div>
    </div>

    <!-- Class Average - Interactive -->
    <div 
      @click="showAnalytics = true"
      class="group bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-indigo-500/25 transition-all duration-300 hover:-translate-y-1 cursor-pointer"
    >
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
          <TrendingUpIcon class="w-6 h-6" />
        </div>
        <div class="text-right">
          <p class="text-xl font-bold">{{ stats.classAverage }}%</p>
          <p class="text-indigo-100 text-sm font-medium">Class Average</p>
        </div>
      </div>
      <div class="text-xs text-indigo-100 opacity-75">Click for details</div>
    </div>

    <!-- Student List Modal -->
    <div v-if="showStudentList" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="showStudentList = false">
      <div class="bg-white rounded-2xl p-6 w-96 max-w-md max-h-96 overflow-y-auto" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-4">All Students ({{ stats.totalStudents }})</h3>
        <div class="space-y-2">
          <div v-for="student in studentList" :key="student.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div>
              <p class="font-medium text-gray-900">{{ student.name }}</p>
              <p class="text-sm text-gray-500">{{ student.id }}</p>
            </div>
            <span :class="getGradeColor(student.grade)" class="font-bold">{{ student.grade }}</span>
          </div>
        </div>
        <button @click="showStudentList = false" class="w-full mt-4 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-colors">
          Close
        </button>
      </div>
    </div>

    <!-- Class List Modal -->
    <div v-if="showClassList" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="showClassList = false">
      <div class="bg-white rounded-2xl p-6 w-96 max-w-md" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-4">Active Classes ({{ stats.activeClasses }})</h3>
        <div class="space-y-3">
          <div v-for="class_ in classList" :key="class_.id" class="p-4 bg-gray-50 rounded-lg">
            <h4 class="font-semibold text-gray-900">{{ class_.name }}</h4>
            <p class="text-sm text-gray-600">{{ class_.students }} students • {{ class_.schedule }}</p>
            <div class="flex space-x-2 mt-2">
              <button class="px-3 py-1 text-xs bg-blue-100 text-blue-600 rounded-full hover:bg-blue-200 transition-colors">
                View Details
              </button>
              <button class="px-3 py-1 text-xs bg-green-100 text-green-600 rounded-full hover:bg-green-200 transition-colors">
                Take Attendance
              </button>
            </div>
          </div>
        </div>
        <button @click="showClassList = false" class="w-full mt-4 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-colors">
          Close
        </button>
      </div>
    </div>

    <!-- Pending Grades Modal -->
    <div v-if="showPendingGrades" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="showPendingGrades = false">
      <div class="bg-white rounded-2xl p-6 w-96 max-w-md max-h-96 overflow-y-auto" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-4">Pending Grades ({{ stats.pendingGrades }})</h3>
        <div class="space-y-3">
          <div v-for="item in pendingGradesList" :key="item.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div>
              <p class="font-medium text-gray-900">{{ item.student }}</p>
              <p class="text-sm text-gray-600">{{ item.assignment }}</p>
              <p class="text-xs text-gray-500">Submitted {{ item.submitted }}</p>
            </div>
            <button 
              @click="gradeItem(item)"
              class="px-3 py-1 text-xs bg-purple-500 text-white rounded-full hover:bg-purple-600 transition-colors"
            >
              Grade
            </button>
          </div>
        </div>
        <button @click="showPendingGrades = false" class="w-full mt-4 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-colors">
          Close
        </button>
      </div>
    </div>

    <!-- Analytics Modal -->
    <div v-if="showAnalytics" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="showAnalytics = false">
      <div class="bg-white rounded-2xl p-6 w-96 max-w-md" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-4">Class Performance Analytics</h3>
        <div class="space-y-4">
          <div class="p-4 bg-indigo-50 rounded-lg">
            <p class="text-2xl font-bold text-indigo-600">{{ stats.classAverage }}%</p>
            <p class="text-sm text-indigo-700">Overall Class Average</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div class="p-3 bg-green-50 rounded-lg text-center">
              <p class="text-lg font-bold text-green-600">{{ analytics.topPerformers }}</p>
              <p class="text-xs text-green-700">Top Performers</p>
            </div>
            <div class="p-3 bg-yellow-50 rounded-lg text-center">
              <p class="text-lg font-bold text-yellow-600">{{ analytics.needsHelp }}</p>
              <p class="text-xs text-yellow-700">Need Help</p>
            </div>
          </div>
          <div class="p-3 bg-blue-50 rounded-lg">
            <p class="text-sm font-medium text-blue-900">Improvement Trend</p>
            <p class="text-lg font-bold text-blue-600">+{{ analytics.improvement }}%</p>
            <p class="text-xs text-blue-700">vs last month</p>
          </div>
        </div>
        <button @click="showAnalytics = false" class="w-full mt-4 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-colors">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Icon components
const UsersIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path></svg>' }
const BookOpenIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>' }
const ClipboardCheckIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>' }
const CalendarIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>' }
const TrendingUpIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>' }

defineProps(['stats'])

// State
const showStudentList = ref(false)
const showClassList = ref(false)
const showPendingGrades = ref(false)
const showAnalytics = ref(false)

// Sample data
const studentList = ref([
  { id: 1, name: 'Sarah Johnson', grade: 'A-' },
  { id: 2, name: 'Michael Chen', grade: 'B+' },
  { id: 3, name: 'Emma Wilson', grade: 'A' },
  { id: 4, name: 'David Rodriguez', grade: 'B' },
  { id: 5, name: 'Lisa Park', grade: 'A-' }
])

const classList = ref([
  { id: 1, name: 'Advanced Calculus', students: 28, schedule: 'MWF 9:00-10:30' },
  { id: 2, name: 'Linear Algebra', students: 35, schedule: 'TTh 11:00-12:30' },
  { id: 3, name: 'Statistics', students: 42, schedule: 'MWF 2:00-3:30' },
  { id: 4, name: 'Discrete Math', students: 25, schedule: 'TTh 3:30-5:00' }
])

const pendingGradesList = ref([
  { id: 1, student: 'Sarah Johnson', assignment: 'Calculus Quiz 3', submitted: '2 days ago' },
  { id: 2, student: 'Michael Chen', assignment: 'Linear Algebra HW', submitted: '1 day ago' },
  { id: 3, student: 'Emma Wilson', assignment: 'Statistics Project', submitted: '3 hours ago' }
])

const analytics = ref({
  topPerformers: 12,
  needsHelp: 8,
  improvement: 5.2
})

// Methods
const getGradeColor = (grade) => {
  if (grade.startsWith('A')) return 'text-green-600'
  if (grade.startsWith('B')) return 'text-blue-600'
  if (grade.startsWith('C')) return 'text-yellow-600'
  return 'text-red-600'
}

const gradeItem = (item) => {
  alert(`Opening grading interface for ${item.student} - ${item.assignment}`)
  showPendingGrades.value = false
}
</script>