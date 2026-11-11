<div>
    <h3 class="text-lg font-bold text-slate-900 mb-1">Masuk Akun</h3>
    <p class="text-xs text-slate-500 mb-6">Masukkan email dan password Anda untuk melanjutkan.</p>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-800 mb-2">Alamat Email</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model="email"
                    type="email"
                    id="email"
                    autocomplete="email"
                    required
                    placeholder="nama@email.com"
                    class="block w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('email') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                >
            </div>
            @error('email')
                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-sm font-semibold text-slate-800">Kata Sandi</label>
                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                    Lupa sandi?
                </a>
            </div>
            <div class="relative" x-data="{ show: false }">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model="password"
                    :type="show ? 'text' : 'password'"
                    id="password"
                    autocomplete="current-password"
                    required
                    placeholder="••••••••"
                    class="block w-full rounded-xl border border-slate-200 pl-10 pr-11 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('password') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                >
                <button
                    type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600"
                    tabindex="-1"
                >
                    <i x-show="!show" data-lucide="eye" class="w-4 h-4"></i>
                    <i x-show="show" x-cloak data-lucide="eye-off" class="w-4 h-4"></i>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between pt-1">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input
                    wire:model="remember"
                    type="checkbox"
                    class="w-4 h-4 rounded text-primary-600 border-slate-300 focus:ring-primary-500/20"
                >
                <span class="text-xs text-slate-600">Ingat saya di perangkat ini</span>
            </label>
        </div>

        <div class="pt-2">
            <x-button
                type="submit"
                variant="primary"
                size="md"
                class="w-full"
                loading="login"
            >
                Masuk ke Aplikasi
            </x-button>
        </div>
    </form>

    <div class="mt-6 pt-6 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-500">
            Belum memiliki akun toko?
            <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700 ml-1">
                Daftar sekarang
            </a>
        </p>
    </div>
</div>

