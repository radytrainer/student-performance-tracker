import apiClient from './axiosConfig';

const gradesAPI = {
  // Get all grades for a specific student
  getStudentGrades(studentId) {
    return apiClient.get(`/students/${studentId}/grades`);
  },

  // Get grades with filters (subject, term, etc.)
  getStudentGradesFiltered(studentId, filters = {}) {
    const params = new URLSearchParams();
    if (filters.subject) params.append('subject', filters.subject);
    if (filters.term) params.append('term', filters.term);
    if (filters.assessment_type) params.append('assessment_type', filters.assessment_type);
    
    return apiClient.get(`/students/${studentId}/grades?${params.toString()}`);
  },

  // Get grade summary/analytics for a student
  getStudentGradeSummary(studentId) {
    return apiClient.get(`/students/${studentId}/grades/summary`);
  },

  // Get student's GPA calculation
  getStudentGPA(studentId) {
    return apiClient.get(`/students/${studentId}/gpa`);
  },

  // Get grade history for a specific subject
  getSubjectGradeHistory(studentId, subject) {
    return apiClient.get(`/students/${studentId}/grades/subject/${subject}`);
  },
};

export default gradesAPI;
