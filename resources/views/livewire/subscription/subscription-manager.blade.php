<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Paket & Langganan Toko</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pilih paket langganan yang sesuai dengan skala bisnis dan jumlah teknisi toko Anda.</p>
        </div>

        <!-- Billing Cycle Switcher -->
        <div class="inline-flex items-center p-1 bg-slate-200/70 rounded-2xl border border-slate-200">
            <button
                type="button"
                wire:click="$set('billingCycle', 'monthly')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $billingCycle === 'monthly' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
            >
                Tagihan Bulanan
            </button>
            <button
                type="button"
                wire:click="$set('billingCycle', 'yearly')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1.5 {{ $billingCycle === 'yearly' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}"
            >
                <span>Tagihan Tahunan</span>
                <span class="text-3xs font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded-full">Hemat 17%</span>
            </button>
        </div>
    </div>

    <!-- Active Plan Status Banner -->
    <div class="bg-gradient-to-r from-primary-900 to-indigo-900 text-white rounded-3xl p-6 sm:p-8 shadow-sm relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center space-x-2.5">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/20 text-white backdrop-blur-xs">
                        Paket Aktif: {{ ucfirst($store->plan) }}
                    </span>
                    @if ($currentSubscription && $currentSubscription->isTrial())
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-400 text-slate-950">
                            Masa Uji Coba Gratis
                        </span>
                    @endif
                </div>

                <h2 class="text-xl sm:text-2xl font-bold tracking-tight">
                    {{ $store->name }}
                </h2>

                <p class="text-xs text-slate-300 max-w-xl">
                    @if ($currentSubscription && $currentSubscription->isTrial())
                        Masa uji coba gratis Anda berlaku hingga <strong>{{ $currentSubscription->trial_ends_at?->isoFormat('D MMMM Y') }}</strong> (Tersisa {{ $currentSubscription->days_left }} hari lagi). Upgrade sekarang untuk memastikan operasional tidak terputus.
                    @elseif ($currentSubscription && $currentSubscription->ends_at)
                        Masa aktif langganan toko Anda berlaku sampai dengan <strong>{{ $currentSubscription->ends_at->isoFormat('D MMMM Y') }}</strong>.
                    @else
                        Akun aktif dan siap digunakan untuk mengelola perbaikan smartphone.
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="p-4 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10 text-center min-w-32">
                    <span class="text-3xs uppercase tracking-wider text-slate-300 block">Kapasitas Teknisi</span>
                    <span class="text-2xl font-bold text-white mt-0.5 block">
                        {{ $store->plan === 'pro' ? '10' : ($store->plan === 'business' ? '3' : '1') }}
                    </span>
                    <span class="text-3xs text-slate-300">Akun Teknisi Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach ($plans as $plan)
            @php
                $isCurrent = $store->plan === $plan->slug;
                $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
            @endphp

            <div class="bg-white rounded-3xl p-6 border {{ $isCurrent ? 'border-primary-500 ring-2 ring-primary-500/20 shadow-md' : 'border-slate-200/80 shadow-2xs' }} flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">{{ $plan->name }}</h3>
                        @if ($isCurrent)
                            <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                Paket Aktif
                            </span>
                        @endif
                    </div>

                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono">
                            Rp{{ number_format($price, 0, ',', '.') }}
                        </span>
                        <span class="text-xs text-slate-500">/ {{ $billingCycle === 'yearly' ? 'tahun' : 'bulan' }}</span>
                    </div>

                    <div class="pt-4 border-t border-slate-100 space-y-2.5 text-xs text-slate-600">
                        <div class="flex items-center space-x-2 font-medium text-slate-800">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span>Maksimal <strong>{{ $plan->max_technicians }} Akun Teknisi</strong></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span>Unlimited Order Servis HP</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span>Live Tracking Link Pelanggan</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span>Cetak Nota Thermal 58mm/80mm</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span>Manajemen Inventaris Sparepart</span>
                        </div>

                        @if ($plan->slug === 'business' || $plan->slug === 'pro')
                            <div class="flex items-center space-x-2 font-medium text-slate-800">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                <span>Buku Kas & Kulakan Supplier</span>
                            </div>
                            <div class="flex items-center space-x-2 font-medium text-slate-800">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                <span>Laporan Bisnis & Analisis Omset</span>
                            </div>
                        @endif

                        @if ($plan->slug === 'pro')
                            <div class="flex items-center space-x-2 font-bold text-primary-700">
                                <i data-lucide="zap" class="w-4 h-4 text-amber-500 shrink-0"></i>
                                <span>Dukungan Prioritas WhatsApp 24/7</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    @if ($isCurrent)
                        <button
                            type="button"
                            disabled
                            class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 font-semibold text-xs cursor-not-allowed"
                        >
                            Paket Sedang Digunakan
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="selectPlan('{{ $plan->id }}')"
                            class="w-full py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold text-xs shadow-2xs transition-colors"
                        >
                            Pilih Paket {{ $plan->name }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Invoice Billing History -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-2xs space-y-4">
        <h2 class="text-base font-bold text-slate-900 pb-2 border-b border-slate-100">
            Riwayat Tagihan & Pembayaran Langganan
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-2xs tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">No. Invoice</th>
                        <th class="py-3 px-4">Paket Langganan</th>
                        <th class="py-3 px-4">Siklus</th>
                        <th class="py-3 px-4 text-right">Nominal</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Tanggal Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($invoices as $inv)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">#{{ $inv->invoice_number }}</td>
                            <td class="py-3 px-4 font-medium">{{ $inv->plan?->name }}</td>
                            <td class="py-3 px-4 capitalize">{{ $inv->billing_cycle }}</td>
                            <td class="py-3 px-4 text-right font-bold">Rp{{ number_format($inv->amount, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center">
                                @if ($inv->status === 'paid')
                                    <span class="inline-block px-2 py-0.5 rounded text-2xs font-bold bg-emerald-100 text-emerald-800">
                                        LUNAS
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-2xs font-bold bg-amber-100 text-amber-800">
                                        MENUNGGU BAYAR
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right text-slate-500">
                                {{ $inv->paid_at ? $inv->paid_at->format('d/m/Y H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 italic">
                                Belum ada riwayat tagihan berbayar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Checkout / Payment Details -->
    @if ($showCheckoutModal && $selectedPlanForCheckout)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-7 space-y-5 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Upgrade ke Paket {{ $selectedPlanForCheckout->name }}</h2>
                        <span class="text-2xs text-slate-500">Siklus: {{ ucfirst($billingCycle) }}</span>
                    </div>
                    <button wire:click="$set('showCheckoutModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                @if (!$activeInvoice)
                    <!-- Step 1: Review & Generate Invoice -->
                    <div class="space-y-4 text-xs">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Paket:</span>
                                <span class="font-bold text-slate-900">{{ $selectedPlanForCheckout->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Periode:</span>
                                <span class="font-medium text-slate-800">{{ $billingCycle === 'yearly' ? '1 Tahun' : '1 Bulan' }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-200 text-sm font-bold text-slate-900">
                                <span>Total Tagihan:</span>
                                <span>
                                    Rp{{ number_format($billingCycle === 'yearly' ? $selectedPlanForCheckout->price_yearly : $selectedPlanForCheckout->price_monthly, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <p class="text-2xs text-slate-500">
                            Dengan mengklik tombol di bawah, invoice resmi akan diterbitkan dengan nomor rekening tujuan transfer.
                        </p>

                        <button
                            type="button"
                            wire:click="generateInvoice"
                            class="w-full py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold text-xs shadow-2xs transition-colors"
                        >
                            Terbitkan Invoice Pembayaran
                        </button>
                    </div>
                @else
                    <!-- Step 2: Payment Instructions -->
                    <div class="space-y-4 text-xs">
                        <div class="p-4 rounded-2xl bg-primary-50 border border-primary-100 space-y-2 text-center">
                            <span class="text-2xs uppercase tracking-wider text-primary-800 font-semibold block">Total yang Harus Ditransfer</span>
                            <span class="text-2xl font-bold font-mono text-primary-950 block">
                                Rp{{ number_format($activeInvoice->amount, 0, ',', '.') }}
                            </span>
                            <span class="text-3xs text-primary-700 block">No. Invoice: #{{ $activeInvoice->invoice_number }}</span>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1.5">
                            <span class="font-bold text-slate-800 block text-2xs uppercase">Rekening Tujuan Transfer:</span>
                            <p class="text-slate-700">Bank Central Asia (BCA): <strong class="font-mono">8801-9922-3344</strong></p>
                            <p class="text-slate-700">Atas Nama: <strong>PT SEMAPON TEKNOLOGI NUSANTARA</strong></p>
                        </div>

                        <div class="pt-2">
                            <button
                                type="button"
                                wire:click="simulatePay"
                                class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-2xs transition-colors flex items-center justify-center space-x-1.5"
                            >
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>Saya Sudah Transfer (Konfirmasi Langsung)</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

