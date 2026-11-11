<?php

namespace App\Services;

use App\Models\Store;
use App\Models\StoreMembership;
use Illuminate\Support\Facades\Auth;

class TenantContext
{
    protected ?Store $store = null;

    protected ?StoreMembership $membership = null;

    public function setStore(Store $store, ?StoreMembership $membership = null): void
    {
        $this->store = $store;
        $this->membership = $membership;
    }

    public function getStore(): ?Store
    {
        if ($this->store === null) {
            $this->resolveFromSessionOrUser();
        }

        return $this->store;
    }

    public function getStoreId(): ?string
    {
        return $this->getStore()?->id;
    }

    public function getMembership(): ?StoreMembership
    {
        if ($this->membership === null) {
            $this->resolveFromSessionOrUser();
        }

        return $this->membership;
    }

    public function getRole(): ?string
    {
        return $this->getMembership()?->role;
    }

    public function isOwner(): bool
    {
        return $this->getRole() === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->getRole() === 'admin';
    }

    public function isTechnician(): bool
    {
        return $this->getRole() === 'technician';
    }

    public function clear(): void
    {
        $this->store = null;
        $this->membership = null;
    }

    protected function resolveFromSessionOrUser(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $activeStoreId = session('active_store_id');
        $membership = null;

        if ($activeStoreId) {
            $membership = StoreMembership::with('store')
                ->where('user_id', $user->id)
                ->where('store_id', $activeStoreId)
                ->where('status', 'active')
                ->first();
        }

        if (! $membership) {
            $membership = StoreMembership::with('store')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($membership) {
                session(['active_store_id' => $membership->store_id]);
            }
        }

        if ($membership && $membership->store) {
            $this->store = $membership->store;
            $this->membership = $membership;
        } elseif ($user->ownedStores()->exists()) {
            $this->store = $user->ownedStores()->first();
        }
    }
}
