<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div class="text-center">
          <!-- 403 Icon -->
          <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
            <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V12a7 7 0 10-14 0v5l-2 2v1h16v-1l-2-2V12z"/>
            </svg>
          </div>

          <h1 class="text-2xl font-bold text-gray-900 mb-2">Access Denied</h1>
          
          <p class="text-gray-600 mb-6">
            {{ message || 'You do not have permission to access this page.' }}
          </p>

          <!-- User Info -->
          <div class="bg-gray-50 rounded-lg p-4 mb-6" v-if="user">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Current User:</h3>
            <div class="text-sm text-gray-600">
              <p><strong>Name:</strong> {{ user.first_name }} {{ user.last_name }}</p>
              <p><strong>Role:</strong> {{ user.role }}</p>
              <p><strong>Email:</strong> {{ user.email }}</p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-3">
            <button
              @click="goToDashboard"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Go to My Dashboard
            </button>
            
            <button
              @click="goBack"
              class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Go Back
            </button>

            <button
              @click="logout"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              Logout
            </button>
          </div>

          <!-- Role Information -->
          <div class="mt-8 border-t border-gray-200 pt-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Role Permissions:</h3>
            <div class="text-xs text-gray-600 space-y-1">
              <div v-if="isAdmin" class="text-green-600">
                ✓ Admin - Full system access
              </div>
              <div v-if="isTeacher" class="text-blue-600">
                ✓ Teacher - Manage classes, grades, and attendance
              </div>
              <div v-if="isStudent" class="text-purple-600">
                ✓ Student - View personal academic information
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const { user, isAdmin, isTeacher, isStudent, getDefaultRoute } = useAuth()
const authStore = useAuthStore()

// Get error message from query params
const message = computed(() => route.query.message)

const goToDashboard = () => {
  const defaultRoute = getDefaultRoute()
  router.push(defaultRoute)
}

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1)
  } else {
    goToDashboard()
  }
}

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}

// Set page title
onMounted(() => {
  document.title = 'Access Denied - Student Tracker'
})
</script>
