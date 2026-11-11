<div>
    <h3 class="text-lg font-bold text-slate-900 mb-1">Lupa Kata Sandi</h3>
    <p class="text-xs text-slate-500 mb-6">Masukkan alamat email akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.</p>

    @if ($status)
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
            {{ $status }}
        </div>
    @endif

    <form wire:submit="sendResetLink" class="space-y-4">
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email Terdaftar</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model="email"
                    type="email"
                    id="email"
                    required
                    autofocus
                    placeholder="nama@email.com"
                    class="block w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('email') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                >
            </div>
            @error('email')
                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <x-button
                type="submit"
                variant="primary"
                size="md"
                class="w-full"
                loading="sendResetLink"
            >
                Kirim Tautan Pemulihan
            </x-button>
        </div>
    </form>

    <div class="mt-6 pt-6 border-t border-slate-100 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i>
            Kembali ke Halaman Masuk
        </a>
    </div>
</div>

