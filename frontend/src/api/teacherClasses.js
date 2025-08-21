import apiClient from './axiosConfig'

export const teacherClassesAPI = {
  // Classes Overview and Management
  async getMyClasses() {
    try {
      const response = await apiClient.get('/classes')
      return response.data
    } catch (error) {
      console.error('Error fetching teacher classes:', error)
      throw error
    }
  },

  async getClassDetails(classId) {
    try {
      const response = await apiClient.get(`/classes/${classId}`)
      return response.data
    } catch (error) {
      console.error('Error fetching class details:', error)
      throw error
    }
  },

  async getClassStats() {
    try {
      const response = await apiClient.get('/admin/classes-stats')
      return response.data
    } catch (error) {
      console.error('Error fetching class stats:', error)
      throw error
    }
  },

  // Student Management in Classes
  async getClassStudents(classId) {
    try {
      const response = await apiClient.get(`/admin/classes/${classId}/students`)
      return response.data
    } catch (error) {
      console.error('Error fetching class students:', error)
      throw error
    }
  },

  async getAllStudents() {
    try {
      const response = await apiClient.get('/students')
      return response.data
    } catch (error) {
      console.error('Error fetching all students:', error)
      throw error
    }
  },

  async addStudentToClass(classId, studentId) {
    try {
      const response = await apiClient.post(`/classes/${classId}/students`, { student_id: studentId })
      return response.data
    } catch (error) {
      console.error('Error adding student to class:', error)
      throw error
    }
  },

  async removeStudentFromClass(classId, studentId) {
    try {
      const response = await apiClient.delete(`/classes/${classId}/students/${studentId}`)
      return response.data
    } catch (error) {
      console.error('Error removing student from class:', error)
      throw error
    }
  },

  async bulkAddStudents(classId, studentIds) {
    try {
      const response = await apiClient.post(`/classes/${classId}/students/bulk`, { 
        student_ids: studentIds 
      })
      return response.data
    } catch (error) {
      console.error('Error bulk adding students:', error)
      throw error
    }
  },

  // Attendance Management
  async getAttendanceClasses() {
    try {
      const response = await apiClient.get('/attendance/my-classes')
      return response.data
    } catch (error) {
      console.error('Error fetching attendance classes:', error)
      throw error
    }
  },

  async getAttendanceData(params = {}) {
    try {
      const response = await apiClient.get('/attendance', { params })
      return response.data
    } catch (error) {
      console.error('Error fetching attendance data:', error)
      throw error
    }
  },

  async saveAttendance(attendanceData) {
    try {
      const response = await apiClient.post('/attendance', attendanceData)
      return response.data
    } catch (error) {
      console.error('Error saving attendance:', error)
      throw error
    }
  },

  async getAttendanceStats() {
    try {
      const response = await apiClient.get('/attendance/stats')
      return response.data
    } catch (error) {
      console.error('Error fetching attendance stats:', error)
      throw error
    }
  },

  async exportAttendance(params = {}) {
    try {
      const response = await apiClient.get('/attendance/export', { 
        params,
        responseType: 'blob'
      })
      return response
    } catch (error) {
      console.error('Error exporting attendance:', error)
      throw error
    }
  },

  // Grade Management
  async getGrades(params = {}) {
    try {
      const response = await apiClient.get('/grades', { params })
      return response.data
    } catch (error) {
      console.error('Error fetching grades:', error)
      throw error
    }
  },

  async createGrade(gradeData) {
    try {
      const response = await apiClient.post('/grades', gradeData)
      return response.data
    } catch (error) {
      console.error('Error creating grade:', error)
      throw error
    }
  },

  async updateGrade(gradeId, gradeData) {
    try {
      const response = await apiClient.put(`/grades/${gradeId}`, gradeData)
      return response.data
    } catch (error) {
      console.error('Error updating grade:', error)
      throw error
    }
  },

  async deleteGrade(gradeId) {
    try {
      const response = await apiClient.delete(`/grades/${gradeId}`)
      return response.data
    } catch (error) {
      console.error('Error deleting grade:', error)
      throw error
    }
  },

  async bulkCreateGrades(gradesData) {
    try {
      const response = await apiClient.post('/grades/bulk', gradesData)
      return response.data
    } catch (error) {
      console.error('Error bulk creating grades:', error)
      throw error
    }
  },

  async getStudentGrades(studentId) {
    try {
      const response = await apiClient.get(`/student-grades/${studentId}`)
      return response.data
    } catch (error) {
      console.error('Error fetching student grades:', error)
      throw error
    }
  },

  async getAssessmentTypes() {
    try {
      const response = await apiClient.get('/grades/assessment-types')
      return response.data
    } catch (error) {
      console.error('Error fetching assessment types:', error)
      throw error
    }
  },

  // Subject Management
  async getMyClassSubjects() {
    try {
      const response = await apiClient.get('/my-class-subjects')
      return response.data
    } catch (error) {
      console.error('Error fetching class subjects:', error)
      throw error
    }
  },

  async getSubjects() {
    try {
      const response = await apiClient.get('/subjects')
      return response.data
    } catch (error) {
      console.error('Error fetching subjects:', error)
      throw error
    }
  },

  // Terms Management
  async getTerms() {
    try {
      const response = await apiClient.get('/terms')
      return response.data
    } catch (error) {
      console.error('Error fetching terms:', error)
      throw error
    }
  },

  async getCurrentTerm() {
    try {
      const response = await apiClient.get('/terms')
      // Find current term from the list
      const terms = response.data?.data || response.data || []
      const currentTerm = terms.find(term => term.is_current) || terms[0]
      return { data: currentTerm }
    } catch (error) {
      console.error('Error fetching current term:', error)
      throw error
    }
  },

  // Class Notes (if we need to implement backend endpoint)
  async getClassNotes(classId) {
    try {
      const response = await apiClient.get(`/classes/${classId}/notes`)
      return response.data
    } catch (error) {
      console.error('Error fetching class notes:', error)
      throw error
    }
  },

  async createClassNote(classId, noteData) {
    try {
      const response = await apiClient.post(`/classes/${classId}/notes`, noteData)
      return response.data
    } catch (error) {
      console.error('Error creating class note:', error)
      throw error
    }
  },

  // Performance Analytics
  async getClassPerformance(classId, termId = null) {
    try {
      const params = termId ? { term_id: termId } : {}
      const response = await apiClient.get(`/classes/${classId}/performance`, { params })
      return response.data
    } catch (error) {
      console.error('Error fetching class performance:', error)
      throw error
    }
  },

  async getGradeDistribution(classId, subjectId = null) {
    try {
      const params = subjectId ? { subject_id: subjectId } : {}
      const response = await apiClient.get(`/classes/${classId}/grade-distribution`, { params })
      return response.data
    } catch (error) {
      console.error('Error fetching grade distribution:', error)
      throw error
    }
  },

  // Real-time data polling
  async pollClassData(classId) {
    try {
      const [classDetails, students, attendance, performance] = await Promise.all([
        this.getClassDetails(classId),
        this.getClassStudents(classId),
        this.getAttendanceStats(),
        this.getClassPerformance(classId)
      ])
      
      return {
        classDetails: classDetails.data,
        students: students.data,
        attendance: attendance.data,
        performance: performance.data
      }
    } catch (error) {
      console.error('Error polling class data:', error)
      throw error
    }
  }
}

export default teacherClassesAPI
