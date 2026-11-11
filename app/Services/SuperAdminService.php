<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;

class SuperAdminService
{
    /**
     * Get platform-wide aggregated metrics without leaking tenant data.
     */
    public function getPlatformOverview(): array
    {
        $totalStores = Store::withoutGlobalScopes()->count();
        $activeStores = Store::withoutGlobalScopes()->where('status', 'active')->count();
        $suspendedStores = Store::withoutGlobalScopes()->where('status', 'suspended')->count();

        $totalUsers = User::count();
        $totalOrders = ServiceOrder::withoutGlobalScopes()->count();
        $totalSubscriptions = Subscription::withoutGlobalScopes()->where('status', 'active')->count();

        // Estimated MRR calculation
        $plans = Plan::all()->keyBy('slug');
        $stores = Store::withoutGlobalScopes()->where('status', 'active')->get();
        $mrr = 0;
        foreach ($stores as $store) {
            if (isset($plans[$store->plan])) {
                $mrr += (float) $plans[$store->plan]->price_monthly;
            }
        }

        return [
            'total_tenants' => $totalStores,
            'active_tenants' => $activeStores,
            'suspended_tenants' => $suspendedStores,
            'total_users' => $totalUsers,
            'total_orders' => $totalOrders,
            'total_subscriptions' => $totalSubscriptions,
            'mrr' => $mrr,
        ];
    }

    /**
     * Suspend a tenant store (restricts access).
     */
    public function suspendStore(Store $store, ?string $reason = null): void
    {
        $settings = $store->settings ?? [];
        if ($reason) {
            $settings['suspension_reason'] = $reason;
            $settings['suspended_at'] = now()->toIso8601String();
        }

        $store->update([
            'status' => 'suspended',
            'settings' => $settings,
        ]);
    }

    /**
     * Reactivate a suspended tenant store.
     */
    public function reactivateStore(Store $store): void
    {
        $settings = $store->settings ?? [];
        unset($settings['suspension_reason']);
        $settings['reactivated_at'] = now()->toIso8601String();

        $store->update([
            'status' => 'active',
            'settings' => $settings,
        ]);
    }
}
