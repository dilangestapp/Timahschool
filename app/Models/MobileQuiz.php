<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileQuiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'school_class_id',
        'subject_id',
        'status',
        'opens_at',
        'closes_at',
        'duration_minutes',
        'pass_score',
        'requires_subscription',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'requires_subscription' => 'boolean',
        'pass_score' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions()
    {
        return $this->hasMany(MobileQuizQuestion::class)->orderBy('position')->orderBy('id');
    }

    public function attempts()
    {
        return $this->hasMany(MobileQuizAttempt::class);
    }
}
