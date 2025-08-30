<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 p-4 md:p-6">
    <div class="max-w-7xl mx-auto space-y-6">
       
      <Header 
        :current-time="currentTime"
        :is-refreshing="isRefreshing"
        @refresh="refreshData"
      />

      
      <KpiCards :kpi-data="kpiData" />

       
      <FilterBar
        v-model:filters="filters"
        :filtered-count="filteredStudents.length"
        :available-subjects="availableSubjects"
        :has-active-filters="hasActiveFilters"
        @clear-filters="clearFilters"
      />

       
      <Alerts
        v-if="alertStudents.length > 0"
        :alert-students="alertStudents"
        @select-student="selectedStudent = $event"
      />

       
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
         
        <div class="lg:col-span-1">
          <StudentSelector
            :students="filteredStudents"
            :selected-student="selectedStudent"
            @select-student="selectedStudent = $event"
          />
        </div>

         
        <div class="lg:col-span-3 space-y-8">
           
          <StudentInfo
            :student="selectedStudent"
            @export="exportStudentData"
          />

           
          <Charts
            :student="selectedStudent"
            :pie-chart-type="pieChartType"
            @export-chart="exportChart"
            @sort-subjects="sortSubjects"
            @toggle-chart-type="toggleChartType"
          />
        </div>
      </div>

       
      <div v-if="notification.show"
           class="fixed bottom-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg transition-all duration-300 z-50">
        {{ notification.message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import Header from '@/components/teacher/Dashboard/Header.vue'
import KpiCards from '@/components/teacher/Dashboard/KpiCards.vue'
import FilterBar from '@/components/teacher/Dashboard/FilterBar.vue'
import Alerts from '@/components/teacher/Dashboard/Alerts.vue'
import StudentSelector from '@/components/teacher/Dashboard/StudentSelector.vue'
import StudentInfo from '@/components/teacher/Dashboard/StudentInfo.vue'
import Charts from '@/components/teacher/Dashboard/Charts.vue'

// Sample students data - EXACT SAME AS ORIGINAL
const studentsData = ref([
  {
    id: 1,
    name: "Alex Johnson",
    course: "Computer Science",
    term: "Fall 2024",
    averageGrade: 85,
    attendanceRate: 92,
    subjects: [
      { name: 'Mathematics', grade: 88 },
      { name: 'Physics', grade: 82 },
      { name: 'Programming', grade: 95 },
      { name: 'English', grade: 78 },
      { name: 'Chemistry', grade: 85 }
    ],
    monthlyGrades: [
      { month: 'Sep', grade: 80 },
      { month: 'Oct', grade: 82 },
      { month: 'Nov', grade: 85 },
      { month: 'Dec', grade: 87 },
      { month: 'Jan', grade: 85 },
      { month: 'Feb', grade: 90 }
    ]
  },
  {
    id: 2,
    name: "Sarah Williams",
    course: "Biology",
    term: "Fall 2024",
    averageGrade: 72,
    attendanceRate: 78,
    subjects: [
      { name: 'Biology', grade: 75 },
      { name: 'Chemistry', grade: 68 },
      { name: 'Mathematics', grade: 70 },
      { name: 'English', grade: 76 },
      { name: 'Physics', grade: 71 }
    ],
    monthlyGrades: [
      { month: 'Sep', grade: 75 },
      { month: 'Oct', grade: 73 },
      { month: 'Nov', grade: 70 },
      { month: 'Dec', grade: 72 },
      { month: 'Jan', grade: 74 },
      { month: 'Feb', grade: 76 }
    ]
  },
  {
    id: 3,
    name: "Michael Chen",
    course: "Engineering",
    term: "Fall 2024",
    averageGrade: 91,
    attendanceRate: 95,
    subjects: [
      { name: 'Mathematics', grade: 94 },
      { name: 'Physics', grade: 92 },
      { name: 'Engineering', grade: 96 },
      { name: 'Chemistry', grade: 88 },
      { name: 'English', grade: 85 }
    ],
    monthlyGrades: [
      { month: 'Sep', grade: 88 },
      { month: 'Oct', grade: 90 },
      { month: 'Nov', grade: 91 },
      { month: 'Dec', grade: 93 },
      { month: 'Jan', grade: 91 },
      { month: 'Feb', grade: 94 }
    ]
  },
  {
    id: 4,
    name: "Emma Davis",
    course: "Literature",
    term: "Fall 2024",
    averageGrade: 88,
    attendanceRate: 89,
    subjects: [
      { name: 'English', grade: 92 },
      { name: 'History', grade: 86 },
      { name: 'Philosophy', grade: 90 },
      { name: 'Art', grade: 85 },
      { name: 'Psychology', grade: 87 }
    ],
    monthlyGrades: [
      { month: 'Sep', grade: 85 },
      { month: 'Oct', grade: 87 },
      { month: 'Nov', grade: 88 },
      { month: 'Dec', grade: 90 },
      { month: 'Jan', grade: 88 },
      { month: 'Feb', grade: 92 }
    ]
  },
  {
    id: 5,
    name: "James Wilson",
    course: "Mathematics",
    term: "Fall 2024",
    averageGrade: 68,
    attendanceRate: 72,
    subjects: [
      { name: 'Mathematics', grade: 65 },
      { name: 'Statistics', grade: 70 },
      { name: 'Physics', grade: 68 },
      { name: 'Computer Science', grade: 72 },
      { name: 'English', grade: 65 }
    ],
    monthlyGrades: [
      { month: 'Sep', grade: 70 },
      { month: 'Oct', grade: 68 },
      { month: 'Nov', grade: 66 },
      { month: 'Dec', grade: 68 },
      { month: 'Jan', grade: 70 },
      { month: 'Feb', grade: 72 }
    ]
  }
])

// State - EXACT SAME AS ORIGINAL
const currentTime = ref('')
const isRefreshing = ref(false)
const pieChartType = ref('doughnut')
const notification = ref({ show: false, message: '' })
const filters = ref({
  name: '',
  subject: '',
  performance: ''
})
const selectedStudent = ref(studentsData.value[0])

// Time update - EXACT SAME AS ORIGINAL
const updateTime = () => {
  currentTime.value = new Date().toLocaleString()
}

// Computed properties - EXACT SAME AS ORIGINAL
const filteredStudents = computed(() => {
  return studentsData.value.filter(student => {
    const nameMatch = !filters.value.name || student.name.toLowerCase().includes(filters.value.name.toLowerCase())
    const subjectMatch = !filters.value.subject || student.subjects.some(subject =>
      subject.name.toLowerCase().includes(filters.value.subject.toLowerCase())
    )
    const performanceMatch = !filters.value.performance || getPerformanceLevel(student) === filters.value.performance
    return nameMatch && subjectMatch && performanceMatch
  })
})

const availableSubjects = computed(() => {
  const subjects = new Set()
  studentsData.value.forEach(student => {
    student.subjects.forEach(subject => subjects.add(subject.name))
  })
  return Array.from(subjects).sort()
})

const kpiData = computed(() => {
  const total = filteredStudents.value.length
  const avgGrade = total > 0 ? Math.round(filteredStudents.value.reduce((sum, s) => sum + s.averageGrade, 0) / total) : 0
  const avgAttendance = total > 0 ? Math.round(filteredStudents.value.reduce((sum, s) => sum + s.attendanceRate, 0) / total) : 0
  const alerts = filteredStudents.value.filter(s => s.averageGrade < 75 || s.attendanceRate < 80).length

  return [
    {
      title: 'Total Students',
      value: total,
      icon: 'Users',
      gradient: 'from-blue-500 to-blue-600',
      textColor: 'text-blue-600',
      badgeColor: 'bg-blue-100 text-blue-700',
      change: '+12%'
    },
    {
      title: 'Average Grade',
      value: `${avgGrade}%`,
      icon: 'TrendingUp',
      gradient: 'from-green-500 to-green-600',
      textColor: 'text-green-600',
      badgeColor: 'bg-green-100 text-green-700',
      change: '+5.2%'
    },
    {
      title: 'Attendance Rate',
      value: `${avgAttendance}%`,
      icon: 'Calendar',
      gradient: 'from-purple-500 to-purple-600',
      textColor: 'text-purple-600',
      badgeColor: 'bg-purple-100 text-purple-700',
      change: '-2.1%'
    },
    {
      title: 'Performance Alerts',
      value: alerts,
      icon: 'AlertTriangle',
      gradient: 'from-red-500 to-red-600',
      textColor: 'text-red-600',
      badgeColor: 'bg-red-100 text-red-700',
      change: '-15%'
    }
  ]
})

const alertStudents = computed(() => {
  return filteredStudents.value.filter(student =>
    student.averageGrade < 75 || student.attendanceRate < 80
  )
})

const hasActiveFilters = computed(() => {
  return Object.values(filters.value).some(value => value !== '')
})

// Methods - EXACT SAME AS ORIGINAL
const refreshData = async () => {
  isRefreshing.value = true
  showNotification('Refreshing data...')
  
  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 1500))
  
  // Randomly update some values
  studentsData.value.forEach(student => {
    const variation = (Math.random() - 0.5) * 4
    student.averageGrade = Math.max(0, Math.min(100, student.averageGrade + variation))
    student.attendanceRate = Math.max(0, Math.min(100, student.attendanceRate + (Math.random() - 0.5) * 6))
  })
  
  isRefreshing.value = false
  showNotification('Data updated successfully')
}

