<template>
  <div class="flex h-screen bg-gray-50">

    <div class="flex-1 overflow-auto p-6">
      <div class="flex justify-between items-center mb-6">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-800">My Attendances</h1>
          <p class="text-gray-600 mt-1">View your attendance recode</p>
        </div>
      </div>

      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mb-2"></div>
        <p>Loading attendance data...</p>
      </div>

      <div v-if="error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
        <p>{{ error }}</p>
        <button @click="fetchAttendance" class="mt-2 text-sm bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
          Retry
        </button>
      </div>

      <div v-if="!loading && !error">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-6">
          
          <!-- Total Present -->
          <div class="bg-white p-5 rounded-2xl shadow-lg hover:shadow-xl transition flex items-center">
            <div class="bg-green-100 p-3 rounded-xl mr-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Presents</p>
              <p class="text-2xl font-bold text-green-700">{{ summary.present }}</p>
              <p class="text-xs text-gray-400">{{ presentPercentage }}%</p>
            </div>
          </div>

          <!-- Total Absent -->
          <div class="bg-white p-5 rounded-2xl shadow-lg hover:shadow-xl transition flex items-center">
            <div class="bg-red-100 p-3 rounded-xl mr-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Absents</p>
              <p class="text-2xl font-bold text-red-700">{{ summary.absent }}</p>
              <p class="text-xs text-gray-400">{{ absentPercentage }}%</p>
            </div>
          </div>

          <!-- Attendance % -->
          <div class="bg-white p-5 rounded-2xl shadow-lg hover:shadow-xl transition flex items-center">
            <div class="bg-blue-100 p-3 rounded-xl mr-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Attendances %</p>
              <p class="text-2xl font-bold text-blue-700">{{ attendancePercentage }}%</p>
              <p class="text-xs text-gray-400">Rate</p>
            </div>
          </div>

          <!-- Total Attendance -->
          <div class="bg-white p-5 rounded-2xl shadow-lg hover:shadow-xl transition flex items-center">
            <div class="bg-yellow-100 p-3 rounded-xl mr-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Totals</p>
              <p class="text-2xl font-bold text-yellow-700">{{ summary.total }}</p>
              <p class="text-xs text-gray-400">Records</p>
            </div>
          </div>

        </div>



    
        <div class="bg-white p-6 mb-6 rounded-xl shadow">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Calendar Views</h2>
          <FullCalendar :options="calendarOptions" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 mb-6 bg-white p-4 rounded-xl shadow">
          <div class="flex flex-wrap items-center gap-4">
            <div class="relative">
              <select v-model="filters.status" class="border rounded-lg p-2 pl-10 pr-4 appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Statuses for attendance</option>
                <option value="present">Presents</option>
                <option value="absent">Absent</option>
                <option value="late">Late</option>
              </select>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            
            <div class="relative">
              <select v-model="filters.subject" class="border rounded-lg p-2 pl-10 pr-4 appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Subjects</option>
                <option v-for="subject in uniqueSubjects" :value="subject" :key="subject">{{ subject }}</option>
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
            <button @click="exportToCSV" class="flex items-center gap-2 bg-indigo-100 px-4 py-2 rounded-lg text-indigo-800 hover:bg-indigo-200 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              CSV
            </button>
          </div>
        </div>


        <div class="overflow-x-auto rounded-lg shadow bg-white" ref="tableRef">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-700">
              <tr>
                <th class="px-6 py-3 text-left font-semibold">Date</th>
                <th class="px-6 py-3 text-left font-semibold">Status</th>
                <th class="px-6 py-3 text-left font-semibold">Subjects</th>
                <th class="px-6 py-3 text-left font-semibold">Time</th>
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
                    <svg v-if="record.status === 'present'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else-if="record.status === 'absent'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <svg v-else-if="record.status === 'late'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ capitalizeFirstLetter(record.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full" :class="subjectColor(record.subject_name)"></div>
                    {{ record.subject_name }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatTimeOnly(record.recorded_at) }}
                </td>
              </tr>
              <tr v-if="filteredAttendance.length === 0">
                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No attendance records found in here</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>

