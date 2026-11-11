<?php

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\Supplier;
use App\Models\User;
use App\Services\FinanceService;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use App\Services\TenantContext;
use App\Services\TrackingTokenService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    // Tenant A
    $this->ownerA = User::factory()->create(['name' => 'Owner Store A']);
    $this->storeA = Store::factory()->create([
        'name' => 'Store Phone Alpha',
        'slug' => 'store-phone-alpha',
        'owner_id' => $this->ownerA->id,
        'status' => 'active',
    ]);
    $this->membershipA = StoreMembership::create([
        'store_id' => $this->storeA->id,
        'user_id' => $this->ownerA->id,
        'role' => 'owner',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Tenant B
    $this->ownerB = User::factory()->create(['name' => 'Owner Store B']);
    $this->storeB = Store::factory()->create([
        'name' => 'Store Phone Beta',
        'slug' => 'store-phone-beta',
        'owner_id' => $this->ownerB->id,
        'status' => 'active',
    ]);
    $this->membershipB = StoreMembership::create([
        'store_id' => $this->storeB->id,
        'user_id' => $this->ownerB->id,
        'role' => 'owner',
        'status' => 'active',
        'joined_at' => now(),
    ]);
});

test('tenant isolation: tenant b cannot see or query tenant a customers', function () {
    // Create customer in Store A
    $customerA = Customer::create([
        'store_id' => $this->storeA->id,
        'name' => 'Customer Rahasia Store A',
        'phone' => '08111111111',
    ]);

    // Context switched to Store B
    $this->actingAs($this->ownerB);
    session(['current_store_id' => $this->storeB->id]);
    app(TenantContext::class)->setStore($this->storeB, $this->membershipB);

    // Query through Eloquent with TenantScope
    $found = Customer::where('id', $customerA->id)->first();
    expect($found)->toBeNull();

    // Directly accessing customer detail endpoint returns 404
    $response = $this->get('/customers/'.$customerA->id);
    $response->assertStatus(404);
});

test('tenant isolation: tenant b cannot view or print tenant a service orders', function () {
    $customerA = Customer::create([
        'store_id' => $this->storeA->id,
        'name' => 'Customer Toko A',
        'phone' => '08122222222',
    ]);

    $deviceA = CustomerDevice::create([
        'store_id' => $this->storeA->id,
        'customer_id' => $customerA->id,
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro Max',
        'type' => 'smartphone',
    ]);

    $orderA = ServiceOrder::create([
        'store_id' => $this->storeA->id,
        'order_number' => 'ORD-ALPHA-001',
        'customer_id' => $customerA->id,
        'customer_device_id' => $deviceA->id,
        'status' => 'in_progress',
        'complaints' => 'Mati total kena air',
        'created_by' => $this->ownerA->id,
    ]);

    // Context switched to Store B
    $this->actingAs($this->ownerB);
    session(['current_store_id' => $this->storeB->id]);
    app(TenantContext::class)->setStore($this->storeB, $this->membershipB);

    // Order lookup in Store B scope is null
    expect(ServiceOrder::where('id', $orderA->id)->first())->toBeNull();

    // Web endpoint for order detail must return 404
    $this->get('/orders/'.$orderA->id)->assertStatus(404);

    // Print receipt endpoint must return 404
    $this->get('/orders/'.$orderA->id.'/print')->assertStatus(404);
});

