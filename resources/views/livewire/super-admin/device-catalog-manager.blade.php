<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Master Katalog Smartphone</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola data referensi merek dan spesifikasi smartphone untuk autofill saat penerimaan servis.</p>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="reseedDefaults"
                wire:confirm="Sinkronkan ulang database referensi dengan paket data HP populer Indonesia?"
                class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-2xs transition-colors cursor-pointer"
                title="Tarik / Perbarui model-model default populer"
            >
                <i data-lucide="refresh-cw" class="w-4 h-4 mr-1.5 text-slate-500"></i>
                Sync Data Populer
            </button>
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-primary-600 rounded-xl hover:bg-primary-700 shadow-xs transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
                Tambah Model Baru
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold">
                <i data-lucide="smartphone" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Model HP</p>
                <p class="text-lg font-bold text-slate-900">{{ number_format($totalDevices) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i data-lucide="award" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Merek / Brand</p>
                <p class="text-lg font-bold text-slate-900">{{ number_format($totalBrands) }} Brand</p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Cari merek, tipe HP, chipset (misal: S23, iPhone 13, Helio)..."
                    class="block w-full rounded-xl border border-slate-200 pl-9 pr-4 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                >
            </div>

            <div class="flex items-center gap-2">
                <select
                    wire:model.live="brandFilter"
                    class="rounded-xl border border-slate-200 px-3 py-2 text-xs sm:text-sm text-slate-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                >
                    <option value="">Semua Brand ({{ $brands->count() }})</option>
                    @foreach($brands as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Table List -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/75 text-slate-500 uppercase tracking-wider text-3xs font-semibold">
                        <th class="py-3 px-4">Merek & Model</th>
                        <th class="py-3 px-4">Chipset / Processor</th>
                        <th class="py-3 px-4">Ringkasan Spesifikasi</th>
                        <th class="py-3 px-4">Varian Bawaan</th>
                        <th class="py-3 px-4 text-center">Tipe</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($devices as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $item->model_name }}</div>
                                <div class="text-slate-500 text-2xs flex items-center gap-1.5 mt-0.5">
                                    <span class="font-semibold text-primary-700 bg-primary-50 px-1.5 py-0.5 rounded">{{ $item->brand }}</span>
                                    @if($item->series)
                                        <span>• {{ $item->series }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">
                                {{ $item->chipset ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs truncate" title="{{ $item->specs_summary }}">
                                {{ $item->specs_summary ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                @if(is_array($item->default_variants) && count($item->default_variants) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($item->default_variants, 0, 3) as $var)
                                            <span class="text-3xs px-1.5 py-0.5 bg-slate-100 text-slate-700 rounded">{{ $var }}</span>
                                        @endforeach
                                        @if(count($item->default_variants) > 3)
                                            <span class="text-3xs text-slate-400">+{{ count($item->default_variants) - 3 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($item->store_id)
                                    <span class="text-3xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-semibold border border-amber-200/60">Toko</span>
                                @else
                                    <span class="text-3xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold border border-blue-200/60">Global</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button
                                        type="button"
                                        wire:click="editDevice('{{ $item->id }}')"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-primary-600 hover:bg-primary-50 transition-colors cursor-pointer"
                                        title="Edit"
                                    >
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deleteDevice('{{ $item->id }}')"
                                        wire:confirm="Hapus model '{{ $item->brand }} {{ $item->model_name }}' dari katalog?"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                        title="Hapus"
                                    >
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i data-lucide="smartphone" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p class="text-sm font-medium">Belum ada data model HP yang sesuai.</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan pencarian atau klik "Sync Data Populer".</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $devices->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form (Tambah / Edit Model) -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 transition-opacity" wire:click="$set('showModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                    <form wire:submit="save">
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-base font-bold text-slate-900">
                                {{ $editingId ? 'Edit Model Smartphone' : 'Tambah Model Smartphone Baru' }}
                            </h3>
                            <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Merek / Brand <span class="text-rose-500">*</span></label>
                                    <input
                                        wire:model="brand"
                                        type="text"
                                        required
                                        placeholder="Apple, Samsung, Xiaomi..."
                                        class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                                    >
                                    @error('brand') <p class="text-rose-600 text-3xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Model / Tipe HP <span class="text-rose-500">*</span></label>
                                    <input
                                        wire:model="model_name"
                                        type="text"
                                        required
                                        placeholder="iPhone 13, Galaxy S23..."
                                        class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                                    >
                                    @error('model_name') <p class="text-rose-600 text-3xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Lini / Seri (Opsional)</label>
                                    <input
                                        wire:model="series"
                                        type="text"
                                        placeholder="Galaxy S, Redmi Note, Reno..."
                                        class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Chipset / Processor</label>
                                    <input
                                        wire:model="chipset"
                                        type="text"
                                        placeholder="Snapdragon 8 Gen 2, A15..."
                                        class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Ringkasan Spesifikasi</label>
                                <textarea
                                    wire:model="specs_summary"
                                    rows="2"
                                    placeholder="Layar 6.1 AMOLED 120Hz, Baterai 5000 mAh, Kamera 50MP..."
                                    class="block w-full rounded-xl border border-slate-200 p-3 text-xs text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilihan Varian RAM/ROM (Pisahkan dengan koma)</label>
                                <input
                                    wire:model="default_variants_text"
                                    type="text"
                                    placeholder="Contoh: 8/128 GB, 8/256 GB, 12/512 GB"
                                    class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilihan Warna Resmi (Pisahkan dengan koma)</label>
                                <input
                                    wire:model="default_colors_text"
                                    type="text"
                                    placeholder="Contoh: Phantom Black, Cream, Green, Lavender"
                                    class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                                >
                            </div>
                        </div>

                        <div class="px-5 py-3.5 bg-slate-50/60 border-t border-slate-100 flex items-center justify-end gap-2">
                            <button
                                type="button"
                                wire:click="$set('showModal', false)"
                                class="px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-xs transition-colors cursor-pointer"
                            >
                                Simpan Model
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
