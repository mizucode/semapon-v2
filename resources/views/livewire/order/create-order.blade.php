<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Penerimaan Servis HP Baru</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Buat tanda terima servis, catat kondisi fisik unit, dan tugaskan teknisi.</p>
        </div>
        <div>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-2xs transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5 text-slate-400"></i>
                Kembali ke Antrean
            </a>
        </div>
    </div>

    <form wire:submit="storeOrder" class="space-y-6">
        <!-- Data Pelanggan -->
        <x-card title="Data Pelanggan">
            <x-slot:action>
                <div class="inline-flex p-1 bg-slate-100 rounded-xl">
                    <button
                        type="button"
                        wire:click="setCustomerMode('existing')"
                        class="px-2.5 sm:px-3.5 py-1.5 rounded-xl text-xs transition-all {{ $customer_mode === 'existing' ? 'bg-white text-slate-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium' }}"
                    >
                        Terdaftar
                    </button>
                    <button
                        type="button"
                        wire:click="setCustomerMode('new')"
                        class="px-2.5 sm:px-3.5 py-1.5 rounded-xl text-xs transition-all {{ $customer_mode === 'new' ? 'bg-white text-slate-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium' }}"
                    >
                        Pelanggan Baru
                    </button>
                </div>
            </x-slot:action>

            @if($customer_mode === 'existing')
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
                                    <span class="px-2 py-0.5 text-3xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">No. HP / WA</span>
                                </div>
                                <p class="text-xs text-slate-600 font-medium mt-0.5">{{ $customer_name }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
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
                                Cari Berdasarkan No. WhatsApp / HP atau Nama Pelanggan *
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
                                        class="w-full flex items-center justify-between p-3 hover:bg-slate-50 text-left transition-colors"
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
                                    wire:click="setCustomerMode('new')"
                                    class="font-bold underline text-amber-900 hover:opacity-80 ml-2 shrink-0"
                                >
                                    + Daftarkan Sebagai Pelanggan Baru
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            @else
                <!-- Form Input Pelanggan Baru -->
                <div class="space-y-4">
                    @if($duplicateCustomer)
                        <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between text-xs text-blue-900">
                            <div>
                                <span class="font-semibold">No. HP {{ $customer_phone }}</span> sudah terdaftar atas nama <strong>{{ $duplicateCustomer->name }}</strong>.
                            </div>
                            <button
                                type="button"
                                wire:click="selectCustomer('{{ $duplicateCustomer->id }}')"
                                class="px-3 py-1 rounded-xl bg-blue-600 text-white font-semibold text-2xs hover:bg-blue-700 shadow-xs shrink-0 ml-3"
                            >
                                Gunakan Pelanggan Ini
                            </button>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- 1. Anchor: No WhatsApp / HP -->
                        <div>
                            <label for="c_phone" class="block text-sm font-semibold text-slate-800 mb-2">
                                No. WhatsApp / HP *
                            </label>
                            <input
                                wire:model.live.debounce.300ms="customer_phone"
                                type="tel"
                                id="c_phone"
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

                        <!-- 2. Nama Pelanggan -->
                        <div>
                            <label for="c_name" class="block text-sm font-semibold text-slate-800 mb-2">Nama Pelanggan *</label>
                            <input
                                wire:model="customer_name"
                                type="text"
                                id="c_name"
                                required
                                placeholder="Contoh: Bpk. Anton"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                            @error('customer_name') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            @endif
        </x-card>

        <!-- Data Perangkat Smartphone -->
        <x-card title="Identitas Perangkat Smartphone">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="d_brand" class="block text-sm font-semibold text-slate-800 mb-2">Merek / Brand *</label>
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
                    <label for="d_model" class="block text-sm font-semibold text-slate-800 mb-2">Model / Tipe HP *</label>
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

                <div>
                    <label for="d_color" class="block text-sm font-semibold text-slate-800 mb-2">Warna Unit</label>
                    <input
                        wire:model="device_color"
                        type="text"
                        id="d_color"
                        placeholder="Hitam, Biru, dll."
                        class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                    >
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
                </div>

                <div>
                    <label for="d_imei" class="block text-sm font-semibold text-slate-800 mb-2">Nomor IMEI / SN (Opsional)</label>
                    <input
                        wire:model="device_imei"
                        type="text"
                        id="d_imei"
                        placeholder="15 Digit IMEI unit..."
                        class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                    >
                </div>
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

                <div class="sm:col-span-2">
                    <label for="d_specs" class="block text-sm font-semibold text-slate-800 mb-2">Spesifikasi Lainnya</label>
                    <input
                        wire:model="device_specs"
                        type="text"
                        id="d_specs"
                        placeholder="Contoh: Chipset Snapdragon 8 Gen 2, 5G, dll."
                        class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                    >
                </div>
            </div>
        </x-card>

        <!-- Keluhan & Kelengkapan -->
        <x-card title="Keluhan Pelanggan & Kelengkapan">
            <div class="space-y-4">
                <div>
                    <label for="complaints" class="block text-sm font-semibold text-slate-800 mb-2">Keluhan Utama Kerusakan *</label>
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
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Kaca Layar</label>
                    <select wire:model="physical_condition.screen_condition" class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden">
                        <option value="Normal">Normal Mulus</option>
                        <option value="Gores Pemakaian">Gores Pemakaian</option>
                        <option value="Retak Rambut">Retak Rambut</option>
                        <option value="Pecah Parah">Pecah Parah</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Tampilan LCD</label>
                    <select wire:model="physical_condition.lcd_condition" class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden">
                        <option value="Normal">Normal Jernih</option>
                        <option value="Bergaris">Bergaris</option>
                        <option value="Ada Tompel / Dot">Ada Tompel / Dot</option>
                        <option value="Blank Hitam">Blank Total</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Kondisi Bodi / Backdoor</label>
                    <select wire:model="physical_condition.body_condition" class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden">
                        <option value="Mulus">Mulus</option>
                        <option value="Lecet Bezel">Lecet Bezel</option>
                        <option value="Penyok / Bengkok">Penyok / Bengkok</option>
                        <option value="Backdoor Pecah">Backdoor Pecah</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Kamera Depan/Belakang</label>
                    <select wire:model="physical_condition.camera_condition" class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden">
                        <option value="Normal">Normal Jernih</option>
                        <option value="Buram / Berdebu">Buram / Berdebu</option>
                        <option value="Kaca Kamera Retak">Kaca Kamera Retak</option>
                        <option value="Mati / Blank">Mati / Blank</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Tombol Fisik</label>
                    <select wire:model="physical_condition.buttons_condition" class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden">
                        <option value="Normal">Normal Empuk</option>
                        <option value="Keras / Mendem">Keras / Mendem</option>
                        <option value="Mati / Copot">Mati / Copot</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Port Charger</label>
                    <select wire:model="physical_condition.charging_condition" class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden">
                        <option value="Normal">Normal Kencang</option>
                        <option value="Longgar">Longgar</option>
                        <option value="Patah / Rusak">Patah / Rusak</option>
                    </select>
                </div>
            </div>
        </x-card>

        <!-- Estimasi Biaya & Penugasan Teknisi -->
        <x-card title="Estimasi Biaya & Penugasan">
            <div class="space-y-5">
                <div>
                    <label for="technician_id" class="block text-sm font-semibold text-slate-800 mb-2">Tugaskan Teknisi</label>
                    <select
                        wire:model="technician_id"
                        id="technician_id"
                        class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                    >
                        <option value="">-- Pilih Teknisi (Antrean Bebas) --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->user->id }}">{{ $tech->user->name }} ({{ $tech->specialization }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="estimated_cost" class="block text-sm font-semibold text-slate-800">Estimasi Biaya Total (Rp)</label>
                        <x-toggle
                            wire:model.live="is_cost_undetermined"
                            label="Tentukan Nanti"
                        />
                    </div>
                    @if($is_cost_undetermined)
                        <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 text-xs text-amber-900 flex items-center justify-between transition-all">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-amber-100/80 flex items-center justify-center text-amber-700 shrink-0">
                                    <i data-lucide="help-circle" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 block">Biaya Menunggu Diagnosa Teknisi</span>
                                    <span class="text-[11px] text-slate-600 block mt-0.5">Biaya awal diset Rp0. Teknisi akan membongkar unit dan konfirmasi biaya ke pelanggan via WhatsApp.</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-2xs font-bold uppercase tracking-wider shrink-0 border border-amber-200/60">
                                Pending
                            </span>
                        </div>
                    @else
                        <input
                            wire:model="estimated_cost"
                            type="text"
                            inputmode="numeric"
                            id="estimated_cost"
                            required
                            placeholder="0"
                            oninput="window.formatRupiahInput(this)"
                            onpaste="setTimeout(() => window.formatRupiahInput(this), 0)"
                            onkeydown="if(!/[0-9]/.test(event.key) && !['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End','Enter','.'].includes(event.key) && !event.ctrlKey && !event.metaKey){ event.preventDefault(); }"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                    @endif
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="estimated_completion_at" class="block text-sm font-semibold text-slate-800">Estimasi Selesai</label>
                        <x-toggle
                            wire:model.live="is_completion_undetermined"
                            label="Tentukan Nanti"
                        />
                    </div>
                    @if($is_completion_undetermined)
                        <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 text-xs text-amber-900 flex items-center justify-between transition-all">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-amber-100/80 flex items-center justify-center text-amber-700 shrink-0">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 block">Estimasi Selesai Menyusul</span>
                                    <span class="text-[11px] text-slate-600 block mt-0.5">Jadwal penyelesaian unit akan disepakati bersama pelanggan setelah kerusakan teridentifikasi.</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-2xs font-bold uppercase tracking-wider shrink-0 border border-amber-200/60">
                                Pending
                            </span>
                        </div>
                    @else
                        <input
                            wire:model="estimated_completion_at"
                            type="datetime-local"
                            id="estimated_completion_at"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                    @endif
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

        <div class="flex items-center justify-end space-x-3 pt-2">
            <x-button type="submit" variant="primary" size="lg" loading="storeOrder" icon="check-circle">
                Simpan & Terbitkan Nota Servis
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

