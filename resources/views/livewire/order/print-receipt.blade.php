<div class="min-h-screen bg-slate-100 py-6 px-4">
    <!-- Non-printable Actions Toolbar -->
    <div class="max-w-2xl mx-auto mb-6 p-4 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3 print:hidden">
        <div class="flex items-center space-x-3">
            <a
                href="{{ route('orders.show', $order->id) }}"
                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
            >
                ← Kembali ke Order
            </a>

            <!-- Format Selector -->
            <div class="inline-flex rounded-xl bg-slate-100 p-0.5 border border-slate-200">
                <button
                    type="button"
                    wire:click="$set('format', 'thermal')"
                    class="px-3 py-1 text-xs font-semibold rounded-xl transition-colors {{ $format === 'thermal' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Thermal (58/80mm)
                </button>
                <button
                    type="button"
                    wire:click="$set('format', 'standard')"
                    class="px-3 py-1 text-xs font-semibold rounded-xl transition-colors {{ $format === 'standard' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Standar (A4 / A5)
                </button>
            </div>
        </div>

        <button
            type="button"
            onclick="window.print()"
            class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-xs transition-colors cursor-pointer"
        >
            <i data-lucide="printer" class="w-4 h-4 mr-1.5"></i>
            Cetak Nota (Print)
        </button>
    </div>

    <!-- PRINT STYLES -->
    <style>
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print\:hidden {
                display: none !important;
            }
            .print\:shadow-none {
                box-shadow: none !important;
                border: none !important;
            }
            @page {
                margin: 0.5cm;
            }
        }
    </style>

    @if ($format === 'thermal')
        <!-- THERMAL RECEIPT FORMAT (58mm - 80mm) -->
        <div class="max-w-[80mm] mx-auto bg-white p-4 sm:p-5 text-slate-900 font-mono text-2xs border border-slate-200 shadow-md print:shadow-none print:border-none print:p-0 print:max-w-full">
            <!-- Header -->
            <div class="text-center pb-3 border-b border-dashed border-slate-400 space-y-1">
                <h1 class="text-xs font-bold uppercase tracking-wider">{{ $store->name ?? 'SEMAPON SERVICE' }}</h1>
                @if ($store->address)
                    <p class="leading-tight text-[10px] text-slate-600">{{ $store->address }}</p>
                @endif
                @if ($store->phone)
                    <p class="text-[10px] text-slate-600">Telp/WA: {{ $store->phone }}</p>
                @endif
            </div>

            <!-- Order Metadata -->
            <div class="py-2.5 border-b border-dashed border-slate-400 space-y-1 text-[11px]">
                <div class="flex justify-between">
                    <span>No. Nota:</span>
                    <span class="font-bold">#{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Tanggal:</span>
                    <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Pelanggan:</span>
                    <span class="font-bold">{{ $order->customer?->name ?? 'Umum' }}</span>
                </div>
                @if ($order->customer?->phone)
                    <div class="flex justify-between">
                        <span>No. HP:</span>
                        <span>{{ $order->customer->phone }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span>Teknisi:</span>
                    <span>{{ $order->technician?->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Est. Selesai:</span>
                    <span class="font-medium text-right">{{ $order->estimated_completion_at ? $order->estimated_completion_at->format('d/m/Y H:i') : 'Menyusul (Cek Diagnosa)' }}</span>
                </div>
            </div>

            <!-- Device Information -->
            <div class="py-2.5 border-b border-dashed border-slate-400 space-y-1 text-[11px]">
                <div class="flex justify-between">
                    <span>Perangkat:</span>
                    <span class="font-bold">{{ $order->device?->brand }} {{ $order->device?->model }}{{ $order->device?->variant ? ' (' . $order->device->variant . ')' : '' }}</span>
                </div>
                @if ($order->device?->imei_or_serial)
                    <div class="flex justify-between">
                        <span>IMEI/SN:</span>
                        <span>{{ $order->device->imei_or_serial }}</span>
                    </div>
                @endif
                @if ($order->device?->security_code)
                    <div class="flex justify-between">
                        <span>Pola/PIN:</span>
                        <span class="font-bold">{{ $order->device->security_code }}</span>
                    </div>
                @endif
                <div>
                    <span class="block text-slate-500">Keluhan:</span>
                    <span class="font-medium block leading-tight">{{ $order->complaint }}</span>
                </div>
            </div>

            <!-- Items & Billing -->
            <div class="py-2.5 border-b border-dashed border-slate-400 space-y-1.5">
                <span class="font-bold uppercase tracking-wider block text-[10px]">Rincian Servis:</span>
                @forelse ($order->items as $item)
                    <div class="flex justify-between items-start text-[11px]">
                        <span class="pr-2">{{ $item->name }}</span>
                        <span class="font-semibold shrink-0">Rp{{ number_format($item->price, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <div class="italic text-slate-400 text-[10px]">Belum ada rincian jasa</div>
                @endforelse
            </div>

            <!-- Financial Totals -->
            <div class="py-2.5 border-b border-dashed border-slate-400 space-y-1 text-[11px]">
                @if ($order->is_cost_undetermined)
                    <div class="flex justify-between font-bold text-slate-900">
                        <span>TOTAL BIAYA:</span>
                        <span class="italic text-[10px]">Menunggu Diagnosa</span>
                    </div>
                    @if ($order->paid_amount > 0)
                        <div class="flex justify-between text-slate-700">
                            <span>Titipan DP:</span>
                            <span>Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-slate-700 text-[10px] pt-1 border-t border-dotted border-slate-300">
                        <span>SISA TAGIHAN:</span>
                        <span class="italic">Menyusul konfirmasi</span>
                    </div>
                    <div class="text-[9px] text-center italic text-slate-500 pt-1">
                        *Biaya & pergantian part akan dikonfirmasi via WA sebelum dikerjakan
                    </div>
                @else
                    <div class="flex justify-between font-bold">
                        <span>TOTAL:</span>
                        <span>Rp{{ number_format($order->total_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-700">
                        <span>DP / Terbayar:</span>
                        <span>Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-xs pt-1 border-t border-dotted border-slate-300">
                        <span>SISA:</span>
                        <span>Rp{{ number_format($order->remaining_balance, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            <!-- Tracking & Footer Notes -->
            <div class="pt-3 text-center space-y-2 text-[9px] text-slate-600 leading-tight">
                @if ($order->warranty_days > 0)
                    <p class="font-bold text-slate-800">GARANSI TOKO: {{ $order->warranty_days }} HARI</p>
                @endif

                <div class="p-2 rounded bg-slate-50 border border-slate-200">
                    <p class="font-bold text-slate-800">Lacak Servis Online:</p>
                    <p class="text-[8px] break-all">{{ Route::has('tracking.show') ? route('tracking.show', $order->tracking_token) : url('/track/' . $order->tracking_token) }}</p>
                </div>

                <p>1. Nota asli wajib dibawa saat pengambilan unit.<br>
                2. Barang yang tidak diambil lebih dari 30 hari di luar tanggung jawab kami.<br>
                3. Garansi tidak berlaku apabila segel rusak / terkena cairan / human error.</p>

                <p class="pt-1 font-bold text-slate-800">Terima kasih atas kepercayaan Anda!</p>
            </div>
        </div>

    @else
        <!-- STANDARD INVOICE / WORK ORDER FORMAT (A4) -->
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl border border-slate-200 shadow-md print:shadow-none print:border-none print:p-0">
            <!-- Header -->
            <div class="flex justify-between items-start pb-6 border-b border-slate-200">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $store->name ?? 'SEMAPON SERVICE' }}</h1>
                    @if ($store->address)
                        <p class="text-xs text-slate-600 mt-1">{{ $store->address }}</p>
                    @endif
                    @if ($store->phone)
                        <p class="text-xs text-slate-600">WhatsApp/Telp: {{ $store->phone }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-primary-50 text-primary-700 font-bold text-xs rounded-lg uppercase tracking-wider mb-2">
                        Tanda Terima Servis
                    </span>
                    <h2 class="text-lg font-bold font-mono text-slate-900">#{{ $order->order_number }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tanggal: {{ $order->created_at->isoFormat('D MMMM Y') }}</p>
                </div>
            </div>

            <!-- Customer & Device Info Grid -->
            <div class="grid grid-cols-2 gap-6 py-6 border-b border-slate-200 text-xs">
                <div class="space-y-1.5">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Informasi Pelanggan:</span>
                    <p class="font-bold text-slate-900 text-sm">{{ $order->customer?->name ?? 'Umum / Walk-in' }}</p>
                    <p class="text-slate-600">Nomor HP: {{ $order->customer?->phone ?? '-' }}</p>
                    @if ($order->customer?->address)
                        <p class="text-slate-600">Alamat: {{ $order->customer->address }}</p>
                    @endif
                </div>

                <div class="space-y-1.5">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Informasi Perangkat:</span>
                    <p class="font-bold text-slate-900 text-sm">{{ $order->device?->brand }} {{ $order->device?->model }} ({{ ucfirst($order->device?->device_type ?? 'Smartphone') }})</p>
                    <p class="text-slate-600">Warna / IMEI: {{ $order->device?->color ?? '-' }} / {{ $order->device?->imei_or_serial ?? '-' }}</p>
                    <p class="text-slate-600">PIN / Pola: <span class="font-mono font-bold">{{ $order->device?->security_code ?? '-' }}</span></p>
                    <p class="text-slate-600">Kelengkapan: {{ $order->accessories_brought ?? 'Unit saja' }}</p>
                    <p class="text-slate-600">
                        Estimasi Selesai: 
                        <span class="font-semibold text-slate-800">
                            {{ $order->estimated_completion_at ? $order->estimated_completion_at->isoFormat('D MMMM Y, HH:mm') : 'Menyusul setelah pengecekan teknisi' }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Complaint -->
            <div class="py-4 border-b border-slate-200 text-xs">
                <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Keluhan & Masalah Unit:</span>
                <p class="p-3 bg-slate-50 rounded-xl text-slate-800 font-medium border border-slate-100">
                    {{ $order->complaint }}
                </p>
            </div>

            <!-- Items Table -->
            <div class="py-6 border-b border-slate-200">
                <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block mb-3">Rincian Perbaikan & Jasa:</span>
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-2xs border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Deskripsi Layanan / Suku Cadang</th>
                            <th class="py-2.5 px-3 text-right">Biaya (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($order->items as $item)
                            <tr>
                                <td class="py-3 px-3 font-medium text-slate-800">{{ $item->name }}</td>
                                <td class="py-3 px-3 text-right font-semibold text-slate-900">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-4 text-center text-slate-400 italic">Belum ada rincian jasa</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Totals & Signatures -->
            <div class="pt-6 grid grid-cols-2 gap-6 text-xs">
                <!-- Terms & Signatures -->
                <div class="space-y-4">
                    <div class="text-[10px] text-slate-500 space-y-1">
                        <p class="font-bold text-slate-700">Ketentuan Servis:</p>
                        <p>1. Nota ini adalah bukti sah kepemilikan saat pengambilan unit.</p>
                        <p>2. Garansi berlaku {{ $order->warranty_days }} hari sejak tanggal pengambilan.</p>
                        <p>3. Garansi gugur jika segel rusak atau terjadi kerusakan fisik/cairan baru.</p>
                    </div>

                    <div class="pt-8 grid grid-cols-2 gap-4 text-center text-2xs">
                        <div>
                            <div class="border-b border-slate-400 pb-12"></div>
                            <p class="mt-1 font-semibold text-slate-700">Tanda Tangan Pelanggan</p>
                        </div>
                        <div>
                            <div class="border-b border-slate-400 pb-12"></div>
                            <p class="mt-1 font-semibold text-slate-700">Petugas / Teknisi</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Balance -->
                <div class="space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100 self-start">
                    @if ($order->is_cost_undetermined)
                        <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 mb-2">
                            <span class="block text-2xs uppercase tracking-wider font-bold text-amber-700">Biaya Belum Ditentukan</span>
                            <p class="text-xs font-semibold mt-0.5">Menunggu Diagnosa / Konfirmasi Teknisi</p>
                            <p class="text-[10px] text-amber-800 mt-1">Biaya & ketersediaan part akan dikonfirmasi via WhatsApp sebelum perbaikan dilakukan.</p>
                        </div>
                        @if ($order->paid_amount > 0)
                            <div class="flex justify-between text-slate-600">
                                <span>Titipan DP:</span>
                                <span class="font-semibold text-emerald-700">Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-slate-700 pt-1 border-t border-slate-200 text-xs">
                            <span class="font-bold">Sisa Tagihan:</span>
                            <span class="font-medium text-slate-500 italic">Menyusul setelah konfirmasi</span>
                        </div>
                    @else
                        <div class="flex justify-between text-slate-600">
                            <span>Total Biaya:</span>
                            <span class="font-bold text-slate-900 text-sm">Rp{{ number_format($order->total_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Uang Muka / Pembayaran:</span>
                            <span class="font-semibold text-emerald-700">Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-900 pt-2 border-t border-slate-200">
                            <span class="font-bold">Sisa Tagihan:</span>
                            <span class="font-bold text-base {{ $order->remaining_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                Rp{{ number_format($order->remaining_balance, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
