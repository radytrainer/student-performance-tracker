<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFeedback extends Model
{
    use HasFactory;

    protected $table = 'student_feedback';

    protected $fillable = [
        'student_id',
        'teacher_id',
        'term_id',
        'question_1',
        'question_2',
        'question_3',
        'question_4',
        'question_5',
        'overall_rating',
        'comments',
        'feedback_date',
    ];

    protected $casts = [
        'overall_rating' => 'decimal:2',
        'feedback_date' => 'date',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'user_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}
