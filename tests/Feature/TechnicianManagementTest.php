<?php

use App\Livewire\Technician\TechnicianIndex;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\TechnicianProfile;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('owner can view technicians index screen', function () {
    $owner = User::factory()->create();
    $store = Store::factory()->create(['owner_id' => $owner->id]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $owner->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    $response = $this->actingAs($owner)
        ->withSession(['active_store_id' => $store->id])
        ->get('/technicians');

    $response->assertStatus(200);
    $response->assertSee('Manajemen Teknisi');
});

test('owner can add a technician within plan limits', function () {
    $owner = User::factory()->create();
    $store = Store::factory()->create([
        'owner_id' => $owner->id,
        'plan' => 'business', // limit: 3 technicians
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $owner->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    $tenantContext = app(TenantContext::class);
    $tenantContext->setStore($store);

    Livewire::actingAs($owner)
        ->test(TechnicianIndex::class)
        ->set('name', 'Bambang Teknisi')
        ->set('email', 'bambang@teknisi.com')
        ->set('phone', '081234445555')
        ->set('password', 'password123')
        ->set('specialization', 'Hardware / IC')
        ->set('commission_type', 'percentage')
        ->set('commission_rate', 35.0)
        ->call('createTechnician')
        ->assertDispatched('toast');

    $user = User::where('email', 'bambang@teknisi.com')->first();
    expect($user)->not->toBeNull();

    $tech = TechnicianProfile::where('user_id', $user->id)->first();
    expect($tech)->not->toBeNull();
    expect($tech->store_id)->toBe($store->id);
    expect($tech->specialization)->toBe('Hardware / IC');
    expect((float) $tech->commission_rate)->toBe(35.0);

    // Test commission calculation helper
    expect($tech->calculateCommission(100000))->toBe(35000.0);
});

test('starter plan restricts adding more than 1 active technician', function () {
    $owner = User::factory()->create();
    $store = Store::factory()->create([
        'owner_id' => $owner->id,
        'plan' => 'starter', // limit: 1 technician
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $owner->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    $tenantContext = app(TenantContext::class);
    $tenantContext->setStore($store);

    // Add first technician
    $tech1User = User::factory()->create();
    $membership1 = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $tech1User->id,
        'role' => 'technician',
        'status' => 'active',
    ]);
    TechnicianProfile::create([
        'store_id' => $store->id,
        'user_id' => $tech1User->id,
        'membership_id' => $membership1->id,
        'specialization' => 'All-Round',
        'commission_type' => 'percentage',
        'commission_rate' => 20,
    ]);

    expect($store->canAddTechnician())->toBeFalse();

    // Attempting to add 2nd technician should trigger error and fail
    Livewire::actingAs($owner)
        ->test(TechnicianIndex::class)
        ->set('name', 'Teknisi Kedua')
        ->set('email', 'kedua@teknisi.com')
        ->set('phone', '0899999999')
        ->set('password', 'password123')
        ->set('specialization', 'Software')
        ->set('commission_type', 'fixed')
        ->set('commission_rate', 50000)
        ->call('createTechnician')
        ->assertHasErrors(['general']);

    expect(User::where('email', 'kedua@teknisi.com')->exists())->toBeFalse();
});

test('owner can toggle technician status and reset password', function () {
    $owner = User::factory()->create();
    $store = Store::factory()->create(['owner_id' => $owner->id]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $owner->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    $tenantContext = app(TenantContext::class);
    $tenantContext->setStore($store);

    $techUser = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);
    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $techUser->id,
        'role' => 'technician',
        'status' => 'active',
    ]);
    $tech = TechnicianProfile::create([
        'store_id' => $store->id,
        'user_id' => $techUser->id,
        'membership_id' => $membership->id,
        'specialization' => 'LCD',
        'commission_type' => 'percentage',
        'commission_rate' => 25,
    ]);

    Livewire::actingAs($owner)
        ->test(TechnicianIndex::class)
        ->call('toggleStatus', $tech->id)
        ->assertDispatched('toast');

    $membership->refresh();
    expect($membership->status)->toBe('inactive');

    // Reset password
    Livewire::actingAs($owner)
        ->test(TechnicianIndex::class)
        ->set('editingTechnicianId', $tech->id)
        ->set('new_password', 'brand-new-pass123')
        ->call('resetPassword')
        ->assertDispatched('toast');

    $techUser->refresh();
    expect(Hash::check('brand-new-pass123', $techUser->password))->toBeTrue();
});
