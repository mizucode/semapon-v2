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
    'customer_id',
    'brand',
    'model',
    'color',
    'variant',
    'specs',
    'imei',
    'serial_number',
    'passcode',
    'notes',
])]
class CustomerDevice extends Model
{
    use BelongsToTenant, HasFactory, HasUlids;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getDeviceNameAttribute(): string
    {
        if ($this->variant) {
            return "{$this->brand} {$this->model} ({$this->variant})";
        }

        return "{$this->brand} {$this->model}";
    }
}
