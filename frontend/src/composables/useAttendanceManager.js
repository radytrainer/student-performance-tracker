import { ref, computed } from 'vue'
import { useAuth } from '@/composables/useAuth'

export function useAttendanceManager() {
  const { getAuthToken } = useAuth()
  
  const loading = ref(false)
  const classes = ref([])
  const attendanceData = ref([])
  const recentAttendance = ref([])
  const selectedClass = ref('')
  const selectedDate = ref(new Date().toISOString().split('T')[0])
  const selectedPeriod = ref('1')
  const hasChanges = ref(false)

  // API helper function
  const apiCall = async (url, options = {}) => {
    const token = getAuthToken()
    const defaultOptions = {
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'X-Requested-With': 'XMLHttpRequest',
        ...options.headers
      }
    }

    const response = await fetch(url, { ...defaultOptions, ...options })
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    return await response.json()
  }

  const loadClasses = async () => {
    try {
      loading.value = true
      const data = await apiCall('/api/attendance/classes')
      classes.value = data.classes
    } catch (error) {
      console.error('Error loading classes:', error)
      // Handle error (show notification, etc.)
    } finally {
      loading.value = false
    }
  }

  const loadAttendance = async () => {
    if (!selectedClass.value) {
      attendanceData.value = []
      return
    }
    
    try {
      loading.value = true
      const params = new URLSearchParams({
        class_id: selectedClass.value,
        date: selectedDate.value,
        period: selectedPeriod.value
      })
      
      const data = await apiCall(`/api/attendance?${params}`)
      attendanceData.value = data.attendances
      hasChanges.value = false
      
      // Load recent attendance for the selected class
      await loadRecentAttendance()
    } catch (error) {
      console.error('Error loading attendance:', error)
    } finally {
      loading.value = false
    }
  }

  const loadRecentAttendance = async () => {
    if (!selectedClass.value) return
    
    try {
      const data = await apiCall(`/api/attendance/recent/${selectedClass.value}`)
      recentAttendance.value = data.recent_attendance
    } catch (error) {
      console.error('Error loading recent attendance:', error)
    }
  }

  const saveAttendance = async () => {
    if (!selectedClass.value || !hasChanges.value) return
    
    try {
      loading.value = true
      
      const payload = {
        class_id: selectedClass.value,
        date: selectedDate.value,
        period: selectedPeriod.value,
        attendance: attendanceData.value
      }
      
      const data = await apiCall('/api/attendance/bulk', {
        method: 'POST',
        body: JSON.stringify(payload)
      })
      
      hasChanges.value = false
      
      // Show success message
      console.log('Attendance saved successfully:', data.message)
      
      // Reload recent attendance
      await loadRecentAttendance()
      
      return { success: true, message: data.message }
    } catch (error) {
      console.error('Error saving attendance:', error)
      return { success: false, message: 'Failed to save attendance' }
    } finally {
      loading.value = false
    }
  }

  const exportAttendance = async () => {
    if (!selectedClass.value) return
    
    try {
      const params = new URLSearchParams({
        class_id: selectedClass.value,
        start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
        end_date: selectedDate.value
      })
      
      const token = getAuthToken()
      const response = await fetch(`/api/attendance/export?${params}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        }
      })
      
      if (response.ok) {
        const blob = await response.blob()
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `attendance_export_${selectedDate.value}.csv`
        document.body.appendChild(a)
        a.click()
        window.URL.revokeObjectURL(url)
        document.body.removeChild(a)
      }
    } catch (error) {
      console.error('Error exporting attendance:', error)
    }
  }

  const loadCalendarData = async (month) => {
    if (!selectedClass.value) return []
    
    try {
      const params = new URLSearchParams({
        class_id: selectedClass.value,
        month: month || new Date().toISOString().slice(0, 7)
      })
      
      const data = await apiCall(`/api/attendance/calendar?${params}`)
      return data.calendar_data
    } catch (error) {
      console.error('Error loading calendar data:', error)
      return []
    }
  }

  const markChanged = () => {
    hasChanges.value = true
  }

  // Initialize on composable creation
  const initialize = async () => {
    await loadClasses()
  }

  return {
    // State
    loading,
    classes,
    attendanceData,
    recentAttendance,
    selectedClass,
    selectedDate,
    selectedPeriod,
    hasChanges,
    
    // Methods
    loadClasses,
    loadAttendance,
    saveAttendance,
    exportAttendance,
    loadCalendarData,
    markChanged,
    initialize
  }
}