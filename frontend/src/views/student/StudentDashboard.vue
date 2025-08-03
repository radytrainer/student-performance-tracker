<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 p-4 md:p-6">
    <div class="max-w-6xl mx-auto space-y-6">
      <!-- Header -->
      <div class="text-center md:text-left mb-8">
        <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-3">
          My Performance Dashboard
        </h1>
        <p class="text-gray-600 text-lg">Track Your Academic Progress</p>
      </div>

      <!-- Student Info Header -->
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
            <User class="w-8 h-8 text-white" />
          </div>
          <div>
            <h2 class="text-3xl font-bold text-gray-900">{{ studentData.name }}</h2>
            <p class="text-gray-600 text-lg">{{ studentData.course }} • {{ studentData.term }}</p>
          </div>
        </div>
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

      <!-- Performance Insights -->
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 mb-8">
        <div class="flex items-center gap-3 mb-6">
          <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg">
            <TrendingUp class="w-5 h-5 text-white" />
          </div>
          <h3 class="text-xl font-bold text-gray-900">Performance Insights</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-2 h-2 bg-green-500 rounded-full"></div>
              <h4 class="font-semibold text-green-800">Strengths</h4>
            </div>
            <p class="text-sm text-green-700">{{ topSubject.name }} ({{ topSubject.grade }}%)</p>
            <p class="text-xs text-green-600 mt-1">Keep up the excellent work!</p>
          </div>
          
          <div class="bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
              <h4 class="font-semibold text-blue-800">Overall Trend</h4>
            </div>
            <p class="text-sm text-blue-700">{{ trendDirection }} {{ Math.abs(trendValue) }}%</p>
            <p class="text-xs text-blue-600 mt-1">{{ trendMessage }}</p>
          </div>
          
          <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border border-orange-200 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
              <h4 class="font-semibold text-orange-800">Focus Area</h4>
            </div>
            <p class="text-sm text-orange-700">{{ weakestSubject.name }} ({{ weakestSubject.grade }}%)</p>
            <p class="text-xs text-orange-600 mt-1">Consider extra study time</p>
          </div>
        </div>
      </div>

      <!-- Goals & Targets -->
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 mb-8">
        <div class="flex items-center gap-3 mb-6">
          <div class="p-2 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg">
            <Target class="w-5 h-5 text-white" />
          </div>
          <h3 class="text-xl font-bold text-gray-900">Academic Goals</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div>
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Overall Grade Target</span>
                <span class="text-sm font-bold text-blue-600">85%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div 
                  class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500"
                  :style="`width: ${Math.min((studentData.averageGrade / 85) * 100, 100)}%`"
                ></div>
              </div>
              <p class="text-xs text-gray-600 mt-1">Current: {{ studentData.averageGrade }}%</p>
            </div>
            
            <div>
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Attendance Target</span>
                <span class="text-sm font-bold text-green-600">95%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div 
                  class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500"
                  :style="`width: ${Math.min((studentData.attendanceRate / 95) * 100, 100)}%`"
                ></div>
              </div>
              <p class="text-xs text-gray-600 mt-1">Current: {{ studentData.attendanceRate }}%</p>
            </div>
          </div>
          
          <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-4">
            <h4 class="font-semibold text-indigo-800 mb-3">Quick Actions</h4>
            <div class="space-y-2">
              <button class="w-full text-left px-3 py-2 bg-white/70 rounded-lg hover:bg-white transition-colors text-sm">
                📚 Review study materials
              </button>
              <button class="w-full text-left px-3 py-2 bg-white/70 rounded-lg hover:bg-white transition-colors text-sm">
                📅 Schedule study sessions
              </button>
              <button class="w-full text-left px-3 py-2 bg-white/70 rounded-lg hover:bg-white transition-colors text-sm">
                👥 Join study groups
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <!-- Monthly Progress Chart -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg">
              <TrendingUp class="w-5 h-5 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">Monthly Progress</h3>
              <p class="text-sm text-gray-600">Your grade trends over time</p>
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
              <p class="text-sm text-gray-600">Your grades by subject</p>
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
              <p class="text-sm text-gray-600">Your performance breakdown</p>
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
              <h3 class="text-xl font-bold text-gray-900">Skills Assessment</h3>
              <p class="text-sm text-gray-600">Multi-dimensional analysis</p>
            </div>
          </div>
          <div class="h-80">
            <canvas ref="radarChart" class="w-full h-full"></canvas>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
        <div class="flex items-center gap-3 mb-6">
          <div class="p-2 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg">
            <Calendar class="w-5 h-5 text-white" />
          </div>
          <h3 class="text-xl font-bold text-gray-900">Recent Activity</h3>
        </div>
        
        <div class="space-y-3">
          <div v-for="activity in recentActivities" :key="activity.id" 
               class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
            <div :class="`p-2 rounded-lg ${activity.color}`">
              <component :is="activity.icon" class="w-4 h-4 text-white" />
            </div>
            <div class="flex-1">
              <p class="font-medium text-gray-900">{{ activity.title }}</p>
              <p class="text-sm text-gray-600">{{ activity.description }}</p>
            </div>
            <span class="text-xs text-gray-500">{{ activity.time }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { 
  Users, TrendingUp, Calendar, AlertTriangle, Filter, Search, X,
  BarChart3, PieChart, User, Activity, Target, BookOpen, Award, Clock
} from 'lucide-vue-next'

// Student data (this would typically come from an API based on the logged-in student)
const studentData = ref({
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
})

// Recent activities
const recentActivities = ref([
  {
    id: 1,
    title: "Assignment Submitted",
    description: "Database Design Project submitted",
    time: "2 hours ago",
    icon: BookOpen,
    color: "bg-blue-500"
  },
  {
    id: 2,
    title: "Quiz Completed",
    description: "Mathematics Quiz - Score: 92%",
    time: "1 day ago",
    icon: Award,
    color: "bg-green-500"
  },
  {
    id: 3,
    title: "Class Attended",
    description: "Programming Fundamentals",
    time: "2 days ago",
    icon: Clock,
    color: "bg-purple-500"
  }
])

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
const kpiData = computed(() => {
  const totalSubjects = studentData.value.subjects.length
  const bestGrade = Math.max(...studentData.value.subjects.map(s => s.grade))
  
  return [
    {
      title: 'Overall Grade',
      value: `${studentData.value.averageGrade}%`,
      icon: TrendingUp,
      gradient: 'from-blue-500 to-blue-600',
      textColor: 'text-blue-600',
      badgeColor: 'bg-blue-100 text-blue-700',
      change: '+3.2%'
    },
    {
      title: 'Attendance Rate',
      value: `${studentData.value.attendanceRate}%`,
      icon: Calendar,
      gradient: 'from-green-500 to-green-600',
      textColor: 'text-green-600',
      badgeColor: 'bg-green-100 text-green-700',
      change: '+1.5%'
    },
    {
      title: 'Best Subject',
      value: `${bestGrade}%`,
      icon: Award,
      gradient: 'from-purple-500 to-purple-600',
      textColor: 'text-purple-600',
      badgeColor: 'bg-purple-100 text-purple-700',
      change: '+2.1%'
    },
    {
      title: 'Total Subjects',
      value: totalSubjects,
      icon: BookOpen,
      gradient: 'from-orange-500 to-orange-600',
      textColor: 'text-orange-600',
      badgeColor: 'bg-orange-100 text-orange-700',
      change: 'Active'
    }
  ]
})

const topSubject = computed(() => {
  return studentData.value.subjects.reduce((prev, current) => 
    (prev.grade > current.grade) ? prev : current
  )
})

const weakestSubject = computed(() => {
  return studentData.value.subjects.reduce((prev, current) => 
    (prev.grade < current.grade) ? prev : current
  )
})

const trendDirection = computed(() => {
  const grades = studentData.value.monthlyGrades
  const lastGrade = grades[grades.length - 1].grade
  const previousGrade = grades[grades.length - 2].grade
  return lastGrade > previousGrade ? 'Improving by' : 'Declining by'
})

const trendValue = computed(() => {
  const grades = studentData.value.monthlyGrades
  const lastGrade = grades[grades.length - 1].grade
  const previousGrade = grades[grades.length - 2].grade
  return Math.round(((lastGrade - previousGrade) / previousGrade) * 100)
})

const trendMessage = computed(() => {
  return trendDirection.value.includes('Improving') ? 
    'Great progress this month!' : 'Focus on improvement areas'
})

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
      labels: studentData.value.monthlyGrades.map(item => item.month),
      datasets: [{
        label: 'Your Grade Progress',
        data: studentData.value.monthlyGrades.map(item => item.grade),
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
      labels: studentData.value.subjects.map(subject => subject.name),
      datasets: [{
        label: 'Your Grades',
        data: studentData.value.subjects.map(subject => subject.grade),
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
      labels: studentData.value.subjects.map(subject => subject.name),
      datasets: [{
        data: studentData.value.subjects.map(subject => subject.grade),
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
      labels: studentData.value.subjects.map(subject => subject.name),
      datasets: [{
        label: 'Your Performance',
        data: studentData.value.subjects.map(subject => subject.grade),
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
</script>

<style scoped>
.bg-clip-text {
  -webkit-background-clip: text;
  background-clip: text;
}
</style>
