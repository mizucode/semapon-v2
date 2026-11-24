<?php

use App\Livewire\Order\CreateOrder;
use App\Livewire\Order\EditOrder;
use App\Livewire\Order\OrderDetail;
use App\Livewire\Order\OrderIndex;
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

test('user can search orders on index', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    Livewire::test(OrderIndex::class)
        ->set('search', 'Poco')
        ->assertSee('Order Servis');
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
    expect($order->order_number)->toMatch('/^ORD-\d{6}-[23456789ABCDEFGHJKLMNPQRSTUVWXYZ]{8}$/');
    expect(strlen($order->order_number))->toBe(19);
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
        ->set('device_imei', '861234567890123')
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

test('technician can dynamically update order status to unrepairable with custom notes', function () {
    $this->actingAs($this->ownerUser);

    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'order_number' => 'ORD-202611-0002',
        'status' => 'diagnosing',
        'complaints' => 'Mati total masuk air',
        'tracking_token' => 'b1b2c3d4e5f67890123456789abcdef1',
        'created_by' => $this->ownerUser->id,
    ]);

    Livewire::test(OrderDetail::class, ['id' => $order->id])
        ->call('openUpdateStatusModal', 'unrepairable')
        ->set('new_status', 'unrepairable')
        ->set('status_notes', 'IC Power & CPU short parah, tidak bisa diperbaiki.')
        ->call('updateStatusWithNotes')
        ->assertHasNoErrors()
        ->assertSet('showUpdateStatusModal', false);

    $order->refresh();
    expect($order->status)->toBe('unrepairable');

    $this->assertDatabaseHas('service_order_histories', [
        'service_order_id' => $order->id,
        'from_status' => 'diagnosing',
        'to_status' => 'unrepairable',
        'notes' => 'IC Power & CPU short parah, tidak bisa diperbaiki.',
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
        ->set('payment_notes', 'Pelunasan Servis Ganti LCD')
        ->call('recordPayment')
        ->assertHasNoErrors();

    $order->refresh();
    expect((float) $order->paid_amount)->toBe(250000.0);
    expect($order->payment_status)->toBe('paid');
    expect($order->remaining_balance)->toBe(0.0);
    expect($order->payments)->toHaveCount(1);
    expect($order->payments->first()->notes)->toBe('Pelunasan Servis Ganti LCD');

    // Verify payment history and notes are rendered in order detail
    Livewire::test(OrderDetail::class, ['id' => $order->id])
        ->assertSee('Pelunasan Servis Ganti LCD')
        ->assertSee('Riwayat Pembayaran');

    // Test overpayment / kembalian display
    $order->update(['paid_amount' => 350000]);
    $order->refresh();
    expect($order->overpaid_amount)->toBe(100000.0);

    Livewire::test(OrderDetail::class, ['id' => $order->id])
        ->assertSee('Kembalian:')
        ->assertSee('100.000');
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
        ->set('customer_address', 'Jl. Merdeka Barat No. 12')
        ->set('device_brand', 'Samsung')
        ->set('device_model', 'Galaxy A54')
        ->set('device_imei', '861234567890124')
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

test('device imei is optional when creating a service order with new device', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    Livewire::test(CreateOrder::class)
        ->set('customer_mode', 'new')
        ->set('customer_name', 'Budi Test')
        ->set('customer_phone', '081234567890')
        ->set('customer_address', 'Jl. Sudirman No. 45')
        ->set('device_brand', 'Xiaomi')
        ->set('device_model', 'Redmi Note 13')
        ->set('device_imei', '')
        ->set('complaints', 'Bootloop')
        ->call('storeOrder')
        ->assertHasNoErrors(['device_imei']);
});

test('order creation requires customer address for new customer', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    Livewire::test(CreateOrder::class)
        ->set('customer_mode', 'new')
        ->set('customer_name', 'Customer Without Address')
        ->set('customer_phone', '081234567899')
        ->set('customer_address', '')
        ->set('device_brand', 'Xiaomi')
        ->set('device_model', 'Redmi Note 13')
        ->set('complaints', 'Layar blank')
        ->call('storeOrder')
        ->assertHasErrors(['customer_address']);
});

test('order detail displays disabled print receipt button when order has no items', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'status' => 'received',
        'complaints' => 'Layar sentuh mati',
        'created_by' => $this->ownerUser->id,
    ]);

    Livewire::test(OrderDetail::class, ['id' => $order->id])
        ->assertSee('Rincian Jasa')
        ->assertSee('Sparepart Masih Kosong')
        ->assertSee('Tutup')
        ->assertSee('Tanda Terima');
});

