<?php

use App\Livewire\Inventory\ProductIndex;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->owner = User::factory()->create();
    $this->store = Store::factory()->create([
        'name' => 'Toko Sparepart Jaya',
        'slug' => 'toko-sparepart-jaya',
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

test('user can view inventory index page', function () {
    $response = $this->actingAs($this->owner)
        ->withSession(['active_store_id' => $this->store->id])
        ->get(route('inventory.index'));

    $response->assertStatus(200);
    $response->assertSee('Inventaris Sparepart');
});

test('user can create product with initial stock and movement audit', function () {
    $this->actingAs($this->owner);
    session(['active_store_id' => $this->store->id]);

    $category = ProductCategory::create([
        'store_id' => $this->store->id,
        'name' => 'Layar & LCD',
    ]);

    Livewire::test(ProductIndex::class)
        ->set('name', 'LCD OLED iPhone X')
        ->set('category_id', $category->id)
        ->set('sku', 'LCD-IPX-OLED')
        ->set('barcode', '8991234567890')
        ->set('type', 'sparepart')
        ->set('buy_price', 200000)
        ->set('sell_price', 350000)
        ->set('stock', 10)
        ->set('min_stock', 3)
        ->set('unit', 'pcs')
        ->call('saveProduct')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('products', [
        'store_id' => $this->store->id,
        'name' => 'LCD OLED iPhone X',
        'sku' => 'LCD-IPX-OLED',
        'stock' => 10,
    ]);

    // Initial stock movement logged
    $this->assertDatabaseHas('stock_movements', [
        'store_id' => $this->store->id,
        'type' => 'in',
        'quantity' => 10,
        'stock_before' => 0,
        'stock_after' => 10,
    ]);
});

test('user can record stock-in and stock-out through inventory service', function () {
    $product = Product::create([
        'store_id' => $this->store->id,
        'name' => 'Baterai Samsung A51',
        'buy_price' => 75000,
        'sell_price' => 150000,
        'stock' => 5,
        'min_stock' => 2,
    ]);

    $inventoryService = app(InventoryService::class);

    // Stock in +5
    $movementIn = $inventoryService->recordStockIn($product, 5, 75000, $this->owner->id, 'Restock supplier');
    $product->refresh();

    expect($product->stock)->toBe(10);
    expect($movementIn->stock_before)->toBe(5);
    expect($movementIn->stock_after)->toBe(10);

    // Stock out -3
    $movementOut = $inventoryService->recordStockOut($product, 3, $this->owner->id, 'Rusak saat pengiriman');
    $product->refresh();

    expect($product->stock)->toBe(7);
    expect($movementOut->stock_before)->toBe(10);
    expect($movementOut->stock_after)->toBe(7);
});

test('stock-out throws exception if stock is insufficient', function () {
    $product = Product::create([
        'store_id' => $this->store->id,
        'name' => 'Konektor Cas Type C',
        'buy_price' => 5000,
        'sell_price' => 25000,
        'stock' => 2,
        'min_stock' => 5,
    ]);

    $inventoryService = app(InventoryService::class);

    expect(fn () => $inventoryService->recordStockOut($product, 5))
        ->toThrow(Exception::class, 'Stok tidak mencukupi');
});

test('service order can deduct sparepart atomically and record snapshot prices', function () {
    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Ahmad',
        'phone' => '081299990000',
    ]);

    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'device_type' => 'smartphone',
        'brand' => 'Oppo',
        'model' => 'A57',
    ]);

    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'order_number' => 'ORD-202611-9991',
        'status' => 'in_progress',
        'complaints' => 'Baterai bocor',
        'created_by' => $this->owner->id,
    ]);

    $product = Product::create([
        'store_id' => $this->store->id,
        'name' => 'Baterai Oppo A57 Original',
        'buy_price' => 90000,
        'sell_price' => 175000,
        'stock' => 4,
        'min_stock' => 1,
    ]);

    $inventoryService = app(InventoryService::class);
    $orderPart = $inventoryService->usePartInOrder($order, $product, 1, $this->owner->id);

    $product->refresh();
    $order->refresh();

    // Stock reduced to 3
    expect($product->stock)->toBe(3);

    // Snapshot prices recorded
    expect((float) $orderPart->cost_price)->toBe(90000.0);
    expect((float) $orderPart->sell_price)->toBe(175000.0);
    expect((float) $orderPart->subtotal)->toBe(175000.0);

    // Added to order cost
    expect((float) $order->total_cost)->toBe(175000.0);

    // Stock movement logged with reference
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => 'service_usage',
        'reference_type' => 'service_order',
        'reference_id' => $order->id,
        'stock_before' => 4,
        'stock_after' => 3,
    ]);

    // Return / reversal test
    $inventoryService->returnPartFromOrder($orderPart, $this->owner->id);
    $product->refresh();

    expect($product->stock)->toBe(4);
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => 'service_return',
        'stock_before' => 3,
        'stock_after' => 4,
    ]);
});

test('low stock scope correctly identifies products needing restock', function () {
    Product::create([
        'store_id' => $this->store->id,
        'name' => 'Stok Aman',
        'buy_price' => 10000,
        'sell_price' => 20000,
        'stock' => 10,
        'min_stock' => 3,
    ]);

    Product::create([
        'store_id' => $this->store->id,
        'name' => 'Stok Kritis',
        'buy_price' => 10000,
        'sell_price' => 20000,
        'stock' => 2,
        'min_stock' => 3,
    ]);

    $lowStockProducts = Product::lowStock()->get();
    expect($lowStockProducts)->toHaveCount(1);
    expect($lowStockProducts->first()->name)->toBe('Stok Kritis');
});

test('inventory is completely isolated between different tenants', function () {
    $otherUser = User::factory()->create();
    $otherStore = Store::factory()->create([
        'name' => 'Toko Sebelah',
        'slug' => 'toko-sebelah',
        'owner_id' => $otherUser->id,
    ]);
    StoreMembership::create([
        'store_id' => $otherStore->id,
        'user_id' => $otherUser->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    Product::create([
        'store_id' => $this->store->id,
        'name' => 'Barang Rahasia Toko A',
        'buy_price' => 10000,
        'sell_price' => 20000,
        'stock' => 5,
    ]);

    // Act as user of Store B
    $response = $this->actingAs($otherUser)
        ->withSession(['active_store_id' => $otherStore->id])
        ->get(route('inventory.index'));

    $response->assertStatus(200);
    $response->assertDontSee('Barang Rahasia Toko A');
});
