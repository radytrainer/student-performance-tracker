<template>
  <div>
    <!-- Toggle button (mobile) -->
    <button @click="isOpen = !isOpen"
      class="md:hidden fixed top-4 left-4 z-50 p-3 bg-gray-800 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
    </button>

    <!-- Sidebar -->
    <transition name="slide">
      <aside v-show="isOpen"
        class="fixed md:relative top-0 left-0 h-screen w-90 bg-gray-50 border-r border-gray-200 z-40 md:translate-x-0 transform transition-all duration-300 flex flex-col"
        :class="{ '-translate-x-full': !isOpen && isMobile }">
        
        <!-- System Branding Section - Sticky Header -->
        <div class="py-6 border-b border-gray-200 bg-white sticky top-0 z-10 flex-shrink-0">
          <div class="flex flex-col items-center justify-center text-center w-full">
            <!-- System Logo -->
            <div class="relative w-16 h-16 rounded-full mb-3 logo-3d">
              <!-- Background circle with gradient -->
              <div class="absolute inset-0 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 shadow-2xl"></div>
              <div class="absolute inset-0.5 rounded-full bg-gradient-to-br from-blue-400/30 to-transparent"></div>
              
              <!-- Icon container -->
              <div class="relative w-full h-full rounded-full bg-gradient-to-br from-blue-400/20 to-blue-600/10 border border-blue-300/30 backdrop-blur-sm flex items-center justify-center">
                <!-- 3D Graduation Cap Icon -->
                <div class="relative">
                  <!-- Shadow layer -->
                  <svg class="absolute w-9 h-9 text-blue-800/40 transform translate-x-0.5 translate-y-0.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12,3L1,9L12,15L21,9V10H23V9M5,13.18V17.18L12,21L19,17.18V13.18L12,17L5,13.18Z" />
                  </svg>
                  <!-- Main icon -->
                  <svg class="relative w-9 h-9 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12,3L1,9L12,15L21,9V10H23V9M5,13.18V17.18L12,21L19,17.18V13.18L12,17L5,13.18Z" />
                  </svg>
                  <!-- Highlight -->
                  <div class="absolute top-1 left-2 w-2 h-2 bg-white/60 rounded-full blur-sm"></div>
                </div>
              </div>
              
              <!-- Outer glow -->
              <div class="absolute -inset-1 rounded-full bg-gradient-to-br from-blue-400/30 to-transparent blur-md"></div>
            </div>

            <!-- System Name -->
            <h2 class="text-lg font-bold text-gray-900 mb-0.5">
              Education Tracker
            </h2>
            <p class="text-gray-600 text-xs">
              Performance System
            </p>
          </div>
        </div>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto">
          <!-- Navigation Menu -->
          <nav class="px-6 py-6">
            <div class="space-y-1">
              <template v-for="route in availableRoutes" :key="route.path || route.name">
                <!-- Expandable Section -->
                <div v-if="route.expandable" class="space-y-1">
                  <!-- Section Header -->
                  <button 
                    @click="toggleSection(route.name)"
                    class="w-full flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded-lg transition-all duration-200 group">
                    <i :class="[route.icon, 'flex-shrink-0 w-5 h-5 mr-4 text-gray-400 group-hover:text-gray-600']"></i>
                    <span class="flex-1 text-left">{{ route.name }}</span>
                    <!-- Chevron Arrow -->
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                      :class="{ 'rotate-180': expandedSections[route.name] }"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                  
                  <!-- Sub-items -->
                  <transition name="expand">
                    <div v-show="expandedSections[route.name]" class="ml-6 space-y-1">
                      <router-link 
                        v-for="child in route.children" 
                        :key="child.path"
                        :to="child.path" 
                        @click="handleRouteClick"
                        class="flex items-center px-4 py-2 text-sm rounded-lg transition-all duration-200 group"
                        :class="{
                          'bg-gray-900 text-white': isActiveRoute(child.path),
                          'text-gray-600 hover:bg-gray-100 hover:text-gray-900': !isActiveRoute(child.path)
                        }">
                        <i :class="[
                            child.icon, 
                            'flex-shrink-0 w-4 h-4 mr-3',
                            {
                              'text-white': isActiveRoute(child.path),
                              'text-gray-400 group-hover:text-gray-600': !isActiveRoute(child.path)
                            }
                          ]"></i>
                        <span>{{ child.name }}</span>
                      </router-link>
                    </div>
                  </transition>
                </div>
                
                <!-- Regular Navigation Item -->
                <router-link v-else
                  :to="route.path" 
                  @click="handleRouteClick"
                  class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 group relative"
                  :class="{
                    'bg-gray-900 text-white': isActiveRoute(route.path),
                    'text-gray-700 hover:bg-gray-100 hover:text-gray-900': !isActiveRoute(route.path)
                  }">
                  <i :class="[
                      route.icon,
                      'flex-shrink-0 w-5 h-5 mr-4 text-center',
                      {
                        'text-white': isActiveRoute(route.path),
                        'text-gray-400 group-hover:text-gray-600': !isActiveRoute(route.path)
                      }
                    ]"></i>
                  <span class="flex-1">{{ route.name }}</span>
                  
                  <!-- Notification badge -->
                  <span v-if="route.path && route.path.includes('alerts') && hasAlerts" 
                    class="inline-flex items-center justify-center w-5 h-5 text-xs font-medium text-white bg-red-600 rounded-full">
                    !
                  </span>
                </router-link>
              </template>
            </div>
          </nav>

          <!-- Active Users Section -->
          <div class="px-6 py-6 border-t border-gray-200 bg-white">
            <div class="flex items-center justify-between mb-4">
              <p class="text-gray-600 text-xs font-medium uppercase tracking-wider">Active Users</p>
              <button @click="refreshActiveUsers" :disabled="isLoading"
                class="text-gray-400 hover:text-gray-600 transition-colors duration-200 disabled:opacity-50"
                title="Refresh active users">
                <svg class="w-4 h-4" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                  </path>
                </svg>
              </button>
            </div>

            <div class="flex -space-x-2">
              <!-- Display active users with profile pictures -->
              <div v-for="(user, index) in activeUsers" :key="user.id" class="relative group cursor-pointer"
                :title="`${user.first_name} ${user.last_name} (${user.role})`">
                <!-- User avatar with image or initials -->
                <div
                  class="w-8 h-8 rounded-full border-2 border-gray-200 shadow-sm hover:scale-110 transition-transform duration-200 overflow-hidden bg-white">
                  <img v-if="getUserImage(user)" :src="getUserImage(user)" :alt="`${user.first_name} ${user.last_name}`"
                    class="w-full h-full object-cover" @error="$event.target.style.display = 'none'" />
                  <div v-else class="w-full h-full flex items-center justify-center text-xs font-semibold text-gray-700"
                    :class="getUserColor(user, index)">
                    {{ getUserInitials(user) }}
                  </div>
                </div>

                <!-- Online indicator -->
                <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-white">
                </div>
              </div>

              <!-- Show additional users count if more than 5 -->
              <div v-if="totalActiveUsers > 5"
                class="w-8 h-8 rounded-full bg-gray-100 border-2 border-gray-200 flex items-center justify-center text-xs text-gray-600 hover:scale-110 transition-transform duration-200 cursor-pointer"
                :title="`${totalActiveUsers - 5} more active users`">
                +{{ totalActiveUsers - 5 }}
              </div>

              <!-- Loading state -->
              <div v-if="isLoading && activeUsers.length === 0" class="flex -space-x-2">
                <div v-for="i in 3" :key="i"
                  class="w-8 h-8 rounded-full bg-gray-100 border-2 border-gray-200 animate-pulse"></div>
              </div>

              <!-- Error state -->
              <div v-if="error && activeUsers.length === 0" class="text-gray-400 text-xs" :title="error">
                Unable to load users
              </div>
            </div>
          </div>
        </div>
      </aside>
    </transition>

    <!-- Overlay on mobile -->
    <div v-if="isOpen && isMobile" @click="isOpen = false"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 md:hidden transition-all duration-300"></div>

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
import { useActiveUsers } from '@/composables/useActiveUsers'