test('user can access print intake receipt and print service receipt', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'status' => 'received',
        'complaints' => 'Layar sentuh mati',
        'created_by' => $this->ownerUser->id,
    ]);

    $response = $this->get(route('orders.print', ['id' => $order->id, 'type' => 'intake']));
    $response->assertOk()
        ->assertSee('TANDA TERIMA SERVIS');

    $responseReceipt = $this->get(route('orders.print', ['id' => $order->id, 'type' => 'receipt']));
    $responseReceipt->assertOk()
        ->assertSee('NOTA SERVIS');
});

test('order detail displays physical condition photos section only when photos exist', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    $orderWithoutPhotos = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'status' => 'received',
        'complaints' => 'Layar sentuh mati',
        'created_by' => $this->ownerUser->id,
        'photos' => [],
    ]);

    Livewire::test(OrderDetail::class, ['id' => $orderWithoutPhotos->id])
        ->assertDontSee('Foto Kondisi Fisik Unit (Opsional)');

    $orderWithPhotos = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'status' => 'received',
        'complaints' => 'Layar sentuh mati',
        'created_by' => $this->ownerUser->id,
        'photos' => ['orders/test/photo1.webp', 'orders/test/photo2.webp'],
    ]);

    Livewire::test(OrderDetail::class, ['id' => $orderWithPhotos->id])
        ->assertSee('Foto Kondisi Fisik Unit (Opsional)')
        ->assertSee('2 Foto');
});

test('user can access edit order page and update order data', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'status' => 'received',
        'complaints' => 'Layar sentuh mati',
        'accessories_included' => 'Unit only',
        'created_by' => $this->ownerUser->id,
    ]);

    $response = $this->get(route('orders.edit', $order->id));
    $response->assertOk()
        ->assertSee('Edit Order')
        ->assertSee('Mengedit order ini berdampak ke seluruh data terkait')
        ->assertDontSee('Estimasi Biaya & Penugasan');

    Livewire::test(EditOrder::class, ['id' => $order->id])
        ->assertSet('complaints', 'Layar sentuh mati')
        ->assertSet('customer_name', 'Budi Santoso')
        ->set('complaints', 'Layar sentuh mati & casing retak')
        ->set('accessories_included', 'Unit + Casing')
        ->call('updateOrder')
        ->assertHasNoErrors()
        ->assertSessionHas('success')
        ->assertRedirect(route('orders.show', $order->id));

    $showResponse = $this->get(route('orders.show', $order->id));
    $showResponse->assertOk()
        ->assertSee('Order Berhasil Diperbarui')
        ->assertSee("Perubahan pada Order #{$order->order_number} berhasil disimpan.");

    expect($order->fresh()->complaints)->toBe('Layar sentuh mati & casing retak');
    expect($order->fresh()->accessories_included)->toBe('Unit + Casing');
});

test('user can delete order from order detail', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'status' => 'received',
        'complaints' => 'Layar sentuh mati',
        'accessories_included' => 'Unit only',
        'created_by' => $this->ownerUser->id,
    ]);

    Livewire::test(OrderDetail::class, ['id' => $order->id])
        ->assertSee('Kelola Order')
        ->assertSee('Edit Order')
        ->assertSee('Perhatian: Edit Data Order')
        ->assertSee('Lanjutkan Edit Order')
        ->assertSee('Hapus Order')
        ->assertSee('Konfirmasi Penghapusan Order')
        ->assertSee('Pencatatan Transaksi')
        ->assertSee('Ya, Hapus Permanen')
        ->call('deleteOrder')
        ->assertRedirect(route('orders.index'));

    expect(ServiceOrder::find($order->id))->toBeNull();
});

test('order detail hides cost summary footer when no items or services added', function () {
    $this->actingAs($this->ownerUser);
    session(['active_store_id' => $this->store->id]);

    $orderNoItems = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'status' => 'received',
        'complaints' => 'Mati total',
        'created_by' => $this->ownerUser->id,
        'paid_amount' => 0,
    ]);

    Livewire::test(OrderDetail::class, ['id' => $orderNoItems->id])
        ->assertDontSee('Total Biaya Servis:')
        ->assertDontSee('Sisa Tagihan:')
        ->assertDontSee('Catat Pembayaran')
        ->assertDontSee('Pembayaran Diterima:');

    $orderWithItem = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'status' => 'received',
        'complaints' => 'Mati total',
        'created_by' => $this->ownerUser->id,
        'paid_amount' => 50000,
        'total_cost' => 150000,
    ]);

    $orderWithItem->items()->create([
        'store_id' => $this->store->id,
        'item_type' => 'service',
        'name' => 'Ganti IC Power',
        'price' => 150000,
        'subtotal' => 150000,
    ]);

    Livewire::test(OrderDetail::class, ['id' => $orderWithItem->id])
        ->assertSee('Total Biaya Servis:')
        ->assertSee('Sisa Tagihan:')
        ->assertSee('Catat Pembayaran')
        ->assertSee('Pembayaran Diterima:')
        ->assertSee('Rp. 50.000');
});
