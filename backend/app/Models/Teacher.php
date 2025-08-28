<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'teacher_code',
        'department',
        'qualification',
        'specialization',
        'hire_date',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(Classes::class, 'class_teacher_id', 'user_id');
    }

    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'teacher_id', 'user_id');
    }

    public function studentNotes(): HasMany
    {
        return $this->hasMany(StudentNote::class, 'teacher_id', 'user_id');
    }

    public function teacherFeedback(): HasMany
    {
        return $this->hasMany(TeacherFeedback::class, 'teacher_id', 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->user->department();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($teacher) {
            if (empty($teacher->teacher_code)) {
                $user = User::find($teacher->user_id);
                if ($user && $user->department) {
                    $teacher->teacher_code = $user->department->generateTeacherCode();
                }
            }
        });
    }
}
