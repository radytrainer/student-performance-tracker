<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Grade;

class GradePolicy
{
    /**
     * Determine whether the user can view any grades.
     */
    public function viewAny(User $user): bool
    {
        // Admin and teachers can view grades, students can only view their own
        return in_array($user->role, ['admin', 'teacher']);
    }

    /**
     * Determine whether the user can view the grade.
     */
    public function view(User $user, Grade $grade): bool
    {
        // Admin can view any grade
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can view grades for their assigned subjects/classes
        if ($user->role === 'teacher') {
            // Check if teacher is assigned to the class subject
            return $grade->classSubject->teacher_id === $user->teacher->user_id;
        }

        // Students can only view their own grades
        if ($user->role === 'student') {
            return $grade->student_id === $user->student->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create grades.
     */
    public function create(User $user): bool
    {
        // Only admin and teachers can create grades
        return in_array($user->role, ['admin', 'teacher']);
    }

    /**
     * Determine whether the user can update the grade.
     */
    public function update(User $user, Grade $grade): bool
    {
        // Admin can update any grade
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can only update grades for their assigned subjects/classes
        if ($user->role === 'teacher') {
            return $grade->classSubject->teacher_id === $user->teacher->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the grade.
     */
    public function delete(User $user, Grade $grade): bool
    {
        // Admin can delete any grade
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can only delete grades for their assigned subjects/classes
        if ($user->role === 'teacher') {
            return $grade->classSubject->teacher_id === $user->teacher->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can view grades for a specific student.
     */
    public function viewStudentGrades(User $user, User $student): bool
    {
        // Admin can view any student's grades
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can view grades for students in their classes
        if ($user->role === 'teacher') {
            // Check if teacher has any classes with this student
            return $user->teacher->classSubjects()
                ->whereHas('class.studentClasses', function ($query) use ($student) {
                    $query->where('student_id', $student->student->user_id)
                          ->where('status', 'active');
                })->exists();
        }

        // Students can only view their own grades
        if ($user->role === 'student') {
            return $user->id === $student->id;
        }

        return false;
    }

    /**
     * Determine whether the user can export grades.
     */
    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }
}
