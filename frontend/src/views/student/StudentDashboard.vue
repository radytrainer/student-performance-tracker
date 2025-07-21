<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <DashboardHeader :student="student" />

    <div class="max-w-7xl mx-auto px-6 py-8">
      <SummaryStats :stats="summaryStats" />

      <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
        <!-- Main Content Area -->
        <div class="xl:col-span-3 space-y-8">
          <ClassSchedule 
            :current-week-schedule="weeklySchedule" 
            :next-week-schedule="nextWeekSchedule" 
          />
          <AcademicProgress 
            :progress="academicProgress" 
            :current-g-p-a="summaryStats.currentGPA" 
          />
          <UpcomingItems :items="upcomingItems" />
          
          <!-- Feedback & Evaluation -->
          <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-100">
              <h2 class="text-2xl font-bold text-gray-900">Feedback & Evaluation</h2>
            </div>
            <div class="p-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <h3 class="text-lg font-bold text-gray-900">Recent Feedback</h3>
                  <div v-for="feedback in recentFeedback" :key="feedback.id" class="p-4 bg-gray-50 rounded-2xl">
                    <h4 class="font-semibold text-gray-900">{{ feedback.subject }}</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ feedback.comment }}</p>
                    <p class="text-xs text-gray-500 mt-2">- {{ feedback.teacher }}</p>
                  </div>
                </div>
                <div class="space-y-4">
                  <h3 class="text-lg font-bold text-gray-900">Evaluation Options</h3>
                  <button class="w-full p-4 bg-blue-50 text-blue-700 rounded-2xl hover:bg-blue-100 transition-colors">
                    <StarIcon class="w-5 h-5 inline mr-2" />
                    Evaluate Teachers
                  </button>
                  <button class="w-full p-4 bg-gray-50 text-gray-700 rounded-2xl hover:bg-gray-100 transition-colors">
                    <EyeIcon class="w-5 h-5 inline mr-2" />
                    View Past Evaluations
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
          <NotificationPanel :notifications="notifications" :unread-count="unreadNotifications" />
          <AttendanceRecord :record="attendanceRecord" />
          <ResourcesMaterials :resources="resources" />
          <SupportContact />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import DashboardHeader from '@/components/Dashboard/DashboardHeader.vue'
import SummaryStats from '@/components/Dashboard/SummaryStats.vue'
import ClassSchedule from '@/components/Dashboard/ClassSchedule.vue'
import AcademicProgress from '@/components/Dashboard/AcademicProgress.vue'
import UpcomingItems from '@/components/Dashboard/UpcommingItems.vue'
import NotificationPanel from '@/components/Dashboard/NotificationPanel.vue'
import AttendanceRecord from '@/components/Dashboard/attendanceRecord.vue'
import ResourcesMaterials from '@/components/Dashboard/ResourcesMaterials.vue'
import SupportContact from '@/components/Dashboard/SupportContact.vue'

// Icon components for inline use
const StarIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>' }
const EyeIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>' }

// State management
const unreadNotifications = ref(5)

// Student data
const student = ref({
  name: 'Sarah Johnson',
  course: 'Computer Science - Year 3',
  studentId: 'CS2024001',
  lastLogin: 'Today 9:30 AM'
})

// Summary statistics
const summaryStats = ref({
  totalCourses: 6,
  assignmentsSubmitted: 12,
  totalAssignments: 15,
  attendanceRate: 94,
  currentGPA: 3.7,
  lastLogin: 'Today'
})

// Weekly schedule
const weeklySchedule = ref([
  {
    id: 1,
    day: 'Monday',
    time: '09:00',
    subject: 'Data Structures & Algorithms',
    teacher: 'Dr. Smith',
    room: 'Room 301',
    status: 'Upcoming',
    isOnline: false
  },
  {
    id: 2,
    day: 'Monday',
    time: '11:00',
    subject: 'Web Development',
    teacher: 'Prof. Johnson',
    room: 'Online',
    status: 'In Progress',
    isOnline: true
  },
  {
    id: 3,
    day: 'Tuesday',
    time: '14:00',
    subject: 'Database Systems',
    teacher: 'Dr. Brown',
    room: 'Room 205',
    status: 'Upcoming',
    isOnline: false
  },
  {
    id: 4,
    day: 'Wednesday',
    time: '10:00',
    subject: 'Software Engineering',
    teacher: 'Prof. Davis',
    room: 'Online',
    status: 'Upcoming',
    isOnline: true
  }
])

