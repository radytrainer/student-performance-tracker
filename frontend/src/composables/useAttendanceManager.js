import { ref } from "vue"

export const useAttendanceManager = () => {
  // State
  const loading = ref(true)
  const classes = ref([])
  const attendanceData = ref([])
  const recentAttendance = ref([])
  const selectedClass = ref("")
  const selectedDate = ref(new Date().toISOString().split("T")[0])
  const selectedPeriod = ref("1")
  const hasChanges = ref(false)

  // Mock data
  const mockClasses = [
    { id: 1, subject: "Mathematics", section: "Section A", grade: "11" },
    { id: 2, subject: "Physics", section: "Section B", grade: "12" },
    { id: 3, subject: "Chemistry", section: "Section A", grade: "12" },
    { id: 4, subject: "Biology", section: "Section C", grade: "11" },
    { id: 5, subject: "English", section: "Section A", grade: "10" },
  ]

  const mockAttendanceData = [
    {
      studentId: 1,
      student: { id: "ST001", name: "Alice Johnson" },
      status: "present",
      excused: false,
      notes: "",
      attendanceRate: 95,
    },
    {
      studentId: 2,
      student: { id: "ST002", name: "Bob Smith" },
      status: "absent",
      excused: true,
      notes: "Doctor appointment",
      attendanceRate: 88,
    },
    {
      studentId: 3,
      student: { id: "ST003", name: "Carol Davis" },
      status: "late",
      excused: false,
      notes: "Transportation delay",
      attendanceRate: 92,
    },
    {
      studentId: 4,
      student: { id: "ST004", name: "David Wilson" },
      status: "present",
      excused: false,
      notes: "",
      attendanceRate: 78,
    },
    {
      studentId: 5,
      student: { id: "ST005", name: "Emma Brown" },
      status: "present",
      excused: false,
      notes: "",
      attendanceRate: 96,
    },
  ]

  const mockRecentAttendance = [
    { date: "2024-01-14", period: 1, present: 26, absent: 2, late: 0 },
    { date: "2024-01-13", period: 1, present: 25, absent: 1, late: 2 },
    { date: "2024-01-12", period: 1, present: 28, absent: 0, late: 0 },
    { date: "2024-01-11", period: 1, present: 24, absent: 3, late: 1 },
    { date: "2024-01-10", period: 1, present: 27, absent: 1, late: 0 },
  ]

  // Methods
  const loadAttendance = async () => {
    if (!selectedClass.value) return

    try {
      loading.value = true
      // Simulate API call
      await new Promise((resolve) => setTimeout(resolve, 500))
      attendanceData.value = [...mockAttendanceData]
      recentAttendance.value = [...mockRecentAttendance]
      hasChanges.value = false
    } catch (error) {
      console.error("Error loading attendance:", error)
    } finally {
      loading.value = false
    }
  }

  const saveAttendance = async () => {
    try {
      loading.value = true

      // TODO: Save to API
      const payload = {
        classId: selectedClass.value,
        date: selectedDate.value,
        period: selectedPeriod.value,
        records: attendanceData.value.map((record) => ({
          studentId: record.studentId,
          status: record.status,
          excused: record.excused,
          notes: record.notes,
        })),
      }

      console.log("Saving attendance:", payload)

      // Simulate API call
      await new Promise((resolve) => setTimeout(resolve, 1000))

      hasChanges.value = false

      // Show success notification
      alert("Attendance saved successfully!")
    } catch (error) {
      console.error("Error saving attendance:", error)
      alert("Failed to save attendance. Please try again.")
    } finally {
      loading.value = false
    }
  }

  const exportAttendance = () => {
    if (!attendanceData.value.length) return

    // Create CSV content
    const headers = ["Student ID", "Student Name", "Status", "Excused", "Notes", "Attendance Rate"]
    const csvContent = [
      headers.join(","),
      ...attendanceData.value.map((record) =>
        [
          record.student.id,
          `"${record.student.name}"`,
          record.status,
          record.excused ? "Yes" : "No",
          `"${record.notes}"`,
          `${record.attendanceRate}%`,
        ].join(","),
      ),
    ].join("\n")

    // Download CSV
    const blob = new Blob([csvContent], { type: "text/csv" })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement("a")
    link.href = url
    link.download = `attendance_${selectedDate.value}_period${selectedPeriod.value}.csv`
    link.click()
    window.URL.revokeObjectURL(url)
  }

  const initializeData = async () => {
    try {
      loading.value = true
      // Load classes
      await new Promise((resolve) => setTimeout(resolve, 1000))
      classes.value = [...mockClasses]
    } catch (error) {
      console.error("Error loading data:", error)
    } finally {
      loading.value = false
    }
  }

  // Initialize on composable creation
  initializeData()

  return {
    loading,
    classes,
    attendanceData,
    recentAttendance,
    selectedClass,
    selectedDate,
    selectedPeriod,
    hasChanges,
    loadAttendance,
    saveAttendance,
    exportAttendance,
  }
}
