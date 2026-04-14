<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'plan_name',
        'status',
        'is_trial',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'cancellation_reason',
        'payment_id',
    ];

    protected $casts = [
        'is_trial' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    const STATUS_TRIAL = 'trial';
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isActive(): bool
    {
        if (!in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_TRIAL], true)) {
            return false;
        }

        if ($this->ends_at === null) {
            return $this->status === self::STATUS_ACTIVE;
        }

        return $this->ends_at->isFuture();
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }
}
