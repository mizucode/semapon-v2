<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'store_id',
    'name',
    'phone',
    'email',
    'address',
    'notes',
    'total_orders_count',
    'total_spent',
])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToTenant, HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'total_orders_count' => 'integer',
            'total_spent' => 'decimal:2',
        ];
    }

    public function devices(): HasMany
    {
        return $this->hasMany(CustomerDevice::class, 'customer_id');
    }

    public function getFormattedPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        return $phone;
    }
}
