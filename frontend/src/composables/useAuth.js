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
      'teacher.import_data': ['admin', 'teacher'],

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
    
    // If permission is a function, call it
    if (typeof allowedRoles === 'function') {
      return allowedRoles()
    }
    
    // If permission is an array of roles, check if user has one of them
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
    if (isSuperAdmin.value) {
      return '/super-admin/dashboard'
    }
    
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
        { name: 'Dashboard', path: '/admin/dashboard', icon: 'fas fa-home' },
        { name: 'Users', path: '/admin/users', icon: 'fas fa-user-group' },
        { name: 'Students', path: '/admin/students', icon: 'fas fa-graduation-cap' },
        { name: 'Classes', path: '/admin/classes', icon: 'fas fa-door-open' },
        { name: 'Subjects', path: '/admin/subjects', icon: 'fas fa-book-open' },
        { name: 'Terms', path: '/admin/terms', icon: 'fas fa-calendar' },
        { name: 'Import', path: '/admin/import', icon: 'fas fa-upload' },
        { name: 'Settings', path: '/admin/settings', icon: 'fas fa-gear' },
        { name: 'Reports', path: '/admin/reports', icon: 'fas fa-chart-simple' },
        { name: 'Alerts', path: '/admin/alerts', icon: 'fas fa-bell' },
         { name: 'Audit Logs', path: '/admin/audit-logs', icon: 'fas fa-clock-rotate-left' },
         { name: 'Backups', path: '/admin/backups', icon: 'fas fa-hard-drive' },
         { name: 'Notes', path: '/admin/notes', icon: 'fas fa-note-sticky' },
         { name: 'Analytics', path: '/analytics', icon: 'fas fa-chart-line' }
      ],
      teacher: [
        { name: 'Dashboard', path: '/teacher/dashboard', icon: 'fas fa-home' },
        { name: 'My Classes', path: '/teacher/classes', icon: 'fas fa-door-open' },
        { name: 'Students', path: '/students', icon: 'fas fa-graduation-cap' },
        { name: 'Grades', path: '/teacher/grades', icon: 'fas fa-list-check' },
        { name: 'Attendance', path: '/teacher/attendance', icon: 'fas fa-calendar' },
        { name: 'Import', path: '/teacher/import', icon: 'fas fa-upload' },
        { name: 'Surveys', path: '/teacher/feedback-forms', icon: 'fas fa-square-poll-vertical' },
        { name: 'Reports', path: '/teacher/reports', icon: 'fas fa-file-lines' },
        { name: 'Alerts', path: '/teacher/alerts', icon: 'fas fa-bell' },
        { name: 'Notes', path: '/teacher/notes', icon: 'fas fa-note-sticky' },
        { name: 'Analytics', path: '/analytics', icon: 'fas fa-chart-line' }
      ],
      student: [
        { name: 'Dashboard', path: '/student/dashboard', icon: 'fas fa-home' },
        { name: 'My Grades', path: '/student/grades', icon: 'fas fa-list-check' },
        { name: 'Attendance', path: '/student/attendance', icon: 'fas fa-calendar' },
        { name: 'Reports', path: '/student/reports', icon: 'fas fa-file-lines' },
        { name: 'Surveys', path: '/student/surveys', icon: 'fas fa-square-poll-vertical' },
        { name: 'Feedback', path: '/student/feedback', icon: 'fas fa-message' }
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
    isSuperAdmin,
    isSchoolAdmin,
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
