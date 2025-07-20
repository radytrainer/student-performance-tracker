# Role-Based Data Filtering System

## Overview
This system ensures that users only see data they are authorized to view based on their role. It implements automatic data filtering at the query level to prevent unauthorized data access.

## Key Components

### 1. Query Scopes in Models
Each model has `scopeAccessibleToUser()` methods that automatically filter data based on the current user's role.

#### User Model Scopes
```php
// Only admin can see all users, others see only themselves
public function scopeAccessibleUsers($query, User $currentUser)
```

#### Student Model Scopes
```php
// Admin: All students
// Teacher: Only students in assigned classes
// Student: Only own profile
public function scopeAccessibleToUser($query, User $user)
```

#### Grade Model Scopes
```php
// Admin: All grades
// Teacher: Only grades for assigned subjects
// Student: Only own grades
public function scopeAccessibleToUser($query, User $user)
```

#### Attendance Model Scopes
```php
// Admin: All attendance records
// Teacher: Only attendance for assigned classes
// Student: Only own attendance
public function scopeAccessibleToUser($query, User $user)
```

### 2. Base Controller with Automatic Filtering
The `BaseController` provides helper methods for consistent data filtering:

```php
protected function applyRoleBasedFilters($query, $user = null)
{
    $user = $user ?: $this->getCurrentUser();
    
    if (method_exists($query->getModel(), 'scopeAccessibleToUser')) {
        return $query->accessibleToUser($user);
    }
    
    return $query;
}
```

### 3. Role-Specific Controllers
Controllers automatically apply role-based filtering to all queries:

#### StudentController
- **Admin**: Can view/edit all students
- **Teacher**: Can only view/edit students in assigned classes
- **Student**: Can only view/edit own profile (limited fields)

#### GradeController
- **Admin**: Full access to all grades
- **Teacher**: Can manage grades for assigned subjects only
- **Student**: Can only view own grades

#### DashboardController
- **Admin**: System-wide statistics and analytics
- **Teacher**: Class-specific data and assigned students
- **Student**: Personal academic performance only

## Data Access Matrix

| Resource | Admin Access | Teacher Access | Student Access |
|----------|--------------|----------------|----------------|
| **Users** | All users | Own profile only | Own profile only |
| **Students** | All students | Assigned students only | Own profile only |
| **Grades** | All grades | Assigned subjects only | Own grades only |
| **Attendance** | All records | Assigned classes only | Own attendance only |
| **Classes** | All classes | Assigned classes only | Enrolled classes only |
| **Analytics** | System-wide | Class-specific | Personal only |

## Implementation Examples

### Automatic Query Filtering
```php
// In any controller extending BaseController
public function index(Request $request)
{
    $user = $this->getCurrentUser();
    $query = Student::with(['user', 'currentClass']);
    
    // This automatically applies role-based filtering
    $query = $this->applyRoleBasedFilters($query, $user);
    
    $students = $query->paginate(15);
    return $this->paginatedResponse($students);
}
```

### Role-Specific Dashboard Data
```php
// Admin sees system overview
$data = [
    'total_students' => Student::count(), // All students
    'total_teachers' => User::where('role', 'teacher')->count(),
    'system_performance' => Grade::avg('score_obtained'), // All grades
];

// Teacher sees class-specific data
$data = [
    'my_students' => Student::accessibleToUser($user)->count(), // Only assigned
    'my_classes' => $user->teacher->classSubjects()->count(),
    'class_performance' => Grade::accessibleToUser($user)->avg('score_obtained'),
];

// Student sees personal data only
$data = [
    'my_gpa' => Grade::forStudent($user->student->user_id)->avg('score_obtained'),
    'my_attendance' => Attendance::forStudent($user->student->user_id)->count(),
];
```

### Grade Management with Role Restrictions
```php
// Teachers can only create grades for their assigned subjects
if ($user->isTeacher()) {
    $classSubject = ClassSubject::findOrFail($request->class_subject_id);
    if ($classSubject->teacher_id !== $user->teacher->user_id) {
        return $this->errorResponse('Access denied', 403);
    }
}
```

