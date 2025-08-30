<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_code',
        'subject_name',
        'description',
        'credit_hours',
        'department',
        'is_core',
    ];

    protected $casts = [
        'is_core' => 'boolean',
    ];

    // Relationships
    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }

    public function grades(): HasManyThrough
    {
        return $this->hasManyThrough(Grade::class, ClassSubject::class);
    }

    public function performanceAlerts(): HasMany
    {
        return $this->hasMany(PerformanceAlert::class);
    }

    // Teacher-Subject relationships
    public function teachers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_subjects', 'subject_id', 'teacher_id')
            ->where('users.role', 'teacher')
            ->withPivot('primary_subject', 'assigned_at')
            ->withTimestamps();
    }

    public function primaryTeachers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->teachers()->wherePivot('primary_subject', true);
    }
}
