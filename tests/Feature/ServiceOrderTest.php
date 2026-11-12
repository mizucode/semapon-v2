<?php

use App\Livewire\Order\CreateOrder;
use App\Livewire\Order\OrderDetail;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->ownerUser = User::factory()->create();
    $this->store = Store::factory()->create([
        'name' => 'Klinik HP Test',
        'slug' => 'klinik-hp-test',
        'owner_id' => $this->ownerUser->id,
        'plan' => 'business',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => $this->ownerUser->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    session(['active_store_id' => $this->store->id]);
    app(TenantContext::class)->setStore($this->store, $membership);

    $this->customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Budi Santoso',
        'phone' => '081234567890',
        'whatsapp_number' => '6281234567890',
    ]);

    $this->device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'device_type' => 'smartphone',
        'brand' => 'Xiaomi',
        'model' => 'Redmi Note 12',
        'color' => 'Hitam',
        'imei' => '861234567890123',
    ]);
});

test('user can view orders index and see stats', function () {
    $response = $this->actingAs($this->ownerUser)
        ->withSession(['active_store_id' => $this->store->id])
        ->get(route('orders.index'));

    $response->assertStatus(200);
    $response->assertSee('Order Servis');
});

test('user can create a service order via intake form', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    Livewire::test(CreateOrder::class)
        ->set('is_new_customer', false)
        ->set('customer_id', $this->customer->id)
        ->set('customer_device_id', $this->device->id)
        ->set('complaints', 'Layar blank setelah terjatuh')
        ->set('accessories_included', 'Unit dan charger')
        ->set('estimated_cost', 150000)
        ->set('down_payment', 50000)
        ->call('storeOrder')
        ->assertHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('service_orders', [
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'complaints' => 'Layar blank setelah terjatuh',
        'paid_amount' => 50000,
        'payment_status' => 'partially_paid',
    ]);

    $order = ServiceOrder::where('customer_id', $this->customer->id)->first();
    expect($order)->not->toBeNull();
    expect($order->order_number)->toStartWith('ORD-');
    expect(strlen($order->tracking_token))->toBe(64);
});

test('user can create a service order with customer address and photos', function () {
    Storage::fake('public');
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    $file = UploadedFile::fake()->image('hp_retak.jpg');

    Livewire::test(CreateOrder::class)
        ->set('customer_mode', 'new')
        ->set('customer_phone', '089988776655')
        ->set('customer_name', 'Doni Salman')
        ->set('customer_address', 'Jl. Merdeka No. 99, Bandung')
        ->set('device_brand', 'Samsung')
        ->set('device_model', 'Galaxy S23')
        ->set('complaints', 'Kaca kamera retak')
        ->set('camera_photo', $file)
        ->call('storeOrder')
        ->assertHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('customers', [
        'name' => 'Doni Salman',
        'address' => 'Jl. Merdeka No. 99, Bandung',
    ]);

    $order = ServiceOrder::whereHas('customer', fn ($q) => $q->where('name', 'Doni Salman'))->first();
    expect($order)->not->toBeNull();
    expect($order->photos)->toBeArray()->toHaveCount(1);
    Storage::disk('public')->assertExists($order->photos[0]);
});

test('user can advance service order status and log history', function () {
    $this->actingAs($this->ownerUser);

    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'order_number' => 'ORD-202611-0001',
        'status' => 'received',
        'complaints' => 'Mati total',
        'tracking_token' => 'a1b2c3d4e5f67890123456789abcdef0',
        'created_by' => $this->ownerUser->id,
    ]);

    Livewire::test(OrderDetail::class, ['id' => $order->id])
        ->call('advanceStatus', 'diagnosing', 'Mulai pengecekan mesin')
        ->assertHasNoErrors();

    $order->refresh();
    expect($order->status)->toBe('diagnosing');

    $this->assertDatabaseHas('service_order_histories', [
        'service_order_id' => $order->id,
        'from_status' => 'received',
        'to_status' => 'diagnosing',
    ]);
});

test('user can add service items, record payment, and complete order', function () {
    $this->actingAs($this->ownerUser);

    $category = ServiceCategory::create([
        'store_id' => $this->store->id,
        'name' => 'Perbaikan Layar',
    ]);

    $service = Service::create([
        'store_id' => $this->store->id,
        'category_id' => $category->id,
        'name' => 'Ganti LCD Redmi Note 12',
        'price' => 250000,
        'default_warranty_days' => 30,
        'is_active' => true,
    ]);

    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'order_number' => 'ORD-202611-0002',
        'status' => 'in_progress',
        'complaints' => 'LCD Retak',
        'tracking_token' => 'b1b2c3d4e5f67890123456789abcdef0',
        'created_by' => $this->ownerUser->id,
    ]);

    Livewire::test(OrderDetail::class, ['id' => $order->id])
        ->set('selected_service_id', $service->id)
        ->call('onServiceSelected')
        ->assertSet('item_name', 'Ganti LCD Redmi Note 12')
        ->assertSet('item_price', 250000.0)
        ->call('addItem')
        ->assertHasNoErrors();

    $order->refresh();
    expect((float) $order->total_cost)->toBe(250000.0);
    expect($order->payment_status)->toBe('unpaid');

    // Record full payment
    Livewire::test(OrderDetail::class, ['id' => $order->id])
        ->set('payment_amount', 250000)
        ->set('payment_method', 'cash')
        ->call('recordPayment')
        ->assertHasNoErrors();

    $order->refresh();
    expect((float) $order->paid_amount)->toBe(250000.0);
    expect($order->payment_status)->toBe('paid');
    expect($order->remaining_balance)->toBe(0.0);
});

