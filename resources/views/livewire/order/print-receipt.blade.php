@php
    $imei = $order->device?->imei ?? $order->device?->serial_number ?? $order->device?->imei_or_serial;
    $pass = $order->passcode ?? $order->device?->passcode ?? $order->device?->security_code;
    $kelengkapan = $order->accessories_included ?? $order->accessories_brought ?? 'Unit saja';
    $complaintText = $order->complaints ?: ($order->complaint ?: '-');
    $trackingUrl = Route::has('tracking.show')
        ? route('tracking.show', $order->order_number)
        : url('/track/' . $order->order_number);
    $storeDefaultWarranty = (int) ($order->store?->getSetting('default_warranty_days', 30) ?? 30);
    $warrantyDays = ($order->status === 'picked_up' && $order->warranty_ends_at)
        ? (int) max(1, round($order->warranty_ends_at->diffInDays($order->picked_up_at ?? now())))
        : $storeDefaultWarranty;

    $conditionLabels = [
        'screen_condition' => 'Kaca Layar',
        'lcd_condition' => 'Tampilan LCD',
        'body_condition' => 'Kondisi Bodi / Backdoor',
        'camera_condition' => 'Kamera Depan/Belakang',
        'buttons_condition' => 'Tombol Fisik',
        'charging_condition' => 'Port Charger',
    ];

    $conditionValueLabels = [
        'screen_condition' => [
            'Normal' => 'Normal Mulus',
            'Gores Pemakaian' => 'Gores Pemakaian',
            'Retak Rambut' => 'Retak Rambut',
            'Pecah Parah' => 'Pecah Parah',
        ],
        'lcd_condition' => [
            'Normal' => 'Normal Jernih',
            'Bergaris' => 'Bergaris',
            'Ada Tompel / Dot' => 'Ada Tompel / Dot',
            'Blank Hitam' => 'Blank Total',
        ],
        'body_condition' => [
            'Mulus' => 'Mulus',
            'Lecet Bezel' => 'Lecet Bezel',
            'Penyok / Bengkok' => 'Penyok / Bengkok',
            'Backdoor Pecah' => 'Backdoor Pecah',
        ],
        'camera_condition' => [
            'Normal' => 'Normal Jernih',
            'Buram / Berdebu' => 'Buram / Berdebu',
            'Kaca Kamera Retak' => 'Kaca Kamera Retak',
            'Mati / Blank' => 'Mati / Blank',
        ],
        'buttons_condition' => [
            'Normal' => 'Normal Empuk',
            'Keras / Mendem' => 'Keras / Mendem',
            'Mati / Copot' => 'Mati / Copot',
        ],
        'charging_condition' => [
            'Normal' => 'Normal Kencang',
            'Longgar' => 'Longgar',
            'Patah / Rusak' => 'Patah / Rusak',
        ],
    ];

    // Format kondisi fisik agar key:value terbaca rapi dalam Bahasa Indonesia sesuai form
    $physicalItems = [];
    if (is_array($order->physical_condition)) {
        $isAssoc = array_keys($order->physical_condition) !== range(0, count($order->physical_condition) - 1);
        foreach ($order->physical_condition as $key => $val) {
            if (blank($val))
                continue;
            if ($isAssoc) {
                $label = $conditionLabels[$key] ?? (
                    strlen((string) $key) <= 3
                        ? strtoupper((string) $key)
                        : ucwords(str_replace(['_', '-'], ' ', (string) $key))
                );
                $displayVal = $conditionValueLabels[$key][$val] ?? $val;
                $physicalItems[] = ['label' => $label, 'value' => $displayVal];
            } else {
                $physicalItems[] = ['label' => null, 'value' => $val];
            }
        }
    } elseif (!empty($order->physical_condition)) {
        $physicalItems[] = ['label' => null, 'value' => $order->physical_condition];
    }

    $physicalText = collect($physicalItems)
        ->map(fn($item) => $item['label'] ? "{$item['label']}: {$item['value']}" : $item['value'])
        ->implode(', ');

    $deviceName = trim(
        ($order->device?->brand ?? '') . ' ' .
        ($order->device?->model ?? '') .
        ($order->device?->variant ? ' (' . $order->device->variant . ')' : '')
    ) ?: '-';
