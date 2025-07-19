# Role-Based Access Control System

## Overview
This document outlines the comprehensive role-based access control (RBAC) system implemented for the Student Performance Tracker backend.

## System Components

### 1. Middleware
- **RoleMiddleware**: Checks user roles (admin, teacher, student)
- **PermissionMiddleware**: Checks specific permissions based on role

### 2. Authorization Policies
- **UserPolicy**: Controls user management access
- **StudentPolicy**: Controls student data access
- **TeacherPolicy**: Controls teacher data access
- **GradePolicy**: Controls grade management access
- **AttendancePolicy**: Controls attendance data access
- **ClassPolicy**: Controls class management access

### 3. Authorization Service
- **AuthorizationService**: Centralized service for complex authorization logic
- Provides methods for role checking, permission validation, and resource access control

## Role Matrix

| Feature/Resource | Admin 👑 | Teacher 👨‍🏫 | Student 👩‍🎓 |
|------------------|----------|-------------|-------------|
| **User Management** |
| View all users | ✅ | ❌ | ❌ |
| Create users | ✅ | ❌ | ❌ |
| Update any user | ✅ | ❌ | ❌ |
| Delete users | ✅ | ❌ | ❌ |
| Reset passwords | ✅ | ❌ | ❌ |
| View own profile | ✅ | ✅ | ✅ |
| Update own profile | ✅ | ✅ | ✅ |
| **Student Management** |
| View all students | ✅ | ✅ (assigned) | ❌ |
| Create students | ✅ | ✅ | ❌ |
| Update students | ✅ | ✅ (assigned) | ✅ (own) |
| Delete students | ✅ | ❌ | ❌ |
| View student performance | ✅ | ✅ (assigned) | ✅ (own) |
| **Grade Management** |
| View all grades | ✅ | ✅ (assigned classes) | ❌ |
| View own grades | ✅ | ✅ | ✅ |
| Create grades | ✅ | ✅ (assigned subjects) | ❌ |
| Update grades | ✅ | ✅ (assigned subjects) | ❌ |
| Delete grades | ✅ | ✅ (assigned subjects) | ❌ |
| Bulk operations | ✅ | ✅ (assigned subjects) | ❌ |
| Export grades | ✅ | ✅ | ❌ |
| **Attendance Management** |
| View all attendance | ✅ | ✅ (assigned classes) | ❌ |
| View own attendance | ✅ | ✅ | ✅ |
| Mark attendance | ✅ | ✅ (assigned classes) | ❌ |
| Update attendance | ✅ | ✅ (assigned classes) | ❌ |
| Delete attendance | ✅ | ✅ (assigned classes) | ❌ |
| Export attendance | ✅ | ✅ | ❌ |
| **Class Management** |
| View all classes | ✅ | ✅ (assigned) | ✅ (enrolled) |
| Create classes | ✅ | ❌ | ❌ |
| Update classes | ✅ | ✅ (if class teacher) | ❌ |
| Delete classes | ✅ | ❌ | ❌ |
| Manage enrollment | ✅ | ✅ (if class teacher) | ❌ |
| Assign teachers | ✅ | ❌ | ❌ |
| **Analytics & Reports** |
| View system analytics | ✅ | ❌ | ❌ |
| View class analytics | ✅ | ✅ (assigned classes) | ❌ |
| View own performance | ✅ | ✅ | ✅ |
| Create reports | ✅ | ✅ | ❌ |
| View all reports | ✅ | ✅ (assigned students) | ❌ |
| View own reports | ✅ | ✅ | ✅ |
| **System Management** |
| System settings | ✅ | ❌ | ❌ |
| Audit logs | ✅ | ❌ | ❌ |
| Data imports | ✅ | ✅ (limited) | ❌ |
| **Feedback System** |
| View all feedback | ✅ | ❌ | ❌ |
| View received feedback | ✅ | ✅ | ❌ |
| Respond to feedback | ✅ | ✅ | ❌ |
| Submit feedback | ❌ | ❌ | ✅ |
| **Notifications** |
| View all notifications | ✅ | ❌ | ❌ |
| Create notifications | ✅ | ✅ | ❌ |
| View own notifications | ✅ | ✅ | ✅ |

## Permission System

