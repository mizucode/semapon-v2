<?php

use App\Livewire\Mobile\MobileMenu;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => 'budi@semapon.com',
    ]);

    $this->store = Store::factory()->create([
        'owner_id' => $this->user->id,
        'name' => 'Mizutech Phone Care',
        'plan' => 'pro',
        'status' => 'active',
    ]);

    $this->membership = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'role' => 'owner',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    $this->actingAs($this->user);
    session(['current_store_id' => $this->store->id]);
    app(TenantContext::class)->setStore($this->store, $this->membership);
});

test('mobile menu page renders successfully with sections and links', function () {
    $response = $this->get(route('menu'));
    $response->assertOk();
    $response->assertSee('Budi Santoso');
    $response->assertSee('Mizutech Phone Care');
    $response->assertSee('Operasional Servis');
    $response->assertSee('Order Servis');
    $response->assertSee('Pelanggan');
    $response->assertSee('Katalog Layanan');
    $response->assertSee('Produk & Sparepart', false);
    $response->assertSee('Kas & Keuangan', false);
    $response->assertSee('Laporan & Analisis', false);
    $response->assertSee('Pengaturan Toko');
    $response->assertSee('Keluar dari Aplikasi');
});

test('mobile menu livewire component loads properly', function () {
    Livewire::test(MobileMenu::class)
        ->assertOk()
        ->assertSee('Budi Santoso')
        ->assertSee('Mizutech Phone Care');
});
