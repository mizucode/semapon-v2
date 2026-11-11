<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('components.layouts.auth', ['title' => 'Lupa Kata Sandi', 'subtitle' => 'Kami akan membantu Anda memulihkan akses ke akun Anda'])]
class ForgotPassword extends Component
{
    #[Rule('required|email')]
    public string $email = '';

    public ?string $status = null;

    public function sendResetLink(): void
    {
        $this->validate();

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->status = __($status);
            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Tautan Terkirim',
                'message' => 'Tautan reset kata sandi telah dikirim ke alamat email Anda.',
            ]);
        } else {
            $this->addError('email', __($status));
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