test('user can view printable receipt for order', function () {
    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'order_number' => 'ORD-202611-0003',
        'status' => 'completed',
        'complaints' => 'Ganti Baterai',
        'tracking_token' => 'c1b2c3d4e5f67890123456789abcdef0',
        'created_by' => $this->ownerUser->id,
    ]);

    $response = $this->actingAs($this->ownerUser)
        ->withSession(['active_store_id' => $this->store->id])
        ->get(route('orders.print', $order->id));

    $response->assertStatus(200);
    $response->assertSee('#ORD-202611-0003');
    $response->assertSee('Budi Santoso');
});

test('service order data is isolated between different tenants', function () {
    // Second store
    $otherUser = User::factory()->create();
    $otherStore = Store::factory()->create([
        'name' => 'Toko HP Lain',
        'slug' => 'toko-hp-lain',
        'owner_id' => $otherUser->id,
        'plan' => 'starter',
    ]);
    StoreMembership::create([
        'store_id' => $otherStore->id,
        'user_id' => $otherUser->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    $orderStoreA = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'order_number' => 'ORD-TOKO-A',
        'status' => 'received',
        'complaints' => 'HP Toko A',
        'tracking_token' => 'd1b2c3d4e5f67890123456789abcdef0',
        'created_by' => $this->ownerUser->id,
    ]);

    // Act as user of Store B
    $response = $this->actingAs($otherUser)
        ->withSession(['active_store_id' => $otherStore->id])
        ->get(route('orders.show', $orderStoreA->id));

    $response->assertStatus(404);
});

test('customer phone is normalized to 08 format on intake', function () {
    expect(CreateOrder::normalizePhoneNumber('+628-591-101-090-54'))->toBe('0859110109054');
    expect(CreateOrder::normalizePhoneNumber('+6281234567890'))->toBe('081234567890');
    expect(CreateOrder::normalizePhoneNumber('6281234567890'))->toBe('081234567890');
    expect(CreateOrder::normalizePhoneNumber('81234567890'))->toBe('081234567890');
    expect(CreateOrder::normalizePhoneNumber('081234567890'))->toBe('081234567890');
    expect(CreateOrder::normalizePhoneNumber('adsdwad'))->toBe('');
    expect(strlen(CreateOrder::normalizePhoneNumber('0812345678901234567890')))->toBe(16);
});

test('user can create a service order with new customer and normalized phone', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    Livewire::test(CreateOrder::class)
        ->set('is_new_customer', true)
        ->set('customer_mode', 'new')
        ->set('customer_name', 'Andi Pratama')
        ->set('customer_phone', '+6281298765432')
        ->set('device_brand', 'Samsung')
        ->set('device_model', 'Galaxy A54')
        ->set('complaints', 'Ganti baterai')
        ->set('estimated_cost', 300000)
        ->set('down_payment', 100000)
        ->call('storeOrder')
        ->assertHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('customers', [
        'store_id' => $this->store->id,
        'name' => 'Andi Pratama',
        'phone' => '081298765432',
    ]);
});

test('user can create service order with undetermined cost and completion date', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    Livewire::test(CreateOrder::class)
        ->set('is_new_customer', false)
        ->set('customer_id', $this->customer->id)
        ->set('customer_device_id', $this->device->id)
        ->set('complaints', 'Mati total terkena air laut')
        ->set('is_cost_undetermined', true)
        ->set('is_completion_undetermined', true)
        ->set('down_payment', 50000)
        ->call('storeOrder')
        ->assertHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('service_orders', [
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'complaints' => 'Mati total terkena air laut',
        'is_cost_undetermined' => true,
        'estimated_cost' => 0,
        'total_cost' => 0,
        'down_payment' => 50000,
        'paid_amount' => 50000,
        'estimated_completion_at' => null,
    ]);

    $order = ServiceOrder::where('complaints', 'Mati total terkena air laut')->first();
    expect($order->is_cost_undetermined)->toBeTrue();
    expect($order->estimated_completion_at)->toBeNull();

    // Verify thermal and standard print receipts render gracefully
    $thermalResponse = $this->get(route('orders.print', ['id' => $order->id, 'format' => 'thermal']));
    $thermalResponse->assertStatus(200);
    $thermalResponse->assertSee('Menunggu Diagnosa');

    $standardResponse = $this->get(route('orders.print', ['id' => $order->id, 'format' => 'standard']));
    $standardResponse->assertStatus(200);
    $standardResponse->assertSee('Menunggu Diagnosa / Konfirmasi Teknisi');
});

test('user can submit order with thousands separator dots in cost and down payment', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    Livewire::test(CreateOrder::class)
        ->set('is_new_customer', false)
        ->set('customer_id', $this->customer->id)
        ->set('customer_device_id', $this->device->id)
        ->set('complaints', 'Ganti LCD dan Casing')
        ->set('is_cost_undetermined', false)
        ->set('estimated_cost', '1.234.576')
        ->set('down_payment', '50.000')
        ->call('storeOrder')
        ->assertHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('service_orders', [
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'estimated_cost' => 1234576,
        'total_cost' => 1234576,
        'down_payment' => 50000,
        'paid_amount' => 50000,
    ]);
});
