<?php

namespace App\Models;

use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'owner_id',
    'name',
    'slug',
    'phone',
    'email',
    'address',
    'logo_path',
    'status',
    'plan',
    'trial_ends_at',
    'subscription_ends_at',
    'settings',
])]
class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Store $store) {
            if (empty($store->slug)) {
                $slug = Str::slug($store->name);
                $originalSlug = $slug;
                $counter = 1;

                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$originalSlug}-{$counter}";
                    $counter++;
                }

                $store->slug = $slug;
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(StoreMembership::class, 'store_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_memberships')
            ->withPivot(['id', 'role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function technicians(): HasMany
    {
        return $this->hasMany(TechnicianProfile::class, 'store_id');
    }

    public function activeTechniciansCount(): int
    {
        return $this->technicians()
            ->whereHas('membership', fn ($q) => $q->where('status', 'active'))
            ->count();
    }

    public function getTechnicianLimit(): int
    {
        $plan = config("semapon.plans.{$this->plan}");

        return (int) ($plan['technicians_limit'] ?? 1);
    }

    public function canAddTechnician(): bool
    {
        return $this->activeTechniciansCount() < $this->getTechnicianLimit();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTrialing(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
