<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'store_id',
    'customer_id',
    'customer_device_id',
    'order_number',
    'tracking_token',
    'status',
    'complaints',
    'physical_condition',
    'photos',
    'accessories_included',
    'passcode',
    'is_cost_undetermined',
    'estimated_cost',
    'total_cost',
    'down_payment',
    'paid_amount',
    'payment_status',
    'technician_id',
    'created_by',
    'estimated_completion_at',
    'customer_approved_at',
    'completed_at',
    'picked_up_at',
    'warranty_ends_at',
])]
class ServiceOrder extends Model
{
    use BelongsToTenant, HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'physical_condition' => 'array',
            'photos' => 'array',
            'is_cost_undetermined' => 'boolean',
            'estimated_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'down_payment' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'estimated_completion_at' => 'datetime',
            'customer_approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'warranty_ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ServiceOrder $order) {
            if (empty($order->tracking_token)) {
                $order->tracking_token = bin2hex(random_bytes(32));
            }

            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });

        static::created(function (ServiceOrder $order) {
            if (! empty($order->tracking_token)) {
                ServiceTrackingToken::create([
                    'store_id' => $order->store_id,
                    'service_order_id' => $order->id,
                    'token_hash' => hash('sha256', $order->tracking_token),
                    'expires_at' => now()->addDays(90),
                ]);
            }
        });
    }

    /**
     * Generate unique human-safe order number: ORD-YYMMDD-XXXXXXXX (Base32 safe without 0, O, 1, I).
     */
    public static function generateOrderNumber(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $charLength = strlen($chars);
        $datePrefix = 'ORD-'.date('ymd').'-';

        do {
            $suffix = '';
            for ($i = 0; $i < 8; $i++) {
                $suffix .= $chars[random_int(0, $charLength - 1)];
            }
            $orderNumber = $datePrefix.$suffix;
        } while (static::withoutGlobalScopes()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    public function trackingTokens(): HasMany
    {
        return $this->hasMany(ServiceTrackingToken::class, 'service_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(CustomerDevice::class, 'customer_device_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class, 'service_order_id');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(ServiceOrderDiagnosis::class, 'service_order_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ServiceOrderHistory::class, 'service_order_id')->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'service_order_id')->latest();
    }

    public function getRemainingBalanceAttribute(): float
    {
        return max(0.0, (float) $this->total_cost - (float) $this->paid_amount);
    }

    public function getOverpaidAmountAttribute(): float
    {
        if ((float) $this->total_cost <= 0) {
            return 0.0;
        }

        return max(0.0, (float) $this->paid_amount - (float) $this->total_cost);
    }

    public function getComplaintAttribute(): ?string
    {
        return $this->complaints;
    }

    public function getAccessoriesBroughtAttribute(): ?string
    {
        return $this->accessories_included;
    }

    public function getSecurityCodeAttribute(): ?string
    {
        return $this->passcode ?? $this->device?->security_code;
    }

    public function updateStatus(string $newStatus, ?string $notes = null, ?string $userId = null): void
    {
        $oldStatus = $this->status;

        $this->status = $newStatus;

        if ($newStatus === 'completed' && ! $this->completed_at) {
            $this->completed_at = now();
        }

        if ($newStatus === 'picked_up') {
            $this->picked_up_at = now();
            $warrantyDays = (int) ($this->store?->getSetting('default_warranty_days', 30) ?? 30);
            $this->warranty_ends_at = now()->addDays($warrantyDays);
        }

        $this->save();

        $this->histories()->create([
            'store_id' => $this->store_id,
            'user_id' => $userId ?: auth()->id(),
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'notes' => $notes,
        ]);
    }
}
