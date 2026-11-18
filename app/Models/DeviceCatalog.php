<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'brand',
    'model_name',
    'series',
    'chipset',
    'specs_summary',
    'default_colors',
    'default_variants',
    'is_verified',
    'store_id',
    'created_by',
])]
class DeviceCatalog extends Model
{
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_colors' => 'array',
            'default_variants' => 'array',
            'is_verified' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope query to items available for a specific store (global master OR store's own custom items).
     */
    public function scopeAvailableFor(Builder $query, ?string $storeId = null): Builder
    {
        return $query->where(function (Builder $q) use ($storeId) {
            $q->whereNull('store_id');
            if ($storeId) {
                $q->orWhere('store_id', $storeId);
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->brand} {$this->model_name}";
    }
}
