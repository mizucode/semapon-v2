<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Teknisi & Komisi</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola staf reparasi, spesialisasi keahlian, dan perhitungan komisi servis.</p>
        </div>
        <div class="flex items-center space-x-2">
            <x-button
                type="button"
                variant="primary"
                size="sm"
                icon="user-plus"
                wire:click="openCreateModal"
            >
                Tambah Teknisi
            </x-button>
        </div>
    </div>

    <!-- Quota & Performance Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-2xs sm:text-xs font-medium text-slate-500">Kuota Teknisi Toko</span>
                <span class="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="flex items-baseline space-x-2 mt-2">
                <span class="text-xl sm:text-2xl font-bold text-slate-900">{{ $activeCount }}</span>
                <span class="text-xs text-slate-400">/ {{ $limit }} Aktif</span>
            </div>
            @if(!$canAdd)
                <span class="inline-flex items-center text-3xs font-medium text-amber-700 mt-1">
                    <i data-lucide="alert-circle" class="w-3 h-3 mr-0.5"></i>
                    Batas paket tercapai
                </span>
            @else
                <span class="inline-flex items-center text-3xs font-medium text-emerald-700 mt-1">
                    Sisa slot: {{ $limit - $activeCount }} teknisi
                </span>
            @endif
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-2xs sm:text-xs font-medium text-slate-500">Total Pekerjaan Selesai</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-xl sm:text-2xl font-bold text-slate-900 mt-2">
                {{ number_format($technicians->sum('total_jobs_completed')) }} Unit
            </p>
            <span class="text-3xs text-slate-400 mt-1 block">Oleh seluruh tim teknisi</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-2xs sm:text-xs font-medium text-slate-500">Total Komisi Terakumulasi</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-xl sm:text-2xl font-bold text-emerald-700 mt-2">
                Rp{{ number_format($technicians->sum('total_commission_earned'), 0, ',', '.') }}
            </p>
            <span class="text-3xs text-slate-400 mt-1 block">Siap dicairkan / dibayarkan</span>
        </div>
    </div>

    <!-- Technicians List -->
    @if($technicians->isEmpty())
        <x-empty-state
            icon="wrench"
            title="Belum ada teknisi terdaftar"
            description="Tambahkan akun teknisi pertama Anda untuk membagi dan menugaskan pekerjaan servis HP."
            actionLabel="Tambah Teknisi Sekarang"
            actionClick="$wire.openCreateModal()"
        />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($technicians as $tech)
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-sm shadow-2xs">
                                {{ strtoupper(substr($tech->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-900">{{ $tech->user->name }}</h3>
                                <p class="text-2xs text-slate-500 font-mono">{{ $tech->user->phone ?: $tech->user->email }}</p>
                            </div>
                        </div>
                        <x-badge :variant="$tech->isActive() ? 'success' : 'slate'">
                            {{ $tech->isActive() ? 'Aktif' : 'Nonaktif' }}
                        </x-badge>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Spesialisasi:</span>
                            <span class="font-semibold text-slate-800">{{ $tech->specialization }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Skema Komisi:</span>
                            <span class="font-semibold text-primary-700">
                                @if($tech->commission_type === 'percentage')
                                    {{ $tech->commission_rate }}% Jasa Servis
                                @elseif($tech->commission_type === 'fixed')
                                    Rp{{ number_format($tech->commission_rate, 0, ',', '.') }} / Unit
                                @else
                                    {{ $tech->commission_rate }}% Bagi Hasil Laba
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Selesai Dikerjakan:</span>
                            <span class="font-bold text-slate-900">{{ $tech->total_jobs_completed }} Unit</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <button
                            type="button"
                            wire:click="openResetPasswordModal('{{ $tech->id }}')"
                            class="text-xs text-slate-500 hover:text-slate-800 font-medium"
                        >
                            Reset Sandi
                        </button>
                        <div class="flex items-center space-x-1.5">
                            <button
                                type="button"
                                wire:click="toggleStatus('{{ $tech->id }}')"
                                class="px-2.5 py-1 text-xs font-medium rounded-xl transition-colors {{ $tech->isActive() ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }}"
                            >
                                {{ $tech->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                            <button
                                type="button"
                                wire:click="editTechnician('{{ $tech->id }}')"
                                class="px-3 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                            >
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Create Technician Modal -->
    <div
        x-data
        x-show="$wire.showCreateModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="$wire.showCreateModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all w-full max-w-lg border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-900">Tambah Akun Teknisi Baru</h3>
                    <button type="button" @click="$wire.showCreateModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="createTechnician" class="space-y-4">
                    @error('general')
                        <div class="p-3 rounded-xl bg-rose-50 text-rose-700 text-xs">{{ $message }}</div>
                    @enderror

                    <div>
                        <label for="t_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Teknisi</label>
                        <input
                            wire:model="name"
                            type="text"
                            id="t_name"
                            required
                            placeholder="Contoh: Rian Hidayat"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        @error('name') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="t_email" class="block text-xs font-semibold text-slate-700 mb-1">Email Login</label>
                            <input
                                wire:model="email"
                                type="email"
                                id="t_email"
                                required
                                placeholder="teknisi@email.com"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                            @error('email') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="t_phone" class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp</label>
                            <input
                                wire:model="phone"
                                type="tel"
                                id="t_phone"
                                required
                                placeholder="081234567890"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                            @error('phone') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="t_password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Akun</label>
                        <input
                            wire:model="password"
                            type="password"
                            id="t_password"
                            required
                            placeholder="Minimal 8 karakter"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        @error('password') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="t_spec" class="block text-xs font-semibold text-slate-700 mb-1">Spesialisasi</label>
                            <select
                                wire:model="specialization"
                                id="t_spec"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                                <option value="All-Round">All-Round (Semua)</option>
                                <option value="Hardware / IC">Hardware / IC Mesin</option>
                                <option value="Ganti LCD / Kaca">Ganti LCD / Kaca</option>
                                <option value="Software / Flashing">Software / Flashing</option>
                            </select>
                        </div>

                        <div>
                            <label for="t_comm_type" class="block text-xs font-semibold text-slate-700 mb-1">Model Komisi</label>
                            <select
                                wire:model="commission_type"
                                id="t_comm_type"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                                <option value="percentage">Persentase (%) Jasa</option>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                                <option value="profit_split">Bagi Hasil Laba (%)</option>
                            </select>
                        </div>

                        <div>
                            <label for="t_comm_rate" class="block text-xs font-semibold text-slate-700 mb-1">Besaran / Rate</label>
                            <input
                                wire:model="commission_rate"
                                type="number"
                                step="any"
                                id="t_comm_rate"
                                required
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <x-button type="button" variant="outline" size="sm" @click="$wire.showCreateModal = false">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm" loading="createTechnician">
                            Simpan & Buat Akun
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Technician Modal -->
    <div
        x-data
        x-show="$wire.showEditModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="$wire.showEditModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all w-full max-w-lg border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-900">Ubah Data Teknisi</h3>
                    <button type="button" @click="$wire.showEditModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="updateTechnician" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="et_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Teknisi</label>
                            <input
                                wire:model="name"
                                type="text"
                                id="et_name"
                                required
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                        </div>
                        <div>
                            <label for="et_phone" class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp</label>
                            <input
                                wire:model="phone"
                                type="tel"
                                id="et_phone"
                                required
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="et_spec" class="block text-xs font-semibold text-slate-700 mb-1">Spesialisasi</label>
                            <select
                                wire:model="specialization"
                                id="et_spec"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                                <option value="All-Round">All-Round (Semua)</option>
                                <option value="Hardware / IC">Hardware / IC Mesin</option>
                                <option value="Ganti LCD / Kaca">Ganti LCD / Kaca</option>
                                <option value="Software / Flashing">Software / Flashing</option>
                            </select>
                        </div>

                        <div>
                            <label for="et_comm_type" class="block text-xs font-semibold text-slate-700 mb-1">Model Komisi</label>
                            <select
                                wire:model="commission_type"
                                id="et_comm_type"
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                                <option value="percentage">Persentase (%) Jasa</option>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                                <option value="profit_split">Bagi Hasil Laba (%)</option>
                            </select>
                        </div>

                        <div>
                            <label for="et_comm_rate" class="block text-xs font-semibold text-slate-700 mb-1">Besaran / Rate</label>
                            <input
                                wire:model="commission_rate"
                                type="number"
                                step="any"
                                id="et_comm_rate"
                                required
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                            >
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <x-button type="button" variant="outline" size="sm" @click="$wire.showEditModal = false">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm" loading="updateTechnician">
                            Simpan Perubahan
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div
        x-data
        x-show="$wire.showResetPasswordModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="$wire.showResetPasswordModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all w-full max-w-sm border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-900">Reset Kata Sandi Teknisi</h3>
                    <button type="button" @click="$wire.showResetPasswordModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="resetPassword" class="space-y-4">
                    <div>
                        <label for="rt_password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Baru</label>
                        <input
                            wire:model="new_password"
                            type="password"
                            id="rt_password"
                            required
                            placeholder="Minimal 8 karakter"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden"
                        >
                        @error('new_password') <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <x-button type="button" variant="outline" size="sm" @click="$wire.showResetPasswordModal = false">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="primary" size="sm" loading="resetPassword">
                            Reset Kata Sandi
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

