<?php

namespace App\Livewire\Auth;

use App\Livewire\Order\CreateOrder;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth', ['title' => 'Daftar Akun SEMAPON', 'subtitle' => 'Mulai kelola konter servis HP Anda lebih rapi dan profesional'])]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $terms = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'terms.accepted' => 'Anda wajib menyetujui syarat & ketentuan SEMAPON.',
        ];
    }

    public function updatedPhone(string $value): void
    {
        $normalized = CreateOrder::normalizePhoneNumber($value);
        if ($this->phone !== $normalized) {
            $this->phone = $normalized;
        }
    }

    public function register(): void
    {
        $this->phone = CreateOrder::normalizePhoneNumber($this->phone);
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'status' => 'active',
        ]);

        event(new Registered($user));

        Auth::login($user);
        session()->regenerate();

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
