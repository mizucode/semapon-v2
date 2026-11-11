<?php

use App\Livewire\Store\StoreSettings;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Services\StoreRegistrationService;
use App\Services\TenantContext;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// Create an inline model to test BelongsToTenant trait and TenantScope
class DummyTenantItem extends Model
{
    use BelongsToTenant;

    protected $table = 'dummy_tenant_items';

    protected $fillable = ['store_id', 'title'];
}

beforeEach(function () {
    Schema::create('dummy_tenant_items', function (Blueprint $table) {
        $table->id();
        $table->string('store_id');
        $table->string('title');
        $table->timestamps();
    });
});

test('owner can register a new store and receive 14 days trial', function () {
    $owner = User::factory()->create();

    $service = app(StoreRegistrationService::class);
    $store = $service->registerStore($owner, [
        'name' => 'Kurnia Servis HP',
        'phone' => '081233445566',
        'address' => 'Jl. Merdeka No. 1',
    ]);

    expect($store)->not->toBeNull();
    expect($store->name)->toBe('Kurnia Servis HP');
    expect($store->slug)->toBe('kurnia-servis-hp');
    expect($store->status)->toBe('active');
    expect($store->plan)->toBe('starter');
    expect($store->trial_ends_at)->not->toBeNull();
    expect($store->trial_ends_at->isFuture())->toBeTrue();

    // Verify ownership and membership
    expect($store->owner_id)->toBe($owner->id);
    $membership = StoreMembership::where('store_id', $store->id)
        ->where('user_id', $owner->id)
        ->first();

    expect($membership)->not->toBeNull();
    expect($membership->role)->toBe('owner');
    expect($membership->status)->toBe('active');
});

test('duplicate store names generate unique slugs', function () {
    $owner1 = User::factory()->create();
    $owner2 = User::factory()->create();
    $service = app(StoreRegistrationService::class);

    $store1 = $service->registerStore($owner1, ['name' => 'Abadi Cell']);
    $store2 = $service->registerStore($owner2, ['name' => 'Abadi Cell']);

    expect($store1->slug)->toBe('abadi-cell');
    expect($store2->slug)->toBe('abadi-cell-1');
});

test('user without a store is redirected to store creation', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('stores.create'));
});

test('resolve tenant middleware sets tenant context and permits dashboard access', function () {
    $owner = User::factory()->create();
    $service = app(StoreRegistrationService::class);
    $store = $service->registerStore($owner, ['name' => 'Mitra Smartphone']);

    $response = $this->actingAs($owner)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Mitra Smartphone');

    $tenantContext = app(TenantContext::class);
    expect($tenantContext->getStoreId())->toBe($store->id);
    expect($tenantContext->getRole())->toBe('owner');
});

test('tenant isolation ensures store A cannot access store B data', function () {
    $ownerA = User::factory()->create();
    $ownerB = User::factory()->create();
    $service = app(StoreRegistrationService::class);

    $storeA = $service->registerStore($ownerA, ['name' => 'Toko A']);
    $storeB = $service->registerStore($ownerB, ['name' => 'Toko B']);

    $tenantContext = app(TenantContext::class);

    // 1. Act as Store A
    $tenantContext->setStore($storeA);
    DummyTenantItem::create(['title' => 'Item Toko A']);

    // 2. Act as Store B
    $tenantContext->setStore($storeB);
    DummyTenantItem::create(['title' => 'Item Toko B']);

    // Query under Store B context
    $itemsStoreB = DummyTenantItem::all();
    expect($itemsStoreB)->toHaveCount(1);
    expect($itemsStoreB->first()->title)->toBe('Item Toko B');

    // Query under Store A context
    $tenantContext->setStore($storeA);
    $itemsStoreA = DummyTenantItem::all();
    expect($itemsStoreA)->toHaveCount(1);
    expect($itemsStoreA->first()->title)->toBe('Item Toko A');

    // Bypass scope returns all records
    expect(DummyTenantItem::withoutTenant()->count())->toBe(2);
});

test('owner can update store settings via livewire component', function () {
    $owner = User::factory()->create();
    $service = app(StoreRegistrationService::class);
    $store = $service->registerStore($owner, ['name' => 'Konter Awal']);

    Livewire::actingAs($owner)
        ->test(StoreSettings::class)
        ->assertSet('name', 'Konter Awal')
        ->set('name', 'Konter Berkah Pro')
        ->set('phone', '0899887766')
        ->set('default_warranty_days', 60)
        ->set('thermal_paper_width', 80)
        ->call('updateStore')
        ->assertDispatched('toast');

    $store->refresh();
    expect($store->name)->toBe('Konter Berkah Pro');
    expect($store->phone)->toBe('0899887766');
    expect($store->getSetting('default_warranty_days'))->toBe(60);
    expect($store->getSetting('thermal_paper_width'))->toBe(80);
});
