<?php

use App\Livewire\Customer\CustomerDetail;
use App\Livewire\Customer\CustomerIndex;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('customer index screen can be rendered by store owner', function () {
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
        ->get('/customers');

    $response->assertStatus(200);
    $response->assertSee('Data Pelanggan');
});

test('owner can create a new customer via livewire', function () {
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

    Livewire::actingAs($owner)
        ->test(CustomerIndex::class)
        ->set('name', 'Hendra Wijaya')
        ->set('phone', '081298765432')
        ->set('email', 'hendra@example.com')
        ->set('address', 'Jl. Sudirman No. 45')
        ->call('createCustomer')
        ->assertDispatched('toast');

    $customer = Customer::where('phone', '081298765432')->first();
    expect($customer)->not->toBeNull();
    expect($customer->name)->toBe('Hendra Wijaya');
    expect($customer->store_id)->toBe($store->id);
    expect($customer->formatted_phone)->toBe('6281298765432');
});

test('owner can search customers by name or phone', function () {
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

    Customer::create([
        'store_id' => $store->id,
        'name' => 'Agus Pratama',
        'phone' => '081211112222',
    ]);

    Customer::create([
        'store_id' => $store->id,
        'name' => 'Dewi Lestari',
        'phone' => '085733334444',
    ]);

    Livewire::actingAs($owner)
        ->test(CustomerIndex::class)
        ->set('search', 'Agus')
        ->assertSee('Agus Pratama')
        ->assertDontSee('Dewi Lestari')
        ->set('search', '08573333')
        ->assertSee('Dewi Lestari')
        ->assertDontSee('Agus Pratama');
});

test('customer detail screen renders and permits adding devices', function () {
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

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Rina Marlina',
        'phone' => '081344445555',
    ]);

    Livewire::actingAs($owner)
        ->test(CustomerDetail::class, ['id' => $customer->id])
        ->assertSee('Rina Marlina')
        ->set('brand', 'Samsung')
        ->set('model', 'Galaxy S22 Ultra')
        ->set('color', 'Phantom Black')
        ->set('imei', '356789012345678')
        ->set('passcode', 'PIN 1234')
        ->call('addDevice')
        ->assertDispatched('toast');

    $device = CustomerDevice::where('customer_id', $customer->id)->first();
    expect($device)->not->toBeNull();
    expect($device->brand)->toBe('Samsung');
    expect($device->model)->toBe('Galaxy S22 Ultra');
    expect($device->device_name)->toBe('Samsung Galaxy S22 Ultra');
    expect($device->store_id)->toBe($store->id);
});

test('customers are strictly isolated between stores', function () {
    $ownerA = User::factory()->create();
    $ownerB = User::factory()->create();

    $storeA = Store::factory()->create(['owner_id' => $ownerA->id]);
    $storeB = Store::factory()->create(['owner_id' => $ownerB->id]);

    $tenantContext = app(TenantContext::class);

    // Customer in Store A
    $tenantContext->setStore($storeA);
    Customer::create([
        'store_id' => $storeA->id,
        'name' => 'Pelanggan Toko A',
        'phone' => '0811111111',
    ]);

    // Customer in Store B
    $tenantContext->setStore($storeB);
    Customer::create([
        'store_id' => $storeB->id,
        'name' => 'Pelanggan Toko B',
        'phone' => '0822222222',
    ]);

    // Query under Store A context
    $tenantContext->setStore($storeA);
    $customersA = Customer::all();
    expect($customersA)->toHaveCount(1);
    expect($customersA->first()->name)->toBe('Pelanggan Toko A');

    // Query under Store B context
    $tenantContext->setStore($storeB);
    $customersB = Customer::all();
    expect($customersB)->toHaveCount(1);
    expect($customersB->first()->name)->toBe('Pelanggan Toko B');
});
