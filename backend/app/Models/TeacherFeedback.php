<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherFeedback extends Model
{
    use HasFactory;

    protected $table = 'teacher_feedback';

    protected $fillable = [
        'teacher_id',
        'student_id',
        'term_id',
        'rating',
        'feedback_text',
        'improvement_areas',
        'strengths',
        'feedback_date',
    ];

    protected $casts = [
        'feedback_date' => 'date',
    ];

    // Relationships
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'user_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}
