<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <TeacherHeader :teacher="teacher" />

    <div class="max-w-7xl mx-auto px-6 py-8">
      <TeacherStats :stats="teacherStats" />

      <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
        <!-- Main Content Area -->
        <div class="xl:col-span-3 space-y-8">
          <ClassSchedule :schedule="todaySchedule" />
          <StudentProgress :students="studentProgress" />
          <AssignmentManager :assignments="assignments" />
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
          <GradingQueue :grading-queue="gradingQueue" :pending-count="pendingGrades" />
          <ClassAnalytics :grade-distribution="gradeDistribution" :analytics="classAnalytics" />
          <QuickActions />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import TeacherHeader from '@/components/teacher/Dashboard/TeacherHeader.vue'
import TeacherStats from '@/components/teacher/Dashboard/TeacherStats.vue'
import ClassSchedule from '@/components/teacher/Dashboard/ClassSchedule.vue'
import StudentProgress from '@/components/teacher/Dashboard/StudentProgress.vue'
import AssignmentManager from '@/components/teacher/Dashboard/AssignmentManager.vue'
import GradingQueue from '@/components/teacher/Dashboard/GradingQueue.vue'
import ClassAnalytics from '@/components/teacher/Dashboard/ClassAnalytics.vue'
import QuickActions from '@/components/teacher/Dashboard/QuickActions.vue'

// Teacher data
const teacher = ref({
  name: 'Dr. Emily Johnson',
  department: 'Mathematics Department',
  employeeId: 'EMP2024001',
  lastLogin: 'Today 8:15 AM'
})

// Teacher statistics
const teacherStats = ref({
  totalStudents: 156,
  activeClasses: 4,
  pendingGrades: 23,
  upcomingClasses: 3,
  classAverage: 87
})

// Today's schedule
const todaySchedule = ref([
  {
    id: 1,
    time: '09:00',
    duration: '90 min',
    subject: 'Advanced Calculus',
    room: 'Room 301',
    students: 28,
    type: 'Lecture',
    status: 'Upcoming'
  },
  {
    id: 2,
    time: '11:00',
    duration: '60 min',
    subject: 'Linear Algebra',
    room: 'Room 205',
    students: 35,
    type: 'Tutorial',
    status: 'In Progress'
  },
  {
    id: 3,
    time: '14:00',
    duration: '90 min',
    subject: 'Statistics',
    room: 'Room 402',
    students: 42,
    type: 'Lecture',
    status: 'Upcoming'
  },
  {
    id: 4,
    time: '16:00',
    duration: '60 min',
    subject: 'Office Hours',
    room: 'Office 301B',
    students: 0,
    type: 'Consultation',
    status: 'Upcoming'
  }
])

// Student progress data
const studentProgress = ref([
  {
    id: 1,
    name: 'Sarah Johnson',
    studentId: 'CS2024001',
    currentGrade: 'A-',
    attendance: 94,
    assignmentsCompleted: 12,
    totalAssignments: 15,
    lastActivity: '2 hours ago'
  },
  {
    id: 2,
    name: 'Michael Chen',
    studentId: 'CS2024002',
    currentGrade: 'B+',
    attendance: 88,
    assignmentsCompleted: 11,
    totalAssignments: 15,
    lastActivity: '1 day ago'
  },
  {
    id: 3,
    name: 'Emma Wilson',
    studentId: 'CS2024003',
    currentGrade: 'A',
    attendance: 96,
    assignmentsCompleted: 14,
    totalAssignments: 15,
    lastActivity: '3 hours ago'
  },
  {
    id: 4,
    name: 'David Rodriguez',
    studentId: 'CS2024004',
    currentGrade: 'B',
    attendance: 82,
    assignmentsCompleted: 10,
    totalAssignments: 15,
    lastActivity: '5 hours ago'
  }
])

// Assignments data
const assignments = ref([
  {
    id: 1,
    title: 'Calculus Problem Set 5',
    subject: 'Advanced Calculus',
    dueDate: 'Dec 15, 2024',
    type: 'assignment',
    submitted: 24,
    total: 28
  },
  {
    id: 2,
    title: 'Midterm Exam',
    subject: 'Linear Algebra',
    dueDate: 'Dec 18, 2024',
    type: 'exam',
    submitted: 32,
    total: 35
  },
  {
    id: 3,
    title: 'Statistics Project',
    subject: 'Statistics',
    dueDate: 'Dec 22, 2024',
    type: 'assignment',
    submitted: 18,
    total: 42
  }
])

// Grading queue
const gradingQueue = ref([
  {
    id: 1,
    studentName: 'Sarah Johnson',
    assignmentTitle: 'Calculus Problem Set 4',
    submittedDate: '2 days ago'
  },
  {
    id: 2,
    studentName: 'Michael Chen',
    assignmentTitle: 'Linear Algebra Quiz',
    submittedDate: '1 day ago'
  },
  {
    id: 3,
    studentName: 'Emma Wilson',
    assignmentTitle: 'Statistics Homework',
    submittedDate: '3 hours ago'
  },
  {
    id: 4,
    studentName: 'David Rodriguez',
    assignmentTitle: 'Calculus Problem Set 4',
    submittedDate: '1 day ago'
  },
  {
    id: 5,
    studentName: 'Lisa Park',
    assignmentTitle: 'Linear Algebra Quiz',
    submittedDate: '4 hours ago'
  }
])

const pendingGrades = ref(23)

// Grade distribution
const gradeDistribution = ref([
  { grade: 'A', count: 45, percentage: 85 },
  { grade: 'B', count: 38, percentage: 72 },
  { grade: 'C', count: 28, percentage: 53 },
  { grade: 'D', count: 12, percentage: 23 },
  { grade: 'F', count: 5, percentage: 9 }
])

// Class analytics
const classAnalytics = ref({
  averageAttendance: 89,
  absentToday: 8,
  completionRate: 84
})
</script>

<style scoped>
/* Modern animations and transitions */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Custom backdrop blur */
.backdrop-blur-xl {
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
}

/* Gradient text effect */
.bg-clip-text {
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Enhanced focus states */
button:focus,
a:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>