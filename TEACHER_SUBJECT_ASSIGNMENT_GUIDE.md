# Teacher-Subject Assignment System

This guide documents the complete teacher-subject assignment system implemented for the Student Performance Tracker application.

## Overview

The teacher-subject assignment system allows administrators to:
- Assign multiple subjects to teachers
- Mark subjects as primary or secondary
- Track assignment dates
- Manage bulk assignments
- Filter teachers by subjects
- View comprehensive teacher-subject relationships

## Database Schema

### Teacher Subjects Table (`teacher_subjects`)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `teacher_id` | bigint | Foreign key to users.id (teachers only) |
| `subject_id` | bigint | Foreign key to subjects.id |
| `primary_subject` | boolean | Whether this is a primary subject for the teacher |
| `assigned_at` | timestamp | When the assignment was made |
| `created_at` | timestamp | Record creation time |
| `updated_at` | timestamp | Record update time |

**Constraints:**
- Unique constraint on (`teacher_id`, `subject_id`) prevents duplicates
- Foreign key constraints ensure referential integrity
- Index on (`teacher_id`, `primary_subject`) for query performance

## Model Relationships

### User Model (Teacher Relationships)

```php
// Many-to-many relationship with subjects
public function subjects(): BelongsToMany
{
    return $this->belongsToMany(Subject::class, 'teacher_subjects', 'teacher_id', 'subject_id')
        ->withPivot('primary_subject', 'assigned_at')
        ->withTimestamps();
}

// Only primary subjects
public function primarySubjects(): BelongsToMany
{
    return $this->subjects()->wherePivot('primary_subject', true);
}
```

### Subject Model (Teacher Relationships)

```php
// Many-to-many relationship with teachers
public function teachers(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'teacher_subjects', 'subject_id', 'teacher_id')
        ->where('users.role', 'teacher')
        ->withPivot('primary_subject', 'assigned_at')
        ->withTimestamps();
}

// Only primary teachers for this subject
public function primaryTeachers(): BelongsToMany
{
    return $this->teachers()->wherePivot('primary_subject', true);
}
```

## API Endpoints

### 1. Assign Subjects to Teacher
**POST** `/api/admin/teachers/{teacher}/subjects`

Assigns one or more subjects to a specific teacher.

**Request Body:**
```json
{
    "subject_ids": [1, 2, 3],
    "primary_subject_id": 1
}
```

**Response:**
```json
{
    "success": true,
    "message": "Subjects assigned successfully",
    "data": {
        "teacher": {...},
        "assigned_count": 3
    }
}
```

### 2. Remove Subject Assignments
**DELETE** `/api/admin/teachers/{teacher}/subjects`

Removes subject assignments from a teacher.

**Request Body:**
```json
{
    "subject_ids": [2, 3]
}
```

### 3. Get Teacher's Subjects
**GET** `/api/admin/teachers/{teacher}/subjects`

Retrieves all subjects assigned to a specific teacher.

**Response:**
```json
{
    "success": true,
    "data": {
        "teacher": {...},
        "subjects": [
            {
                "id": 1,
                "subject_name": "Mathematics",
                "primary_subject": true,
                "assigned_at": "2024-08-28T10:00:00Z"
            }
        ],
        "total_subjects": 3,
        "primary_subjects_count": 1
    }
}
```

### 4. Get Subject's Teachers
**GET** `/api/admin/subjects/{subject}/teachers`

Retrieves all teachers assigned to a specific subject.

### 5. Bulk Assignment
**POST** `/api/admin/teachers/bulk-assign-subjects`

Assigns subjects to multiple teachers in a single operation.

**Request Body:**
```json
{
    "assignments": [
        {
            "teacher_id": 1,
            "subject_ids": [1, 2],
            "primary_subject_id": 1
        },
        {
            "teacher_id": 2,
            "subject_ids": [3, 4],
            "primary_subject_id": 3
        }
    ]
}
```

## Teacher Filtering

The user listing endpoint now supports filtering teachers by subjects:

**GET** `/api/users?role=teacher&subject_id=1`
- Returns teachers assigned to subject ID 1

**GET** `/api/users?role=teacher&primary_subjects_only=true`
- Returns only teachers' primary subject assignments

## Validation Rules

### Assignment Validation
- `subject_ids`: Required array of existing subject IDs
- `primary_subject_id`: Optional, must be one of the selected subjects
- Teacher must have role 'teacher'
- School isolation enforced for non-super admins

### Security Features
- Role-based access control (admin only)
- School isolation for multi-school deployments
- Prevents duplicate assignments
- Validates teacher and subject existence

## Sample Data

The `TeacherSubjectSeeder` creates realistic subject assignments based on:
- Teacher expertise areas (Mathematics, Science, English, etc.)
- Random assignments if no expertise match
- Primary subject designation for each teacher
- Historical assignment dates

## Usage Examples

### Frontend Integration

```javascript
// Get teachers for a specific subject
const getSubjectTeachers = async (subjectId) => {
    const response = await axios.get(`/api/admin/subjects/${subjectId}/teachers`);
    return response.data.data.teachers;
};

// Assign subjects to teacher
const assignSubjects = async (teacherId, subjectIds, primarySubjectId) => {
    const response = await axios.post(`/api/admin/teachers/${teacherId}/subjects`, {
        subject_ids: subjectIds,
        primary_subject_id: primarySubjectId
    });
    return response.data;
};

// Bulk assignment
const bulkAssignSubjects = async (assignments) => {
    const response = await axios.post('/api/admin/teachers/bulk-assign-subjects', {
        assignments
    });
    return response.data;
};
```

### Laravel/PHP Usage

```php
// Get teacher with subjects
$teacher = User::with(['subjects' => function($query) {
    $query->withPivot('primary_subject', 'assigned_at');
}])->find($teacherId);

// Get teachers for a subject
$subject = Subject::with(['teachers' => function($query) {
    $query->withPivot('primary_subject', 'assigned_at');
}])->find($subjectId);

// Filter teachers by subject
$teachers = User::where('role', 'teacher')
    ->whereHas('subjects', function($q) use ($subjectId) {
        $q->where('subject_id', $subjectId);
    })->get();
```

## Testing

Run the test script to verify functionality:

```bash
# Test database relationships
php backend/test_teacher_subjects.php

# Test API endpoints (requires server running)
chmod +x test_teacher_subjects_api.sh
./test_teacher_subjects_api.sh
```

## Migration and Setup

```bash
# Run migration
php artisan migrate

# Seed sample data
php artisan db:seed --class=TeacherSubjectSeeder

# Or seed all data
php artisan migrate:fresh --seed
```

## Performance Considerations

- Indexes on foreign keys and pivot columns for fast queries
- Eager loading prevents N+1 query problems
- Bulk operations for efficiency
- Proper pagination support in user listings

## Future Enhancements

Potential improvements:
- Subject specialization levels (beginner, intermediate, advanced)
- Teaching load calculations based on subject assignments
- Subject preference scoring
- Historical assignment tracking
- Integration with class scheduling system
- Automated assignment suggestions based on teacher qualifications