import { ref, computed, onMounted, nextTick } from 'vue'
import axios from 'axios'
import jsPDF from 'jspdf'
import html2canvas from 'html2canvas'
import * as XLSX from 'xlsx'


import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'


const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})


apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})


const attendance = ref([])
const loading = ref(true)
const error = ref(null)
const filters = ref({
  status: '',
  subject: '',
  date: ''
})
const tableRef = ref(null) 


const fetchAttendance = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await apiClient.get('/student/my-attendance')
    attendance.value = response.data.data
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load attendance data'
    console.error('Error fetching attendance:', err)
  } finally {
    loading.value = false
  }
}


const calendarOptions = ref({
  plugins: [dayGridPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: ''
  },
  events: computed(() => {
    return attendance.value.map(record => ({
      title: capitalizeFirstLetter(record.status) + (record.subject_name ? ` - ${record.subject_name}` : ''),
      date: record.date,
      color: {
        present: '#34D399', 
        absent: '#F87171',  
        late: '#FBBF24'     
      }[record.status] || '#9CA3AF' 
    }))
  })
})


const summary = computed(() => {
  const stats = { present: 0, absent: 0, late: 0, total: 0 }
  attendance.value.forEach(record => {
    stats[record.status]++
    stats.total++
  })
  return stats
})

const presentPercentage = computed(() => {
  return summary.value.total ? Math.round((summary.value.present / summary.value.total) * 100) : 0
})

const absentPercentage = computed(() => {
  return summary.value.total ? Math.round((summary.value.absent / summary.value.total) * 100) : 0
})

const latePercentage = computed(() => {
  return summary.value.total ? Math.round((summary.value.late / summary.value.total) * 100) : 0
})

const attendancePercentage = computed(() => {
  return summary.value.total 
    ? Math.round(((summary.value.present + summary.value.late) / summary.value.total) * 100) 
    : 0
})

const uniqueSubjects = computed(() => {
  const subjects = new Set()
  attendance.value.forEach(record => {
    if (record.subject_name) {
      subjects.add(record.subject_name)
    }
  })
  return Array.from(subjects).sort()
})

const filteredAttendance = computed(() => {
  return attendance.value.filter(record => {
    const matchesStatus = !filters.value.status || record.status === filters.value.status
    const matchesSubject = !filters.value.subject || record.subject_name === filters.value.subject
    const matchesDate = !filters.value.date || record.date === filters.value.date
    return matchesStatus && matchesSubject && matchesDate
  })
})

// Methods
const resetFilters = () => {
  filters.value = { status: '', subject: '', date: '' }
}

const statusClass = (status) => {
  return {
    'present': 'bg-green-100 text-green-800',
    'absent': 'bg-red-100 text-red-800',
    'late': 'bg-yellow-100 text-yellow-800'
  }[status] || 'bg-gray-100 text-gray-800'
}

const subjectColor = (subjectName) => {
  if (!subjectName) return 'bg-gray-300'
  
  const colors = ['blue', 'green', 'red', 'yellow', 'purple', 'pink', 'indigo']
  const hash = subjectName.split('').reduce((acc, char) => char.charCodeAt(0) + acc, 0)
  const colorIndex = hash % colors.length
  return `bg-${colors[colorIndex]}-500`
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const options = { year: 'numeric', month: 'short', day: 'numeric' }
  return new Date(dateString).toLocaleDateString(undefined, options)
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  })
}

const formatTimeOnly = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true })
}

const capitalizeFirstLetter = (string) => {
  return string.charAt(0).toUpperCase() + string.slice(1)
}


