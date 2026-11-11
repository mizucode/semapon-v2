<div>
    <h3 class="text-lg font-bold text-slate-900 mb-1">Buat Kata Sandi Baru</h3>
    <p class="text-xs text-slate-500 mb-6">Silakan masukkan kata sandi baru untuk akun Anda.</p>

    <form wire:submit="resetPassword" class="space-y-4">
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model="email"
                    type="email"
                    id="email"
                    required
                    readonly
                    class="block w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 py-3 text-sm text-slate-600 focus:outline-hidden"
                >
            </div>
            @error('email')
                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi Baru</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model="password"
                    type="password"
                    id="password"
                    required
                    autofocus
                    placeholder="Minimal 8 karakter"
                    class="block w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('password') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                >
            </div>
            @error('password')
                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Kata Sandi Baru</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model="password_confirmation"
                    type="password"
                    id="password_confirmation"
                    required
                    placeholder="Ulangi kata sandi baru"
                    class="block w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                >
            </div>
        </div>

        <div class="pt-2">
            <x-button
                type="submit"
                variant="primary"
                size="md"
                class="w-full"
                loading="resetPassword"
            >
                Simpan Kata Sandi Baru
            </x-button>
        </div>
    </form>
</div>

