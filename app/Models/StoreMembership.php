<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'store_id',
    'user_id',
    'role',
    'status',
    'joined_at',
])]
class StoreMembership extends Model
{
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        $role = Role::with('permissions')->where('slug', $this->role)->first();

        return $role ? $role->hasPermission($permissionSlug) : false;
    }
}
