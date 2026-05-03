<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileDevice extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_REPLACED = 'replaced';

    protected $fillable = [
        'user_id',
        'phone',
        'device_id',
        'device_name',
        'device_model',
        'platform',
        'app_version',
        'status',
        'first_login_at',
        'last_seen_at',
        'replaced_at',
        'blocked_at',
        'admin_note',
    ];

    protected $casts = [
        'first_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'replaced_at' => 'datetime',
        'blocked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function markSeen(): void
    {
        $this->forceFill(['last_seen_at' => now()])->save();
    }
}
