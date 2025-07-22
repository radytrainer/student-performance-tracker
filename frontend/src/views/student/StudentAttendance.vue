<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Main Content -->
    <div class="flex-1 overflow-auto p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-800">My Attendance</h1>
          <p class="text-gray-600 mt-1">View your attendance records and statistics</p>
        </div>
        <button @click="toggleNotifications" class="relative p-2 rounded-full bg-white shadow">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span v-if="unreadNotifications > 0" class="absolute top-0 right-0 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">
            {{ unreadNotifications }}
          </span>
        </button>
      </div>

      <!-- Attendance Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-green-100 p-4 rounded-2xl shadow flex items-start">
          <div class="bg-green-200 p-3 rounded-full mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-green-800">Total Present</h2>
            <p class="text-2xl font-bold text-green-900">{{ summary.totalPresent }} Days</p>
          </div>
        </div>
        
        <div class="bg-red-100 p-4 rounded-2xl shadow flex items-start">
          <div class="bg-red-200 p-3 rounded-full mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-red-800">Total Absent</h2>
            <p class="text-2xl font-bold text-red-900">{{ summary.totalAbsent }} Days</p>
          </div>
        </div>
        
        <div class="bg-blue-100 p-4 rounded-2xl shadow flex items-start">
          <div class="bg-blue-200 p-3 rounded-full mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-blue-800">Attendance %</h2>
            <p class="text-2xl font-bold text-blue-900">{{ summary.percentage }}%</p>
          </div>
        </div>
        
        <div class="bg-yellow-100 p-4 rounded-2xl shadow flex items-start">
          <div class="bg-yellow-200 p-3 rounded-full mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-yellow-800">Last Present</h2>
            <p class="text-2xl font-bold text-yellow-900">{{ summary.lastPresent }}</p>
          </div>
        </div>
      </div>

      <!-- Filters and Export -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6 bg-white p-4 rounded-xl shadow">
        <div class="flex flex-wrap items-center gap-4">
          <div class="relative">
           <select
              v-model="filters.subject"
              class="border rounded-lg p-2 pl-10 pr-4 appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Subjects</option>
              <option v-for="subject in subjects" :key="subject.id" :value="subject.subject_name">
                {{ subject.subject_name }}
              </option>
          </select>

            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          
          <div class="relative">
            <input type="date" v-model="filters.date" class="border rounded-lg p-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          
          <button @click="resetFilters" class="flex items-center gap-2 bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Reset
          </button>
        </div>
        
        <div class="flex items-center gap-2">
          <button @click="exportToPDF" class="flex items-center gap-2 bg-red-100 px-4 py-2 rounded-lg text-red-800 hover:bg-red-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
            </svg>
            PDF
          </button>
          <button @click="exportToExcel" class="flex items-center gap-2 bg-green-100 px-4 py-2 rounded-lg text-green-800 hover:bg-green-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Excel
          </button>
        </div>
      </div>

      <!-- Calendar View -->
      <div class="mb-6 border p-4 rounded-xl bg-white shadow">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold">Calendar View</h2>
          <div class="flex gap-2">
            <button @click="prevMonth" class="p-1 rounded-full hover:bg-gray-100">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <span class="font-semibold">{{ currentMonth }}</span>
            <button @click="nextMonth" class="p-1 rounded-full hover:bg-gray-100">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>
        <div class="grid grid-cols-7 gap-1 text-center text-sm">
          <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="p-2 font-semibold text-gray-500">
            {{ day }}
          </div>
          <template v-for="day in calendarDays" :key="day.date">
            <div v-if="day.inMonth" 
                 @click="selectDate(day.date)"
                 class="p-2 rounded-lg cursor-pointer hover:bg-gray-50 border"
                 :class="[dayAttendanceClass(day.date), { 'border-blue-300': isSelectedDate(day.date) }]">
              <p class="font-semibold">{{ day.day }}</p>
              <div class="h-2 w-2 mx-auto rounded-full mt-1" :class="dayStatusDot(day.date)"></div>
            </div>
            <div v-else class="p-2 text-gray-300">
              {{ day.day }}
            </div>
          </template>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="overflow-x-auto rounded-lg shadow bg-white">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100 text-gray-700">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">Date</th>
              <th class="px-6 py-3 text-left font-semibold">Status</th>
              <th class="px-6 py-3 text-left font-semibold">Subject</th>
              <th class="px-6 py-3 text-left font-semibold">Time</th>
              <th class="px-6 py-3 text-left font-semibold">Remarks</th>
              <th class="px-6 py-3 text-left font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="record in filteredAttendance" :key="record.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  {{ formatDate(record.date) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="statusClass(record.status)" class="px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1">
                  <svg v-if="record.status === 'Present'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <svg v-else-if="record.status === 'Absent'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <svg v-else-if="record.status === 'Late'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ record.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full" :class="subjectColor(record.subject)"></div>
                  {{ record.subject }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ record.time }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <span v-if="record.remarks">{{ record.remarks }}</span>
                  <span v-else class="text-gray-400">No remarks</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <button @click="viewDetails(record)" class="text-blue-600 hover:text-blue-800 flex items-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  View
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Notifications Panel -->
    <div v-if="showNotifications" class="w-80 bg-white border-l shadow-lg overflow-y-auto">
      <div class="p-4 border-b sticky top-0 bg-white z-10">
        <div class="flex justify-between items-center">
          <h2 class="text-lg font-bold">Notifications</h2>
          <button @click="toggleNotifications" class="p-1 rounded-full hover:bg-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="flex justify-between mt-2 text-sm">
          <button @click="markAllAsRead" class="text-blue-600 hover:text-blue-800">Mark all as read</button>
          <button @click="clearNotifications" class="text-gray-500 hover:text-gray-700">Clear all</button>
        </div>
      </div>
      
      <div v-if="notifications.length > 0" class="divide-y">
        <div v-for="(notification, index) in notifications" :key="index" 
             @click="markAsRead(index)"
             class="p-4 cursor-pointer hover:bg-gray-50"
             :class="{ 'bg-blue-50': !notification.read }">
          <div class="flex items-start gap-3">
            <div class="mt-1 flex-shrink-0">
              <div class="h-8 w-8 rounded-full flex items-center justify-center"
                   :class="notification.type === 'warning' ? 'bg-red-100 text-red-600' : 
                          notification.type === 'info' ? 'bg-blue-100 text-blue-600' : 
                          'bg-green-100 text-green-600'">
                <svg v-if="notification.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <h3 class="font-semibold">{{ notification.title }}</h3>
              <p class="text-sm text-gray-600 mt-1">{{ notification.message }}</p>
              <div class="flex justify-between items-center mt-2">
                <span class="text-xs text-gray-400">{{ formatTime(notification.time) }}</span>
                <span v-if="!notification.read" class="h-2 w-2 rounded-full bg-blue-500"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-else class="p-8 text-center text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <p class="mt-2">No notifications</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios' // ✅ Using Axios to fetch subjects

// Summary Data
const summary = ref({
  totalPresent: 45,
  totalAbsent: 5,
  percentage: 90,
  lastPresent: 'July 20, 2025'
})

// ✅ Dynamic Subjects from API
const subjects = ref([])
const fetchSubjects = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/public-subjects')
    subjects.value = response.data
  } catch (error) {
    console.error('Failed to fetch subjects:', error)
  }
}

