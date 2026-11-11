<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = [
        'store_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'supplier_id')->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'supplier_id');
    }

    public function getTotalDebtAttribute(): float
    {
        return (float) $this->purchases()
            ->where('status', '!=', 'cancelled')
            ->selectRaw('SUM(total_amount - paid_amount) as debt')
            ->value('debt') ?? 0;
    }
}
