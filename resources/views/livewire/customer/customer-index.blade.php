<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Data Pelanggan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola kontak, riwayat perangkat, dan riwayat perbaikan pelanggan toko Anda.</p>
        </div>
        <div class="flex items-center space-x-2">
            <x-button
                type="button"
                variant="primary"
                size="sm"
                icon="user-plus"
                wire:click="openCreateModal"
            >
                Tambah Pelanggan
            </x-button>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="relative w-full sm:max-w-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari nama, nomor HP / WA, atau email..."
                class="block w-full rounded-xl border border-slate-200 pl-10 pr-9 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-colors"
            >
            @if($search)
                <button
                    type="button"
                    wire:click="$set('search', '')"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                >
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            @endif
        </div>

        <div class="flex items-center text-xs text-slate-500 w-full sm:w-auto justify-between sm:justify-end gap-2">
            <span>Total Pelanggan: <strong class="text-slate-900 font-semibold">{{ number_format($totalCustomers) }}</strong></span>
        </div>
    </div>

    <!-- Customer List (Desktop Table & Mobile Cards) -->
    @if($customers->isEmpty())
        <x-empty-state
            icon="users"
            title="Tidak ada pelanggan ditemukan"
            description="{{ $search ? 'Tidak ada data pelanggan yang cocok dengan kata kunci pencarian Anda.' : 'Belum ada data pelanggan yang terdaftar di toko ini.' }}"
            actionLabel="Tambah Pelanggan Baru"
            actionClick="$wire.openCreateModal()"
        />
    @else
        <!-- Desktop Table View -->
        <div class="hidden sm:block bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold text-3xs">
                    <tr>
                        <th class="py-3 px-5">Nama Pelanggan</th>
                        <th class="py-3 px-5">Kontak & WhatsApp</th>
                        <th class="py-3 px-5">Alamat</th>
                        <th class="py-3 px-5 text-center">Perangkat</th>
                        <th class="py-3 px-5 text-center">Total Servis</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach($customers as $customer)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-5">
                                <a href="{{ route('customers.show', $customer->id) }}" class="font-semibold text-slate-900 hover:text-primary-600 block">
                                    {{ $customer->name }}
                                </a>
                                @if($customer->email)
                                    <span class="text-3xs text-slate-400 block">{{ $customer->email }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-5 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono text-xs text-slate-800">{{ $customer->phone }}</span>
                                    <a
                                        href="https://wa.me/{{ $customer->formatted_phone }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="p-1 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors"
                                        title="Kirim pesan WhatsApp"
                                    >
                                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="py-3 px-5 max-w-xs truncate text-slate-500">
                                {{ $customer->address ?: '-' }}
                            </td>
                            <td class="py-3 px-5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-2xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $customer->devices_count }} unit
                                </span>
                            </td>
                            <td class="py-3 px-5 text-center">
                                <span class="font-semibold text-slate-900">{{ $customer->total_orders_count }}</span>
                            </td>
                            <td class="py-3 px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-1">
                                    <a
                                        href="{{ route('customers.show', $customer->id) }}"
                                        class="p-1.5 text-slate-500 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                        title="Lihat Detail & Perangkat"
                                    >
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <button
                                        type="button"
                                        wire:click="editCustomer('{{ $customer->id }}')"
                                        class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors"
                                        title="Edit Data"
                                    >
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <!-- Mobile Cards View -->
        <div class="sm:hidden space-y-3">
            @foreach($customers as $customer)
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <a href="{{ route('customers.show', $customer->id) }}" class="font-bold text-sm text-slate-900 hover:text-primary-600 block">
                                {{ $customer->name }}
                            </a>
                            <div class="flex items-center space-x-2 mt-0.5">
                                <span class="font-mono text-2xs text-slate-600">{{ $customer->phone }}</span>
                                <a
                                    href="https://wa.me/{{ $customer->formatted_phone }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-emerald-600 inline-flex items-center text-3xs font-medium"
                                >
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5 mr-0.5"></i>
                                    WA
                                </a>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-3xs font-semibold bg-primary-50 text-primary-700">
                            {{ $customer->devices_count }} Perangkat
                        </span>
                    </div>

                    @if($customer->address)
                        <p class="text-2xs text-slate-500 line-clamp-1">
                            <i data-lucide="map-pin" class="w-3 h-3 inline mr-1 text-slate-400"></i>
                            {{ $customer->address }}
                        </p>
                    @endif

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-3xs text-slate-400">{{ $customer->total_orders_count }} riwayat servis</span>
                        <div class="flex items-center space-x-2">
                            <button
                                type="button"
                                wire:click="editCustomer('{{ $customer->id }}')"
                                class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
                            >
                                Edit
                            </button>
                            <a
                                href="{{ route('customers.show', $customer->id) }}"
                                class="px-2.5 py-1 text-xs font-semibold text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition-colors"
                            >
                                Detail &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    @endif

    <!-- Create Customer Modal -->
    <div
        x-data
        x-show="$wire.showCreateModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="$wire.showCreateModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all w-full max-w-md border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-900">Tambah Pelanggan Baru</h3>
                    <button type="button" @click="$wire.showCreateModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="createCustomer" class="space-y-4">
                    <div>
                        <label for="c_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input
                            wire:model="name"
                            type="text"
                            id="c_name"
                            required
                            placeholder="Contoh: Hendra Wijaya"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        @error('name') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="c_phone" class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp / HP</label>
                            <input
                                wire:model="phone"
                                type="tel"
                                id="c_phone"
                                required
                                placeholder="081234567890"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                            @error('phone') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="c_email" class="block text-xs font-semibold text-slate-700 mb-1">Email (Opsional)</label>
                            <input
                                wire:model="email"
                                type="email"
                                id="c_email"
                                placeholder="nama@email.com"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="c_address" class="block text-xs font-semibold text-slate-700 mb-1">Alamat (Opsional)</label>
                        <textarea
                            wire:model="address"
                            id="c_address"
                            rows="2"
                            placeholder="Alamat domisili pelanggan..."
                            class="block w-full rounded-xl border border-slate-200 p-4 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div>
                        <label for="c_notes" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <input
                            wire:model="notes"
                            type="text"
                            id="c_notes"
                            placeholder="Pelanggan langganan, rekanan, dll."
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <x-button type="button" variant="outline" size="sm" @click="$wire.showCreateModal = false">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm" loading="createCustomer">
                            Simpan Pelanggan
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div
        x-data
        x-show="$wire.showEditModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="$wire.showEditModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all w-full max-w-md border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-900">Ubah Data Pelanggan</h3>
                    <button type="button" @click="$wire.showEditModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="updateCustomer" class="space-y-4">
                    <div>
                        <label for="e_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input
                            wire:model="name"
                            type="text"
                            id="e_name"
                            required
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        @error('name') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="e_phone" class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp / HP</label>
                            <input
                                wire:model="phone"
                                type="tel"
                                id="e_phone"
                                required
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                            @error('phone') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="e_email" class="block text-xs font-semibold text-slate-700 mb-1">Email (Opsional)</label>
                            <input
                                wire:model="email"
                                type="email"
                                id="e_email"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="e_address" class="block text-xs font-semibold text-slate-700 mb-1">Alamat (Opsional)</label>
                        <textarea
                            wire:model="address"
                            id="e_address"
                            rows="2"
                            class="block w-full rounded-xl border border-slate-200 p-4 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div>
                        <label for="e_notes" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <input
                            wire:model="notes"
                            type="text"
                            id="e_notes"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <x-button type="button" variant="outline" size="sm" @click="$wire.showEditModal = false">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm" loading="updateCustomer">
                            Simpan Perubahan
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

