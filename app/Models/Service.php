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
    'category_id',
    'name',
    'description',
    'price',
    'estimated_duration_minutes',
    'warranty_days',
    'is_active',
])]
class Service extends Model
{
    use BelongsToTenant, HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'estimated_duration_minutes' => 'integer',
            'warranty_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }
}
