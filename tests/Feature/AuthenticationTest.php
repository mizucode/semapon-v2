<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Profile\UserProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('Masuk Akun');
});

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
    $response->assertSee('Daftar Akun Baru');
});

test('new users can register', function () {
    Livewire::test(Register::class)
        ->set('name', 'Budi Santoso')
        ->set('email', 'budi@example.com')
        ->set('phone', '081234567890')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('terms', true)
        ->call('register')
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = User::where('email', 'budi@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->status)->toBe('active');
    expect(strlen($user->id))->toBe(26); // ULID
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

test('users cannot register with invalid data', function () {
    Livewire::test(Register::class)
        ->set('name', '')
        ->set('email', 'not-an-email')
        ->set('phone', '')
        ->set('password', 'short')
        ->set('password_confirmation', 'different')
        ->set('terms', false)
        ->call('register')
        ->assertHasErrors(['name', 'email', 'phone', 'password', 'terms']);

    $this->assertGuest();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'email' => 'teknisi@semapon.com',
        'password' => Hash::make('password123'),
        'status' => 'active',
    ]);

    Livewire::test(Login::class)
        ->set('email', 'teknisi@semapon.com')
        ->set('password', 'password123')
        ->call('login')
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('users cannot authenticate with invalid password', function () {
    User::factory()->create([
        'email' => 'teknisi@semapon.com',
        'password' => Hash::make('password123'),
        'status' => 'active',
    ]);

    Livewire::test(Login::class)
        ->set('email', 'teknisi@semapon.com')
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});

test('inactive or suspended users cannot authenticate', function () {
    User::factory()->create([
        'email' => 'banned@semapon.com',
        'password' => Hash::make('password123'),
        'status' => 'suspended',
    ]);

    Livewire::test(Login::class)
        ->set('email', 'banned@semapon.com')
        ->set('password', 'password123')
        ->call('login')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});

test('users are rate limited after 5 failed login attempts', function () {
    User::factory()->create([
        'email' => 'throttle@semapon.com',
        'password' => Hash::make('password123'),
        'status' => 'active',
    ]);

    $component = Livewire::test(Login::class)
        ->set('email', 'throttle@semapon.com')
        ->set('password', 'wrong-password');

    for ($i = 0; $i < 5; $i++) {
        $component->call('login');
    }

    $component->call('login')
        ->assertHasErrors(['email']);
});

test('authenticated users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('unauthenticated users cannot access protected routes', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/profile')->assertRedirect('/login');
});

test('users can view and update their profile', function () {
    $user = User::factory()->create([
        'name' => 'Nama Lama',
        'email' => 'lama@example.com',
        'phone' => '0811111111',
    ]);

    Livewire::actingAs($user)
        ->test(UserProfile::class)
        ->assertSet('name', 'Nama Lama')
        ->assertSet('email', 'lama@example.com')
        ->set('name', 'Nama Baru')
        ->set('phone', '0822222222')
        ->call('updateProfile')
        ->assertDispatched('toast');

    $user->refresh();
    expect($user->name)->toBe('Nama Baru');
    expect($user->phone)->toBe('0822222222');
});

test('users can update their password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password-123'),
    ]);

    Livewire::actingAs($user)
        ->test(UserProfile::class)
        ->set('current_password', 'old-password-123')
        ->set('new_password', 'new-password-123')
        ->set('new_password_confirmation', 'new-password-123')
        ->call('updatePassword')
        ->assertDispatched('toast');

    $user->refresh();
    expect(Hash::check('new-password-123', $user->password))->toBeTrue();
});
