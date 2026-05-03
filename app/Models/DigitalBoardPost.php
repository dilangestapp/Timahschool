<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalBoardPost extends Model
{
    use HasFactory;

    public const TYPE_ANNOUNCEMENT = 'announcement';
    public const TYPE_REPORT = 'report';
    public const TYPE_EVALUATION = 'evaluation';
    public const TYPE_SUBSCRIPTION = 'subscription';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'author_id',
        'title',
        'content',
        'type',
        'audience',
        'school_class_id',
        'status',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(function ($sub) {
                $sub->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function ($sub) {
                $sub->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}
