<script setup>
import { ref, onMounted } from 'vue'
import BarChart from '@/components/charts/BarChart.vue'
import analyticsAPI from '@/api/analytics'

const performanceData = ref(null)
const subjectData = ref(null)
const isLoading = ref(true)
const error = ref(null)

const fetchChartData = async () => {
  try {
    const [performanceRes, subjectRes] = await Promise.all([
      analyticsAPI.getPerformanceTrends(),
      analyticsAPI.getSubjectComparison()
    ])
    
    performanceData.value = {
      labels: performanceRes.data.map(item => item.month),
      datasets: [{
        label: 'Average Score',
        data: performanceRes.data.map(item => item.average_score),
        backgroundColor: '#4f46e5'
      }]
    }
    
    subjectData.value = {
      labels: subjectRes.data.map(item => item.subject),
      datasets: [{
        label: 'Subject Scores',
        data: subjectRes.data.map(item => item.average_score),
        backgroundColor: [
          '#4f46e5', '#6366f1', '#a5b4fc', '#c7d2fe'
        ]
      }]
    }
    
  } catch (err) {
    error.value = err.message || 'Failed to load chart data'
    console.error('Error fetching chart data:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchChartData)
</script>

<template>
  <div class="dashboard">
    <h1>Performance Dashboard</h1>
    
    <div v-if="isLoading" class="loading">Loading data...</div>
    
    <div v-else-if="error" class="error">{{ error }}</div>
    
    <div v-else class="chart-container">
      <div class="chart-card">
        <h2>Performance Trend</h2>
        <BarChart 
          v-if="performanceData" 
          :chart-data="performanceData"
          :chart-options="{
            responsive: true,
            scales: {
              y: { beginAtZero: true, max: 100 }
            }
          }"
        />
      </div>
      
      <div class="chart-card">
        <h2>Subject Comparison</h2>
        <BarChart 
          v-if="subjectData" 
          :chart-data="subjectData"
          :chart-options="{
            responsive: true,
            scales: {
              y: { beginAtZero: true, max: 100 }
            }
          }"
        />
      </div>
    </div>
  </div>
</template>