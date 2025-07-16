<template>
  <div class="flex">
    <!-- Toggle button (mobile) -->
    <button
      @click="isOpen = !isOpen"
      class="md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded"
    >
      ☰
    </button>

    <!-- Sidebar -->
    <transition name="slide">
      <aside
        v-show="isOpen"
        class="fixed md:relative top-0 left-0 h-full w-64 bg-gray-200 text-gray-600 z-40 p-4 md:translate-x-0 transform transition-transform duration-300"
        :class="{ '-translate-x-full': !isOpen && isMobile }"
      >
        <div class="text-2xl font-bold mb-6">📊 Tracker</div>

        <nav class="space-y-2">
          <a href="#" class="block px-3 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
          </a>
          <a href="#" class="block px-3 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-user-graduate mr-2"></i> Students
          </a>
          <a href="#" class="block px-3 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-chart-line mr-2"></i> Performance
          </a>
          <a href="#" class="block px-3 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-cogs mr-2"></i> Settings
          </a>
        </nav>
      </aside>
    </transition>

    <!-- Overlay on mobile -->
    <div
      v-if="isOpen && isMobile"
      @click="isOpen = false"
      class="fixed inset-0 bg-black bg-opacity-40 z-30 md:hidden"
    ></div>

    <!-- Main content -->
    <main class="flex-1 p-4 md:ml-64">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const isOpen = ref(true)
const isMobile = ref(window.innerWidth < 768)

const updateSize = () => {
  isMobile.value = window.innerWidth < 768
  isOpen.value = !isMobile.value
}

onMounted(() => {
  updateSize()
  window.addEventListener('resize', updateSize)
})
onUnmounted(() => {
  window.removeEventListener('resize', updateSize)
})
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}
</style>
