<div class="space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Profil & Keamanan</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola data akun pribadi, kata sandi, dan status perangkat yang terhubung.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 1. Informasi Profil -->
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Informasi Akun" subtitle="Perbarui nama lengkap, email, dan nomor kontak Anda">
                <form wire:submit="updateProfile" class="space-y-4">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                        <input
                            wire:model="name"
                            type="text"
                            id="name"
                            required
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('name') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                        >
                        @error('name')
                            <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                            <input
                                wire:model="email"
                                type="email"
                                id="email"
                                required
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('email') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                            >
                            @error('email')
                                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1.5">No. WhatsApp / HP</label>
                            <input
                                wire:model="phone"
                                type="tel"
                                id="phone"
                                required
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('phone') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                            >
                            @error('phone')
                                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <x-button
                            type="submit"
                            variant="primary"
                            size="sm"
                            loading="updateProfile"
                            icon="save"
                        >
                            Simpan Perubahan
                        </x-button>
                    </div>
                </form>
            </x-card>

            <!-- 2. Ubah Kata Sandi -->
            <x-card title="Ubah Kata Sandi" subtitle="Pastikan akun Anda menggunakan kata sandi yang kuat dan unik">
                <form wire:submit="updatePassword" class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi Saat Ini</label>
                        <input
                            wire:model="current_password"
                            type="password"
                            id="current_password"
                            required
                            placeholder="••••••••"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('current_password') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                        >
                        @error('current_password')
                            <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password" class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi Baru</label>
                            <input
                                wire:model="new_password"
                                type="password"
                                id="new_password"
                                required
                                placeholder="Minimal 8 karakter"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('new_password') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                            >
                            @error('new_password')
                                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Sandi Baru</label>
                            <input
                                wire:model="new_password_confirmation"
                                type="password"
                                id="new_password_confirmation"
                                required
                                placeholder="Ulangi kata sandi"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                            >
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <x-button
                            type="submit"
                            variant="secondary"
                            size="sm"
                            loading="updatePassword"
                            icon="key-round"
                        >
                            Perbarui Kata Sandi
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>

        <!-- 3. Sidebar Akun & Keamanan Sesi -->
        <div class="space-y-6">
            <x-card title="Status Akun">
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Status Akun:</span>
                        <x-badge variant="success">Aktif</x-badge>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">User ID (ULID):</span>
                        <span class="font-mono text-3xs text-slate-600 truncate max-w-[140px]">{{ $user->id ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Terdaftar Sejak:</span>
                        <span class="text-slate-700 font-medium">{{ $user->created_at?->format('d M Y') ?? '-' }}</span>
                    </div>
                </div>
            </x-card>

            <x-card title="Keamanan Sesi Perangkat" subtitle="Keluarkan akun dari semua perangkat lain yang pernah login">
                <form wire:submit="logoutOtherDevices" class="space-y-3">
                    <p class="text-2xs text-slate-500 leading-relaxed">
                        Jika Anda menduga akun Anda dibuka di tempat lain, masukkan kata sandi Anda saat ini untuk mengakhiri semua sesi lainnya.
                    </p>
                    <div>
                        <input
                            wire:model="logout_other_password"
                            type="password"
                            required
                            placeholder="Kata sandi untuk konfirmasi"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('logout_other_password') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                        >
                        @error('logout_other_password')
                            <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-button
                        type="submit"
                        variant="danger"
                        size="sm"
                        class="w-full"
                        loading="logoutOtherDevices"
                        icon="shield-alert"
                    >
                        Keluarkan Perangkat Lain
                    </x-button>
                </form>
            </x-card>
        </div>
    </div>
</div>

