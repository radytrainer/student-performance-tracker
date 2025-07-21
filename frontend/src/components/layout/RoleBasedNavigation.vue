<template>
  <nav class="bg-white shadow-sm border-b border-gray-100" v-if="isAuthenticated">
    <div class="max-w-full mx-auto px-6">
      <div class="flex justify-between items-center h-16">
        <!-- Empty space where logo was -->
        <div class="flex items-center">
          <!-- Logo removed as requested -->
        </div>

        <!-- Search Bar (optional for future enhancement) -->
        <div class="hidden md:flex flex-1 max-w-md mx-8">
          <div class="relative w-full">
            <input 
              type="text" 
              placeholder="Search..."
              class="w-full px-4 py-2 pl-10 pr-4 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            >
            <div class="absolute inset-y-0 left-0 flex items-center pl-3">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- User Menu -->
        <div class="flex items-center space-x-4">
          <!-- Notifications -->
          <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5c-.1-.1-.2-.2-.3-.3L12 9V6a2 2 0 00-4 0v3L3.8 13.2c-.1.1-.2.2-.3.3L0 17h5"></path>
            </svg>
          </button>

          <!-- Role Badge -->
          <span 
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider"
            :class="roleBadgeClass"
          >
            {{ userRole }}
          </span>

          <!-- User Profile Dropdown -->
          <div class="relative">
            <button
              @click="showUserMenu = !showUserMenu"
              class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500"
              id="user-menu"
              aria-haspopup="true"
            >
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                {{ userInitials }}
              </div>
              <span class="hidden md:block text-sm font-medium text-gray-700">{{ userFullName }}</span>
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <div
              v-show="showUserMenu"
              @click="showUserMenu = false"
              class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-100 z-50 py-2"
              role="menu"
              aria-orientation="vertical"
              aria-labelledby="user-menu"
            >
              <!-- User Info Header -->
              <div class="px-4 py-3 border-b border-gray-100">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                    {{ userInitials }}
                  </div>
                  <div>
                    <div class="font-semibold text-gray-900">{{ user?.first_name }} {{ user?.last_name }}</div>
                    <div class="text-sm text-gray-500">{{ user?.email }}</div>
                  </div>
                </div>
              </div>

              <!-- Menu Items -->
              <div class="py-1">
                <router-link
                  to="/profile"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200"
                  role="menuitem"
                  v-if="showForPermission('view_profile')"
                >
                  <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  My Profile
                </router-link>

                <template v-if="isAdmin">
                  <router-link
                    to="/admin/settings"
                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200"
                    role="menuitem"
                  >
                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    System Settings
                  </router-link>
                </template>

                <hr class="my-1 border-gray-100">
                
                <button
                  @click="handleLogout"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200"
                  role="menuitem"
                >
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                  </svg>
                  Sign out
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile menu button -->
        <div class="-mr-2 flex items-center sm:hidden">
          <button
            @click="showMobileMenu = !showMobileMenu"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
          >
            <span class="sr-only">Open main menu</span>
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-show="showMobileMenu" class="sm:hidden">
      <!-- Mobile User Menu -->
      <div class="pt-4 pb-3 border-t border-gray-200">
        <div class="flex items-center px-4">
          <div class="flex-shrink-0">
            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
              <span class="text-sm font-medium text-gray-700">
                {{ userInitials }}
              </span>
            </div>
          </div>
          <div class="ml-3">
            <div class="text-base font-medium text-gray-800">
              {{ user?.first_name }} {{ user?.last_name }}
            </div>
            <div class="text-sm text-gray-500">{{ user?.email }}</div>
          </div>
          <div class="ml-auto">
            <span :class="roleBadgeClass" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
              {{ userRole?.toUpperCase() }}
            </span>
          </div>
        </div>
        <div class="mt-3 space-y-1">
          <router-link
            to="/profile"
            @click="showMobileMenu = false"
            class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100"
            v-if="showForPermission('view_profile')"
          >
            <i class="fas fa-user mr-2"></i>
            My Profile
          </router-link>
          <button
            @click="handleLogout"
            class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100"
          >
            <i class="fas fa-sign-out-alt mr-2"></i>
            Sign out
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const {
  user,
  isAuthenticated,
  userRole,
  isAdmin,
  isTeacher,
  isStudent,
  showForPermission
} = useAuth()

// Component state
const showUserMenu = ref(false)
const showMobileMenu = ref(false)

const userInitials = computed(() => {
  if (!user.value) return ''
  const first = user.value.first_name?.charAt(0) || ''
  const last = user.value.last_name?.charAt(0) || ''
  return (first + last).toUpperCase()
})

const userFullName = computed(() => {
  if (!user.value) return ''
  const firstName = user.value.first_name || ''
  const lastName = user.value.last_name || ''
  return `${firstName} ${lastName}`.trim()
})

const roleBadgeClass = computed(() => {
  const baseClasses = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider'
  switch (userRole.value) {
    case 'admin':
      return `${baseClasses} bg-red-100 text-red-800`
    case 'teacher':
      return `${baseClasses} bg-blue-100 text-blue-800`
    case 'student':
      return `${baseClasses} bg-green-100 text-green-800`
    default:
      return `${baseClasses} bg-gray-100 text-gray-800`
  }
})

// Methods
const handleLogout = async () => {
  showUserMenu.value = false
  showMobileMenu.value = false
  await authStore.logout()
  router.push('/login')
}

// Close dropdowns when clicking outside
document.addEventListener('click', (event) => {
  if (!event.target.closest('#user-menu')) {
    showUserMenu.value = false
  }
})
</script>
