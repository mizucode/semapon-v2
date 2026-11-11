<?php

use App\Livewire\Subscription\SubscriptionManager;
use App\Models\Plan;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PlanSeeder::class);

    $this->owner = User::factory()->create();
    $this->store = Store::factory()->create([
        'name' => 'Klinik SaaS Subscriptions',
        'slug' => 'klinik-saas-subscriptions',
        'owner_id' => $this->owner->id,
        'plan' => 'starter',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => $this->owner->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    session(['active_store_id' => $this->store->id]);
    app(TenantContext::class)->setStore($this->store, $membership);
});

test('store owner can view subscription packages page', function () {
    $response = $this->actingAs($this->owner)
        ->withSession(['active_store_id' => $this->store->id])
        ->get(route('subscriptions.index'));

    $response->assertStatus(200);
    $response->assertSee('Starter');
    $response->assertSee('Business');
    $response->assertSee('Pro');
});

test('owner can generate invoice and upgrade plan to Business', function () {
    $this->actingAs($this->owner);
    session(['active_store_id' => $this->store->id]);

    $businessPlan = Plan::where('slug', 'business')->firstOrFail();

    // Generate invoice via Livewire
    Livewire::test(SubscriptionManager::class)
        ->call('selectPlan', $businessPlan->id)
        ->set('billingCycle', 'monthly')
        ->call('generateInvoice')
        ->assertHasNoErrors()
        ->assertDispatched('toast');

    $invoice = SubscriptionPayment::where('store_id', $this->store->id)
        ->where('plan_id', $businessPlan->id)
        ->first();

    expect($invoice)->not->toBeNull();
    expect($invoice->invoice_number)->toStartWith('INV-');
    expect((float) $invoice->amount)->toBe(199000.0);
    expect($invoice->status)->toBe('pending');

    // Confirm Payment
    $subscriptionService = app(SubscriptionService::class);
    $subscription = $subscriptionService->confirmPayment($invoice);

    expect($invoice->fresh()->status)->toBe('paid');
    expect($this->store->fresh()->plan)->toBe('business');
    expect($subscription->status)->toBe('active');
    expect($subscription->ends_at)->not->toBeNull();
});

test('plan limits correctly enforce technician maximums', function () {
    $subscriptionService = app(SubscriptionService::class);

    // Starter plan: 1 technician
    $this->store->update(['plan' => 'starter']);
    expect($subscriptionService->getMaxTechnicians($this->store))->toBe(1);

    // Business plan: 3 technicians
    $this->store->update(['plan' => 'business']);
    expect($subscriptionService->getMaxTechnicians($this->store))->toBe(3);

    // Pro plan: 10 technicians
    $this->store->update(['plan' => 'pro']);
    expect($subscriptionService->getMaxTechnicians($this->store))->toBe(10);
});

test('subscriptions and invoices are isolated between tenants', function () {
    $otherUser = User::factory()->create();
    $otherStore = Store::factory()->create([
        'name' => 'Toko HP Utara',
        'slug' => 'toko-hp-utara',
        'owner_id' => $otherUser->id,
        'plan' => 'starter',
    ]);
    StoreMembership::create([
        'store_id' => $otherStore->id,
        'user_id' => $otherUser->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    $proPlan = Plan::where('slug', 'pro')->first();
    SubscriptionPayment::create([
        'store_id' => $this->store->id,
        'plan_id' => $proPlan->id,
        'invoice_number' => 'INV-RAHASIA-A',
        'amount' => 399000,
        'billing_cycle' => 'monthly',
        'status' => 'paid',
    ]);

    // Access as user from Store B
    $response = $this->actingAs($otherUser)
        ->withSession(['active_store_id' => $otherStore->id])
        ->get(route('subscriptions.index'));

    $response->assertStatus(200);
    $response->assertDontSee('INV-RAHASIA-A');
});