// Attendance Records (static for now)
const attendanceRecords = ref([
  { id: 1, date: '2025-07-20', status: 'Present', subject: 'Math', time: '08:00 - 09:00', remarks: 'On time' },
  { id: 2, date: '2025-07-19', status: 'Absent', subject: 'Science', time: '09:00 - 10:00', remarks: 'Sick leave' },
  { id: 3, date: '2025-07-18', status: 'Late', subject: 'Literature', time: '10:00 - 11:00', remarks: 'Late by 5 mins' },
  { id: 4, date: '2025-07-17', status: 'Present', subject: 'History', time: '11:00 - 12:00', remarks: '' },
  { id: 5, date: '2025-07-16', status: 'Present', subject: 'Geography', time: '13:00 - 14:00', remarks: 'Participated actively' },
  { id: 6, date: '2025-07-15', status: 'Absent', subject: 'Math', time: '08:00 - 09:00', remarks: 'Family event' },
  { id: 7, date: '2025-07-14', status: 'Present', subject: 'Science', time: '09:00 - 10:00', remarks: '' },
  { id: 8, date: '2025-07-13', status: 'Late', subject: 'Literature', time: '10:00 - 11:00', remarks: 'Traffic delay' },
  { id: 9, date: '2025-07-12', status: 'Present', subject: 'History', time: '11:00 - 12:00', remarks: '' },
  { id: 10, date: '2025-07-11', status: 'Present', subject: 'Geography', time: '13:00 - 14:00', remarks: 'Submitted assignment' },
])

// Filters
const filters = ref({ subject: '', date: '' })

// Calendar, Selected Date, Notifications (unchanged) ...
const currentDate = ref(new Date())
const selectedDate = ref(null)
const showNotifications = ref(false)
const notifications = ref([
  { id: 1, title: 'Low Attendance Warning', message: 'Your attendance in Math is below 75%', type: 'warning', time: new Date(Date.now() - 3600000), read: false },
  { id: 2, title: 'Absence Notification', message: 'You were marked absent for Science on July 19', type: 'info', time: new Date(Date.now() - 86400000), read: false },
  { id: 3, title: 'Attendance Improved', message: 'Great job! Your attendance has improved this week', type: 'success', time: new Date(Date.now() - 172800000), read: true },
])

