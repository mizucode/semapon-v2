<?php

use App\Livewire\Notification\NotificationList;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Product;
use App\Models\Reminder;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Services\ReminderService;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->owner = User::factory()->create();
    $this->store = Store::factory()->create([
        'name' => 'Klinik Notifikasi HP',
        'slug' => 'klinik-notifikasi-hp',
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

test('user can view notifications screen', function () {
    $response = $this->actingAs($this->owner)
        ->withSession(['active_store_id' => $this->store->id])
        ->get(route('notifications.index'));

    $response->assertStatus(200);
    $response->assertSee('Pusat Pengingat');
});

test('reminder scanner generates alerts for overdue orders and low stock', function () {
    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Pak Joko',
        'phone' => '081234567890',
    ]);

    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'device_type' => 'smartphone',
        'brand' => 'Samsung',
        'model' => 'A52',
    ]);

    // 1. Overdue order (completion date was 2 days ago)
    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'order_number' => 'ORD-202611-1234',
        'status' => 'in_progress',
        'complaints' => 'Ganti IC',
        'estimated_completion_at' => now()->subDays(2),
        'created_by' => $this->owner->id,
    ]);

    // 2. Low stock product (stock 1 <= min_stock 3)
    $product = Product::create([
        'store_id' => $this->store->id,
        'name' => 'Fleksibel On Off Vivo Y12',
        'buy_price' => 15000,
        'sell_price' => 45000,
        'stock' => 1,
        'min_stock' => 3,
    ]);

    $reminderService = app(ReminderService::class);
    $count = $reminderService->scanAndGenerateReminders($this->store);

    expect($count)->toBe(2);

    $this->assertDatabaseHas('reminders', [
        'store_id' => $this->store->id,
        'type' => 'overdue_order',
        'reference_type' => 'service_order',
        'reference_id' => $order->id,
        'is_read' => false,
    ]);

    $this->assertDatabaseHas('reminders', [
        'store_id' => $this->store->id,
        'type' => 'low_stock',
        'reference_type' => 'product',
        'reference_id' => $product->id,
        'is_read' => false,
    ]);

    // Re-scanning should NOT produce duplicates
    $rescanCount = $reminderService->scanAndGenerateReminders($this->store);
    expect($rescanCount)->toBe(0);
});

test('user can mark a reminder as read and mark all as read', function () {
    $this->actingAs($this->owner);
    session(['active_store_id' => $this->store->id]);

    $reminder1 = Reminder::create([
        'store_id' => $this->store->id,
        'type' => 'overdue_order',
        'title' => 'Pengingat 1',
        'message' => 'Detail 1',
        'is_read' => false,
    ]);

    $reminder2 = Reminder::create([
        'store_id' => $this->store->id,
        'type' => 'low_stock',
        'title' => 'Pengingat 2',
        'message' => 'Detail 2',
        'is_read' => false,
    ]);

    // Mark single as read
    Livewire::test(NotificationList::class)
        ->call('markAsRead', $reminder1->id)
        ->assertHasNoErrors();

    expect($reminder1->fresh()->is_read)->toBeTrue();
    expect($reminder2->fresh()->is_read)->toBeFalse();

    // Mark all as read
    Livewire::test(NotificationList::class)
        ->call('markAllRead')
        ->assertHasNoErrors();

    expect($reminder2->fresh()->is_read)->toBeTrue();
});

test('technician assignment generates dedicated reminder', function () {
    $techUser = User::factory()->create();

    $customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Bu Rina',
        'phone' => '081234567891',
    ]);
    $device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'device_type' => 'smartphone',
        'brand' => 'iPhone',
        'model' => '12',
    ]);
    $order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $customer->id,
        'customer_device_id' => $device->id,
        'order_number' => 'ORD-202611-5555',
        'status' => 'received',
        'complaints' => 'Layar bergaris',
        'created_by' => $this->owner->id,
    ]);

    $reminderService = app(ReminderService::class);
    $reminder = $reminderService->notifyTechnicianAssignment($order, $techUser);

    expect($reminder->user_id)->toBe($techUser->id);
    expect($reminder->type)->toBe('tech_assigned');
    expect($reminder->reference_id)->toBe($order->id);
});

test('reminders are strictly isolated between tenants', function () {
    $otherUser = User::factory()->create();
    $otherStore = Store::factory()->create([
        'name' => 'Toko Servis Timur',
        'slug' => 'toko-servis-timur',
        'owner_id' => $otherUser->id,
    ]);
    StoreMembership::create([
        'store_id' => $otherStore->id,
        'user_id' => $otherUser->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    Reminder::create([
        'store_id' => $this->store->id,
        'type' => 'low_stock',
        'title' => 'Peringatan Rahasia Toko A',
        'message' => 'Pesan rahasia',
        'is_read' => false,
    ]);

    // Access as user from Store B
    $response = $this->actingAs($otherUser)
        ->withSession(['active_store_id' => $otherStore->id])
        ->get(route('notifications.index'));

    $response->assertStatus(200);
    $response->assertDontSee('Peringatan Rahasia Toko A');
});
