<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Classes;

class ClassPolicy
{
    /**
     * Determine whether the user can view any classes.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view classes (with different scopes)
        return auth()->check();
    }

    /**
     * Determine whether the user can view the class.
     */
    public function view(User $user, Classes $class): bool
    {
        // Admin can view any class
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can view classes they are assigned to
        if ($user->role === 'teacher') {
            return $user->teacher->classSubjects()
                ->where('class_id', $class->id)
                ->exists();
        }

        // Students can view classes they are enrolled in
        if ($user->role === 'student') {
            return $user->student->studentClasses()
                ->where('class_id', $class->id)
                ->where('status', 'active')
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create classes.
     */
    public function create(User $user): bool
    {
        // Only admin can create classes
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the class.
     */
    public function update(User $user, Classes $class): bool
    {
        // Admin can update any class
        if ($user->role === 'admin') {
            return true;
        }

        // Class teachers can update their assigned class (limited fields)
        if ($user->role === 'teacher') {
            return $class->class_teacher_id === $user->teacher->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the class.
     */
    public function delete(User $user, Classes $class): bool
    {
        // Only admin can delete classes
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can manage class subjects.
     */
    public function manageSubjects(User $user, Classes $class): bool
    {
        // Admin can manage subjects for any class
        if ($user->role === 'admin') {
            return true;
        }

        // Class teachers can manage subjects for their class
        if ($user->role === 'teacher') {
            return $class->class_teacher_id === $user->teacher->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can manage class enrollment.
     */
    public function manageEnrollment(User $user, Classes $class): bool
    {
        // Admin can manage enrollment for any class
        if ($user->role === 'admin') {
            return true;
        }

        // Class teachers can manage enrollment for their class
        if ($user->role === 'teacher') {
            return $class->class_teacher_id === $user->teacher->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can view class analytics.
     */
    public function viewAnalytics(User $user, Classes $class): bool
    {
        // Admin can view analytics for any class
        if ($user->role === 'admin') {
            return true;
        }

        // Teachers can view analytics for classes they teach
        if ($user->role === 'teacher') {
            return $user->teacher->classSubjects()
                ->where('class_id', $class->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can assign teachers to class.
     */
    public function assignTeacher(User $user): bool
    {
        // Only admin can assign teachers to classes
        return $user->role === 'admin';
    }
}