// Computed: Attendance filter by subject/date
const filteredAttendance = computed(() => {
  return attendanceRecords.value.filter(r => {
    return (!filters.value.subject || r.subject === filters.value.subject) &&
           (!filters.value.date || r.date === filters.value.date)
  })
})

const unreadNotifications = computed(() => {
  return notifications.value.filter(n => !n.read).length
})
const currentMonth = computed(() => {
  return currentDate.value.toLocaleString('default', { month: 'long', year: 'numeric' })
})

// 🟩 Calendar Days Setup (unchanged)
const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  const firstDay = new Date(year, month, 1)
  const daysInMonth = new Date(year, month + 1, 0).getDate()
  const firstDayOfWeek = firstDay.getDay()
  const prevMonthDays = firstDayOfWeek
  const days = []

  const prevMonthLastDay = new Date(year, month, 0).getDate()
  for (let i = prevMonthLastDay - prevMonthDays + 1; i <= prevMonthLastDay; i++) {
    days.push({ day: i, date: `${year}-${month.toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`, inMonth: false })
  }
  for (let i = 1; i <= daysInMonth; i++) {
    days.push({ day: i, date: `${year}-${(month + 1).toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`, inMonth: true })
  }
  const daysToAdd = 42 - days.length
  for (let i = 1; i <= daysToAdd; i++) {
    days.push({ day: i, date: `${year}-${(month + 2).toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`, inMonth: false })
  }
  return days
})

// Methods
const resetFilters = () => { filters.value = { subject: '', date: '' } }
const statusClass = (status) => ({ 'Present': 'bg-green-100 text-green-800', 'Absent': 'bg-red-100 text-red-800', 'Late': 'bg-yellow-100 text-yellow-800' }[status] || 'bg-gray-100 text-gray-800')
const subjectColor = (subject) => ({ 'Math': 'bg-blue-500', 'Science': 'bg-green-500', 'Literature': 'bg-purple-500', 'History': 'bg-yellow-500', 'Geography': 'bg-red-500' }[subject] || 'bg-gray-500')
const dayAttendanceClass = (date) => {
  const record = attendanceRecords.value.find(r => r.date === date)
  return record ? ({ 'Present': 'bg-green-50', 'Absent': 'bg-red-50', 'Late': 'bg-yellow-50' }[record.status] || 'bg-gray-50') : ''
}
const dayStatusDot = (date) => {
  const record = attendanceRecords.value.find(r => r.date === date)
  return record ? ({ 'Present': 'bg-green-500', 'Absent': 'bg-red-500', 'Late': 'bg-yellow-500' }[record.status] || 'bg-gray-300') : 'bg-transparent'
}
const isSelectedDate = (date) => selectedDate.value === date
const selectDate = (date) => { selectedDate.value = date; filters.value.date = date }
const prevMonth = () => { currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1) }
const nextMonth = () => { currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1) }
const toggleNotifications = () => { showNotifications.value = !showNotifications.value }
const markAsRead = (index) => { notifications.value[index].read = true }
const markAllAsRead = () => { notifications.value.forEach(n => n.read = true) }
const clearNotifications = () => { notifications.value = [] }
const viewDetails = (record) => { alert(`Viewing details for ${record.subject} on ${record.date}`) }
const exportToPDF = () => { alert('PDF export functionality would be implemented here') }
const exportToExcel = () => { alert('Excel export functionality would be implemented here') }
const formatDate = (dateString) => new Date(dateString).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
const formatTime = (date) => {
  const now = new Date()
  const diff = now - date
  if (diff < 60000) return 'Just now'
  if (diff < 3600000) return `${Math.floor(diff / 60000)} min ago`
  if (diff < 86400000) return `${Math.floor(diff / 3600000)} hours ago`
  if (diff < 604800000) return `${Math.floor(diff / 86400000)} days ago`
  return date.toLocaleDateString()
}

// ✅ Fetch Subjects and set today’s selected date (if any)
onMounted(() => {
  const today = new Date().toISOString().split('T')[0]
  if (attendanceRecords.value.some(r => r.date === today)) {
    selectedDate.value = today
  }
  fetchSubjects()
})
</script>
<style scoped>
/* Custom styles */
.progress-circle {
  position: relative;
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: conic-gradient(#4CAF50 calc(var(--percentage) * 1%), #e0e0e0 0);
  display: flex;
  align-items: center;
  justify-content: center;
}

.progress-circle::before {
  content: '';
  position: absolute;
  width: 80%;
  height: 80%;
  border-radius: 50%;
  background: white;
}

.progress-circle span {
  position: relative;
  z-index: 1;
  font-size: 1.2rem;
  font-weight: bold;
}
</style>