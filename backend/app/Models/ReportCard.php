<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'term_id',
        'generated_by',
        'overall_grade',
        'attendance_percentage',
        'principal_remarks',
        'file_path',
        'generated_at',
    ];

    protected $casts = [
        'overall_grade' => 'decimal:2',
        'attendance_percentage' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
