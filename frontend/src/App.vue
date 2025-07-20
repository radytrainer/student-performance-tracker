<script setup>
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { watchEffect, computed } from 'vue'
import RoleBasedNavigation from '@/components/layout/RoleBasedNavigation.vue'
import Sidebar from '@/layout/Sidebar.vue'
import Footer from '@/layout/Footer.vue'
// import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const authStore = useAuthStore()

// Check authentication status when app loads
authStore.initialize()

// Computed property to check if user is authenticated
const isAuthenticated = computed(() => authStore.isAuthenticated)

// Global loading state (optional)
// const isLoading = ref(false)

// Watch for route changes to set loading state
// watchEffect(() => {
//   isLoading.value = router.currentRoute.value.meta.loading || false
// })
</script>

<template>
  <div class="app-container">
    <!-- Only show RoleBasedNavigation when authenticated -->
    <RoleBasedNavigation v-if="isAuthenticated" />
    
    <div class="app-layout" :class="{ 'no-sidebar': !isAuthenticated }">
      <!-- Only show Sidebar when authenticated -->
      <Sidebar v-if="isAuthenticated" />
      
      <main class="main-content" :class="{ 'full-width': !isAuthenticated }">
        <div v-if="isLoading" class="loading-overlay">
          <!-- <LoadingSpinner size="large" /> -->
        </div>
        
        <RouterView v-slot="{ Component }">
          <Transition name="fade" mode="out-in">
            <component :is="Component" />
          </Transition>
        </RouterView>
      </main>
    </div>
    
    <!-- Only show Footer when authenticated -->
    <Footer v-if="isAuthenticated" />
    
    <!-- Global notification/toast component -->
    <!-- <ToastNotification /> -->
  </div>
</template>

<style>
/* Base styles */
.app-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #f5f7fa;
}

.app-layout {
  display: flex;
  flex: 1;
}

.app-layout.no-sidebar {
  flex-direction: column;
}

.main-content {
  flex: 1;
  padding: 0;
  position: relative;
}

.main-content.full-width {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

/* Transition effects */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .app-layout {
    flex-direction: column;
  }
  
  .main-content {
    padding: 0;
  }
  
  .main-content.full-width {
    padding: 0;
  }
}
</style>
