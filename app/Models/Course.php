<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'school_class_id',
        'created_by',
        'title',
        'slug',
        'description',
        'objectives',
        'content_html',
        'content_text',
        'editor_type',
        'level',
        'thumbnail',
        'order',
        'status',
        'published_at',
        'document_path',
        'document_name',
        'document_mime',
        'document_size',
        'is_downloadable',
        'mobile_access',
        'estimated_minutes',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'document_size' => 'integer',
        'is_downloadable' => 'boolean',
        'estimated_minutes' => 'integer',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    public const MOBILE_ACCESS_SUBSCRIPTION = 'subscription';
    public const MOBILE_ACCESS_FREE = 'free';
    public const MOBILE_ACCESS_LOCKED = 'locked';

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeVisibleOnMobile($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->when(Schema::hasColumn('courses', 'mobile_access'), function ($builder) {
                $builder->where(function ($sub) {
                    $sub->whereNull('mobile_access')
                        ->orWhere('mobile_access', '!=', self::MOBILE_ACCESS_LOCKED);
                });
            });
    }

    public function hasDocument(): bool
    {
        return !empty($this->document_path);
    }

    public function hasRichContent(): bool
    {
        return trim((string) ($this->content_html ?? '')) !== '';
    }

    public function isDownloadable(): bool
    {
        if (!Schema::hasColumn('courses', 'is_downloadable')) {
            return true;
        }

        return (bool) ($this->is_downloadable ?? true);
    }

    public function mobileAccess(): string
    {
        if (!Schema::hasColumn('courses', 'mobile_access')) {
            return self::MOBILE_ACCESS_SUBSCRIPTION;
        }

        return $this->mobile_access ?: self::MOBILE_ACCESS_SUBSCRIPTION;
    }

    public function excerpt(int $limit = 160): string
    {
        $text = trim((string) ($this->content_text ?? $this->description ?? ''));

        if ($text === '') {
            return '';
        }

        return mb_strlen($text) > $limit
            ? mb_substr($text, 0, $limit - 1) . '…'
            : $text;
    }

    public function humanDocumentSize(): string
    {
        $bytes = (int) ($this->document_size ?? 0);

        if ($bytes <= 0) {
            return '-';
        }

        $units = ['o', 'Ko', 'Mo', 'Go'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 2, ',', ' ') . ' ' . $units[$power];
    }
}
