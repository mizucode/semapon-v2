<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Katalog Jasa Servis</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar tarif ongkos kerja, estimasi pengerjaan, dan garansi standar layanan toko.</p>
        </div>
        <div class="flex items-center space-x-2">
            <x-button
                type="button"
                variant="outline"
                size="sm"
                icon="folder-plus"
                @click="$wire.showCategoryModal = true"
            >
                Kelola Kategori
            </x-button>
            <x-button
                type="button"
                variant="primary"
                size="sm"
                icon="plus"
                wire:click="openCreateServiceModal"
            >
                Tambah Layanan
            </x-button>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="relative w-full sm:max-w-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari nama layanan servis..."
                class="block w-full rounded-xl border border-slate-200 pl-10 pr-9 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
            >
        </div>

        <div class="flex items-center space-x-2 w-full sm:w-auto">
            <select
                wire:model.live="selectedCategory"
                class="w-full sm:w-48 rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
            >
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Services Grid -->
    @if($services->isEmpty())
        <x-empty-state
            icon="sparkles"
            title="Belum ada layanan servis"
            description="Tambahkan jenis-jenis layanan jasa servis HP yang Anda sediakan di konter Anda."
            actionLabel="Tambah Layanan Pertama"
            actionClick="$wire.openCreateServiceModal()"
        />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($services as $service)
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between mb-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-3xs font-semibold bg-primary-50 text-primary-700">
                                {{ $service->category?->name ?: 'Umum' }}
                            </span>
                            <x-badge :variant="$service->is_active ? 'success' : 'slate'" size="sm">
                                {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                        </div>
                        <h3 class="font-bold text-sm text-slate-900">{{ $service->name }}</h3>
                        @if($service->description)
                            <p class="text-2xs text-slate-500 mt-1 line-clamp-2">{{ $service->description }}</p>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 space-y-2">
                        <div class="flex items-baseline justify-between">
                            <span class="text-xs text-slate-400">Ongkos Kerja:</span>
                            <span class="text-base font-bold text-slate-900">
                                Rp{{ number_format($service->price, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-2xs text-slate-500">
                            <span><i data-lucide="clock" class="w-3.5 h-3.5 inline mr-1 text-slate-400"></i>{{ $service->estimated_duration_minutes }} Menit</span>
                            <span><i data-lucide="shield-check" class="w-3.5 h-3.5 inline mr-1 text-slate-400"></i>Garansi {{ $service->warranty_days }} Hari</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <button
                            type="button"
                            wire:click="toggleServiceStatus('{{ $service->id }}')"
                            class="text-xs font-medium {{ $service->is_active ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-600 hover:text-emerald-800' }}"
                        >
                            {{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                        <button
                            type="button"
                            wire:click="editService('{{ $service->id }}')"
                            class="px-3 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                            Edit
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $services->links() }}
        </div>
    @endif

    <!-- Add/Edit Service Modal -->
    <div
        x-data
        x-show="$wire.showServiceModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="$wire.showServiceModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all w-full max-w-lg border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-900">{{ $editingServiceId ? 'Ubah Layanan Servis' : 'Tambah Layanan Baru' }}</h3>
                    <button type="button" @click="$wire.showServiceModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="saveService" class="space-y-4">
                    <div>
                        <label for="s_cat" class="block text-xs font-semibold text-slate-700 mb-1">Kategori Layanan</label>
                        <select
                            wire:model="category_id"
                            id="s_cat"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                            <option value="">Pilih Kategori (Opsional)</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="s_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Layanan / Ongkos Kerja</label>
                        <input
                            wire:model="name"
                            type="text"
                            id="s_name"
                            required
                            placeholder="Contoh: Jasa Ganti LCD iPhone"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        @error('name') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="s_desc" class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi / Ruang Lingkup</label>
                        <textarea
                            wire:model="description"
                            id="s_desc"
                            rows="2"
                            placeholder="Pengerjaan termasuk pembersihan lem dan pengeleman ulang..."
                            class="block w-full rounded-xl border border-slate-200 p-4 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="s_price" class="block text-xs font-semibold text-slate-700 mb-1">Tarif Ongkos (Rp)</label>
                            <input
                                wire:model="price"
                                type="number"
                                step="any"
                                id="s_price"
                                required
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                            @error('price') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="s_duration" class="block text-xs font-semibold text-slate-700 mb-1">Estimasi (Menit)</label>
                            <input
                                wire:model="estimated_duration_minutes"
                                type="number"
                                id="s_duration"
                                required
                                min="5"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                        </div>

                        <div>
                            <label for="s_warranty" class="block text-xs font-semibold text-slate-700 mb-1">Garansi (Hari)</label>
                            <input
                                wire:model="warranty_days"
                                type="number"
                                id="s_warranty"
                                required
                                min="0"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <x-button type="button" variant="outline" size="sm" @click="$wire.showServiceModal = false">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm" loading="saveService">
                            Simpan Layanan
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Category Modal -->
    <div
        x-data
        x-show="$wire.showCategoryModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="$wire.showCategoryModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all w-full max-w-sm border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-900">Tambah Kategori Baru</h3>
                    <button type="button" @click="$wire.showCategoryModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="createCategory" class="space-y-4">
                    <div>
                        <label for="cat_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Kategori</label>
                        <input
                            wire:model="new_category_name"
                            type="text"
                            id="cat_name"
                            required
                            placeholder="Contoh: Layar & Touchscreen"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <x-button type="button" variant="outline" size="sm" @click="$wire.showCategoryModal = false">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm" loading="createCategory">
                            Tambah Kategori
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

