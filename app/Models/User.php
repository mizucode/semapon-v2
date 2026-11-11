<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\TenantContext;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'status', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlids, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(StoreMembership::class, 'user_id');
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_memberships')
            ->withPivot(['id', 'role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function ownedStores(): HasMany
    {
        return $this->hasMany(Store::class, 'owner_id');
    }

    public function currentMembership(?string $storeId = null): ?StoreMembership
    {
        $query = $this->memberships()->where('status', 'active');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->first();
    }

    public function isSuperAdmin(): bool
    {
        return $this->memberships()
            ->where('role', 'super_admin')
            ->where('status', 'active')
            ->exists();
    }

    public function hasPermission(string $permissionSlug, ?string $storeId = null): bool
    {
        $tenantContext = app(TenantContext::class);
        $storeId = $storeId ?? $tenantContext->getStoreId();

        $membership = $this->memberships()
            ->where('status', 'active')
            ->when($storeId, fn ($q) => $q->where('store_id', $storeId))
            ->first();

        return $membership ? $membership->hasPermission($permissionSlug) : false;
    }
}
