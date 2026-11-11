<div class="space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Profil Toko</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Sesuaikan identitas konter, logo toko, cetak nota, dan masa garansi bawaan.</p>
    </div>

    <form wire:submit="updateStore" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Store Info -->
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Informasi Identitas Toko">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Toko / Usaha Servis</label>
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
                            <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1.5">No. Telepon / WhatsApp</label>
                            <input
                                wire:model="phone"
                                type="tel"
                                id="phone"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                            >
                        </div>
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email Toko</label>
                            <input
                                wire:model="email"
                                type="email"
                                id="email"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="address" class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Konter</label>
                        <textarea
                            wire:model="address"
                            id="address"
                            rows="2"
                            class="block w-full rounded-xl border border-slate-200 p-4 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                        ></textarea>
                    </div>
                </div>
            </x-card>

            <x-card title="Kustomisasi Nota & Garansi" subtitle="Informasi yang tertera pada nota cetak tanda terima dan struk kasir">
                <div class="space-y-4">
                    <div>
                        <label for="receipt_header" class="block text-xs font-semibold text-slate-700 mb-1.5">Judul / Header Nota</label>
                        <input
                            wire:model="receipt_header"
                            type="text"
                            id="receipt_header"
                            placeholder="Contoh: Maju Cell - Servis Bergaransi"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                        >
                    </div>

                    <div>
                        <label for="receipt_footer" class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan Kaki / Footer Nota</label>
                        <textarea
                            wire:model="receipt_footer"
                            id="receipt_footer"
                            rows="2"
                            placeholder="Barang yang diservis wajib diambil maksimal 30 hari..."
                            class="block w-full rounded-xl border border-slate-200 p-4 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="default_warranty_days" class="block text-xs font-semibold text-slate-700 mb-1.5">Garansi Bawaan (Hari)</label>
                            <input
                                wire:model="default_warranty_days"
                                type="number"
                                id="default_warranty_days"
                                min="0"
                                max="365"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                            >
                        </div>
                        <div>
                            <label for="thermal_paper_width" class="block text-xs font-semibold text-slate-700 mb-1.5">Ukuran Kertas Thermal Struk</label>
                            <select
                                wire:model="thermal_paper_width"
                                id="thermal_paper_width"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                            >
                                <option value="58">58 mm (Printer Kasir Mini)</option>
                                <option value="80">80 mm (Printer Standar)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Sidebar Store Logo & Info -->
        <div class="space-y-6">
            <x-card title="Logo Toko">
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview Logo">
                            @elseif ($current_logo_path)
                                <img src="{{ asset('storage/' . $current_logo_path) }}" class="w-full h-full object-cover" alt="Logo Toko">
                            @else
                                <i data-lucide="image" class="w-7 h-7 text-slate-400"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <label for="logo_upload" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl cursor-pointer shadow-2xs transition-colors">
                                <i data-lucide="upload" class="w-3.5 h-3.5 mr-1.5 text-slate-500"></i>
                                Unggah Logo
                            </label>
                            <input
                                wire:model="logo"
                                type="file"
                                id="logo_upload"
                                accept="image/*"
                                class="sr-only"
                            >
                            <p class="text-3xs text-slate-400 mt-1">PNG, JPG, max 2MB.</p>
                        </div>
                    </div>
                    @error('logo')
                        <p class="text-2xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </x-card>

            <x-card title="Identitas Tenant">
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Slug Toko:</span>
                        <span class="font-mono font-medium text-slate-800 bg-slate-100 px-2 py-0.5 rounded-md text-3xs">{{ $store->slug ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Paket Langganan:</span>
                        <x-badge variant="info">{{ ucfirst($store->plan ?? 'Starter') }}</x-badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Status Toko:</span>
                        <x-badge variant="success">{{ ucfirst($store->status ?? 'Aktif') }}</x-badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Store ULID:</span>
                        <span class="font-mono text-3xs text-slate-600 truncate max-w-[140px]">{{ $store->id ?? '-' }}</span>
                    </div>
                </div>
            </x-card>

            <div>
                <x-button
                    type="submit"
                    variant="primary"
                    size="md"
                    class="w-full"
                    loading="updateStore"
                    icon="save"
                >
                    Simpan Semua Pengaturan
                </x-button>
            </div>
        </div>
    </form>
</div>

