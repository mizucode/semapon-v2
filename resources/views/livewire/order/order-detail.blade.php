<div class="space-y-6">
    <!-- Top Bar / Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Order #{{ $order->order_number }}
                </h1>

                <!-- Status Badge -->
                <x-badge :status="$order->status" />

                <!-- Payment Status Badge -->
                @if ($order->is_cost_undetermined && $order->items->isEmpty())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-2xs font-semibold bg-amber-500 text-white shadow-xs">
                        MENUNGGU DIAGNOSA
                    </span>
                @elseif ($order->payment_status === 'paid')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-2xs font-semibold bg-emerald-500 text-white shadow-xs">
                        LUNAS
                    </span>
                @elseif ($order->payment_status === 'partially_paid')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-2xs font-semibold bg-amber-500 text-white shadow-xs">
                        DP TERCATAT
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-2xs font-semibold bg-rose-500 text-white shadow-xs">
                        BELUM BAYAR
                    </span>
                @endif
            </div>

            <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-2">
                <span>Diterima: {{ $order->created_at->isoFormat('D MMMM Y, HH:mm') }} WIB</span>
                <span>•</span>
                <span>Estimasi Selesai: {{ $order->estimated_completion_at ? $order->estimated_completion_at->isoFormat('D MMM Y, HH:mm') : 'Menyusul (Menunggu Diagnosa)' }}</span>
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Public Tracking Link -->
            @if (Route::has('tracking.show'))
                <a
                    href="{{ route('tracking.show', $order->tracking_token) }}"
                    target="_blank"
                    class="inline-flex items-center px-4 py-3 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    title="Buka Halaman Tracking Pelanggan"
                >
                    <i data-lucide="external-link" class="w-3.5 h-3.5 mr-1.5"></i>
                    Tracking Pelanggan
                </a>
            @endif

            <!-- WhatsApp Update Link -->
            @if ($order->customer && $order->customer->whatsapp_number)
                @php
                    $trackingUrl = route('tracking.show', $order->tracking_token);
                    $waText = "Halo kak *{$order->customer->name}*, berikut update perbaikan gadget Anda *({$order->device?->device_type} {$order->device?->brand} {$order->device?->model})* dengan nomor nota *#{$order->order_number}*.\nStatus saat ini: *{$statusLabels[$order->status]}*.\n\nPantau live progres perbaikan di link resmi kami:\n{$trackingUrl}\n\nTerima kasih telah mempercayakan servis kepada kami.";
                    $waLink = "https://wa.me/{$order->customer->whatsapp_number}?text=" . rawurlencode($waText);
                @endphp
                <a
                    href="{{ $waLink }}"
                    target="_blank"
                    class="inline-flex items-center px-4 py-3 text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition-colors"
                >
                    <i data-lucide="message-circle" class="w-3.5 h-3.5 mr-1.5"></i>
                    Kirim WA
                </a>
            @endif

            <!-- Print Receipt Link -->
            @if (Route::has('orders.print'))
                <a
                    href="{{ route('orders.print', $order->id) }}"
                    target="_blank"
                    class="inline-flex items-center px-4 py-3 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition-colors"
                >
                    <i data-lucide="printer" class="w-3.5 h-3.5 mr-1.5"></i>
                    Cetak Nota
                </a>
            @endif
        </div>
    </div>

    <!-- Status Advance Workflow Bar -->
    <div class="bg-primary-50/70 border border-primary-100 p-4 rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center space-x-2 text-primary-900">
            <i data-lucide="arrow-right-circle" class="w-5 h-5 text-primary-600 shrink-0"></i>
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wider text-primary-800">Alur Pengerjaan Servis</h2>
                <p class="text-xs text-primary-700">Perbarui status tahapan pengerjaan untuk memberi notifikasi ke teknisi & pelanggan.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($order->status === 'received')
                <button
                    type="button"
                    wire:click="advanceStatus('diagnosing', 'Mulai proses diagnosa unit oleh teknisi.')"
                    class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-colors"
                >
                    Mulai Diagnosa
                </button>
            @elseif ($order->status === 'diagnosing')
                <button
                    type="button"
                    wire:click="advanceStatus('waiting_approval', 'Diagnosa selesai, meminta konfirmasi biaya kepada pelanggan.')"
                    class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-colors"
                >
                    Tunggu Konfirmasi Biaya
                </button>
                <button
                    type="button"
                    wire:click="advanceStatus('in_progress', 'Biaya disetujui, langsung mulai pengerjaan.')"
                    class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-colors"
                >
                    Langsung Kerjakan
                </button>
            @elseif ($order->status === 'waiting_approval')
                <button
                    type="button"
                    wire:click="advanceStatus('in_progress', 'Pelanggan telah menyetujui estimasi biaya.')"
                    class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-colors"
                >
                    Pelanggan Setuju (Kerjakan)
                </button>
                <button
                    type="button"
                    wire:click="advanceStatus('cancelled', 'Pelanggan membatalkan perbaikan karena biaya / sparepart.')"
                    class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-colors"
                >
                    Batal Servis
                </button>
            @elseif ($order->status === 'in_progress')
                <button
                    type="button"
                    wire:click="advanceStatus('waiting_parts', 'Menunggu ketersediaan suku cadang.')"
                    class="px-3.5 py-1.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-colors"
                >
                    Menunggu Sparepart
                </button>
                <button
                    type="button"
                    wire:click="advanceStatus('testing', 'Perbaikan selesai, masuk ke tahap QC & pengujian fungsi.')"
                    class="px-3.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-colors"
                >
                    Selesai & Uji Fungsi (QC)
                </button>
            @elseif ($order->status === 'waiting_parts')
                <button
                    type="button"
                    wire:click="advanceStatus('in_progress', 'Sparepart telah tersedia, melanjutkan perbaikan.')"
                    class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-colors"
                >
                    Sparepart Ada (Lanjut Servis)
                </button>
            @elseif ($order->status === 'testing')
                <button
                    type="button"
                    wire:click="advanceStatus('ready_for_pickup', 'Semua uji fungsi lolos. Gadget siap diserahkan ke pelanggan.')"
                    class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-colors"
                >
                    QC Lolos (Siap Diambil)
                </button>
            @elseif ($order->status === 'ready_for_pickup')
                <button
                    type="button"
                    wire:click="advanceStatus('completed', 'Unit telah diserahkan dan diterima oleh pelanggan.')"
                    class="px-3.5 py-1.5 bg-green-700 hover:bg-green-800 text-white text-xs font-semibold rounded-xl shadow-2xs transition-colors"
                >
                    Unit Sudah Diambil Pelanggan
                </button>
            @endif

            @if (!in_array($order->status, ['completed', 'cancelled', 'unrepairable']))
                <button
                    type="button"
                    wire:click="advanceStatus('unrepairable', 'Gadget tidak dapat diperbaiki (kerusakan parah/IC dead).')"
                    wire:confirm="Yakin ingin menandai unit ini sebagai gagal servis / tidak bisa diperbaiki?"
                    class="px-3 py-1.5 bg-white border border-rose-200 hover:bg-rose-50 text-rose-700 text-xs font-semibold rounded-xl transition-colors"
                >
                    Tidak Dapat Diperbaiki
                </button>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 1 Col: Customer & Device Overview -->
        <div class="space-y-6">
            <!-- Customer Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Data Pelanggan</span>
                    @if ($order->customer)
                        <a href="{{ route('customers.show', $order->customer->id) }}" class="text-2xs font-semibold text-primary-600 hover:underline">
                            Lihat Profil
                        </a>
                    @endif
                </div>

                @if ($order->customer)
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ $order->customer->name }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5 flex items-center">
                            <i data-lucide="phone" class="w-3.5 h-3.5 mr-1 text-slate-400"></i>
                            {{ $order->customer->phone }}
                        </p>
                        @if ($order->customer->address)
                            <p class="text-xs text-slate-500 mt-1 flex items-start">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 mr-1 text-slate-400 shrink-0 mt-0.5"></i>
                                {{ $order->customer->address }}
                            </p>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-slate-500 italic">Pelanggan Umum (Tanpa Akun)</p>
                @endif
            </div>

            <!-- Device & Intake Details -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Detail Perangkat & Kondisi Masuk</span>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-2xs uppercase">Tipe & Seri Gadget</span>
                        <span class="font-bold text-slate-800 text-sm">
                            {{ $order->device?->brand ?? '-' }} {{ $order->device?->model ?? '-' }}
                        </span>
                        <span class="text-slate-500 block text-2xs">
                            Kategori: {{ ucfirst($order->device?->device_type ?? 'Smartphone') }} • Warna: {{ $order->device?->color ?? '-' }}
                            @if ($order->device?->variant)
                                • Varian: {{ $order->device->variant }}
                            @endif
                        </span>
                        @if ($order->device?->specs)
                            <span class="text-slate-600 block text-2xs mt-0.5">
                                Spek: {{ $order->device->specs }}
                            </span>
                        @endif
                    </div>

                    @if ($order->device?->imei_or_serial)
                        <div>
                            <span class="text-slate-400 block text-2xs uppercase">IMEI / Serial Number</span>
                            <span class="font-mono text-slate-700 bg-slate-50 px-2 py-0.5 rounded border border-slate-100 inline-block mt-0.5">
                                {{ $order->device->imei_or_serial }}
                            </span>
                        </div>
                    @endif

                    @if ($order->device?->security_code)
                        <div>
                            <span class="text-slate-400 block text-2xs uppercase">PIN / Pola Layar</span>
                            <span class="font-mono font-bold text-slate-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 inline-block mt-0.5">
                                {{ $order->device->security_code }}
                            </span>
                        </div>
                    @endif

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 block text-2xs uppercase">Keluhan Awal Pelanggan</span>
                        <div class="mt-1 p-2.5 rounded-xl bg-slate-50 text-slate-800 text-xs font-medium border border-slate-100">
                            {{ $order->complaint }}
                        </div>
                    </div>

                    @if ($order->accessories_brought)
                        <div>
                            <span class="text-slate-400 block text-2xs uppercase">Kelengkapan Ditinggal</span>
                            <p class="text-slate-700 mt-0.5">{{ $order->accessories_brought }}</p>
                        </div>
                    @endif

                    <!-- Physical Checklist Summary -->
                    @if (is_array($order->physical_condition_checklist) && count($order->physical_condition_checklist) > 0)
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-slate-400 block text-2xs uppercase mb-1.5">Kondisi Fisik Saat Diterima</span>
                            <div class="grid grid-cols-2 gap-1.5">
                                @foreach ($order->physical_condition_checklist as $k => $val)
                                    <div class="flex items-center space-x-1.5 text-2xs">
                                        @if ($val)
                                            <span class="text-emerald-600 font-bold">✓</span>
                                        @else
                                            <span class="text-rose-500 font-bold">✕</span>
                                        @endif
                                        <span class="text-slate-600 capitalize">{{ str_replace('_', ' ', $k) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Foto Kondisi Fisik Unit Saat Diterima -->
                    @if (is_array($order->photos) && count($order->photos) > 0)
                        <div class="pt-3 border-t border-slate-100">
                            <span class="text-slate-400 block text-2xs uppercase mb-2 font-semibold">Foto Fisik Unit ({{ count($order->photos) }})</span>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($order->photos as $photo)
                                    <a href="{{ asset('storage/'.$photo) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-200 aspect-square group bg-slate-50 relative">
                                        <img src="{{ asset('storage/'.$photo) }}" alt="Foto Fisik Unit" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

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
        </div>

        <!-- Right 2 Cols: Services & Cost Breakdown, Diagnosis, Timeline -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Billing & Items Card -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-2xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Rincian Jasa Servis & Sparepart</h2>
                        <p class="text-xs text-slate-500">Daftar biaya perbaikan yang ditagihkan kepada pelanggan.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="openAddItemModal"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-xl transition-colors"
                        >
                            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
                            Tambah Biaya / Jasa
                        </button>
                        <button
                            type="button"
                            wire:click="$set('showPaymentModal', true)"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-2xs transition-colors"
                        >
                            <i data-lucide="credit-card" class="w-3.5 h-3.5 mr-1"></i>
                            Catat Pembayaran
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
                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-xl transition-colors"
                    >
                        <i data-lucide="clipboard-check" class="w-3.5 h-3.5 mr-1"></i>
                        Catat Diagnosa / QC
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
                    <p class="text-xs text-slate-500">Jejak perubahan status, pembayaran, dan tindakan pada order ini.</p>
                </div>

                <div class="relative pl-6 space-y-4 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse ($order->histories as $history)
                        <div class="relative text-xs">
                            <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-primary-600 border-2 border-white ring-2 ring-primary-100"></div>
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-800">
                                    {{ $history->user?->name ?? 'Sistem' }}
                                    @if ($history->from_status !== $history->to_status)
                                        <span class="text-2xs font-normal text-slate-500">
                                            ({{ $statusLabels[$history->from_status] ?? ucfirst(str_replace('_', ' ', $history->from_status)) }} → {{ $statusLabels[$history->to_status] ?? ucfirst(str_replace('_', ' ', $history->to_status)) }})
                                        </span>
                                    @endif
                                </span>
                                <span class="text-2xs text-slate-400">{{ $history->created_at->diffForHumans() }}</span>
                            </div>
                            @if ($history->notes)
                                <p class="text-slate-600 text-2xs mt-0.5">{{ $history->notes }}</p>
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
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="addItem"
                        class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors"
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
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="recordPayment"
                        class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors"
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
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="saveDiagnosis"
                        class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors"
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
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="assignTechnician"
                        class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors"
                    >
                        Simpan Penugasan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

