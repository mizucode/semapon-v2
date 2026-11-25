<?php

use App\Livewire\Inventory\ProductIndex;
use App\Livewire\Service\ServiceCatalog;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Product;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

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

test('technician is forbidden from accessing admin and owner routes', function () {
    $owner = User::factory()->create();
    $techUser = User::factory()->create();
    $store = Store::factory()->create(['owner_id' => $owner->id]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $techUser->id,
        'role' => 'technician',
        'status' => 'active',
    ]);

    $forbiddenRoutes = [
        '/customers',
        '/suppliers',
        '/purchases',
        '/finances',
        '/reports',
        '/technicians',
        '/settings/store',
        '/subscriptions',
        '/notifications',
        '/orders/create',
    ];

    foreach ($forbiddenRoutes as $uri) {
        $this->actingAs($techUser)
            ->withSession(['active_store_id' => $store->id])
            ->get($uri)
            ->assertStatus(403);
    }

    $allowedRoutes = [
        '/dashboard',
        '/orders',
        '/services',
        '/inventory',
        '/profile',
    ];

    foreach ($allowedRoutes as $uri) {
        $this->actingAs($techUser)
            ->withSession(['active_store_id' => $store->id])
            ->get($uri)
            ->assertStatus(200);
    }
});

test('technician only sees assigned orders and restricted menus and create buttons are hidden', function () {
    $owner = User::factory()->create();
    $techA = User::factory()->create(['name' => 'Teknisi Andi']);
    $techB = User::factory()->create(['name' => 'Teknisi Budi']);
    $store = Store::factory()->create(['owner_id' => $owner->id]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $techA->id,
        'role' => 'technician',
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $techB->id,
        'role' => 'technician',
        'status' => 'active',
    ]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Pelanggan Uji',
        'phone' => '081234567890',
        'whatsapp_number' => '6281234567890',
    ]);

    $device = CustomerDevice::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'device_type' => 'smartphone',
        'brand' => 'Samsung',
        'model' => 'Galaxy A54',
    ]);

    $orderTechA = ServiceOrder::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'technician_id' => $techA->id,
        'order_number' => 'ORD-TECH-A01',
        'status' => 'in_progress',
        'complaints' => 'Ganti LCD retak',
        'tracking_token' => 'token_tech_a_12345678901234567890',
        'created_by' => $owner->id,
    ]);

    $orderTechB = ServiceOrder::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'technician_id' => $techB->id,
        'order_number' => 'ORD-TECH-B02',
        'status' => 'diagnosing',
        'complaints' => 'Baterai drop',
        'tracking_token' => 'token_tech_b_12345678901234567890',
        'created_by' => $owner->id,
    ]);

    $unassignedOrder = ServiceOrder::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'technician_id' => null,
        'order_number' => 'ORD-OPEN-003',
        'status' => 'received',
        'complaints' => 'Port cas longgar',
        'tracking_token' => 'token_open_03_1234567890123456789',
        'created_by' => $owner->id,
    ]);

    // 1. Orders list (/orders)
    $ordersResponse = $this->actingAs($techA)
        ->withSession(['active_store_id' => $store->id])
        ->get('/orders');

    $ordersResponse->assertStatus(200)
        ->assertSee('ORD-TECH-A01')
        ->assertDontSee('ORD-TECH-B02')
        ->assertDontSee('ORD-OPEN-003')
        ->assertDontSee('Tambah Servis')
        ->assertDontSee('Semua Teknisi')
        ->assertSee('Katalog Layanan')
        ->assertSee('Produk & Sparepart', false)
        ->assertDontSee('Supplier & Pembelian', false)
        ->assertDontSee('Keuangan & Rekap', false)
        ->assertDontSee('Pengaturan Toko');

    // 2. Order Detail permissions
    $this->actingAs($techA)
        ->withSession(['active_store_id' => $store->id])
        ->get("/orders/{$orderTechA->id}")
        ->assertStatus(200)
        ->assertSee('ORD-TECH-A01')
        ->assertDontSee('Ganti Teknisi')
        ->assertDontSee('Hapus Order');

    $this->actingAs($techA)
        ->withSession(['active_store_id' => $store->id])
        ->get("/orders/{$orderTechB->id}")
        ->assertStatus(403);

    $this->actingAs($techA)
        ->withSession(['active_store_id' => $store->id])
        ->get("/orders/{$unassignedOrder->id}")
        ->assertStatus(403);

    $this->actingAs($techA)
        ->withSession(['active_store_id' => $store->id])
        ->get("/orders/{$orderTechA->id}/edit")
        ->assertStatus(403);

    // 3. Technician Dashboard (/dashboard)
    $dashboardResponse = $this->actingAs($techA)
        ->withSession(['active_store_id' => $store->id])
        ->get('/dashboard');

    $dashboardResponse->assertStatus(200)
        ->assertSee('Tugas Servis Saya')
        ->assertSee('Tugas Aktif')
        ->assertSee('Lihat Tugas Servis')
        ->assertSee('ORD-TECH-A01')
        ->assertDontSee('ORD-TECH-B02')
        ->assertDontSee('Pendapatan Hari Ini')
        ->assertDontSee('Order Servis Baru')
        ->assertDontSee('Peringatan Stok');
});

