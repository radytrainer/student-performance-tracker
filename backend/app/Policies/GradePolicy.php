<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Grade;

class GradePolicy
{
    public function viewAny(User $user): bool
    {
        // Allow all roles to enter, filtering is done in controller
        return in_array($user->role, ['admin', 'teacher', 'student']);
    }

    public function view(User $user, Grade $grade): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isTeacher() && $user->teacher) {
            return $grade->classSubject->teacher_id === $user->teacher->user_id;
        }

        if ($user->isStudent() && $user->student) {
            return $grade->student_id === $user->student->user_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }

    public function update(User $user, Grade $grade): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isTeacher() && $user->teacher) {
            return $grade->classSubject->teacher_id === $user->teacher->user_id;
        }

        return false;
    }

    public function delete(User $user, Grade $grade): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isTeacher() && $user->teacher) {
            return $grade->classSubject->teacher_id === $user->teacher->user_id;
        }

        return false;
    }

    public function viewStudentGrades(User $user, User $student): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isTeacher() && $user->teacher) {
            return $user->teacher->classSubjects()
                ->whereHas('class.studentClasses', fn($q) =>
                    $q->where('student_id', $student->student->user_id)
                      ->where('status', 'active')
                )->exists();
        }

        if ($user->isStudent()) {
            return $user->id === $student->id;
        }

        return false;
    }

    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }
}