// Next week's schedule
const nextWeekSchedule = ref([
  {
    id: 5,
    day: 'Monday',
    time: '10:00',
    subject: 'Advanced Algorithms',
    teacher: 'Dr. Martinez',
    room: 'Room 402',
    status: 'Upcoming',
    isOnline: false
  },
  {
    id: 6,
    day: 'Tuesday',
    time: '13:00',
    subject: 'Machine Learning',
    teacher: 'Prof. Chen',
    room: 'Online',
    status: 'Upcoming',
    isOnline: true
  },
  {
    id: 7,
    day: 'Wednesday',
    time: '09:00',
    subject: 'Software Architecture',
    teacher: 'Dr. Wilson',
    room: 'Room 301',
    status: 'Upcoming',
    isOnline: false
  },
  {
    id: 8,
    day: 'Thursday',
    time: '15:00',
    subject: 'Project Management',
    teacher: 'Prof. Taylor',
    room: 'Room 205',
    status: 'Upcoming',
    isOnline: false
  }
])

// Academic progress
const academicProgress = ref([
  { id: 1, name: 'Mathematics', grade: 'A-', progress: 85, recentScore: '92%' },
  { id: 2, name: 'Programming', grade: 'A', progress: 92, recentScore: '95%' },
  { id: 3, name: 'Physics', grade: 'B+', progress: 78, recentScore: '88%' },
  { id: 4, name: 'English', grade: 'A-', progress: 88, recentScore: '90%' }
])

// Upcoming items
const upcomingItems = ref([
  {
    id: 1,
    title: 'Final Project Submission',
    subject: 'Web Development',
    dueDate: 'Dec 15, 2024',
    type: 'assignment',
    priority: 'High',
    timeLeft: '3 days left',
    submissionStatus: 'Not Submitted'
  },
  {
    id: 2,
    title: 'Midterm Exam',
    subject: 'Data Structures',
    dueDate: 'Dec 18, 2024',
    type: 'exam',
    priority: 'High',
    timeLeft: '6 days left',
    submissionStatus: 'Scheduled'
  },
  {
    id: 3,
    title: 'Research Paper',
    subject: 'Database Systems',
    dueDate: 'Dec 22, 2024',
    type: 'assignment',
    priority: 'Medium',
    timeLeft: '10 days left',
    submissionStatus: 'Draft Saved'
  }
])

// Notifications
const notifications = ref([
  {
    id: 1,
    type: 'assignment',
    title: 'New Assignment Posted',
    message: 'Web Development - Final Project guidelines are now available',
    time: '2 hours ago'
  },
  {
    id: 2,
    type: 'grade',
    title: 'Grade Updated',
    message: 'Your Mathematics quiz grade has been posted',
    time: '1 day ago'
  },
  {
    id: 3,
    type: 'announcement',
    title: 'System Maintenance',
    message: 'Scheduled maintenance on Dec 20, 2024 from 2-4 AM',
    time: '2 days ago'
  },
  {
    id: 4,
    type: 'deadline',
    title: 'Assignment Due Soon',
    message: 'Programming assignment due in 2 days',
    time: '3 hours ago'
  }
])

// Attendance record
const attendanceRecord = ref({
  thisMonth: 94,
  totalAbsences: 3,
  lateCheckins: 2,
  weeklyAverage: 96
})

// Recent feedback
const recentFeedback = ref([
  {
    id: 1,
    subject: 'Web Development',
    comment: 'Excellent work on the React project. Great attention to detail.',
    teacher: 'Prof. Johnson'
  },
  {
    id: 2,
    subject: 'Mathematics',
    comment: 'Good improvement in problem-solving approach.',
    teacher: 'Dr. Smith'
  }
])

// Resources
const resources = ref([
  {
    id: 1,
    title: 'Lecture Notes - Week 12',
    subject: 'Data Structures',
    type: 'document'
  },
  {
    id: 2,
    title: 'Tutorial Video - React Hooks',
    subject: 'Web Development',
    type: 'video'
  },
  {
    id: 3,
    title: 'Reading List - Database Design',
    subject: 'Database Systems',
    type: 'link'
  },
  {
    id: 4,
    title: 'Assignment Template',
    subject: 'Software Engineering',
    type: 'document'
  }
])
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