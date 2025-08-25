import { ref } from 'vue'

const toasts = ref([])

export function useToast() {
  const showToast = (message, type = 'info', duration = 3000) => {
    const id = Date.now() + Math.random()
    const toast = {
      id,
      message,
      type,
      visible: true
    }
    
    toasts.value.push(toast)
    
    if (duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, duration)
    }
    
    return id
  }

  const removeToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  const showSuccess = (message, duration = 3000) => {
    return showToast(message, 'success', duration)
  }

  const showError = (message, duration = 5000) => {
    return showToast(message, 'error', duration)
  }

  const showWarning = (message, duration = 4000) => {
    return showToast(message, 'warning', duration)
  }

  const showInfo = (message, duration = 3000) => {
    return showToast(message, 'info', duration)
  }

  const clearAllToasts = () => {
    toasts.value = []
  }

  return {
    toasts,
    showToast,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    removeToast,
    clearAllToasts
  }
}
