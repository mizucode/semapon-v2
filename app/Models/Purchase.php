<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = [
        'store_id',
        'supplier_id',
        'user_id',
        'purchase_number',
        'purchase_date',
        'total_amount',
        'paid_amount',
        'payment_status',
        'status',
        'received_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'received_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Purchase $purchase) {
            if (empty($purchase->purchase_number)) {
                $prefix = 'PO-'.date('Ym').'-';
                $latest = static::withoutGlobalScopes()
                    ->where('store_id', $purchase->store_id)
                    ->where('purchase_number', 'like', "{$prefix}%")
                    ->latest('id')
                    ->value('purchase_number');

                $nextNumber = 1;
                if ($latest) {
                    $parts = explode('-', $latest);
                    $nextNumber = ((int) end($parts)) + 1;
                }

                $purchase->purchase_number = $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'purchase_id');
    }

    public function getRemainingDebtAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }
}
