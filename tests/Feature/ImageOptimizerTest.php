<?php

use App\Livewire\Order\CreateOrder;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Services\ImageOptimizer;
use App\Services\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');

    $this->user = User::factory()->create();
    $this->store = Store::factory()->create([
        'name' => 'Toko Servis Cepat',
        'owner_id' => $this->user->id,
        'plan' => 'business',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'role' => 'owner',
        'status' => 'active',
    ]);

    session(['active_store_id' => $this->store->id]);
    app(TenantContext::class)->setStore($this->store, $membership);
});

test('image optimizer compresses and resizes large images to webp', function () {
    // Create a fake large 2400x1800 image
    $largeImage = UploadedFile::fake()->image('test_phone.jpg', 2400, 1800);

    $storedPath = ImageOptimizer::optimizeAndStore(
        $largeImage,
        'orders/'.$this->store->id,
        'public',
        1600,
        1600,
        80
    );

    // Assert file exists on disk
    Storage::disk('public')->assertExists($storedPath);

    // Assert extension is webp (or jpg)
    expect($storedPath)->toMatch('/\.(webp|jpg)$/');

    // Read stored image to verify resized dimensions
    $fullPath = Storage::disk('public')->path($storedPath);
    $size = getimagesize($fullPath);

    expect($size[0])->toBeLessThanOrEqual(1600)
        ->and($size[1])->toBeLessThanOrEqual(1600);
});

test('create order stores compressed photos when saving order', function () {
    $photo1 = UploadedFile::fake()->image('crack_screen.jpg', 2000, 1500);
    $photo2 = UploadedFile::fake()->image('back_cover.png', 1800, 1800);

    $component = Livewire::actingAs($this->user)
        ->test(CreateOrder::class)
        ->set('customer_mode', 'new')
        ->set('customer_name', 'Rizky Kurniawan')
        ->set('customer_phone', '081299887766')
        ->set('customer_address', 'Jl. Sukajadi No. 12, Bandung')
        ->set('device_brand', 'Samsung')
        ->set('device_model', 'Galaxy A54')
        ->set('device_imei', '861234567890123')
        ->set('complaints', 'Layar retak parah')
        ->set('gallery_photos', [$photo1, $photo2])
        ->call('storeOrder');

    $component->assertHasNoErrors();

    // Verify order was created in DB
    $order = ServiceOrder::where('complaints', 'Layar retak parah')->first();
    expect($order)->not->toBeNull()
        ->and($order->photos)->toBeArray()
        ->and(count($order->photos))->toBe(2);

    foreach ($order->photos as $storedPhoto) {
        Storage::disk('public')->assertExists($storedPhoto);
        expect($storedPhoto)->toMatch('/\.(webp|jpg)$/');

        $fullPath = Storage::disk('public')->path($storedPhoto);
        $size = getimagesize($fullPath);
        expect($size[0])->toBeLessThanOrEqual(1600)
            ->and($size[1])->toBeLessThanOrEqual(1600);
    }
});
