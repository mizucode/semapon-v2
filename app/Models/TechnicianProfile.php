<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'store_id',
    'user_id',
    'membership_id',
    'specialization',
    'commission_type',
    'commission_rate',
    'notes',
    'total_jobs_completed',
    'total_commission_earned',
])]
class TechnicianProfile extends Model
{
    use BelongsToTenant, HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'total_jobs_completed' => 'integer',
            'total_commission_earned' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(StoreMembership::class, 'membership_id');
    }

    public function isActive(): bool
    {
        return $this->membership?->status === 'active';
    }

    public function calculateCommission(float $serviceLaborFee, float $profit = 0): float
    {
        return match ($this->commission_type) {
            'percentage' => round(($serviceLaborFee * (float) $this->commission_rate) / 100, 2),
            'profit_split' => round(($profit * (float) $this->commission_rate) / 100, 2),
            'fixed' => (float) $this->commission_rate,
            default => 0.0,
        };
    }
}
