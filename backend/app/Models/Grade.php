<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Term;
use App\Models\User;

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
        'max_score'      => 'decimal:2',
        'score_obtained' => 'decimal:2',
        'weightage'      => 'decimal:2',
        'recorded_at'    => 'datetime',
    ];

    /**
     * Relationships — properly imported models
     */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_id');
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class, 'class_subject_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope filters for role-based access control
     */

    public function scopeAccessibleToUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isTeacher()) {
            return $query->whereHas('classSubject', function ($q) use ($user) {
                $q->where('teacher_id', $user->teacher->user_id);
            });
        }

        if ($user->isStudent()) {
            return $query->where('student_id', $user->student->user_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeByAssessmentType($query, string $type)
    {
        return $query->where('assessment_type', $type);
    }
}
