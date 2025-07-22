<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedbackForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'google_form_id',
        'google_form_url',
        'template_used',
        'created_by_teacher_id',
        'is_active',
        'survey_type',
        'questions',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function createdByTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_teacher_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(FormAssignment::class);
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

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('created_by_teacher_id', $teacherId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('survey_type', $type);
    }

    // Helper methods
    public function getTotalResponsesAttribute()
    {
        return $this->responses()->count();
    }

    public function getAverageScoreAttribute()
    {
        return $this->responses()->avg('average_score');
    }
}
