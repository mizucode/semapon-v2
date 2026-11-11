<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('database connection is active and operational', function () {
    expect(DB::connection()->getPdo())->not->toBeNull();
});

test('user model uses ulid as primary key', function () {
    $user = User::factory()->create([
        'name' => 'Budi Servis',
        'email' => 'budi@example.com',
        'phone' => '08123456789',
        'status' => 'active',
    ]);

    expect($user->id)->toBeString();
    expect(strlen($user->id))->toBe(26); // Standard ULID length is 26 characters
    expect($user->status)->toBe('active');
});

test('application foundation home page renders successfully', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('SEMAPON');
    $response->assertSee('Sistem Manajemen Servis Smartphone');
    $response->assertSee('Order Servis Terkini');
});

test('semapon configuration file is loaded properly', function () {
    expect(config('semapon.currency'))->toBe('IDR');
    expect(config('semapon.currency_symbol'))->toBe('Rp');
    expect(config('semapon.plans'))->toHaveKeys(['starter', 'business', 'pro']);
});
