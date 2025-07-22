<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndividualFormAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_form_id',
        'assigned_to_user_id',
        'assigned_by_teacher_id',
        'due_date',
        'is_active',
        'instructions',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function feedbackForm(): BelongsTo
    {
        return $this->belongsTo(FeedbackForm::class);
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedByTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_teacher_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('assigned_to_user_id', $userId);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('assigned_by_teacher_id', $teacherId);
    }
}
