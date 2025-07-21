<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-8 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Academic Progress & Grades</h2>
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-500">Overall GPA:</span>
          <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            {{ currentGPA }}
          </span>
        </div>
      </div>
    </div>

    <div class="p-8">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div
          v-for="subject in progress"
          :key="subject.id"
          class="group p-6 bg-gradient-to-br from-white to-gray-50 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-gray-200 transition-all duration-300"
        >
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">{{ subject.name }}</h3>
            <span :class="getGradeColor(subject.grade)" class="text-2xl font-bold">{{ subject.grade }}</span>
          </div>
          <div class="relative">
            <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
              <div
                class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 rounded-full transition-all duration-500 ease-out"
                :style="`width: ${subject.progress}%`"
              ></div>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600 font-medium">{{ subject.progress }}% Complete</span>
              <span class="text-gray-500">Recent: {{ subject.recentScore }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps(['progress', 'currentGPA'])

const getGradeColor = (grade) => {
  if (grade.startsWith('A')) return 'text-green-600'
  if (grade.startsWith('B')) return 'text-blue-600'
  if (grade.startsWith('C')) return 'text-yellow-600'
  return 'text-red-600'
}
</script>