const route = useRoute()
const { getAvailableRoutes } = useAuth()
const {
  activeUsers,
  totalActiveUsers,
  isLoading,
  error,
  refreshActiveUsers,
  getUserImage,
  getUserInitials,
  getUserColor
} = useActiveUsers()

const isOpen = ref(true)
const isMobile = ref(window.innerWidth < 768)

const availableRoutes = computed(() => getAvailableRoutes())

// Example state for notifications (you can connect this to your actual alert system)
const hasAlerts = ref(false) // Set this based on your actual alert system

// Expandable sections state
const expandedSections = ref({
  // Admin sections
  'User Management': true,
  'Academic': false,
  'System': false,
  'Analytics & Reports': false,
  // Teacher sections
  'Classroom Management': true,
  'Assessment': false,
  'Data & Reports': false,
  'Communication': false
})

const toggleSection = (sectionName) => {
  expandedSections.value[sectionName] = !expandedSections.value[sectionName]
}

const isActiveRoute = (path) => {
  return route.path === path || route.path.startsWith(path + '/')
}

const handleRouteClick = () => {
  if (isMobile.value) {
    isOpen.value = false
  }
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

/* Hide scrollbar completely while keeping scroll functionality */
aside {
  overflow-y: auto;
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* Internet Explorer 10+ */
}

aside::-webkit-scrollbar {
  width: 0;
  height: 0;
  display: none; /* Webkit browsers */
}

/* Mobile overlay backdrop blur */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}

/* Ensure icons are properly centered */
.w-5.h-5 {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 3D Logo Effects */
.logo-3d {
  transition: transform 0.3s ease;
  animation: float 6s ease-in-out infinite;
}

.logo-3d:hover {
  transform: scale(1.05) rotateY(10deg);
}

/* Floating animation */
@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-3px);
  }
}

/* Enhanced drop shadows for 3D effect */
.drop-shadow-lg {
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3)) drop-shadow(0 0 8px rgba(255, 255, 255, 0.1));
}

/* Expand/Collapse Animation */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-10px);
}

.expand-enter-to,
.expand-leave-from {
  opacity: 1;
  max-height: 500px;
  transform: translateY(0);
}
</style>
