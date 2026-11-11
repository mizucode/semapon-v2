<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Exception;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Create subscription invoice for a store to upgrade/renew a plan.
     */
    public function createInvoice(
        Store $store,
        Plan $plan,
        string $billingCycle = 'monthly',
        string $method = 'transfer'
    ): SubscriptionPayment {
        $amount = $billingCycle === 'yearly'
            ? (float) $plan->price_yearly
            : (float) $plan->price_monthly;

        $currentSubscription = Subscription::where('store_id', $store->id)
            ->latest('id')
            ->first();

        return SubscriptionPayment::create([
            'store_id' => $store->id,
            'subscription_id' => $currentSubscription?->id,
            'plan_id' => $plan->id,
            'amount' => $amount,
            'billing_cycle' => $billingCycle,
            'payment_method' => $method,
            'status' => 'pending',
            'notes' => "Langganan Paket {$plan->name} ({$billingCycle})",
        ]);
    }

    /**
     * Confirm payment approval (by admin or gateway webhook) and activate subscription.
     */
    public function confirmPayment(SubscriptionPayment $invoice): Subscription
    {
        if ($invoice->status === 'paid') {
            throw new Exception('Invoice ini sudah pernah dibayar.');
        }

        return DB::transaction(function () use ($invoice) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $plan = Plan::findOrFail($invoice->plan_id);
            $store = Store::withoutGlobalScopes()->findOrFail($invoice->store_id);

            $startsAt = now();
            $endsAt = $invoice->billing_cycle === 'yearly'
                ? now()->addYear()
                : now()->addMonth();

            // Create or update store subscription
            $subscription = Subscription::create([
                'store_id' => $store->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);

            $invoice->update(['subscription_id' => $subscription->id]);

            // Update store plan
            $store->update(['plan' => $plan->slug]);

            return $subscription;
        });
    }

    /**
     * Get maximum allowed technicians for the store based on active plan.
     */
    public function getMaxTechnicians(Store $store): int
    {
        $plan = Plan::where('slug', $store->plan)->first();

        return $plan ? $plan->max_technicians : 1;
    }
}
