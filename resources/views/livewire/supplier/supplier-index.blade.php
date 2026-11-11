<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Daftar Supplier & Mitra Kulakan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola data vendor suku cadang, kontak pemasok, dan rekap hutang pembelian.</p>
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('purchases.index') }}"
                class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition-colors shadow-2xs"
            >
                <i data-lucide="shopping-bag" class="w-4 h-4 mr-1.5 text-slate-500"></i>
                Riwayat Pembelian (PO)
            </a>
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-xs transition-colors"
            >
                <i data-lucide="plus-circle" class="w-4 h-4 mr-1.5"></i>
                Tambah Supplier
            </button>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div class="relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama supplier, nama kontak sales, nomor telepon..."
                class="w-full pl-10 pr-4 py-2 rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500 placeholder:text-slate-400"
            />
        </div>
    </div>

    <!-- Supplier Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-2xs tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Nama Supplier</th>
                        <th class="py-3 px-4">Kontak Person</th>
                        <th class="py-3 px-4">Telepon / WhatsApp</th>
                        <th class="py-3 px-4">Alamat</th>
                        <th class="py-3 px-4 text-right">Sisa Hutang</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($suppliers as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $item->name }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $item->contact_person ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                @if ($item->phone)
                                    <a
                                        href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $item->phone) }}"
                                        target="_blank"
                                        class="text-emerald-700 hover:underline inline-flex items-center gap-1 font-medium"
                                    >
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        {{ $item->phone }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500 max-w-xs truncate">
                                {{ $item->address ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if ($item->total_debt > 0)
                                    <span class="font-bold text-rose-600">
                                        Rp{{ number_format($item->total_debt, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-emerald-600 font-medium">Lunas</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($item->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-semibold bg-emerald-50 text-emerald-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-semibold bg-slate-100 text-slate-500">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button
                                    type="button"
                                    wire:click="openEditModal('{{ $item->id }}')"
                                    class="p-1 text-slate-400 hover:text-slate-700 transition-colors"
                                    title="Edit Supplier"
                                >
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                Belum ada supplier yang terdaftar. Klik "+ Tambah Supplier" di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($suppliers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Add / Edit Supplier -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">
                        {{ $isEditing ? 'Edit Data Supplier' : 'Tambah Supplier Baru' }}
                    </h2>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nama Supplier / Toko Grosir <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            wire:model="name"
                            placeholder="Contoh: Grosir Sparepart Roxy Mas"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        />
                        @error('name') <span class="text-rose-500 text-2xs block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nama Kontak / Sales</label>
                        <input
                            type="text"
                            wire:model="contact_person"
                            placeholder="Contoh: Koh Hendra"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">No. HP / WhatsApp</label>
                            <input
                                type="text"
                                wire:model="phone"
                                placeholder="08123456789"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Email</label>
                            <input
                                type="email"
                                wire:model="email"
                                placeholder="sales@supplier.com"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Alamat Gudang / Toko</label>
                        <textarea
                            wire:model="address"
                            rows="2"
                            placeholder="Alamat lengkap..."
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-primary-500 focus:ring-primary-500"
                        ></textarea>
                    </div>

                    <div>
                        <label class="inline-flex items-center space-x-2 text-xs">
                            <input
                                type="checkbox"
                                wire:model="is_active"
                                class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                            />
                            <span class="text-slate-700 font-medium">Supplier Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="saveSupplier"
                        class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors"
                    >
                        Simpan Supplier
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

