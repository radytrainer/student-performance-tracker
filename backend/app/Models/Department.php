<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'head_teacher_id',
    ];

    protected $with = ['headTeacher'];

    public function headTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_teacher_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'teacher');
    }

    public function getTeacherCountAttribute(): int
    {
        return $this->teachers()->count();
    }

    public function generateTeacherCode(): string
    {
        $deptCode = strtoupper(substr($this->name, 0, 4));
        
        // Get all existing teacher codes for this department
        $existingCodes = $this->teachers()
            ->whereHas('teacher', function($q) use ($deptCode) {
                $q->where('teacher_code', 'like', $deptCode . '-%');
            })
            ->with('teacher')
            ->get()
            ->pluck('teacher.teacher_code')
            ->filter()
            ->toArray();
        
        // Extract numbers from existing codes
        $existingNumbers = [];
        foreach ($existingCodes as $code) {
            if (preg_match('/(\d+)$/', $code, $matches)) {
                $existingNumbers[] = intval($matches[1]);
            }
        }
        
        // Find next available number
        $nextNumber = 1;
        while (in_array($nextNumber, $existingNumbers)) {
            $nextNumber++;
        }

        return $deptCode . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->whereHas('users', function($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        });
    }
}
