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
app.mount('#app')

// ✅ Initialize auth store (load token)
const auth = useAuthStore()
auth.initialize()

