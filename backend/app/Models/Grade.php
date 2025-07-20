<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_subject_id',
        'term_id',
        'assessment_type',
        'max_score',
        'score_obtained',
        'weightage',
        'grade_letter',
        'remarks',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'score_obtained' => 'decimal:2',
        'weightage' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_id');
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Query scopes for role-based filtering
    public function scopeAccessibleToUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query; // Admin can see all grades
        }

        if ($user->isTeacher()) {
            // Teachers can only see grades for their assigned subjects
            return $query->whereHas('classSubject', function ($q) use ($user) {
                $q->where('teacher_id', $user->teacher->user_id);
            });
        }

        if ($user->isStudent()) {
            // Students can only see their own grades
            return $query->where('student_id', $user->student->user_id);
        }

        return $query->whereRaw('1 = 0'); // No access for other roles
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeByAssessmentType($query, $type)
    {
        return $query->where('assessment_type', $type);
    }
}