test('technician can view services and inventory in read-only mode without crud actions', function () {
    $owner = User::factory()->create();
    $techUser = User::factory()->create(['name' => 'Teknisi ReadOnly']);
    $store = Store::factory()->create(['owner_id' => $owner->id]);

    $techMembership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $techUser->id,
        'role' => 'technician',
        'status' => 'active',
    ]);

    app(TenantContext::class)->setStore($store, $techMembership);

    $service = Service::create([
        'store_id' => $store->id,
        'name' => 'Jasa Ganti Baterai Tanam',
        'price' => 75000,
        'estimated_duration_minutes' => 45,
        'warranty_days' => 14,
        'is_active' => true,
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Baterai Original iPhone 12',
        'sku' => 'BAT-IP12-ORI',
        'type' => 'sparepart',
        'buy_price' => 250000,
        'sell_price' => 450000,
        'stock' => 6,
        'min_stock' => 2,
        'unit' => 'pcs',
        'is_active' => true,
    ]);

    // 1. Read-only /services
    $this->actingAs($techUser)
        ->withSession(['active_store_id' => $store->id])
        ->get('/services')
        ->assertStatus(200)
        ->assertSee('Jasa Ganti Baterai Tanam')
        ->assertDontSee('Tambah Layanan')
        ->assertDontSee('Kelola Kategori')
        ->assertDontSee('Nonaktifkan');

    Livewire::actingAs($techUser)
        ->test(ServiceCatalog::class)
        ->call('openCreateServiceModal')
        ->assertForbidden();

    Livewire::actingAs($techUser)
        ->test(ServiceCatalog::class)
        ->call('editService', $service->id)
        ->assertForbidden();

    Livewire::actingAs($techUser)
        ->test(ServiceCatalog::class)
        ->call('toggleServiceStatus', $service->id)
        ->assertForbidden();

    // 2. Read-only /inventory
    $this->actingAs($techUser)
        ->withSession(['active_store_id' => $store->id])
        ->get('/inventory')
        ->assertStatus(200)
        ->assertSee('Baterai Original iPhone 12')
        ->assertDontSee('Tambah Barang Baru')
        ->assertDontSee('Atur Stok')
        ->assertDontSee('Harga Modal');

    Livewire::actingAs($techUser)
        ->test(ProductIndex::class)
        ->call('openCreateModal')
        ->assertForbidden();

    Livewire::actingAs($techUser)
        ->test(ProductIndex::class)
        ->call('openEditModal', $product->id)
        ->assertForbidden();

    Livewire::actingAs($techUser)
        ->test(ProductIndex::class)
        ->call('openStockAdjustModal', $product->id)
        ->assertForbidden();
});
