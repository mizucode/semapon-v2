<?php

use App\Livewire\Dashboard\TenantDashboard;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\TechnicianProfile;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->store = Store::factory()->create([
        'owner_id' => $this->user->id,
        'status' => 'active',
    ]);

    $this->membership = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'role' => 'owner',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    $this->actingAs($this->user);
    session(['current_store_id' => $this->store->id]);
    app(TenantContext::class)->setStore($this->store, $this->membership);
});

test('tenant dashboard renders successfully with kpi cards and quick actions', function () {
    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Budi Santoso',
        'phone' => '081234567890',
    ]);

    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'brand' => 'Samsung',
        'model' => 'Galaxy S23 Ultra',
        'type' => 'smartphone',
    ]);

    ServiceOrder::create([
        'store_id' => $this->store->id,
        'order_number' => 'SO-20261109-0001',
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'status' => 'in_progress',
        'complaints' => 'Layar bergaris dan pecah',
        'created_by' => $this->user->id,
    ]);

    Product::create([
        'store_id' => $this->store->id,
        'name' => 'LCD Samsung S23 Original',
        'sku' => 'LCD-S23-01',
        'stock' => 1,
        'min_stock' => 3,
        'buy_price' => 1200000,
        'sell_price' => 1800000,
        'type' => 'sparepart',
        'is_active' => true,
    ]);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee('Selamat Pagi');
    $response->assertSee($this->store->name);
    $response->assertSee('Sedang Dikerjakan');
    $response->assertSee('Siap Diambil');
    $response->assertSee('Pendapatan Hari Ini');
    $response->assertSee('SO-20261109-0001');
    $response->assertSee('Galaxy S23 Ultra');
    $response->assertSee('LCD Samsung S23 Original');
});

test('tenant dashboard reflects completed services and revenue accurately', function () {
    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Dewi Lestari',
        'phone' => '081987654321',
    ]);

    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'brand' => 'Apple',
        'model' => 'iPhone 14 Pro',
        'type' => 'smartphone',
    ]);

    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'order_number' => 'SO-20261109-0002',
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'status' => 'completed',
        'complaints' => 'Baterai boros dan drop',
        'completed_at' => now(),
        'total_cost' => 500000,
        'created_by' => $this->user->id,
    ]);

    Payment::create([
        'store_id' => $this->store->id,
        'service_order_id' => $order->id,
        'customer_id' => $customer->id,
        'payment_number' => 'PAY-20261109-0001',
        'amount' => 500000,
        'payment_method' => 'cash',
        'status' => 'success',
        'user_id' => $this->user->id,
    ]);

    Livewire::test(TenantDashboard::class)
        ->assertSee('Rp 500.000')
        ->assertSee('SO-20261109-0002');
});

test('dashboard hides revenue for salaried technician and shows commission from completed orders for commission technician', function () {
    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Rina Pelanggan',
        'phone' => '081298765432',
    ]);

    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'brand' => 'Xiaomi',
        'model' => 'Poco X5',
        'type' => 'smartphone',
    ]);

    // 1. Salaried Employee Technician (Karyawan Gaji)
    $salariedUser = User::factory()->create(['name' => 'Riko Karyawan']);
    $salariedMembership = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => $salariedUser->id,
        'role' => 'technician',
        'status' => 'active',
        'joined_at' => now(),
    ]);
    TechnicianProfile::create([
        'store_id' => $this->store->id,
        'user_id' => $salariedUser->id,
        'membership_id' => $salariedMembership->id,
        'specialization' => 'All-Round',
        'commission_type' => 'salary',
        'commission_rate' => 0,
    ]);

    ServiceOrder::create([
        'store_id' => $this->store->id,
        'order_number' => 'SO-SALARIED-01',
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'technician_id' => $salariedUser->id,
        'status' => 'completed',
        'complaints' => 'Ganti konektor cas',
        'completed_at' => now(),
        'total_cost' => 300000,
        'created_by' => $this->user->id,
    ]);

    app(TenantContext::class)->setStore($this->store, $salariedMembership);
    $this->actingAs($salariedUser);

    Livewire::test(TenantDashboard::class)
        ->assertSee('Tugas Aktif')
        ->assertSee('Perlu Tindakan')
        ->assertDontSee('Pendapatan Hari Ini')
        ->assertDontSee('Pendapatan Komisi');

    // 2. Commission Technician (Komisi 20%)
    $commUser = User::factory()->create(['name' => 'Bayu Komisi']);
    $commMembership = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => $commUser->id,
        'role' => 'technician',
        'status' => 'active',
        'joined_at' => now(),
    ]);
    TechnicianProfile::create([
        'store_id' => $this->store->id,
        'user_id' => $commUser->id,
        'membership_id' => $commMembership->id,
        'specialization' => 'Hardware / IC',
        'commission_type' => 'percentage',
        'commission_rate' => 20,
    ]);

    // Completed order: 400.000 -> 20% commission = 80.000
    ServiceOrder::create([
        'store_id' => $this->store->id,
        'order_number' => 'SO-COMM-DONE',
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'technician_id' => $commUser->id,
        'status' => 'completed',
        'complaints' => 'Servis IC sinyal',
        'completed_at' => now(),
        'total_cost' => 400000,
        'created_by' => $this->user->id,
    ]);

    // In-progress order: 500.000 -> should NOT count toward completed commission or owner completed revenue
    ServiceOrder::create([
        'store_id' => $this->store->id,
        'order_number' => 'SO-COMM-PROGRESS',
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'technician_id' => $commUser->id,
        'status' => 'in_progress',
        'complaints' => 'Ganti LCD',
        'total_cost' => 500000,
        'created_by' => $this->user->id,
    ]);

    app(TenantContext::class)->setStore($this->store, $commMembership);
    $this->actingAs($commUser);

    Livewire::test(TenantDashboard::class)
        ->assertSee('Pendapatan Komisi')
        ->assertSee('Rp 80.000')
        ->assertDontSee('Pendapatan Hari Ini');

    // 3. Owner & Admin see store revenue only from completed orders today (300.000 + 400.000 = 700.000)
    $adminUser = User::factory()->create(['name' => 'Siti Admin']);
    $adminMembership = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => $adminUser->id,
        'role' => 'admin',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    app(TenantContext::class)->setStore($this->store, $adminMembership);
    $this->actingAs($adminUser);

    Livewire::test(TenantDashboard::class)
        ->assertSee('Pendapatan Hari Ini')
        ->assertSee('Rp 700.000')
        ->assertSee('dari 2 servis selesai hari ini');
});
