<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Buku Kas & Keuangan Toko</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pantau arus kas masuk servis, biaya operasional, saldo riil, serta rekap piutang & hutang.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="openCashInModal"
                class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition-colors shadow-2xs"
            >
                <i data-lucide="arrow-down-left" class="w-4 h-4 mr-1.5 text-emerald-600"></i>
                Tambah Modal / Kas Masuk
            </button>
            <button
                type="button"
                wire:click="openExpenseModal"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition-colors"
            >
                <i data-lucide="arrow-up-right" class="w-4 h-4 mr-1.5"></i>
                Catat Pengeluaran
            </button>
        </div>
    </div>

    <!-- Financial KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Pemasukan -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Pemasukan Kas</span>
            <div class="mt-1">
                <span class="text-lg sm:text-xl font-bold text-emerald-600 block">
                    Rp{{ number_format($summary['total_income'], 0, ',', '.') }}
                </span>
                <span class="text-3xs text-slate-400">Total penerimaan uang</span>
            </div>
        </div>

        <!-- Pengeluaran -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Pengeluaran Kas</span>
            <div class="mt-1">
                <span class="text-lg sm:text-xl font-bold text-rose-600 block">
                    Rp{{ number_format($summary['total_expense'], 0, ',', '.') }}
                </span>
                <span class="text-3xs text-slate-400">Biaya & operasional</span>
            </div>
        </div>

        <!-- Saldo Kas / Laba Kas -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Saldo Kas Riil</span>
            <div class="mt-1">
                <span class="text-lg sm:text-xl font-bold {{ $summary['net_profit'] >= 0 ? 'text-primary-700' : 'text-rose-700' }} block">
                    Rp{{ number_format($summary['net_profit'], 0, ',', '.') }}
                </span>
                <span class="text-3xs text-slate-400">Selisih arus kas</span>
            </div>
        </div>

        <!-- Piutang Pelanggan -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Piutang Pelanggan</span>
            <div class="mt-1">
                <span class="text-lg sm:text-xl font-bold text-amber-600 block">
                    Rp{{ number_format($summary['total_receivables'], 0, ',', '.') }}
                </span>
                <span class="text-3xs text-slate-400">Tagihan servis belum lunas</span>
            </div>
        </div>

        <!-- Hutang Supplier -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs col-span-2 lg:col-span-1">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Hutang ke Supplier</span>
            <div class="mt-1">
                <span class="text-lg sm:text-xl font-bold text-purple-700 block">
                    Rp{{ number_format($summary['total_supplier_debt'], 0, ',', '.') }}
                </span>
                <span class="text-3xs text-slate-400">Kewajiban kulakan PO</span>
            </div>
        </div>
    </div>

    <!-- Filters & Date Range -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select
                    wire:model.live="typeFilter"
                    class="rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 text-slate-700 w-full sm:w-auto"
                >
                    <option value="">Semua Transaksi (In & Out)</option>
                    <option value="in">Kas Masuk Saja (+)</option>
                    <option value="out">Kas Keluar Saja (-)</option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
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

    <!-- Cash Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-2xs tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">Tipe Kas</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Keterangan / Rincian</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4 text-right">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $tx)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 text-slate-500">
                                {{ $tx->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td class="py-3 px-4">
                                @if ($tx->type === 'in')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-bold bg-emerald-50 text-emerald-700">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3 mr-1"></i> MASUK
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-bold bg-rose-50 text-rose-700">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3 mr-1"></i> KELUAR
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 font-medium text-slate-800 capitalize">
                                {{ str_replace('_', ' ', $tx->category) }}
                            </td>

                            <td class="py-3 px-4 text-slate-600 max-w-sm truncate">
                                {{ $tx->description }}
                            </td>

                            <td class="py-3 px-4 text-slate-500 text-2xs">
                                {{ $tx->user?->name ?? 'Sistem' }}
                            </td>

                            <td class="py-3 px-4 text-right font-bold {{ $tx->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $tx->type === 'in' ? '+' : '-' }}Rp{{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                Belum ada catatan mutasi kas pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Record Expense -->
    @if ($showExpenseModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Catat Pengeluaran Toko</h2>
                    <button wire:click="$set('showExpenseModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nominal Biaya (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="number"
                            wire:model="expense_amount"
                            min="1"
                            placeholder="0"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 font-bold"
                        />
                        @error('expense_amount') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Kategori Biaya</label>
                            <select
                                wire:model="expense_category_id"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">-- Biaya Umum --</option>
                                @foreach ($expenseCategories as $ec)
                                    <option value="{{ $ec->id }}">{{ $ec->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Metode Bayar</label>
                            <select
                                wire:model="expense_method"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="cash">Kas Tunai</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Keterangan Biaya <span class="text-rose-500">*</span></label>
                        <textarea
                            wire:model="expense_notes"
                            rows="2"
                            placeholder="Contoh: Pembayaran listrik bulanan, beli timah solder & alkohol 99%..."
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        ></textarea>
                        @error('expense_notes') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="$set('showExpenseModal', false)"
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="submitExpense"
                        class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors"
                    >
                        Simpan Biaya
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Manual Cash-In -->
    @if ($showCashInModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Tambah Saldo Kas Masuk</h2>
                    <button wire:click="$set('showCashInModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nominal Kas Masuk (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="number"
                            wire:model="cash_in_amount"
                            min="1"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 font-bold text-emerald-700"
                        />
                        @error('cash_in_amount') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Sumber Kas</label>
                        <select
                            wire:model="cash_in_category"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="modal_awal">Modal Awal / Tambahan Kas Toko</option>
                            <option value="pendapatan_lain">Pendapatan Lain-lain</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Keterangan <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            wire:model="cash_in_desc"
                            placeholder="Contoh: Setoran uang kembalian kasir pagi..."
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        />
                        @error('cash_in_desc') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="$set('showCashInModal', false)"
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="submitCashIn"
                        class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors"
                    >
                        Simpan Kas Masuk
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

