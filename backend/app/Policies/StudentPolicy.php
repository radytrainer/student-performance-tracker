<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Student;

class StudentPolicy
{
    /**
     * Determine whether the user can view any students.
     */
    public function viewAny(User $user): bool
    {
        // Admin and teachers can view students
        return in_array($user->role, ['admin', 'teacher']);
    }

    /**
     * Determine whether the user can view the student.
     */
    public function view(User $user, Student $student): bool
    {
        // Admin can view any student
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can view students in their assigned classes
        if ($user->role === 'teacher') {
            return $user->teacher->classSubjects()
                ->whereHas('class.studentClasses', function ($query) use ($student) {
                    $query->where('student_id', $student->user_id)
                          ->where('status', 'active');
                })->exists();
        }

        // Students can only view their own profile
        if ($user->role === 'student') {
            return $student->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create students.
     */
    public function create(User $user): bool
    {
        // Only admin and teachers can create student records
        return in_array($user->role, ['admin', 'teacher']);
    }

    /**
     * Determine whether the user can update the student.
     */
    public function update(User $user, Student $student): bool
    {
        // Admin can update any student
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can update students in their assigned classes
        if ($user->role === 'teacher') {
            return $user->teacher->classSubjects()
                ->whereHas('class.studentClasses', function ($query) use ($student) {
                    $query->where('student_id', $student->user_id)
                          ->where('status', 'active');
                })->exists();
        }

        // Students can update their own profile (limited fields)
        if ($user->role === 'student') {
            return $student->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the student.
     */
    public function delete(User $user, Student $student): bool
    {
        // Only admin can delete student records
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view student performance data.
     */
    public function viewPerformance(User $user, Student $student): bool
    {
        // Admin can view any student's performance
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can view performance for students in their classes
        if ($user->role === 'teacher') {
            return $user->teacher->classSubjects()
                ->whereHas('class.studentClasses', function ($query) use ($student) {
                    $query->where('student_id', $student->user_id)
                          ->where('status', 'active');
                })->exists();
        }

        // Students can only view their own performance
        if ($user->role === 'student') {
            return $student->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can enroll student in classes.
     */
    public function enrollInClass(User $user, Student $student): bool
    {
        // Admin can enroll any student
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can enroll students in their classes
        if ($user->role === 'teacher') {
            return true; // Will be further restricted by class assignment
        }

        return false;
    }

    /**
     * Determine whether the user can export student data.
     */
    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }
}
