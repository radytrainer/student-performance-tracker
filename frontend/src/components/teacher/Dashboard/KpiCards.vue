<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div
      v-for="(kpi, index) in kpiData"
      :key="index"
      class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-white/20"
    >
      <div class="flex items-center justify-between mb-4">
        <div :class="`p-3 rounded-xl bg-gradient-to-br ${kpi.gradient} shadow-lg`">
          <component :is="getIcon(kpi.icon)" class="w-6 h-6 text-white" />
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
</template>

<script setup>
import { Users, TrendingUp, Calendar, AlertTriangle } from 'lucide-vue-next'

defineProps({
  kpiData: Array
})

const getIcon = (iconName) => {
  const icons = {
    Users,
    TrendingUp,
    Calendar,
    AlertTriangle
  }
  return icons[iconName]
}
</script>