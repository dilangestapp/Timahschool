<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'full_name',
        'email',
        'phone',
        'avatar',
        'status',
        'password',
        'last_login_at',
        'last_login_ip',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class)->latest();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id')->latest();
    }

    public function assignedClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_assignments', 'teacher_id', 'school_class_id')
            ->withPivot(['subject_id', 'is_active', 'notes'])
            ->withTimestamps();
    }

    public function receivedTeacherMessages()
    {
        return $this->hasMany(TeacherMessage::class, 'teacher_id')->latest();
    }

    public function sentTeacherMessages()
    {
        return $this->hasMany(TeacherMessage::class, 'student_id')->latest();
    }

    public function getActiveSubscriptionAttribute(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIAL])
            ->where('ends_at', '>', now())
            ->first();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription !== null;
    }

    public function hasRole(string $roleName): bool
    {
        $roleName = mb_strtolower(trim($roleName));

        if ($this->relationLoaded('role') && $this->role && mb_strtolower((string) $this->role->name) === $roleName) {
            return true;
        }

        if ($this->role && mb_strtolower((string) $this->role->name) === $roleName) {
            return true;
        }

        return $this->roles()->whereRaw('LOWER(name) = ?', [$roleName])->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher') || $this->hasRole('enseignant');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student') || $this->hasRole('eleve') || $this->hasRole('élève');
    }
}
