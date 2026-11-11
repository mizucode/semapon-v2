<div class="space-y-6">
    @if ($isInvalid)
        <!-- Invalid / Expired Token State -->
        <div class="bg-white rounded-3xl p-8 text-center border border-slate-200/80 shadow-sm space-y-4 max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                <i data-lucide="shield-alert" class="w-8 h-8"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-900">Tautan Pelacakan Tidak Valid</h1>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    {{ $errorMessage }}
                    Pastikan Anda membuka tautan yang dikirimkan oleh pihak toko kami atau hubungi nomor WhatsApp toko untuk konfirmasi status unit Anda.
                </p>
            </div>
        </div>
    @else
        @php
            $steps = [
                'received' => ['label' => 'Diterima', 'icon' => 'inbox'],
                'diagnosing' => ['label' => 'Diagnosa', 'icon' => 'search'],
                'in_progress' => ['label' => 'Pengerjaan', 'icon' => 'wrench'],
                'testing' => ['label' => 'Pengujian QC', 'icon' => 'check-circle-2'],
                'ready_for_pickup' => ['label' => 'Siap Diambil', 'icon' => 'bell'],
                'completed' => ['label' => 'Selesai', 'icon' => 'award'],
            ];

            $statusRank = [
                'received' => 1,
                'diagnosing' => 2,
                'waiting_approval' => 2,
                'in_progress' => 3,
                'waiting_parts' => 3,
                'testing' => 4,
                'ready_for_pickup' => 5,
                'completed' => 6,
                'cancelled' => 0,
                'unrepairable' => 0,
            ];

            $currentRank = $statusRank[$order->status] ?? 1;

            $statusLabels = [
                'received' => 'Perangkat Diterima',
                'diagnosing' => 'Sedang Didiagnosa',
                'waiting_approval' => 'Menunggu Konfirmasi Anda',
                'in_progress' => 'Sedang Dikerjakan Teknisi',
                'waiting_parts' => 'Menunggu Suku Cadang',
                'testing' => 'Pengujian Fungsi (QC)',
                'ready_for_pickup' => 'Perbaikan Selesai — Siap Diambil',
                'completed' => 'Unit Telah Diambil (Selesai)',
                'cancelled' => 'Perbaikan Dibatalkan',
                'unrepairable' => 'Tidak Dapat Diperbaiki',
            ];
        @endphp

        <!-- Top Order Card -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
                <div>
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Nomor Tanda Terima</span>
                    <h1 class="text-xl sm:text-2xl font-bold font-mono text-slate-900">#{{ $order->order_number }}</h1>
                </div>

                <div class="sm:text-right">
                    @if ($order->status === 'cancelled' || $order->status === 'unrepairable')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            {{ $statusLabels[$order->status] }}
                        </span>
                    @elseif ($order->status === 'ready_for_pickup' || $order->status === 'completed')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $statusLabels[$order->status] }}
                        </span>
                    @elseif ($order->status === 'waiting_approval')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                            {{ $statusLabels[$order->status] }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                            {{ $statusLabels[$order->status] }}
                        </span>
                    @endif
                    <p class="text-2xs text-slate-400 mt-1">Diterima: {{ $order->created_at->isoFormat('D MMMM Y, HH:mm') }} WIB</p>
                </div>
            </div>

            <!-- Customer Action Request (Approval Gate) -->
            @if ($order->status === 'waiting_approval')
                <div class="p-4 sm:p-5 rounded-2xl bg-amber-50/80 border border-amber-200 space-y-3">
                    <div class="flex items-start space-x-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                        <div>
                            <h2 class="text-xs sm:text-sm font-bold text-amber-900">Konfirmasi Persetujuan Biaya Servis</h2>
                            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                                Teknisi kami telah selesai memeriksa unit Anda. Rincian perbaikan dan estimasi biaya tercantum di bawah. Silakan konfirmasi keputusan Anda agar perbaikan dapat segera kami lanjutkan.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2.5 pt-1">
                        <button
                            type="button"
                            wire:click="approveEstimate"
                            wire:confirm="Setujui estimasi biaya dan lanjutkan perbaikan?"
                            class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-2xs transition-colors flex items-center justify-center space-x-1.5"
                        >
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Setujui & Lanjutkan Perbaikan</span>
                        </button>
                        <button
                            type="button"
                            wire:click="rejectEstimate"
                            wire:confirm="Yakin ingin membatalkan servis perangkat ini?"
                            class="py-2.5 px-4 rounded-xl bg-white border border-rose-300 hover:bg-rose-50 text-rose-700 font-semibold text-xs transition-colors flex items-center justify-center space-x-1.5"
                        >
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span>Batalkan Servis</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Visual Progress Tracker (Steps) -->
            @if (!in_array($order->status, ['cancelled', 'unrepairable']))
                <div class="pt-2">
                    <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block mb-3">Progres Pengerjaan</span>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        @php $i = 1; @endphp
                        @foreach ($steps as $stepKey => $stepMeta)
                            @php
                                $isDone = $currentRank > $i;
                                $isCurrent = $currentRank === $i;
                                $i++;
                            @endphp
                            <div class="flex flex-col items-center text-center p-2 rounded-xl {{ $isCurrent ? 'bg-primary-50 border border-primary-200 text-primary-700' : ($isDone ? 'bg-slate-50 text-emerald-700' : 'bg-slate-50/50 text-slate-400') }}">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center mb-1 text-xs {{ $isCurrent ? 'bg-primary-600 text-white shadow-2xs' : ($isDone ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500') }}">
                                    @if ($isDone)
                                        ✓
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </div>
                                <span class="text-2xs font-medium leading-tight">{{ $stepMeta['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Device Info Card -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-2xs space-y-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400 pb-2 border-b border-slate-100">
                Informasi Perangkat
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 text-2xs block uppercase">Tipe Perangkat</span>
                    <span class="font-bold text-slate-800 text-sm">
                        {{ $order->device?->brand ?? '-' }} {{ $order->device?->model ?? '-' }}
                    </span>
                    <span class="text-slate-500 block text-2xs mt-0.5">
                        Warna: {{ $order->device?->color ?? '-' }}
                    </span>
                </div>

                @if ($order->device?->imei_or_serial)
                    <div>
                        <span class="text-slate-400 text-2xs block uppercase">IMEI / Nomor Seri</span>
                        @php
                            $imei = $order->device->imei_or_serial;
                            $maskedImei = strlen($imei) > 8 ? substr($imei, 0, 4) . '••••' . substr($imei, -4) : $imei;
                        @endphp
                        <span class="font-mono text-slate-700 bg-slate-50 px-2 py-0.5 rounded border border-slate-100 inline-block mt-0.5">
                            {{ $maskedImei }}
                        </span>
                    </div>
                @endif
            </div>

            <div class="pt-2 border-t border-slate-100 text-xs">
                <span class="text-slate-400 text-2xs block uppercase mb-1">Keluhan Yang Dilaporkan</span>
                <p class="p-3 bg-slate-50 rounded-xl text-slate-700 font-medium border border-slate-100">
                    {{ $order->complaint }}
                </p>
            </div>

            @if ($order->estimated_completion)
                <div class="text-xs text-slate-500 flex items-center gap-1.5 pt-1">
                    <i data-lucide="calendar-clock" class="w-3.5 h-3.5 text-primary-600"></i>
                    <span>Estimasi Selesai: <strong>{{ \Carbon\Carbon::parse($order->estimated_completion)->isoFormat('D MMMM Y') }}</strong></span>
                </div>
            @endif
        </div>

        <!-- Cost Breakdown Card -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-2xs space-y-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400 pb-2 border-b border-slate-100">
                Rincian Biaya Servis
            </h2>

            @if ($order->items->isNotEmpty())
                <div class="space-y-2">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between items-center text-xs py-1.5 border-b border-slate-50">
                            <span class="text-slate-700 font-medium">{{ $item->name }}</span>
                            <span class="font-semibold text-slate-900">Rp{{ number_format($item->price, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 italic py-1">Estimasi rincian biaya sedang dihitung oleh teknisi.</p>
            @endif

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Total Biaya:</span>
                    <span class="font-bold text-slate-900 text-sm">Rp{{ number_format($order->total_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Pembayaran Masuk:</span>
                    <span class="font-semibold text-emerald-700">Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-900 pt-2 border-t border-slate-200">
                    <span class="font-bold">Sisa Tagihan:</span>
                    <span class="font-bold text-base {{ $order->remaining_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $order->remaining_balance > 0 ? 'Rp' . number_format($order->remaining_balance, 0, ',', '.') : 'LUNAS (Rp0)' }}
                    </span>
                </div>
            </div>

            @if ($order->warranty_days > 0)
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 text-xs text-emerald-800 flex items-center space-x-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>Dilindungi <strong>Garansi Toko {{ $order->warranty_days }} Hari</strong> {{ $order->warranty_until ? '(s/d ' . \Carbon\Carbon::parse($order->warranty_until)->format('d/m/Y') . ')' : '' }}</span>
                </div>
            @endif
        </div>

        <!-- Share & Store Contact Card -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-2xs space-y-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400 pb-2 border-b border-slate-100">
                Pusat Bantuan & Kontak Toko
            </h2>

            <div class="text-xs text-slate-600 space-y-1">
                <p class="font-bold text-slate-900 text-sm">{{ $store?->name }}</p>
                @if ($store?->address)
                    <p>{{ $store->address }}</p>
                @endif
                @if ($store?->phone)
                    <p>WhatsApp: {{ $store->phone }}</p>
                @endif
            </div>

            <div class="pt-2 flex flex-wrap gap-2.5" x-data="{ copied: false }">
                <!-- Copy Tracking Link -->
                <button
                    type="button"
                    @click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => copied = false, 2000)"
                    class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
                >
                    <i data-lucide="copy" class="w-3.5 h-3.5 mr-1.5"></i>
                    <span x-text="copied ? 'Tautan Disalin!' : 'Salin Tautan Tracking'">Salin Tautan Tracking</span>
                </button>

                @if ($store?->phone)
                    <a
                        href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $store->phone) }}?text={{ rawurlencode('Halo ' . ($store->name ?? '') . ', saya ingin bertanya mengenai servis #' . $order->order_number) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition-colors"
                    >
                        <i data-lucide="message-circle" class="w-3.5 h-3.5 mr-1.5"></i>
                        Chat WhatsApp Toko
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>

