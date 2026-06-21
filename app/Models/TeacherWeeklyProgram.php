<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherWeeklyProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'teacher_assignment_id',
        'school_class_id',
        'subject_id',
        'week_start',
        'program_date',
        'weekday',
        'start_time',
        'end_time',
        'activity_type',
        'title',
        'description',
        'teacher_notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'week_start' => 'date',
        'program_date' => 'date',
        'weekday' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function assignment()
    {
        return $this->belongsTo(TeacherAssignment::class, 'teacher_assignment_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