### Admin Permissions
```
users.view, users.create, users.update, users.delete
students.view, students.create, students.update, students.delete
teachers.view, teachers.create, teachers.update, teachers.delete
classes.view, classes.create, classes.update, classes.delete
subjects.view, subjects.create, subjects.update, subjects.delete
grades.view, grades.create, grades.update, grades.delete
attendance.view, attendance.create, attendance.update, attendance.delete
reports.view, reports.create, reports.update, reports.delete
analytics.view, system.settings, audit.logs
notifications.view, notifications.create
feedback.view, feedback.manage
imports.view, imports.create
```

### Teacher Permissions
```
students.view, students.update (assigned students only)
classes.view, classes.update (assigned classes only)
subjects.view (assigned subjects only)
grades.view, grades.create, grades.update, grades.delete (assigned subjects only)
attendance.view, attendance.create, attendance.update (assigned classes only)
reports.view, reports.create (assigned students only)
analytics.view (assigned classes only)
notifications.view, notifications.create
feedback.view, feedback.respond
imports.view, imports.create (limited)
profile.view, profile.update
```

### Student Permissions
```
grades.view.own (own grades only)
attendance.view.own (own attendance only)
reports.view.own (own reports only)
notifications.view.own (own notifications only)
feedback.create (submit feedback)
profile.view, profile.update (own profile only)
```

## API Route Protection

### Route Groups
1. **Public Routes**: Login, Register
2. **Authenticated Routes**: Profile management
3. **Admin Only**: User management, system settings, audit logs
4. **Admin & Teacher**: Student management, grade management, analytics
5. **Teacher Only**: Teacher-specific dashboards and tools
6. **Student Only**: Student-specific views and feedback submission

### Middleware Usage
```php
// Role-based protection
Route::middleware(['role:admin'])->group(function () {
    // Admin only routes
});

Route::middleware(['role:admin,teacher'])->group(function () {
    // Admin and teacher routes
});

// Permission-based protection
Route::middleware(['permission:grades.create'])->group(function () {
    // Routes requiring grade creation permission
});
```

## Authorization Policies

### Policy Methods
- `viewAny()`: Check if user can view any resources
- `view()`: Check if user can view specific resource
- `create()`: Check if user can create resources
- `update()`: Check if user can update specific resource
- `delete()`: Check if user can delete specific resource

### Custom Policy Methods
- `viewStudentGrades()`: Check if user can view specific student's grades
- `markAttendance()`: Check if user can mark attendance for specific class
- `manageEnrollment()`: Check if user can manage class enrollment

## Usage Examples

### In Controllers
```php
// Check if user can view grades
$this->authorize('viewAny', Grade::class);

// Check if user can update specific grade
$this->authorize('update', $grade);

// Check if teacher can access student
if (!AuthorizationService::teacherCanAccessStudent(auth()->user(), $student)) {
    abort(403);
}
```

### In Blade Templates
```php
@can('create', App\Models\User::class)
    <button>Create User</button>
@endcan

@cannot('delete', $student)
    <span>Cannot delete this student</span>
@endcannot
```

### In API Responses
```php
$user = auth()->user();
$permissions = AuthorizationService::getUserPermissions($user);
$accessRules = AuthorizationService::getDashboardAccessRules($user);

return response()->json([
    'user' => $user,
    'permissions' => $permissions,
    'access_rules' => $accessRules
]);
```

## Security Features

### Rate Limiting
- Role-based rate limits for different actions
- Prevents abuse of bulk operations
- Configurable limits per role

### Data Filtering
- Teachers only see students in their assigned classes
- Students only see their own data
- Admins have full access with proper logging

### Audit Trail
- All actions are logged with user context
- Role changes are tracked
- Sensitive operations require additional verification

## Testing Role-Based Access

### Test Endpoint
```
GET /api/permissions
```
Returns current user's role and permissions for testing.

### Test Cases
1. **Admin Access**: Should have full system access
2. **Teacher Access**: Should only access assigned classes/students
3. **Student Access**: Should only access own data
4. **Cross-Role Access**: Should be denied appropriately
5. **Policy Enforcement**: Resource-specific access control

## Future Enhancements

1. **Fine-Grained Permissions**: More specific permission granularity
2. **Temporary Access**: Time-limited permissions for specific tasks
3. **Delegation**: Teachers delegating permissions to assistants
4. **Dynamic Roles**: Role changes based on context (e.g., substitute teacher)
5. **Permission Caching**: Improved performance for permission checks