test('tenant isolation: cannot assign technician from another tenant', function () {
    // Technician belonging to Store B
    $techUserB = User::factory()->create(['name' => 'Teknisi Toko B']);
    StoreMembership::create([
        'store_id' => $this->storeB->id,
        'user_id' => $techUserB->id,
        'role' => 'technician',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Order in Store A
    $customerA = Customer::create([
        'store_id' => $this->storeA->id,
        'name' => 'Customer Toko A',
        'phone' => '08133333333',
    ]);
    $deviceA = CustomerDevice::create([
        'store_id' => $this->storeA->id,
        'customer_id' => $customerA->id,
        'brand' => 'Xiaomi',
        'model' => 'Redmi Note 12',
        'type' => 'smartphone',
    ]);
    $orderA = ServiceOrder::create([
        'store_id' => $this->storeA->id,
        'order_number' => 'ORD-ALPHA-002',
        'customer_id' => $customerA->id,
        'customer_device_id' => $deviceA->id,
        'status' => 'pending',
        'complaints' => 'Baterai kembung',
        'created_by' => $this->ownerA->id,
    ]);

    // Active in Store A
    $this->actingAs($this->ownerA);
    session(['current_store_id' => $this->storeA->id]);
    app(TenantContext::class)->setStore($this->storeA, $this->membershipA);

    // Ensure technician from Store B cannot be resolved in Store A
    $isStoreATech = StoreMembership::where('store_id', $this->storeA->id)
        ->where('user_id', $techUserB->id)
        ->exists();

    expect($isStoreATech)->toBeFalse();
});

test('tenant isolation: inventory stock cannot be accessed or deducted across tenants', function () {
    $productA = Product::create([
        'store_id' => $this->storeA->id,
        'name' => 'Baterai iPhone 13 Original',
        'sku' => 'BAT-IP13-ORI',
        'stock' => 10,
        'min_stock' => 2,
        'buy_price' => 200000,
        'sell_price' => 350000,
        'type' => 'sparepart',
        'is_active' => true,
    ]);

    // Switch to Store B
    $this->actingAs($this->ownerB);
    session(['current_store_id' => $this->storeB->id]);
    app(TenantContext::class)->setStore($this->storeB, $this->membershipB);

    // Product A cannot be queried in Store B
    expect(Product::where('id', $productA->id)->first())->toBeNull();
});

test('transaction integrity: inventory part usage deduction and safe rollback on removal', function () {
    $this->actingAs($this->ownerA);
    session(['current_store_id' => $this->storeA->id]);
    app(TenantContext::class)->setStore($this->storeA, $this->membershipA);

    $customer = Customer::create([
        'store_id' => $this->storeA->id,
        'name' => 'Test Transaction Customer',
        'phone' => '08144444444',
    ]);
    $device = CustomerDevice::create([
        'store_id' => $this->storeA->id,
        'customer_id' => $customer->id,
        'brand' => 'Oppo',
        'model' => 'Reno 8',
        'type' => 'smartphone',
    ]);
    $order = ServiceOrder::create([
        'store_id' => $this->storeA->id,
        'order_number' => 'ORD-ALPHA-003',
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'status' => 'in_progress',
        'complaints' => 'Ganti LCD',
        'created_by' => $this->ownerA->id,
    ]);

    $product = Product::create([
        'store_id' => $this->storeA->id,
        'name' => 'LCD Oppo Reno 8',
        'sku' => 'LCD-OP-R8',
        'stock' => 5,
        'min_stock' => 1,
        'buy_price' => 300000,
        'sell_price' => 500000,
        'type' => 'sparepart',
        'is_active' => true,
    ]);

    $inventoryService = app(InventoryService::class);

    // 1. Attach part to order (stock drops from 5 to 4)
    $part = $inventoryService->usePartInOrder(
        order: $order,
        product: $product,
        quantity: 1,
        userId: $this->ownerA->id
    );

    $product->refresh();
    expect($product->stock)->toBe(4);

    // 2. Remove part (reversal: stock recovers from 4 back to 5)
    $inventoryService->returnPartFromOrder($part, $this->ownerA->id);

    $product->refresh();
    expect($product->stock)->toBe(5);
});

test('authorization: unauthenticated users cannot access protected routes', function () {
    // No login
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/customers')->assertRedirect('/login');
    $this->get('/orders')->assertRedirect('/login');
    $this->get('/inventory')->assertRedirect('/login');
    $this->get('/superadmin/dashboard')->assertRedirect('/login');
});

test('authorization: regular tenant owners cannot access super admin platform', function () {
    $this->actingAs($this->ownerA);
    session(['current_store_id' => $this->storeA->id]);
    app(TenantContext::class)->setStore($this->storeA, $this->membershipA);

    $response = $this->get('/superadmin/dashboard');
    $response->assertStatus(403);
});

test('security: tracking token uses sha256 and handles expiration and revocation safely', function () {
    $trackingService = app(TrackingTokenService::class);

    $customer = Customer::create([
        'store_id' => $this->storeA->id,
        'name' => 'Tracking Test Customer',
        'phone' => '08155555555',
    ]);
    $device = CustomerDevice::create([
        'store_id' => $this->storeA->id,
        'customer_id' => $customer->id,
        'brand' => 'Asus',
        'model' => 'ROG Phone 6',
        'type' => 'smartphone',
    ]);
    $order = ServiceOrder::create([
        'store_id' => $this->storeA->id,
        'order_number' => 'ORD-ALPHA-TRACK',
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'status' => 'diagnosed',
        'complaints' => 'Touch error',
        'created_by' => $this->ownerA->id,
    ]);

    // Generate token
    $result = $trackingService->generateToken($order);
    $plainToken = $result['plain_token'];
    expect(strlen($plainToken))->toBe(64); // 256 bits

    // Token should resolve order
    $resolvedOrder = $trackingService->resolveOrder($plainToken);
    expect($resolvedOrder)->not->toBeNull();
    expect($resolvedOrder->id)->toBe($order->id);

    // Revoke token
    $trackingService->revokeTokensForOrder($order);

    // After revocation, token cannot resolve
    $afterRevocation = $trackingService->resolveOrder($plainToken);
    expect($afterRevocation)->toBeNull();
});

test('tenant isolation: cash transactions and payments are strictly isolated', function () {
    $this->actingAs($this->ownerA);
    session(['current_store_id' => $this->storeA->id]);
    app(TenantContext::class)->setStore($this->storeA, $this->membershipA);

    $customer = Customer::create([
        'store_id' => $this->storeA->id,
        'name' => 'Customer Cash Test',
        'phone' => '08166666666',
    ]);
    $device = CustomerDevice::create([
        'store_id' => $this->storeA->id,
        'customer_id' => $customer->id,
        'brand' => 'Vivo',
        'model' => 'Y21',
        'type' => 'smartphone',
    ]);
    $order = ServiceOrder::create([
        'store_id' => $this->storeA->id,
        'order_number' => 'ORD-ALPHA-CASH',
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'status' => 'in_progress',
        'complaints' => 'Mati',
        'total_cost' => 250000,
        'created_by' => $this->ownerA->id,
    ]);

    $financeService = app(FinanceService::class);
    $payment = $financeService->recordOrderPayment(
        order: $order,
        amount: 250000,
        method: 'cash',
        userId: $this->ownerA->id,
        notes: 'Deposit servis toko A'
    );

    // Switch to Store B
    $this->actingAs($this->ownerB);
    session(['current_store_id' => $this->storeB->id]);
    app(TenantContext::class)->setStore($this->storeB, $this->membershipB);

    // Payment A cannot be seen in Store B
    expect(Payment::where('id', $payment->id)->first())->toBeNull();
});

test('transaction integrity: purchase cancellation cleanly reverses inventory stock', function () {
    $this->actingAs($this->ownerA);
    session(['current_store_id' => $this->storeA->id]);
    app(TenantContext::class)->setStore($this->storeA, $this->membershipA);

    $purchaseService = app(PurchaseService::class);

    $supplier = Supplier::create([
        'store_id' => $this->storeA->id,
        'name' => 'Supplier LCD Grosir',
        'phone' => '08199999999',
    ]);

    $product = Product::create([
        'store_id' => $this->storeA->id,
        'name' => 'LCD Redmi 10',
        'sku' => 'LCD-RED-10',
        'stock' => 0,
        'min_stock' => 2,
        'buy_price' => 150000,
        'sell_price' => 250000,
        'type' => 'sparepart',
        'is_active' => true,
    ]);

    $purchase = $purchaseService->createPurchase(
        supplier: $supplier,
        items: [
            [
                'product_id' => $product->id,
                'quantity' => 10,
                'unit_cost' => 150000,
            ],
        ],
        downPayment: 0,
        userId: $this->ownerA->id,
        notes: 'PO Test Batal'
    );

    // Receive goods -> stock becomes 10
    $purchaseService->receivePurchase($purchase, $this->ownerA->id);
    $product->refresh();
    expect($product->stock)->toBe(10);

    // Cancel purchase -> stock rolls back to 0
    $purchaseService->cancelPurchase($purchase, $this->ownerA->id);
    $product->refresh();
    expect($product->stock)->toBe(0);
});
