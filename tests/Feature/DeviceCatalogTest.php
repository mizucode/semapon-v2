<?php

use App\Livewire\Order\CreateOrder;
use App\Livewire\SuperAdmin\DeviceCatalogManager;
use App\Models\DeviceCatalog;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\DeviceCatalogSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    // Super Admin setup
    $this->superAdmin = User::factory()->create(['name' => 'Root Admin', 'email' => 'admin@semapon.id']);
    $superAdminStore = Store::factory()->create([
        'name' => 'Platform HQ',
        'slug' => 'platform-hq',
        'owner_id' => $this->superAdmin->id,
    ]);
    StoreMembership::create([
        'store_id' => $superAdminStore->id,
        'user_id' => $this->superAdmin->id,
        'role' => 'super_admin',
        'status' => 'active',
    ]);

    // Regular tenant owner setup
    $this->ownerUser = User::factory()->create();
    $this->store = Store::factory()->create([
        'name' => 'Klinik HP Test',
        'slug' => 'klinik-hp-test',
        'owner_id' => $this->ownerUser->id,
        'plan' => 'business',
    ]);
    $this->ownerMembership = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => $this->ownerUser->id,
        'role' => 'owner',
        'status' => 'active',
    ]);
});

test('super admin can view device catalog manager and see seeded devices', function () {
    $this->seed(DeviceCatalogSeeder::class);

    $this->actingAs($this->superAdmin)
        ->get(route('superadmin.devices'))
        ->assertStatus(200)
        ->assertSee('Master Katalog Smartphone')
        ->assertSee('iPhone 15 Pro');

    // Test livewire search for Samsung
    Livewire::actingAs($this->superAdmin)
        ->test(DeviceCatalogManager::class)
        ->set('search', 'S24 Ultra')
        ->assertSee('Galaxy S24 Ultra');
});

test('regular tenant user cannot access superadmin device catalog page', function () {
    session(['active_store_id' => $this->store->id]);
    app(TenantContext::class)->setStore($this->store, $this->ownerMembership);

    $this->actingAs($this->ownerUser)
        ->get(route('superadmin.devices'))
        ->assertStatus(403);
});

test('super admin can create, update, and delete device catalog entry', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(DeviceCatalogManager::class)
        ->call('openCreateModal')
        ->set('brand', 'Nothing')
        ->set('model_name', 'Phone (2a)')
        ->set('series', 'Phone 2')
        ->set('chipset', 'Dimensity 7200 Pro')
        ->set('specs_summary', 'AMOLED 120Hz, Dimensity 7200 Pro, 50MP OIS')
        ->set('default_colors_text', 'Black, White, Milk')
        ->set('default_variants_text', '8/128, 12/256')
        ->set('is_verified', true)
        ->call('save')
        ->assertHasNoErrors();

    $device = DeviceCatalog::where('model_name', 'Phone (2a)')->first();
    expect($device)->not->toBeNull()
        ->and($device->brand)->toBe('Nothing')
        ->and($device->default_colors)->toBe(['Black', 'White', 'Milk'])
        ->and($device->default_variants)->toBe(['8/128', '12/256']);

    // Update
    Livewire::actingAs($this->superAdmin)
        ->test(DeviceCatalogManager::class)
        ->call('editDevice', $device->id)
        ->set('chipset', 'Dimensity 7200 Pro+')
        ->call('save')
        ->assertHasNoErrors();

    expect($device->fresh()->chipset)->toBe('Dimensity 7200 Pro+');

    // Delete
    Livewire::actingAs($this->superAdmin)
        ->test(DeviceCatalogManager::class)
        ->call('deleteDevice', $device->id);

    expect(DeviceCatalog::find($device->id))->toBeNull();
});

test('order intake form can autocomplete device from catalog and pick suggestions', function () {
    session(['active_store_id' => $this->store->id]);
    app(TenantContext::class)->setStore($this->store, $this->ownerMembership);

    $catalogDevice = DeviceCatalog::create([
        'brand' => 'Apple',
        'model_name' => 'iPhone 13',
        'series' => 'iPhone 13 Series',
        'chipset' => 'Apple A15 Bionic',
        'specs_summary' => 'Super Retina XDR OLED 6.1", A15 Bionic, Dual 12MP',
        'default_colors' => ['Midnight', 'Starlight', 'Blue', 'Pink', 'Green'],
        'default_variants' => ['128 GB', '256 GB', '512 GB'],
        'is_verified' => true,
    ]);

    Livewire::actingAs($this->ownerUser)
        ->test(CreateOrder::class)
        ->set('device_catalog_search', 'iPhone 13')
        ->assertSee('iPhone 13')
        ->call('selectCatalogDevice', $catalogDevice->id)
        ->assertSet('device_brand', 'Apple')
        ->assertSet('device_model', 'iPhone 13')
        ->assertSet('suggested_colors', ['Midnight', 'Starlight', 'Blue', 'Pink', 'Green'])
        ->call('pickSuggestedColor', 'Midnight')
        ->assertSet('device_color', 'Midnight')
        ->call('pickSuggestedVariant', '256 GB')
        ->assertSet('device_variant', '256 GB');
});
