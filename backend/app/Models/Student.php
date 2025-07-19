<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'student_code',
        'date_of_birth',
        'gender',
        'address',
        'parent_name',
        'parent_phone',
        'enrollment_date',
        'current_class_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentClass(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'current_class_id');
    }

    public function studentClasses(): HasMany
    {
        return $this->hasMany(StudentClass::class, 'student_id', 'user_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'student_id', 'user_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id', 'user_id');
    }

    public function performanceAlerts(): HasMany
    {
        return $this->hasMany(PerformanceAlert::class, 'student_id', 'user_id');
    }

    public function studentFeedback(): HasMany
    {
        return $this->hasMany(StudentFeedback::class, 'student_id', 'user_id');
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class, 'student_id', 'user_id');
    }

    public function studentNotes(): HasMany
    {
        return $this->hasMany(StudentNote::class, 'student_id', 'user_id');
    }
}
