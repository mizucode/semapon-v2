<?php

use App\Livewire\Finance\FinanceIndex;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\Supplier;
use App\Models\User;
use App\Services\FinanceService;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->owner = User::factory()->create();
    $this->store = Store::factory()->create([
        'name' => 'Klinik Keuangan HP',
        'slug' => 'klinik-keuangan-hp',
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

test('owner can access financial ledger overview', function () {
    $response = $this->actingAs($this->owner)
        ->withSession(['active_store_id' => $this->store->id])
        ->get(route('finances.index'));

    $response->assertStatus(200);
    $response->assertSee('Buku Kas');
});

test('recording order payment updates order status and logs cash transaction', function () {
    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Dewi',
        'phone' => '081233445566',
    ]);

    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'device_type' => 'smartphone',
        'brand' => 'Vivo',
        'model' => 'Y20',
    ]);

    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'order_number' => 'ORD-202611-8881',
        'status' => 'in_progress',
        'complaints' => 'Mati lampu layar',
        'total_cost' => 300000,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
        'created_by' => $this->owner->id,
    ]);

    $financeService = app(FinanceService::class);

    // Pay DP 100.000
    $payment = $financeService->recordOrderPayment(
        $order,
        100000,
        'qris',
        $this->owner->id,
        'QRIS-991',
        'DP Servis Layar'
    );

    $order->refresh();
    expect($payment->payment_number)->toStartWith('PAY-');
    expect((float) $order->paid_amount)->toBe(100000.0);
    expect($order->payment_status)->toBe('partially_paid');

    // Verify Cash Transaction
    $this->assertDatabaseHas('cash_transactions', [
        'store_id' => $this->store->id,
        'type' => 'in',
        'amount' => 100000,
        'category' => 'service_income',
        'reference_type' => 'payment',
        'reference_id' => $payment->id,
    ]);

    // Pay Remaining 200.000
    $financeService->recordOrderPayment($order, 200000, 'cash', $this->owner->id);
    $order->refresh();

    expect((float) $order->paid_amount)->toBe(300000.0);
    expect($order->payment_status)->toBe('paid');
});

test('voiding a payment reverses order balance and cash ledger', function () {
    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Bambang',
        'phone' => '081233445577',
    ]);

    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'device_type' => 'smartphone',
        'brand' => 'Realme',
        'model' => 'C35',
    ]);

    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'order_number' => 'ORD-202611-8882',
        'status' => 'in_progress',
        'complaints' => 'Ganti speaker',
        'total_cost' => 150000,
        'paid_amount' => 0,
        'created_by' => $this->owner->id,
    ]);

    $financeService = app(FinanceService::class);
    $payment = $financeService->recordOrderPayment($order, 150000, 'cash', $this->owner->id);

    expect((float) $order->fresh()->paid_amount)->toBe(150000.0);

    // Void payment
    $financeService->voidPayment($payment, $this->owner->id, 'Salah input nominal kasir');

    $order->refresh();
    $payment->refresh();

    expect($payment->status)->toBe('void');
    expect((float) $order->paid_amount)->toBe(0.0);
    expect($order->payment_status)->toBe('unpaid');

    // Refund ledger entry recorded
    $this->assertDatabaseHas('cash_transactions', [
        'store_id' => $this->store->id,
        'type' => 'out',
        'amount' => 150000,
        'category' => 'refund',
    ]);
});

test('owner can record operational expense and manual cash in', function () {
    $this->actingAs($this->owner);
    session(['active_store_id' => $this->store->id]);

    $category = ExpenseCategory::create([
        'store_id' => $this->store->id,
        'name' => 'Listrik & Utilitas',
    ]);

    // Record expense via Livewire
    Livewire::test(FinanceIndex::class)
        ->set('expense_amount', 75000)
        ->set('expense_category_id', $category->id)
        ->set('expense_notes', 'Token listrik bengkel servis')
        ->call('submitExpense')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('expenses', [
        'store_id' => $this->store->id,
        'category_id' => $category->id,
        'amount' => 75000,
        'notes' => 'Token listrik bengkel servis',
    ]);

    $this->assertDatabaseHas('cash_transactions', [
        'store_id' => $this->store->id,
        'type' => 'out',
        'amount' => 75000,
        'category' => 'expense',
    ]);

    // Record cash-in via Livewire
    Livewire::test(FinanceIndex::class)
        ->set('cash_in_amount', 500000)
        ->set('cash_in_category', 'modal_awal')
        ->set('cash_in_desc', 'Setoran modal kas kecil')
        ->call('submitCashIn')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('cash_transactions', [
        'store_id' => $this->store->id,
        'type' => 'in',
        'amount' => 500000,
        'category' => 'modal_awal',
    ]);
});

test('financial summary computes correct net balance, receivables, and debts', function () {
    $financeService = app(FinanceService::class);

    // 1. Expense: 50.000
    $financeService->recordExpense($this->store, 50000, null, 'cash', $this->owner->id, 'Beli isolasi');

    // 2. Order with total 300.000, DP 100.000 -> Receivable: 200.000
    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Slamet',
        'phone' => '081233445588',
    ]);
    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'device_type' => 'smartphone',
        'brand' => 'Infinix',
        'model' => 'Hot 11',
    ]);
    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'order_number' => 'ORD-202611-8883',
        'status' => 'in_progress',
        'complaints' => 'Ganti LCD',
        'total_cost' => 300000,
        'paid_amount' => 0,
        'created_by' => $this->owner->id,
    ]);
    $financeService->recordOrderPayment($order, 100000, 'cash', $this->owner->id);

    // 3. Purchase PO with total 250.000, paid 100.000 -> Debt: 150.000
    $supplier = Supplier::create([
        'store_id' => $this->store->id,
        'name' => 'Supplier LCD Grosir',
    ]);
    Purchase::create([
        'store_id' => $this->store->id,
        'supplier_id' => $supplier->id,
        'purchase_number' => 'PO-202611-9999',
        'purchase_date' => now()->toDateString(),
        'total_amount' => 250000,
        'paid_amount' => 100000,
        'payment_status' => 'partially_paid',
        'status' => 'received',
    ]);

    $summary = $financeService->getFinancialSummary($this->store);

    expect($summary['total_income'])->toBe(100000.0);
    expect($summary['total_expense'])->toBe(50000.0);
    expect($summary['net_profit'])->toBe(50000.0); // 100k in - 50k out
    expect($summary['total_receivables'])->toBe(200000.0); // 300k - 100k
    expect($summary['total_supplier_debt'])->toBe(150000.0); // 250k - 100k
});

test('financial data is completely isolated between different tenants', function () {
    $otherUser = User::factory()->create();
    $otherStore = Store::factory()->create([
        'name' => 'Toko HP Selatan',
        'slug' => 'toko-hp-selatan',
        'owner_id' => $otherUser->id,
    ]);
    StoreMembership::create([
        'store_id' => $otherStore->id,
        'user_id' => $otherUser->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    // Store A expense
    $financeService = app(FinanceService::class);
    $financeService->recordExpense($this->store, 999000, null, 'cash', $this->owner->id, 'Biaya Toko A');

    // Act as user of Store B
    $response = $this->actingAs($otherUser)
        ->withSession(['active_store_id' => $otherStore->id])
        ->get(route('finances.index'));

    $response->assertStatus(200);
    $response->assertDontSee('999.000');
});
