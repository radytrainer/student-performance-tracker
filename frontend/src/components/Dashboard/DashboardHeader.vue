<template>
  <header>
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex justify-between items-center py-6">
        <div class="flex items-center space-x-4">
          <div>
            <h1 class="text-2xl font-bold text-purple-600 tracking-tight">
              Welcome back, {{ user?.first_name }}!
            </h1>
            <p class="text-sm text-gray-600 font-medium">
              {{ user?.course }}  User ID: {{ user?.id }} / Student Code: {{ user?.student_code }}
            </p>
            <p class="text-xs text-gray-500">
              Last login: {{ formattedLastLogin }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useAuth } from '@/composables/useAuth'
import { computed } from 'vue'
import moment from 'moment'

// Use composable to get current user
const { user } = useAuth()

// Format last login nicely
const formattedLastLogin = computed(() => {
  return user.value?.last_login
    ? moment(user.value.last_login).fromNow()
    : 'N/A'
})
</script>
