<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any attendance records.
     */
    public function viewAny(User $user): bool
    {
        // Admin and teachers can view attendance records
        return in_array($user->role, ['admin', 'teacher']);
    }

    /**
     * Determine whether the user can view the attendance record.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // Admin can view any attendance record
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can view attendance for their assigned classes
        if ($user->role === 'teacher') {
            // Check if teacher is assigned to this class
            return $user->teacher->classSubjects()
                ->where('class_id', $attendance->class_id)
                ->exists();
        }

        // Students can only view their own attendance
        if ($user->role === 'student') {
            return $attendance->student_id === $user->student->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create attendance records.
     */
    public function create(User $user): bool
    {
        // Only admin and teachers can create attendance records
        return in_array($user->role, ['admin', 'teacher']);
    }

    /**
     * Determine whether the user can update the attendance record.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // Admin can update any attendance record
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can only update attendance for their assigned classes
        if ($user->role === 'teacher') {
            return $user->teacher->classSubjects()
                ->where('class_id', $attendance->class_id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the attendance record.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        // Admin can delete any attendance record
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can only delete attendance for their assigned classes
        if ($user->role === 'teacher') {
            return $user->teacher->classSubjects()
                ->where('class_id', $attendance->class_id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can view attendance for a specific student.
     */
    public function viewStudentAttendance(User $user, User $student): bool
    {
        // Admin can view any student's attendance
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can view attendance for students in their classes
        if ($user->role === 'teacher') {
            return $user->teacher->classSubjects()
                ->whereHas('class.studentClasses', function ($query) use ($student) {
                    $query->where('student_id', $student->student->user_id)
                          ->where('status', 'active');
                })->exists();
        }

        // Students can only view their own attendance
        if ($user->role === 'student') {
            return $user->id === $student->id;
        }

        return false;
    }

    /**
     * Determine whether the user can mark attendance for a class.
     */
    public function markAttendance(User $user, $classId): bool
    {
        // Admin can mark attendance for any class
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can only mark attendance for their assigned classes
        if ($user->role === 'teacher') {
            return $user->teacher->classSubjects()
                ->where('class_id', $classId)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can export attendance reports.
     */
    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }
}
