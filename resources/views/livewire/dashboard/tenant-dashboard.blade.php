<div class="space-y-5">
    <!-- Welcome Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl shadow-xs border border-slate-100">
        <div>
            <p class="text-xs font-medium text-slate-500">Selamat Pagi,</p>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-0.5">{{ auth()->user()->name }}</h1>
            <p class="text-sm text-slate-500 mt-1">Ringkasan operasional dan servis toko <span class="font-semibold text-primary-600">{{ $store?->name }}</span> hari ini.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <i data-lucide="plus-circle" class="w-4 h-4 mr-1.5"></i>
                Order Servis Baru
            </a>
        </div>
    </div>

    <!-- Alert Banners (if any) -->
    @if($overdueOrdersCount > 0 || $lowStockCount > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
            @if($overdueOrdersCount > 0)
                <div class="flex items-center justify-between gap-3 p-3.5 sm:p-4 bg-rose-600 text-white rounded-2xl shadow-xs">
                    <div class="flex items-center gap-2.5 sm:gap-3 flex-1 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-white"></i>
                        </div>
                        <p class="text-xs font-medium leading-relaxed">
                            Terdapat <strong>{{ $overdueOrdersCount }}</strong> order servis yang melewati estimasi selesai!
                        </p>
                    </div>
                    <a href="{{ route('orders.index') }}" class="shrink-0 inline-flex items-center justify-center text-xs font-bold px-3 py-1.5 bg-white text-rose-600 hover:bg-rose-50 rounded-xl shadow-xs transition-colors whitespace-nowrap">
                        Lihat
                    </a>
                </div>
            @endif

            @if($lowStockCount > 0)
                <div class="flex items-center justify-between gap-3 p-3.5 sm:p-4 bg-amber-500 text-white rounded-2xl shadow-xs">
                    <div class="flex items-center gap-2.5 sm:gap-3 flex-1 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                            <i data-lucide="package-x" class="w-4 h-4 text-white"></i>
                        </div>
                        <p class="text-xs font-medium leading-relaxed">
                            Terdapat <strong>{{ $lowStockCount }}</strong> sparepart/produk dengan stok di bawah batas minimal!
                        </p>
                    </div>
                    <a href="{{ route('inventory.index') }}" class="shrink-0 inline-flex items-center justify-center text-xs font-bold px-3 py-1.5 bg-white text-amber-600 hover:bg-amber-50 rounded-xl shadow-xs transition-colors whitespace-nowrap">
                        Restock
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Active Services -->
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-100 flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Sedang Dikerjakan</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $activeOrdersCount }}</h3>
                <span class="text-2xs text-slate-400 mt-1 block">unit dalam antrean/servis</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                <i data-lucide="wrench" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Ready For Pickup -->
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-100 flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Siap Diambil</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $readyOrdersCount }}</h3>
                <span class="text-2xs text-blue-600 font-medium mt-1 block">menunggu konfirmasi pelanggan</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                <i data-lucide="package-check" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Completed Today -->
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-100 flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Selesai Hari Ini</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $completedTodayCount }}</h3>
                <span class="text-2xs text-emerald-600 font-medium mt-1 block">unit tuntas diserahkan</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Revenue Today -->
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-100 flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Pendapatan Hari Ini</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">Rp {{ number_format($revenueToday, 0, ',', '.') }}</h3>
                <span class="text-2xs text-slate-400 mt-1 block">pembayaran kas masuk</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Content Grid: Recent Orders & Inventory Alert -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-xs border border-slate-100 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Order Servis Terbaru</h2>
                    <p class="text-xs text-slate-500">Unit servis yang baru masuk atau diperbarui.</p>
                </div>
                <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 flex items-center gap-1">
                    Semua Order <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-3xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-2.5 px-3">No. Order</th>
                            <th class="py-2.5 px-3">Pelanggan</th>
                            <th class="py-2.5 px-3">Perangkat</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 px-3 font-semibold text-slate-900">
                                    <a href="{{ route('orders.show', $order->id) }}" class="hover:text-primary-600">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-3 text-slate-700">
                                    {{ $order->customer?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $order->device?->brand }} {{ $order->device?->model }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-semibold
                                        @if($order->status === 'completed') bg-emerald-50 text-emerald-700
                                        @elseif($order->status === 'ready_for_pickup') bg-blue-50 text-blue-700
                                        @elseif($order->status === 'in_progress') bg-indigo-50 text-indigo-700
                                        @elseif($order->status === 'waiting_parts') bg-amber-50 text-amber-700
                                        @elseif($order->status === 'cancelled') bg-rose-50 text-rose-700
                                        @else bg-slate-100 text-slate-700
                                        @endif
                                    ">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center p-1.5 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-xs text-slate-400">
                                    Belum ada order servis yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inventory Alerts / Quick Info (1 col) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-100 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Peringatan Stok</h2>
                    <p class="text-xs text-slate-500">Barang perlu segera di-restock.</p>
                </div>
                <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                    Lihat Stok
                </a>
            </div>

            <div class="space-y-3">
                @forelse($lowStockProducts as $prod)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="min-w-0 pr-2">
                            <p class="text-xs font-semibold text-slate-900 truncate">{{ $prod->name }}</p>
                            <p class="text-2xs text-slate-400">SKU: {{ $prod->sku ?? '-' }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-xs font-bold text-rose-600">{{ $prod->stock }}</span>
                            <span class="text-2xs text-slate-400">/ min {{ $prod->min_stock }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">
                        Semua stok sparepart dan produk dalam kondisi aman.
                    </div>
                @endforelse
            </div>

            <!-- Quick Navigation Card -->
            <div class="pt-4 border-t border-slate-100 space-y-2">
                <p class="text-xs font-bold text-slate-700">Akses Cepat</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('purchases.index') }}" class="p-2.5 rounded-xl border border-slate-200 hover:border-primary-500 hover:text-primary-600 text-slate-600 font-medium flex items-center gap-2 transition-colors">
                        <i data-lucide="truck" class="w-4 h-4 text-slate-400"></i>
                        <span>Pembelian</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="p-2.5 rounded-xl border border-slate-200 hover:border-primary-500 hover:text-primary-600 text-slate-600 font-medium flex items-center gap-2 transition-colors">
                        <i data-lucide="bar-chart-2" class="w-4 h-4 text-slate-400"></i>
                        <span>Laporan</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
