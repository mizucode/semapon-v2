<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = [
        'store_id',
        'subscription_id',
        'plan_id',
        'invoice_number',
        'amount',
        'billing_cycle',
        'payment_method',
        'status',
        'proof_image',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SubscriptionPayment $invoice) {
            if (empty($invoice->invoice_number)) {
                $prefix = 'INV-'.date('Ym').'-';
                $latest = static::withoutGlobalScopes()
                    ->where('invoice_number', 'like', "{$prefix}%")
                    ->latest('id')
                    ->value('invoice_number');

                $nextNumber = 1;
                if ($latest) {
                    $parts = explode('-', $latest);
                    $nextNumber = ((int) end($parts)) + 1;
                }

                $invoice->invoice_number = $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
