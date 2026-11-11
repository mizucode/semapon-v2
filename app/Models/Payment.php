<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = [
        'store_id',
        'service_order_id',
        'customer_id',
        'user_id',
        'payment_number',
        'amount',
        'type',
        'payment_method',
        'reference_number',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->payment_number)) {
                $prefix = 'PAY-'.date('Ym').'-';
                $latest = static::withoutGlobalScopes()
                    ->where('store_id', $payment->store_id)
                    ->where('payment_number', 'like', "{$prefix}%")
                    ->latest('id')
                    ->value('payment_number');

                $nextNumber = 1;
                if ($latest) {
                    $parts = explode('-', $latest);
                    $nextNumber = ((int) end($parts)) + 1;
                }

                $payment->payment_number = $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
