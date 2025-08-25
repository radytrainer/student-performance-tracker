<script setup>
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { watchEffect, computed, ref } from 'vue'
import RoleBasedNavigation from '@/components/layout/RoleBasedNavigation.vue'
import Sidebar from '@/layout/Sidebar.vue'
import Footer from '@/layout/Footer.vue'
import ToastHub from '@/components/notifications/ToastHub.vue'
// import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const authStore = useAuthStore()

// Computed property to check if user is authenticated
const isAuthenticated = computed(() => authStore.isAuthenticated)

// Global loading state (optional)
const isLoading = ref(false)

// Watch for route changes to set loading state
// watchEffect(() => {
//   isLoading.value = router.currentRoute.value.meta.loading || false
// })
</script>

<template>
  <div class="app-container">
    <!-- Fixed/Sticky Navigation when authenticated -->
    <RoleBasedNavigation v-if="isAuthenticated" class="fixed-navbar" />
    
    <div class="app-layout" :class="{ 'no-sidebar': !isAuthenticated }">
      <!-- Fixed Sidebar when authenticated -->
      <Sidebar v-if="isAuthenticated" class="fixed-sidebar" />
      
      <main class="main-content" :class="{ 'full-width': !isAuthenticated, 'with-sidebar': isAuthenticated }">
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
    
    <!-- Footer when authenticated -->
    <Footer v-if="isAuthenticated" class="main-footer" />
    
    <!-- Global realtime toast notifications -->
    <ToastHub />
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

/* Fixed/Sticky Navbar */
.fixed-navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background-color: #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  height: 60px; /* Adjust based on your navbar height */
}

.app-layout {
  display: flex;
  flex: 1;
  margin-top: 60px; /* Same as navbar height to prevent overlap */
}

.app-layout.no-sidebar {
  flex-direction: column;
  margin-top: 0; /* No margin when not authenticated */
}

/* Fixed Sidebar */
.fixed-sidebar {
  position: fixed;
  top: 0px; /* Start below the navbar */
  left: 0;
  bottom: 0;
  width: 250px; /* Adjust based on your sidebar width */
  z-index: 1000;
  background-color: #fff;
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
  overflow-y: auto ;
  overflow-x: hidden;
}

/* Main content adjustments */
.main-content {
  flex: 1;
  padding: 20px;
  position: relative;
  min-height: calc(100vh - 60px); /* Full height minus navbar */
}

.main-content.with-sidebar {
  margin-left: 250px; /* Same as sidebar width */
  padding: 20px;
}

.main-content.full-width {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0;
  margin-left: 0;
  min-height: 100vh;
}

/* Footer adjustments */
.main-footer {
  margin-left: 250px; /* Same as sidebar width when authenticated */
  margin-top: auto;
  background-color: #fff;
  border-top: 1px solid #e5e7eb;
  padding: 20px;
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
  z-index: 1001; /* Above navbar and sidebar */
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
@media (max-width: 1024px) {
  .fixed-sidebar {
    width: 200px;
  }
  
  .main-content.with-sidebar {
    margin-left: 200px;
  }
  
  .main-footer {
    margin-left: 200px;
  }
}

@media (max-width: 768px) {
  .fixed-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    width: 250px;
  }
  
  .fixed-sidebar.mobile-open {
    transform: translateX(0);
  }
  
  .main-content.with-sidebar {
    margin-left: 0;
  }
  
  .main-footer {
    margin-left: 0;
  }
  
  .app-layout {
    flex-direction: column;
  }
  
  .main-content {
    padding: 15px;
  }
  
  .main-content.full-width {
    padding: 0;
  }
}

@media (max-width: 480px) {
  .fixed-navbar {
    height: 50px;
  }
  
  .app-layout {
    margin-top: 50px;
  }
  
  .fixed-sidebar {
    top: 50px;
  }
  
  .main-content {
    min-height: calc(100vh - 50px);
    padding: 10px;
  }
}

/* Scrollbar styling for sidebar */
.fixed-sidebar::-webkit-scrollbar {
  width: 6px;
}

.fixed-sidebar::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.fixed-sidebar::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.fixed-sidebar::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
