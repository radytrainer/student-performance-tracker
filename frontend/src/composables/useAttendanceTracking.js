import { ref, computed, watch } from 'vue'
import { teacherClassesAPI } from '@/api/teacherClasses'
import { useToast } from '@/composables/useToast'

export function useAttendanceTracking() {
  // State
  const selectedDate = ref(new Date().toISOString().split('T')[0])
  const selectedClass = ref(null)
  const selectedPeriod = ref(null)
  const attendanceRecords = ref([])
  const students = ref([])
  const loading = ref(false)
  const saving = ref(false)
  
  const { showSuccess, showError } = useToast()

  // Computed
  const attendanceByStudent = computed(() => {
    const attendance = {}
    attendanceRecords.value.forEach(record => {
      attendance[record.student_id] = record.status
    })
    return attendance
  })

  const attendanceStats = computed(() => {
    const total = students.value.length
    const present = attendanceRecords.value.filter(r => r.status === 'present').length
    const absent = attendanceRecords.value.filter(r => r.status === 'absent').length
    const late = attendanceRecords.value.filter(r => r.status === 'late').length
    const excused = attendanceRecords.value.filter(r => r.status === 'excused').length
    
    return {
      total,
      present,
      absent,
      late,
      excused,
      presentPercentage: total > 0 ? Math.round((present / total) * 100) : 0
    }
  })

  // Methods
  const fetchStudentsForAttendance = async (classId) => {
    try {
      loading.value = true
      const response = await teacherClassesAPI.getClassStudents(classId)
      students.value = response.data || response
    } catch (error) {
      showError('Failed to fetch students for attendance')
      console.error('Error fetching students:', error)
    } finally {
      loading.value = false
    }
  }

  const fetchAttendanceForDate = async (classId, date) => {
    try {
      loading.value = true
      const response = await teacherClassesAPI.getAttendanceData({
        class_id: classId,
        date: date
      })
      attendanceRecords.value = response.data || response
      
      // Initialize missing records
      students.value.forEach(student => {
        const studentId = student.id || student.user_id
        if (!attendanceRecords.value.find(r => r.student_id === studentId)) {
          attendanceRecords.value.push({
            student_id: studentId,
            class_id: classId,
            date: date,
            status: 'present', // Default status
            notes: '',
            recorded_at: null
          })
        }
      })
    } catch (error) {
      showError('Failed to fetch attendance data')
      console.error('Error fetching attendance:', error)
    } finally {
      loading.value = false
    }
  }

  const updateAttendanceStatus = (studentId, status) => {
    const record = attendanceRecords.value.find(r => r.student_id === studentId)
    if (record) {
      record.status = status
    }
  }

  const updateAttendanceNotes = (studentId, notes) => {
    const record = attendanceRecords.value.find(r => r.student_id === studentId)
    if (record) {
      record.notes = notes
    }
  }

  const saveAttendance = async () => {
    if (!selectedClass.value || attendanceRecords.value.length === 0) return

    try {
      saving.value = true
      
      // Prepare data for API
      const attendanceData = {
        class_id: selectedClass.value.id,
        date: selectedDate.value,
        attendance: attendanceRecords.value.map(record => ({
          student_id: record.student_id,
          status: record.status,
          notes: record.notes
        }))
      }
      
      await teacherClassesAPI.saveAttendance(attendanceData)
      showSuccess('Attendance saved successfully')
      
      // Update recorded_at timestamp for local records
      attendanceRecords.value.forEach(record => {
        record.recorded_at = new Date().toISOString()
      })
      
    } catch (error) {
      showError('Failed to save attendance')
      console.error('Error saving attendance:', error)
    } finally {
      saving.value = false
    }
  }

  const markAllPresent = () => {
    attendanceRecords.value.forEach(record => {
      record.status = 'present'
    })
  }

  const markAllAbsent = () => {
    attendanceRecords.value.forEach(record => {
      record.status = 'absent'
    })
  }

  const toggleStudentAttendance = (studentId) => {
    const record = attendanceRecords.value.find(r => r.student_id === studentId)
    if (record) {
      record.status = record.status === 'present' ? 'absent' : 'present'
    }
  }

  const initializeAttendanceForClass = async (classData) => {
    selectedClass.value = classData
    await fetchStudentsForAttendance(classData.id)
    await fetchAttendanceForDate(classData.id, selectedDate.value)
  }

  const resetAttendance = () => {
    selectedClass.value = null
    selectedPeriod.value = null
    attendanceRecords.value = []
    students.value = []
  }

  // Watch for date changes
  watch(selectedDate, (newDate) => {
    if (selectedClass.value && newDate) {
      fetchAttendanceForDate(selectedClass.value.id, newDate)
    }
  })

  return {
    // State
    selectedDate,
    selectedClass,
    selectedPeriod,
    attendanceRecords,
    students,
    loading,
    saving,
    
    // Computed
    attendanceByStudent,
    attendanceStats,
    
    // Methods
    fetchStudentsForAttendance,
    fetchAttendanceForDate,
    updateAttendanceStatus,
    updateAttendanceNotes,
    saveAttendance,
    markAllPresent,
    markAllAbsent,
    toggleStudentAttendance,
    initializeAttendanceForClass,
    resetAttendance
  }
}
