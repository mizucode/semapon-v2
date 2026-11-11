<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pusat Pengingat & Notifikasi</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Peringatan otomatis untuk order lewat batas estimasi, HP selesai belum diambil, dan stok sparepart kritis.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="scanNow"
                class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition-colors shadow-2xs"
            >
                <i data-lucide="refresh-cw" class="w-4 h-4 mr-1.5 text-primary-600"></i>
                Pindai Sekarang
            </button>
            <button
                type="button"
                wire:click="markAllRead"
                class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-xs transition-colors"
            >
                <i data-lucide="check-check" class="w-4 h-4 mr-1.5"></i>
                Tandai Semua Dibaca
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <button
                type="button"
                wire:click="$set('onlyUnread', true)"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $onlyUnread ? 'bg-primary-600 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
            >
                Belum Dibaca Saja
            </button>
            <button
                type="button"
                wire:click="$set('onlyUnread', false)"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ !$onlyUnread ? 'bg-primary-600 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
            >
                Semua Riwayat
            </button>
        </div>
    </div>

    <!-- Reminders List -->
    <div class="space-y-3">
        @forelse ($reminders as $item)
            @php
                $typeMeta = [
                    'overdue_order' => ['icon' => 'clock', 'badge' => 'Order Terlambat', 'color' => 'rose'],
                    'ready_unpicked' => ['icon' => 'bell', 'badge' => 'Belum Diambil', 'color' => 'amber'],
                    'low_stock' => ['icon' => 'alert-triangle', 'badge' => 'Stok Menipis', 'color' => 'orange'],
                    'tech_assigned' => ['icon' => 'wrench', 'badge' => 'Tugas Baru', 'color' => 'indigo'],
                ];
                $meta = $typeMeta[$item->type] ?? ['icon' => 'info', 'badge' => 'Pengingat', 'color' => 'slate'];
            @endphp

            <div class="bg-white p-4 sm:p-5 rounded-2xl border {{ $item->is_read ? 'border-slate-200/60 opacity-80' : 'border-slate-200 shadow-2xs ring-1 ring-primary-100' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all">
                <div class="flex items-start space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-{{ $meta['color'] }}-50 text-{{ $meta['color'] }}-600 flex items-center justify-center shrink-0 mt-0.5">
                        <i data-lucide="{{ $meta['icon'] }}" class="w-5 h-5"></i>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="inline-block px-2 py-0.5 rounded text-3xs font-bold uppercase tracking-wider bg-{{ $meta['color'] }}-50 text-{{ $meta['color'] }}-700">
                                {{ $meta['badge'] }}
                            </span>
                            @if (!$item->is_read)
                                <span class="w-2 h-2 rounded-full bg-primary-600 inline-block"></span>
                            @endif
                            <span class="text-3xs text-slate-400">{{ $item->created_at->diffForHumans() }}</span>
                        </div>

                        <h2 class="text-sm font-bold text-slate-900">{{ $item->title }}</h2>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $item->message }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                    @if ($item->reference_type === 'service_order' && $item->reference_id)
                        <a
                            href="{{ route('orders.show', $item->reference_id) }}"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-xl border border-primary-200 transition-colors"
                        >
                            Buka Order →
                        </a>
                    @elseif ($item->reference_type === 'product' && $item->reference_id)
                        <a
                            href="{{ route('inventory.index') }}"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-xl border border-primary-200 transition-colors"
                        >
                            Lihat Stok →
                        </a>
                    @endif

                    @if (!$item->is_read)
                        <button
                            type="button"
                            wire:click="markAsRead('{{ $item->id }}')"
                            class="p-1.5 text-slate-400 hover:text-slate-700 transition-colors rounded-lg hover:bg-slate-100"
                            title="Tandai Sudah Dibaca"
                        >
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white p-12 text-center rounded-2xl border border-slate-200/80 shadow-2xs space-y-2">
                <i data-lucide="bell-off" class="w-10 h-10 text-slate-300 mx-auto"></i>
                <h2 class="text-sm font-bold text-slate-700">Tidak Ada Pengingat</h2>
                <p class="text-xs text-slate-400">Semua pekerjaan terkelola dengan baik. Tidak ada notifikasi yang memerlukan perhatian saat ini.</p>
            </div>
        @endforelse
    </div>

    @if ($reminders->hasPages())
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            {{ $reminders->links() }}
        </div>
    @endif
</div>

