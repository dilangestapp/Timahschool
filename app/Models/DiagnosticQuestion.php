<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticQuestion extends Model
{
    protected $fillable = [
        'category',
        'question',
        'type',
        'options',
        'weight',
        'is_active',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'weight' => 'integer',
        'order' => 'integer',
    ];
}