## Security Features

### 1. Automatic Filtering
- All queries automatically apply role-based filters
- No manual permission checking required in most cases
- Prevents accidental data leaks

### 2. Query-Level Protection
- Data filtering happens at the database level
- Even if a user guesses an ID, they can't access unauthorized data
- Performance optimized with proper database queries

### 3. Role-Specific Validation
```php
// Different validation rules based on user role
if ($user->isAdmin() || $user->isTeacher()) {
    $rules = [
        'student_code' => 'required|string',
        'parent_name' => 'sometimes|string',
        // Full access to all fields
    ];
} elseif ($user->isStudent()) {
    $rules = [
        'address' => 'sometimes|string',
        'parent_phone' => 'sometimes|string',
        // Limited fields only
    ];
}
```

### 4. Data Response Filtering
```php
// Students get simplified grade data
'recent_grades' => Grade::forStudent($student->user_id)
    ->latest('recorded_at')
    ->limit(10)
    ->get()
    ->map(function ($grade) {
        return [
            'subject' => $grade->classSubject->subject->subject_name,
            'score' => $grade->score_obtained,
            'max_score' => $grade->max_score,
            'percentage' => round(($grade->score_obtained / $grade->max_score) * 100, 2),
            // Sensitive data like 'recorded_by' is excluded
        ];
    }),
```

## API Response Examples

### Admin Dashboard Response
```json
{
    "success": true,
    "data": {
        "overview": {
            "total_students": 150,
            "total_teachers": 25,
            "total_classes": 12,
            "current_term": "Spring 2025"
        },
        "recent_activity": {
            "new_students_this_month": 5,
            "grades_entered_today": 45,
            "attendance_marked_today": 120
        }
    }
}
```

### Teacher Dashboard Response
```json
{
    "success": true,
    "data": {
        "overview": {
            "assigned_classes": 3,
            "total_students": 75,
            "subjects_teaching": 2
        },
        "my_classes": [
            {
                "id": 1,
                "name": "9th Grade A",
                "students_count": 25,
                "subjects_teaching": ["Mathematics", "Physics"]
            }
        ]
    }
}
```

### Student Dashboard Response
```json
{
    "success": true,
    "data": {
        "overview": {
            "current_class": "9th Grade A",
            "student_code": "STU001",
            "current_term": "Spring 2025"
        },
        "academic_performance": {
            "current_gpa": 3.8,
            "attendance_percentage": 92.5
        },
        "recent_grades": [
            {
                "subject": "Mathematics",
                "assessment_type": "exam",
                "score": 85,
                "max_score": 100,
                "percentage": 85,
                "date": "2025-01-15"
            }
        ]
    }
}
```

## Testing Role-Based Access

### Test Scenarios
1. **Admin Access**: Should see all data across the system
2. **Teacher Access**: Should only see data for assigned classes/students
3. **Student Access**: Should only see own personal data
4. **Cross-Role Testing**: Ensure users cannot access data outside their scope

### Test Commands
```php
// Test student access
$student = User::where('role', 'student')->first();
$grades = Grade::accessibleToUser($student)->get();
// Should only return the student's own grades

// Test teacher access
$teacher = User::where('role', 'teacher')->first();
$students = Student::accessibleToUser($teacher)->get();
// Should only return students in teacher's assigned classes

// Test admin access
$admin = User::where('role', 'admin')->first();
$allData = Student::accessibleToUser($admin)->get();
// Should return all students
```

## Benefits

1. **Security**: Automatic data protection at query level
2. **Performance**: Efficient database queries with proper filtering
3. **Maintainability**: Consistent filtering logic across all controllers
4. **Scalability**: Easy to extend for new roles or permissions
5. **Compliance**: Ensures data privacy and access control requirements

## Usage Guidelines

1. **Always use scopes**: Never query models directly without applying role-based scopes
2. **Extend BaseController**: All controllers should extend BaseController for consistent filtering
3. **Test thoroughly**: Verify that each role only sees appropriate data
4. **Document changes**: Update this document when adding new filtering rules
5. **Performance monitoring**: Monitor query performance with complex role-based filters
