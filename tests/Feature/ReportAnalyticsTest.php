<?php

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Product;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\TechnicianProfile;
use App\Models\User;
use App\Services\ReportService;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->owner = User::factory()->create();
    $this->store = Store::factory()->create([
        'name' => 'Klinik Laporan Analisis',
        'slug' => 'klinik-laporan-analisis',
        'owner_id' => $this->owner->id,
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

test('owner can access report dashboard page', function () {
    $response = $this->actingAs($this->owner)
        ->withSession(['active_store_id' => $this->store->id])
        ->get(route('reports.index'));

    $response->assertStatus(200);
    $response->assertSee('Laporan');
});

test('service stats calculation accurately counts orders and revenue', function () {
    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Pelanggan 1',
        'phone' => '081234567890',
    ]);
    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'device_type' => 'smartphone',
        'brand' => 'Xiaomi',
        'model' => 'Redmi 9',
    ]);

    // Order 1: Completed, total 200.000
    $order1 = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'order_number' => 'ORD-202611-0101',
        'status' => 'completed',
        'complaints' => 'Ganti LCD',
        'total_cost' => 200000,
        'paid_amount' => 200000,
        'created_by' => $this->owner->id,
    ]);
    $order1->items()->create([
        'store_id' => $this->store->id,
        'name' => 'Ganti LCD Redmi 9',
        'price' => 200000,
    ]);

    // Order 2: In progress, total 150.000
    ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'order_number' => 'ORD-202611-0102',
        'status' => 'in_progress',
        'complaints' => 'Mati Total',
        'total_cost' => 150000,
        'paid_amount' => 50000,
        'created_by' => $this->owner->id,
    ]);

    // Order 3: Cancelled
    ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'order_number' => 'ORD-202611-0103',
        'status' => 'cancelled',
        'complaints' => 'Kemahalan',
        'total_cost' => 0,
        'paid_amount' => 0,
        'created_by' => $this->owner->id,
    ]);

    $reportService = app(ReportService::class);
    $stats = $reportService->getServiceStats($this->store);

    expect($stats['total_orders'])->toBe(3);
    expect($stats['completed_orders'])->toBe(1);
    expect($stats['in_progress_orders'])->toBe(1);
    expect($stats['cancelled_orders'])->toBe(1);
    expect($stats['total_revenue'])->toBe(200000.0);
    expect($stats['popular_services'])->toHaveCount(1);
});

test('technician stats computes commission correctly', function () {
    $techUser = User::factory()->create(['name' => 'Mas Doni']);
    $techMembership = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => $techUser->id,
        'role' => 'technician',
        'status' => 'active',
    ]);

    TechnicianProfile::create([
        'store_id' => $this->store->id,
        'user_id' => $techUser->id,
        'membership_id' => $techMembership->id,
        'specialization' => 'hardware',
        'commission_type' => 'percentage',
        'commission_rate' => 30.0, // 30% commission
    ]);

    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Pelanggan 2',
        'phone' => '081234567891',
    ]);
    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'device_type' => 'smartphone',
        'brand' => 'Oppo',
        'model' => 'A15',
    ]);

    // Order completed by Doni for 500.000 -> 30% of 500.000 = 150.000
    ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'technician_id' => $techUser->id,
        'order_number' => 'ORD-202611-0104',
        'status' => 'completed',
        'complaints' => 'Reball IC',
        'total_cost' => 500000,
        'paid_amount' => 500000,
        'created_by' => $this->owner->id,
    ]);

    $reportService = app(ReportService::class);
    $techStats = $reportService->getTechnicianStats($this->store);

    expect($techStats)->toHaveCount(1);
    expect($techStats[0]['name'])->toBe('Mas Doni');
    expect($techStats[0]['completed'])->toBe(1);
    expect((float) $techStats[0]['total_revenue'])->toBe(500000.0);
    expect((float) $techStats[0]['commission_earned'])->toBe(150000.0);
});

test('inventory stats computes total valuation accurately', function () {
    Product::create([
        'store_id' => $this->store->id,
        'name' => 'LCD Samsung M20',
        'buy_price' => 100000,
        'sell_price' => 200000,
        'stock' => 5, // 5 * 100.000 = 500.000
        'min_stock' => 2,
    ]);

    Product::create([
        'store_id' => $this->store->id,
        'name' => 'Baterai iPhone 7',
        'buy_price' => 50000,
        'sell_price' => 120000,
        'stock' => 10, // 10 * 50.000 = 500.000
        'min_stock' => 5,
    ]);

    $reportService = app(ReportService::class);
    $invStats = $reportService->getInventoryStats($this->store);

    expect($invStats['total_items'])->toBe(2);
    expect($invStats['total_units'])->toBe(15);
    expect((float) $invStats['total_valuation'])->toBe(1000000.0);
});

test('orders CSV export formats correct columns and data', function () {
    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Sari Indah',
        'phone' => '081299887766',
    ]);
    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'device_type' => 'smartphone',
        'brand' => 'Vivo',
        'model' => 'V21',
    ]);
    ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'order_number' => 'ORD-202611-0999',
        'status' => 'completed',
        'complaints' => 'Mati lampu',
        'total_cost' => 175000,
        'paid_amount' => 175000,
        'created_by' => $this->owner->id,
    ]);

    $reportService = app(ReportService::class);
    $csv = $reportService->exportOrdersCsv($this->store);

    expect($csv)->toContain('ORD-202611-0999');
    expect($csv)->toContain('Sari Indah');
    expect($csv)->toContain('Vivo V21');
    expect($csv)->toContain('completed');
});
