<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiweeklyEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'school_class_id',
        'period_starts_at',
        'period_ends_at',
        'opens_at',
        'closes_at',
        'duration_minutes',
        'status',
    ];

    protected $casts = [
        'period_starts_at' => 'datetime',
        'period_ends_at' => 'datetime',
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function reports()
    {
        return $this->hasMany(ProgressReport::class);
    }
}
