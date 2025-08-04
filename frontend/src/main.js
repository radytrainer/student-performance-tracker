import './assets/tailwind.css'

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
auth.initialize().then(() => {
  app.mount('#app')
})