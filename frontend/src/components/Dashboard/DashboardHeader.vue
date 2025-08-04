<template>
  <header class="bg-white shadow-md rounded-lg">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex justify-between items-center py-6">
        <div class="flex items-center space-x-4">
          <div>
<<<<<<< HEAD
            <h1 class="text-3xl font-bold text-purple-600 tracking-tight">
              Welcome back, {{ user?.first_name }}!
            </h1>
            <p class="text-sm text-gray-600 font-medium">
               User ID: {{ user?.id }} / Student Code: {{ user?.student_code }}
            </p>
            <p class="text-sm text-gray-600 font-medium">
              Student Courses:{{ user?.course }} 
            </p>
            <p class="text-xs text-gray-500">
=======
            <h1
              class="text-5xl font-bold tracking-tight mb-4 bg-gradient-to-r from-purple-600 to-blue-500 text-transparent bg-clip-text"
            >
              Welcome back, {{ user?.first_name }}!
            </h1>
            <p class="text-sm text-gray-600 font-medium mb-2">
              User ID: {{ user?.id }} / Student Code: {{ user?.student_code }}
            </p>
            <p class="text-sm text-gray-600 font-medium mb-2">
              Student Courses: {{ user?.course || "N/A" }}
            </p>
            <p class="text-sm text-gray-600 font-medium mb-2">
              Email: {{ user?.email || "N/A" }}
            </p>
            <p class="text-xs text-gray-500 mb-2">
>>>>>>> main
              Last login: {{ formattedLastLogin }}
            </p>
          </div>
        </div>
        <div>
          <!-- Student header image -->
          <img src="@/assets/StudentHeader.png" alt="" class="w-66 h-60" />
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
<<<<<<< HEAD
import { useAuth } from '@/composables/useAuth'
import { computed } from 'vue'

// Use composable to get current user
const { user } = useAuth()

// Helper function to format relative time
const formatRelativeTime = (dateString) => {
  if (!dateString) return 'N/A'
  
  const now = new Date()
  const date = new Date(dateString)
  const diffInSeconds = Math.floor((now - date) / 1000)
  
  if (diffInSeconds < 60) {
    return 'Just now'
  } else if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60)
    return `${minutes} minute${minutes > 1 ? 's' : ''} ago`
  } else if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600)
    return `${hours} hour${hours > 1 ? 's' : ''} ago`
  } else {
    const days = Math.floor(diffInSeconds / 86400)
    return `${days} day${days > 1 ? 's' : ''} ago`
  }
}

// Format last login nicely
const formattedLastLogin = computed(() => {
  return formatRelativeTime(user.value?.last_login)
})
=======
import { useAuth } from "@/composables/useAuth";
import { computed } from "vue";

const { user } = useAuth();

const formatRelativeTime = (dateString) => {
  if (!dateString) return "N/A";
  const now = new Date();
  const date = new Date(dateString);
  const diffInSeconds = Math.floor((now - date) / 1000);
  if (diffInSeconds < 60) {
    return "Just now";
  } else if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60);
    return `${minutes} minute${minutes > 1 ? "s" : ""} ago`;
  } else if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600);
    return `${hours} hour${hours > 1 ? "s" : ""} ago`;
  } else {
    const days = Math.floor(diffInSeconds / 86400);
    return `${days} day${days > 1 ? "s" : ""} ago`;
  }
};

const formattedLastLogin = computed(() => {
  return formatRelativeTime(user.value?.last_login);
});
>>>>>>> main
</script>
