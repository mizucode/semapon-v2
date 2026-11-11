<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Laporan & Rekap Bisnis</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Analisis performa servis, komisi teknisi, valuasi inventaris, dan laba rugi toko.</p>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="exportOrders"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition-colors shadow-2xs"
            >
                <i data-lucide="download" class="w-4 h-4 mr-1.5 text-slate-600"></i>
                Export Data CSV
            </button>
        </div>
    </div>

    <!-- Date Range Filter & Tabs -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <!-- Tabs -->
            <div class="flex items-center space-x-1 overflow-x-auto scrollbar-none">
                <button
                    type="button"
                    wire:click="$set('activeTab', 'overview')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors whitespace-nowrap {{ $activeTab === 'overview' ? 'bg-primary-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    Ringkasan Servis
                </button>
                <button
                    type="button"
                    wire:click="$set('activeTab', 'technicians')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors whitespace-nowrap {{ $activeTab === 'technicians' ? 'bg-primary-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    Kinerja Teknisi & Komisi
                </button>
                <button
                    type="button"
                    wire:click="$set('activeTab', 'inventory')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors whitespace-nowrap {{ $activeTab === 'inventory' ? 'bg-primary-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    Valuasi Inventaris
                </button>
                <button
                    type="button"
                    wire:click="$set('activeTab', 'financial')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors whitespace-nowrap {{ $activeTab === 'financial' ? 'bg-primary-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    Keuangan & Arus Kas
                </button>
            </div>

            <!-- Date Picker -->
            <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-500 text-2xs">Periode:</span>
                <input
                    type="date"
                    wire:model.live="startDate"
                    class="rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                />
                <span class="text-slate-400 text-2xs">s/d</span>
                <input
                    type="date"
                    wire:model.live="endDate"
                    class="rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                />
            </div>
        </div>
    </div>

    <!-- Tab 1: OVERVIEW SERVIS -->
    @if ($activeTab === 'overview')
        <div class="space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Total Order Masuk</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $serviceStats['total_orders'] }}</span>
                    <span class="text-3xs text-slate-400">Periode terpilih</span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Servis Sukses (Selesai)</span>
                    <span class="text-2xl font-bold text-emerald-600 mt-1 block">{{ $serviceStats['completed_orders'] }}</span>
                    <span class="text-3xs text-slate-400">Unit diambil pelanggan</span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Dalam Proses Pengerjaan</span>
                    <span class="text-2xl font-bold text-indigo-600 mt-1 block">{{ $serviceStats['in_progress_orders'] }}</span>
                    <span class="text-3xs text-slate-400">Antrean & pengerjaan aktif</span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Gagal / Dibatalkan</span>
                    <span class="text-2xl font-bold text-rose-600 mt-1 block">{{ $serviceStats['cancelled_orders'] }}</span>
                    <span class="text-3xs text-slate-400">Batal / tidak bisa perbaiki</span>
                </div>
            </div>

            <!-- Popular Services Breakdown -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100">
                    Layanan & Jasa Paling Sering Dilakukan
                </h2>

                <div class="space-y-3">
                    @forelse ($serviceStats['popular_services'] as $ps)
                        <div class="flex items-center justify-between text-xs p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <div>
                                <span class="font-bold text-slate-900 block">{{ $ps->name }}</span>
                                <span class="text-2xs text-slate-500">{{ $ps->count }} kali dikerjakan</span>
                            </div>
                            <span class="font-bold text-slate-800">Rp{{ number_format($ps->total_amount, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic py-2">Belum ada data pengerjaan pada periode ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- Tab 2: KINERJA TEKNISI -->
    @if ($activeTab === 'technicians')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">Produktivitas & Rekap Komisi Teknisi</h2>
                <p class="text-xs text-slate-500">Perhitungan pembagian komisi berdasarkan order yang telah selesai dikerjakan.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-2xs tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Nama Teknisi</th>
                            <th class="py-3 px-4">Spesialisasi</th>
                            <th class="py-3 px-4 text-center">Ditugaskan</th>
                            <th class="py-3 px-4 text-center">Selesai</th>
                            <th class="py-3 px-4 text-right">Omset Perbaikan</th>
                            <th class="py-3 px-4 text-right">Estimasi Komisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($techStats as $ts)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $ts['name'] }}</td>
                                <td class="py-3 px-4 capitalize text-slate-600">{{ $ts['specialization'] }}</td>
                                <td class="py-3 px-4 text-center">{{ $ts['total_assigned'] }}</td>
                                <td class="py-3 px-4 text-center font-bold text-emerald-600">{{ $ts['completed'] }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-900">
                                    Rp{{ number_format($ts['total_revenue'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-primary-700">
                                    Rp{{ number_format($ts['commission_earned'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                    Belum ada data teknisi terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Tab 3: VALUASI INVENTARIS -->
    @if ($activeTab === 'inventory')
        <div class="space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Total Nilai Aset Stok (Modal)</span>
                    <span class="text-xl sm:text-2xl font-bold text-primary-700 mt-1 block">
                        Rp{{ number_format($inventoryStats['total_valuation'], 0, ',', '.') }}
                    </span>
                    <span class="text-3xs text-slate-400">Valuasi harga beli barang</span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Total Macam Item</span>
                    <span class="text-2xl font-bold text-slate-800 mt-1 block">{{ $inventoryStats['total_items'] }} SKU</span>
                    <span class="text-3xs text-slate-400">Varian produk terdaftar</span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Total Kuantitas Fisik</span>
                    <span class="text-2xl font-bold text-slate-800 mt-1 block">{{ $inventoryStats['total_units'] }} Unit</span>
                    <span class="text-3xs text-slate-400">Seluruh unit di gudang</span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-rose-500 uppercase tracking-wider block">Peringatan Stok Menipis</span>
                    <span class="text-2xl font-bold text-rose-600 mt-1 block">{{ $inventoryStats['low_stock_count'] }} Item</span>
                    <span class="text-3xs text-slate-400">Di bawah batas minimum</span>
                </div>
            </div>

            <!-- Top Used Parts -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100">
                    Suku Cadang Paling Cepat Berputar (Fast Moving)
                </h2>

                <div class="space-y-3">
                    @forelse ($inventoryStats['top_parts'] as $tp)
                        <div class="flex items-center justify-between text-xs p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <div>
                                <span class="font-bold text-slate-900 block">{{ $tp->product?->name ?? 'Item Sparepart' }}</span>
                                <span class="text-2xs text-slate-500">{{ $tp->total_qty }} unit terpakai dalam servis</span>
                            </div>
                            <span class="font-bold text-slate-800">Rp{{ number_format($tp->total_revenue, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic py-2">Belum ada sparepart yang terpasang pada order.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- Tab 4: KEUANGAN & LABA RUGI -->
    @if ($activeTab === 'financial')
        <div class="space-y-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Pemasukan Kas</span>
                    <span class="text-xl font-bold text-emerald-600 mt-1 block">
                        Rp{{ number_format($financialSummary['total_income'], 0, ',', '.') }}
                    </span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Pengeluaran Toko</span>
                    <span class="text-xl font-bold text-rose-600 mt-1 block">
                        Rp{{ number_format($financialSummary['total_expense'], 0, ',', '.') }}
                    </span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Saldo Kas Bersih</span>
                    <span class="text-xl font-bold {{ $financialSummary['net_profit'] >= 0 ? 'text-primary-700' : 'text-rose-700' }} mt-1 block">
                        Rp{{ number_format($financialSummary['net_profit'], 0, ',', '.') }}
                    </span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Piutang Pelanggan</span>
                    <span class="text-xl font-bold text-amber-600 mt-1 block">
                        Rp{{ number_format($financialSummary['total_receivables'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    @endif
</div>

