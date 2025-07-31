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

    // Helper methods
    public function getFormattedOverallGradeAttribute()
    {
        return number_format($this->overall_grade, 2);
    }

    public function getFormattedAttendanceAttribute()
    {
        return number_format($this->attendance_percentage, 1) . '%';
    }

    public function getGradeLetterAttribute()
    {
        $grade = $this->overall_grade;
        
        if ($grade >= 90) return 'A+';
        if ($grade >= 85) return 'A';
        if ($grade >= 80) return 'B+';
        if ($grade >= 75) return 'B';
        if ($grade >= 70) return 'C+';
        if ($grade >= 65) return 'C';
        if ($grade >= 60) return 'D+';
        if ($grade >= 55) return 'D';
        return 'F';
    }

    public function getAttendanceStatusAttribute()
    {
        $percentage = $this->attendance_percentage;
        
        if ($percentage >= 95) return 'Excellent';
        if ($percentage >= 85) return 'Good';
        if ($percentage >= 75) return 'Satisfactory';
        if ($percentage >= 65) return 'Needs Improvement';
        return 'Poor';
    }
}
