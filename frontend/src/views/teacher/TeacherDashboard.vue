<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 p-4 md:p-6">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="text-center md:text-left mb-8">
        <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-3">
          Analytics Dashboard
        </h1>
        <p class="text-gray-600 text-lg">Professional Student Performance Analytics</p>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div
          v-for="(kpi, index) in kpiData"
          :key="index"
          class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-white/20"
        >
          <div class="flex items-center justify-between mb-4">
            <div :class="`p-3 rounded-xl bg-gradient-to-br ${kpi.gradient} shadow-lg`">
              <component :is="kpi.icon" class="w-6 h-6 text-white" />
            </div>
            <div :class="`px-3 py-1 rounded-full text-xs font-medium ${kpi.badgeColor}`">
              {{ kpi.change }}
            </div>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600 mb-1">{{ kpi.title }}</p>
            <p :class="`text-3xl font-bold ${kpi.textColor}`">{{ kpi.value }}</p>
          </div>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 mb-8">
        <div class="flex items-center gap-3 mb-4">
          <div class="p-2 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg">
            <Filter class="w-5 h-5 text-white" />
          </div>
          <h3 class="text-xl font-bold text-gray-900">Smart Filters</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              v-model="filters.name"
              @input="applyFilters"
              placeholder="Search students..."
              class="w-full pl-12 pr-4 py-3 bg-white/70 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            />
          </div>
          
          <input
            v-model="filters.subject"
            @input="applyFilters"
            placeholder="Filter by subject..."
            class="w-full px-4 py-3 bg-white/70 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
          
          <input
            v-model="filters.term"
            @input="applyFilters"
            placeholder="Filter by term..."
            class="w-full px-4 py-3 bg-white/70 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
          
          <button
            @click="clearFilters"
            :disabled="!hasActiveFilters"
            class="px-6 py-3 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-xl hover:from-red-600 hover:to-pink-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 font-medium"
          >
            <X class="w-4 h-4 inline mr-2" />
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Performance Alerts -->
      <div v-if="alertStudents.length > 0" class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 mb-8">
        <div class="flex items-center gap-3 mb-6">
          <div class="p-2 bg-gradient-to-br from-red-500 to-orange-500 rounded-lg">
            <AlertTriangle class="w-5 h-5 text-white" />
          </div>
          <h3 class="text-xl font-bold text-red-600">Performance Alerts</h3>
          <div class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
            {{ alertStudents.length }} Active
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="student in alertStudents"
            :key="student.id"
            class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-xl p-4 hover:shadow-md transition-all"
          >
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-semibold text-gray-900">{{ student.name }}</h4>
              <div class="flex gap-2">
                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-medium">
                  Grade: {{ student.averageGrade }}%
                </span>
                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-lg text-xs font-medium">
                  Attendance: {{ student.attendanceRate }}%
                </span>
              </div>
            </div>
            <p class="text-sm text-gray-600">{{ student.course }}</p>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Student Selector -->
        <div class="lg:col-span-1">
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
            <div class="flex items-center gap-3 mb-6">
              <div class="p-2 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg">
                <Users class="w-5 h-5 text-white" />
              </div>
              <h3 class="text-xl font-bold text-gray-900">Students</h3>
              <div class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                {{ filteredStudents.length }}
              </div>
            </div>
            
            <div class="space-y-3">
              <button
                v-for="student in filteredStudents"
                :key="student.id"
                @click="selectedStudent = student"
                :class="`w-full p-4 rounded-xl text-left transition-all duration-300 ${
                  selectedStudent.id === student.id
                    ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg'
                    : 'bg-gray-50 hover:bg-gray-100 text-gray-900'
                }`"
              >
                <div class="flex items-center justify-between mb-2">
                  <span class="font-semibold">{{ student.name }}</span>
                  <div :class="`px-2 py-1 rounded-lg text-xs font-medium ${getStatusColor(student)}`">
                    {{ getStatusText(student) }}
                  </div>
                </div>
                <div class="text-sm opacity-80">
                  <div>{{ student.course }}</div>
                  <div class="flex justify-between mt-1">
                    <span>Grade: {{ student.averageGrade }}%</span>
                    <span>Attendance: {{ student.attendanceRate }}%</span>
                  </div>
                </div>
              </button>
            </div>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="lg:col-span-3 space-y-8">
          <!-- Student Info Header -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                <User class="w-8 h-8 text-white" />
              </div>
              <div>
                <h2 class="text-3xl font-bold text-gray-900">{{ selectedStudent.name }}</h2>
                <p class="text-gray-600 text-lg">{{ selectedStudent.course }} • {{ selectedStudent.term }}</p>
              </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                <div class="text-2xl font-bold text-blue-700">{{ selectedStudent.averageGrade }}%</div>
                <div class="text-sm text-blue-600 font-medium">Average Grade</div>
              </div>
              <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl">
                <div class="text-2xl font-bold text-green-700">{{ selectedStudent.attendanceRate }}%</div>
                <div class="text-sm text-green-600 font-medium">Attendance</div>
              </div>
              <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl">
                <div class="text-2xl font-bold text-purple-700">{{ selectedStudent.subjects.length }}</div>
                <div class="text-sm text-purple-600 font-medium">Subjects</div>
              </div>
              <div class="text-center p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl">
                <div class="text-2xl font-bold text-orange-700">{{ Math.max(...selectedStudent.subjects.map(s => s.grade)) }}%</div>
                <div class="text-sm text-orange-600 font-medium">Best Score</div>
              </div>
            </div>
          </div>

          <!-- Professional Charts Grid -->
          <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Monthly Progress Chart -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
              <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg">
                  <TrendingUp class="w-5 h-5 text-white" />
                </div>
                <div>
                  <h3 class="text-xl font-bold text-gray-900">Monthly Progress</h3>
                  <p class="text-sm text-gray-600">Grade trends over time</p>
                </div>
              </div>
              <div class="h-80">
                <canvas ref="lineChart" class="w-full h-full"></canvas>
              </div>
            </div>

            <!-- Subject Performance Chart -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
              <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg">
                  <BarChart3 class="w-5 h-5 text-white" />
                </div>
                <div>
                  <h3 class="text-xl font-bold text-gray-900">Subject Performance</h3>
                  <p class="text-sm text-gray-600">Grades by subject</p>
                </div>
              </div>
              <div class="h-80">
                <canvas ref="barChart" class="w-full h-full"></canvas>
              </div>
            </div>

            <!-- Grade Distribution Chart -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
              <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg">
                  <PieChart class="w-5 h-5 text-white" />
                </div>
                <div>
                  <h3 class="text-xl font-bold text-gray-900">Grade Distribution</h3>
                  <p class="text-sm text-gray-600">Performance breakdown</p>
                </div>
              </div>
              <div class="h-80">
                <canvas ref="pieChart" class="w-full h-full"></canvas>
              </div>
            </div>

            <!-- Performance Radar Chart -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
              <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-lg">
                  <Activity class="w-5 h-5 text-white" />
                </div>
                <div>
                  <h3 class="text-xl font-bold text-gray-900">Performance Radar</h3>
                  <p class="text-sm text-gray-600">Multi-dimensional analysis</p>
                </div>
              </div>
              <div class="h-80">
                <canvas ref="radarChart" class="w-full h-full"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { 
  Users, TrendingUp, Calendar, AlertTriangle, Filter, Search, X, 
  BarChart3, PieChart, User, Activity 
} from 'lucide-vue-next'

// Mock data
const studentsData = ref([
  {
    id: 1,
    name: "Sarah Johnson",
    course: "Computer Science",
    term: "Fall 2024",
    averageGrade: 88,
    attendanceRate: 94,
    subjects: [
      { name: "Mathematics", grade: 92 },
      { name: "Programming", grade: 89 },
      { name: "Physics", grade: 85 },
      { name: "English", grade: 87 },
      { name: "Database", grade: 90 }
    ],
    monthlyGrades: [
      { month: "Sep", grade: 85 },
      { month: "Oct", grade: 87 },
      { month: "Nov", grade: 89 },
      { month: "Dec", grade: 88 }
    ]
  },
  {
    id: 2,
    name: "Michael Chen",
    course: "Engineering",
    term: "Fall 2024",
    averageGrade: 72,
    attendanceRate: 78,
    subjects: [
      { name: "Mathematics", grade: 75 },
      { name: "Physics", grade: 68 },
      { name: "Chemistry", grade: 74 },
      { name: "Engineering", grade: 71 },
      { name: "Materials", grade: 72 }
    ],
    monthlyGrades: [
      { month: "Sep", grade: 74 },
      { month: "Oct", grade: 72 },
      { month: "Nov", grade: 71 },
      { month: "Dec", grade: 72 }
    ]
  },
  {
    id: 3,
    name: "Emma Davis",
    course: "Business",
    term: "Fall 2024",
    averageGrade: 91,
    attendanceRate: 97,
    subjects: [
      { name: "Economics", grade: 93 },
      { name: "Marketing", grade: 89 },
      { name: "Finance", grade: 92 },
      { name: "Management", grade: 90 },
      { name: "Statistics", grade: 91 }
    ],
    monthlyGrades: [
      { month: "Sep", grade: 89 },
      { month: "Oct", grade: 91 },
      { month: "Nov", grade: 93 },
      { month: "Dec", grade: 91 }
    ]
  },
  {
    id: 4,
    name: "James Wilson",
    course: "Psychology",
    term: "Fall 2024",
    averageGrade: 69,
    attendanceRate: 65,
    subjects: [
      { name: "Psychology", grade: 72 },
      { name: "Statistics", grade: 65 },
      { name: "Research", grade: 68 },
      { name: "Biology", grade: 71 },
      { name: "Sociology", grade: 69 }
    ],
    monthlyGrades: [
      { month: "Sep", grade: 71 },
      { month: "Oct", grade: 69 },
      { month: "Nov", grade: 67 },
      { month: "Dec", grade: 69 }
    ]
  }
])

// Reactive data
const filters = ref({
  name: '',
  subject: '',
  term: ''
})

const selectedStudent = ref(studentsData.value[0])

// Chart refs
const lineChart = ref(null)
const barChart = ref(null)
const pieChart = ref(null)
const radarChart = ref(null)

// Chart instances
let lineChartInstance = null
let barChartInstance = null
let pieChartInstance = null
let radarChartInstance = null

// Computed properties
const filteredStudents = computed(() => {
  return studentsData.value.filter(student => {
    const nameMatch = !filters.value.name || student.name.toLowerCase().includes(filters.value.name.toLowerCase())
    const subjectMatch = !filters.value.subject || student.subjects.some(subject => 
      subject.name.toLowerCase().includes(filters.value.subject.toLowerCase())
    )
    const termMatch = !filters.value.term || student.term.toLowerCase().includes(filters.value.term.toLowerCase())
    
    return nameMatch && subjectMatch && termMatch
  })
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

// Methods
const applyFilters = () => {
  // Filters are reactive, so this will automatically update filteredStudents
}

const clearFilters = () => {
  filters.value = {
    name: '',
    subject: '',
    term: ''
  }
}

const getStatusColor = (student) => {
  if (student.averageGrade < 75 || student.attendanceRate < 80) {
    return 'bg-red-100 text-red-700'
  }
  if (student.averageGrade >= 85 && student.attendanceRate >= 90) {
    return 'bg-green-100 text-green-700'
  }
  return 'bg-blue-100 text-blue-700'
}

const getStatusText = (student) => {
  if (student.averageGrade < 75 || student.attendanceRate < 80) return 'At Risk'
  if (student.averageGrade >= 85 && student.attendanceRate >= 90) return 'Excellent'
  return 'Good'
}

// Chart creation functions
const createLineChart = async () => {
  if (!lineChart.value) return
  
  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)
  
  if (lineChartInstance) {
    lineChartInstance.destroy()
  }
  
  const ctx = lineChart.value.getContext('2d')
  lineChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: selectedStudent.value.monthlyGrades.map(item => item.month),
      datasets: [{
        label: 'Grade Progress',
        data: selectedStudent.value.monthlyGrades.map(item => item.grade),
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderWidth: 4,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: 'rgb(59, 130, 246)',
        pointBorderColor: '#fff',
        pointBorderWidth: 3,
        pointRadius: 8,
        pointHoverRadius: 12
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          grid: {
            color: 'rgba(0, 0, 0, 0.05)'
          },
          ticks: {
            font: {
              size: 12,
              weight: '500'
            }
          }
        },
        x: {
          grid: {
            display: false
          },
          ticks: {
            font: {
              size: 12,
              weight: '500'
            }
          }
        }
      },
      elements: {
        point: {
          hoverBackgroundColor: 'rgb(59, 130, 246)'
        }
      }
    }
  })
}

const createBarChart = async () => {
  if (!barChart.value) return
  
  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)
  
  if (barChartInstance) {
    barChartInstance.destroy()
  }
  
  const ctx = barChart.value.getContext('2d')
  const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
  
  barChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: selectedStudent.value.subjects.map(subject => subject.name),
      datasets: [{
        label: 'Grade',
        data: selectedStudent.value.subjects.map(subject => subject.grade),
        backgroundColor: colors.map(color => color + '20'),
        borderColor: colors,
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          grid: {
            color: 'rgba(0, 0, 0, 0.05)'
          },
          ticks: {
            font: {
              size: 12,
              weight: '500'
            }
          }
        },
        x: {
          grid: {
            display: false
          },
          ticks: {
            font: {
              size: 11,
              weight: '500'
            },
            maxRotation: 45
          }
        }
      }
    }
  })
}

const createPieChart = async () => {
  if (!pieChart.value) return
  
  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)
  
  if (pieChartInstance) {
    pieChartInstance.destroy()
  }
  
  const ctx = pieChart.value.getContext('2d')
  const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
  
  pieChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: selectedStudent.value.subjects.map(subject => subject.name),
      datasets: [{
        data: selectedStudent.value.subjects.map(subject => subject.grade),
        backgroundColor: colors,
        borderColor: '#fff',
        borderWidth: 4,
        hoverBorderWidth: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '60%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            padding: 20,
            usePointStyle: true,
            font: {
              size: 12,
              weight: '500'
            }
          }
        }
      }
    }
  })
}

const createRadarChart = async () => {
  if (!radarChart.value) return
  
  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)
  
  if (radarChartInstance) {
    radarChartInstance.destroy()
  }
  
  const ctx = radarChart.value.getContext('2d')
  
  radarChartInstance = new Chart(ctx, {
    type: 'radar',
    data: {
      labels: selectedStudent.value.subjects.map(subject => subject.name),
      datasets: [{
        label: 'Performance',
        data: selectedStudent.value.subjects.map(subject => subject.grade),
        borderColor: 'rgb(139, 92, 246)',
        backgroundColor: 'rgba(139, 92, 246, 0.2)',
        borderWidth: 3,
        pointBackgroundColor: 'rgb(139, 92, 246)',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        r: {
          beginAtZero: true,
          max: 100,
          grid: {
            color: 'rgba(0, 0, 0, 0.1)'
          },
          angleLines: {
            color: 'rgba(0, 0, 0, 0.1)'
          },
          pointLabels: {
            font: {
              size: 11,
              weight: '500'
            }
          }
        }
      }
    }
  })
}

const updateCharts = async () => {
  await nextTick()
  createLineChart()
  createBarChart()
  createPieChart()
  createRadarChart()
}

// Lifecycle
onMounted(() => {
  updateCharts()
})

watch(selectedStudent, () => {
  updateCharts()
}, { deep: true })
</script>

<style scoped>
.bg-clip-text {
  -webkit-background-clip: text;
  background-clip: text;
}
</style>