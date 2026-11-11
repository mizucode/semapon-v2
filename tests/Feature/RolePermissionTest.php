<?php

use App\Models\Role;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('roles and permissions are seeded correctly', function () {
    expect(Role::where('slug', 'owner')->exists())->toBeTrue();
    expect(Role::where('slug', 'admin')->exists())->toBeTrue();
    expect(Role::where('slug', 'technician')->exists())->toBeTrue();
    expect(Role::where('slug', 'super_admin')->exists())->toBeTrue();

    $ownerRole = Role::where('slug', 'owner')->first();
    expect($ownerRole->hasPermission('orders.view'))->toBeTrue();
    expect($ownerRole->hasPermission('finances.view'))->toBeTrue();
    expect($ownerRole->hasPermission('store.settings'))->toBeTrue();

    $techRole = Role::where('slug', 'technician')->first();
    expect($techRole->hasPermission('orders.view'))->toBeTrue();
    expect($techRole->hasPermission('orders.status.update'))->toBeTrue();
    expect($techRole->hasPermission('finances.view'))->toBeFalse();
    expect($techRole->hasPermission('store.settings'))->toBeFalse();
});

test('owner can access store settings route', function () {
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
        ->get('/settings/store');

    $response->assertStatus(200);
});

test('technician is forbidden from accessing store settings route', function () {
    $owner = User::factory()->create();
    $techUser = User::factory()->create();
    $store = Store::factory()->create(['owner_id' => $owner->id]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $techUser->id,
        'role' => 'technician',
        'status' => 'active',
    ]);

    $response = $this->actingAs($techUser)
        ->withSession(['active_store_id' => $store->id])
        ->get('/settings/store');

    $response->assertStatus(403);
});

test('admin is forbidden from accessing store settings route', function () {
    $owner = User::factory()->create();
    $adminUser = User::factory()->create();
    $store = Store::factory()->create(['owner_id' => $owner->id]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $adminUser->id,
        'role' => 'admin',
        'status' => 'active',
    ]);

    $response = $this->actingAs($adminUser)
        ->withSession(['active_store_id' => $store->id])
        ->get('/settings/store');

    $response->assertStatus(403);
});

test('authorization gates evaluate correctly for different roles', function () {
    $owner = User::factory()->create();
    $techUser = User::factory()->create();
    $store = Store::factory()->create(['owner_id' => $owner->id]);

    $ownerMembership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $owner->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    $techMembership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $techUser->id,
        'role' => 'technician',
        'status' => 'active',
    ]);

    $tenantContext = app(TenantContext::class);

    // Context: Owner
    $tenantContext->setStore($store, $ownerMembership);
    expect(Gate::forUser($owner)->allows('owner'))->toBeTrue();
    expect(Gate::forUser($owner)->allows('manage-finances'))->toBeTrue();
    expect(Gate::forUser($owner)->allows('manage-store'))->toBeTrue();

    // Context: Technician
    $tenantContext->setStore($store, $techMembership);
    expect(Gate::forUser($techUser)->allows('owner'))->toBeFalse();
    expect(Gate::forUser($techUser)->allows('technician'))->toBeTrue();
    expect(Gate::forUser($techUser)->allows('manage-finances'))->toBeFalse();
    expect(Gate::forUser($techUser)->allows('manage-store'))->toBeFalse();
});
