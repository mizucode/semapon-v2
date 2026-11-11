<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('customers.index') }}" class="p-1.5 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $customer->name }}</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1 pl-9">Terdaftar sejak {{ $customer->created_at->format('d M Y') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <a
                href="https://wa.me/{{ $customer->formatted_phone }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition-colors shadow-2xs"
            >
                <i data-lucide="message-circle" class="w-4 h-4 mr-1.5 text-emerald-600"></i>
                Kirim WhatsApp
            </a>
            <x-button
                type="button"
                variant="primary"
                size="sm"
                icon="smartphone"
                wire:click="openAddDeviceModal"
            >
                Tambah Perangkat HP
            </x-button>
        </div>
    </div>

    <!-- Customer Overview Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Customer Profile Card -->
        <x-card title="Informasi Kontak & Alamat">
            <div class="space-y-3.5 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-500">Nomor WhatsApp:</span>
                    <span class="font-mono font-semibold text-slate-900">{{ $customer->phone }}</span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-500">Email:</span>
                    <span class="text-slate-800">{{ $customer->email ?: '-' }}</span>
                </div>
                <div class="pb-2 border-b border-slate-100">
                    <span class="text-slate-500 block mb-1">Alamat:</span>
                    <p class="text-slate-800 leading-relaxed">{{ $customer->address ?: '-' }}</p>
                </div>
                <div>
                    <span class="text-slate-500 block mb-1">Catatan Pelanggan:</span>
                    <p class="text-slate-600 italic">{{ $customer->notes ?: 'Tidak ada catatan tambahan.' }}</p>
                </div>
            </div>
        </x-card>

        <!-- Service History Stats -->
        <x-card title="Statistik Servis Pelanggan">
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-2xs text-slate-500 block">Total Unit Diservis</span>
                        <strong class="text-xl font-bold text-slate-900">{{ $customer->total_orders_count }} Kali</strong>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center">
                        <i data-lucide="wrench" class="w-5 h-5"></i>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-2xs text-slate-500 block">Total Pengeluaran Servis</span>
                        <strong class="text-xl font-bold text-emerald-700">Rp{{ number_format($customer->total_spent, 0, ',', '.') }}</strong>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <i data-lucide="wallet" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Quick Intake Action -->
        <x-card title="Tindakan Cepat">
            <div class="space-y-3">
                <p class="text-xs text-slate-500 leading-relaxed">
                    Terima unit baru dari pelanggan ini untuk dibuatkan nota tanda terima servis langsung.
                </p>
                <div class="pt-2">
                    <a
                        href="#"
                        class="w-full inline-flex items-center justify-center px-4 py-2.5 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-xs transition-colors"
                    >
                        <i data-lucide="plus-circle" class="w-4 h-4 mr-1.5"></i>
                        Buat Order Servis untuk Pelanggan Ini
                    </a>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Customer Devices List -->
    <x-card title="Daftar Perangkat HP Pelanggan" subtitle="HP atau smartphone yang pernah diperbaiki atau dimiliki pelanggan ini">
        <x-slot:action>
            <x-button
                type="button"
                variant="outline"
                size="sm"
                icon="plus"
                wire:click="openAddDeviceModal"
            >
                Tambah Perangkat
            </x-button>
        </x-slot:action>

        @if($customer->devices->isEmpty())
            <x-empty-state
                icon="smartphone"
                title="Belum ada perangkat terdaftar"
                description="Pelanggan ini belum memiliki perangkat smartphone yang ditautkan."
                actionLabel="Tambah Perangkat Sekarang"
                actionClick="$wire.openAddDeviceModal()"
            />
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($customer->devices as $device)
                    <div class="p-4 rounded-2xl border border-slate-200/80 bg-white hover:border-primary-200 transition-colors shadow-2xs space-y-3">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-9 h-9 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
                                    <i data-lucide="smartphone" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-900">{{ $device->device_name }}</h4>
                                    <span class="text-2xs text-slate-400">{{ $device->color ?: 'Warna tidak dicatat' }}</span>
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click="deleteDevice('{{ $device->id }}')"
                                wire:confirm="Hapus perangkat ini dari data pelanggan?"
                                class="text-slate-400 hover:text-rose-600 transition-colors p-1"
                                title="Hapus Perangkat"
                            >
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="pt-2 border-t border-slate-100 space-y-1.5 text-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">IMEI:</span>
                                <span class="font-mono text-slate-700">{{ $device->imei ?: '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Pola/Passcode:</span>
                                <span class="font-mono text-slate-700">{{ $device->passcode ?: '-' }}</span>
                            </div>
                            @if($device->notes)
                                <div class="pt-1 text-slate-500 italic">
                                    "{{ $device->notes }}"
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    <!-- Add Device Modal -->
    <div
        x-data
        x-show="$wire.showAddDeviceModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="$wire.showAddDeviceModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all w-full max-w-md border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-900">Tambah Perangkat HP</h3>
                    <button type="button" @click="$wire.showAddDeviceModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="addDevice" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="d_brand" class="block text-xs font-semibold text-slate-700 mb-1">Merek / Brand</label>
                            <input
                                wire:model="brand"
                                type="text"
                                id="d_brand"
                                required
                                placeholder="iPhone, Samsung, dll."
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                            @error('brand') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="d_model" class="block text-xs font-semibold text-slate-700 mb-1">Tipe / Model</label>
                            <input
                                wire:model="model"
                                type="text"
                                id="d_model"
                                required
                                placeholder="13 Pro, A54, dll."
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                            @error('model') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="d_color" class="block text-xs font-semibold text-slate-700 mb-1">Warna Unit</label>
                            <input
                                wire:model="color"
                                type="text"
                                id="d_color"
                                placeholder="Hitam, Biru, dll."
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                        </div>
                        <div>
                            <label for="d_imei" class="block text-xs font-semibold text-slate-700 mb-1">Nomor IMEI</label>
                            <input
                                wire:model="imei"
                                type="text"
                                id="d_imei"
                                placeholder="15 digit IMEI"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="d_passcode" class="block text-xs font-semibold text-slate-700 mb-1">Pola Kunci / PIN / Sandi Layar</label>
                        <input
                            wire:model="passcode"
                            type="text"
                            id="d_passcode"
                            placeholder="Contoh: PIN 123456 atau Pola L"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                    </div>

                    <div>
                        <label for="d_notes" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Kondisi Khusus</label>
                        <textarea
                            wire:model="device_notes"
                            id="d_notes"
                            rows="2"
                            placeholder="Lecet pemakaian di bezel, kaca kamera baret..."
                            class="block w-full rounded-xl border border-slate-200 p-4 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <x-button type="button" variant="outline" size="sm" @click="$wire.showAddDeviceModal = false">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm" loading="addDevice">
                            Simpan Perangkat
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

