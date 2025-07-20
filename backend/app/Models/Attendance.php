<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'date',
        'status',
        'recorded_by',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'date' => 'date',
        'recorded_at' => 'datetime',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Query scopes for role-based filtering
    public function scopeAccessibleToUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query; // Admin can see all attendance records
        }

        if ($user->isTeacher()) {
            // Teachers can only see attendance for their assigned classes
            return $query->whereIn('class_id', function ($subQuery) use ($user) {
                $subQuery->select('class_id')
                    ->from('class_subjects')
                    ->where('teacher_id', $user->teacher->user_id);
            });
        }

        if ($user->isStudent()) {
            // Students can only see their own attendance
            return $query->where('student_id', $user->student->user_id);
        }

        return $query->whereRaw('1 = 0'); // No access for other roles
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
