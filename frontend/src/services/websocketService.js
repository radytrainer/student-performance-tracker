class WebSocketService {
  constructor() {
    this.ws = null
    this.reconnectInterval = null
    this.reconnectAttempts = 0
    this.maxReconnectAttempts = 5
    this.reconnectDelay = 2000
    this.listeners = new Map()
    this.isConnecting = false
    this.url = this.getWebSocketUrl()
  }

  getWebSocketUrl() {
    // Determine WebSocket URL based on environment
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:'
    const host = window.location.host
    
    // For development, use localhost:8080 (Laravel's default WebSocket port)
    if (host.includes('localhost') || host.includes('127.0.0.1')) {
      return `ws://localhost:6001/app/your-app-key`
    }
    
    return `${protocol}//${host}/ws`
  }

  connect(userId, userRole) {
    if (this.isConnecting || (this.ws && this.ws.readyState === WebSocket.OPEN)) {
      return Promise.resolve()
    }

    this.isConnecting = true
    
    return new Promise((resolve, reject) => {
      try {
        // Create WebSocket connection with user info
        const wsUrl = `${this.url}?user_id=${userId}&role=${userRole}`
        this.ws = new WebSocket(wsUrl)

        this.ws.onopen = () => {
          console.log('✅ WebSocket connected')
          this.isConnecting = false
          this.reconnectAttempts = 0
          
          // Send authentication message
          this.send({
            type: 'auth',
            user_id: userId,
            role: userRole
          })
          
          resolve()
        }

        this.ws.onmessage = (event) => {
          try {
            const data = JSON.parse(event.data)
            console.log('📨 WebSocket message received:', data)
            this.handleMessage(data)
          } catch (error) {
            console.error('Error parsing WebSocket message:', error)
          }
        }

        this.ws.onclose = (event) => {
          console.log('❌ WebSocket disconnected:', event.code, event.reason)
          this.isConnecting = false
          
          // Attempt to reconnect unless it was a clean close
          if (event.code !== 1000 && this.reconnectAttempts < this.maxReconnectAttempts) {
            this.scheduleReconnect(userId, userRole)
          }
        }

        this.ws.onerror = (error) => {
          console.error('🚨 WebSocket error:', error)
          this.isConnecting = false
          reject(error)
        }

      } catch (error) {
        this.isConnecting = false
        reject(error)
      }
    })
  }

  scheduleReconnect(userId, userRole) {
    if (this.reconnectInterval) {
      clearTimeout(this.reconnectInterval)
    }

    this.reconnectAttempts++
    const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1) // Exponential backoff

    console.log(`🔄 Attempting to reconnect in ${delay}ms... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`)

    this.reconnectInterval = setTimeout(() => {
      this.connect(userId, userRole)
        .catch(error => {
          console.error('Reconnection failed:', error)
        })
    }, delay)
  }

  disconnect() {
    if (this.reconnectInterval) {
      clearTimeout(this.reconnectInterval)
      this.reconnectInterval = null
    }

    if (this.ws && this.ws.readyState === WebSocket.OPEN) {
      this.ws.close(1000, 'Client disconnecting')
    }
    
    this.ws = null
    this.reconnectAttempts = 0
    this.listeners.clear()
  }

  send(data) {
    if (this.ws && this.ws.readyState === WebSocket.OPEN) {
      this.ws.send(JSON.stringify(data))
      return true
    } else {
      console.warn('⚠️ WebSocket not connected. Cannot send:', data)
      return false
    }
  }

  subscribe(event, callback) {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, new Set())
    }
    this.listeners.get(event).add(callback)
    
    console.log(`📝 Subscribed to ${event} event`)
    
    // Return unsubscribe function
    return () => {
      this.unsubscribe(event, callback)
    }
  }

  unsubscribe(event, callback) {
    if (this.listeners.has(event)) {
      this.listeners.get(event).delete(callback)
      
      // Remove event listener set if empty
      if (this.listeners.get(event).size === 0) {
        this.listeners.delete(event)
      }
    }
  }

  handleMessage(data) {
    const { type, event } = data

    // Handle different message types
    switch (type) {
      case 'grade_updated':
      case 'grade_created':
      case 'grade_deleted':
        this.notifyListeners('grade_change', data)
        this.notifyListeners(type, data)
        break
        
      case 'student_performance_update':
        this.notifyListeners('performance_update', data)
        break
        
      case 'notification':
        this.notifyListeners('notification', data)
        break
        
      case 'heartbeat':
        // Respond to heartbeat to keep connection alive
        this.send({ type: 'heartbeat_ack' })
        break
        
      default:
        if (event) {
          this.notifyListeners(event, data)
        }
        console.log('Unhandled WebSocket message type:', type)
    }
  }

  notifyListeners(event, data) {
    if (this.listeners.has(event)) {
      this.listeners.get(event).forEach(callback => {
        try {
          callback(data)
        } catch (error) {
          console.error(`Error in WebSocket listener for ${event}:`, error)
        }
      })
    }
  }

  // Specific methods for grade-related events
  subscribeToGradeUpdates(studentId, callback) {
    return this.subscribe('grade_change', (data) => {
      // Only notify if the grade update affects this student
      if (data.student_id === studentId || data.affected_students?.includes(studentId)) {
        callback(data)
      }
    })
  }

  subscribeToPerformanceUpdates(studentId, callback) {
    return this.subscribe('performance_update', (data) => {
      if (data.student_id === studentId) {
        callback(data)
      }
    })
  }

  subscribeToNotifications(userId, callback) {
    return this.subscribe('notification', (data) => {
      if (data.user_id === userId || data.broadcast) {
        callback(data)
      }
    })
  }

  // Connection status methods
  isConnected() {
    return this.ws && this.ws.readyState === WebSocket.OPEN
  }

  getConnectionState() {
    if (!this.ws) return 'CLOSED'
    
    switch (this.ws.readyState) {
      case WebSocket.CONNECTING:
        return 'CONNECTING'
      case WebSocket.OPEN:
        return 'OPEN'
      case WebSocket.CLOSING:
        return 'CLOSING'
      case WebSocket.CLOSED:
        return 'CLOSED'
      default:
        return 'UNKNOWN'
    }
  }
}

// Create singleton instance
const websocketService = new WebSocketService()

export default websocketService
