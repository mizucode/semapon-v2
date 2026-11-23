<div class="space-y-6" x-data="{ showPrintBlockedModal: false, showEditWarningModal: false }">
    <!-- Flash Success Notification -->
    @if (session()->has('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-emerald-50 border border-emerald-200/90 rounded-2xl p-4 sm:p-5 flex items-start justify-between gap-3 shadow-2xs"
        >
            <div class="flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-emerald-950">Order Berhasil Diperbarui</h4>
                    <p class="text-xs text-emerald-800 mt-0.5 leading-relaxed">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
            <button
                type="button"
                @click="show = false"
                class="p-1 rounded-lg text-emerald-600 hover:text-emerald-800 hover:bg-emerald-100 transition-colors cursor-pointer"
                title="Tutup pemberitahuan"
            >
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Top Bar / Header -->
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
            <div class="flex items-center gap-2">
                <div
                    x-data="{ copied: false }"
                    @click="navigator.clipboard.writeText('#{{ $order->order_number }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="group inline-flex items-center gap-2 cursor-pointer select-all"
                    title="Klik untuk salin Order ID"
                >
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-none group-hover:text-primary-600 transition-colors">
                        Order ID: #{{ $order->order_number }}
                    </h1>
                    <span class="inline-flex items-center justify-center p-1 rounded-lg text-slate-400 group-hover:text-primary-600 group-hover:bg-primary-50 transition-all">
                        <i x-show="!copied" data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <i x-show="copied" x-cloak data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                    </span>
                    <span
                        x-show="copied"
                        x-cloak
                        x-transition
                        class="text-2xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md"
                    >
                        Tersalin!
                    </span>
                </div>

                <!-- Payment Status Badge (Hanya tampil jika ada status pembayaran pasti) -->
                @if ($order->payment_status === 'paid')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-2xs font-semibold bg-emerald-500 text-white shadow-xs">
                        LUNAS
                    </span>
                @elseif ($order->payment_status === 'partially_paid')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-2xs font-semibold bg-amber-500 text-white shadow-xs">
                        DP TERCATAT
                    </span>
                @elseif (!$order->is_cost_undetermined)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-2xs font-semibold bg-rose-500 text-white shadow-xs">
                        BELUM BAYAR
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <span class="hidden sm:inline text-slate-300 font-light select-none leading-none">|</span>

                <span class="text-xs sm:text-sm text-slate-500 font-normal leading-none">
                    {{ $order->created_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                </span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
            <!-- Public Tracking Link -->
            @if (Route::has('tracking.show'))
                <a
                    href="{{ route('tracking.show', $order->order_number) }}"
                    target="_blank"
                    class="h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap shrink-0"
                    title="Buka Halaman Tracking Pelanggan"
                >
                    <i data-lucide="external-link" class="w-3.5 h-3.5 mr-1.5 shrink-0"></i>
                    <span class="sm:hidden">Tracking</span>
                    <span class="hidden sm:inline">Tracking Pelanggan</span>
                </a>
            @endif

            <!-- WhatsApp Update Link -->
            @if ($order->customer && $order->customer->whatsapp_number)
                @php
                    $trackingUrl = route('tracking.show', $order->order_number);
                    $waText = "Halo kak *{$order->customer->name}*, berikut update perbaikan gadget Anda *({$order->device?->device_type} {$order->device?->brand} {$order->device?->model})* dengan nomor nota *#{$order->order_number}*.\nStatus saat ini: *{$statusLabels[$order->status]}*.\n\nPantau live progres perbaikan di link resmi kami:\n{$trackingUrl}\n\nTerima kasih telah mempercayakan servis kepada kami.";
                    $waLink = "https://wa.me/{$order->customer->whatsapp_number}?text=" . rawurlencode($waText);
                @endphp
                <a
                    href="{{ $waLink }}"
                    target="_blank"
                    class="h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition-colors inline-flex items-center justify-center whitespace-nowrap shrink-0"
                >
                    <i data-lucide="message-circle" class="w-3.5 h-3.5 mr-1.5 shrink-0"></i>
                    <span>Kirim WA</span>
                </a>
            @endif

            <!-- Print Intake / Tanda Terima Link (Bisa dicetak langsung saat unit diterima) -->
            @if (Route::has('orders.print'))
                <a
                    href="{{ route('orders.print', ['id' => $order->id, 'type' => 'intake']) }}"
                    target="_blank"
                    class="h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap shrink-0"
                    title="Cetak Tanda Bukti Penyerahan Unit untuk Pelanggan (Bisa dicetak di awal)"
                >
                    <i data-lucide="file-text" class="w-3.5 h-3.5 mr-1.5 text-slate-500 shrink-0"></i>
                    <span>Tanda Terima</span>
                </a>
            @endif

            <!-- Print Receipt Link -->
            @if (Route::has('orders.print'))
                @php
                    $isWaPresent = $order->customer && !empty($order->customer->whatsapp_number);
                    $colSpanClass = $isWaPresent ? 'col-span-1' : 'col-span-2 sm:col-span-1';
                @endphp
                @if ($order->items->isNotEmpty())
                    <a
                        href="{{ route('orders.print', ['id' => $order->id, 'type' => 'receipt']) }}"
                        target="_blank"
                        class="{{ $colSpanClass }} h-10 px-4 text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-xs transition-colors inline-flex items-center justify-center whitespace-nowrap shrink-0"
                    >
                        <i data-lucide="printer" class="w-3.5 h-3.5 mr-1.5 shrink-0"></i>
                        <span>Cetak Nota</span>
                    </a>
                @else
                    <button
                        type="button"
                        @click="showPrintBlockedModal = true"
                        class="{{ $colSpanClass }} h-10 px-4 text-xs sm:text-sm font-semibold text-slate-500 bg-slate-200 hover:bg-slate-300/80 border border-slate-300 rounded-lg transition-all cursor-pointer shadow-2xs group inline-flex items-center justify-center whitespace-nowrap shrink-0"
                        title="Klik untuk info: Rincian jasa & sparepart masih kosong"
                    >
                        <i data-lucide="printer" class="w-3.5 h-3.5 mr-1.5 text-slate-400 group-hover:text-slate-600 transition-colors shrink-0"></i>
                        <span>Cetak Nota</span>
                    </button>
                @endif
            @endif
        </div>
    </div>

    <!-- Dynamic Status & Workflow Control Panel -->
    <div class="bg-white border border-slate-200/80 p-4 sm:p-5 rounded-2xl shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center shrink-0">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div class="flex flex-col justify-center gap-0.5">
                <span class="text-xs font-bold text-slate-800 leading-tight">Status Pengerjaan Saat Ini</span>
                @php
                    $statusLabel = $statusLabels[$order->status] ?? ($statusOptions[$order->status]['badge'] ?? ucfirst(str_replace('_', ' ', $order->status)));
                @endphp
                <span class="text-sm font-semibold text-blue-600 leading-tight">{{ $statusLabel }}</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <!-- Quick Advance Next Step (If normal flow) -->
            @if ($order->status === 'diagnosing')
                <button
                    type="button"
                    wire:click="advanceStatus('in_progress', 'Diagnosa selesai, mulai pengerjaan.')"
                    class="w-full sm:w-auto h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-2xs transition-all active:scale-95 cursor-pointer inline-flex items-center justify-center whitespace-nowrap shrink-0 gap-1.5"
                >
                    <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                    <span>Mulai Pengerjaan</span>
                </button>
            @elseif ($order->status === 'waiting_approval')
                <button
                    type="button"
                    wire:click="advanceStatus('in_progress', 'Pelanggan telah menyetujui estimasi biaya.')"
                    class="w-full sm:w-auto h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-2xs transition-all active:scale-95 cursor-pointer inline-flex items-center justify-center whitespace-nowrap shrink-0 gap-1.5"
                >
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Pelanggan Setuju</span>
                </button>
            @elseif ($order->status === 'in_progress')
                <button
                    type="button"
                    wire:click="advanceStatus('testing', 'Perbaikan selesai, masuk ke tahap QC & pengujian fungsi.')"
                    class="w-full sm:w-auto h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-lg shadow-2xs transition-all active:scale-95 cursor-pointer inline-flex items-center justify-center whitespace-nowrap shrink-0 gap-1.5"
                >
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                    <span>Uji Fungsi (QC)</span>
                </button>
            @elseif ($order->status === 'waiting_parts')
                <button
                    type="button"
                    wire:click="advanceStatus('in_progress', 'Sparepart telah tiba, melanjutkan servis.')"
                    class="w-full sm:w-auto h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-2xs transition-all active:scale-95 cursor-pointer inline-flex items-center justify-center whitespace-nowrap shrink-0 gap-1.5"
                >
                    <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                    <span>Sparepart Ada (Lanjut)</span>
                </button>
            @elseif ($order->status === 'testing')
                <button
                    type="button"
                    wire:click="advanceStatus('ready_for_pickup', 'Semua uji fungsi lolos. Gadget siap diserahkan ke pelanggan.')"
                    class="w-full sm:w-auto h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs transition-all active:scale-95 cursor-pointer inline-flex items-center justify-center whitespace-nowrap shrink-0 gap-1.5"
                >
                    <i data-lucide="bell" class="w-3.5 h-3.5"></i>
                    <span>QC Lolos (Siap Diambil)</span>
                </button>
            @elseif ($order->status === 'ready_for_pickup')
                <button
                    type="button"
                    wire:click="advanceStatus('completed', 'Unit telah diserahkan dan diterima oleh pelanggan.')"
                    class="w-full sm:w-auto h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-white bg-green-700 hover:bg-green-800 rounded-lg shadow-2xs transition-all active:scale-95 cursor-pointer inline-flex items-center justify-center whitespace-nowrap shrink-0 gap-1.5"
                >
                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                    <span>Unit Diambil (Selesai)</span>
                </button>
            @endif

            <!-- Dynamic Action: Perbarui Status -->
            <button
                type="button"
                wire:click="openUpdateStatusModal"
                class="w-full sm:w-auto h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-2xs transition-all active:scale-95 cursor-pointer inline-flex items-center justify-center whitespace-nowrap shrink-0 gap-1.5"
            >
                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                <span>Perbarui Status</span>
            </button>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 1 Col: Customer & Device Overview -->
        <div class="space-y-6">
            <!-- Customer Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Data Pelanggan</h3>
                    @if ($order->customer)
                        <a href="{{ route('customers.show', $order->customer->id) }}" class="text-2xs font-semibold text-primary-600 hover:underline">
                            Lihat Detail
                        </a>
                    @endif
                </div>

                @if ($order->customer)
                    <div class="overflow-hidden">
                        <table class="w-full text-xs">
                            <tbody>
                                <tr>
                                    <td class="py-1.5 pr-2 font-medium text-slate-900 whitespace-nowrap align-top w-32">Nama</td>
                                    <td class="py-1.5 px-1 text-slate-400 align-top w-3">:</td>
                                    <td class="py-1.5 pl-2 text-slate-700 align-top">{{ $order->customer->name }}</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 pr-2 font-medium text-slate-900 whitespace-nowrap align-top">No. WhatsApp / HP</td>
                                    <td class="py-1.5 px-1 text-slate-400 align-top">:</td>
                                    <td class="py-1.5 pl-2 text-slate-700 align-top">
                                        @if ($order->customer->phone)
                                            <a href="https://wa.me/{{ $order->customer->formatted_phone }}" target="_blank" class="text-slate-700 hover:text-primary-600 hover:underline inline-flex items-center gap-1" title="Buka WhatsApp">
                                                <span>{{ $order->customer->phone }}</span>
                                            </a>
                                        @else
                                            <span class="text-slate-400 italic">Tidak ada</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 pr-2 font-medium text-slate-900 whitespace-nowrap align-top">Alamat</td>
                                    <td class="py-1.5 px-1 text-slate-400 align-top">:</td>
                                    <td class="py-1.5 pl-2 text-slate-700 align-top leading-relaxed">
                                        {{ $order->customer->address ?: '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-slate-500 italic">Pelanggan Umum (Tanpa Akun)</p>
                @endif
            </div>

            <!-- Device & Intake Details -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Perangkat dan Kondisi</h3>
                </div>

                <div class="overflow-hidden">
                    <table class="w-full text-xs">
                        <tbody>
                            <tr>
                                <td class="py-1.5 pr-2 font-medium text-slate-900 whitespace-nowrap align-top w-32">Tipe Gadget</td>
                                <td class="py-1.5 px-1 text-slate-400 align-top w-3">:</td>
                                <td class="py-1.5 pl-2 text-slate-700 align-top">
                                    {{ $order->device?->brand ?? '-' }} {{ $order->device?->model ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1.5 pr-2 font-medium text-slate-900 whitespace-nowrap align-top">Kategori</td>
                                <td class="py-1.5 px-1 text-slate-400 align-top">:</td>
                                <td class="py-1.5 pl-2 text-slate-700 align-top">
                                    {{ ucfirst($order->device?->device_type ?? 'Smartphone') }}
                                </td>
                            </tr>
                            @if ($order->device?->color)
                                <tr>
                                    <td class="py-1.5 pr-2 font-medium text-slate-900 whitespace-nowrap align-top">Warna</td>
                                    <td class="py-1.5 px-1 text-slate-400 align-top">:</td>
                                    <td class="py-1.5 pl-2 text-slate-700 align-top">
                                        {{ $order->device->color }}
                                    </td>
                                </tr>
                            @endif
                            @if ($order->device?->variant)
                                <tr>
                                    <td class="py-1.5 pr-2 font-medium text-slate-900 whitespace-nowrap align-top">Varian</td>
                                    <td class="py-1.5 px-1 text-slate-400 align-top">:</td>
                                    <td class="py-1.5 pl-2 text-slate-700 align-top">
                                        {{ $order->device->variant }}
                                    </td>
                                </tr>
                            @endif
                            @if ($order->device?->specs)
                                <tr>
                                    <td class="py-1.5 pr-2 font-medium text-slate-900 whitespace-nowrap align-top">Spesifikasi</td>
                                    <td class="py-1.5 px-1 text-slate-400 align-top">:</td>
                                    <td class="py-1.5 pl-2 text-slate-700 align-top leading-relaxed">
                                        {{ $order->device->specs }}
                                    </td>
                                </tr>
                            @endif
                            @if ($order->device?->imei_or_serial)
                                <tr>
                                    <td class="py-1.5 pr-2 font-medium text-slate-900 whitespace-nowrap align-top">IMEI / Serial No</td>
                                    <td class="py-1.5 px-1 text-slate-400 align-top">:</td>
                                    <td class="py-1.5 pl-2 text-slate-700 align-top font-mono">
                                        {{ $order->device->imei_or_serial }}
                                    </td>
                                </tr>
                            @endif
                            @if ($order->passcode || $order->device?->security_code || $order->device?->passcode)
                                <tr>
                                    <td class="py-1.5 pr-2 font-medium text-slate-900 whitespace-nowrap align-top">PIN / Pola Layar</td>
                                    <td class="py-1.5 px-1 text-slate-400 align-top">:</td>
                                    <td class="py-1.5 pl-2 text-slate-700 align-top font-mono font-semibold">
                                        {{ $order->passcode ?: ($order->device?->security_code ?: $order->device?->passcode) }}
                                    </td>
                                </tr>
                            @endif
                            @if ($order->accessories_included || $order->accessories_brought)
                                <tr>
                                    <td class="py-1.5 pr-2 font-medium text-slate-900 whitespace-nowrap align-top">Kelengkapan</td>
                                    <td class="py-1.5 px-1 text-slate-400 align-top">:</td>
                                    <td class="py-1.5 pl-2 text-slate-700 align-top">
                                        {{ $order->accessories_included ?: $order->accessories_brought }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Physical Checklist Summary -->
                @if (is_array($order->physical_condition_checklist) && count($order->physical_condition_checklist) > 0)
                    <div class="pt-3 border-t border-slate-100">
                        <span class="text-xs font-medium text-slate-900 block mb-2">Kondisi Fisik Saat Diterima</span>
                        <div class="grid grid-cols-2 gap-1.5">
                            @php
                                $checklistLabels = [
                                    'power_on' => 'Nyala Normal',
                                    'lcd_touchscreen' => 'Layar / LCD',
                                    'charging' => 'Pengisian Daya',
                                    'camera' => 'Kamera',
                                    'speaker_mic' => 'Speaker / Mic',
                                    'wifi_bluetooth' => 'WiFi / BT',
                                    'buttons' => 'Tombol Fisik',
                                    'body_casing' => 'Fisik / Casing',
                                ];
                            @endphp
                            @foreach ($order->physical_condition_checklist as $k => $val)
                                <div class="flex items-center space-x-1.5 text-2xs">
                                    @if ($val)
                                        <span class="text-emerald-600 font-bold">✓</span>
                                    @else
                                        <span class="text-rose-500 font-bold">✕</span>
                                    @endif
                                    <span class="text-slate-600">{{ $checklistLabels[$k] ?? ucwords(str_replace('_', ' ', $k)) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Customer Complaint Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Keluhan Pelanggan</h3>
                </div>
                <div class="text-xs text-slate-800 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                    {{ $order->complaints ?: ($order->complaint ?: 'Tidak ada catatan keluhan khusus.') }}
                </div>
            </div>

            {{-- Foto Kondisi Fisik Unit (Opsional) Card --}}
            @if (is_array($order->photos) && count($order->photos) > 0)
                <div
                    x-data="{ activePhotoUrl: null }"
                    @keydown.escape.window="activePhotoUrl = null"
                    class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-900">Foto Kondisi Fisik Unit (Opsional)</h3>
                        <span class="text-2xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                            {{ count($order->photos) }} Foto
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($order->photos as $photo)
                            @php
                                $photoUrl = asset('storage/'.$photo);
                            @endphp
                            <button
                                type="button"
                                @click="activePhotoUrl = '{{ $photoUrl }}'"
                                class="block rounded-xl overflow-hidden border border-slate-200 aspect-square group bg-slate-50 relative hover:border-primary-400 hover:shadow-xs transition-all cursor-pointer text-left focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                                <img
                                    src="{{ $photoUrl }}"
                                    alt="Foto Fisik Unit"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                />
                                <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/25 transition-colors flex items-center justify-center">
                                    <i data-lucide="zoom-in" class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow"></i>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    {{-- Modal Preview Foto --}}
                    <div
                        x-show="activePhotoUrl !== null"
                        x-cloak
                        class="fixed inset-0 z-50 overflow-y-auto"
                        role="dialog"
                        aria-modal="true"
                    >
                        <!-- Backdrop -->
                        <div
                            x-show="activePhotoUrl !== null"
                            x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity"
                            @click="activePhotoUrl = null"
                        ></div>

                        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                            <div
                                x-show="activePhotoUrl !== null"
                                x-transition:enter="ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all max-w-3xl w-full border border-slate-800/20"
                                @click.away="activePhotoUrl = null"
                            >
                                <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-white">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="image" class="w-4 h-4 text-primary-600"></i>
                                        <h3 class="text-sm font-bold text-slate-900">Foto Kondisi Fisik Unit</h3>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a
                                            :href="activePhotoUrl"
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors"
                                            title="Buka gambar di tab baru"
                                        >
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                            <span>Buka Asli</span>
                                        </a>
                                        <button
                                            type="button"
                                            @click="activePhotoUrl = null"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                                        >
                                            <i data-lucide="x" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-3 sm:p-4 bg-slate-900 flex items-center justify-center min-h-[300px] max-h-[75vh] overflow-hidden">
                                    <img
                                        :src="activePhotoUrl"
                                        alt="Foto Fisik Unit"
                                        class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-md"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Technician Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Teknisi Bertugas</span>
                    <button
                        type="button"
                        wire:click="$set('showAssignTechModal', true)"
                        class="text-2xs font-semibold text-primary-600 hover:underline"
                    >
                        Ganti Teknisi
                    </button>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr($order->technician?->name ?? 'A', 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">{{ $order->technician?->name ?? 'Antrean Terbuka' }}</h2>
                        <p class="text-2xs text-slate-500">{{ $order->technician?->email ?? 'Belum ada teknisi spesifik' }}</p>
                    </div>
                </div>
            </div>

            <!-- Kelola Order (Edit & Hapus) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Kelola Order</h3>
                </div>

                <div class="flex flex-col sm:flex-row lg:flex-col gap-2.5">
                    <button
                        type="button"
                        @click="showEditWarningModal = true"
                        class="w-full h-10 px-4 text-xs sm:text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap cursor-pointer gap-2"
                    >
                        <i data-lucide="edit-3" class="w-4 h-4 text-slate-500"></i>
                        <span>Edit Order</span>
                    </button>

                    <button
                        type="button"
                        wire:click="deleteOrder"
                        wire:confirm="Yakin ingin menghapus order #{{ $order->order_number }} secara permanen? Seluruh riwayat dan data servis akan dihapus."
                        class="w-full h-10 px-4 text-xs sm:text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap cursor-pointer gap-2"
                    >
                        <i data-lucide="trash-2" class="w-4 h-4 text-rose-600"></i>
                        <span>Hapus Order</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right 2 Cols: Services & Cost Breakdown, Diagnosis, Timeline -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Billing & Items Card -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-2xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Rincian Biaya</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="openAddItemModal"
                            class="h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap shrink-0 cursor-pointer"
                        >
                            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1.5 shrink-0"></i>
                            <span>Tambah Biaya / Jasa</span>
                        </button>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-2xs tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-2.5 px-3">Item Layanan / Sparepart</th>
                                <th class="py-2.5 px-3 text-right">Harga / Biaya</th>
                                <th class="py-2.5 px-3 text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($order->items as $item)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="py-3 px-3 font-medium text-slate-800">
                                        {{ $item->name }}
                                        @if ($item->service)
                                            <span class="block text-2xs text-slate-400">Dari Katalog: {{ $item->service->category?->name ?? 'Umum' }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-right font-semibold text-slate-900">
                                        Rp{{ number_format($item->price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <button
                                            type="button"
                                            wire:click="removeItem('{{ $item->id }}')"
                                            wire:confirm="Hapus item biaya ini?"
                                            class="p-1 text-slate-400 hover:text-rose-600 transition-colors"
                                            title="Hapus item"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-400 italic">
                                        Belum ada rincian biaya yang ditambahkan. Klik tombol "+ Tambah Biaya / Jasa" di atas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Cost Summary Footer -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2 text-xs">
                    @if ($order->is_cost_undetermined && $order->items->isEmpty())
                        <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 mb-2">
                            <span class="block text-2xs uppercase tracking-wider font-bold text-amber-700">Biaya Belum Ditentukan</span>
                            <p class="text-xs font-semibold mt-0.5">Menunggu Diagnosa Teknisi</p>
                            <p class="text-[11px] text-amber-700 mt-1">Biaya awal unit ini belum ditentukan (misal: kondisi mati total / butuh pengecekan jalur board). Setelah diagnosa selesai, silakan tambah rincian jasa/sparepart di atas.</p>
                        </div>
                    @endif
                    <div class="flex justify-between text-slate-600">
                        <span>Total Biaya Servis:</span>
                        <span class="font-bold text-slate-900 text-sm">
                            @if ($order->is_cost_undetermined && $order->items->isEmpty())
                                <span class="italic text-xs font-normal text-amber-700">Menunggu Diagnosa</span>
                            @else
                                Rp{{ number_format($order->total_cost, 0, ',', '.') }}
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Uang Masuk / Pembayaran Diterima:</span>
                        <span class="font-semibold text-emerald-700">Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-800 pt-2 border-t border-slate-200">
                        <span class="font-bold">Sisa Tagihan:</span>
                        @if ($order->is_cost_undetermined && $order->items->isEmpty())
                            <span class="italic text-xs font-medium text-slate-500">Menyusul setelah diagnosa</span>
                        @else
                            <span class="font-bold text-sm {{ $order->remaining_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $order->remaining_balance > 0 ? 'Rp' . number_format($order->remaining_balance, 0, ',', '.') : 'Lunas (Rp0)' }}
                            </span>
                        @endif
                    </div>
                    @if ($order->warranty_days > 0)
                        <div class="text-2xs text-slate-500 pt-1 flex items-center justify-between">
                            <span>Masa Garansi Toko:</span>
                            <span class="font-semibold text-slate-700">{{ $order->warranty_days }} Hari {{ $order->warranty_until ? '(s/d ' . \Carbon\Carbon::parse($order->warranty_until)->format('d M Y') . ')' : '' }}</span>
                        </div>
                    @endif

                    <div class="pt-2 border-t border-slate-200 flex justify-end">
                        <button
                            type="button"
                            wire:click="$set('showPaymentModal', true)"
                            class="w-full sm:w-auto h-10 px-4 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs transition-colors inline-flex items-center justify-center whitespace-nowrap cursor-pointer gap-1.5"
                        >
                            <i data-lucide="credit-card" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>Catat Pembayaran</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Diagnosis & Inspection Card -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Diagnosa & Pengujian (QC)</h2>
                        <p class="text-xs text-slate-500">Catatan kerusakan dari teknisi dan hasil checklist pengetesan.</p>
                    </div>
                    <button
                        type="button"
                        wire:click="$set('showDiagnosisModal', true)"
                        class="h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap shrink-0 cursor-pointer"
                    >
                        <i data-lucide="clipboard-check" class="w-3.5 h-3.5 mr-1.5 shrink-0"></i>
                        <span>Catat Diagnosa / QC</span>
                    </button>
                </div>

                @if ($order->diagnoses->isNotEmpty())
                    <div class="space-y-4">
                        @foreach ($order->diagnoses as $diag)
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-2">
                                <div class="flex items-center justify-between text-2xs text-slate-500">
                                    <span class="font-bold text-slate-700">Teknisi: {{ $diag->technician?->name ?? 'Sistem' }}</span>
                                    <span>{{ $diag->created_at->isoFormat('D MMM Y, HH:mm') }}</span>
                                </div>
                                <div class="text-xs text-slate-800">
                                    <span class="font-semibold block text-slate-500 text-2xs">Temuan Diagnosa:</span>
                                    <p class="mt-0.5">{{ $diag->diagnosis_notes }}</p>
                                </div>
                                @if ($diag->action_taken)
                                    <div class="text-xs text-slate-800 pt-1">
                                        <span class="font-semibold block text-slate-500 text-2xs">Tindakan Perbaikan:</span>
                                        <p class="mt-0.5">{{ $diag->action_taken }}</p>
                                    </div>
                                @endif

                                @if (is_array($diag->testing_checklist))
                                    <div class="pt-2 border-t border-slate-200/60">
                                        <span class="font-semibold block text-slate-500 text-2xs mb-1.5">Hasil Checklist QC:</span>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 text-2xs">
                                            @foreach ($diag->testing_checklist as $qcItem => $pass)
                                                <div class="flex items-center space-x-1">
                                                    @if ($pass)
                                                        <span class="text-emerald-600 font-bold">✓</span>
                                                    @else
                                                        <span class="text-rose-500 font-bold">✕</span>
                                                    @endif
                                                    <span class="text-slate-600 uppercase">{{ str_replace('_', ' ', $qcItem) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic py-2">Belum ada catatan diagnosa teknisi.</p>
                @endif
            </div>

            <!-- History Timeline Log -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Riwayat & Log Aktivitas</h2>
                        <p class="text-xs text-slate-500">Jejak kronologis status, tindakan teknisi, dan catatan pengerjaan.</p>
                    </div>
                    <button
                        type="button"
                        wire:click="openUpdateStatusModal"
                        class="h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap shrink-0 cursor-pointer"
                    >
                        <i data-lucide="plus" class="w-3.5 h-3.5 mr-1.5 shrink-0"></i>
                        <span>Update Status / Catatan</span>
                    </button>
                </div>

                <div class="relative pl-6 space-y-5 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse ($order->histories->sortByDesc('created_at') as $history)
                        <div class="relative text-xs">
                            <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-primary-600 border-2 border-white ring-2 ring-primary-100"></div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center space-x-1.5 flex-wrap">
                                    <span class="font-bold text-slate-800">
                                        {{ $history->user?->name ?? 'Sistem' }}
                                    </span>
                                    @if ($history->from_status !== $history->to_status)
                                        <span class="text-2xs font-medium text-slate-500 inline-flex items-center gap-1">
                                            <span class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-600 font-semibold text-[10px]">{{ $statusLabels[$history->from_status] ?? ucfirst(str_replace('_', ' ', $history->from_status)) }}</span>
                                            <span>→</span>
                                            <span class="px-1.5 py-0.5 bg-primary-50 text-primary-700 rounded font-semibold text-[10px]">{{ $statusLabels[$history->to_status] ?? ucfirst(str_replace('_', ' ', $history->to_status)) }}</span>
                                        </span>
                                    @endif
                                </div>
                                <span class="text-3xs text-slate-400 whitespace-nowrap" title="{{ $history->created_at->format('d/m/Y H:i') }}">
                                    {{ $history->created_at->diffForHumans() }}
                                </span>
                            </div>
                            @if ($history->notes)
                                <div class="mt-1.5 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700 leading-relaxed font-normal">
                                    {{ $history->notes }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada catatan riwayat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Add Item / Service -->
    @if ($showAddItemModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Tambah Layanan atau Sparepart</h2>
                    <button wire:click="$set('showAddItemModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Pilih dari Katalog (Opsional)</label>
                        <select
                            wire:model.live="selected_service_id"
                            wire:change="onServiceSelected"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">-- Ketik Bebas / Custom --</option>
                            @foreach ($availableServices as $srv)
                                <option value="{{ $srv->id }}">{{ $srv->name }} (Rp{{ number_format($srv->price, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nama Layanan / Barang <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            wire:model="item_name"
                            placeholder="Contoh: Ganti LCD OLED Samsung A52"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        />
                        @error('item_name') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Harga / Biaya (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="number"
                            wire:model="item_price"
                            min="0"
                            placeholder="0"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        />
                        @error('item_price') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="$set('showAddItemModal', false)"
                        class="h-10 px-4 text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer inline-flex items-center justify-center whitespace-nowrap"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="addItem"
                        class="h-10 px-4 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center whitespace-nowrap"
                    >
                        Simpan Item
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Record Payment -->
    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Catat Penerimaan Pembayaran</h2>
                    <button wire:click="$set('showPaymentModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex justify-between items-center">
                        <span class="text-slate-600">Sisa Tagihan Saat Ini:</span>
                        <span class="font-bold text-slate-900 text-sm">Rp{{ number_format($order->remaining_balance, 0, ',', '.') }}</span>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nominal Pembayaran Diterima (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="number"
                            wire:model="payment_amount"
                            min="1"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 font-bold text-slate-900"
                        />
                        @error('payment_amount') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Metode Pembayaran</label>
                        <select
                            wire:model="payment_method"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="cash">Tunai (Cash)</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="$set('showPaymentModal', false)"
                        class="h-10 px-4 text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer inline-flex items-center justify-center whitespace-nowrap"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="recordPayment"
                        class="h-10 px-4 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center whitespace-nowrap"
                    >
                        Simpan Pembayaran
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Diagnosis / QC Checklist -->
    @if ($showDiagnosisModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Catat Diagnosa & Checklist Uji Fungsi</h2>
                    <button wire:click="$set('showDiagnosisModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Temuan Kerusakan / Diagnosa <span class="text-rose-500">*</span></label>
                        <textarea
                            wire:model="diagnosis_notes"
                            rows="3"
                            placeholder="Jelaskan temuan kerusakan komponen, jalur konslet, atau analisis teknis..."
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        ></textarea>
                        @error('diagnosis_notes') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tindakan Perbaikan yang Dilakukan</label>
                        <textarea
                            wire:model="action_taken"
                            rows="2"
                            placeholder="Contoh: Reball IC power, jumper jalur mic, ganti konektor fleksibel..."
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        ></textarea>
                    </div>

                    <div>
                        <span class="block font-medium text-slate-700 mb-2">Checklist Uji Fungsi (QC):</span>
                        <div class="grid grid-cols-2 gap-2 p-3 rounded-xl bg-slate-50 border border-slate-100">
                            @foreach ($testing_checklist as $qcKey => $qcVal)
                                <label class="inline-flex items-center space-x-2 text-xs">
                                    <input
                                        type="checkbox"
                                        wire:model="testing_checklist.{{ $qcKey }}"
                                        class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                    />
                                    <span class="capitalize text-slate-700">{{ str_replace('_', ' ', $qcKey) }} Normal</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="$set('showDiagnosisModal', false)"
                        class="h-10 px-4 text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer inline-flex items-center justify-center whitespace-nowrap"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="saveDiagnosis"
                        class="h-10 px-4 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center whitespace-nowrap"
                    >
                        Simpan Diagnosa
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Assign Technician -->
    @if ($showAssignTechModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Tugaskan Teknisi</h2>
                    <button wire:click="$set('showAssignTechModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <label class="block font-medium text-slate-700 mb-1">Pilih Teknisi Penanggung Jawab</label>
                    <select
                        wire:model="assigned_technician_id"
                        class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">-- Antrean Terbuka (Belum Ada) --</option>
                        @foreach ($technicians as $tech)
                            <option value="{{ $tech->user_id }}">{{ $tech->user?->name }} ({{ ucfirst($tech->specialization) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="$set('showAssignTechModal', false)"
                        class="h-10 px-4 text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer inline-flex items-center justify-center whitespace-nowrap"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="assignTechnician"
                        class="h-10 px-4 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center whitespace-nowrap"
                    >
                        Simpan Penugasan
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Update Status & Log Aktivitas -->
    @if ($showUpdateStatusModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Perbarui Status & Log Aktivitas</h2>
                        <p class="text-xs text-slate-500">Pilih status pengerjaan baru dan sertakan keterangan teknisi.</p>
                    </div>
                    <button wire:click="$set('showUpdateStatusModal', false)" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="updateStatusWithNotes" class="space-y-4">
                    <div>
                        <label class="block font-medium text-slate-700 text-xs mb-1.5">Pilih Status Baru <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto p-1.5 border border-slate-200 rounded-xl bg-slate-50/50">
                            @foreach ($statusOptions as $key => $meta)
                                <label class="flex items-start space-x-2.5 p-2 rounded-lg cursor-pointer border transition-colors {{ $new_status === $key ? 'bg-primary-50/70 border-primary-500' : 'bg-white border-slate-200/80 hover:bg-slate-50' }}">
                                    <input
                                        type="radio"
                                        wire:model.live="new_status"
                                        value="{{ $key }}"
                                        class="mt-0.5 text-primary-600 focus:ring-primary-500 cursor-pointer"
                                    >
                                    <div class="min-w-0">
                                        <span class="block text-xs font-semibold {{ $new_status === $key ? 'text-primary-900' : 'text-slate-800' }}">
                                            {{ $meta['label'] }}
                                        </span>
                                        <span class="block text-3xs text-slate-500 leading-tight mt-0.5">
                                            {{ $meta['description'] }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('new_status')
                            <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 text-xs mb-1">
                            Keterangan / Catatan Pengerjaan (Opsional)
                        </label>
                        <textarea
                            wire:model="status_notes"
                            rows="3"
                            placeholder="Contoh: IC CPU short, unit tidak bisa diperbaiki / Menunggu sparepart LCD tiba dari suplier / Selesai QC siap diserahkan..."
                            class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 focus:border-primary-500 focus:ring-primary-500"
                        ></textarea>
                        <span class="block text-3xs text-slate-400 mt-1">Catatan ini akan tersimpan permanen di riwayat aktivitas order dan dapat dilihat oleh tim.</span>
                        @error('status_notes')
                            <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="$set('showUpdateStatusModal', false)"
                            class="h-10 px-4 text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer inline-flex items-center justify-center whitespace-nowrap"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="h-10 px-4 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors inline-flex items-center justify-center gap-1.5 cursor-pointer shadow-xs whitespace-nowrap"
                        >
                            <i data-lucide="loader-circle" class="w-3.5 h-3.5 animate-spin" wire:loading wire:target="updateStatusWithNotes"></i>
                            <span>Simpan Status & Catatan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Dialog: Peringatan Rincian Servis Kosong Sebelum Cetak Nota -->
    <div
        x-show="showPrintBlockedModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div
            x-show="showPrintBlockedModal"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="showPrintBlockedModal = false"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                x-show="showPrintBlockedModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 border border-slate-100 space-y-4"
            >
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <i data-lucide="file-warning" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-slate-900">Rincian Jasa & Sparepart Masih Kosong</h3>
                    </div>
                </div>

                <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-3.5 text-xs text-amber-900 leading-relaxed">
                    Dokumen nota membutuhkan setidaknya <strong>satu tindakan jasa servis atau suku cadang</strong> agar rincian biaya pekerjaan serta pertanggungjawaban garansi tercatat secara sah dan transparan bagi pelanggan.
                </div>

                <div class="bg-blue-50/70 border border-blue-200/80 rounded-xl p-3 text-xs text-blue-900 leading-relaxed flex items-start gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-blue-600 shrink-0 mt-0.5"></i>
                    <div>
                        <span class="font-bold">Butuh cetak bukti serah terima sekarang?</span>
                        <p class="text-blue-800 mt-0.5">
                            Gunakan tombol <strong>Tanda Terima</strong> di samping untuk mencetak bukti masuk unit ke pelanggan tanpa harus memasukkan rincian biaya terlebih dahulu.
                        </p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Silakan masukkan rincian perbaikan terlebih dahulu melalui menu <strong>Rincian Biaya</strong> sebelum mencetak tanda bukti / nota pelanggan.
                </p>

                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        @click="showPrintBlockedModal = false"
                        class="w-full sm:w-auto h-10 px-5 text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer inline-flex items-center justify-center text-center"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Dialog: Peringatan Dampak Perubahan Edit Order --}}
    <div
        x-show="showEditWarningModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div
            x-show="showEditWarningModal"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="showEditWarningModal = false"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                x-show="showEditWarningModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 border border-slate-100 space-y-4"
            >
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Perhatian: Edit Data Order</h3>
                        <p class="text-xs text-slate-500">Order #{{ $order->order_number }}</p>
                    </div>
                </div>

                <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-3.5 text-xs text-amber-900 leading-relaxed space-y-2">
                    <p class="font-semibold text-amber-950">
                        Mengubah data order ini akan berdampak secara menyeluruh ke seluruh sistem:
                    </p>
                    <ul class="space-y-1.5 list-disc list-inside text-amber-900">
                        <li><strong>Data Pelanggan & Unit:</strong> Perubahan nama, nomor HP, atau tipe unit akan memperbarui data pelanggan & perangkat.</li>
                        <li><strong>Dokumen & Nota Cetak:</strong> Seluruh nota servis, tanda terima, dan rincian garansi akan langsung mengacu pada data yang baru.</li>
                        <li><strong>Halaman Tracking Pelanggan:</strong> Status dan informasi yang dilihat oleh pelanggan pada link publik akan otomatis berubah.</li>
                    </ul>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Pastikan perubahan dilakukan dengan benar sesuai konfirmasi fisik unit atau permintaan pelanggan.
                </p>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        @click="showEditWarningModal = false"
                        class="w-full sm:w-auto h-10 px-4 text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer inline-flex items-center justify-center"
                    >
                        Batal
                    </button>
                    <a
                        href="{{ route('orders.edit', $order->id) }}"
                        wire:navigate
                        class="w-full sm:w-auto h-10 px-5 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                        <span>Lanjutkan Edit Order</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

