import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import websocketService from '@/services/websocketService'

export function useWebSocket() {
  const authStore = useAuthStore()
  const isConnected = ref(false)
  const connectionState = ref('CLOSED')
  const error = ref(null)

  const connect = async () => {
    try {
      if (!authStore.user) {
        throw new Error('User not authenticated')
      }

      const userId = authStore.user.id
      const userRole = authStore.user.role
      
      await websocketService.connect(userId, userRole)
      isConnected.value = true
      connectionState.value = 'OPEN'
      error.value = null
      
      console.log('🔌 WebSocket connected successfully')
    } catch (err) {
      console.error('WebSocket connection failed:', err)
      error.value = err.message
      isConnected.value = false
      connectionState.value = 'CLOSED'
    }
  }

  const disconnect = () => {
    websocketService.disconnect()
    isConnected.value = false
    connectionState.value = 'CLOSED'
  }

  const subscribe = (event, callback) => {
    return websocketService.subscribe(event, callback)
  }

  const send = (data) => {
    return websocketService.send(data)
  }

  const subscribeToGradeUpdates = (studentId, callback) => {
    return websocketService.subscribeToGradeUpdates(studentId, callback)
  }

  const subscribeToPerformanceUpdates = (studentId, callback) => {
    return websocketService.subscribeToPerformanceUpdates(studentId, callback)
  }

  const subscribeToNotifications = (userId, callback) => {
    return websocketService.subscribeToNotifications(userId, callback)
  }

  // Auto-connect on mount if user is authenticated
  onMounted(() => {
    if (authStore.user && authStore.isLoggedIn) {
      connect()
    }
  })

  // Cleanup on unmount
  onUnmounted(() => {
    // Don't disconnect here as other components might be using it
    // Just clean up any component-specific subscriptions
  })

  return {
    isConnected,
    connectionState,
    error,
    connect,
    disconnect,
    subscribe,
    send,
    subscribeToGradeUpdates,
    subscribeToPerformanceUpdates,
    subscribeToNotifications
  }
}

export function useGradeUpdates(studentId) {
  const { subscribeToGradeUpdates } = useWebSocket()
  const gradeUpdates = ref([])
  const lastUpdate = ref(null)

  let unsubscribe = null

  const startListening = () => {
    if (unsubscribe) {
      unsubscribe()
    }

    unsubscribe = subscribeToGradeUpdates(studentId, (data) => {
      console.log('📊 Grade update received:', data)
      
      gradeUpdates.value.unshift({
        ...data,
        timestamp: new Date()
      })
      
      lastUpdate.value = data
      
      // Keep only last 50 updates
      if (gradeUpdates.value.length > 50) {
        gradeUpdates.value = gradeUpdates.value.slice(0, 50)
      }
    })
  }

  const stopListening = () => {
    if (unsubscribe) {
      unsubscribe()
      unsubscribe = null
    }
  }

  onMounted(() => {
    if (studentId) {
      startListening()
    }
  })

  onUnmounted(() => {
    stopListening()
  })

  return {
    gradeUpdates,
    lastUpdate,
    startListening,
    stopListening
  }
}

export function useNotifications(userId) {
  const { subscribeToNotifications } = useWebSocket()
  const notifications = ref([])
  const unreadCount = ref(0)

  let unsubscribe = null

  const startListening = () => {
    if (unsubscribe) {
      unsubscribe()
    }

    unsubscribe = subscribeToNotifications(userId, (data) => {
      console.log('🔔 Notification received:', data)
      
      notifications.value.unshift({
        ...data,
        timestamp: new Date(),
        read: false,
        id: Date.now() + Math.random()
      })
      
      unreadCount.value++
      
      // Show browser notification if permission granted
      if (Notification.permission === 'granted') {
        new Notification(data.title || 'New Update', {
          body: data.message,
          icon: '/favicon.ico',
          badge: '/favicon.ico'
        })
      }
    })
  }

  const markAsRead = (notificationId) => {
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification && !notification.read) {
      notification.read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
  }

  const markAllAsRead = () => {
    notifications.value.forEach(notification => {
      notification.read = true
    })
    unreadCount.value = 0
  }

  const clearNotifications = () => {
    notifications.value = []
    unreadCount.value = 0
  }

  const requestNotificationPermission = async () => {
    if ('Notification' in window) {
      const permission = await Notification.requestPermission()
      return permission === 'granted'
    }
    return false
  }

  onMounted(() => {
    if (userId) {
      startListening()
      // Request notification permission on first load
      requestNotificationPermission()
    }
  })

  onUnmounted(() => {
    if (unsubscribe) {
      unsubscribe()
    }
  })

  return {
    notifications,
    unreadCount,
    markAsRead,
    markAllAsRead,
    clearNotifications,
    requestNotificationPermission
  }
}
