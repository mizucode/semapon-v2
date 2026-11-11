<?php

use App\Livewire\Service\ServiceCatalog;
use App\Models\Service;
use App\Models\ServiceCategory;
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

test('service catalog screen can be rendered by store user', function () {
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
        ->get('/services');

    $response->assertStatus(200);
    $response->assertSee('Katalog Jasa Servis');
});

test('user can create category and service in catalog', function () {
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

    $component = Livewire::actingAs($owner)->test(ServiceCatalog::class);

    // 1. Add Category
    $component->set('new_category_name', 'Ganti Kaca & LCD')
        ->call('createCategory')
        ->assertDispatched('toast');

    $category = ServiceCategory::where('name', 'Ganti Kaca & LCD')->first();
    expect($category)->not->toBeNull();
    expect($category->store_id)->toBe($store->id);

    // 2. Add Service under Category
    $component->set('category_id', $category->id)
        ->set('name', 'Jasa Ganti LCD OLED')
        ->set('price', 150000)
        ->set('estimated_duration_minutes', 90)
        ->set('warranty_days', 45)
        ->set('is_active', true)
        ->call('saveService')
        ->assertDispatched('toast');

    $service = Service::where('name', 'Jasa Ganti LCD OLED')->first();
    expect($service)->not->toBeNull();
    expect($service->store_id)->toBe($store->id);
    expect($service->category_id)->toBe($category->id);
    expect((float) $service->price)->toBe(150000.0);
    expect($service->warranty_days)->toBe(45);
});

test('user can search services and toggle active status', function () {
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

    $service1 = Service::create([
        'store_id' => $store->id,
        'name' => 'Bypass Akun Google FRP',
        'price' => 100000,
        'estimated_duration_minutes' => 30,
        'warranty_days' => 7,
        'is_active' => true,
    ]);

    $service2 = Service::create([
        'store_id' => $store->id,
        'name' => 'Ganti Konektor Charger',
        'price' => 75000,
        'estimated_duration_minutes' => 45,
        'warranty_days' => 14,
        'is_active' => true,
    ]);

    Livewire::actingAs($owner)
        ->test(ServiceCatalog::class)
        ->set('search', 'Bypass')
        ->assertSee('Bypass Akun Google FRP')
        ->assertDontSee('Ganti Konektor Charger')
        ->call('toggleServiceStatus', $service1->id)
        ->assertDispatched('toast');

    $service1->refresh();
    expect($service1->is_active)->toBeFalse();
});

test('services are strictly isolated per tenant', function () {
    $ownerA = User::factory()->create();
    $ownerB = User::factory()->create();

    $storeA = Store::factory()->create(['owner_id' => $ownerA->id]);
    $storeB = Store::factory()->create(['owner_id' => $ownerB->id]);

    $tenantContext = app(TenantContext::class);

    // Store A service
    $tenantContext->setStore($storeA);
    Service::create([
        'store_id' => $storeA->id,
        'name' => 'Servis Toko A',
        'price' => 50000,
    ]);

    // Store B service
    $tenantContext->setStore($storeB);
    Service::create([
        'store_id' => $storeB->id,
        'name' => 'Servis Toko B',
        'price' => 70000,
    ]);

    // Query under Store A context
    $tenantContext->setStore($storeA);
    $servicesA = Service::all();
    expect($servicesA)->toHaveCount(1);
    expect($servicesA->first()->name)->toBe('Servis Toko A');

    // Query under Store B context
    $tenantContext->setStore($storeB);
    $servicesB = Service::all();
    expect($servicesB)->toHaveCount(1);
    expect($servicesB->first()->name)->toBe('Servis Toko B');
});
