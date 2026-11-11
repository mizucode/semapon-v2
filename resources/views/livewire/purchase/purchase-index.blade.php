<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pembelian & Kulakan Sparepart</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Catat purchase order ke supplier, konfirmasi penerimaan barang, dan kelola hutang kulakan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('suppliers.index') }}"
                class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition-colors shadow-2xs"
            >
                <i data-lucide="truck" class="w-4 h-4 mr-1.5 text-slate-500"></i>
                Data Supplier
            </a>
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-xs transition-colors"
            >
                <i data-lucide="plus-circle" class="w-4 h-4 mr-1.5"></i>
                Buat PO Baru
            </button>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nomor PO, nama supplier..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 placeholder:text-slate-400"
                />
            </div>

            <div class="flex items-center gap-2">
                <select
                    wire:model.live="statusFilter"
                    class="rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 text-slate-700"
                >
                    <option value="">Semua Status Barang</option>
                    <option value="pending">Menunggu Kedatangan (Pending)</option>
                    <option value="received">Barang Diterima (Received)</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>

                <select
                    wire:model.live="paymentFilter"
                    class="rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 text-slate-700"
                >
                    <option value="">Semua Status Bayar</option>
                    <option value="paid">Lunas</option>
                    <option value="partially_paid">Sebagian (Ada Hutang)</option>
                    <option value="unpaid">Belum Bayar</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Purchase Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-2xs tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">No. PO & Tanggal</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Rincian Barang</th>
                        <th class="py-3 px-4 text-right">Total Tagihan</th>
                        <th class="py-3 px-4 text-center">Status Barang</th>
                        <th class="py-3 px-4 text-center">Pembayaran</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchases as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 font-mono block">#{{ $item->purchase_number }}</span>
                                <span class="text-2xs text-slate-400">{{ $item->purchase_date->format('d/m/Y') }}</span>
                            </td>

                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ $item->supplier?->name ?? '-' }}
                            </td>

                            <td class="py-3 px-4">
                                <span class="text-slate-600 block text-2xs">
                                    {{ $item->items->count() }} macam item
                                    @if ($item->items->isNotEmpty())
                                        ({{ $item->items->first()->product?->name }}...)
                                    @endif
                                </span>
                            </td>

                            <td class="py-3 px-4 text-right">
                                <span class="font-bold text-slate-900 block">Rp{{ number_format($item->total_amount, 0, ',', '.') }}</span>
                                @if ($item->remaining_debt > 0)
                                    <span class="text-2xs text-rose-600 font-semibold">
                                        Hutang: Rp{{ number_format($item->remaining_debt, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-2xs text-emerald-600 font-medium">Lunas</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center">
                                @if ($item->status === 'received')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Diterima Masuk Stok
                                    </span>
                                @elseif ($item->status === 'cancelled')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Dibatalkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu Barang
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center">
                                @if ($item->payment_status === 'paid')
                                    <span class="inline-block px-2 py-0.5 rounded text-2xs font-bold bg-emerald-100 text-emerald-800">
                                        LUNAS
                                    </span>
                                @elseif ($item->payment_status === 'partially_paid')
                                    <span class="inline-block px-2 py-0.5 rounded text-2xs font-bold bg-amber-100 text-amber-800">
                                        TERBAYAR SEBAGIAN
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-2xs font-bold bg-rose-100 text-rose-800">
                                        BELUM DIBAYAR
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-right space-x-1">
                                @if ($item->status === 'pending')
                                    <button
                                        type="button"
                                        wire:click="confirmReceive('{{ $item->id }}')"
                                        wire:confirm="Konfirmasi bahwa barang dari supplier sudah tiba fisik dan tambahkan ke stok?"
                                        class="inline-flex items-center px-2.5 py-1 text-2xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-2xs transition-colors"
                                        title="Konfirmasi Terima Barang"
                                    >
                                        <i data-lucide="package-check" class="w-3 h-3 mr-1"></i>
                                        Terima Barang
                                    </button>
                                @endif

                                @if ($item->remaining_debt > 0 && $item->status !== 'cancelled')
                                    <button
                                        type="button"
                                        wire:click="openPayModal('{{ $item->id }}')"
                                        class="inline-flex items-center px-2.5 py-1 text-2xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-xl transition-colors"
                                        title="Bayar Hutang Supplier"
                                    >
                                        <i data-lucide="credit-card" class="w-3 h-3 mr-1"></i>
                                        Bayar
                                    </button>
                                @endif

                                @if ($item->status !== 'cancelled')
                                    <button
                                        type="button"
                                        wire:click="cancelPurchaseOrder('{{ $item->id }}')"
                                        wire:confirm="Batalkan PO ini? Jika barang sudah pernah diterima, stok akan otomatis dikembalikan (reversal)."
                                        class="p-1 text-slate-400 hover:text-rose-600 transition-colors"
                                        title="Batalkan PO"
                                    >
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                Belum ada transaksi pembelian kulakan. Klik "+ Buat PO Baru" di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($purchases->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Create Purchase Order -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Buat Purchase Order (PO) Kulakan</h2>
                    <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Pilih Supplier <span class="text-rose-500">*</span></label>
                        <select
                            wire:model="selected_supplier_id"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">-- Pilih Mitra Supplier --</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }} ({{ $sup->contact_person ?? 'No Contact' }})</option>
                            @endforeach
                        </select>
                        @error('selected_supplier_id') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Items Section -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-700 uppercase text-2xs tracking-wider">Item Barang yang Dibeli:</span>
                            <button
                                type="button"
                                wire:click="addItemRow"
                                class="text-primary-600 font-semibold hover:underline text-2xs flex items-center gap-1"
                            >
                                <i data-lucide="plus" class="w-3 h-3"></i> Tambah Baris
                            </button>
                        </div>

                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            @foreach ($items as $idx => $row)
                                <div class="grid grid-cols-12 gap-2 items-center bg-slate-50 p-2 rounded-xl border border-slate-100">
                                    <div class="col-span-6">
                                        <select
                                            wire:model="items.{{ $idx }}.product_id"
                                            wire:change="onProductSelected({{ $idx }})"
                                            class="w-full rounded-lg border-slate-200 text-xs py-1.5 focus:border-primary-500 focus:ring-primary-500"
                                        >
                                            <option value="">-- Pilih Barang --</option>
                                            @foreach ($products as $prod)
                                                <option value="{{ $prod->id }}">{{ $prod->name }} (Sisa: {{ $prod->stock }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-span-2">
                                        <input
                                            type="number"
                                            wire:model="items.{{ $idx }}.quantity"
                                            min="1"
                                            placeholder="Qty"
                                            class="w-full rounded-lg border-slate-200 text-xs py-1.5 focus:border-primary-500 focus:ring-primary-500"
                                        />
                                    </div>

                                    <div class="col-span-3">
                                        <input
                                            type="number"
                                            wire:model="items.{{ $idx }}.unit_cost"
                                            min="0"
                                            placeholder="Harga Satuan"
                                            class="w-full rounded-lg border-slate-200 text-xs py-1.5 focus:border-primary-500 focus:ring-primary-500 font-mono"
                                        />
                                    </div>

                                    <div class="col-span-1 text-center">
                                        <button
                                            type="button"
                                            wire:click="removeItemRow({{ $idx }})"
                                            class="p-1 text-slate-400 hover:text-rose-600 transition-colors"
                                        >
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Uang Muka / Dibayar Sekarang (Rp)</label>
                            <input
                                type="number"
                                wire:model="down_payment"
                                min="0"
                                placeholder="0"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 font-bold"
                            />
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Catatan PO</label>
                            <input
                                type="text"
                                wire:model="purchase_notes"
                                placeholder="Contoh: Estimasi tiba besok sore..."
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="$set('showCreateModal', false)"
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="savePurchase"
                        class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors"
                    >
                        Simpan PO
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Record Supplier Payment -->
    @if ($showPayModal && $selectedPurchaseForPay)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Bayar Hutang Supplier</h2>
                        <span class="text-2xs text-slate-500">PO #{{ $selectedPurchaseForPay->purchase_number }} — {{ $selectedPurchaseForPay->supplier?->name }}</span>
                    </div>
                    <button wire:click="$set('showPayModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center text-xs">
                    <span class="text-slate-600">Sisa Hutang Tagihan:</span>
                    <span class="font-bold text-rose-600 text-sm">
                        Rp{{ number_format($selectedPurchaseForPay->remaining_debt, 0, ',', '.') }}
                    </span>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nominal Pembayaran (Rp) <span class="text-rose-500">*</span></label>
                        <input
                            type="number"
                            wire:model="pay_amount"
                            min="1"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 font-bold"
                        />
                        @error('pay_amount') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Metode</label>
                            <select
                                wire:model="pay_method"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="transfer">Transfer Bank</option>
                                <option value="cash">Tunai (Cash)</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">No. Bukti / Ref</label>
                            <input
                                type="text"
                                wire:model="pay_ref"
                                placeholder="Contoh: TF-BCA-987"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 font-mono"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="$set('showPayModal', false)"
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="submitPayment"
                        class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors"
                    >
                        Simpan Pembayaran
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

