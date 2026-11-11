<?php

use App\Livewire\Purchase\PurchaseIndex;
use App\Livewire\Supplier\SupplierIndex;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->owner = User::factory()->create();
    $this->store = Store::factory()->create([
        'name' => 'Mitra Gadget Servis',
        'slug' => 'mitra-gadget-servis',
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

    $this->productA = Product::create([
        'store_id' => $this->store->id,
        'name' => 'LCD Samsung A12',
        'buy_price' => 120000,
        'sell_price' => 250000,
        'stock' => 2,
        'min_stock' => 5,
    ]);

    $this->productB = Product::create([
        'store_id' => $this->store->id,
        'name' => 'Baterai Xiaomi Poco X3',
        'buy_price' => 85000,
        'sell_price' => 175000,
        'stock' => 1,
        'min_stock' => 3,
    ]);
});

test('user can view suppliers index and create a new supplier', function () {
    $this->actingAs($this->owner);
    session(['active_store_id' => $this->store->id]);

    $response = $this->get(route('suppliers.index'));
    $response->assertStatus(200);
    $response->assertSee('Daftar Supplier');

    Livewire::test(SupplierIndex::class)
        ->set('name', 'Grosir Sparepart Glodok Jaya')
        ->set('contact_person', 'Koh Alex')
        ->set('phone', '081399887766')
        ->set('email', 'glodokjaya@supplier.com')
        ->call('saveSupplier')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('suppliers', [
        'store_id' => $this->store->id,
        'name' => 'Grosir Sparepart Glodok Jaya',
        'contact_person' => 'Koh Alex',
        'phone' => '081399887766',
    ]);
});

test('user can create a purchase order with multiple items and down payment', function () {
    $this->actingAs($this->owner);
    session(['active_store_id' => $this->store->id]);

    $supplier = Supplier::create([
        'store_id' => $this->store->id,
        'name' => 'CV Mega Parts',
        'phone' => '081234567890',
    ]);

    Livewire::test(PurchaseIndex::class)
        ->set('selected_supplier_id', $supplier->id)
        ->set('items', [
            ['product_id' => $this->productA->id, 'quantity' => 5, 'unit_cost' => 120000],
            ['product_id' => $this->productB->id, 'quantity' => 4, 'unit_cost' => 85000],
        ])
        ->set('down_payment', 400000)
        ->call('savePurchase')
        ->assertHasNoErrors();

    // Total: (5*120.000) + (4*85.000) = 600.000 + 340.000 = 940.000
    // DP: 400.000, Remaining debt: 540.000
    $purchase = Purchase::where('supplier_id', $supplier->id)->first();
    expect($purchase)->not->toBeNull();
    expect($purchase->purchase_number)->toStartWith('PO-');
    expect((float) $purchase->total_amount)->toBe(940000.0);
    expect((float) $purchase->paid_amount)->toBe(400000.0);
    expect($purchase->remaining_debt)->toBe(540000.0);
    expect($purchase->payment_status)->toBe('partially_paid');
    expect($purchase->status)->toBe('pending');
});

test('receiving purchase confirms receipt and atomically increments stock', function () {
    $supplier = Supplier::create([
        'store_id' => $this->store->id,
        'name' => 'CV Mega Parts',
    ]);

    $purchaseService = app(PurchaseService::class);
    $purchase = $purchaseService->createPurchase($supplier, [
        ['product_id' => $this->productA->id, 'quantity' => 10, 'unit_cost' => 120000],
    ], 0, $this->owner->id);

    // Initial stock was 2
    expect($this->productA->fresh()->stock)->toBe(2);

    // Confirm receipt
    $purchaseService->receivePurchase($purchase, $this->owner->id);

    $purchase->refresh();
    expect($purchase->status)->toBe('received');
    expect($purchase->received_at)->not->toBeNull();

    // Stock should now be 2 + 10 = 12
    expect($this->productA->fresh()->stock)->toBe(12);

    // Stock movement audit created
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $this->productA->id,
        'type' => 'in',
        'quantity' => 10,
        'reference_type' => 'purchase',
        'reference_id' => $purchase->id,
        'stock_before' => 2,
        'stock_after' => 12,
    ]);
});

test('supplier payment reduces debt and marks purchase as paid', function () {
    $supplier = Supplier::create([
        'store_id' => $this->store->id,
        'name' => 'Sentral Sparepart',
    ]);

    $purchaseService = app(PurchaseService::class);
    $purchase = $purchaseService->createPurchase($supplier, [
        ['product_id' => $this->productA->id, 'quantity' => 2, 'unit_cost' => 100000],
    ], 50000, $this->owner->id);

    // Total 200.000, paid 50.000, remaining debt 150.000
    expect($purchase->remaining_debt)->toBe(150000.0);
    expect($supplier->fresh()->total_debt)->toBe(150000.0);

    // Pay remaining 150.000
    $purchaseService->recordSupplierPayment($purchase, 150000, 'transfer', $this->owner->id);

    $purchase->refresh();
    expect($purchase->remaining_debt)->toBe(0.0);
    expect($purchase->payment_status)->toBe('paid');
    expect($supplier->fresh()->total_debt)->toBe(0.0);
});

test('cancelling a received purchase reverses stock correctly', function () {
    $supplier = Supplier::create([
        'store_id' => $this->store->id,
        'name' => 'Sentral Sparepart',
    ]);

    $purchaseService = app(PurchaseService::class);
    $purchase = $purchaseService->createPurchase($supplier, [
        ['product_id' => $this->productA->id, 'quantity' => 5, 'unit_cost' => 100000],
    ], 0, $this->owner->id);

    $purchaseService->receivePurchase($purchase, $this->owner->id);
    expect($this->productA->fresh()->stock)->toBe(7); // 2 + 5

    // Cancel PO
    $purchaseService->cancelPurchase($purchase, $this->owner->id);

    $purchase->refresh();
    expect($purchase->status)->toBe('cancelled');
    // Stock reversed back to 2
    expect($this->productA->fresh()->stock)->toBe(2);
});

test('suppliers and purchases are isolated between tenants', function () {
    $otherUser = User::factory()->create();
    $otherStore = Store::factory()->create([
        'name' => 'Toko HP Barat',
        'slug' => 'toko-hp-barat',
        'owner_id' => $otherUser->id,
    ]);
    StoreMembership::create([
        'store_id' => $otherStore->id,
        'user_id' => $otherUser->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    $supplierA = Supplier::create([
        'store_id' => $this->store->id,
        'name' => 'Supplier Rahasia Toko A',
    ]);

    // Access as user from Store B
    $response = $this->actingAs($otherUser)
        ->withSession(['active_store_id' => $otherStore->id])
        ->get(route('suppliers.index'));

    $response->assertStatus(200);
    $response->assertDontSee('Supplier Rahasia Toko A');
});
