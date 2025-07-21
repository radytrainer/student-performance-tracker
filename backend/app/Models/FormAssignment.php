<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_form_id',
        'class_id',
        'assigned_by_teacher_id',
        'due_date',
        'scheduled_date',
        'is_active',
        'auto_remind',
        'remind_days_before',
        'instructions',
    ];

    protected $casts = [
        'due_date' => 'date',
        'scheduled_date' => 'datetime',
        'is_active' => 'boolean',
        'auto_remind' => 'boolean',
        'remind_days_before' => 'integer',
    ];

    // Relationships
    public function feedbackForm(): BelongsTo
    {
        return $this->belongsTo(FeedbackForm::class);
    }

    public function assignedClass(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function assignedByTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_teacher_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(FormResponse::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('assigned_by_teacher_id', $teacherId);
    }

    // Helper methods
    public function getResponsesCountAttribute()
    {
        return $this->responses()->count();
    }

    public function getStudentsCountAttribute()
    {
        return $this->assignedClass->students()->count();
    }

    public function getCompletionRateAttribute()
    {
        $studentsCount = $this->students_count;
        if ($studentsCount === 0) return 0;
        return round(($this->responses_count / $studentsCount) * 100, 2);
    }
}
