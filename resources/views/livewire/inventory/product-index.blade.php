<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Inventaris Sparepart & Aksesoris</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola stok suku cadang HP, barcode SKU, harga modal, dan log mutasi barang.</p>
        </div>
        <div>
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2.5 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-xs transition-colors"
            >
                <i data-lucide="plus-circle" class="w-4 h-4 mr-1.5"></i>
                Tambah Barang Baru
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Total Macam Item</span>
            <div class="flex items-center justify-between mt-1">
                <span class="text-2xl font-bold text-slate-800">{{ $stats['total_items'] }}</span>
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                    <i data-lucide="package" class="w-4 h-4"></i>
                </span>
            </div>
        </div>

        <div
            wire:click="$toggle('onlyLowStock')"
            class="bg-white p-4 rounded-2xl border transition-all cursor-pointer {{ $onlyLowStock ? 'border-rose-400 ring-2 ring-rose-100 bg-rose-50/20' : 'border-slate-200/80 shadow-2xs hover:border-rose-200' }}"
        >
            <span class="text-2xs font-semibold text-rose-500 uppercase tracking-wider block">Stok Menipis / Habis</span>
            <div class="flex items-center justify-between mt-1">
                <span class="text-2xl font-bold text-rose-600">{{ $stats['low_stock_count'] }}</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Sparepart Servis</span>
            <div class="flex items-center justify-between mt-1">
                <span class="text-2xl font-bold text-indigo-600">{{ $stats['spareparts_count'] }}</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="cpu" class="w-4 h-4"></i>
                </span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-2xs font-semibold text-slate-400 uppercase tracking-wider block">Aksesoris & Lainnya</span>
            <div class="flex items-center justify-between mt-1">
                <span class="text-2xl font-bold text-emerald-600">{{ $stats['accessories_count'] }}</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="headphones" class="w-4 h-4"></i>
                </span>
            </div>
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
                    placeholder="Cari nama barang, SKU, kode barcode..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 placeholder:text-slate-400"
                />
            </div>

            <div class="flex items-center gap-2">
                <select
                    wire:model.live="typeFilter"
                    class="rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 text-slate-700"
                >
                    <option value="">Semua Tipe</option>
                    <option value="sparepart">Sparepart Saja</option>
                    <option value="accessory">Aksesoris Saja</option>
                    <option value="unit">Unit Handphone</option>
                    <option value="other">Lain-lain</option>
                </select>

                <select
                    wire:model.live="categoryFilter"
                    class="rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 text-slate-700"
                >
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Product Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-2xs tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Nama Produk & Kode</th>
                        <th class="py-3 px-4">Tipe & Kategori</th>
                        <th class="py-3 px-4 text-right">Harga Modal</th>
                        <th class="py-3 px-4 text-right">Harga Jual</th>
                        <th class="py-3 px-4 text-center">Stok</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block text-xs">{{ $item->name }}</span>
                                <span class="text-2xs text-slate-400 font-mono">
                                    {{ $item->sku ? 'SKU: ' . $item->sku : '' }}
                                    {{ $item->barcode ? ' • Barcode: ' . $item->barcode : '' }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-2xs font-semibold capitalize {{ $item->type === 'sparepart' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $item->type }}
                                </span>
                                @if ($item->category)
                                    <span class="block text-2xs text-slate-400 mt-0.5">{{ $item->category->name }}</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-right text-slate-600 font-medium">
                                Rp{{ number_format($item->buy_price, 0, ',', '.') }}
                            </td>

                            <td class="py-3 px-4 text-right font-bold text-slate-900">
                                Rp{{ number_format($item->sell_price, 0, ',', '.') }}
                            </td>

                            <td class="py-3 px-4 text-center">
                                @if ($item->isLowStock())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i data-lucide="alert-circle" class="w-3 h-3 mr-1 text-rose-500"></i>
                                        {{ $item->stock }} {{ $item->unit }}
                                    </span>
                                @else
                                    <span class="font-bold text-slate-800">
                                        {{ $item->stock }} <span class="text-2xs text-slate-400 font-normal">{{ $item->unit }}</span>
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center">
                                @if ($item->is_active)
                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                @else
                                    <span class="inline-block w-2 h-2 rounded-full bg-slate-300"></span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-right space-x-1">
                                <button
                                    type="button"
                                    wire:click="openStockAdjustModal('{{ $item->id }}')"
                                    class="inline-flex items-center px-2.5 py-1 text-2xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-xl border border-primary-200 transition-colors"
                                    title="Tambah / Atur Stok"
                                >
                                    <i data-lucide="layers" class="w-3 h-3 mr-1"></i>
                                    Atur Stok
                                </button>
                                <button
                                    type="button"
                                    wire:click="openEditModal('{{ $item->id }}')"
                                    class="p-1 text-slate-400 hover:text-slate-700 transition-colors"
                                    title="Edit Produk"
                                >
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="package-search" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs">Tidak ada data barang yang sesuai filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Add / Edit Product -->
    @if ($showProductModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">
                        {{ $isEditing ? 'Edit Data Barang' : 'Tambah Barang Baru' }}
                    </h2>
                    <button wire:click="$set('showProductModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nama Barang / Sparepart <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            wire:model="name"
                            placeholder="Contoh: LCD iPhone 11 Original OLED"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        />
                        @error('name') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Tipe Produk</label>
                            <select
                                wire:model="type"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="sparepart">Sparepart</option>
                                <option value="accessory">Aksesoris</option>
                                <option value="unit">Unit Handphone</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Kategori</label>
                            <select
                                wire:model="category_id"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">-- Tanpa Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Kode SKU</label>
                            <input
                                type="text"
                                wire:model="sku"
                                placeholder="LCD-IP11-OLED"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 font-mono"
                            />
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Kode Barcode</label>
                            <input
                                type="text"
                                wire:model="barcode"
                                placeholder="899123456789"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 font-mono"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Harga Modal / Beli (Rp) <span class="text-rose-500">*</span></label>
                            <input
                                type="number"
                                wire:model="buy_price"
                                min="0"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                            />
                            @error('buy_price') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                            <input
                                type="number"
                                wire:model="sell_price"
                                min="0"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 font-bold"
                            />
                            @error('sell_price') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        @if (!$isEditing)
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">Stok Awal</label>
                                <input
                                    type="number"
                                    wire:model="stock"
                                    min="0"
                                    class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                                />
                            </div>
                        @endif

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Batas Minimum</label>
                            <input
                                type="number"
                                wire:model="min_stock"
                                min="0"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Satuan</label>
                            <input
                                type="text"
                                wire:model="unit"
                                placeholder="pcs"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="inline-flex items-center space-x-2 text-xs">
                            <input
                                type="checkbox"
                                wire:model="is_active"
                                class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                            />
                            <span class="text-slate-700 font-medium">Barang Aktif (Dapat digunakan dalam order & POS)</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="$set('showProductModal', false)"
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="saveProduct"
                        class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors"
                    >
                        Simpan Barang
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Quick Stock Adjustment / Stock-In -->
    @if ($showStockAdjustModal && $selectedProductForStock)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Atur Stok Barang</h2>
                        <span class="text-2xs text-slate-500">{{ $selectedProductForStock->name }}</span>
                    </div>
                    <button wire:click="$set('showStockAdjustModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center text-xs">
                    <span class="text-slate-600">Sisa Stok Saat Ini:</span>
                    <span class="font-bold text-slate-900 text-sm">
                        {{ $selectedProductForStock->stock }} {{ $selectedProductForStock->unit }}
                    </span>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Jenis Operasi Stok</label>
                        <select
                            wire:model="adjust_type"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="in">Stok Masuk (+ Tambah Stok)</option>
                            <option value="out">Stok Keluar (- Kurang Stok)</option>
                            <option value="adjustment">Stok Opname (Set Stok Fisik Baru)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">
                            {{ $adjust_type === 'adjustment' ? 'Jumlah Stok Fisik Riil' : 'Jumlah Unit' }} <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="number"
                            wire:model="adjust_quantity"
                            min="1"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 font-bold"
                        />
                        @error('adjust_quantity') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Catatan / Alasan</label>
                        <input
                            type="text"
                            wire:model="adjust_notes"
                            placeholder="Contoh: Pembelian supplier, retur rusak, opname bulanan..."
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="$set('showStockAdjustModal', false)"
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="submitStockAdjustment"
                        class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

