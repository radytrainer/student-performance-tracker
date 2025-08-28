<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
use HasApiTokens, HasFactory, Notifiable;

protected $fillable = [
'username',
'email',
'password_hash',
'role',
'school_id',
'department_id',
'is_super_admin',
'first_name',
'last_name',
'profile_picture',
'phone',
'bio',
'is_active',
'last_login',
];

        // Always include computed profile_picture_url in JSON responses
        protected $appends = ['profile_picture_url'];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    // Add password accessor for authentication
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
    protected $casts = [
        'responsibilities' => 'array',
        'qualifications' => 'array',
        'posted_date' => 'date',
    ];
    public function getCreatedAtAttribute($value)
    {
        $date = date('d-m-Y', strtotime($value));
        $day = date('l', strtotime($value));
        return $day . ', ' . $date;
    }

    public function getUpdatedAtAttribute($value)
    {
        $date = date('F d, Y', strtotime($value));
        $day = date('l', strtotime($value));
        return $day . ', ' . $date;
    }
    public function getPostedDateAttribute($value)
    {
        return date('d-M-y', strtotime($value));
    }

    // Accessor for full image URL (legacy)
    public function getImageUrlAttribute()
    {
        return $this->getProfilePictureUrlAttribute();
    }

    // Accessor for profile_picture_url (absolute URL or null)
    public function getProfilePictureUrlAttribute()
    {
        if (!$this->profile_picture) {
            return null;
        }
        $pp = $this->profile_picture;
        if (str_starts_with($pp, 'http://') || str_starts_with($pp, 'https://')) {
            return $pp;
        }
        return asset('storage/' . ltrim($pp, '/'));
    }

    // Accessor for formatted created_at
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('d-M-y') : null;
    }

    // Relationships
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function headedDepartment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Department::class, 'head_teacher_id');
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'recorded_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // Role checking methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin' && $this->is_super_admin;
    }

    public function isSchoolAdmin(): bool
    {
        return $this->role === 'admin' && !$this->is_super_admin;
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    // Query scopes for role-based filtering
    public function scopeAccessibleUsers($query, User $currentUser)
    {
        if ($currentUser->isSuperAdmin()) {
            // Super admin can see all users
            return $query;
        } elseif ($currentUser->isSchoolAdmin()) {
            // School admin can only see users from their school
            return $query->where('school_id', $currentUser->school_id);
        }

        // Non-admin users can only see their own profile
        return $query->where('id', $currentUser->id);
    }

    // Scope for school-based isolation
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
