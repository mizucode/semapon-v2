<div>
    <h3 class="text-lg font-bold text-slate-900 mb-1">Daftar Akun Baru</h3>
    <p class="text-xs text-slate-500 mb-6">Daftarkan akun pemilik untuk mulai mengelola toko servis HP Anda.</p>

    <form wire:submit="register" class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-semibold text-slate-800 mb-2">Nama Lengkap</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model="name"
                    type="text"
                    id="name"
                    required
                    autofocus
                    placeholder="Contoh: Budi Prasetyo"
                    class="block w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('name') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                >
            </div>
            @error('name')
                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

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
            <label for="phone" class="block text-sm font-semibold text-slate-800 mb-2">No. WhatsApp / HP</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <i data-lucide="phone" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model="phone"
                    type="tel"
                    id="phone"
                    required
                    inputmode="numeric"
                    maxlength="16"
                    placeholder="081234567890"
                    x-on:keydown="
                        if ($event.key.length === 1 && !/\d/.test($event.key) && !$event.ctrlKey && !$event.metaKey && !$event.altKey) {
                            $event.preventDefault();
                        }
                    "
                    x-on:paste="
                        $event.preventDefault();
                        let pasted = ($event.clipboardData || window.clipboardData).getData('text') || '';
                        let digits = pasted.replace(/\D/g, '');
                        if (digits.startsWith('628')) {
                            digits = '08' + digits.slice(3);
                        } else if (digits.startsWith('62')) {
                            digits = '0' + digits.slice(2);
                        } else if (digits.startsWith('8')) {
                            digits = '08' + digits.slice(1);
                        } else if (digits.length >= 2 && digits.startsWith('0') && digits[1] !== '8') {
                            digits = '08' + digits.slice(1);
                        } else if (digits.length >= 1 && !digits.startsWith('0')) {
                            digits = '08' + digits;
                        }
                        digits = digits.slice(0, 16);
                        $el.value = digits;
                        $wire.phone = digits;
                    "
                    x-on:input="
                        let val = $el.value.replace(/\D/g, '');
                        if (val.startsWith('628')) {
                            val = '08' + val.slice(3);
                        } else if (val.startsWith('62')) {
                            val = '0' + val.slice(2);
                        } else if (val.startsWith('8')) {
                            val = '08' + val.slice(1);
                        } else if (val.length >= 2 && val.startsWith('0') && val[1] !== '8') {
                            val = '08' + val.slice(1);
                        } else if (val.length >= 1 && !val.startsWith('0')) {
                            val = '08' + val;
                        }
                        val = val.slice(0, 16);
                        if ($el.value !== val) {
                            $el.value = val;
                        }
                        $wire.phone = val;
                    "
                    class="block w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('phone') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                >
            </div>
            @error('phone')
                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-800 mb-2">Kata Sandi</label>
            <div class="relative" x-data="{ show: false }">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model="password"
                    :type="show ? 'text' : 'password'"
                    id="password"
                    required
                    placeholder="Minimal 8 karakter"
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

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-800 mb-2">Konfirmasi Kata Sandi</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model="password_confirmation"
                    type="password"
                    id="password_confirmation"
                    required
                    placeholder="Ulangi kata sandi"
                    class="block w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                >
            </div>
        </div>

        <div class="pt-1">
            <label class="flex items-start space-x-2.5 cursor-pointer">
                <input
                    wire:model="terms"
                    type="checkbox"
                    class="w-4 h-4 mt-0.5 rounded text-primary-600 border-slate-300 focus:ring-primary-500/20"
                >
                <span class="text-2xs text-slate-600 leading-normal">
                    Saya menyetujui <a href="#" class="text-primary-600 underline font-medium">Syarat & Ketentuan Layanan</a> serta Kebijakan Privasi SEMAPON.
                </span>
            </label>
            @error('terms')
                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <x-button
                type="submit"
                variant="primary"
                size="md"
                class="w-full"
                loading="register"
            >
                Daftar Akun Sekarang
            </x-button>
        </div>
    </form>

    <div class="mt-6 pt-6 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-500">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-700 ml-1">
                Masuk di sini
            </a>
        </p>
    </div>
</div>

