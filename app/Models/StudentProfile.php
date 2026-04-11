<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_class_id',
        'parent_name',
        'parent_phone',
        'birth_date',
        'gender',
        'notes',
        'trial_started_at',
        'trial_ends_at',
        'trial_used',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'trial_used' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function isTrialActive(): bool
    {
        if (!$this->trial_used || !$this->trial_ends_at) {
            return false;
        }
        return $this->trial_ends_at->isFuture();
    }
}