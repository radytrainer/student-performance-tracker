<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const isMenuOpen = ref(false)

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <header class="navbar">
    <div class="navbar-brand">
      <router-link to="/" class="logo">
        <span class="text-indigo-600 font-bold">Student</span> Performance Tracker
      </router-link>
    </div>
    
    <nav class="navbar-menu">
      <router-link 
        v-for="route in router.getRoutes()" 
        :key="route.name"
        :to="route.path"
        v-show="route.meta.requiresAuth && authStore.isAuthenticated"
        class="nav-link"
        active-class="active"
      >
        {{ route.name }}
      </router-link>
      
      <button v-if="authStore.isAuthenticated" @click="logout" class="logout-btn">
        Logout
      </button>
    </nav>
  </header>
</template>

<style scoped>
.navbar {
  @apply bg-white shadow-sm py-4 px-6 flex justify-between items-center;
}

.logo {
  @apply text-xl font-semibold no-underline;
}

.navbar-menu {
  @apply flex items-center space-x-6;
}

.nav-link {
  @apply text-gray-600 hover:text-indigo-600 transition-colors;
}

.nav-link.active {
  @apply text-indigo-600 font-medium;
}

.logout-btn {
  @apply text-gray-600 hover:text-red-600 transition-colors;
}
</style>