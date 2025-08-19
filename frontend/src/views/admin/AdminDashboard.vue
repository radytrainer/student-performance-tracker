<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 p-4 md:p-6">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="text-center md:text-left mb-8">
        <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-3">
          Admin Dashboard
        </h1>
        <p class="text-gray-600 text-lg">System Overview & Management Console</p>
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

      <!-- Charts Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
        <!-- User Growth Chart -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg">
              <TrendingUp class="w-5 h-5 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">User Growth</h3>
              <p class="text-sm text-gray-600">Monthly user registration trends</p>
            </div>
          </div>
          <div class="h-80">
            <canvas ref="userGrowthChart" class="w-full h-full"></canvas>
          </div>
        </div>

        <!-- Course Performance Chart -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg">
              <BarChart3 class="w-5 h-5 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">Course Performance</h3>
              <p class="text-sm text-gray-600">Average grades by course</p>
            </div>
          </div>
          <div class="h-80">
            <canvas ref="coursePerformanceChart" class="w-full h-full"></canvas>
          </div>
        </div>

        <!-- User Distribution Chart -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg">
              <PieChart class="w-5 h-5 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">User Distribution</h3>
              <p class="text-sm text-gray-600">Users by type and status</p>
            </div>
          </div>
          <div class="h-80">
            <canvas ref="userDistributionChart" class="w-full h-full"></canvas>
          </div>
        </div>

        <!-- System Activity Chart -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-lg">
              <Activity class="w-5 h-5 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">System Activity</h3>
              <p class="text-sm text-gray-600">Daily active users</p>
            </div>
          </div>
          <div class="h-80">
            <canvas ref="activityChart" class="w-full h-full"></canvas>
          </div>
        </div>
      </div>

      <!-- System Health & Alerts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- System Health -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg">
              <Activity class="w-5 h-5 text-white" />
            </div>
            <h3 class="text-xl font-bold text-gray-900">System Health</h3>
            <div class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
              Operational
            </div>
          </div>
          <div class="space-y-4">
            <div v-for="metric in systemHealth" :key="metric.name" class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div :class="`w-3 h-3 rounded-full ${getStatusColor(metric.status)}`"></div>
                <span class="font-medium text-gray-700">{{ metric.name }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">{{ metric.value }}</span>
                <div class="w-20 bg-gray-200 rounded-full h-2">
                  <div
                    :class="`h-2 rounded-full ${getStatusColor(metric.status)}`"
                    :style="`width: ${metric.percentage}%`"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Alerts -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-gradient-to-br from-red-500 to-orange-500 rounded-lg">
              <AlertTriangle class="w-5 h-5 text-white" />
            </div>
            <h3 class="text-xl font-bold text-gray-900">System Alerts</h3>
            <div class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
              {{ systemAlerts.length }} Active
            </div>
          </div>
          <div class="space-y-3 max-h-64 overflow-y-auto">
            <div
              v-for="alert in systemAlerts"
              :key="alert.id"
              :class="`p-3 rounded-xl border-l-4 ${getSeverityStyles(alert.severity)}`"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="font-medium text-gray-900">{{ alert.title }}</span>
                <span class="text-xs text-gray-500">{{ alert.time }}</span>
              </div>
              <p class="text-sm text-gray-600">{{ alert.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- User Overview -->
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
        <div class="flex items-center gap-3 mb-6">
          <div class="p-2 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg">
            <Users class="w-5 h-5 text-white" />
          </div>
          <h3 class="text-xl font-bold text-gray-900">User Overview</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div v-for="userType in userTypes" :key="userType.type" class="p-4 bg-gray-50 rounded-xl">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <component :is="userType.icon" class="w-4 h-4 text-gray-600" />
                <span class="font-medium text-gray-700">{{ userType.label }}</span>
              </div>
              <span class="text-lg font-bold text-gray-900">{{ userType.count }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full transition-all duration-500"
                :style="`width: ${(userType.count / totalUsers) * 100}%`"
              ></div>
            </div>
            <div class="flex justify-between text-xs text-gray-600 mt-1">
              <span>Active: {{ userType.active }}</span>
              <span>{{ Math.round((userType.count / totalUsers) * 100) }}%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import {
  Users, TrendingUp, AlertTriangle, BarChart3, PieChart, Activity, BookOpen, Shield, Database
} from 'lucide-vue-next'

// --- Admin Data ---
const adminData = ref({
  totalUsers: 0,
  totalStudents: 0,
  totalTeachers: 0,
  totalAdmins: 0,
  activeCourses: 0,
  totalClasses: 0,
  totalAttendance: 0,
  userGrowth: [],
  coursePerformance: [],
  activity: []
})

// --- Fetch from API ---
const fetchAdminStats = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/admin/stats')
    const data = await res.json()
    adminData.value = {
      totalUsers: data.totalUsers || 0,
      totalStudents: data.totalStudents || 0,
      totalTeachers: data.totalTeachers || 0,
      totalAdmins: data.totalAdmins || 0,
      activeCourses: data.totalSubjects || 0,
      totalClasses: data.totalClasses || 0,
      totalAttendance: data.totalAttendance || 0,
      userGrowth: data.userGrowth || [],
      coursePerformance: data.coursePerformance || [],
      activity: data.activity || []
    }
  } catch (error) {
    console.error('Failed to fetch admin stats:', error)
  }
}

// --- KPI Boxes ---
const kpiData = computed(() => [
  {
    title: 'Total Users',
    value: adminData.value.totalUsers.toLocaleString(),
    icon: Users,
    gradient: 'from-blue-500 to-blue-600',
    textColor: 'text-blue-600',
    badgeColor: 'bg-blue-100 text-blue-700',
    change: '+12%'
  },
  {
    title: 'Total Subjects',
    value: adminData.value.activeCourses,
    icon: BookOpen,
    gradient: 'from-green-500 to-green-600',
    textColor: 'text-green-600',
    badgeColor: 'bg-green-100 text-green-700',
    change: '+8%'
  },
  {
    title: 'Total Classes',
    value: adminData.value.totalClasses,
    icon: BarChart3,
    gradient: 'from-purple-500 to-purple-600',
    textColor: 'text-purple-600',
    badgeColor: 'bg-purple-100 text-purple-700',
    change: '+0%'
  },
  {
    title: 'Total Attendances',
    value: adminData.value.totalAttendance,
    icon: Activity,
    gradient: 'from-orange-500 to-orange-600',
    textColor: 'text-orange-600',
    badgeColor: 'bg-orange-100 text-orange-700',
    change: '+5%'
  }
])

// --- Other Data ---
const systemHealth = ref([
  { name: 'CPU Usage', value: '45%', percentage: 45, status: 'good' },
  { name: 'Memory Usage', value: '67%', percentage: 67, status: 'warning' },
  { name: 'Disk Space', value: '23%', percentage: 23, status: 'good' },
  { name: 'Network', value: '12%', percentage: 12, status: 'good' },
  { name: 'Database', value: '89%', percentage: 89, status: 'warning' }
])
const systemAlerts = ref([
  { id: 1, title: 'High Server Load', description: 'Server CPU usage is above 85%', severity: 'high', time: '5 min ago' },
  { id: 2, title: 'Database Backup Completed', description: 'Daily backup completed successfully', severity: 'low', time: '1 hour ago' },
  { id: 3, title: 'New User Registrations Spike', description: '50+ new registrations in the last hour', severity: 'medium', time: '2 hours ago' }
])
const userTypes = computed(() => [
  { type: 'students', label: 'Students', count: adminData.value.totalStudents, active: Math.floor(adminData.value.totalStudents * 0.85), icon: Users },
  { type: 'teachers', label: 'Teachers', count: adminData.value.totalTeachers, active: Math.floor(adminData.value.totalTeachers * 0.92), icon: BookOpen },
  { type: 'admins', label: 'Administrators', count: adminData.value.totalAdmins, active: Math.floor(adminData.value.totalAdmins * 0.95), icon: Shield }
])
const totalUsers = computed(() => adminData.value.totalUsers)

// --- Helper functions ---
const getStatusColor = (status) => {
  switch (status) {
    case 'good': return 'bg-green-500'
    case 'warning': return 'bg-yellow-500'
    case 'critical': return 'bg-red-500'
    default: return 'bg-gray-500'
  }
}
const getSeverityStyles = (severity) => {
  switch (severity) {
    case 'high': return 'bg-red-50 border-red-500'
    case 'medium': return 'bg-yellow-50 border-yellow-500'
    case 'low': return 'bg-blue-50 border-blue-500'
    default: return 'bg-gray-50 border-gray-500'
  }
}

// --- Charts ---
import { Chart, registerables } from 'chart.js'
Chart.register(...registerables)

const userGrowthChart = ref(null)
const coursePerformanceChart = ref(null)
const userDistributionChart = ref(null)
const activityChart = ref(null)

let userGrowthChartInstance = null
let coursePerformanceChartInstance = null
let userDistributionChartInstance = null
let activityChartInstance = null

const createCharts = () => {
  // User Growth
  if (userGrowthChartInstance) userGrowthChartInstance.destroy()
  userGrowthChartInstance = new Chart(userGrowthChart.value, {
    type: 'line',
    data: {
      labels: adminData.value.userGrowth.map(i => i.month),
      datasets: [{ label: 'Users', data: adminData.value.userGrowth.map(i => i.count), borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.2)' }]
    }
  })
  // Course Performance
  if (coursePerformanceChartInstance) coursePerformanceChartInstance.destroy()
  coursePerformanceChartInstance = new Chart(coursePerformanceChart.value, {
    type: 'bar',
    data: {
      labels: adminData.value.coursePerformance.map(i => i.course),
      datasets: [{ label: 'Average Grade', data: adminData.value.coursePerformance.map(i => i.avg), backgroundColor: '#10b981' }]
    }
  })
  // User Distribution
  if (userDistributionChartInstance) userDistributionChartInstance.destroy()
  userDistributionChartInstance = new Chart(userDistributionChart.value, {
    type: 'pie',
    data: {
      labels: ['Students', 'Teachers', 'Admins'],
      datasets: [{ data: [adminData.value.totalStudents, adminData.value.totalTeachers, adminData.value.totalAdmins], backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6'] }]
    }
  })
  // Activity
  if (activityChartInstance) activityChartInstance.destroy()
  activityChartInstance = new Chart(activityChart.value, {
    type: 'line',
    data: {
      labels: adminData.value.activity.map(i => i.date),
      datasets: [{ label: 'Daily Active Users', data: adminData.value.activity.map(i => i.count), borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.2)' }]
    }
  })
}

onMounted(async () => {
  await fetchAdminStats()
  nextTick(() => createCharts())
})
</script>

<style scoped>
.bg-clip-text {
  -webkit-background-clip: text;
  background-clip: text;
}
</style>