const exportStudentData = () => {
  const data = JSON.stringify(selectedStudent.value, null, 2)
  const blob = new Blob([data], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `${selectedStudent.value.name}_data.json`
  a.click()
  showNotification('Student data exported')
}

const exportChart = (chartType) => {
  showNotification(`${chartType} chart exported`)
}

const sortSubjects = () => {
  selectedStudent.value.subjects.sort((a, b) => b.grade - a.grade)
  showNotification('Subjects sorted by grade')
}

const toggleChartType = () => {
  pieChartType.value = pieChartType.value === 'doughnut' ? 'pie' : 'doughnut'
  showNotification(`Switched to ${pieChartType.value} chart`)
}

const clearFilters = () => {
  filters.value = {
    name: '',
    subject: '',
    performance: ''
  }
  showNotification('Filters cleared')
}

const showNotification = (message) => {
  notification.value = { show: true, message }
  setTimeout(() => {
    notification.value.show = false
  }, 3000)
}

// Utility methods - EXACT SAME AS ORIGINAL
const getPerformanceLevel = (student) => {
  if (student.averageGrade < 75 || student.attendanceRate < 80) return 'at-risk'
  if (student.averageGrade >= 85 && student.attendanceRate >= 90) return 'excellent'
  return 'good'
}

// Lifecycle - EXACT SAME AS ORIGINAL
onMounted(() => {
  updateTime()
  setInterval(updateTime, 1000)
})

watch(filteredStudents, () => {
  if (selectedStudent.value && !filteredStudents.value.some(s => s.id === selectedStudent.value.id)) {
    selectedStudent.value = filteredStudents.value[0] || null
  }
})
</script>

<style scoped>
.bg-clip-text {
  -webkit-background-clip: text;
  background-clip: text;
}
  
</style>