<div class="space-y-6">
    <!-- Hearder -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Antrean & Riwayat Order Servis</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pantau progres reparasi smartphone, antrean teknisi, dan status pembayaran.</p>
        </div>
        <div>
            <a
                href="{{ route('orders.create') }}"
                class="inline-flex shrink-0 items-center justify-center h-9 px-4 py-2 gap-1.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow-xs transition-all active:translate-y-px cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Terima Servis Baru</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Sedang Dalam Proses / Antrean</span>
            <div class="mt-1">
                <span class="text-2xl font-bold text-amber-600">{{ $stats['total_active'] }} Unit</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Selesai (Siap Diambil)</span>
            <div class="mt-1">
                <span class="text-2xl font-bold text-emerald-600">{{ $stats['waiting_pickup'] }} Unit</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Sudah Diambil Hari Ini</span>
            <div class="mt-1">
                <span class="text-2xl font-bold text-slate-800">{{ $stats['completed_today'] }} Unit</span>
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div class="flex flex-col lg:flex-row gap-3 items-center justify-between">
            <!-- Search Bar -->
            <div class="relative w-full lg:max-w-md">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Cari nomor nota (#ORD), nama, HP, atau tipe HP..."
                    class="block w-full rounded-xl border border-slate-200 pl-10 pr-9 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-all"
                >
                @if($search)
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors"
                    >
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                @endif
            </div>

            <!-- Custom Dropdowns -->
            <div class="flex items-center gap-2.5 w-full lg:w-auto">
                <!-- 1. Custom Status Dropdown -->
                <div class="relative flex-1 lg:flex-initial" x-data="{ open: false }">
                    @php
                        $statusLabels = [
                            '' => 'Semua Status',
                            'active_queue' => 'Antrean & Pengerjaan',
                            'received' => 'Diterima',
                            'diagnosing' => 'Diagnosa',
                            'waiting_approval' => 'Tunggu Konfirmasi',
                            'in_progress' => 'Dalam Pengerjaan',
                            'waiting_parts' => 'Menunggu Sparepart',
                            'testing' => 'Pengujian (QC)',
                            'ready_for_pickup' => 'Siap Diambil',
                            'completed' => 'Selesai',
                            'unrepairable' => 'Tidak Bisa Diperbaiki',
                            'cancelled' => 'Dibatalkan',
                        ];
                        $currentStatusLabel = $statusLabels[$statusFilter] ?? 'Semua Status';
                    @endphp

                    <button
                        type="button"
                        @click="open = !open"
                        @click.outside="open = false"
                        class="w-full lg:w-52 flex items-center justify-between px-4 py-3 rounded-xl border {{ $statusFilter !== '' ? 'border-primary-500 bg-primary-50/50 text-primary-700 font-semibold' : 'border-slate-200 bg-white text-slate-700 font-medium hover:border-slate-300' }} text-sm shadow-2xs transition-all"
                    >
                        <div class="flex items-center space-x-2 truncate">
                            <i data-lucide="activity" class="w-3.5 h-3.5 {{ $statusFilter !== '' ? 'text-primary-600' : 'text-slate-400' }} shrink-0"></i>
                            <span class="truncate">{{ $currentStatusLabel }}</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1.5 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute left-0 lg:right-0 lg:left-auto mt-1.5 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-40"
                    >
                        <div class="px-3.5 py-1.5 text-3xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                            Pilih Status
                        </div>
                        <div class="py-1 max-h-64 overflow-y-auto no-scrollbar">
                            @foreach($statusLabels as $value => $label)
                                <button
                                    type="button"
                                    wire:click="$set('statusFilter', '{{ $value }}')"
                                    @click="open = false"
                                    class="w-full flex items-center justify-between px-3.5 py-2 text-xs text-left transition-colors {{ $statusFilter === (string)$value ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}"
                                >
                                    <span>{{ $label }}</span>
                                    @if($statusFilter === (string)$value)
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-primary-600"></i>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- 2. Custom Technician Dropdown -->
                <div class="relative flex-1 lg:flex-initial" x-data="{ open: false }">
                    @php
                        $selectedTech = $technicians->first(fn($t) => (string)$t->user_id === (string)$technicianFilter || (string)$t->user?->id === (string)$technicianFilter);
                        $currentTechLabel = $selectedTech ? ($selectedTech->user->name ?? 'Teknisi') : 'Semua Teknisi';
                    @endphp

                    <button
                        type="button"
                        @click="open = !open"
                        @click.outside="open = false"
                        class="w-full lg:w-52 flex items-center justify-between px-4 py-3 rounded-xl border {{ $technicianFilter !== '' ? 'border-primary-500 bg-primary-50/50 text-primary-700 font-semibold' : 'border-slate-200 bg-white text-slate-700 font-medium hover:border-slate-300' }} text-sm shadow-2xs transition-all"
                    >
                        <div class="flex items-center space-x-2 truncate">
                            <i data-lucide="user" class="w-3.5 h-3.5 {{ $technicianFilter !== '' ? 'text-primary-600' : 'text-slate-400' }} shrink-0"></i>
                            <span class="truncate">{{ $currentTechLabel }}</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1.5 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-1.5 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-40"
                    >
                        <div class="px-3.5 py-1.5 text-3xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                            Pilih Teknisi
                        </div>
                        <div class="py-1 max-h-64 overflow-y-auto no-scrollbar">
                            <button
                                type="button"
                                wire:click="$set('technicianFilter', '')"
                                @click="open = false"
                                class="w-full flex items-center justify-between px-3.5 py-2 text-xs text-left transition-colors {{ $technicianFilter === '' ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}"
                            >
                                <span>Semua Teknisi</span>
                                @if($technicianFilter === '')
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-primary-600"></i>
                                @endif
                            </button>

                            @foreach($technicians as $tech)
                                <button
                                    type="button"
                                    wire:click="$set('technicianFilter', '{{ $tech->user->id }}')"
                                    @click="open = false"
                                    class="w-full flex items-center justify-between px-3.5 py-2 text-xs text-left transition-colors {{ (string)$technicianFilter === (string)$tech->user->id ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}"
                                >
                                    <span class="truncate">{{ $tech->user->name }} (Teknisi)</span>
                                    @if((string)$technicianFilter === (string)$tech->user->id)
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-primary-600"></i>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Content (Table on Desktop, Cards on Mobile) -->
    @if($orders->isEmpty())
        <x-empty-state
            icon="clipboard-list"
            title="Tidak ada order ditemukan"
            description="Tidak ada order servis HP yang sesuai dengan filter atau kata kunci Anda."
            actionLabel="Terima Servis Baru"
            actionUrl="{{ route('orders.create') }}"
        />
    @else
        <!-- Desktop Table -->
        <div class="hidden sm:block bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold text-3xs">
                    <tr>
                        <th class="py-3 px-5">Nota & Tanggal</th>
                        <th class="py-3 px-5">Pelanggan</th>
                        <th class="py-3 px-5">Perangkat HP</th>
                        <th class="py-3 px-5">Keluhan</th>
                        <th class="py-3 px-5">Teknisi</th>
                        <th class="py-3 px-5">Status</th>
                        <th class="py-3 px-5 text-right">Biaya / DP</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach($orders as $order)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-5 whitespace-nowrap">
                                <a href="{{ route('orders.show', $order->id) }}" class="font-bold text-slate-900 hover:text-primary-600 block">
                                    #{{ $order->order_number }}
                                </a>
                                <span class="text-3xs text-slate-400 block font-normal">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="py-3 px-5 whitespace-nowrap">
                                <span class="font-semibold text-slate-800 block">{{ $order->customer->name }}</span>
                                <span class="text-3xs text-slate-400 font-mono">{{ $order->customer->phone }}</span>
                            </td>
                            <td class="py-3 px-5 whitespace-nowrap">
                                <span class="font-semibold text-slate-900 block">{{ $order->device->device_name }}</span>
                                <span class="text-3xs text-slate-400">{{ $order->device->color ?: 'Tanpa warna' }}</span>
                            </td>
                            <td class="py-3 px-5 max-w-xs truncate text-slate-600">
                                {{ $order->complaints }}
                            </td>
                            <td class="py-3 px-5 whitespace-nowrap">
                                @if($order->technician)
                                    <span class="inline-flex items-center text-xs text-slate-800">
                                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-bold text-3xs flex items-center justify-center mr-1.5">
                                            {{ strtoupper(substr($order->technician->name, 0, 1)) }}
                                        </span>
                                        {{ $order->technician->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-2xs">Belum ditugaskan</span>
                                @endif
                            </td>
                            <td class="py-3 px-5 whitespace-nowrap">
                                <x-badge :status="$order->status" />
                            </td>
                            <td class="py-3 px-5 text-right whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">
                                    Rp{{ number_format($order->total_cost, 0, ',', '.') }}
                                </span>
                                @if($order->down_payment > 0)
                                    <span class="text-3xs text-emerald-600 block">DP: Rp{{ number_format($order->down_payment, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end">
                                    <a
                                        href="{{ route('orders.show', $order->id) }}"
                                        class="inline-flex shrink-0 items-center justify-center h-8 px-3 gap-1 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-all active:translate-y-px shadow-xs cursor-pointer"
                                        title="Buka Lembar Kerja & Update Status"
                                    >
                                        <span>Kelola</span>
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <!-- Mobile Cards List -->
        <div class="sm:hidden space-y-3">
            @foreach($orders as $order)
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('orders.show', $order->id) }}" class="font-bold text-sm text-slate-900 hover:text-primary-600">
                                    #{{ $order->order_number }}
                                </a>
                                <span class="text-3xs text-slate-400">{{ $order->created_at->format('d M') }}</span>
                            </div>
                            <h4 class="font-semibold text-xs text-slate-800 mt-1">{{ $order->device->device_name }}</h4>
                        </div>
                        <x-badge :status="$order->status" size="sm" />
                    </div>

                    <p class="text-2xs text-slate-600 line-clamp-2 bg-slate-50 p-2 rounded-xl">
                        <strong class="text-slate-700">Keluhan:</strong> {{ $order->complaints }}
                    </p>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-2xs text-slate-500 block">Pelanggan: {{ $order->customer->name }}</span>
                            <span class="font-bold text-sm text-slate-900">Rp{{ number_format($order->total_cost, 0, ',', '.') }}</span>
                        </div>
                        <a
                            href="{{ route('orders.show', $order->id) }}"
                            class="inline-flex shrink-0 items-center justify-center h-8 px-3 gap-1 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-all active:translate-y-px cursor-pointer shadow-xs"
                        >
                            <span>Buka Order</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>

