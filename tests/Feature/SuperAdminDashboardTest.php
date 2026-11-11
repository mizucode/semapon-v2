<?php

use App\Livewire\SuperAdmin\SuperAdminDashboard;
use App\Models\Role;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\User;
use App\Services\SuperAdminService;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PlanSeeder::class);

    // Create a Super Admin user
    $this->superAdmin = User::factory()->create(['name' => 'Root Admin', 'email' => 'admin@semapon.id']);
    $superAdminStore = Store::factory()->create([
        'name' => 'Platform HQ',
        'slug' => 'platform-hq',
        'owner_id' => $this->superAdmin->id,
    ]);
    $superRole = Role::where('name', 'super_admin')->first();

    StoreMembership::create([
        'store_id' => $superAdminStore->id,
        'user_id' => $this->superAdmin->id,
        'role' => 'super_admin',
        'status' => 'active',
    ]);

    // Create standard store owner
    $this->normalOwner = User::factory()->create(['name' => 'Owner Normal']);
    $this->normalStore = Store::factory()->create([
        'name' => 'Toko HP Normal',
        'slug' => 'toko-hp-normal',
        'owner_id' => $this->normalOwner->id,
        'plan' => 'business',
        'status' => 'active',
    ]);
    StoreMembership::create([
        'store_id' => $this->normalStore->id,
        'user_id' => $this->normalOwner->id,
        'role' => 'owner',
        'status' => 'active',
    ]);
});

test('super admin can view platform overview dashboard', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('superadmin.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Platform Control Center');
    $response->assertSee('Toko HP Normal');
});

test('normal store owner cannot access super admin platform dashboard', function () {
    $response = $this->actingAs($this->normalOwner)
        ->get(route('superadmin.dashboard'));

    $response->assertStatus(403);
});

test('unauthenticated guest is redirected to login', function () {
    $response = $this->get(route('superadmin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('super admin can suspend and reactivate tenant stores', function () {
    $this->actingAs($this->superAdmin);

    // Initially active
    expect($this->normalStore->status)->toBe('active');

    // Suspend
    Livewire::test(SuperAdminDashboard::class)
        ->call('toggleSuspend', $this->normalStore->id)
        ->assertHasNoErrors();

    expect($this->normalStore->fresh()->status)->toBe('suspended');

    // Reactivate
    Livewire::test(SuperAdminDashboard::class)
        ->call('toggleSuspend', $this->normalStore->id)
        ->assertHasNoErrors();

    expect($this->normalStore->fresh()->status)->toBe('active');
});

test('super admin service accurately aggregates platform metrics', function () {
    $adminService = app(SuperAdminService::class);
    $overview = $adminService->getPlatformOverview();

    // 2 stores created in beforeEach (Platform HQ + Toko HP Normal)
    expect($overview['total_tenants'])->toBe(2);
    expect($overview['active_tenants'])->toBe(2);
    expect($overview['suspended_tenants'])->toBe(0);
    expect($overview['total_users'])->toBe(2);
});
