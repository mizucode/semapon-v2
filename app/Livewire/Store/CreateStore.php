<?php

namespace App\Livewire\Store;

use App\Services\StoreRegistrationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('components.layouts.auth', ['title' => 'Buat Toko Servis Baru', 'subtitle' => 'Langkah awal untuk mulai mengelola operasional servis HP Anda'])]
class CreateStore extends Component
{
    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('nullable|string|max:20')]
    public string $phone = '';

    #[Rule('nullable|string|email|max:255')]
    public string $email = '';

    #[Rule('nullable|string|max:500')]
    public string $address = '';

    public function mount(): void
    {
        $user = Auth::user();
        if ($user) {
            $this->phone = $user->phone ?? '';
            $this->email = $user->email ?? '';
        }
    }

    public function createStore(StoreRegistrationService $service): void
    {
        $validated = $this->validate();

        $store = $service->registerStore(Auth::user(), $validated);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Toko Berhasil Dibuat!',
            'message' => "Selamat datang di toko {$store->name}. Periode trial 14 hari Anda aktif.",
        ]);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    public function render()
    {
        return view('livewire.store.create-store');
    }
}
