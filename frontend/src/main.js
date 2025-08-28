import './assets/tailwind.css'
import '@fortawesome/fontawesome-free/css/all.css'

// main.js
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'

const app = createApp(App)

app.use(createPinia())
app.use(router)

// ✅ Initialize auth store (load token) before mounting
const auth = useAuthStore()
auth.initialize().then(async () => {
  // Initialize realtime after auth (so Authorization header is available)
  try {
    const { initEcho } = await import('./realtime/echo')
    initEcho()
  } catch (e) {
    console.warn('Realtime init failed:', e)
  }
  app.mount('#app')
})