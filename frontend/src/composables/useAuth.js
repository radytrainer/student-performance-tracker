import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function useAuth() {
  const authStore = useAuthStore()

  // Current user and authentication status
  const user = computed(() => authStore.user)
  const isAuthenticated = computed(() => authStore.isAuthenticated)
  const userRole = computed(() => authStore.user?.role)

  // Role checking functions
  const isAdmin = computed(() => userRole.value === 'admin')
  const isSuperAdmin = computed(() => userRole.value === 'admin' && authStore.user?.is_super_admin)
  const isSchoolAdmin = computed(() => userRole.value === 'admin' && !authStore.user?.is_super_admin)
  const isTeacher = computed(() => userRole.value === 'teacher')
  const isStudent = computed(() => userRole.value === 'student')

  // Permission checking functions
  const hasRole = (role) => {
    if (!userRole.value) return false
    return userRole.value === role
  }

  const hasAnyRole = (roles) => {
    if (!userRole.value) return false
    return roles.includes(userRole.value)
  }

  const hasPermission = (permission) => {
    const permissions = {
      // Super Admin permissions
      'super_admin.manage_schools': () => isSuperAdmin.value,
      'super_admin.manage_sub_admins': () => isSuperAdmin.value,
      'super_admin.view_all_schools': () => isSuperAdmin.value,

      // Admin permissions
      'admin.manage_users': ['admin'],
      'admin.manage_system': ['admin'],
      'admin.view_all_data': ['admin'],
      'admin.manage_classes': ['admin'],
      'admin.manage_subjects': ['admin'],
      'admin.view_reports': ['admin'],

      // Teacher permissions
      'teacher.manage_classes': ['admin', 'teacher'],
      'teacher.manage_grades': ['admin', 'teacher'],
      'teacher.view_students': ['admin', 'teacher'],
      'teacher.manage_attendance': ['admin', 'teacher'],
      'teacher.create_reports': ['admin', 'teacher'],
      'teacher.view_analytics': ['admin', 'teacher'],

      // Student permissions
      'student.view_own_grades': ['admin', 'teacher', 'student'],
      'student.view_own_attendance': ['admin', 'teacher', 'student'],
      'student.view_own_reports': ['admin', 'teacher', 'student'],
      'student.submit_feedback': ['admin', 'student'],

      // Shared permissions
      'view_dashboard': ['admin', 'teacher', 'student'],
      'view_profile': ['admin', 'teacher', 'student'],
      'edit_own_profile': ['admin', 'teacher', 'student']
    }

    const allowedRoles = permissions[permission]
    if (!allowedRoles) return false
    
    return allowedRoles.includes(userRole.value)
  }

  // Route access checking
  const canAccessRoute = (routeMeta) => {
    // If no auth required, allow access
    if (!routeMeta.requiresAuth) return true
    
    // If auth required but user not authenticated, deny access
    if (!isAuthenticated.value) return false
    
    // If specific role required, check it
    if (routeMeta.requiresRole) {
      return hasRole(routeMeta.requiresRole)
    }

    // If any of specific roles required, check them
    if (routeMeta.requiresAnyRole) {
      return hasAnyRole(routeMeta.requiresAnyRole)
    }

    // If permission required, check it
    if (routeMeta.requiresPermission) {
      return hasPermission(routeMeta.requiresPermission)
    }

    // If authenticated and no specific requirements, allow access
    return true
  }

  // Navigation helpers
  const getDefaultRoute = () => {
    switch (userRole.value) {
      case 'admin':
        return '/admin/dashboard'
      case 'teacher':
        return '/teacher/dashboard'
      case 'student':
        return '/student/dashboard'
      default:
        return '/login'
    }
  }

  const getAvailableRoutes = () => {
    const routes = {
      admin: [
        { name: 'Admin Dashboard', path: '/admin/dashboard', icon: 'fas fa-tachometer-alt' },
        { name: 'Manage Users', path: '/admin/users', icon: 'fas fa-users' },
        { name: 'Manage Students', path: '/admin/students', icon: 'fas fa-user-graduate' },
        { name: 'Manage Classes', path: '/admin/classes', icon: 'fas fa-school' },
        { name: 'Manage Subjects', path: '/admin/subjects', icon: 'fas fa-book' },
        { name: 'Academic Terms', path: '/admin/terms', icon: 'fas fa-calendar-alt' },
        { name: 'Data Import', path: '/admin/import', icon: 'fas fa-file-import' },
        { name: 'System Settings', path: '/admin/settings', icon: 'fas fa-cog' },
        { name: 'Reports', path: '/admin/reports', icon: 'fas fa-chart-bar' },
        { name: 'Analytics', path: '/analytics', icon: 'fas fa-chart-line' }
      ],
      teacher: [
        { name: 'Teacher Dashboard', path: '/teacher/dashboard', icon: 'fas fa-chalkboard-teacher' },
        { name: 'My Classes', path: '/teacher/classes', icon: 'fas fa-school' },
        { name: 'Students', path: '/students', icon: 'fas fa-user-graduate' },
        { name: 'Grades', path: '/teacher/grades', icon: 'fas fa-clipboard-list' },
        { name: 'Attendance', path: '/teacher/attendance', icon: 'fas fa-calendar-check' },
        { name: 'Feedback Surveys', path: '/teacher/feedback-forms', icon: 'fas fa-poll' },
        { name: 'Reports', path: '/teacher/reports', icon: 'fas fa-file-alt' },
        { name: 'Analytics', path: '/analytics', icon: 'fas fa-chart-line' }
      ],
      student: [
        { name: 'Student Dashboard', path: '/student/dashboard', icon: 'fas fa-user-graduate' },
        { name: 'My Grades', path: '/student/grades', icon: 'fas fa-clipboard-list' },
        { name: 'My Attendance', path: '/student/attendance', icon: 'fas fa-calendar-check' },
        { name: 'My Reports', path: '/student/reports', icon: 'fas fa-file-alt' },
        { name: 'Survey Feedback', path: '/student/surveys', icon: 'fas fa-poll' },
        { name: 'Feedback', path: '/student/feedback', icon: 'fas fa-comment' }
      ]
    }

    return routes[userRole.value] || []
  }

  // Component visibility helpers
  const showForRoles = (roles) => {
    if (!userRole.value) return false
    return roles.includes(userRole.value)
  }

  const hideForRoles = (roles) => {
    if (!userRole.value) return true
    return !roles.includes(userRole.value)
  }

  const showForPermission = (permission) => {
    return hasPermission(permission)
  }

  return {
    // User info
    user,
    isAuthenticated,
    userRole,

    // Role checks
    isAdmin,
    isTeacher,
    isStudent,
    hasRole,
    hasAnyRole,
    hasPermission,

    // Route access
    canAccessRoute,
    getDefaultRoute,
    getAvailableRoutes,

    // Component visibility
    showForRoles,
    hideForRoles,
    showForPermission
  }
}
