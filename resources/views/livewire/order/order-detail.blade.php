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
            <div class="flex flex-col justify-center gap-1">
                <span class="text-xs font-bold text-slate-900 leading-tight">Status Pengerjaan Saat Ini</span>
                @php
                    $statusLabel = $statusLabels[$order->status] ?? ($statusOptions[$order->status]['badge'] ?? ucfirst(str_replace('_', ' ', $order->status)));
                    $currentStatusDotColors = [
                        'received' => 'bg-blue-600',
                        'diagnosing' => 'bg-purple-600',
                        'waiting_approval' => 'bg-amber-500',
                        'in_progress' => 'bg-indigo-600',
                        'waiting_parts' => 'bg-orange-500',
                        'testing' => 'bg-violet-600',
                        'ready_for_pickup' => 'bg-sky-500',
                        'completed' => 'bg-emerald-500',
                        'picked_up' => 'bg-slate-700',
                        'unrepairable' => 'bg-rose-600',
                        'cancelled' => 'bg-rose-500',
                        'pending' => 'bg-amber-500',
                    ];
                    $currentDotClass = $currentStatusDotColors[$order->status] ?? 'bg-slate-400';
                @endphp
                <div class="inline-flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $currentDotClass }}"></span>
                    <span class="text-xs sm:text-sm font-medium text-slate-800 leading-tight">{{ $statusLabel }}</span>
                </div>
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
    @if (!$isTechnician)
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
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 1 Col: Customer & Device Overview -->
        <div class="flex flex-col gap-6">
            <!-- Customer Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Data Pelanggan</h3>
                    @if (!$isTechnician && $order->customer)
                        <a href="{{ route('customers.show', $order->customer->id) }}" class="text-2xs font-semibold text-primary-600 hover:underline">
                            Lihat Detail
                        </a>
                    @endif
                </div>

                @if ($order->customer)
                    <dl class="space-y-3 text-xs">
                        <div>
                            <dt class="text-xs font-semibold text-slate-900">Nama Pelanggan</dt>
                            <dd class="mt-0.5 font-normal text-slate-600 break-words leading-relaxed">
                                {{ $order->customer->name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-900">No. WhatsApp / HP</dt>
                            <dd class="mt-0.5 font-normal text-slate-600 break-words">
                                @if ($order->customer->phone)
                                    <a href="https://wa.me/{{ $order->customer->formatted_phone }}" target="_blank" class="font-normal text-slate-600 hover:text-primary-600 hover:underline inline-flex items-center gap-1" title="Buka WhatsApp">
                                        <span>{{ $order->customer->phone }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-500">Tidak ada</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-900">Alamat Pelanggan</dt>
                            <dd class="mt-0.5 font-normal text-slate-600 break-words leading-relaxed">
                                {{ $order->customer->address ?: '-' }}
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="text-xs text-slate-500">Pelanggan Umum (Tanpa Akun)</p>
                @endif
            </div>

            <!-- Customer Complaint Card -->
            <div x-data="{ open: false }" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <button
                    type="button"
                    @click="open = !open"
                    :aria-expanded="open"
                    class="w-full flex items-center justify-between gap-3 text-left cursor-pointer group"
                    :class="{ 'pb-3 border-b border-slate-100': open }"
                >
                    <h3 class="text-base font-bold text-slate-900">Keluhan Pelanggan</h3>
                    <span class="w-7 h-7 rounded-lg bg-slate-50 group-hover:bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-500 group-hover:text-slate-700 transition-colors shrink-0">
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </span>
                </button>
                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="pt-4"
                >
                    <p class="text-xs font-normal text-slate-600 text-justify leading-relaxed break-words">
                        {{ $order->complaints ?: ($order->complaint ?: 'Tidak ada catatan keluhan khusus.') }}
                    </p>
                </div>
            </div>

            <!-- Device Details Card -->
            <div x-data="{ open: false }" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <button
                    type="button"
                    @click="open = !open"
                    :aria-expanded="open"
                    class="w-full flex items-center justify-between gap-3 text-left cursor-pointer group"
                    :class="{ 'pb-3 border-b border-slate-100': open }"
                >
                    <h3 class="text-base font-bold text-slate-900">Data Perangkat</h3>
                    <span class="w-7 h-7 rounded-lg bg-slate-50 group-hover:bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-500 group-hover:text-slate-700 transition-colors shrink-0">
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </span>
                </button>

                @php
                    $deviceImei = $order->device?->imei ?: ($order->device?->serial_number ?: $order->device?->imei_or_serial);
                    $devicePasscode = $order->passcode ?: ($order->device?->passcode ?: $order->device?->security_code);
                @endphp

                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="pt-4"
                >
                    <dl class="space-y-3 text-xs">
                        <div>
                            <dt class="text-xs font-semibold text-slate-900">Merek / Brand</dt>
                            <dd class="mt-0.5 font-normal text-slate-600 break-words leading-relaxed">
                                {{ $order->device?->brand ?: '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-900">Model / Tipe HP</dt>
                            <dd class="mt-0.5 font-normal text-slate-600 break-words leading-relaxed">
                                {{ $order->device?->model ?: '-' }}
                            </dd>
                        </div>
                        @if ($deviceImei)
                            <div>
                                <dt class="text-xs font-semibold text-slate-900">Nomor IMEI / SN</dt>
                                <dd class="mt-0.5 font-normal text-slate-600 break-all">
                                    {{ $deviceImei }}
                                </dd>
                            </div>
                        @endif
                        @if ($devicePasscode)
                            <div>
                                <dt class="text-xs font-semibold text-slate-900">Pola / PIN / Sandi Layar</dt>
                                <dd class="mt-0.5 font-normal text-slate-600 break-words leading-relaxed">
                                    {{ $devicePasscode }}
                                </dd>
                            </div>
                        @endif
                        @if ($order->device?->color)
                            <div>
                                <dt class="text-xs font-semibold text-slate-900">Warna Unit</dt>
                                <dd class="mt-0.5 font-normal text-slate-600 break-words leading-relaxed">
                                    {{ $order->device->color }}
                                </dd>
                            </div>
                        @endif
                        @if ($order->device?->variant)
                            <div>
                                <dt class="text-xs font-semibold text-slate-900">Varian (RAM / Internal)</dt>
                                <dd class="mt-0.5 font-normal text-slate-600 break-words leading-relaxed">
                                    {{ $order->device->variant }}
                                </dd>
                            </div>
                        @endif
                        @if ($order->device?->specs)
                            <div>
                                <dt class="text-xs font-semibold text-slate-900">Spesifikasi Lainnya</dt>
                                <dd class="mt-0.5 font-normal text-slate-600 break-words leading-relaxed">
                                    {{ $order->device->specs }}
                                </dd>
                            </div>
                        @endif
                        @if ($order->accessories_included || $order->accessories_brought)
                            <div>
                                <dt class="text-xs font-semibold text-slate-900">Kelengkapan yang Dititipkan</dt>
                                <dd class="mt-0.5 font-normal text-slate-600 break-words leading-relaxed">
                                    {{ $order->accessories_included ?: $order->accessories_brought }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Physical Condition Card -->
            @php
                $physicalCond = $order->physical_condition ?? [];
                $physicalLabels = [
                    'screen_condition' => 'Kaca Layar',
                    'lcd_condition' => 'Tampilan LCD',
                    'body_condition' => 'Kondisi Bodi / Backdoor',
                    'camera_condition' => 'Kamera Depan/Belakang',
                    'buttons_condition' => 'Tombol Fisik',
                    'charging_condition' => 'Port Charger',
                ];
                $isAssoc = is_array($physicalCond) && array_keys($physicalCond) !== range(0, count($physicalCond) - 1);
            @endphp
            @if (is_array($physicalCond) && count($physicalCond) > 0)
                <div x-data="{ open: false }" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <button
                        type="button"
                        @click="open = !open"
                        :aria-expanded="open"
                        class="w-full flex items-center justify-between gap-3 text-left cursor-pointer group"
                        :class="{ 'pb-3 border-b border-slate-100': open }"
                    >
                        <h3 class="text-base font-bold text-slate-900">Kondisi Fisik Awal</h3>
                        <span class="w-7 h-7 rounded-lg bg-slate-50 group-hover:bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-500 group-hover:text-slate-700 transition-colors shrink-0">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </span>
                    </button>
                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="pt-4"
                    >
                        <dl class="space-y-3 text-xs">
                            @if ($isAssoc)
                                @foreach ($physicalCond as $itemKey => $itemVal)
                                    @if (!empty($itemVal))
                                        <div>
                                            <dt class="text-xs font-semibold text-slate-900">
                                                {{ $physicalLabels[$itemKey] ?? ucwords(str_replace('_', ' ', $itemKey)) }}
                                            </dt>
                                            <dd class="mt-0.5 font-normal text-slate-600 break-words leading-relaxed">
                                                {{ $itemVal }}
                                            </dd>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                @foreach ($physicalCond as $idx => $itemVal)
                                    @if (!empty($itemVal))
                                        <div>
                                            <dt class="text-xs font-semibold text-slate-900">
                                                {{ count($physicalCond) > 1 ? 'Kondisi ' . ($idx + 1) : 'Kondisi Fisik' }}
                                            </dt>
                                            <dd class="mt-0.5 font-normal text-slate-600 break-words leading-relaxed">
                                                {{ $itemVal }}
                                            </dd>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </dl>
                    </div>
                </div>
            @elseif (is_array($order->physical_condition_checklist) && count($order->physical_condition_checklist) > 0)
                <div x-data="{ open: false }" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <button
                        type="button"
                        @click="open = !open"
                        :aria-expanded="open"
                        class="w-full flex items-center justify-between gap-3 text-left cursor-pointer group"
                        :class="{ 'pb-3 border-b border-slate-100': open }"
                    >
                        <h3 class="text-base font-bold text-slate-900">Kondisi Fisik Awal</h3>
                        <span class="w-7 h-7 rounded-lg bg-slate-50 group-hover:bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-500 group-hover:text-slate-700 transition-colors shrink-0">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </span>
                    </button>
                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="pt-4"
                    >
                        <dl class="space-y-3 text-xs">
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
                                <div>
                                    <dt class="text-xs font-semibold text-slate-900">
                                        {{ $checklistLabels[$k] ?? ucwords(str_replace('_', ' ', $k)) }}
                                    </dt>
                                    <dd class="mt-0.5 font-normal text-slate-600 break-words">
                                        @if ($val)
                                            <span class="text-emerald-700">✓ Normal / Baik</span>
                                        @else
                                            <span class="text-rose-600">✕ Bermasalah / Rusak</span>
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
            @endif

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
                    @if (!$isTechnician)
                        <button
                            type="button"
                            wire:click="$set('showAssignTechModal', true)"
                            class="text-2xs font-semibold text-primary-600 hover:underline cursor-pointer"
                        >
                            Ganti Teknisi
                        </button>
                    @endif
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
                    <div class="py-2.5 text-center text-slate-500 text-xs font-normal">
                        Belum ada teknisi yang ditugaskan.
                    </div>
                @endif
            </div>

            <!-- Kelola Order (Edit & Hapus) - Desktop Only -->
            @if (!$isTechnician)
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
            @endif
        </div>

        <!-- Right 2 Cols: Services & Cost Breakdown, Diagnosis, Timeline -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            <!-- Billing & Items Card -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-2xs space-y-5">
                <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Rincian Biaya</h2>
                    <button
                        type="button"
                        wire:click="openAddItemModal"
                        class="h-9 sm:h-10 px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors shadow-2xs inline-flex items-center justify-center whitespace-nowrap shrink-0 cursor-pointer gap-1.5"
                    >
                        <i data-lucide="plus" class="w-4 h-4 shrink-0"></i>
                        <span>Tambah Biaya</span>
                    </button>
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
                                        <div>{{ $item->name }}</div>
                                        @if ($item->orderPart?->product)
                                            <div class="mt-1 flex items-center gap-1.5 text-2xs text-slate-500 font-normal">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">
                                                    Sparepart: {{ $item->orderPart->product->name }} ({{ $item->orderPart->quantity }} {{ $item->orderPart->product->unit ?? 'pcs' }})
                                                </span>
                                            </div>
                                        @endif
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
                                                    text: '{{ $item->orderPart?->product ? 'Hapus item ini dan kembalikan stok sparepart ke inventaris?' : 'Hapus \'' . addslashes($item->name) . '\' dari rincian biaya servis?' }}',
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
                                    <td colspan="3" class="py-12 text-center text-slate-500 text-xs font-normal">
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

            <!-- History Timeline Log -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Riwayat & Log Aktivitas</h2>
                </div>

                @if ($order->histories->isNotEmpty())
                    <div class="relative pl-7 space-y-5 before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:-translate-x-1/2 before:bg-slate-200">
                        @foreach ($order->histories->sortByDesc('created_at') as $history)
                            <div class="text-xs">
                                <div class="relative flex items-center justify-between gap-2 min-h-4">
                                    <div class="absolute -left-4 top-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 rounded-full bg-primary-600 border-2 border-white ring-2 ring-primary-100"></div>
                                    <span class="font-bold text-slate-800 leading-none">
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
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 text-center py-4">Belum ada catatan riwayat.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal: Tambah Biaya -->
    @if ($showAddItemModal)
        <div
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            @keydown.escape.window="$wire.set('showAddItemModal', false)"
        >
            <div
                class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-5 border border-slate-200"
                @click.outside="$wire.set('showAddItemModal', false)"
            >
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Tambah Biaya</h2>
                    <button
                        type="button"
                        wire:click="$set('showAddItemModal', false)"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                    >
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                @php
                    $selectedCatalogService = $selected_service_id
                        ? $availableServices->firstWhere('id', $selected_service_id)
                        : null;
                @endphp

                <form wire:submit="addItem" class="space-y-4">
                    @if ($availableServices->isNotEmpty())
                        <div x-data="{ openCatalog: false }" @click.outside="openCatalog = false" class="relative">
                            <label class="block text-xs font-semibold text-slate-900 mb-2">
                                Pilih dari Katalog Layanan
                            </label>
                            <button
                                type="button"
                                @click="openCatalog = !openCatalog"
                                class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl border {{ $selectedCatalogService ? 'border-primary-500 bg-primary-50/40 text-primary-900 font-semibold' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }} text-xs transition-all cursor-pointer focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                                <span class="truncate">
                                    @if ($selectedCatalogService)
                                        {{ $selectedCatalogService->name }} — Rp {{ number_format($selectedCatalogService->price, 0, ',', '.') }}
                                    @else
                                        Input Manual / Tanpa Jasa Katalog
                                    @endif
                                </span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': openCatalog }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </button>

                            <div
                                x-show="openCatalog"
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute left-0 right-0 mt-1.5 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50 max-h-56 overflow-y-auto divide-y divide-slate-50"
                            >
                                <button
                                    type="button"
                                    wire:click="selectCatalogService('')"
                                    @click="openCatalog = false"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ !$selected_service_id ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"
                                >
                                    <span>Input Manual / Tanpa Jasa Katalog</span>
                                    @if (!$selected_service_id)
                                        <svg class="w-3.5 h-3.5 text-primary-600 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    @endif
                                </button>

                                @foreach ($availableServices as $srv)
                                    <button
                                        type="button"
                                        wire:click="selectCatalogService('{{ $srv->id }}')"
                                        @click="openCatalog = false"
                                        class="w-full flex items-center justify-between gap-3 px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ $selected_service_id === $srv->id ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}"
                                    >
                                        <span class="truncate">{{ $srv->name }}</span>
                                        <span class="shrink-0 font-semibold {{ $selected_service_id === $srv->id ? 'text-primary-700' : 'text-slate-500' }}">
                                            Rp {{ number_format($srv->price, 0, ',', '.') }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Sparepart Selector (from Inventory) -->
                    <div x-data="{ openParts: false }" @click.outside="openParts = false" class="space-y-2.5">
                        <div class="relative">
                            <label class="block text-xs font-semibold text-slate-900 mb-2">
                                Gunakan Sparepart Inventaris <span class="font-normal text-slate-400">(Opsional)</span>
                            </label>
                            <button
                                type="button"
                                @click="openParts = !openParts"
                                class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl border {{ $selectedProduct ? 'border-primary-500 bg-primary-50/40 text-primary-900 font-semibold' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }} text-xs transition-all cursor-pointer focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                                <span class="truncate">
                                    @if ($selectedProduct)
                                        {{ $selectedProduct->name }} — Sisa {{ $selectedProduct->stock }} {{ $selectedProduct->unit }}
                                    @else
                                        Tanpa Sparepart (Jasa Saja / Manual)
                                    @endif
                                </span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': openParts }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </button>

                            <div
                                x-show="openParts"
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute left-0 right-0 mt-1.5 bg-white rounded-xl shadow-xl border border-slate-200 z-50 overflow-hidden"
                            >
                                <div class="p-2 border-b border-slate-100 bg-slate-50/60">
                                    <input
                                        type="text"
                                        wire:model.live.debounce.250ms="part_search"
                                        placeholder="Cari nama sparepart atau SKU..."
                                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                                    />
                                </div>

                                <div class="max-h-52 overflow-y-auto divide-y divide-slate-50 py-1">
                                    <button
                                        type="button"
                                        wire:click="selectSparepart('')"
                                        @click="openParts = false"
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ !$selected_product_id ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"
                                    >
                                        <span>Tanpa Sparepart (Jasa Saja / Manual)</span>
                                        @if (!$selected_product_id)
                                            <svg class="w-3.5 h-3.5 text-primary-600 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                        @endif
                                    </button>

                                    @forelse ($availableProducts as $prod)
                                        <button
                                            type="button"
                                            wire:click="selectSparepart('{{ $prod->id }}')"
                                            @click="openParts = false"
                                            class="w-full flex items-center justify-between gap-3 px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ $selected_product_id === $prod->id ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}"
                                        >
                                            <div class="min-w-0">
                                                <div class="truncate font-medium">{{ $prod->name }}</div>
                                                <div class="text-2xs text-slate-400 mt-0.5">
                                                    Stok: <span class="font-semibold text-slate-600">{{ $prod->stock }} {{ $prod->unit }}</span>
                                                    {{ $prod->sku ? ' • ' . $prod->sku : '' }}
                                                </div>
                                            </div>
                                            <span class="shrink-0 font-semibold {{ $selected_product_id === $prod->id ? 'text-primary-700' : 'text-slate-600' }}">
                                                {{ (float) $prod->sell_price > 0 ? 'Rp ' . number_format($prod->sell_price, 0, ',', '.') : 'Rp 0' }}
                                            </span>
                                        </button>
                                    @empty
                                        <div class="px-3.5 py-4 text-center text-2xs text-slate-400">
                                            Tidak ada sparepart tersedia yang cocok.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        @if ($selectedProduct)
                            <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                                <div class="min-w-0">
                                    <span class="font-semibold text-slate-800 block">Jumlah Pakai</span>
                                    <span class="text-2xs text-emerald-600 font-medium">
                                        Stok inventaris berkurang {{ $part_quantity }} {{ $selectedProduct->unit }}
                                    </span>
                                </div>
                                <div class="inline-flex items-center rounded-lg border border-slate-200 bg-white overflow-hidden shrink-0">
                                    <button
                                        type="button"
                                        wire:click="decrementPartQuantity"
                                        @disabled($part_quantity <= 1)
                                        class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-slate-50 disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed font-bold"
                                    >
                                        −
                                    </button>
                                    <span class="w-9 text-center font-bold text-slate-900 text-xs select-none">
                                        {{ $part_quantity }}
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="incrementPartQuantity"
                                        @disabled($part_quantity >= $selectedProduct->stock)
                                        class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-slate-50 disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed font-bold"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                        @endif

                        @error('selected_product_id')
                            <span class="text-rose-500 text-2xs block">{{ $message }}</span>
                        @enderror
                        @error('part_quantity')
                            <span class="text-rose-500 text-2xs block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="modal_item_name" class="block text-xs font-semibold text-slate-900 mb-2">
                            Nama Layanan / Sparepart <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="modal_item_name"
                            type="text"
                            wire:model="item_name"
                            placeholder="Contoh: Ganti LCD OLED Samsung A52"
                            class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-all"
                        />
                        @error('item_name')
                            <span class="text-rose-500 text-2xs block mt-1.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="modal_item_price" class="block text-xs font-semibold text-slate-900 mb-2">
                            Nominal Biaya <span class="text-rose-500">*</span>
                        </label>
                        <div
                            x-data="{
                                rawPrice: $wire.entangle('item_price'),
                                displayPrice: '',
                                init() {
                                    this.syncFromWire(this.rawPrice);
                                    this.$watch('rawPrice', (val) => this.syncFromWire(val));
                                },
                                syncFromWire(val) {
                                    const num = Number(val || 0);
                                    this.displayPrice = num > 0 ? new Intl.NumberFormat('id-ID').format(num) : '';
                                },
                                updatePrice(e) {
                                    const digits = (e.target.value || '').replace(/\D/g, '');
                                    const num = digits ? Number(digits) : 0;
                                    this.rawPrice = num;
                                    this.displayPrice = digits ? new Intl.NumberFormat('id-ID').format(num) : '';
                                }
                            }"
                            class="relative"
                        >
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-semibold text-slate-400">
                                Rp
                            </span>
                            <input
                                id="modal_item_price"
                                type="text"
                                inputmode="numeric"
                                :value="displayPrice"
                                @input="updatePrice($event)"
                                placeholder="0"
                                class="block w-full rounded-xl border border-slate-200 pl-9 pr-3.5 py-2.5 text-xs font-semibold text-slate-900 placeholder:font-normal placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-all"
                            />
                        </div>
                        @error('item_price')
                            <span class="text-rose-500 text-2xs block mt-1.5">{{ $message }}</span>
                        @enderror
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
                            type="submit"
                            wire:loading.attr="disabled"
                            class="h-10 px-4 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center gap-1.5 whitespace-nowrap"
                        >
                            <i data-lucide="loader-circle" class="w-3.5 h-3.5 animate-spin" wire:loading wire:target="addItem"></i>
                            <span>Simpan Biaya</span>
                        </button>
                    </div>
                </form>
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
                        <label class="block font-semibold text-slate-900 mb-2">Nominal Pembayaran Diterima <span class="text-rose-500">*</span></label>
                        <div
                            x-data="{
                                rawAmount: $wire.entangle('payment_amount'),
                                displayAmount: '',
                                init() {
                                    this.syncFromWire(this.rawAmount);
                                    this.$watch('rawAmount', (val) => this.syncFromWire(val));
                                },
                                syncFromWire(val) {
                                    const num = Number(val || 0);
                                    this.displayAmount = num > 0 ? new Intl.NumberFormat('id-ID').format(num) : '';
                                },
                                updateAmount(e) {
                                    const digits = (e.target.value || '').replace(/\D/g, '');
                                    const num = digits ? Number(digits) : 0;
                                    this.rawAmount = num;
                                    this.displayAmount = digits ? new Intl.NumberFormat('id-ID').format(num) : '';
                                }
                            }"
                            class="relative"
                        >
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-semibold text-slate-400">
                                Rp
                            </span>
                            <input
                                type="text"
                                inputmode="numeric"
                                :value="displayAmount"
                                @input="updateAmount($event)"
                                placeholder="0"
                                class="block w-full rounded-xl border border-slate-200 pl-9 pr-3.5 py-2.5 text-xs font-semibold text-slate-900 placeholder:font-normal placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-all"
                            />
                        </div>
                        @error('payment_amount') <span class="text-rose-500 text-2xs block mt-1.5">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-900 mb-2">Metode Pembayaran</label>
                        <select
                            wire:model="payment_method"
                            class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 bg-white text-xs text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-all"
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
                            class="block w-full rounded-xl border border-slate-200 p-3 text-xs text-slate-800 placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-all"
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
                    <label class="block font-semibold text-slate-900 mb-2">Pilih Teknisi Penanggung Jawab</label>
                    <select
                        wire:model="assigned_technician_id"
                        class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 bg-white text-xs text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-all"
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

    <!-- Modal: Perbarui Status -->
    @if ($showUpdateStatusModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-5 border border-slate-200">
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Perbarui Status</h2>
                    <button
                        type="button"
                        wire:click="$set('showUpdateStatusModal', false)"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                    >
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                @php
                    $statusDotColors = [
                        'received' => 'bg-blue-600',
                        'diagnosing' => 'bg-purple-600',
                        'waiting_approval' => 'bg-amber-500',
                        'in_progress' => 'bg-indigo-600',
                        'waiting_parts' => 'bg-orange-500',
                        'testing' => 'bg-violet-600',
                        'ready_for_pickup' => 'bg-sky-500',
                        'completed' => 'bg-emerald-500',
                        'unrepairable' => 'bg-rose-600',
                        'cancelled' => 'bg-rose-500',
                    ];
                @endphp

                <form wire:submit="updateStatusWithNotes" class="space-y-5">
                    <div>
                        <label class="block font-semibold text-slate-900 text-xs mb-2.5">
                            Pilih Status Baru <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @foreach ($statusOptions as $key => $meta)
                                @php
                                    $isSelected = $new_status === $key;
                                    $dotClass = $statusDotColors[$key] ?? 'bg-slate-400';
                                @endphp
                                <label class="group relative flex items-center justify-between gap-2.5 px-3.5 py-2.5 rounded-xl border cursor-pointer transition-all {{ $isSelected ? 'bg-primary-50/70 border-primary-500 ring-2 ring-primary-500/15' : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/70' }}">
                                    <input
                                        type="radio"
                                        wire:model.live="new_status"
                                        value="{{ $key }}"
                                        class="sr-only"
                                    >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $dotClass }}"></span>
                                        <span class="text-xs truncate {{ $isSelected ? 'font-semibold text-primary-900' : 'font-medium text-slate-700 group-hover:text-slate-900' }}">
                                            {{ $meta['badge'] ?? $meta['label'] }}
                                        </span>
                                    </div>
                                    <span class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 transition-colors {{ $isSelected ? 'bg-primary-600 border border-primary-600 text-white' : 'border border-slate-300 bg-white group-hover:border-slate-400' }}">
                                        @if ($isSelected)
                                            <svg class="w-2.5 h-2.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('new_status')
                            <span class="text-rose-500 text-2xs mt-1.5 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-900 text-xs mb-2">
                            Keterangan
                        </label>
                        <textarea
                            wire:model="status_notes"
                            rows="3"
                            placeholder="Contoh: IC CPU short, unit tidak bisa diperbaiki / Menunggu sparepart LCD tiba dari suplier / Selesai QC siap diserahkan..."
                            class="block w-full rounded-xl border border-slate-200 p-3 text-xs text-slate-800 placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-all"
                        ></textarea>
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
                            <span>Simpan Status</span>
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

    @if (!$isTechnician)
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
    @endif
</div>