@endphp

<div class="min-h-screen bg-slate-100 py-6 px-4 print:min-h-0 print:bg-white print:p-0">
    <!-- Non-printable Actions Toolbar -->
    <div
        class="max-w-3xl mx-auto mb-6 p-3 sm:p-4 bg-white rounded-2xl border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print:hidden">
        <!-- Kiri: Kembali -->
        <x-button href="{{ route('orders.show', $order->id) }}" variant="outline" icon="arrow-left"
            class="w-full sm:w-auto">
            Kembali ke Order
        </x-button>

        <!-- Tengah: Format Selector -->
        <div
            class="inline-flex items-center justify-center rounded-xl bg-slate-100 p-1 border border-slate-200 w-full sm:w-auto h-10">
            @if ($type !== 'intake')
                <button type="button" wire:click="$set('format', 'thermal')"
                    class="flex-1 sm:flex-none h-8 px-4 inline-flex items-center justify-center text-sm font-semibold rounded-lg transition-all cursor-pointer {{ $format === 'thermal' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Thermal
                </button>
            @endif
            <button type="button" wire:click="$set('format', 'a5')"
                class="flex-1 sm:flex-none h-8 px-4 inline-flex items-center justify-center text-sm font-semibold rounded-lg transition-all cursor-pointer {{ $format === 'a5' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Kertas A5
            </button>
            <button type="button" wire:click="$set('format', 'a4')"
                class="flex-1 sm:flex-none h-8 px-4 inline-flex items-center justify-center text-sm font-semibold rounded-lg transition-all cursor-pointer {{ ($format === 'a4' || $format === 'standard') ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Kertas A4
            </button>
        </div>

        <!-- Kanan: Tombol Print -->
        <x-button type="button" variant="primary" icon="printer" onclick="window.print()" class="w-full sm:w-auto">
            Cetak {{ $type === 'intake' ? 'Tanda Terima' : 'Nota Servis' }}
        </x-button>
    </div>

    <!-- PRINT STYLES -->
    <style>
        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                margin: 0 !important;
                @if ($format === 'thermal' && $type !== 'intake')
                    size: auto;
                @elseif ($format === 'a5')
                    size: A5 portrait;
                @else size: A4 portrait;
                @endif
            }

            /* Single-page fit guarantee */
            .print-standard-doc {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .print-standard-doc,
            .print-standard-doc * {
                color: #0f172a !important;
            }

            @if ($format === 'a4')
                /* A4 Layout: Lega & Proporsional */
                .print-standard-doc {
                    padding: 8mm 12mm !important;
                    font-size: 13px !important;
                    line-height: 1.55 !important;
                }

                .print-standard-doc .sec-py {
                    padding-top: 14px !important;
                    padding-bottom: 14px !important;
                }

                .print-standard-doc .sig-container {
                    padding-top: 56px !important;
                }

                .print-standard-doc .sig-line {
                    padding-bottom: 72px !important;
                }

                .print-standard-doc h1 {
                    font-size: 22px !important;
                }

                .print-standard-doc h2 {
                    font-size: 16px !important;
                }

                .print-standard-doc .text-sm {
                    font-size: 15px !important;
                }

                .print-standard-doc .text-xs {
                    font-size: 13px !important;
                }

                .print-standard-doc .text-[11px] {
                    font-size: 12px !important;
                }

                .print-standard-doc .text-[10px] {
                    font-size: 11px !important;
                }

                .print-standard-doc .tbl-td {
                    padding-top: 8px !important;
                    padding-bottom: 8px !important;
                    font-size: 13px !important;
                }

            @else

                /* A5 Layout: Kompak & Pas 1 Lembar A5 */
                .print-standard-doc {
                    padding: 4mm 6mm !important;
                    font-size: 10.5px !important;
                    line-height: 1.35 !important;
                }

                .print-standard-doc .sec-py {
                    padding-top: 6px !important;
                    padding-bottom: 6px !important;
                }

                .print-standard-doc .sig-container {
                    padding-top: 36px !important;
                }

                .print-standard-doc .sig-line {
                    padding-bottom: 56px !important;
                }

                .print-standard-doc h1 {
                    font-size: 15px !important;
                }

                .print-standard-doc h2 {
                    font-size: 12px !important;
                }

                .print-standard-doc .text-sm {
                    font-size: 12px !important;
                }

                .print-standard-doc .text-xs {
                    font-size: 10.5px !important;
                }

                .print-standard-doc .text-[11px] {
                    font-size: 10px !important;
                }

                .print-standard-doc .text-[10px] {
                    font-size: 9px !important;
                }

                .print-standard-doc .tbl-td {
                    padding-top: 4px !important;
                    padding-bottom: 4px !important;
                    font-size: 10.5px !important;
                }

            @endif
        }
    </style>

    @if ($format === 'thermal' && $type !== 'intake')
        <!-- ========================================================= -->
        <!-- THERMAL RECEIPT FORMAT (58mm - 80mm)                      -->
        <!-- ========================================================= -->
        <div
            class="w-full max-w-[80mm] mx-auto bg-white p-4 text-black text-[11px] leading-snug border border-slate-300 shadow-md print:shadow-none print:border-none print:p-2 print:max-w-full">
            <!-- Header Toko -->
            <div class="text-center pb-3 border-b border-dashed border-black">
                <h1 class="text-sm font-bold uppercase tracking-wide">{{ $store->name ?? 'SEMAPON SERVICE' }}</h1>
                @if ($store->address)
                    <p class="text-[10px] leading-tight mt-1">{{ $store->address }}</p>
                @endif
                @if ($store->phone)
                    <p class="text-[10px] mt-0.5">Telp/WA: {{ $store->phone }}</p>
                @endif

                <div class="mt-2.5 pt-2 border-t border-dotted border-black">
                    <p class="text-xs font-bold uppercase tracking-wider">
                        {{ $type === 'intake' ? 'TANDA TERIMA SERVIS' : 'NOTA SERVIS' }}
                    </p>
                </div>
            </div>

            <!-- Metadata Order -->
            <div class="py-2.5 border-b border-dashed border-black space-y-1 text-[10.5px]">
                <div class="flex justify-between gap-2">
                    <span class="shrink-0">No. {{ $type === 'intake' ? 'Order' : 'Nota' }}</span>
                    <span class="font-bold text-right">#{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="shrink-0">Tanggal</span>
                    <span class="text-right">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="shrink-0">Pelanggan</span>
                    <span class="font-bold text-right">{{ $order->customer?->name ?? 'Umum' }}</span>
                </div>
                @if ($order->customer?->phone)
                    <div class="flex justify-between gap-2">
                        <span class="shrink-0">No. HP</span>
                        <span class="text-right">{{ $order->customer->phone }}</span>
                    </div>
                @endif
                @if ($order->customer?->address)
                    <div class="flex justify-between gap-2">
                        <span class="shrink-0">Alamat</span>
                        <span class="text-right leading-tight">{{ $order->customer->address }}</span>
                    </div>
                @endif
                <div class="flex justify-between gap-2">
                    <span class="shrink-0">Teknisi</span>
                    <span class="text-right">{{ $order->technician?->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="shrink-0">Est. Selesai</span>
                    <span
                        class="text-right">{{ $order->estimated_completion_at ? $order->estimated_completion_at->format('d/m/Y H:i') : 'Konfirmasi WA' }}</span>
                </div>
            </div>

            <!-- Informasi Perangkat -->
            <div class="py-2.5 border-b border-dashed border-black space-y-1.5 text-[10.5px]">
                <div>
                    <span class="block text-[9.5px] uppercase">Perangkat:</span>
                    <span class="font-bold block">{{ $deviceName }}</span>
                </div>

                @if ($order->device?->color)
                    <div class="flex justify-between gap-2">
                        <span class="shrink-0">Warna</span>
                        <span class="text-right">{{ $order->device->color }}</span>
                    </div>
                @endif

                <div>
                    <span class="block text-[9.5px] uppercase">Kelengkapan:</span>
                    <span class="block">{{ $kelengkapan }}</span>
                </div>

                @if ($physicalText)
                    <div>
                        <span class="block text-[9.5px] uppercase">Kondisi Fisik:</span>
                        <span class="block leading-tight">{{ $physicalText }}</span>
                    </div>
                @endif

                <div>
                    <span class="block text-[9.5px] uppercase">Keluhan:</span>
                    <span class="font-bold block leading-tight">{{ $complaintText }}</span>
                </div>
            </div>

            @if ($type === 'intake')
                <!-- Estimasi & DP (Intake) -->
                <div class="py-2.5 border-b border-dashed border-black space-y-1 text-[10.5px]">
                    @if ($order->down_payment > 0 || $order->paid_amount > 0)
                        <div class="flex justify-between gap-2 font-bold">
                            <span>TITIPAN DP</span>
                            <span>Rp{{ number_format(max($order->down_payment, $order->paid_amount), 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between gap-2">
                        <span>Estimasi Biaya</span>
                        <span class="font-bold text-right">
                            @if ($order->is_cost_undetermined || (!$order->estimated_cost && !$order->total_cost))
                                Menunggu Diagnosa
                            @else
                                Rp{{ number_format($order->estimated_cost ?: $order->total_cost, 0, ',', '.') }}
                            @endif
                        </span>
                    </div>
                    <p class="text-[9px] italic pt-1 leading-tight">
                        *Estimasi biaya & pengerjaan akan dikonfirmasi via WA/Telp sebelum dikerjakan.
                    </p>
                </div>

                <!-- Ketentuan & Tanda Tangan (Intake) -->
                <div class="pt-2.5 space-y-3 text-[9.5px] leading-tight">
                    <ol class="list-decimal pl-3.5 space-y-0.5 text-left">
                        <li>Tanda terima ini adalah bukti sah penyerahan & pengambilan unit servis.</li>
                        <li>Pengambilan unit wajib membawa lembar bukti ini.</li>
                    </ol>

                    <div class="pt-2 grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="border-b border-dotted border-black h-12"></div>
                            <p class="mt-1 font-bold text-[9px]">Penerima Toko</p>
                        </div>
                        <div>
                            <div class="border-b border-dotted border-black h-12"></div>
                            <p class="mt-1 font-bold text-[9px]">Pelanggan</p>
                        </div>
                    </div>

                    <div class="p-2 border border-dashed border-black text-center">
                        <p class="font-bold text-[9.5px] uppercase">Lacak Servis Online</p>
                        <p class="text-[8.5px] break-all mt-0.5">{{ $trackingUrl }}</p>
                    </div>

                    <p class="text-center font-bold pt-1">Terima kasih atas kepercayaan Anda!</p>
                </div>
            @else
                <!-- Rincian Jasa & Sparepart (Nota) -->
                <div class="py-2.5 border-b border-dashed border-black space-y-1.5 text-[10.5px]">
                    <span class="font-bold uppercase block text-[9.5px]">Rincian Servis & Sparepart:</span>
                    @forelse ($order->items as $item)
                        <div class="flex justify-between items-start gap-2">
                            <span class="leading-tight">{{ $item->name }}</span>
                            <span class="font-bold shrink-0">Rp{{ number_format($item->price, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="italic text-[10px]">Belum ada rincian jasa</div>
                    @endforelse
                </div>

                <!-- Total Biaya (Nota) -->
                <div class="py-2.5 border-b border-dashed border-black space-y-1 text-[10.5px]">
                    @if ($order->is_cost_undetermined)
                        <div class="flex justify-between font-bold">
                            <span>TOTAL BIAYA</span>
                            <span>Menunggu Diagnosa</span>
                        </div>
                        @if ($order->paid_amount > 0)
                            <div class="flex justify-between">
                                <span>Titipan DP</span>
                                <span>Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between pt-1 border-t border-dotted border-black font-bold">
                            <span>SISA TAGIHAN</span>
                            <span>Menyusul</span>
                        </div>
                    @else
                        <div class="flex justify-between font-bold">
                            <span>TOTAL</span>
                            <span>Rp{{ number_format($order->total_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>DP / Terbayar</span>
                            <span>Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-xs pt-1 border-t border-dotted border-black">
                            <span>SISA</span>
                            <span>Rp{{ number_format($order->remaining_balance, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Footer & Garansi (Nota) -->
                <div class="pt-2.5 space-y-2.5 text-[9.5px] leading-tight">
                    @if ($warrantyDays > 0)
                        <p class="text-center font-bold border border-black py-1 uppercase">
                            GARANSI TOKO: {{ $warrantyDays }} HARI
                        </p>
                    @endif

                    @php
                        $storeWarrantyTerms = trim($order->store?->getSetting('warranty_terms') ?? '');
                        $warrantyTermsLines = $storeWarrantyTerms ? array_filter(array_map('trim', explode("\n", $storeWarrantyTerms))) : [];
                    @endphp
                    @if (!empty($warrantyTermsLines))
                        <div class="space-y-0.5 text-left text-[9px] leading-tight">
                            @foreach ($warrantyTermsLines as $termLine)
                                <p>{{ $termLine }}</p>
                            @endforeach
                        </div>
                    @else
                        <ol class="list-decimal pl-3.5 space-y-0.5 text-left">
                            <li>Nota asli wajib dibawa saat pengambilan unit.</li>
                            <li>Barang tidak diambil lebih dari 30 hari di luar tanggung jawab kami.</li>
                            <li>Garansi gugur jika segel rusak / terkena cairan / human error.</li>
                        </ol>
                    @endif

                    <div class="p-2 border border-dashed border-black text-center">
                        <p class="font-bold text-[9.5px] uppercase">Lacak Servis Online</p>
                        <p class="text-[8.5px] break-all mt-0.5">{{ $trackingUrl }}</p>
                    </div>

                    <p class="text-center font-bold pt-1">Terima kasih atas kepercayaan Anda!</p>
                </div>
            @endif
        </div>

    @else
        <!-- ========================================================= -->
        <!-- STANDARD FORMAT (A4 / A5)                                 -->
        <!-- ========================================================= -->
        <div
            class="print-standard-doc {{ $format === 'a5' ? 'max-w-xl p-6 sm:p-7' : 'max-w-3xl p-8 sm:p-10' }} mx-auto bg-white rounded-xl border border-slate-200 shadow-md text-slate-900 leading-snug">
            <!-- Header Dokumen: Kop Toko -->
            <div class="pb-4 sec-py border-b border-slate-300 text-slate-900 text-center">
                <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-tight text-slate-900">
                    {{ $store->name ?? 'SEMAPON SERVICE' }}
                </h1>
                @if ($store->address)
                    <p class="text-xs text-slate-900 mt-1 leading-relaxed max-w-lg mx-auto">{{ $store->address }}</p>
                @endif
                @if ($store->phone)
                    <p class="text-xs text-slate-900 font-normal mt-0.5">{{ 'WhatsApp / Telp: ' . $store->phone }}</p>
                @endif
            </div>

            <!-- Section Tanda Terima Servis (Di Bawah Garis) -->
            <div class="my-3.5 sm:my-4 print:my-2.5 py-3 sm:py-3.5 px-4 sec-py bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-center">
                <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-900">
                    {{ $type === 'intake' ? 'TANDA TERIMA SERVIS' : 'NOTA PERBAIKAN & TAGIHAN' }}
                </h2>
                <p class="text-lg sm:text-xl font-bold text-slate-900 mt-1.5 leading-none">
                    #{{ $order->order_number }}
                </p>
                <p class="text-[11px] text-slate-900 mt-1 font-normal">
                    {{ $order->created_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                </p>
            </div>

            <!-- Grid Informasi Seimbang (Kiri: Pelanggan & Order | Kanan: Perangkat) -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 print:grid-cols-2 gap-6 print:gap-4 py-6 sec-py border-b border-slate-200 text-xs text-slate-900">
                <!-- Kolom Kiri -->
                <div class="space-y-3">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                            Informasi Pelanggan
                        </h3>
                        <p class="text-xs font-normal text-slate-900">Nama: <span>{{ $order->customer?->name ?? 'Umum / Walk-in' }}</span></p>
                        <p class="text-slate-900 mt-0.5 font-normal">No. HP:
                            <span>{{ $order->customer?->phone ?? '-' }}</span>
                        </p>
                        <p class="text-slate-900 mt-0.5 leading-relaxed font-normal">Alamat:
                            <span>{{ $order->customer?->address ?: '-' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="sm:border-l print:border-l sm:border-slate-200 print:border-slate-200 sm:pl-6 print:pl-4 space-y-3">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                            Informasi Perangkat
                        </h3>
                        <p class="text-xs font-normal text-slate-900">Perangkat: <span>{{ $deviceName }}</span></p>
                        <p class="text-slate-900 mt-0.5 font-normal">Warna: <span>{{ $order->device?->color ?? '-' }}</span></p>
                        <p class="text-slate-900 mt-0.5 leading-relaxed font-normal">Kelengkapan: <span>{{ $kelengkapan }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Keluhan & Kondisi Fisik -->
            <div class="py-5 sec-py space-y-3 print:space-y-2 text-xs text-slate-900">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                        Keluhan & Masalah Unit
                    </h3>
                    <div
                        class="p-3 print:p-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 font-normal leading-relaxed">
                        {{ $complaintText }}
                    </div>
                </div>

                @if (count($physicalItems) > 0)
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                            Kondisi Fisik Awal
                        </h3>
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <table class="w-full text-xs text-left text-slate-900">
                                <thead class="bg-slate-50 uppercase text-[10px] font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="tbl-td py-1.5 px-3 w-1/2">Pemeriksaan / Komponen</th>
                                        <th class="tbl-td py-1.5 px-3 w-1/2">Kondisi Fisik</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($physicalItems as $item)
                                        <tr>
                                            <td class="tbl-td py-1.5 px-3 font-normal text-slate-900 w-1/2">
                                                {{ $item['label'] ?: 'Fisik' }}
                                            </td>
                                            <td class="tbl-td py-1.5 px-3 font-normal text-slate-900 w-1/2">
                                                {{ $item['value'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            @if ($type === 'intake')
                <!-- Ketentuan & Lacak Status (Intake) -->
                <div class="py-5 sec-py border-b border-slate-200 space-y-3 text-xs text-slate-900">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                            Ketentuan Penyerahan Unit
                        </h3>
                        <ol class="list-decimal pl-4 space-y-1 text-[11px] text-slate-900 leading-relaxed font-normal">
                            <li>Tanda terima ini adalah bukti sah penyerahan & kepemilikan saat pengambilan unit.</li>
                            <li>Pengambilan unit wajib menunjukkan lembar tanda terima ini.</li>
                            <li>Estimasi biaya dan tindakan perbaikan akan dikonfirmasi sebelum pengerjaan.</li>
                        </ol>
                    </div>

                    <div class="pt-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-900 block">Lacak Status Servis
                            Online:</span>
                        <p class="text-[11px] break-all text-slate-900 mt-0.5 font-normal">{{ $trackingUrl }}</p>
                    </div>
                </div>

                <!-- Tanda Tangan Full-Width (Intake) -->
                <div class="pt-12 sm:pt-16 sig-container grid grid-cols-2 gap-10 text-center text-xs text-slate-900">
                    <div>
                        <div class="border-b border-slate-900 sig-line pb-20 sm:pb-24"></div>
                        <p class="mt-2.5 font-semibold text-slate-900">Petugas Penerima Toko</p>
                    </div>
                    <div>
                        <div class="border-b border-slate-900 sig-line pb-20 sm:pb-24"></div>
                        <p class="mt-2.5 font-semibold text-slate-900">Tanda Tangan Pelanggan</p>
                    </div>
                </div>
            @else
                <!-- Rincian Servis & Biaya (Nota) -->
                <div class="py-5 sec-py border-b border-slate-200 text-slate-900">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-900 block mb-2">Rincian Perbaikan &
                        Jasa:</span>
                    <table class="w-full text-xs text-left text-slate-900">
                        <thead class="bg-slate-50 text-slate-900 uppercase text-[10px] border-b border-slate-200 font-bold">
                            <tr>
                                <th class="tbl-td py-2 px-3 text-slate-900">Deskripsi Layanan / Suku Cadang</th>
                                <th class="tbl-td py-2 px-3 text-right text-slate-900">Biaya (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($order->items as $item)
                                <tr>
                                    <td class="tbl-td py-2 px-3 font-normal text-slate-900">{{ $item->name }}</td>
                                    <td class="tbl-td py-2 px-3 text-right font-normal text-slate-900">
                                        Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="tbl-td py-3 text-center text-slate-900 italic font-normal">Belum ada
                                        rincian jasa</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Ketentuan & Totals (Nota) -->
                <div
                    class="py-5 sec-py border-b border-slate-200 grid grid-cols-1 sm:grid-cols-2 print:grid-cols-2 gap-6 print:gap-4 text-xs text-slate-900">
                    <!-- Kiri: Ketentuan & Garansi -->
                    <div class="space-y-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                            Ketentuan Servis & Garansi
                        </h3>
                        @php
                            $storeWarrantyTerms = trim($order->store?->getSetting('warranty_terms') ?? '');
                            $warrantyTermsLines = $storeWarrantyTerms ? array_filter(array_map('trim', explode("\n", $storeWarrantyTerms))) : [];
                        @endphp
                        @if (!empty($warrantyTermsLines))
                            <div class="space-y-1 text-[11px] text-slate-900 leading-relaxed font-normal">
                                @foreach ($warrantyTermsLines as $termLine)
                                    <p>{{ $termLine }}</p>
                                @endforeach
                            </div>
                        @else
                            <ol class="list-decimal pl-4 space-y-1 text-[11px] text-slate-900 leading-relaxed font-normal">
                                <li>Nota ini adalah bukti sah kepemilikan saat pengambilan unit.</li>
                                @if ($warrantyDays > 0)
                                    <li class="font-normal text-slate-900">Garansi toko berlaku {{ $warrantyDays }} hari sejak tanggal
                                        pengambilan.</li>
                                @endif
                                <li>Garansi gugur jika segel rusak atau terjadi kerusakan fisik/cairan baru.</li>
                            </ol>
                        @endif

                        <div class="pt-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-900 block">Lacak Status Servis
                                Online:</span>
                            <p class="text-[11px] break-all text-slate-900 mt-0.5 font-normal">{{ $trackingUrl }}</p>
                        </div>
                    </div>

                    <!-- Kanan: Rincian Pembayaran -->
                    <div class="space-y-2 bg-slate-50 p-4 print:p-3 rounded-xl border border-slate-200 text-xs text-slate-900">
                        @if ($order->is_cost_undetermined)
                            <div class="p-2.5 rounded-lg bg-slate-100 border border-slate-300 text-slate-900 mb-1">
                                <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-900">Biaya Belum
                                    Ditentukan</span>
                                <p class="text-xs font-semibold mt-0.5 text-slate-900">Menunggu Diagnosa / Konfirmasi Teknisi</p>
                            </div>
                            @if ($order->paid_amount > 0)
                                <div class="flex justify-between text-slate-900">
                                    <span>Titipan DP:</span>
                                    <span
                                        class="font-semibold text-slate-900">Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-slate-900 pt-1 border-t border-slate-200 text-xs">
                                <span class="font-bold">Sisa Tagihan:</span>
                                <span class="font-medium text-slate-900 italic">Menyusul setelah konfirmasi</span>
                            </div>
                        @else
                            <div class="flex justify-between text-slate-900">
                                <span>Total Biaya:</span>
                                <span
                                    class="font-bold text-slate-900 text-sm">Rp{{ number_format($order->total_cost, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-slate-900">
                                <span>Uang Muka / Terbayar:</span>
                                <span
                                    class="font-semibold text-slate-900">Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-slate-900 pt-2 border-t border-slate-200">
                                <span class="font-bold">Sisa Tagihan:</span>
                                <span class="font-bold text-base text-slate-900">
                                    Rp{{ number_format($order->remaining_balance, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tanda Tangan Full-Width (Nota) -->
                <div class="pt-12 sm:pt-16 sig-container grid grid-cols-2 gap-10 text-center text-xs text-slate-900">
                    <div>
                        <div class="border-b border-slate-900 sig-line pb-20 sm:pb-24"></div>
                        <p class="mt-2.5 font-semibold text-slate-900">Petugas / Teknisi</p>
                    </div>
                    <div>
                        <div class="border-b border-slate-900 sig-line pb-20 sm:pb-24"></div>
                        <p class="mt-2.5 font-semibold text-slate-900">Tanda Tangan Pelanggan</p>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>