<template>
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
     
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg">
            <TrendingUp class="w-5 h-5 text-white" />
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">Monthly Progress</h3>
            <p class="text-sm text-gray-600">Grade trends over time</p>
          </div>
        </div>
        <button 
          @click="$emit('export-chart', 'line')"
          class="px-3 py-1 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-xs"
        >
          Export
        </button>
      </div>
      <div class="h-80">
        <canvas ref="lineChart" class="w-full h-full"></canvas>
      </div>
    </div>

     
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg">
            <BarChart3 class="w-5 h-5 text-white" />
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">Subject Performance</h3>
            <p class="text-sm text-gray-600">Grades by subject</p>
          </div>
        </div>
        <button 
          @click="$emit('sort-subjects')"
          class="px-3 py-1 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-xs"
        >
          Sort
        </button>
      </div>
      <div class="h-80">
        <canvas ref="barChart" class="w-full h-full"></canvas>
      </div>
    </div>

     
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg">
            <PieChart class="w-5 h-5 text-white" />
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">Grade Distribution</h3>
            <p class="text-sm text-gray-600">Performance breakdown</p>
          </div>
        </div>
        <button 
          @click="$emit('toggle-chart-type')"
          class="px-3 py-1 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-xs"
        >
          {{ pieChartType === 'doughnut' ? 'Pie' : 'Doughnut' }}
        </button>
      </div>
      <div class="h-80">
        <canvas ref="pieChart" class="w-full h-full"></canvas>
      </div>
    </div>

    
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
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import { TrendingUp, BarChart3, PieChart, Activity } from 'lucide-vue-next'

const props = defineProps({
  student: Object,
  pieChartType: String
})

defineEmits(['export-chart', 'sort-subjects', 'toggle-chart-type'])

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

// Chart creation functions
const createLineChart = async () => {
  if (!lineChart.value || !props.student) return
  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)
  if (lineChartInstance) lineChartInstance.destroy()
  
  const ctx = lineChart.value.getContext('2d')
  lineChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.student.monthlyGrades.map(item => item.month),
      datasets: [{
        label: 'Grade Progress',
        data: props.student.monthlyGrades.map(item => item.grade),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderWidth: 4,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#3b82f6',
        pointBorderColor: '#fff',
        pointBorderWidth: 3,
        pointRadius: 8,
        pointHoverRadius: 12
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: { font: { size: 12, weight: '500' } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 12, weight: '500' } }
        }
      }
    }
  })
}

const createBarChart = async () => {
  if (!barChart.value || !props.student) return
  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)
  if (barChartInstance) barChartInstance.destroy()
  
  const ctx = barChart.value.getContext('2d')
  const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
  
  barChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.student.subjects.map(subject => subject.name),
      datasets: [{
        label: 'Grade',
        data: props.student.subjects.map(subject => subject.grade),
        backgroundColor: colors.map(color => color + '40'),
        borderColor: colors,
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: { font: { size: 12, weight: '500' } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 11, weight: '500' }, maxRotation: 45 }
        }
      }
    }
  })
}

const createPieChart = async () => {
  if (!pieChart.value || !props.student) return
  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)
  if (pieChartInstance) pieChartInstance.destroy()
  
  const ctx = pieChart.value.getContext('2d')
  const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
  
  pieChartInstance = new Chart(ctx, {
    type: props.pieChartType,
    data: {
      labels: props.student.subjects.map(subject => subject.name),
      datasets: [{
        data: props.student.subjects.map(subject => subject.grade),
        backgroundColor: colors,
        borderColor: '#fff',
        borderWidth: 4,
        hoverBorderWidth: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: props.pieChartType === 'doughnut' ? '60%' : 0,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            padding: 20,
            usePointStyle: true,
            font: { size: 12, weight: '500' }
          }
        }
      }
    }
  })
}

const createRadarChart = async () => {
  if (!radarChart.value || !props.student) return
  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)
  if (radarChartInstance) radarChartInstance.destroy()
  
  const ctx = radarChart.value.getContext('2d')
  
  radarChartInstance = new Chart(ctx, {
    type: 'radar',
    data: {
      labels: props.student.subjects.map(subject => subject.name),
      datasets: [{
        label: 'Performance',
        data: props.student.subjects.map(subject => subject.grade),
        borderColor: '#8b5cf6',
        backgroundColor: 'rgba(139, 92, 246, 0.2)',
        borderWidth: 3,
        pointBackgroundColor: '#8b5cf6',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        r: {
          beginAtZero: true,
          max: 100,
          grid: { color: 'rgba(0, 0, 0, 0.1)' },
          angleLines: { color: 'rgba(0, 0, 0, 0.1)' },
          pointLabels: { font: { size: 11, weight: '500' } }
        }
      }
    }
  })
}

const updateCharts = async () => {
  await nextTick()
  setTimeout(() => {
    createLineChart()
    createBarChart()
    createPieChart()
    createRadarChart()
  }, 100)
}

// Lifecycle
onMounted(() => {
  updateCharts()
})

watch(() => props.student, () => {
  updateCharts()
}, { deep: true })

watch(() => props.pieChartType, () => {
  createPieChart()
})
</script>