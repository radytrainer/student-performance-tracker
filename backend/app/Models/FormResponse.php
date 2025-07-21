<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_form_id',
        'student_id',
        'form_assignment_id',
        'google_response_id',
        'response_data',
        'score_total',
        'average_score',
        'submitted_at',
    ];

    protected $casts = [
        'response_data' => 'array',
        'score_total' => 'decimal:2',
        'average_score' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    // Relationships
    public function feedbackForm(): BelongsTo
    {
        return $this->belongsTo(FeedbackForm::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function formAssignment(): BelongsTo
    {
        return $this->belongsTo(FormAssignment::class);
    }

    // Scopes
    public function scopeByForm($query, $formId)
    {
        return $query->where('feedback_form_id', $formId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByAssignment($query, $assignmentId)
    {
        return $query->where('form_assignment_id', $assignmentId);
    }

    // Helper methods
    public function calculateScores()
    {
        $responses = $this->response_data;
        if (empty($responses) || !is_array($responses)) {
            return;
        }

        $total = 0;
        $count = 0;

        foreach ($responses as $response) {
            if (is_numeric($response)) {
                $total += $response;
                $count++;
            }
        }

        if ($count > 0) {
            $this->score_total = $total;
            $this->average_score = round($total / $count, 2);
        }
    }
}
