<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Profil & Keamanan', 'breadcrumbs' => [['label' => 'Profil & Keamanan']]])]
class UserProfile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public string $logout_other_password = '';

    public function mount(): void
    {
        $user = Auth::user();

        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
        }
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $user->fill($validated);
        $user->save();

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Profil Diperbarui',
            'message' => 'Informasi profil akun Anda berhasil disimpan.',
        ]);
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'new_password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Kata Sandi Diperbarui',
            'message' => 'Kata sandi akun Anda berhasil diubah.',
        ]);
    }

    public function logoutOtherDevices(): void
    {
        $this->validate([
            'logout_other_password' => ['required', 'string', 'current_password'],
        ]);

        Auth::logoutOtherDevices($this->logout_other_password);

        $this->reset('logout_other_password');

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Sesi Lain Dikeluarkan',
            'message' => 'Semua sesi perangkat lain berhasil dikeluarkan.',
        ]);
    }

    public function render()
    {
        return view('livewire.profile.user-profile', [
            'user' => Auth::user(),
        ]);
    }
}
