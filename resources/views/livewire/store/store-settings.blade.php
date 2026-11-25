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
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
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
                        <div>
                            <label for="store_code" class="block text-xs font-semibold text-slate-700 mb-1.5">Kode Toko (ID Nota)</label>
                            <input
                                wire:model="store_code"
                                type="text"
                                id="store_code"
                                required
                                maxlength="6"
                                placeholder="A5T5"
                                class="block w-full uppercase font-bold rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors @error('store_code') border-rose-300 bg-rose-50/30 text-rose-900 @enderror"
                            >
                            @error('store_code')
                                <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                            <span class="block mt-1 text-3xs text-slate-400">Contoh: ORD-{{ strtoupper($store_code ?: 'A5T5') }}-{{ date('Y') }}-0001</span>
                        </div>
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

            <!-- Pengaturan Garansi Toko -->
            <x-card title="Pengaturan Garansi Toko" subtitle="Konfigurasi masa garansi bawaan serta syarat & ketentuan klaim garansi servis">
                <div class="space-y-5">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="default_warranty_days" class="block text-xs font-semibold text-slate-700">Masa Garansi Bawaan (Hari)</label>
                            <span class="text-2xs text-slate-400">Pilih cepat durasi:</span>
                        </div>

                        <!-- Preset duration buttons -->
                        <div class="flex flex-wrap gap-1.5 mb-2.5">
                            <button
                                type="button"
                                wire:click="$set('default_warranty_days', 0)"
                                class="px-2.5 py-1 text-2xs font-medium rounded-lg border transition cursor-pointer {{ $default_warranty_days === 0 ? 'bg-primary-50 border-primary-300 text-primary-700 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                            >
                                0 Hari (Tanpa Garansi)
                            </button>
                            <button
                                type="button"
                                wire:click="$set('default_warranty_days', 7)"
                                class="px-2.5 py-1 text-2xs font-medium rounded-lg border transition cursor-pointer {{ $default_warranty_days === 7 ? 'bg-primary-50 border-primary-300 text-primary-700 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                            >
                                7 Hari (1 Minggu)
                            </button>
                            <button
                                type="button"
                                wire:click="$set('default_warranty_days', 14)"
                                class="px-2.5 py-1 text-2xs font-medium rounded-lg border transition cursor-pointer {{ $default_warranty_days === 14 ? 'bg-primary-50 border-primary-300 text-primary-700 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                            >
                                14 Hari (2 Minggu)
                            </button>
                            <button
                                type="button"
                                wire:click="$set('default_warranty_days', 30)"
                                class="px-2.5 py-1 text-2xs font-medium rounded-lg border transition cursor-pointer {{ $default_warranty_days === 30 ? 'bg-primary-50 border-primary-300 text-primary-700 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                            >
                                30 Hari (1 Bulan)
                            </button>
                            <button
                                type="button"
                                wire:click="$set('default_warranty_days', 60)"
                                class="px-2.5 py-1 text-2xs font-medium rounded-lg border transition cursor-pointer {{ $default_warranty_days === 60 ? 'bg-primary-50 border-primary-300 text-primary-700 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                            >
                                60 Hari (2 Bulan)
                            </button>
                            <button
                                type="button"
                                wire:click="$set('default_warranty_days', 90)"
                                class="px-2.5 py-1 text-2xs font-medium rounded-lg border transition cursor-pointer {{ $default_warranty_days === 90 ? 'bg-primary-50 border-primary-300 text-primary-700 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                            >
                                90 Hari (3 Bulan)
                            </button>
                        </div>

                        <div class="relative">
                            <input
                                wire:model="default_warranty_days"
                                type="number"
                                id="default_warranty_days"
                                min="0"
                                max="365"
                                placeholder="30"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 font-bold focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                            >
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Hari</span>
                        </div>
                        @error('default_warranty_days')
                            <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-2xs text-slate-400">Masa garansi bawaan yang otomatis diterapkan saat membuat nota order baru atau memilih layanan.</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="warranty_terms" class="block text-xs font-semibold text-slate-700">Syarat & Ketentuan Garansi Toko (S&K)</label>
                            <button
                                type="button"
                                wire:click="loadDefaultWarrantyTerms"
                                class="text-2xs font-semibold text-primary-600 hover:text-primary-700 hover:underline inline-flex items-center gap-1 cursor-pointer"
                                title="Muat template standar ketentuan garansi"
                            >
                                <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                <span>Gunakan Template Standar</span>
                            </button>
                        </div>
                        <textarea
                            wire:model="warranty_terms"
                            id="warranty_terms"
                            rows="5"
                            placeholder="Tuliskan syarat klaim garansi toko Anda..."
                            class="block w-full rounded-xl border border-slate-200 p-4 text-xs sm:text-sm text-slate-800 leading-relaxed focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
                        ></textarea>
                        @error('warranty_terms')
                            <p class="mt-1 text-2xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-2xs text-slate-400">Ketentuan ini akan otomatis dicetak pada nota servis pelanggan dan halaman pelacakan status perbaikan online.</p>
                    </div>
                </div>
            </x-card>

            <x-card title="Kustomisasi Cetak Nota & Struk" subtitle="Informasi yang tertera pada nota cetak tanda terima dan struk kasir">
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
                        <span class="font-medium text-slate-800 bg-slate-100 px-2 py-0.5 rounded-md text-3xs">{{ $store->slug ?? '-' }}</span>
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
                        <span class="text-3xs text-slate-600 truncate max-w-[140px]">{{ $store->id ?? '-' }}</span>
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

