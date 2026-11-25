<div class="space-y-6 pb-16 sm:pb-24">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Tambah Service</h1>
        </div>
        <div>
            <x-button
                href="{{ route('orders.index') }}"
                variant="outline"
                icon="arrow-left"
            >
                Kembali
            </x-button>
        </div>
    </div>

    <form wire:submit="storeOrder" class="space-y-6">
        <!-- Data Pelanggan -->
        <x-card title="Data Pelanggan" x-data="{ mode: $wire.entangle('customer_mode') }">
            <x-slot:action>
                <div class="inline-flex p-1 bg-slate-100 rounded-lg">
                    <button
                        type="button"
                        @click="mode = 'existing'"
                        :class="mode === 'existing' ? 'bg-white text-slate-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 sm:px-3.5 py-1.5 rounded-lg text-xs cursor-pointer select-none transition-colors"
                    >
                        Terdaftar
                    </button>
                    <button
                        type="button"
                        @click="mode = 'new'"
                        :class="mode === 'new' ? 'bg-white text-slate-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 sm:px-3.5 py-1.5 rounded-lg text-xs cursor-pointer select-none transition-colors"
                    >
                        Pelanggan Baru
                    </button>
                </div>
            </x-slot:action>

            <!-- Mode 1: Pelanggan Terdaftar -->
            <div x-show="mode === 'existing'" x-cloak>
                @if($customer_id)
                    <!-- Selected Customer Summary Card -->
                    <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold text-sm shrink-0 border border-primary-100">
                                <i data-lucide="user-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-bold text-slate-900">{{ $customer_phone }}</span>
                                    <span class="px-2 py-0.5 text-3xs font-semibold rounded-full bg-emerald-600 text-white shadow-2xs">No. HP / WA</span>
                                </div>
                                <p class="text-xs text-slate-600 font-medium mt-0.5">{{ $customer_name }}</p>
                                @if($customer_address)
                                    <p class="text-2xs text-slate-500 mt-0.5 flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                        <span>{{ $customer_address }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="mode = 'new'"
                            wire:click="switchToNewCustomer"
                            class="inline-flex items-center px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition-colors shadow-2xs self-end sm:self-center cursor-pointer"
                        >
                            Ganti Pelanggan
                        </button>
                    </div>
                @else
                    <!-- Search by Phone (Anchor) / Name -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-800 mb-2">
                                Cari Berdasarkan No. WhatsApp / HP atau Nama Pelanggan <span class="text-rose-500">*</span>
                            </label>
                            <input
                                wire:model.live.debounce.300ms="customer_search"
                                type="text"
                                placeholder="Ketik nomor WhatsApp / HP (contoh: 0812...) atau nama pelanggan..."
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                        </div>

                        <!-- Live Search Results -->
                        @if($matchingCustomers->isNotEmpty())
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-lg divide-y divide-slate-100 overflow-hidden">
                                @foreach($matchingCustomers as $match)
                                    <button
                                        type="button"
                                        wire:click="selectCustomer('{{ $match->id }}')"
                                        class="w-full flex items-center justify-between p-3 hover:bg-slate-50 text-left transition-colors cursor-pointer"
                                    >
                                        <div>
                                            <span class="text-xs font-bold text-slate-900 block">{{ $match->phone }}</span>
                                            <span class="text-2xs text-slate-500">{{ $match->name }}</span>
                                        </div>
                                        <div class="text-xs text-primary-600 font-semibold">
                                            Pilih
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @elseif(strlen(trim($customer_search)) >= 2)
                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 flex items-center justify-between">
                                <span>Nomor / nama <strong>"{{ $customer_search }}"</strong> tidak ditemukan.</span>
                                <button
                                    type="button"
                                    @click="mode = 'new'"
                                    wire:click="setCustomerMode('new')"
                                    class="font-bold underline text-amber-900 hover:opacity-80 ml-2 shrink-0 cursor-pointer"
                                >
                                    + Daftarkan Sebagai Pelanggan Baru
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Mode 2: Form Input Pelanggan Baru -->
            <div x-show="mode === 'new'" x-cloak class="space-y-4">
                @if($duplicateCustomer)
                    <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between text-xs text-blue-900">
                        <div>
                            <span class="font-semibold">No. HP {{ $customer_phone }}</span> sudah terdaftar atas nama <strong>{{ $duplicateCustomer->name }}</strong>.
                        </div>
                        <button
                            type="button"
                            @click="mode = 'existing'"
                            wire:click="selectCustomer('{{ $duplicateCustomer->id }}')"
                            class="px-3.5 py-1.5 rounded-lg bg-blue-600 text-white font-semibold text-xs hover:bg-blue-700 shadow-xs shrink-0 ml-3 cursor-pointer transition-colors"
                        >
                            Gunakan Pelanggan Ini
                        </button>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-5 sm:gap-y-6">
                    <!-- 1. Nama Pelanggan -->
                    <div>
                        <label for="c_name" class="block text-sm font-semibold text-slate-800 mb-2">Nama Pelanggan <span class="text-rose-500">*</span></label>
                        <input
                            wire:model="customer_name"
                            type="text"
                            id="c_name"
                            :required="mode === 'new'"
                            placeholder="Contoh: Bpk. Anton"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        @error('customer_name') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- 2. No WhatsApp / HP -->
                    <div>
                        <label for="c_phone" class="block text-sm font-semibold text-slate-800 mb-2">
                            No. WhatsApp / HP <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.live.debounce.300ms="customer_phone"
                            type="tel"
                            id="c_phone"
                            :required="mode === 'new'"
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
                                    $wire.customer_phone = digits;
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
                                    $wire.customer_phone = val;
                                "
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                            @error('customer_phone') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- 3. Alamat Pelanggan -->
                        <div class="sm:col-span-2">
                            <label for="c_address" class="block text-sm font-semibold text-slate-800 mb-2">
                                Alamat Pelanggan <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                wire:model="customer_address"
                                id="c_address"
                                rows="2"
                                :required="mode === 'new'"
                                placeholder="Contoh: Jl. Diponegoro No. 15, RT 03/RW 02..."
                                class="block w-full rounded-xl border border-slate-200 p-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            ></textarea>
                            @error('customer_address') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
        </x-card>

        <!-- Data Perangkat Smartphone -->
        <x-card title="Identitas Perangkat Smartphone" subtitle="Pilih dari katalog database atau isi secara manual">
            <div
                class="space-y-5 sm:space-y-6"
                x-data="{
                    open: false,
                    search: $wire.entangle('device_catalog_search'),
                    selectedCatalogId: $wire.entangle('selected_catalog_id'),
                    suggestedColors: $wire.entangle('suggested_colors'),
                    suggestedVariants: $wire.entangle('suggested_variants'),
                    catalogs: {{ \Illuminate\Support\Js::from($allCatalogDevices) }},
                    get results() {
                        if (!this.search || this.search.trim().length < 2) return [];
                        let q = this.search.toLowerCase().trim();
                        return this.catalogs.filter(c => {
                            return (c.model_name && c.model_name.toLowerCase().includes(q)) ||
                                   (c.brand && c.brand.toLowerCase().includes(q)) ||
                                   (c.series && c.series.toLowerCase().includes(q)) ||
                                   (c.chipset && c.chipset.toLowerCase().includes(q));
                        }).slice(0, 8);
                    },
                    select(item) {
                        this.open = false;
                        this.search = item.brand + ' ' + item.model_name;
                        this.selectedCatalogId = item.id;
                        this.suggestedColors = item.default_colors || [];
                        this.suggestedVariants = item.default_variants || [];

                        let brandEl = document.getElementById('d_brand');
                        if (brandEl) {
                            brandEl.value = item.brand;
                            brandEl.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        let modelEl = document.getElementById('d_model');
                        if (modelEl) {
                            modelEl.value = item.model_name;
                            modelEl.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        let specsEl = document.getElementById('device_specs');
                        let specsVal = item.specs_summary || (item.chipset ? 'Chipset: ' + item.chipset : '');
                        if (specsEl) {
                            specsEl.value = specsVal;
                            specsEl.dispatchEvent(new Event('input', { bubbles: true }));
                        }

                        $wire.selectCatalogDevice(item.id);
                    },
                    pickColor(c) {
                        let el = document.getElementById('d_color');
                        if (el) {
                            el.value = c;
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        $wire.pickSuggestedColor(c);
                    },
                    pickVariant(v) {
                        let el = document.getElementById('d_variant');
                        if (el) {
                            el.value = v;
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        $wire.pickSuggestedVariant(v);
                    },
                    clear() {
                        this.open = false;
                        this.search = '';
                        this.selectedCatalogId = null;
                        this.suggestedColors = [];
                        this.suggestedVariants = [];
                        $wire.clearCatalogSelection();
                    }
                }"
            >
                <!-- Search & Autocomplete dari Katalog Smartphone -->
                <div
                    class="relative"
                    @click.outside="open = false"
                >
                    <label class="block text-sm font-semibold text-slate-800 mb-2">
                        Pencarian Cepat Katalog HP
                    </label>

                    <div class="relative">
                        <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input
                            x-model="search"
                            @focus="open = true"
                            @input="open = true"
                            @keydown.escape="open = false"
                            type="text"
                            placeholder="Ketik model atau merek... contoh: iPhone 13, Redmi Note 12, Galaxy A54"
                            class="block w-full pl-11 pr-28 py-3 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        <div
                            x-show="selectedCatalogId"
                            x-cloak
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 flex items-center gap-1 text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full text-xs font-semibold"
                        >
                            <i data-lucide="check" class="w-3.5 h-3.5"></i> Terhubung
                        </div>
                    </div>

                    <!-- Dropdown Hasil Pencarian (Instant Client-Side) -->
                    <div
                        x-show="open && search && search.trim().length >= 2"
                        x-cloak
                        class="absolute z-30 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden divide-y divide-slate-100 max-h-64 overflow-y-auto"
                    >
                        <template x-for="item in results" :key="item.id">
                            <button
                                type="button"
                                @click="select(item)"
                                class="w-full text-left px-4 py-2.5 hover:bg-slate-50 transition flex items-center justify-between group cursor-pointer"
                            >
                                <div>
                                    <div class="text-sm font-semibold text-slate-800 group-hover:text-primary-600 flex items-center gap-2">
                                        <span class="text-2xs font-bold uppercase tracking-wider text-primary-600 bg-primary-50 px-1.5 py-0.5 rounded" x-text="item.brand"></span>
                                        <span x-text="item.model_name"></span>
                                        <span x-show="item.is_verified" class="inline-flex items-center text-blue-500">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>
                                        </span>
                                    </div>
                                    <div class="text-2xs text-slate-400 mt-0.5" x-text="item.specs_summary || (item.chipset ? 'Chipset: ' + item.chipset : 'Referensi Default')"></div>
                                </div>
                                <span class="text-xs text-primary-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                                    Pilih <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </span>
                            </button>
                        </template>

                        <div x-show="results.length === 0" class="px-4 py-3 text-xs text-slate-500 text-center">
                            Tidak ada di katalog referensi. Silakan isi form di bawah secara manual.
                        </div>
                    </div>

                    @if($catalogSuggestions->isNotEmpty())
                        <div class="hidden">
                            @foreach($catalogSuggestions as $item)
                                <span>{{ $item->brand }} {{ $item->model_name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-5 sm:gap-y-6 pt-5 border-t border-slate-100">
                    <div>
                        <label for="d_brand" class="block text-sm font-semibold text-slate-800 mb-2">Merek / Brand <span class="text-rose-500">*</span></label>
                        <input
                            wire:model="device_brand"
                            type="text"
                            id="d_brand"
                            required
                            placeholder="Apple, Samsung, Xiaomi, dll."
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        @error('device_brand') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="d_model" class="block text-sm font-semibold text-slate-800 mb-2">Model / Tipe HP <span class="text-rose-500">*</span></label>
                        <input
                            wire:model="device_model"
                            type="text"
                            id="d_model"
                            required
                            placeholder="iPhone 13, Galaxy A54, dll."
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        @error('device_model') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Nomor IMEI / SN -->
                    <div>
                        <label for="d_imei" class="block text-sm font-semibold text-slate-800 mb-2">Nomor IMEI / SN</label>
                        <input
                            wire:model="device_imei"
                            type="text"
                            id="d_imei"
                            placeholder="15 Digit IMEI unit..."
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        @error('device_imei') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Pola / PIN / Sandi Layar -->
                    <div>
                        <label for="passcode" class="block text-sm font-semibold text-slate-800 mb-2">Pola / PIN / Sandi Layar</label>
                        <input
                            wire:model="passcode"
                            type="text"
                            id="passcode"
                            placeholder="Contoh: PIN 1234 atau Pola Gambar L"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                    </div>

                    <div>
                        <label for="d_color" class="block text-sm font-semibold text-slate-800 mb-2">Warna Unit</label>
                        <input
                            wire:model="device_color"
                            type="text"
                            id="d_color"
                            placeholder="Hitam, Biru, dll."
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        <div x-show="suggestedColors && suggestedColors.length > 0" x-cloak class="flex flex-wrap items-center gap-1.5 mt-2">
                            <span class="text-3xs text-slate-400 font-medium">Saran:</span>
                            <template x-for="c in suggestedColors" :key="c">
                                <button
                                    type="button"
                                    @click="pickColor(c)"
                                    class="text-2xs px-2 py-0.5 rounded-md border border-slate-200 bg-slate-50 hover:bg-primary-50 hover:border-primary-300 hover:text-primary-700 transition cursor-pointer"
                                    x-text="c"
                                ></button>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label for="d_variant" class="block text-sm font-semibold text-slate-800 mb-2">Varian (RAM / Internal)</label>
                        <input
                            wire:model="device_variant"
                            type="text"
                            id="d_variant"
                            placeholder="Contoh: 12/512, 8/256, dll."
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        <div x-show="suggestedVariants && suggestedVariants.length > 0" x-cloak class="flex flex-wrap items-center gap-1.5 mt-2">
                            <span class="text-3xs text-slate-400 font-medium">Saran:</span>
                            <template x-for="v in suggestedVariants" :key="v">
                                <button
                                    type="button"
                                    @click="pickVariant(v)"
                                    class="text-2xs px-2 py-0.5 rounded-md border border-slate-200 bg-slate-50 hover:bg-primary-50 hover:border-primary-300 hover:text-primary-700 transition cursor-pointer"
                                    x-text="v"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <!-- Spesifikasi Lainnya (Textarea) -->
                    <div class="sm:col-span-2">
                        <label for="device_specs" class="block text-sm font-semibold text-slate-800 mb-2">
                            Spesifikasi Lainnya (Opsional)
                        </label>
                        <textarea
                            wire:model="device_specs"
                            id="device_specs"
                            rows="2"
                            placeholder="RAM, Storage, Chipset, atau detail spesifikasi perangkat lainnya..."
                            class="block w-full rounded-xl border border-slate-200 p-3.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-all"
                        ></textarea>
                        @error('device_specs') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Keluhan & Kelengkapan -->
        <x-card title="Keluhan Pelanggan & Kelengkapan">
            <div class="space-y-5 sm:space-y-6">
                <div>
                    <label for="complaints" class="block text-sm font-semibold text-slate-800 mb-2">Keluhan Utama Kerusakan <span class="text-rose-500">*</span></label>
                    <textarea
                        wire:model="complaints"
                        id="complaints"
                        rows="3"
                        required
                        placeholder="Contoh: Layar sentuh blank hitam setelah jatuh, dicas hanya getar..."
                        class="block w-full rounded-xl border border-slate-200 p-4 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                    ></textarea>
                    @error('complaints') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="accessories" class="block text-sm font-semibold text-slate-800 mb-2">Kelengkapan yang Dititipkan</label>
                    <input
                        wire:model="accessories_included"
                        type="text"
                        id="accessories"
                        placeholder="Unit HP Saja, Batangan + Dus, Charger Ori, Case, SIM Card..."
                        class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                    >
                </div>
            </div>
        </x-card>

        <!-- Checklist Kondisi Fisik Saat Masuk -->
        <x-card title="Checklist Kondisi Fisik Awal" subtitle="Catatan kondisi sebelum unit dikerjakan untuk perlindungan konter dan transparansi">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-5 sm:gap-y-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Kaca Layar</label>
                    <x-custom-select
                        wire:model="physical_condition.screen_condition"
                        :options="[
                            'Normal' => 'Normal Mulus',
                            'Gores Pemakaian' => 'Gores Pemakaian',
                            'Retak Rambut' => 'Retak Rambut',
                            'Pecah Parah' => 'Pecah Parah',
                        ]"
                        header="Kondisi Kaca Layar"
                    />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Tampilan LCD</label>
                    <x-custom-select
                        wire:model="physical_condition.lcd_condition"
                        :options="[
                            'Normal' => 'Normal Jernih',
                            'Tidak Bisa Dicek (Matot)' => 'Tidak Bisa Dicek (Matot)',
                            'Bergaris' => 'Bergaris',
                            'Ada Tompel / Dot' => 'Ada Tompel / Dot',
                            'Blank Hitam' => 'Blank Total',
                        ]"
                        header="Kondisi Tampilan LCD"
                    />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Kondisi Bodi / Backdoor</label>
                    <x-custom-select
                        wire:model="physical_condition.body_condition"
                        :options="[
                            'Mulus' => 'Mulus',
                            'Lecet Bezel' => 'Lecet Bezel',
                            'Penyok / Bengkok' => 'Penyok / Bengkok',
                            'Backdoor Pecah' => 'Backdoor Pecah',
                        ]"
                        header="Kondisi Bodi / Backdoor"
                    />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Kamera Depan/Belakang</label>
                    <x-custom-select
                        wire:model="physical_condition.camera_condition"
                        :options="[
                            'Normal' => 'Normal Jernih',
                            'Tidak Bisa Dicek (Matot)' => 'Tidak Bisa Dicek (Matot)',
                            'Buram / Berdebu' => 'Buram / Berdebu',
                            'Kaca Kamera Retak' => 'Kaca Kamera Retak',
                            'Mati / Blank' => 'Mati / Blank',
                        ]"
                        header="Kondisi Kamera"
                    />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Tombol Fisik</label>
                    <x-custom-select
                        wire:model="physical_condition.buttons_condition"
                        :options="[
                            'Normal' => 'Normal Empuk',
                            'Tidak Bisa Ditest (Matot)' => 'Tidak Bisa Ditest (Matot)',
                            'Keras / Mendem' => 'Keras / Mendem',
                            'Mati / Copot' => 'Mati / Copot',
                        ]"
                        header="Kondisi Tombol Fisik"
                    />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Port Charger</label>
                    <x-custom-select
                        wire:model="physical_condition.charging_condition"
                        :options="[
                            'Normal' => 'Normal Kencang',
                            'Tidak Merespons (Matot)' => 'Tidak Merespons (Matot)',
                            'Longgar' => 'Longgar',
                            'Patah / Rusak' => 'Patah / Rusak',
                        ]"
                        header="Kondisi Port Charger"
                    />
                </div>
            </div>

            <!-- Upload Foto Kondisi Unit -->
            <div
                x-data="{
                    isCompressing: false,
                    compressImage(file, maxDim = 1600, quality = 0.8) {
                        return new Promise((resolve) => {
                            if (!file.type || !file.type.match(/^image\//i)) {
                                resolve(file);
                                return;
                            }
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                const img = new Image();
                                img.onload = () => {
                                    let w = img.width;
                                    let h = img.height;
                                    if (w > maxDim || h > maxDim) {
                                        const ratio = Math.min(maxDim / w, maxDim / h);
                                        w = Math.round(w * ratio);
                                        h = Math.round(h * ratio);
                                    }
                                    const canvas = document.createElement('canvas');
                                    canvas.width = w;
                                    canvas.height = h;
                                    const ctx = canvas.getContext('2d');
                                    ctx.drawImage(img, 0, 0, w, h);

                                    canvas.toBlob((blob) => {
                                        if (blob && blob.size < file.size) {
                                            const newName = file.name.replace(/\.[^/.]+$/, '') + '.webp';
                                            const compressedFile = new File([blob], newName, {
                                                type: 'image/webp',
                                                lastModified: Date.now()
                                            });
                                            resolve(compressedFile);
                                        } else {
                                            resolve(file);
                                        }
                                    }, 'image/webp', quality);
                                };
                                img.onerror = () => resolve(file);
                                img.src = e.target.result;
                            };
                            reader.onerror = () => resolve(file);
                            reader.readAsDataURL(file);
                        });
                    },
                    async handleFiles(event, propertyName, isMultiple = false) {
                        const rawFiles = Array.from(event.target.files);
                        if (!rawFiles.length) return;

                        this.isCompressing = true;
                        try {
                            const compressed = await Promise.all(rawFiles.map(f => this.compressImage(f)));
                            if (isMultiple) {
                                @this.uploadMultiple(propertyName, compressed, () => {
                                    this.isCompressing = false;
                                }, () => {
                                    this.isCompressing = false;
                                });
                            } else {
                                @this.upload(propertyName, compressed[0], () => {
                                    this.isCompressing = false;
                                }, () => {
                                    this.isCompressing = false;
                                });
                            }
                        } catch (err) {
                            console.error('Kompresi gambar gagal, mengunggah file biasa', err);
                            this.isCompressing = false;
                        } finally {
                            event.target.value = '';
                        }
                    }
                }"
                class="mt-6 pt-5 border-t border-slate-100"
            >
                <!-- Hidden File Inputs -->
                <input
                    x-ref="cameraInput"
                    type="file"
                    accept="image/*"
                    capture="environment"
                    @change="handleFiles($event, 'camera_photo', false)"
                    class="hidden"
                >
                <input
                    x-ref="galleryInput"
                    type="file"
                    accept="image/*"
                    multiple
                    @change="handleFiles($event, 'gallery_photos', true)"
                    class="hidden"
                >

                <div class="flex items-center justify-between mb-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-800">Foto Kondisi Fisik Unit (Opsional)</label>
                        <p class="text-xs text-slate-400 mt-0.5">Dokumentasi fisik perangkat saat diterima.</p>
                    </div>

                    @if(count($device_photos) > 0)
                        <span class="text-xs font-semibold text-primary-600 bg-primary-50 px-2.5 py-1 rounded-lg shrink-0">
                            {{ count($device_photos) }} foto dipilih
                        </span>
                    @endif
                </div>

                <!-- Grid Foto & Tombol Tambah -->
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    <!-- 1. Thumbnail List of Selected Photos (Tampil di Kiri Duluan) -->
                    @foreach($device_photos as $index => $photo)
                        @php
                            $previewUrl = null;
                            try {
                                if (is_object($photo) && method_exists($photo, 'isPreviewable') && $photo->isPreviewable()) {
                                    $previewUrl = $photo->temporaryUrl();
                                }
                            } catch (\Throwable $e) {
                                $previewUrl = null;
                            }
                        @endphp
                        <div wire:key="device-photo-{{ $index }}-{{ is_object($photo) && method_exists($photo, 'getFilename') ? $photo->getFilename() : $index }}" class="relative group/thumb rounded-xl overflow-hidden border border-slate-200 bg-slate-100 min-h-[110px] flex items-center justify-center">
                            @if($previewUrl)
                                <img src="{{ $previewUrl }}" alt="Kondisi HP" class="w-full h-[110px] object-cover">
                            @else
                                <div class="flex flex-col items-center justify-center p-2 text-slate-400">
                                    <i data-lucide="image" class="w-6 h-6 mb-1 text-slate-400"></i>
                                    <span class="text-3xs text-center truncate max-w-[90px]">{{ is_object($photo) && method_exists($photo, 'getClientOriginalName') ? $photo->getClientOriginalName() : 'Foto #'.($index + 1) }}</span>
                                </div>
                            @endif

                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center">
                                <button
                                    type="button"
                                    wire:click="removeDevicePhoto({{ $index }})"
                                    class="p-1.5 rounded-lg bg-rose-600 text-white hover:bg-rose-700 shadow-xs cursor-pointer transition-transform hover:scale-110"
                                    title="Hapus Foto"
                                >
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach

                    <!-- 2. Tombol Tambah Foto (Berada di Sebelah Kanan Foto) -->
                    <!-- Tombol Kamera -->
                    <button
                        type="button"
                        @click="$refs.cameraInput.click()"
                        class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 border-dashed border-slate-200 hover:border-primary-500 hover:bg-primary-50/40 transition-all cursor-pointer group bg-slate-50/50 min-h-[110px]"
                    >
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                            <i data-lucide="camera" class="w-5 h-5 text-slate-500 group-hover:text-primary-600 transition-colors"></i>
                        </div>
                        <span class="mt-2 text-xs font-medium text-slate-700 group-hover:text-primary-700">Foto</span>
                    </button>

                    <!-- Tombol Galeri -->
                    <button
                        type="button"
                        @click="$refs.galleryInput.click()"
                        class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 border-dashed border-slate-200 hover:border-primary-500 hover:bg-primary-50/40 transition-all cursor-pointer group bg-slate-50/50 min-h-[110px]"
                    >
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                            <i data-lucide="upload" class="w-5 h-5 text-slate-500 group-hover:text-primary-600 transition-colors"></i>
                        </div>
                        <span class="mt-2 text-xs font-medium text-slate-700 group-hover:text-primary-700">Galeri</span>
                    </button>
                </div>

                <!-- Loading indicator saat upload & kompresi -->
                <div x-show="isCompressing" x-cloak class="mt-2.5 text-xs text-primary-600 flex items-center gap-2">
                    <svg class="animate-spin h-3.5 w-3.5 text-primary-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                    <span>Mengompresi dan menyiapkan foto...</span>
                </div>

                <div wire:loading wire:target="camera_photo, gallery_photos" class="mt-2.5 text-xs text-primary-600 flex items-center gap-2">
                    <svg class="animate-spin h-3.5 w-3.5 text-primary-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                    <span>Mengunggah foto perangkat...</span>
                </div>

                @error('camera_photo') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                @error('gallery_photos.*') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </x-card>

        <!-- Estimasi Biaya & Penugasan Teknisi -->
        <x-card title="Estimasi Biaya & Penugasan">
            <div class="space-y-5 sm:space-y-6">
                <div>
                    <label for="technician_id" class="block text-sm font-semibold text-slate-800 mb-2">Tugaskan Teknisi</label>
                    <x-custom-select
                        wire:model="technician_id"
                        :options="['' => '-- Antrean Bebas (Tanpa Teknisi Ditugaskan) --'] + $technicians->mapWithKeys(fn($t) => [$t->user->id => $t->user->name . ' (' . ($t->specialization ?? 'Teknisi') . ')'])->all()"
                        placeholder="-- Antrean Bebas (Tanpa Teknisi Ditugaskan) --"
                        header="Pilih Teknisi Penanggung Jawab"
                    />
                </div>

                <div x-data="{ undeterminedCost: $wire.entangle('is_cost_undetermined') }">
                    <div class="flex items-center justify-between mb-2">
                        <label for="estimated_cost" class="block text-sm font-semibold text-slate-800">Estimasi Biaya</label>
                        <x-toggle
                            x-model="undeterminedCost"
                            label="Tentukan Nanti"
                        />
                    </div>
                    <div x-show="undeterminedCost" x-cloak class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 text-xs text-amber-900 flex items-center justify-between transition-all">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-2xs">
                                <i data-lucide="help-circle" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 block">Pending Estimasi Biaya</span>
                                <span class="text-[11px] text-slate-600 block mt-0.5">Biaya Menunggu Diagnosa Teknisi</span>
                            </div>
                        </div>
                    </div>
                    <div x-show="!undeterminedCost" x-cloak>
                        <input
                            wire:model="estimated_cost"
                            type="text"
                            inputmode="numeric"
                            id="estimated_cost"
                            placeholder="0"
                            oninput="window.formatRupiahInput(this)"
                            onpaste="setTimeout(() => window.formatRupiahInput(this), 0)"
                            onkeydown="if(!/[0-9]/.test(event.key) && !['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End','Enter','.'].includes(event.key) && !event.ctrlKey && !event.metaKey){ event.preventDefault(); }"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                    </div>
                </div>

                <div x-data="{ undeterminedCompletion: $wire.entangle('is_completion_undetermined') }">
                    <div class="flex items-center justify-between mb-2">
                        <label for="estimated_completion_at" class="block text-sm font-semibold text-slate-800">Estimasi Selesai</label>
                        <x-toggle
                            x-model="undeterminedCompletion"
                            label="Tentukan Nanti"
                        />
                    </div>
                    <div x-show="undeterminedCompletion" x-cloak class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 text-xs text-amber-900 flex items-center justify-between transition-all">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-2xs">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 block">Pending Estimasi Selesai</span>
                                <span class="text-[11px] text-slate-600 block mt-0.5">Estimasi Selesai Menyusul</span>
                            </div>
                        </div>
                    </div>
                    <div x-show="!undeterminedCompletion" x-cloak>
                        <input
                            wire:model="estimated_completion_at"
                            type="datetime-local"
                            id="estimated_completion_at"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                    </div>
                </div>

                <div>
                    <label for="down_payment" class="block text-sm font-semibold text-slate-800 mb-2">Uang Muka / DP (Rp)</label>
                    <input
                        wire:model="down_payment"
                        type="text"
                        inputmode="numeric"
                        id="down_payment"
                        required
                        placeholder="0"
                        oninput="window.formatRupiahInput(this)"
                        onpaste="setTimeout(() => window.formatRupiahInput(this), 0)"
                        onkeydown="if(!/[0-9]/.test(event.key) && !['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End','Enter','.'].includes(event.key) && !event.ctrlKey && !event.metaKey){ event.preventDefault(); }"
                        class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                    >
                    <span class="block mt-1.5 text-2xs text-slate-400">Isi 0 jika tidak ada pembayaran di awal</span>
                </div>
            </div>
        </x-card>

        <div class="flex items-center sm:justify-end pt-4">
            <x-button type="submit" variant="primary" size="lg" loading="storeOrder" class="w-full sm:w-auto">
                Buat Orderan
            </x-button>
        </div>
    </form>

    <script>
        if (typeof window.formatRupiahInput !== 'function') {
            window.formatRupiahInput = function (el) {
                if (!el) return;
                const originalLen = el.value.length;
                const originalCursor = el.selectionStart;

                const digits = el.value.replace(/\D/g, '');
                if (!digits) {
                    el.value = '';
                    return;
                }

                const formatted = new Intl.NumberFormat('id-ID').format(digits);
                el.value = formatted;

                if (originalCursor !== null) {
                    const diff = formatted.length - originalLen;
                    const newPos = Math.max(0, originalCursor + diff);
                    el.setSelectionRange(newPos, newPos);
                }
            };
        }
    </script>
</div>

