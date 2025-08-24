import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Make Pusher available globally (Echo expects window.Pusher)
window.Pusher = Pusher

// Resolve base URL for auth endpoint from API base (strip trailing /api)
function getApiRoot() {
  const api = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
  return api.replace(/\/?api\/?$/, '')
}

export function initEcho() {
  const apiRoot = getApiRoot()
  const token = localStorage.getItem('auth_token')

  const key = import.meta.env.VITE_PUSHER_APP_KEY || import.meta.env.VITE_PUSHER_KEY
  const cluster = import.meta.env.VITE_PUSHER_APP_CLUSTER || import.meta.env.VITE_PUSHER_CLUSTER || 'mt1'

  if (!key) {
    if (import.meta.env.DEV) {
      console.warn('[Echo] Missing VITE_PUSHER_APP_KEY. Realtime disabled.')
    }
    return null
  }

  const echo = new Echo({
    broadcaster: 'pusher',
    key,
    cluster,
    forceTLS: true,
    // Auth endpoint for private channels
    authEndpoint: `${apiRoot}/broadcasting/auth`,
    // Ensure Sanctum/cookies and bearer token are included
    auth: {
      headers: {
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        'X-Requested-With': 'XMLHttpRequest'
      },
      withCredentials: true
    },
    // Optional: if using self-hosted websockets set wsHost/wsPort here
  })

  window.Echo = echo
  return echo
}
