<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_label',
        'role_tag',
        'message',
        'is_anonymous',
        'is_published',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];
}
