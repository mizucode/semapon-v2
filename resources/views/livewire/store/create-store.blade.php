<div>
    <h3 class="text-lg font-bold text-slate-900 mb-1">Daftarkan Toko / Konter Anda</h3>
    <p class="text-xs text-slate-500 mb-6">Isi informasi toko Anda di bawah ini untuk memulai masa uji coba gratis (14 Hari Trial).</p>

    <form wire:submit="createStore" class="space-y-4">
        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Toko / Konter HP</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i data-lucide="store" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model="name"
                    type="text"
                    id="name"
                    required
                    autofocus
                    placeholder="Contoh: Maju Jaya Cell & Service"
                    class="block w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('name') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                >
            </div>
            @error('name')
                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1.5">No. WhatsApp Toko</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                    </div>
                    <input
                        wire:model="phone"
                        type="tel"
                        id="phone"
                        placeholder="081234567890"
                        class="block w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('phone') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                    >
                </div>
                @error('phone')
                    <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email Toko (Opsional)</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <input
                        wire:model="email"
                        type="email"
                        id="email"
                        placeholder="toko@email.com"
                        class="block w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('email') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                    >
                </div>
                @error('email')
                    <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="address" class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Lengkap Toko</label>
            <textarea
                wire:model="address"
                id="address"
                rows="3"
                placeholder="Jl. Raya Konter No. 123, Kota / Kabupaten..."
                class="block w-full rounded-xl border border-slate-200 p-4 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('address') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
            ></textarea>
            @error('address')
                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="p-3.5 bg-primary-50/70 border border-primary-100 rounded-2xl flex items-start space-x-3 text-primary-900">
            <div class="shrink-0 mt-0.5">
                <i data-lucide="sparkles" class="w-4 h-4 text-primary-600"></i>
            </div>
            <p class="text-2xs leading-relaxed text-primary-800">
                Pendaftaran toko Anda otomatis menyertakan <strong>14 Hari Trial Gratis</strong> dengan akses penuh ke manajemen order, pelacakan servis pelanggan, dan fitur keuangan.
            </p>
        </div>

        <div class="pt-2">
            <x-button
                type="submit"
                variant="primary"
                size="md"
                class="w-full"
                loading="createStore"
            >
                Simpan & Masuk ke Dashboard
            </x-button>
        </div>
    </form>
</div>

