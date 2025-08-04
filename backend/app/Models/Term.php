<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'term_name',
        'academic_year',
        'start_date',
        'end_date',
        'is_current',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    // Relationships
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function performanceAlerts(): HasMany
    {
        return $this->hasMany(PerformanceAlert::class);
    }

    public function studentFeedback(): HasMany
    {
        return $this->hasMany(StudentFeedback::class);
    }

    public function teacherFeedback(): HasMany
    {
        return $this->hasMany(TeacherFeedback::class);
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }
}
