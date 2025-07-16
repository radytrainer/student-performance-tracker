import './assets/tailwind.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import apiClient from './api/axiosConfig'
import '@fortawesome/fontawesome-free/css/all.min.css'
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
// Make axios available globally (optional)
app.config.globalProperties.$http = apiClient

app.use(router)

app.mount('#app')
