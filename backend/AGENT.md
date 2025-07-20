# Student Performance Tracker - Database Setup Guide

## Project Overview
A comprehensive Laravel-based student performance tracking system with role-based access, analytics dashboard, grade management, attendance tracking, and feedback systems.

## Database Setup Commands

### Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_tracker
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Migration Commands
```bash
# Run all migrations
php artisan migrate

# Run migrations with seeding
php artisan migrate --seed

# Reset and migrate with seeding
php artisan migrate:fresh --seed

# Rollback migrations
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```

### Seeding Commands
```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder
```

## Database Schema Overview

### Core Tables Structure

#### **Users Table** (Authentication & Basic Info)
- Primary roles: `admin`, `teacher`, `student`
- Fields: username, email, password_hash, role, first_name, last_name, profile_picture, is_active, last_login

#### **Teachers Table** (Teacher-specific data)
- Primary key: `user_id` (FK to users.id)
- Fields: teacher_code, department, qualification, specialization, hire_date

#### **Students Table** (Student-specific data)
- Primary key: `user_id` (FK to users.id)
- Fields: student_code, date_of_birth, gender, address, parent_name, parent_phone, enrollment_date, current_class_id

#### **Classes Table** (Class management)
- Fields: class_name, academic_year, class_teacher_id, room_number, schedule

#### **Subjects Table** (Subject definitions)
- Fields: subject_code, subject_name, description, credit_hours, department, is_core

#### **Terms Table** (Academic terms/semesters)
- Fields: term_name, academic_year, start_date, end_date, is_current, status

#### **Grades Table** (Comprehensive grading system)
- Assessment types: `exam`, `quiz`, `assignment`, `project`, `participation`
- Fields: student_id, class_subject_id, term_id, assessment_type, max_score, score_obtained, weightage, grade_letter, remarks, recorded_by, recorded_at

#### **Attendances Table** (Daily attendance tracking)
- Status types: `present`, `absent`, `late`, `excused`
- Fields: student_id, class_id, date, status, recorded_by, notes, recorded_at

#### **Performance Alerts Table** (Automated alerts)
- Alert types: `low_grade`, `attendance`, `behavior`, `improvement`
- Severity levels: `low`, `medium`, `high`
- Fields: student_id, term_id, alert_type, severity, message, related_subject_id, is_resolved, resolved_by, resolved_at, created_by

#### **Student Feedback Table** (Student surveys about teachers)
- Fields: student_id, teacher_id, term_id, question_1 to question_5 (1-10 scale), overall_rating, comments, feedback_date

#### **Teacher Feedback Table** (Teacher feedback to students)
- Fields: teacher_id, student_id, term_id, rating (1-5 scale), feedback_text, improvement_areas, strengths, feedback_date

### Supporting Tables
- **class_subjects**: Links classes with subjects and assigns teachers
- **student_classes**: Student enrollment in classes
- **report_cards**: Generated reports
- **student_notes**: Teacher notes about students
- **data_imports**: CSV/Excel import tracking
- **notifications**: System notifications
- **audit_logs**: System audit trail
- **system_settings**: Application configuration

## Sample Data Created by Seeders

### Users Created:
- **Admin**: admin@school.com / admin123
- **Teachers**: 3 teachers with different departments
- **Students**: 6 students with complete profiles

### Academic Structure:
- **Classes**: 9th Grade A, 9th Grade B, 10th Grade A
- **Subjects**: 14 subjects including Mathematics, English, Science, History, etc.
- **Terms**: Fall 2024, Spring 2025 (current), Summer 2025, Fall 2025

### System Settings:
- School configuration, grading scale, alert thresholds
- Academic calendar settings, notification preferences

## Key Relationships

- Users have one Teacher or Student profile
- Students belong to Classes and can enroll in multiple Classes over time
- Classes have many Subjects taught by Teachers
- Grades link Students to ClassSubjects for specific Terms
- Attendance tracks daily presence for Students in Classes
- PerformanceAlerts monitor student performance across Terms
- Feedback systems allow bidirectional communication between Students and Teachers

## Development Notes

### Code Style:
- Follow Laravel conventions for naming
- Use Eloquent relationships instead of manual joins
- Implement proper validation in form requests
- Use Laravel's built-in authentication

### Security:
- All passwords are hashed using Laravel's Hash facade
- Role-based middleware for route protection
- Input validation on all forms
- CSRF protection enabled

### Performance:
- Database indexes on foreign keys and frequently queried columns
- Eager loading for relationships to prevent N+1 queries
- Pagination for large datasets

## Next Steps for Development

1. **Authentication System**: Implement login/logout with role-based access
2. **Dashboard**: Create role-specific dashboards with KPIs
3. **Grade Management**: CRUD operations for grades with validation
4. **Attendance System**: Daily attendance marking with calendar view
5. **Analytics**: Charts and reports using Chart.js
6. **Data Import**: CSV/Excel upload functionality
7. **Notification System**: Real-time alerts for performance issues
8. **Report Generation**: PDF export functionality
9. **Feedback System**: Student/teacher feedback forms
10. **API Development**: REST API for mobile app integration
