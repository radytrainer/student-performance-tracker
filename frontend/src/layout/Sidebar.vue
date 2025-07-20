<template>
  <div >
    <!-- Toggle button (mobile) -->
    <button
      @click="isOpen = !isOpen"
      class="md:hidden fixed top-4 left-4 z-50 p-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
    </button>

    <!-- Sidebar -->
    <transition name="slide">
      <aside
        v-show="isOpen"
        class="fixed md:relative top-0 left-0 h-screen w-80 bg-gradient-to-b from-purple-600 via-blue-600 to-indigo-700 text-white z-40 md:translate-x-0 transform transition-all duration-300 shadow-2xl"
        :class="{ '-translate-x-full': !isOpen && isMobile }"
      >
        <!-- User Profile Section -->
        <div class="p-6 border-b border-white/10">
          <div class="flex flex-col items-center text-center">
            <!-- Profile Picture -->
            <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm border-2 border-white/30 flex items-center justify-center mb-4 shadow-lg">
              <span class="text-xl font-bold text-white">
                {{ userInitials }}
              </span>
            </div>
            
            <!-- User Info -->
            <h3 class="text-lg font-bold text-white mb-1">
              {{ user?.first_name }} {{ user?.last_name }}
            </h3>
            <p class="text-white/70 text-sm mb-3">
              {{ user?.email }}
            </p>
            
            <!-- Role Badge -->
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white border border-white/30">
              {{ userRole?.toUpperCase() }}
            </span>
          </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 py-6 space-y-2">
          <router-link
            v-for="route in availableRoutes"
            :key="route.path"
            :to="route.path"
            @click="handleRouteClick"
            class="flex items-center px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all duration-300 group"
            :class="{ 
              'bg-white/20 text-white shadow-lg': isActiveRoute(route.path),
              'hover:translate-x-1': !isActiveRoute(route.path)
            }"
          >
            <i :class="route.icon" class="mr-3 text-lg group-hover:scale-110 transition-transform duration-300"></i>
            <span class="font-medium">{{ route.name }}</span>
          </router-link>
        </nav>

        <!-- Active Users Section -->
        <div class="p-4 border-t border-white/10">
          <p class="text-white/60 text-xs font-medium uppercase tracking-wider mb-3">Active Users</p>
          <div class="flex -space-x-2">
            <div v-for="i in 5" :key="i" 
                 class="w-8 h-8 rounded-full border-2 border-white/30 shadow-lg hover:scale-110 transition-transform duration-300 cursor-pointer"
                 :class="getActiveUserColor(i)">
            </div>
            <div class="w-8 h-8 rounded-full bg-white/20 border-2 border-white/30 flex items-center justify-center text-xs text-white/70 hover:scale-110 transition-transform duration-300 cursor-pointer">
              +3
            </div>
          </div>
        </div>

        <!-- Decorative Element -->
        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-black/20 to-transparent pointer-events-none"></div>
      </aside>
    </transition>

    <!-- Overlay on mobile -->
    <div
      v-if="isOpen && isMobile"
      @click="isOpen = false"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 md:hidden transition-all duration-300"
    ></div>

    <!-- Main content -->
    <main class="flex-1 transition-all duration-300" :class="{ 'md:ml-80': isOpen }">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const route = useRoute()
const { getAvailableRoutes, user, userRole } = useAuth()

const isOpen = ref(true)
const isMobile = ref(window.innerWidth < 768)

const availableRoutes = computed(() => getAvailableRoutes())

const userInitials = computed(() => {
  if (!user.value) return ''
  const first = user.value.first_name?.charAt(0) || ''
  const last = user.value.last_name?.charAt(0) || ''
  return (first + last).toUpperCase()
})

const isActiveRoute = (path) => {
  return route.path === path || route.path.startsWith(path + '/')
}

const handleRouteClick = () => {
  if (isMobile.value) {
    isOpen.value = false
  }
}

const getActiveUserColor = (index) => {
  const colors = [
    'bg-gradient-to-br from-pink-400 to-orange-400',
    'bg-gradient-to-br from-blue-400 to-purple-400', 
    'bg-gradient-to-br from-green-400 to-blue-400',
    'bg-gradient-to-br from-yellow-400 to-red-400',
    'bg-gradient-to-br from-purple-400 to-indigo-400'
  ]
  return colors[index - 1]
}

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

/* Custom scrollbar for webkit browsers */
aside::-webkit-scrollbar {
  width: 4px;
}

aside::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

aside::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 2px;
}

aside::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

/* Glassmorphism effect */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}
</style>