<div class="flex flex-col gap-6" x-data="{ showPrintBlockedModal: false, showEditWarningModal: false, showDeleteOrderModal: false }">
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

        <div class="flex items-center gap-2 w-full md:w-auto">
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

    <!-- Kelola Order (Edit & Hapus) - Mobile Only (Tampil di bawah section status) -->
    <div class="lg:hidden bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
        <div class="pb-2.5 border-b border-slate-100">
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Kelola Order</h3>
        </div>

        <div class="grid grid-cols-2 gap-2.5">
            <button
                type="button"
                @click="showEditWarningModal = true"
                class="w-full h-10 px-3 text-xs sm:text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap cursor-pointer gap-2"
            >
                <i data-lucide="edit-3" class="w-4 h-4 text-slate-500"></i>
                <span>Edit Order</span>
            </button>

            <button
                type="button"
                @click="showDeleteOrderModal = true"
                class="w-full h-10 px-3 text-xs sm:text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap cursor-pointer gap-2"
            >
                <i data-lucide="trash-2" class="w-4 h-4 text-rose-600"></i>
                <span>Hapus Order</span>
            </button>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 1 Col: Customer & Device Overview -->
        <div class="flex flex-col gap-6">
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
                @php
                    $physicalCond = $order->physical_condition ?? [];
                    $physicalLabels = [
                        'screen_condition' => 'Kaca Layar',
                        'lcd_condition' => 'Tampilan LCD',
                        'body_condition' => 'Bodi / Backdoor',
                        'camera_condition' => 'Kamera Depan/Belakang',
                        'buttons_condition' => 'Tombol Fisik',
                        'charging_condition' => 'Port Charger',
                    ];
                @endphp
                @if (is_array($physicalCond) && count($physicalCond) > 0)
                    <div class="pt-3 border-t border-slate-100">
                        <span class="text-xs font-semibold text-slate-900 block mb-2.5">Checklist Kondisi Fisik Saat Masuk</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach ($physicalCond as $itemKey => $itemVal)
                                @if (!empty($itemVal))
                                    @php
                                        $isMatot = str_contains($itemVal, 'Matot');
                                        $isNormal = in_array(strtolower($itemVal), ['normal', 'mulus', 'normal mulus', 'normal jernih', 'normal empuk', 'normal kencang']);
                                        $isWarning = $isMatot || str_contains(strtolower($itemVal), 'pecah') || str_contains(strtolower($itemVal), 'mati') || str_contains(strtolower($itemVal), 'rusak');
                                    @endphp
                                    <div class="p-2 rounded-xl border {{ $isMatot ? 'bg-amber-50/60 border-amber-200' : ($isWarning ? 'bg-rose-50/60 border-rose-200' : ($isNormal ? 'bg-emerald-50/40 border-emerald-100' : 'bg-slate-50 border-slate-200')) }}">
                                        <span class="text-3xs font-medium text-slate-500 block leading-tight">{{ $physicalLabels[$itemKey] ?? ucwords(str_replace('_', ' ', $itemKey)) }}</span>
                                        <span class="text-xs font-semibold {{ $isMatot ? 'text-amber-800' : ($isWarning ? 'text-rose-700' : ($isNormal ? 'text-emerald-700' : 'text-slate-800')) }} block mt-0.5 truncate" title="{{ $itemVal }}">
                                            @if($isMatot)
                                                ⚡ {{ $itemVal }}
                                            @elseif($isNormal)
                                                ✓ {{ $itemVal }}
                                            @else
                                                • {{ $itemVal }}
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @elseif (is_array($order->physical_condition_checklist) && count($order->physical_condition_checklist) > 0)
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
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Teknisi Bertugas</h3>
                    <button
                        type="button"
                        wire:click="$set('showAssignTechModal', true)"
                        class="text-2xs font-semibold text-primary-600 hover:underline cursor-pointer"
                    >
                        Ganti Teknisi
                    </button>
                </div>

                @if ($order->technician)
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr($order->technician->name, 0, 2)) }}
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">{{ $order->technician->name }}</h2>
                            <p class="text-2xs text-slate-500">{{ $order->technician->email }}</p>
                        </div>
                    </div>
                @else
                    <div class="py-2.5 text-center text-slate-400 text-xs font-normal">
                        Belum ada teknisi yang ditugaskan. Klik tombol "Ganti Teknisi" di atas.
                    </div>
                @endif
            </div>

            <!-- Kelola Order (Edit & Hapus) - Desktop Only -->
            <div class="hidden lg:block bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Kelola Order</h3>
                </div>

                <div class="flex flex-col gap-2.5">
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
                        @click="showDeleteOrderModal = true"
                        class="w-full h-10 px-4 text-xs sm:text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap cursor-pointer gap-2"
                    >
                        <i data-lucide="trash-2" class="w-4 h-4 text-rose-600"></i>
                        <span>Hapus Order</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right 2 Cols: Services & Cost Breakdown, Diagnosis, Timeline -->
        <div class="lg:col-span-2 flex flex-col gap-6">
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
                            class="h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap shrink-0 cursor-pointer gap-1.5"
                        >
                            <i data-lucide="plus" class="w-4 h-4 shrink-0"></i>
                            <span>Tambah Biaya</span>
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
                        <tbody class="divide-y divide-slate-100 border-b border-slate-100">
                            @forelse ($order->items as $item)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="py-3 px-3 font-medium text-slate-800">
                                        {{ $item->name }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-semibold text-slate-900">
                                        Rp. {{ number_format($item->price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <button
                                            type="button"
                                            @click="
                                                Swal.fire({
                                                    title: 'Hapus Item Biaya?',
                                                    text: 'Hapus \'{{ addslashes($item->name) }}\' dari rincian biaya servis?',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#e11d48',
                                                    cancelButtonColor: '#64748b',
                                                    confirmButtonText: 'Ya, Hapus',
                                                    cancelButtonText: 'Batal',
                                                    reverseButtons: true,
                                                    customClass: {
                                                        popup: 'rounded-2xl shadow-xl',
                                                        confirmButton: 'rounded-xl text-xs sm:text-sm font-semibold px-4 py-2.5',
                                                        cancelButton: 'rounded-xl text-xs sm:text-sm font-semibold px-4 py-2.5'
                                                    }
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $wire.removeItem('{{ $item->id }}');
                                                    }
                                                })
                                            "
                                            class="p-1 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                                            title="Hapus item"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-12 text-center text-slate-400 text-xs font-normal">
                                        Belum ada rincian biaya yang ditambahkan. Klik tombol "+ Tambah Biaya" di atas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Cost Summary Footer -->
                @if ($order->items->isNotEmpty())
                    <div class="!mt-8 sm:!mt-10 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200/80 space-y-2.5 text-xs shadow-2xs">
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Total Biaya Servis:</span>
                            <span class="font-bold text-slate-900 text-sm">
                                Rp. {{ number_format($order->total_cost, 0, ',', '.') }}
                            </span>
                        </div>
                        @if (($order->paid_amount ?? 0) > 0)
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-slate-600">
                                    <span>Pembayaran Diterima:</span>
                                    <span class="font-bold text-emerald-700 text-sm">Rp. {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                                </div>
                                @if ($order->payments->isNotEmpty())
                                    <div class="bg-slate-50 border border-slate-200/70 rounded-lg p-2.5 space-y-1 text-2xs">
                                        <div class="font-semibold text-slate-500 uppercase tracking-wider text-[10px] mb-1">Riwayat Pembayaran:</div>
                                        @foreach ($order->payments as $pmt)
                                            <div class="flex items-center justify-between text-slate-600">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span class="text-slate-400">{{ $pmt->created_at->format('d/m/Y') }}</span>
                                                    <span class="font-medium text-slate-700 truncate">{{ $pmt->notes ?: ($pmt->type === 'dp' ? 'Uang Muka (DP)' : 'Pembayaran') }}</span>
                                                    <span class="text-[10px] px-1 py-0.2 bg-slate-200 text-slate-600 rounded uppercase">{{ $pmt->payment_method }}</span>
                                                </div>
                                                <span class="font-semibold text-emerald-600 shrink-0">+Rp. {{ number_format($pmt->amount, 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div class="flex justify-between text-slate-800 pt-2 border-t border-slate-200">
                            <span class="font-bold">Sisa Tagihan:</span>
                            <span class="font-bold text-sm {{ $order->remaining_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $order->remaining_balance > 0 ? 'Rp. ' . number_format($order->remaining_balance, 0, ',', '.') : 'Lunas (Rp. 0)' }}
                            </span>
                        </div>
                        @if ($order->overpaid_amount > 0)
                            <div class="flex justify-between items-center text-slate-800 pt-2 border-t border-slate-200">
                                <span class="font-bold text-amber-700">Kembalian:</span>
                                <span class="font-bold text-sm text-amber-700">
                                    Rp. {{ number_format($order->overpaid_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
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
                @endif
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
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Riwayat & Log Aktivitas</h2>
                </div>

                <div class="relative pl-7 space-y-5 before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:-translate-x-1/2 before:bg-slate-200">
                    @forelse ($order->histories->sortByDesc('created_at') as $history)
                        <div class="relative text-xs">
                            <div class="absolute -left-4 top-1 w-3 h-3 -translate-x-1/2 rounded-full bg-primary-600 border-2 border-white ring-2 ring-primary-100"></div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-800">
                                    {{ $statusLabels[$history->to_status] ?? ucfirst(str_replace('_', ' ', $history->to_status)) }}
                                </span>
                                <span class="text-3xs text-slate-400 whitespace-nowrap" title="{{ $history->created_at->isoFormat('D MMMM Y, HH:mm') }} WIB{{ $history->user ? ' (oleh ' . $history->user->name . ')' : '' }}">
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
                                <option value="{{ $srv->id }}">{{ $srv->name }} (Rp. {{ number_format($srv->price, 0, ',', '.') }})</option>
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
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Catat Penerimaan Pembayaran</h2>
                    <button wire:click="$set('showPaymentModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-3.5 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex justify-between items-center">
                        <span class="text-slate-600">Sisa Tagihan Saat Ini:</span>
                        <span class="font-bold text-slate-900 text-sm">Rp. {{ number_format($order->remaining_balance, 0, ',', '.') }}</span>
                    </div>

                    @if ($order->overpaid_amount > 0)
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/80 flex justify-between items-center">
                            <span class="text-amber-800 font-semibold">Kembalian:</span>
                            <span class="font-bold text-amber-800 text-sm">Rp. {{ number_format($order->overpaid_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if ($order->payments->isNotEmpty() || ($order->paid_amount ?? 0) > 0)
                        <div class="border border-slate-200/80 rounded-xl p-3 bg-slate-50/60 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-700 text-2xs uppercase tracking-wider flex items-center gap-1.5">
                                    <i data-lucide="history" class="w-3.5 h-3.5 text-slate-500"></i>
                                    Riwayat Pembayaran Sebelumnya
                                </span>
                                <span class="text-2xs font-semibold text-emerald-700">Total: Rp. {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="space-y-1.5 max-h-36 overflow-y-auto divide-y divide-slate-100 pr-0.5">
                                @forelse ($order->payments as $pmt)
                                    <div class="pt-1.5 first:pt-0 flex items-center justify-between text-2xs">
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-semibold text-slate-800">{{ $pmt->created_at->format('d/m/Y H:i') }}</span>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-medium uppercase bg-slate-200 text-slate-700">{{ $pmt->payment_method }}</span>
                                                @if ($pmt->type === 'dp')
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-amber-100 text-amber-800">DP</span>
                                                @endif
                                            </div>
                                            <div class="text-slate-500 truncate max-w-[240px]">
                                                {{ $pmt->notes ?: ($pmt->type === 'dp' ? 'Uang Muka (DP)' : 'Tanpa keterangan') }}
                                                @if ($pmt->user)
                                                    <span class="text-slate-400"> • Kasir: {{ $pmt->user->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="font-bold text-emerald-600 whitespace-nowrap">+Rp. {{ number_format($pmt->amount, 0, ',', '.') }}</span>
                                    </div>
                                @empty
                                    <div class="flex items-center justify-between text-2xs">
                                        <div class="space-y-0.5">
                                            <span class="font-semibold text-slate-800">Pembayaran Awal / DP</span>
                                            <div class="text-slate-500">Tercatat saat pembuatan order</div>
                                        </div>
                                        <span class="font-bold text-emerald-600">+Rp. {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif

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

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-medium text-slate-700">Keterangan / Catatan Pembayaran</label>
                            <span class="text-2xs text-slate-400">Pilih cepat:</span>
                        </div>

                        <!-- Quick Tags -->
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            <button
                                type="button"
                                wire:click="$set('payment_notes', 'Uang Muka (DP)')"
                                class="px-2 py-0.5 text-2xs rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer"
                            >
                                Uang Muka (DP)
                            </button>
                            <button
                                type="button"
                                wire:click="$set('payment_notes', 'Pelunasan Servis')"
                                class="px-2 py-0.5 text-2xs rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer"
                            >
                                Pelunasan Servis
                            </button>
                            <button
                                type="button"
                                wire:click="$set('payment_notes', 'Cicilan / Tambahan Pembayaran')"
                                class="px-2 py-0.5 text-2xs rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer"
                            >
                                Cicilan
                            </button>
                            <button
                                type="button"
                                wire:click="$set('payment_notes', 'Biaya Sparepart')"
                                class="px-2 py-0.5 text-2xs rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer"
                            >
                                Biaya Sparepart
                            </button>
                        </div>

                        <textarea
                            wire:model="payment_notes"
                            rows="2"
                            placeholder="Misal: Uang Muka (DP) dari pelanggan, atau catatan bukti transfer..."
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        ></textarea>
                        @error('payment_notes') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
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
                        class="h-10 px-4 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center whitespace-nowrap gap-1.5"
                    >
                        <i data-lucide="loader-circle" wire:loading wire:target="recordPayment" class="animate-spin w-4 h-4"></i>
                        <span wire:loading.remove wire:target="recordPayment">Simpan Pembayaran</span>
                        <span wire:loading wire:target="recordPayment">Menyimpan...</span>
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

    {{-- Modal Dialog: Konfirmasi Hapus Order Secara Permanen --}}
    <div
        x-show="showDeleteOrderModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div
            x-show="showDeleteOrderModal"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="showDeleteOrderModal = false"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                x-show="showDeleteOrderModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 border border-slate-100 space-y-4"
                @click.away="showDeleteOrderModal = false"
            >
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                        <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Konfirmasi Penghapusan Order</h3>
                        <p class="text-xs text-slate-500">Order ID: #{{ $order->order_number }}</p>
                    </div>
                </div>

                <div class="bg-rose-50/70 border border-rose-200/80 rounded-xl p-3.5 text-xs text-rose-900 leading-relaxed space-y-2">
                    <p class="font-semibold text-rose-950">
                        Peringatan: Tindakan ini bersifat permanen dan tidak dapat dibatalkan.
                    </p>
                    <ul class="space-y-1.5 list-disc list-inside text-rose-900">
                        <li><strong>Pencatatan Transaksi:</strong> Order ini akan dihapus dari daftar transaksi servis, rekapan keuangan, dan mutasi kas toko.</li>
                        <li><strong>Riwayat & Dokumentasi:</strong> Seluruh riwayat diagnosa teknisi, checklist kondisi fisik, catatan keluhan, dan foto unit akan terhapus.</li>
                        <li><strong>Nota & Tracking Publik:</strong> Akses tracking online pelanggan akan dinonaktifkan dan tanda terima servis tidak lagi valid.</li>
                    </ul>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Apakah Anda yakin ingin menghapus data servis ini dari sistem secara permanen?
                </p>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        @click="showDeleteOrderModal = false"
                        class="w-full sm:w-auto h-10 px-4 text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer inline-flex items-center justify-center"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="deleteOrder"
                        class="w-full sm:w-auto h-10 px-5 text-xs sm:text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <i data-lucide="loader-circle" wire:loading wire:target="deleteOrder" class="animate-spin w-4 h-4"></i>
                        <i data-lucide="trash-2" wire:loading.remove wire:target="deleteOrder" class="w-4 h-4"></i>
                        <span wire:loading.remove wire:target="deleteOrder">Ya, Hapus Permanen</span>
                        <span wire:loading wire:target="deleteOrder">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

