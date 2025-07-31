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
        class="fixed md:relative top-0 left-0 h-screen w-100 bg-gradient-to-b from-purple-600 via-blue-600 to-indigo-700 text-white z-40 md:translate-x-0 transform transition-all duration-300 shadow-2xl"
        :class="{ '-translate-x-full': !isOpen && isMobile }"
      >
        <!-- User Profile Section -->
        <div class="p-6 border-b border-white/10">
          <div class="flex flex-col items-center text-center">
            <!-- Profile Picture -->
            <ImageUpload
              :current-image="profileImage || user?.profile_picture"
              :fallback-text="`${user?.first_name || ''} ${user?.last_name || ''}`"
              :alt-text="`${user?.first_name || ''} ${user?.last_name || ''} Profile Picture`"
              size="medium"
              :editable="false"
              class="mb-4"
            />
            
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
          <div class="flex items-center justify-between mb-3">
            <p class="text-white/60 text-xs font-medium uppercase tracking-wider">Active Users</p>
            <button 
              @click="refreshActiveUsers" 
              :disabled="isLoading"
              class="text-white/40 hover:text-white/70 transition-colors duration-200 disabled:opacity-50"
              title="Refresh active users"
            >
              <svg class="w-3 h-3" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
            </button>
          </div>
          
          <div class="flex -space-x-2">
            <!-- Display active users with profile pictures -->
            <div 
              v-for="(user, index) in activeUsers" 
              :key="user.id" 
              class="relative group cursor-pointer"
              :title="`${user.first_name} ${user.last_name} (${user.role})`"
            >
              <!-- User avatar with image or initials -->
              <div class="w-8 h-8 rounded-full border-2 border-white/30 shadow-lg hover:scale-110 transition-transform duration-300 overflow-hidden">
                <img 
                  v-if="getUserImage(user)"
                  :src="getUserImage(user)" 
                  :alt="`${user.first_name} ${user.last_name}`"
                  class="w-full h-full object-cover"
                  @error="$event.target.style.display = 'none'"
                />
                <div 
                  v-else
                  class="w-full h-full flex items-center justify-center text-xs font-semibold text-white"
                  :class="getUserColor(user, index)"
                >
                  {{ getUserInitials(user) }}
                </div>
              </div>
              
              <!-- Online indicator -->
              <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-green-400 rounded-full border border-white/50"></div>
            </div>
            
            <!-- Show additional users count if more than 5 -->
            <div 
              v-if="totalActiveUsers > 5" 
              class="w-8 h-8 rounded-full bg-white/20 border-2 border-white/30 flex items-center justify-center text-xs text-white/70 hover:scale-110 transition-transform duration-300 cursor-pointer"
              :title="`${totalActiveUsers - 5} more active users`"
            >
              +{{ totalActiveUsers - 5 }}
            </div>
            
            <!-- Loading state -->
            <div 
              v-if="isLoading && activeUsers.length === 0" 
              class="flex -space-x-2"
            >
              <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full bg-white/10 border-2 border-white/20 animate-pulse"></div>
            </div>
            
            <!-- Error state -->
            <div 
              v-if="error && activeUsers.length === 0" 
              class="text-white/40 text-xs"
              :title="error"
            >
              Unable to load users
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
import { useProfileImage } from '@/composables/useProfileImage'
import { useActiveUsers } from '@/composables/useActiveUsers'
import ImageUpload from '@/components/ImageUpload.vue'

const route = useRoute()
const { getAvailableRoutes, user, userRole } = useAuth()
const { profileImage } = useProfileImage()
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
  width: 0px;
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