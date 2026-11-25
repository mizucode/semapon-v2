<div class="space-y-6">
    <!-- Hearder -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Antrean & Riwayat Order Servis</h1>
        </div>
        @if(!$isTechnician)
            <div class="hidden lg:block">
                <x-button
                    href="{{ route('orders.create') }}"
                    icon="plus"
                >
                    Tambah Servis
                </x-button>
            </div>
        @endif
    </div>

    <!-- Floating Action Button (Mobile) -->
    @if(!$isTechnician)
        <a
            href="{{ route('orders.create') }}"
            class="lg:hidden fixed bottom-14 right-4 z-40 inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm rounded-full shadow-lg shadow-primary-600/30 active:scale-95 transition-all"
            aria-label="Tambah Servis"
        >
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah</span>
        </a>
    @endif

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <x-stat-card label="Antrean & Proses" :value="$stats['total_active']" />
        <x-stat-card label="Siap Diambil" :value="$stats['waiting_pickup']" />
        <x-stat-card label="Selesai Hari Ini" :value="$stats['completed_today']" />
        <x-stat-card label="Servis Bulan Ini" :value="$stats['total_month']" />
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
                    wire:key="orders-search-input"
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    autocomplete="off"
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
                            'received' => 'Menunggu Diagnosa',
                            'diagnosing' => 'Sedang Diagnosa',
                            'waiting_approval' => 'Tunggu Konfirmasi',
                            'in_progress' => 'Dalam Pengerjaan',
                            'waiting_parts' => 'Menunggu Sparepart',
                            'testing' => 'Pengujian (QC)',
                            'ready_for_pickup' => 'Siap Diambil',
                            'completed' => 'Selesai',
                            'picked_up' => 'Sudah Diambil',
                            'unrepairable' => 'Tidak Bisa Diperbaiki',
                            'cancelled' => 'Dibatalkan',
                            'pending' => 'Menunggu',
                        ];
                        $currentStatusLabel = $statusLabels[$statusFilter] ?? 'Semua Status';
                    @endphp

                    <button
                        type="button"
                        @click="open = !open"
                        @click.outside="open = false"
                        class="w-full lg:w-56 flex items-center justify-between px-4 py-3 rounded-xl border {{ $statusFilter !== '' ? 'border-primary-500 bg-primary-50/50 text-primary-700 font-semibold' : 'border-slate-200 bg-white text-slate-700 font-medium hover:border-slate-300' }} text-sm shadow-2xs transition-all cursor-pointer"
                    >
                        <div class="flex items-center space-x-2 truncate">
                            @if($statusFilter)
                                <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ 
                                    $statusFilter === 'received' ? 'bg-blue-600' :
                                    ($statusFilter === 'diagnosing' ? 'bg-purple-600' :
                                    ($statusFilter === 'waiting_approval' ? 'bg-amber-500' :
                                    ($statusFilter === 'in_progress' ? 'bg-indigo-600' :
                                    ($statusFilter === 'waiting_parts' ? 'bg-orange-500' :
                                    ($statusFilter === 'testing' ? 'bg-violet-600' :
                                    ($statusFilter === 'ready_for_pickup' ? 'bg-sky-500' :
                                    ($statusFilter === 'completed' ? 'bg-emerald-500' :
                                    ($statusFilter === 'picked_up' ? 'bg-slate-700' :
                                    ($statusFilter === 'unrepairable' ? 'bg-rose-600' :
                                    ($statusFilter === 'cancelled' ? 'bg-rose-500' :
                                    ($statusFilter === 'pending' ? 'bg-amber-500' : 'bg-slate-400')))))))))))
                                }}"></span>
                            @else
                                <i data-lucide="activity" class="w-4 h-4 {{ $statusFilter !== '' ? 'text-primary-600' : 'text-slate-400' }} shrink-0"></i>
                            @endif
                            <span class="truncate">{{ $currentStatusLabel }}</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 shrink-0 ml-1.5 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
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
                        class="absolute left-0 lg:right-0 lg:left-auto mt-1.5 w-60 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-40"
                    >
                        <div class="px-3.5 py-1.5 text-3xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                            Pilih Status
                        </div>
                        <div class="py-1 max-h-64 overflow-y-auto">
                            @foreach($statusLabels as $value => $label)
                                <button
                                    type="button"
                                    wire:click="$set('statusFilter', '{{ $value }}')"
                                    @click="open = false"
                                    class="w-full flex items-center justify-between px-3.5 py-2 text-xs text-left transition-colors cursor-pointer {{ $statusFilter === (string)$value ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}"
                                >
                                    <div class="flex items-center space-x-2 truncate">
                                        @if($value)
                                            <span class="w-2 h-2 rounded-full shrink-0 {{ 
                                                $value === 'received' ? 'bg-blue-600' :
                                                ($value === 'diagnosing' ? 'bg-purple-600' :
                                                ($value === 'waiting_approval' ? 'bg-amber-500' :
                                                ($value === 'in_progress' ? 'bg-indigo-600' :
                                                ($value === 'waiting_parts' ? 'bg-orange-500' :
                                                ($value === 'testing' ? 'bg-violet-600' :
                                                ($value === 'ready_for_pickup' ? 'bg-sky-500' :
                                                ($value === 'completed' ? 'bg-emerald-500' :
                                                ($value === 'picked_up' ? 'bg-slate-700' :
                                                ($value === 'unrepairable' ? 'bg-rose-600' :
                                                ($value === 'cancelled' ? 'bg-rose-500' :
                                                ($value === 'pending' ? 'bg-amber-500' : 'bg-slate-400')))))))))))
                                            }}"></span>
                                        @else
                                            <span class="w-2 h-2 rounded-full shrink-0 bg-transparent"></span>
                                        @endif
                                        <span class="truncate">{{ $label }}</span>
                                    </div>
                                    @if($statusFilter === (string)$value)
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-primary-600 shrink-0"></i>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if(!$isTechnician)
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
                            class="w-full lg:w-56 flex items-center justify-between px-4 py-3 rounded-xl border {{ $technicianFilter !== '' ? 'border-primary-500 bg-primary-50/50 text-primary-700 font-semibold' : 'border-slate-200 bg-white text-slate-700 font-medium hover:border-slate-300' }} text-sm shadow-2xs transition-all cursor-pointer"
                        >
                            <div class="flex items-center space-x-2 truncate">
                                <i data-lucide="user" class="w-4 h-4 {{ $technicianFilter !== '' ? 'text-primary-600' : 'text-slate-400' }} shrink-0"></i>
                                <span class="truncate">{{ $currentTechLabel }}</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 shrink-0 ml-1.5 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
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
                @endif
            </div>
        </div>
    </div>

    <!-- Orders Content (Table on Desktop, Cards on Mobile) -->
    @if($orders->isEmpty())
        <x-empty-state
            icon="clipboard-list"
            title="Tidak ada order ditemukan"
            :description="$isTechnician ? 'Belum ada order servis yang ditugaskan kepada Anda atau sesuai dengan filter pencarian.' : 'Tidak ada order servis HP yang sesuai dengan filter atau kata kunci Anda.'"
            :actionLabel="$isTechnician ? null : 'Tambah Servis'"
            :actionUrl="$isTechnician ? null : route('orders.create')"
        />
    @else
        <!-- Desktop Table -->
        <div class="hidden sm:block bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold text-3xs">
                    <tr>
                        <th class="py-3 px-5">Nota & Tanggal</th>
                        <th class="py-3 px-5">Pelanggan & Unit</th>
                        <th class="py-3 px-5">Keluhan & Teknisi</th>
                        <th class="py-3 px-5">Status</th>
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
                                <span class="text-xs text-slate-400 block font-normal mt-0.5">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="py-3 px-5 max-w-[220px] lg:max-w-[260px] xl:max-w-xs">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="font-semibold text-slate-800 truncate" title="{{ $order->customer->name }}">{{ $order->customer->name }}</span>
                                    @if($order->customer->phone)
                                        <span class="text-xs text-slate-500 font-normal shrink-0">({{ $order->customer->phone }})</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-600 mt-0.5 truncate" title="{{ $order->device->device_name }}{{ $order->device->color ? ' ('.$order->device->color.')' : '' }}">
                                    <span class="font-medium text-slate-700">{{ $order->device->device_name }}</span>
                                    @if($order->device->color)
                                        <span class="text-slate-400 text-xs">({{ $order->device->color }})</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-5 max-w-[180px] lg:max-w-[220px] xl:max-w-xs">
                                <p class="text-xs text-slate-700 truncate" title="{{ $order->complaints }}">
                                    {{ $order->complaints ?: '-' }}
                                </p>
                                <div class="mt-0.5 text-xs text-slate-500">
                                    @if($order->technician)
                                        <span class="truncate block" title="{{ $order->technician->name }}">{{ $order->technician->name }}</span>
                                    @else
                                        <span class="text-slate-400">Belum ditugaskan</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-5 whitespace-nowrap">
                                <x-status-badge :status="$order->status" />
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
                <a
                    href="{{ route('orders.show', $order->id) }}"
                    class="block bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-slate-300 active:scale-[0.99] transition-all space-y-2.5 cursor-pointer group"
                >
                    <!-- 1. Header: Date & Time + Tinted Status Badge -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-slate-400 font-medium">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </span>
                        <x-status-badge :status="$order->status" size="sm" :tinted="true" />
                    </div>

                    <!-- 2. Order Number (Primary Title) & Customer + Device Info -->                    <div>
                        <h3 class="text-[15px] font-bold text-slate-900 leading-snug">
                            #{{ $order->order_number }}
                        </h3>
                        <div class="mt-2 space-y-0.5">
                            <div class="flex items-center gap-1 text-xs text-slate-700 min-w-0">
                                <span class="font-medium text-slate-800 truncate">{{ $order->customer->name }}</span>
                                @if($order->customer->phone)
                                    <span class="text-slate-400 text-xs shrink-0">({{ $order->customer->phone }})</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-600 truncate font-medium">
                                <span class="text-slate-700">{{ $order->device?->brand }} {{ $order->device?->model }}</span>
                                @if($order->device?->variant)
                                    <span class="font-normal text-slate-500">({{ $order->device->variant }})</span>
                                @endif
                                @if($order->device?->color)
                                    <span class="font-normal text-slate-400">({{ $order->device->color }})</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 3. Complaints (Left-aligned with Device & Customer) -->
                    <p class="text-xs text-slate-700 line-clamp-2 break-words">
                        <span class="font-medium">Keluhan:</span>
                        <span>{{ $order->complaints ?: '-' }}</span>
                    </p>
                </a>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>

