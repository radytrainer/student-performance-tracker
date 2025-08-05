<script setup>
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale)

const props = defineProps({
  chartData: Object,
  chartOptions: Object
})
</script>

<template>
  <Line :data="chartData" :options="chartOptions" />
</template>
