<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = [
        'store_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'subscription_id');
    }

    public function isActive(): bool
    {
        if ($this->status === 'active') {
            return $this->ends_at === null || $this->ends_at->isFuture();
        }

        if ($this->status === 'trial') {
            return $this->trial_ends_at === null || $this->trial_ends_at->isFuture();
        }

        return false;
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial' && ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }

    public function getDaysLeftAttribute(): int
    {
        if ($this->isTrial() && $this->trial_ends_at) {
            return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
        }

        if ($this->ends_at) {
            return max(0, (int) now()->diffInDays($this->ends_at, false));
        }

        return 0;
    }
}
