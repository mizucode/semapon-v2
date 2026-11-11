<?php

namespace App\Services;

use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreRegistrationService
{
    public function __construct(
        public TenantContext $tenantContext
    ) {}

    /**
     * @param  array{name: string, phone?: ?string, email?: ?string, address?: ?string, slug?: ?string}  $data
     */
    public function registerStore(User $owner, array $data): Store
    {
        return DB::transaction(function () use ($owner, $data) {
            $trialDays = (int) config('semapon.trial_days', 14);

            $slug = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
            $originalSlug = $slug;
            $counter = 1;

            while (Store::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$counter}";
                $counter++;
            }

            $store = Store::create([
                'owner_id' => $owner->id,
                'name' => $data['name'],
                'slug' => $slug,
                'phone' => $data['phone'] ?? $owner->phone,
                'email' => $data['email'] ?? $owner->email,
                'address' => $data['address'] ?? null,
                'status' => 'active',
                'plan' => 'starter',
                'trial_ends_at' => now()->addDays($trialDays),
                'settings' => [
                    'receipt_header' => $data['name'],
                    'receipt_footer' => 'Terima kasih atas kepercayaan Anda mempercayakan servis HP di konter kami.',
                    'default_warranty_days' => (int) config('semapon.default_warranty_days', 30),
                    'thermal_paper_width' => 58, // 58mm or 80mm
                ],
            ]);

            $membership = StoreMembership::create([
                'store_id' => $store->id,
                'user_id' => $owner->id,
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            session(['active_store_id' => $store->id]);
            $this->tenantContext->setStore($store, $membership);

            return $store;
        });
    }
}
