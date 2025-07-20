<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'student_code',
        'date_of_birth',
        'gender',
        'address',
        'parent_name',
        'parent_phone',
        'enrollment_date',
        'current_class_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentClass(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'current_class_id');
    }

    public function studentClasses(): HasMany
    {
        return $this->hasMany(StudentClass::class, 'student_id', 'user_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'student_id', 'user_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id', 'user_id');
    }

    public function performanceAlerts(): HasMany
    {
        return $this->hasMany(PerformanceAlert::class, 'student_id', 'user_id');
    }

    public function studentFeedback(): HasMany
    {
        return $this->hasMany(StudentFeedback::class, 'student_id', 'user_id');
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class, 'student_id', 'user_id');
    }

    public function studentNotes(): HasMany
    {
        return $this->hasMany(StudentNote::class, 'student_id', 'user_id');
    }

    // Query scopes for role-based filtering
    public function scopeAccessibleToUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query; // Admin can see all students
        }

        if ($user->isTeacher()) {
            // Teachers can only see students in their assigned classes
            return $query->whereHas('studentClasses', function ($q) use ($user) {
                $q->whereIn('class_id', function ($subQuery) use ($user) {
                    $subQuery->select('class_id')
                        ->from('class_subjects')
                        ->where('teacher_id', $user->teacher->user_id);
                })->where('status', 'active');
            });
        }

        if ($user->isStudent()) {
            // Students can only see their own profile
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1 = 0'); // No access for other roles
    }

    public function scopeInClass($query, $classId)
    {
        return $query->whereHas('studentClasses', function ($q) use ($classId) {
            $q->where('class_id', $classId)->where('status', 'active');
        });
    }

    public function scopeActive($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('is_active', true);
        });
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }
}
