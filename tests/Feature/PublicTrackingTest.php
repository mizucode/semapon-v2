<?php

use App\Livewire\Tracking\PublicTracking;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\User;
use App\Services\TrackingTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->store = Store::factory()->create([
        'name' => 'Dokter Gadget',
        'slug' => 'dokter-gadget',
        'owner_id' => $this->owner->id,
        'phone' => '081122334455',
    ]);

    $this->customer = Customer::create([
        'store_id' => $this->store->id,
        'name' => 'Siti Aminah',
        'phone' => '089876543210',
    ]);

    $this->device = CustomerDevice::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'device_type' => 'smartphone',
        'brand' => 'Apple',
        'model' => 'iPhone 13',
        'color' => 'Midnight Blue',
        'imei' => '351234567890123',
        'passcode' => '998877_SECRET_PIN',
    ]);

    $this->order = ServiceOrder::create([
        'store_id' => $this->store->id,
        'customer_id' => $this->customer->id,
        'customer_device_id' => $this->device->id,
        'order_number' => 'ORD-202611-7777',
        'status' => 'waiting_approval',
        'complaints' => 'Baterai cepat habis dan kembung',
        'estimated_cost' => 350000,
        'total_cost' => 350000,
        'paid_amount' => 0,
        'created_by' => $this->owner->id,
        'passcode' => '998877_SECRET_PIN',
    ]);
});

test('guest can access public tracking without authentication', function () {
    $token = $this->order->tracking_token;

    $response = $this->get(route('tracking.show', $token));
    $response->assertStatus(200);
    $response->assertSee('#ORD-202611-7777');
    $response->assertSee('iPhone 13');
    $response->assertSee('Dokter Gadget');
});

test('public tracking never exposes sensitive lockscreen PIN or passcode', function () {
    $token = $this->order->tracking_token;

    $response = $this->get(route('tracking.show', $token));
    $response->assertStatus(200);
    $response->assertDontSee('998877_SECRET_PIN');
});

test('public tracking page includes noindex robots tag', function () {
    $token = $this->order->tracking_token;

    $response = $this->get(route('tracking.show', $token));
    $response->assertStatus(200);
    $response->assertSee('noindex, nofollow', false);
});

test('customer can approve estimate directly on tracking page', function () {
    $token = $this->order->tracking_token;

    Livewire::test(PublicTracking::class, ['token' => $token])
        ->call('approveEstimate')
        ->assertHasNoErrors();

    $this->order->refresh();
    expect($this->order->status)->toBe('in_progress');
    expect($this->order->customer_approved_at)->not->toBeNull();

    $this->assertDatabaseHas('service_order_histories', [
        'service_order_id' => $this->order->id,
        'from_status' => 'waiting_approval',
        'to_status' => 'in_progress',
    ]);
});

test('customer can reject estimate directly on tracking page', function () {
    $token = $this->order->tracking_token;

    Livewire::test(PublicTracking::class, ['token' => $token])
        ->call('rejectEstimate')
        ->assertHasNoErrors();

    $this->order->refresh();
    expect($this->order->status)->toBe('cancelled');

    $this->assertDatabaseHas('service_order_histories', [
        'service_order_id' => $this->order->id,
        'from_status' => 'waiting_approval',
        'to_status' => 'cancelled',
    ]);
});

test('invalid or nonexistent token displays error message', function () {
    $response = $this->get(route('tracking.show', 'completely-invalid-random-token-here'));
    $response->assertStatus(200);
    $response->assertSee('Tautan Pelacakan Tidak Valid');
});

test('revoked tracking token cannot be used', function () {
    $tokenService = app(TrackingTokenService::class);
    $tokenData = $tokenService->generateToken($this->order);
    $plainToken = $tokenData['plain_token'];

    // Revoke it
    $tokenData['token']->revoke();

    $resolved = $tokenService->resolveOrder($plainToken);
    expect($resolved)->toBeNull();
});

test('expired tracking token cannot be used', function () {
    $tokenService = app(TrackingTokenService::class);
    $tokenData = $tokenService->generateToken($this->order, validDays: -1);
    $plainToken = $tokenData['plain_token'];

    $resolved = $tokenService->resolveOrder($plainToken);
    expect($resolved)->toBeNull();
});
