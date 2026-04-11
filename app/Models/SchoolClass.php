<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'school_classes';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'level',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function studentProfiles()
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'school_class_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_assignments', 'school_class_id', 'teacher_id')
            ->withPivot(['subject_id', 'is_active', 'notes'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
