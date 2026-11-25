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

    public function isSalaried(): bool
    {
        return $this->commission_type === 'salary';
    }

    public function hasCommission(): bool
    {
        return ! $this->isSalaried() && in_array($this->commission_type, ['percentage', 'fixed', 'profit_split'], true);
    }

    public function calculateCommission(float $serviceLaborFee, float $profit = 0): float
    {
        if ($this->isSalaried()) {
            return 0.0;
        }

        return match ($this->commission_type) {
            'percentage' => round(($serviceLaborFee * (float) $this->commission_rate) / 100, 2),
            'profit_split' => round(($profit * (float) $this->commission_rate) / 100, 2),
            'fixed' => (float) $this->commission_rate,
            default => 0.0,
        };
    }

    public function calculateOrderCommission(ServiceOrder $order): float
    {
        if ($this->isSalaried() || (float) $this->commission_rate <= 0) {
            return 0.0;
        }

        $totalCost = (float) $order->total_cost;

        if ($this->commission_type === 'profit_split') {
            $partsCost = (float) $order->parts()
                ->get()
                ->sum(fn (ServiceOrderPart $part) => (float) $part->cost_price * $part->quantity);
            $profit = max(0.0, $totalCost - $partsCost);

            return round(($profit * (float) $this->commission_rate) / 100, 2);
        }

        return match ($this->commission_type) {
            'percentage' => round(($totalCost * (float) $this->commission_rate) / 100, 2),
            'fixed' => (float) $this->commission_rate,
            default => 0.0,
        };
    }
}