const exportToPDF = async () => {
  try {
    await nextTick();
    
    if (!tableRef.value) {
      console.error('Table element not found');
      return;
    }

    
    const pdf = new jsPDF('p', 'pt', 'a4');
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();

    // School Header
    pdf.setFontSize(10);
    pdf.setTextColor(100);
    pdf.text("www.passerellesnumeriques.org | BP 511 St. 371 Phum Tropeang Chhuk , City ZIP | +855 23 99 55 00 |", pageWidth / 2, 30, { align: 'center' });
    pdf.text("passerelles numériques cambodia", pageWidth / 2, 45, { align: 'center' });

    pdf.setFontSize(18);
    pdf.setTextColor(40);
    pdf.text("Student Attendance Report", pageWidth / 2, 80, { align: 'center' });

    // Report Info
    pdf.setFontSize(12);
    pdf.text(`Class: ${localStorage.getItem('class_name') || 'All Classes'}`, 40, 110);
    pdf.text(`Date: ${new Date().toLocaleDateString()}`, pageWidth - 40, 110, { align: 'right' });

  
    const canvas = await html2canvas(tableRef.value, {
      scale: 2,
      logging: false,
      useCORS: true,
      backgroundColor: null
    });

    const imgData = canvas.toDataURL('image/png');
    const imgWidth = pageWidth - 80;
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    
    
    const tableY = 130;
    pdf.addImage(imgData, 'PNG', 40, tableY, imgWidth, imgHeight);

    
    const footerY = tableY + imgHeight + 20; 
    
    pdf.setFontSize(10);
    pdf.setTextColor(100);
    
    pdf.text("Notes:", 40, footerY);
    pdf.text("- Absences require parental note within 3 days", 40, footerY + 15);
    pdf.text("- Late arrivals marked after 8:15 AM", 40, footerY + 30);
    pdf.text("- Report discrepancies to office within 24 hours", 40, footerY + 45);
    
    
    pdf.text(`© ${new Date().getFullYear()} passerelles numériques cambodia`, pageWidth / 2, pageHeight - 20, { align: 'center' });

    // Save PDF
    pdf.save(`attendance_${new Date().toISOString().slice(0,10)}.pdf`);
    
  } catch (err) {
    console.error('PDF generation error:', err);
    alert('Error generating PDF. Please try again.');
  }
}

// Export Excel
const exportToExcel = () => {
  if (!filteredAttendance.value.length) return alert('No data to export')
  
  const data = filteredAttendance.value.map(r => ({
    Date: formatDate(r.date),
    Status: capitalizeFirstLetter(r.status),
    Subject: r.subject_name,
    Time: formatTimeOnly(r.recorded_at)
  }))
  
  const worksheet = XLSX.utils.json_to_sheet(data)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Attendance')
  XLSX.writeFile(workbook, 'attendance.xlsx')
}

// Initialize
onMounted(() => {
  fetchAttendance()
})
const exportToCSV = () => {
  if (!filteredAttendance.value.length) return alert('No data to export')
  
  const headers = ['Date', 'Status', 'Subject', 'Time']
  const data = filteredAttendance.value.map(r => [
    formatDate(r.date),
    capitalizeFirstLetter(r.status),
    r.subject_name,
    formatTimeOnly(r.recorded_at)
  ])
  
  // Create CSV content
  let csvContent = "data:text/csv;charset=utf-8,"
    + headers.join(',') + "\n"
    + data.map(row => row.join(',')).join("\n")
  
  // Create download link
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement("a")
  link.setAttribute("href", encodedUri)
  link.setAttribute("download", `attendance_${new Date().toISOString().slice(0,10)}.csv`)
  document.body.appendChild(link)
  
  // Trigger download
  link.click()
  document.body.removeChild(link)
}
</script>

<style scoped>
/* Calendar styling */
:deep(.fc) {
  font-family: inherit;
}

:deep(.fc-header-toolbar) {
  margin-bottom: 1em;
}

:deep(.fc-daygrid-day-number) {
  color: #4b5563;
}

:deep(.fc-day-today) {
  background-color: #f0f9ff !important;
}

:deep(.fc-daygrid-event) {
  font-size: 0.8em;
  padding: 2px 4px;
  border-radius: 0.25rem;
}

:deep(.fc-daygrid-day-frame) {
  min-height: 80px;
}
</style>