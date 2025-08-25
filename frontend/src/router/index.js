import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAuth } from '@/composables/useAuth'

const routes = [
  {
    path: '/',
    name: 'Home',
    redirect: () => {
      const { getDefaultRoute } = useAuth()
      return getDefaultRoute()
    }
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { guestOnly: true }
  },

  // Super Admin routes
  {
    path: '/super-admin',
    redirect: '/super-admin/dashboard',
    meta: { requiresAuth: true, requiresPermission: 'super_admin.manage_schools' }
  },
  {
    path: '/super-admin/dashboard',
    name: 'SuperAdminDashboard',
    component: () => import('@/views/superAdmin/SuperAdminDashboard.vue'),
    meta: { 
      requiresAuth: true, 
      requiresPermission: 'super_admin.manage_schools',
      title: 'Super Admin Dashboard'
    }
  },
  {
    path: '/super-admin/schools/:id',
    name: 'SuperAdminSchoolDetails',
    component: () => import('@/views/superAdmin/SuperAdminSchoolDetails.vue'),
    props: true,
    meta: {
      requiresAuth: true,
      requiresPermission: 'super_admin.view_all_schools',
      title: 'School Details'
    }
  },
  {
    path: '/super-admin/import',
    name: 'SuperAdminImport',
    component: () => import('@/views/admin/AdminImport.vue'),
    meta: {
      requiresAuth: true,
      requiresPermission: 'super_admin.manage_schools',
      title: 'Data Import'
    }
  },

  // Admin routes
  {
    path: '/admin',
    redirect: '/admin/dashboard',
    meta: { requiresAuth: true, requiresRole: 'admin' }
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: () => import('@/views/admin/AdminDashboard.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'admin',
      title: 'Admin Dashboard'
    }
  },
  {
    path: '/admin/users',
    name: 'AdminUsers',
    component: () => import('@/views/admin/AdminUsers.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'admin',
      requiresPermission: 'admin.manage_users',
      title: 'Manage Users'
    }
  },
  {
    path: '/admin/classes',
    name: 'AdminClasses',
    component: () => import('@/views/admin/AdminClasses.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'admin',
      requiresPermission: 'admin.manage_classes',
      title: 'Manage Classes'
    }
  },
  {
    path: '/admin/subjects',
    name: 'AdminSubjects',
    component: () => import('@/views/admin/AdminSubjects.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'admin',
      requiresPermission: 'admin.manage_subjects',
      title: 'Manage Subjects'
    }
  },
  {
    path: '/admin/settings',
    name: 'AdminSettings',
    component: () => import('@/views/admin/AdminSettings.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'admin',
      requiresPermission: 'admin.manage_system',
      title: 'System Settings'
    }
  },
  {
    path: '/admin/audit-logs',
    name: 'AdminAuditLogs',
    component: () => import('@/views/admin/AdminAuditLogs.vue'),
    meta: {
      requiresAuth: true,
      requiresRole: 'admin',
      title: 'Audit Logs'
    }
  },
  {
    path: '/admin/students',
    name: 'AdminStudents',
    component: () => import('@/views/admin/AdminStudents.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'admin',
      requiresPermission: 'admin.manage_users',
      title: 'Manage Students'
    }
  },
  {
    path: '/admin/terms',
    name: 'AdminTerms',
    component: () => import('@/views/admin/AdminTerms.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'admin',
      requiresPermission: 'admin.manage_system',
      title: 'Academic Terms'
    }
  },
  {
    path: '/admin/import',
    name: 'AdminImport',
    component: () => import('@/views/admin/AdminImport.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'admin',
      requiresPermission: 'admin.manage_users',
      title: 'Data Import'
    }
  },
  {
    path: '/admin/reports',
    name: 'AdminReports',
    component: () => import('@/views/admin/AdminReports.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'admin',
      requiresPermission: 'admin.view_reports',
      title: 'System Reports'
    }
  },
  {
    path: '/admin/alerts',
    name: 'AdminAlerts',
    component: () => import('@/views/admin/AdminAlerts.vue'),
    meta: {
      requiresAuth: true,
      requiresRole: 'admin',
      title: 'Performance Alerts'
    }
  },
  {
    path: '/admin/audit-logs',
    name: 'AdminAuditLogs',
    component: () => import('@/views/admin/AdminAuditLogs.vue'),
    meta: {
      requiresAuth: true,
      requiresRole: 'admin',
      title: 'Audit Logs'
    }
  },
  {
    path: '/admin/backups',
    name: 'AdminBackups',
    component: () => import('@/views/admin/AdminBackups.vue'),
    meta: {
      requiresAuth: true,
      requiresRole: 'admin',
      title: 'Backups'
    }
  },
  {
    path: '/admin/notes',
    name: 'AdminNotes',
    component: () => import('@/views/admin/AdminNotes.vue'),
    meta: {
      requiresAuth: true,
      requiresRole: 'admin',
      title: 'Student Notes'
    }
  },
  
  // Teacher routes
  {
    path: '/teacher',
    redirect: '/teacher/dashboard',
    meta: { requiresAuth: true, requiresRole: 'teacher' }
  },
  {
    path: '/teacher/dashboard',
    name: 'TeacherDashboard',
    component: () => import('@/views/teacher/TeacherDashboard.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'teacher',
      title: 'Teacher Dashboard'
    }
  },
  {
    path: '/teacher/classes',
    name: 'TeacherClasses',
    component: () => import('@/views/teacher/TeacherClasses.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'teacher',
      requiresPermission: 'teacher.manage_classes',
      title: 'My Classes'
    }
  },
  {
    path: '/teacher/grades',
    name: 'TeacherGrades',
    component: () => import('@/views/teacher/TeacherGrades.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'teacher',
      requiresPermission: 'teacher.manage_grades',
      title: 'Manage Grades'
    }
  },
  {
    path: '/teacher/attendance',
    name: 'TeacherAttendance',
    component: () => import('@/views/teacher/TeacherAttendance.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'teacher',
      requiresPermission: 'teacher.manage_attendance',
      title: 'Manage Attendance'
    }
  },
  {
    path: '/teacher/import',
    name: 'TeacherImport',
    component: () => import('@/views/teacher/TeacherImport.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'teacher',
      title: 'Import Data'
    }
  },
  {
    path: '/teacher/feedback-forms',
    name: 'TeacherFeedbackForms',
    component: () => import('@/views/teacher/FeedbackForms.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'teacher',
      title: 'Student Feedback Surveys'
    }
  },
  {
    path: '/teacher/reports',
    name: 'TeacherReports',
    component: () => import('@/views/ReportView.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'teacher',
      requiresPermission: 'teacher.create_reports',
      title: 'Reports'
    }
  },
   {
   path: '/teacher/analytics',
   name: 'TeacherAnalytics',
   component: () => import('@/views/teacher/TeacherAnalytics.vue'),
   meta: {
   requiresAuth: true,
   requiresRole: 'teacher',
   requiresPermission: 'teacher.view_analytics',
   title: 'Survey Analytics'
   }
   },
  {
    path: '/teacher/alerts',
    name: 'TeacherAlerts',
    component: () => import('@/views/teacher/TeacherAlerts.vue'),
    meta: {
      requiresAuth: true,
      requiresRole: 'teacher',
      title: 'Performance Alerts'
    }
  },

  // Student routes
  {
  path: '/student',
  redirect: '/student/dashboard',
  meta: { requiresAuth: true, requiresRole: 'student' }
  },
  {
  path: '/student/dashboard',
    name: 'StudentDashboard',
    component: () => import('@/views/student/StudentDashboard.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'student',
      title: 'Student Dashboard'
    }
  },
  {
    path: '/student/grades',
    name: 'StudentGrades',
    component: () => import('@/views/student/StudentGrades.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'student',
      requiresPermission: 'student.view_own_grades',
      title: 'My Grades'
    }
  },
  {
    path: '/student/attendance',
    name: 'StudentAttendance',
    component: () => import('@/views/student/StudentAttendance.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'student',
      requiresPermission: 'student.view_own_attendance',
      title: 'My Attendance'
    }
  },
  {
    path: '/student/reports',
    name: 'StudentReports',
    component: () => import('@/views/student/StudentReports.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'student',
      requiresPermission: 'student.view_own_reports',
      title: 'My Reports'
    }
  },
  {
    path: '/student/feedback',
    name: 'StudentFeedback',
    component: () => import('@/views/student/StudentFeedback.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'student',
      requiresPermission: 'student.submit_feedback',
      title: 'Submit Feedback'
    }
  },
  {
    path: '/student/surveys',
    name: 'StudentSurveys',
    component: () => import('@/views/student/FeedbackSurveys.vue'),
    meta: { 
      requiresAuth: true, 
      requiresRole: 'student',
      title: 'Feedback Surveys'
    }
  },

  // Shared routes (accessible by multiple roles)
  {
    path: '/analytics',
    name: 'Analytics',
    component: () => import('@/views/AnalyticsView.vue'),
    meta: { 
      requiresAuth: true, 
      requiresAnyRole: ['admin', 'teacher'],
      requiresPermission: 'teacher.view_analytics',
      title: 'Analytics'
    }
  },
  {
    path: '/students',
    name: 'Students',
    component: () => import('@/views/StudentsView.vue'),
    meta: { 
      requiresAuth: true, 
      requiresAnyRole: ['admin', 'teacher'],
      requiresPermission: 'teacher.view_students',
      title: 'Students'
    }
  },
  {
    path: '/students/:id',
    name: 'StudentDetail',
    component: () => import('@/views/StudentDetailView.vue'),
    meta: { 
      requiresAuth: true, 
      requiresAnyRole: ['admin', 'teacher'],
      requiresPermission: 'teacher.view_students',
      title: 'Student Details'
    },
    props: true
  },

  // Profile routes (accessible by all authenticated users)
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('@/views/ProfileView.vue'),
    meta: { 
      requiresAuth: true,
      requiresPermission: 'view_profile',
      title: 'My Profile'
    }
  },

  // Error routes
  {
    path: '/unauthorized',
    name: 'Unauthorized',
    component: () => import('@/views/errors/UnauthorizedView.vue'),
    meta: { title: 'Access Denied' }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/errors/NotFoundView.vue'),
    meta: { title: 'Page Not Found' }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { top: 0 }
  }
})

// Navigation guard for authentication and authorization
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Wait for auth initialization if needed
  if (authStore.isLoading) {
    await new Promise(resolve => {
      const unwatch = authStore.$subscribe((mutation, state) => {
        if (!state.isLoading) {
          unwatch()
          resolve()
        }
      })
    })
  }

  // Set page title
  document.title = to.meta.title ? `${to.meta.title} - Student Tracker` : 'Student Tracker'
  
  // Check if the route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'Login', query: { redirect: to.fullPath } })
    return
  }
  
  // Check if the route is guest-only
  if (to.meta.guestOnly && authStore.isAuthenticated) {
    const redirectPath = authStore.getRedirectPath(authStore.user?.role)
    next({ path: redirectPath })
    return
  }

  // For authenticated routes, perform authorization checks
  if (authStore.isAuthenticated && to.meta.requiresAuth) {
    const { canAccessRoute } = useAuth()
    
    if (!canAccessRoute(to.meta)) {
      // Redirect to unauthorized page
      next({ 
        name: 'Unauthorized', 
        query: { 
          message: 'You do not have permission to access this page',
          redirect: to.fullPath 
        }
      })
      return
    }
  }
  
  next()
})

export default router